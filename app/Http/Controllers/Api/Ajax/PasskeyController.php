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
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TorrentPier\Torrent\Passkey;
use TorrentPier\Tracker\Peers;

/**
 * Passkey Controller
 *
 * Handles passkey generation for users.
 */
class PasskeyController
{
    use AjaxResponse;

    protected string $action = 'passkey';

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        $mode = $this->requireMode($body);
        if ($mode instanceof ResponseInterface) {
            return $mode;
        }

        $reqUid = $this->requireUserId($body);
        if ($reqUid instanceof ResponseInterface) {
            return $reqUid;
        }

        if (!IS_ADMIN && $reqUid != userdata('user_id')) {
            return $this->error(__('NOT_AUTHORISED'));
        }

        switch ($mode) {
            case 'generate':
                if (empty($body['confirmed'])) {
                    return $this->promptConfirm(__('BT_GEN_PASSKEY_NEW'));
                }

                if (!$passkey = Passkey::generate($reqUid, IS_ADMIN)) {
                    return $this->error('Could not insert passkey');
                }

                Peers::removeByUser($reqUid);

                return $this->response([
                    'passkey' => $passkey,
                ]);

            default:
                return $this->error('Invalid mode: ' . $mode);
        }
    }
}
