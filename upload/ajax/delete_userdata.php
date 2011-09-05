<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $userdata, $lang;

$mode = (string) $this->request['mode'];
$user_id = $this->request['user_id'];
		
switch($mode)
{
	case 'delete_profile':
		if ($userdata['user_id'] == $user_id) $this->ajax_die($lang['USER_DELETE_ME']);
	    if (empty($this->request['confirmed'])) $this->prompt_for_confirm($lang['USER_DELETE_CONFIRM']);
	    
		if ($user_id != 2 && $user_id != BOT_UID)
        {
	        require(INC_DIR .'functions_admin.php');

	        user_delete($user_id);
	        delete_user_sessions($user_id);
	
	        $this->response['info'] = $lang['USER_DELETED'];
        }
        else $this->ajax_die($lang['USER_DELETE_CSV']);
    break;
			
    case 'delete_message':
        if (empty($this->request['confirmed']) && $userdata['user_id'] == $user_id) $this->prompt_for_confirm($lang['DELETE_USER_POSTS_ME']);
        if (empty($this->request['confirmed'])) $this->prompt_for_confirm($lang['DELETE_USER_POSTS_CONFIRM']);
        
		if (IS_ADMIN)
	    {
	        require(INC_DIR .'functions_admin.php');
			
	        post_delete('user', $user_id);
			
	        $this->response['info'] = $lang['USER_DELETED_POSTS'];
	    }
	    else $this->ajax_die($lang['NOT_ADMIN']);
    break;
			
	case 'user_activate':
		if (empty($this->request['confirmed'])) $this->prompt_for_confirm($lang['DEACTIVATE_CONFIRM']);
			
	    DB()->query("UPDATE ". BB_USERS ." SET user_active = '1' WHERE user_id = ". $user_id);
			
	    $this->response['info'] = $lang['USER_ACTIVATE_ON'];
	break;
			
	case 'user_deactivate':
        if ($userdata['user_id'] == $user_id) $this->ajax_die($lang['USER_DEACTIVATE_ME']);	
		if (empty($this->request['confirmed'])) $this->prompt_for_confirm($lang['ACTIVATE_CONFIRM']);
			
	    DB()->query("UPDATE ". BB_USERS ." SET user_active = '0' WHERE user_id = ". $user_id);
	    delete_user_sessions($user_id);
			
	    $this->response['info'] = $lang['USER_ACTIVATE_OFF'];
	break;
}

$this->response['mode']	= $mode;
$this->response['url'] = html_entity_decode(make_url('/') . PROFILE_URL . $user_id);