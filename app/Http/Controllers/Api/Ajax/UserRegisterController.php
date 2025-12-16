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
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TorrentPier\Validate;

/**
 * User Register Controller
 *
 * Handles real-time validation during user registration.
 */
class UserRegisterController
{
    use AjaxResponse;

    protected string $action = 'user_register';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        $mode = $this->requireMode($body);
        if ($mode instanceof ResponseInterface) {
            return $mode;
        }

        $html = '<img src="/assets/images/good.gif">';
        switch ($mode) {
            case 'check_name':
                $username = clean_username($body['username'] ?? '');
                if ($err = Validate::username($username)) {
                    $html = '<img src="/assets/images/bad.gif"> <span class="leechmed bold">' . $err . '</span>';
                }
                break;

            case 'check_email':
                $email = (string)($body['email'] ?? '');
                if ($err = Validate::email($email)) {
                    $html = '<img src="/assets/images/bad.gif"> <span class="leechmed bold">' . $err . '</span>';
                }
                break;

            case 'check_pass':
                $pass = (string)($body['pass'] ?? '');
                $passConfirm = (string)($body['pass_confirm'] ?? '');
                if ($err = Validate::password($pass, $passConfirm)) {
                    $html = '<img src="/assets/images/bad.gif"> <span class="leechmed bold">' . $err . '</span>';
                } else {
                    $text = IS_GUEST ? __('CHOOSE_PASS_REG_OK') : __('CHOOSE_PASS_OK');
                    $html = '<img src="/assets/images/good.gif"> <span class="seedmed bold">' . $text . '</span>';
                }
                break;

            case 'check_country':
                $country = (string)($body['country'] ?? '');
                $html = render_flag($country, false);
                break;

            default:
                return $this->error('Invalid mode: ' . $mode);
        }

        return $this->response([
            'html' => $html,
            'mode' => $mode,
        ]);
    }
}
