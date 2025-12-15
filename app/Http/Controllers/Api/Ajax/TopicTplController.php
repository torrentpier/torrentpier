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
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Topic Template Controller
 *
 * Handles topic template management for super admins.
 */
class TopicTplController
{
    use AjaxResponse;

    protected string $action = 'topic_tpl';

    /**
     * @throws BindingResolutionException
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        if (!IS_SUPER_ADMIN) {
            return $this->error(__('ONLY_FOR_SUPER_ADMIN'));
        }

        $body = $request->getParsedBody() ?? [];
        array_deep($body, 'trim');

        $mode = (string)($body['mode'] ?? '');
        $sqlError = false;

        // Validate tpl_id for load/save/remove modes
        $tplId = 0;
        $tplData = null;
        if (\in_array($mode, ['load', 'save', 'remove'], true)) {
            $tplId = (int)($body['tpl_id'] ?? 0);
            if (!$tplId) {
                return $this->error('Выбранный шаблон не найден, создайте новый (empty tpl_id)');
            }
            $tplData = DB()->fetch_row('SELECT * FROM ' . BB_TOPIC_TPL . " WHERE tpl_id = $tplId LIMIT 1");
            if (!$tplData) {
                return $this->error("Шаблон [id: $tplId] не найден в БД");
            }
        }

        // Validate and prepare sql_args for save/new modes
        $sqlArgs = [];
        $tplName = '';
        if (\in_array($mode, ['save', 'new'], true)) {
            $tplName = htmlCHR(Str::squish($body['tpl_name'] ?? ''));
            if (!$tplName) {
                return $this->error('не заполнено название шаблона');
            }
            $tplName = substr($tplName, 0, 60);

            $tplSrcForm = htmlCHR($body['tpl_src_form'] ?? '');
            if (!$tplSrcForm) {
                return $this->error('не заполнен скрипт формы шаблона');
            }

            $tplSrcTitle = htmlCHR($body['tpl_src_title'] ?? '');
            if (!$tplSrcTitle) {
                return $this->error('не заполнен формат названия темы');
            }
            $tplSrcTitle = Str::squish($tplSrcTitle);

            $tplSrcMsg = htmlCHR($body['tpl_src_msg'] ?? '');
            if (!$tplSrcMsg) {
                return $this->error('не заполнен формат создания сообщения');
            }

            $tplComment = htmlCHR($body['tpl_comment'] ?? '');

            preg_match('#\d+#', (string)($body['tpl_rules'] ?? ''), $m);
            $tplRulesPostId = isset($m[0]) ? (int)$m[0] : 0;

            $sqlArgs = [
                'tpl_name' => $tplName,
                'tpl_src_form' => $tplSrcForm,
                'tpl_src_title' => $tplSrcTitle,
                'tpl_src_msg' => $tplSrcMsg,
                'tpl_comment' => $tplComment,
                'tpl_rules_post_id' => $tplRulesPostId,
                'tpl_last_edit_tm' => (int)TIMENOW,
                'tpl_last_edit_by' => (int)userdata('user_id'),
            ];
        }

        $responseData = [];

        switch ($mode) {
            case 'load':
                $val = [
                    'tpl-name-save' => $tplData['tpl_name'],
                    'tpl-src-form' => $tplData['tpl_src_form'],
                    'tpl-src-title' => $tplData['tpl_src_title'],
                    'tpl-src-msg' => $tplData['tpl_src_msg'],
                    'tpl-comment-save' => $tplData['tpl_comment'],
                    'tpl-rules-save' => $tplData['tpl_rules_post_id'],
                ];
                array_deep($val, 'html_ent_decode');

                $val['tpl-id-save'] = $tplId;
                $val['tpl-last-edit-tst'] = $tplData['tpl_last_edit_tm'];

                $responseData['val'] = $val;
                $responseData['html'] = [
                    'tpl-name-old-save' => $tplData['tpl_name'],
                    'tpl-last-edit-time' => bb_date($tplData['tpl_last_edit_tm'], 'd-M-y H:i'),
                    'tpl-last-edit-by' => profile_url(get_userdata((int)$tplData['tpl_last_edit_by'])),
                ];
                $responseData['tpl_rules_href'] = POST_URL . $tplData['tpl_rules_post_id'] . '#' . $tplData['tpl_rules_post_id'];
                break;

            case 'assign':
                $assignTplId = (int)($body['tpl_id'] ?? 0);
                if (!$assignTplId) {
                    return $this->error('Выбранный шаблон не найден, создайте новый (empty tpl_id)');
                }
                $forumId = (int)($body['forum_id'] ?? 0);
                if (!$forumId) {
                    return $this->error('empty forum_id');
                }
                if (!forum_exists($forumId)) {
                    return $this->error("нет такого форума [id: $forumId]");
                }

                if ($assignTplId == -1) {
                    // Disable templates
                    $newTplId = 0;
                    $responseData['msg'] = 'Шаблоны в этом форуме отключены';
                } else {
                    // Enable template
                    $templateName = DB()->fetch_row('SELECT tpl_name FROM ' . BB_TOPIC_TPL . " WHERE tpl_id = $assignTplId LIMIT 1", 'tpl_name');
                    if (!$templateName) {
                        return $this->error("Шаблон [id: $assignTplId] не найден в БД");
                    }
                    $newTplId = $assignTplId;
                    $responseData['msg'] = "Включен шаблон $templateName";
                }
                DB()->query('UPDATE ' . BB_FORUMS . " SET forum_tpl_id = $newTplId WHERE forum_id = $forumId LIMIT 1");
                break;

            case 'save':
                if ($tplData['tpl_last_edit_tm'] > ($body['tpl_l_ed_tst'] ?? 0) && $tplData['tpl_last_edit_by'] != userdata('user_id')) {
                    $lastEditByUsername = get_username((int)$tplData['tpl_last_edit_by']);
                    $msg = "Изменения не были сохранены!\n\n";
                    $msg .= 'Шаблон был отредактирован: ' . html_ent_decode($lastEditByUsername) . ', ' . bb_date($tplData['tpl_last_edit_tm'], 'd-M-y H:i');

                    return $this->error($msg);
                }
                $sql = 'UPDATE ' . BB_TOPIC_TPL . ' SET ' . DB()->build_array('UPDATE', $sqlArgs) . " WHERE tpl_id = $tplId LIMIT 1";
                if (!DB()->query($sql)) {
                    $sqlError = DB()->sql_error();
                }
                $responseData['tpl_id'] = $tplId;
                $responseData['tpl_name'] = $tplName;
                $responseData['html'] = [
                    'tpl-last-edit-time' => bb_date(TIMENOW, 'd-M-y H:i'),
                    'tpl-last-edit-by' => profile_url(get_userdata(userdata('username'), true)),
                ];
                break;

            case 'new':
                $sql = 'INSERT INTO ' . BB_TOPIC_TPL . DB()->build_array('INSERT', $sqlArgs);
                if (!DB()->query($sql)) {
                    $sqlError = DB()->sql_error();
                }
                break;

            case 'remove':
                $forumId = (int)($body['forum_id'] ?? 0);
                if (!$forumId) {
                    return $this->error('empty forum_id');
                }
                if (!forum_exists($forumId)) {
                    return $this->error("нет такого форума [id: $forumId]");
                }
                $sql = 'DELETE FROM ' . BB_TOPIC_TPL . " WHERE tpl_id = $tplId LIMIT 1";
                if (!DB()->query($sql)) {
                    $sqlError = DB()->sql_error();
                }
                $getForumTplId = DB()->fetch_row('SELECT forum_tpl_id FROM ' . BB_FORUMS . ' WHERE forum_id = ' . $forumId . ' LIMIT 1');
                if ($tplId == $getForumTplId['forum_tpl_id']) {
                    DB()->query('UPDATE ' . BB_FORUMS . " SET forum_tpl_id = 0 WHERE forum_id = $forumId LIMIT 1");
                }
                $responseData['msg'] = "Шаблон {$tplData['tpl_name']} успешно удалён";
                break;

            default:
                return $this->error("invalid mode: $mode");
        }

        // Handle SQL errors (duplicate entry)
        if ($sqlError) {
            if ($sqlError['code'] == 1062) {
                return $this->error('Шаблон с таким названием уже существует, выберите другое название');
            }

            return $this->error("db error {$sqlError['code']}: {$sqlError['message']}");
        }

        $responseData['mode'] = $mode;
        $responseData['timestamp'] = TIMENOW;

        return $this->response($responseData);
    }
}
