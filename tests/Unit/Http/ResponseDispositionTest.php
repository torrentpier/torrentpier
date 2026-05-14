<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TorrentPier\Http\Response;

/**
 * Regression coverage for fix(http): sanitize "/", "\\" and "%" in
 * Content-Disposition filenames.
 *
 * Symfony's HeaderUtils::makeDisposition() (RFC 6266) throws
 * InvalidArgumentException when "/" or "\\" appear in either the filename or
 * the fallback, and when "%" or non-ASCII appears in the fallback. Three call
 * sites (Response::download / Response::torrent / Response::torrentContent)
 * accept user-controlled titles and used to abort with HTTP 500 on common
 * inputs such as "Чарли Чудо-пёс / Charlie the Wonderdog".
 */
beforeEach(function () {
    $this->tempFile = tempnam(sys_get_temp_dir(), 'tp_resp_disp_');
    file_put_contents($this->tempFile, 'dummy');
});

afterEach(function () {
    if (isset($this->tempFile) && is_file($this->tempFile)) {
        @unlink($this->tempFile);
    }
});

/**
 * Extract the value of the (RFC 6266) `filename` parameter from a
 * Content-Disposition header. Symfony emits it bare for token-safe values and
 * quoted for values containing spaces, so cover both.
 */
function filenameParam(string $header): string
{
    if (preg_match('/(?:^|;\s*)filename=(?:"([^"]+)"|([^;]+))/', $header, $m)) {
        return ($m[1] ?? '') !== '' ? $m[1] : ($m[2] ?? '');
    }

    return '';
}

describe('Response::torrentContent() Content-Disposition', function () {
    it('survives a forward slash in the filename', function () {
        $response = Response::torrentContent('d1:ae', 'Charlie / Wonderdog.torrent');

        expect($response->headers->get('Content-Disposition'))->toBeString();
    });

    it('survives a backslash in the filename', function () {
        $response = Response::torrentContent('d1:ae', 'a\\b.torrent');

        expect($response->headers->get('Content-Disposition'))->toBeString();
    });

    it('survives a percent sign in the filename', function () {
        $response = Response::torrentContent('d1:ae', '50%off.torrent');

        expect($response->headers->get('Content-Disposition'))->toBeString();
    });

    it('emits a UTF-8 filename star and a pure-ASCII filename fallback', function () {
        $header = Response::torrentContent('d1:ae', 'Чарли.torrent')
            ->headers->get('Content-Disposition');

        expect($header)->toContain("filename*=utf-8''");

        $name = filenameParam($header);
        expect($name)->toMatch('/^[\x20-\x7E]+$/');
    });

    it('strips a forward slash from both filename and the ASCII fallback', function () {
        $header = Response::torrentContent('d1:ae', 'a/b.torrent')
            ->headers->get('Content-Disposition');

        expect(filenameParam($header))->not->toContain('/');

        preg_match("/filename\\*=utf-8''([^;]+)/", $header, $star);
        expect($star[1] ?? '')->not->toContain('%2F');
    });

    it('falls back to the default name when the input is empty', function () {
        $header = Response::torrentContent('d1:ae', '')
            ->headers->get('Content-Disposition');

        expect($header)->toContain('download.torrent');
    });
});

describe('Response::torrent() Content-Disposition', function () {
    it('survives a non-ASCII filename without throwing', function () {
        $response = Response::torrent($this->tempFile, 'Чарли.torrent');

        expect($response)->toBeInstanceOf(BinaryFileResponse::class)
            ->and($response->headers->get('Content-Disposition'))->toBeString();
    });

    it('strips a forward slash from the filename header', function () {
        $header = Response::torrent($this->tempFile, 'a / b.torrent')
            ->headers->get('Content-Disposition');

        $name = filenameParam($header);
        expect($name)->not->toContain('/');
    });
});

describe('Response::download() Content-Disposition', function () {
    it('handles a non-ASCII title with a slash separator (the reported bug)', function () {
        $header = Response::download($this->tempFile, 'Чарли Чудо-пёс / Charlie the Wonderdog.torrent')
            ->headers->get('Content-Disposition');

        $name = filenameParam($header);
        expect($name)
            ->toMatch('/^[\x20-\x7E]+$/')
            ->not->toContain('/');
    });

    it('strips backslashes from the filename header', function () {
        $header = Response::download($this->tempFile, 'a\\b.torrent')
            ->headers->get('Content-Disposition');

        $name = filenameParam($header);
        expect($name)->not->toContain('\\');
    });
});
