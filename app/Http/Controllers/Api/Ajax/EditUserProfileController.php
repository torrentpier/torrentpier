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
use TorrentPier\Sessions;
use TorrentPier\Validate;

/**
 * Edit User Profile Controller
 *
 * Handles admin editing of user profile fields.
 */
class EditUserProfileController
{
    use AjaxResponse;

    protected string $action = 'edit_user_profile';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody() ?? [];

        $userId = (int)($body['user_id'] ?? 0);
        if (!$userId || !get_userdata($userId)) {
            return $this->error(__('NO_USER_ID_SPECIFIED'));
        }

        $field = (string)($body['field'] ?? '');
        if (!$field) {
            return $this->error('invalid profile field');
        }

        $table = BB_USERS;
        $value = (string)($body['value'] ?? '0');
        $originalValue = $value;
        $responseData = [];

        switch ($field) {
            case 'username':
                $value = clean_username($value);
                if ($err = Validate::username($value)) {
                    return $this->error($err);
                }
                sync_user_to_manticore($userId, $value);
                $responseData['new_value'] = $originalValue;
                break;

            case 'user_email':
                $value = htmlCHR($value);
                if ($err = Validate::email($value)) {
                    return $this->error($err);
                }
                $responseData['new_value'] = $originalValue;
                break;

            case 'user_website':
                if ($value == '' || preg_match('#^https?://[\w\#!$%&~/.\-;:=,?@\p{Cyrillic}\[\]+]+$#iu', $value)) {
                    $responseData['new_value'] = htmlCHR($value);
                } else {
                    return $this->error(__('WEBSITE_ERROR'));
                }
                break;

            case 'user_gender':
                if (!config()->get('gender')) {
                    return $this->error(__('MODULE_OFF'));
                }
                if (!isset(__('GENDER_SELECT')[$value])) {
                    return $this->error(__('ERROR'));
                }
                $responseData['new_value'] = __('GENDER_SELECT')[$value];
                break;

            case 'user_birthday':
                if (!config()->get('birthday_enabled')) {
                    return $this->error(__('MODULE_OFF'));
                }
                $birthdayDate = date_parse($value);

                if (!empty($birthdayDate['year'])) {
                    if (strtotime($value) >= TIMENOW) {
                        return $this->error(__('WRONG_BIRTHDAY_FORMAT'));
                    }
                    if (bb_date(TIMENOW, 'Y', false) - $birthdayDate['year'] > config()->get('birthday_max_age')) {
                        return $this->error(\sprintf(__('BIRTHDAY_TO_HIGH'), config()->get('birthday_max_age')));
                    }
                    if (bb_date(TIMENOW, 'Y', false) - $birthdayDate['year'] < config()->get('birthday_min_age')) {
                        return $this->error(\sprintf(__('BIRTHDAY_TO_LOW'), config()->get('birthday_min_age')));
                    }
                }
                $responseData['new_value'] = $originalValue;
                break;

            case 'user_twitter':
                if ($value && !preg_match('#^[a-zA-Z0-9_]{1,15}$#', $value)) {
                    return $this->error(__('TWITTER_ERROR'));
                }
                $responseData['new_value'] = $originalValue;
                break;

            case 'user_occ':
            case 'user_interests':
                $responseData['new_value'] = htmlCHR($value);
                break;

            case 'u_up_total':
            case 'u_down_total':
            case 'u_up_release':
            case 'u_up_bonus':
                if (!IS_ADMIN) {
                    return $this->error(__('NOT_ADMIN'));
                }

                $table = BB_BT_USERS;
                $numericValue = (int)$originalValue;

                if ($numericValue < 0) {
                    return $this->error(__('WRONG_INPUT'));
                }

                foreach (['KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8] as $s => $m) {
                    if (stripos($originalValue, $s) !== false) {
                        $numericValue *= (1024 ** $m);
                        break;
                    }
                }
                $value = (string)$numericValue;
                $responseData['new_value'] = humn_size($numericValue, space: ' ');

                $btu = get_bt_userdata($userId);
                $btu[$field] = $numericValue;
                $responseData['update_ids']['u_ratio'] = (string)get_bt_ratio($btu);
                CACHE('bb_cache')->rm('btu_' . $userId);
                break;

            case 'user_points':
                $floatValue = (float)str_replace(',', '.', $originalValue);
                $value = \sprintf('%.2f', $floatValue);
                if ($floatValue < 0.0 || \strlen(strstr($value, '.', true)) > 14) {
                    return $this->error(__('WRONG_INPUT'));
                }
                $responseData['new_value'] = $value;
                break;

            default:
                return $this->error("invalid profile field: $field");
        }

        $valueSql = DB()->escape($value, true);
        DB()->query("UPDATE $table SET $field = $valueSql WHERE user_id = $userId LIMIT 1");

        Sessions::cache_rm_user_sessions($userId);

        $responseData['edit_id'] = $body['edit_id'] ?? '';

        return $this->response($responseData);
    }
}
