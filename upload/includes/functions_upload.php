<?php

class upload_common
{
	var $cfg = array(
		'max_size'    => 0,
		'max_width'   => 0,
		'max_height'  => 0,
		'allowed_ext' => array(),
		'upload_path' => '',
	);
	var $file = array(
		'name'     => '',
		'type'     => '',
		'size'     => 0,
		'tmp_name' => '',
		'error'    => UPLOAD_ERR_NO_FILE,
	);
	var $orig_name   = '';
	var $file_path   = '';       // Stored file path
	var $file_ext    = '';
	var $file_ext_id = '';
	var $file_size   = '';
	var $ext_ids     = array();  // array_flip($bb_cfg['file_id_ext'])
	var $errors      = array();
	var $img_types   = array(1=>'gif', 2=>'jpg', 3=>'png', 6=>'bmp', 7=>'tiff', 8=>'tiff');

	function init ($cfg = array(), $post_params = array(), $uploaded_only = true)
	{
		global $bb_cfg, $lang;

		$lang['upload_error_common'] = 'Ошибка загрузки файла';
		$lang['upload_errors'] = array(
			UPLOAD_ERR_INI_SIZE   => 'превышен максимальный размер файлов для сервера',
			UPLOAD_ERR_FORM_SIZE  => 'превышен максимальный размер загружаемого файла',
			UPLOAD_ERR_PARTIAL    => 'файл был загружен частично',
			UPLOAD_ERR_NO_FILE    => 'файл не был загружен',
			UPLOAD_ERR_NO_TMP_DIR => 'временная директория не найдена',
			UPLOAD_ERR_CANT_WRITE => 'ошибка записи',
			UPLOAD_ERR_EXTENSION  => 'загрузка остановлена из-за расширения',
		);

		$this->cfg = array_merge($this->cfg, $cfg);
		$this->file = $post_params;

		// upload errors from $_FILES
		if ($this->file['error'])
		{
			$msg = $lang['upload_error_common'];
			$msg .= ($err_desc =& $lang['upload_errors'][$this->file['error']]) ? " ($err_desc)" : '';
			$this->errors[] = $msg;
			return false;
		}
		// file_exists
		if (!file_exists($this->file['tmp_name']))
		{
			$this->errors[] = "uploaded file not exists: {$this->file['tmp_name']}";
			return false;
		}
		// size
		if (!$this->file_size = filesize($this->file['tmp_name']))
		{
			$this->errors[] = "uploaded file is empty: {$this->file['tmp_name']}";
			return false;
		}
		if ($this->cfg['max_size'] && $this->file_size > $this->cfg['max_size'])
		{
			$this->errors[] = 'Загруженный файл превышает максимальный размер '. humn_size($this->cfg['max_size']);
			return false;
		}
		// is_uploaded_file
		if ($uploaded_only && !is_uploaded_file($this->file['tmp_name']))
		{
			$this->errors[] = "not uploaded file: {$this->file['tmp_name']}";
			return false;
		}
		// get ext
		$this->ext_ids = array_flip($bb_cfg['file_id_ext']);
		$file_name_ary = explode('.', $this->file['name']);
		$this->file_ext = strtolower(end($file_name_ary));

		// img
		if ($this->cfg['max_width'] || $this->cfg['max_height'])
		{
			if ($img_info = getimagesize($this->file['tmp_name']))
			{
				list($width, $height, $type, $attr) = $img_info;

				// redefine ext
				if (!$width || !$height || !$type || !isset($this->img_types[$type]))
				{
					$this->errors[] = 'Недопустимый тип файла изображения';
					return false;
				}
				$this->file_ext = $this->img_types[$type];

				// width & height
				if (($this->cfg['max_width'] && $width > $this->cfg['max_width']) || ($this->cfg['max_height'] && $height > $this->cfg['max_height']))
				{
					$this->errors[] = "Размеры изображения превышают максимально допустимые {$this->cfg['max_width']}x{$this->cfg['max_height']} px";
					return false;
				}
			}
			else
			{
				$this->errors[] = 'Загруженный файл не является изображением';
				return false;
			}
		}
		// check ext
		if (!isset($this->ext_ids[$this->file_ext]) || !in_array($this->file_ext, $this->cfg['allowed_ext'], true))
		{
			$this->errors[] = 'Расширение '. htmlCHR($this->file_ext) .' для загружаемых файлов не разрешено';
			return false;
		}
		$this->file_ext_id = $this->ext_ids[$this->file_ext];

		return true;
	}

	function store ($mode = '', $params = array())
	{
		global $bb_cfg;

		if ($mode == 'avatar')
		{
			delete_avatar($params['user_id'], $params['avatar_ext_id']);
			$file_path = get_avatar_path($params['user_id'], $this->file_ext_id, $bb_cfg['avatars']['upload_path']);
			return $this->_move($file_path);
		}
		else if ($mode == 'attach')
		{
			$file_path = get_attach_path($params['topic_id']);
			return $this->_move($file_path);
		}
		else
		{
			trigger_error("invalid upload mode: $mode", E_USER_ERROR);
		}
	}

	function _move ($file_path)
	{
		$dir = dirname($file_path);
		if (!file_exists($dir))
		{
			if (!bb_mkdir($dir))
			{
				$this->errors[] = "Cannot create dir: $dir";
				return false;
			}
		}
		if (!@rename($this->file['tmp_name'], $file_path))
		{
			if (!@copy($this->file['tmp_name'], $file_path))
			{
				$this->errors[] = 'Cannot copy tmp file';
				return false;
			}
			@unlink($this->file['tmp_name']);
		}
		@chmod($file_path, 0664);

		return file_exists($file_path);
	}
}