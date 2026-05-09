<?php

declare(strict_types=1);

/**
 * Integration tests for the CSRF middleware (PR #2420).
 *
 * Verifies end-to-end behaviour of `App\Http\Middleware\VerifyCsrfToken`
 * against a running TorrentPier instance: token issuance via meta tag and
 * hidden form field, 419 on missing/wrong token, 200 on correct `_token`
 * body field or `X-CSRF-Token` header, token rotation after login/logout.
 *
 * Skips automatically when no HTTP base URL is configured. Set
 *   TP_TEST_BASE_URL=https://tp.test/   (or another running instance)
 * before running pest to enable.
 */

const CSRF_LOGIN_PATH = '/login';
const CSRF_PROTECTED_POST_PATH = '/posting';
const CSRF_TOKEN_LENGTH = 40;

beforeEach(function () {
    $base = getenv('TP_TEST_BASE_URL') ?: 'https://tp.test/';
    $this->base = rtrim($base, '/');

    // Probe — skip the suite when the host is unreachable so CI without
    // a live instance does not flap.
    $ctx = stream_context_create([
        'http' => ['timeout' => 2, 'ignore_errors' => true],
        'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    $head = @file_get_contents($this->base . CSRF_LOGIN_PATH, false, $ctx);
    if ($head === false) {
        $this->markTestSkipped("TorrentPier instance not reachable at {$this->base}");
    }

    $this->cookieJar = [];
});

/**
 * Issue a request with the in-memory cookie jar, returning [status, headers, body].
 *
 * @return array{0: int, 1: array<string, list<string>>, 2: string}
 */
function csrfRequest(
    object $ctx,
    string $method,
    string $path,
    array $body = [],
    array $extraHeaders = [],
): array {
    $url = $ctx->base . $path;

    $headers = ['Accept: text/html,application/json'];
    foreach ($extraHeaders as $name => $value) {
        $headers[] = $name . ': ' . $value;
    }
    if (!empty($ctx->cookieJar)) {
        $cookieHeader = 'Cookie: ' . implode('; ', array_map(
            fn ($name, $value) => $name . '=' . $value,
            array_keys($ctx->cookieJar),
            array_values($ctx->cookieJar),
        ));
        $headers[] = $cookieHeader;
    }

    $opts = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers) . "\r\n",
            'timeout' => 5,
            'ignore_errors' => true,
            'follow_location' => 0,
        ],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ];

    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) && $body !== []) {
        $opts['http']['header'] .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $opts['http']['content'] = http_build_query($body);
    }

    $stream = @fopen($url, 'r', false, stream_context_create($opts));
    if ($stream === false) {
        return [0, [], ''];
    }

    $meta = stream_get_meta_data($stream);
    $responseHeaders = [];
    $status = 0;
    foreach ($meta['wrapper_data'] ?? [] as $line) {
        if (preg_match('#^HTTP/[\d.]+\s+(\d+)#', $line, $m)) {
            $status = (int) $m[1];
            continue;
        }
        if (str_contains($line, ':')) {
            [$name, $value] = explode(':', $line, 2);
            $responseHeaders[strtolower(trim($name))][] = trim($value);
        }
    }

    foreach ($responseHeaders['set-cookie'] ?? [] as $sc) {
        if (preg_match('#^([^=]+)=([^;]*)#', $sc, $m)) {
            $ctx->cookieJar[$m[1]] = $m[2];
        }
    }

    $bodyContent = stream_get_contents($stream);
    fclose($stream);

    return [$status, $responseHeaders, $bodyContent ?: ''];
}

function extractMetaToken(string $html): ?string
{
    return preg_match('#<meta\s+name="csrf-token"\s+content="([^"]+)"#', $html, $m) ? $m[1] : null;
}

function extractFormToken(string $html): ?string
{
    return preg_match('#name="_token"\s+value="([^"]+)"#', $html, $m) ? $m[1] : null;
}

