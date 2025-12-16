<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace App\Http\Controllers\Api\Ajax;

use App\Http\Controllers\Api\Ajax\Concerns\AjaxResponse;
use Arokettu\Bencode\Bencode;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TorrentPier\Attachment;
use TorrentPier\Torrent\FileList;

/**
 * View Torrent Controller
 *
 * Returns torrent file list.
 */
class ViewTorrentController
{
    use AjaxResponse;

    protected string $action = 'view_torrent';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        if (!isset($body['topic_id'])) {
            return $this->error(__('EMPTY_TOPIC_ID'));
        }
        $topicId = (int)$body['topic_id'];

        $topic = DB()->fetch_row('
            SELECT
                t.topic_id, t.forum_id, t.attach_ext_id
            FROM ' . BB_TOPICS . " t
            WHERE t.topic_id = $topicId LIMIT 1");

        if (!$topic || $topic['attach_ext_id'] != TORRENT_EXT_ID) {
            return $this->error(__('ERROR_BUILD'));
        }

        // Check rights
        $isAuth = auth(AUTH_ALL, $topic['forum_id'], userdata());
        if (!$isAuth['auth_view']) {
            return $this->error(__('SORRY_AUTH_VIEW_ATTACH'));
        }

        $filename = Attachment::getPath($topicId);
        if (!files()->isFile($filename) || !$fileContents = files()->get($filename)) {
            return $this->error(__('ERROR_NO_ATTACHMENT') . "\n\n" . htmlCHR($filename));
        }

        try {
            $tor = Bencode::decode($fileContents, dictType: Bencode\Collection::ARRAY);
        } catch (Exception $e) {
            return $this->response([
                'html' => htmlCHR(__('TORFILE_INVALID') . ': ' . $e->getMessage()),
            ]);
        }

        $torrent = new FileList($tor);
        $torFilelist = $torrent->get_filelist();

        return $this->response([
            'html' => $torFilelist,
        ]);
    }
}