describe('CSRF middleware (live HTTP)', function () {
    test('GET /login issues a 40-char token in both meta tag and hidden form field', function () {
        [$status, , $body] = csrfRequest($this, 'GET', CSRF_LOGIN_PATH);
        expect($status)->toBe(200);
        $meta = extractMetaToken($body);
        $form = extractFormToken($body);
        expect($meta)
            ->not->toBeNull()
            ->and(strlen($meta))->toBe(CSRF_TOKEN_LENGTH);
        expect($form)
            ->not->toBeNull()
            ->and($form)->toBe($meta);
    });

    test('POST without _token is rejected with 419 and a plain-text body', function () {
        [$status, , $body] = csrfRequest($this, 'POST', CSRF_LOGIN_PATH, [
            'login_username' => 'admin',
            'login_password' => 'wrong',
            'login' => '1',
        ]);
        expect($status)->toBe(419)
            ->and(trim($body))->toBe('CSRF token mismatch.');
    });

    test('POST with a wrong _token is rejected with 419', function () {
        [$status] = csrfRequest($this, 'POST', CSRF_LOGIN_PATH, [
            '_token' => str_repeat('x', CSRF_TOKEN_LENGTH),
            'login_username' => 'admin',
            'login_password' => 'wrong',
            'login' => '1',
        ]);
        expect($status)->toBe(419);
    });

    test('POST with a wrong X-CSRF-Token header is rejected with 419', function () {
        [$status] = csrfRequest($this, 'POST', CSRF_PROTECTED_POST_PATH, [
            'mode' => 'newtopic',
            'f' => '1',
        ], ['X-CSRF-Token' => str_repeat('y', CSRF_TOKEN_LENGTH)]);
        expect($status)->toBe(419);
    });

    test('POST with the matching _token authenticates admin and rotates the token', function () {
        [, , $loginPage] = csrfRequest($this, 'GET', CSRF_LOGIN_PATH);
        $guestToken = extractMetaToken($loginPage);
        expect($guestToken)->not->toBeNull();

        [$status, $headers] = csrfRequest($this, 'POST', CSRF_LOGIN_PATH, [
            '_token' => $guestToken,
            'login_username' => 'admin',
            'login_password' => 'admin',
            'login' => '1',
        ]);
        expect($status)->toBe(302)
            ->and($headers['location'][0] ?? null)->not->toBeNull();

        // Visit a logged-in page; the new token should differ from the guest one.
        [, , $home] = csrfRequest($this, 'GET', '/');
        $adminToken = extractMetaToken($home);
        expect($adminToken)
            ->not->toBeNull()
            ->and($adminToken)->not->toBe($guestToken);

        // The stale guest token must now be rejected on a state-changing endpoint.
        [$staleStatus] = csrfRequest($this, 'POST', CSRF_PROTECTED_POST_PATH, [
            '_token' => $guestToken,
            'mode' => 'newtopic',
            'f' => '1',
        ]);
        expect($staleStatus)->toBe(419);
    })->skip(
        getenv('TP_TEST_ADMIN_PASSWORD') !== false && getenv('TP_TEST_ADMIN_PASSWORD') !== 'admin',
        'Default admin/admin credentials required for this test',
    );

    test('POST with the matching token in the X-CSRF-Token header is accepted', function () {
        [, , $page] = csrfRequest($this, 'GET', CSRF_LOGIN_PATH);
        $token = extractMetaToken($page);
        expect($token)->not->toBeNull();

        [$status] = csrfRequest($this, 'POST', CSRF_PROTECTED_POST_PATH, [
            'mode' => 'newtopic',
            'f' => '1',
        ], ['X-CSRF-Token' => $token]);

        // /posting is auth-gated, so a guest gets a redirect/200 page rather than 419 — either
        // way the request passed CSRF. We only assert that 419 did NOT happen.
        expect($status)->not->toBe(419);
    });
});
