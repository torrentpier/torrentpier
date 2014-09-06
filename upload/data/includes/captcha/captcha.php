<?php

/**
*  Captcha
*/
class captcha_common
{
	var $cfg            = array();      // конфиг
	var $can_bypass     = false;        // может обойти капчу
	var $cap_img_total  = 300;          // количество текущих картинок
	var $new_per_minute = 10;           // сколько генерить новых, столько же будет помечаться для удаления
	var $key_ttl        = 300;          // время жизни _code_ ключа
	var $cap_sid_len    = 20;           // длина sid'a
	var $cap_min_chars  = 3;            // минимум символов на картинке
	var $cap_max_chars  = 5;            // максимум
	var $img_ext        = 'jpg';

	var $cap_sid_key    = 'cap_sid';    // ключи/значения в $_POST
	var $cap_sid_val    = '';
	var $curr_code_key  = '';
	var $prev_code_key  = '';

	var $new_cap_id     = 0;
	var $new_cap_sid    = '';
	var $new_code_key   = '';
	var $new_cap_code   = '';
	var $new_img_url   = '';
	var $new_img_path   = '';
	var $new_img_bin    = '';

	function captcha_common ($cfg)
	{
		$this->cfg           = $cfg;
		$this->can_bypass    = !empty($_POST[$this->cfg['secret_key']]);
		$this->curr_code_key = $this->get_key_name(TIMENOW);
		$this->prev_code_key = $this->get_key_name(TIMENOW - $this->key_ttl);
	}

	function verify_code ()
	{
		// обход
		if ($this->can_bypass || $this->cfg['disabled'])
		{
			if (!empty($_POST[$this->cfg['secret_key']])) log_get('cap/off', @$_POST['login_username']);
			return true;
		}
		// cap_sid
		if (isset($_POST[$this->cap_sid_key]) && verify_id($_POST[$this->cap_sid_key], $this->cap_sid_len))
		{
			$this->cap_sid_val = $_POST[$this->cap_sid_key];
		}
		else
		{
			return false;
		}
		// code
		$entered_code = '';
		if (isset($_POST[$this->curr_code_key]))
		{
			$entered_code = (string) $_POST[$this->curr_code_key];
		}
		else if (isset($_POST[$this->prev_code_key]))
		{
			$entered_code = (string) $_POST[$this->prev_code_key];
		}

		$entered_code = strtolower(trim($entered_code));

		$valid_code = $this->get_code();

		if ($entered_code === $valid_code)
		{
			$this->del_sid();
			return true;
		}
		else
		{
			$this->del_sid();
			return false;
		}
	}

	function get_html ()
	{
		if ($this->cfg['disabled']) return '';

		$this->gen_cap_sid();
		$this->new_img_url  = $this->get_img_url($this->new_cap_id);
		$this->new_code_key = $this->get_key_name(TIMENOW);

		return '
			<div><img src="'. $this->new_img_url .'?'. mt_rand() .'" width="120" height="72" alt="pic" /></div>
			<input type="hidden" name="'. $this->cap_sid_key .'" value="'. $this->new_cap_sid .'" />
			<input type="text" name="'. $this->new_code_key .'" value="" size="25" class="bold" />
		';
	}

	function get_code ()
	{
		if ($this->cap_sid_val AND $code = CACHE('bb_cap_sid')->get('c_sid_'. $this->cap_sid_val))
		{
			return strtolower(trim($code));
		}
		else
		{
			return null;
		}
	}

	function del_sid ()
	{
		if ($this->cap_sid_val)
		{
			CACHE('bb_cap_sid')->rm('c_sid_'. $this->cap_sid_val);
		}
	}

	function gen_cap_sid ()
	{
		$row = DB('cap')->fetch_row("SELECT MIN(cap_id) AS min_id, MAX(cap_id) AS max_id FROM ". BB_CAPTCHA ." WHERE cap_id > 0");

		$min_id = intval($row['min_id']) + $this->new_per_minute;
		$max_id = intval($row['max_id']);

		$this->new_cap_id = ($min_id < $max_id) ? mt_rand($min_id, $max_id) : $max_id;

		$this->new_cap_code = (string) DB('cap')->fetch_row("SELECT cap_code FROM ". BB_CAPTCHA ." WHERE cap_id = {$this->new_cap_id}", 'cap_code');

		$this->new_cap_sid = make_rand_str($this->cap_sid_len);

		CACHE('bb_cap_sid')->set('c_sid_'. $this->new_cap_sid, $this->new_cap_code, $this->key_ttl*2);
	}

	function get_img_url ($id)
	{
		return $this->get_path($id, $this->cfg['img_url']);
	}

	function get_img_path ($id)
	{
		return $this->get_path($id, $this->cfg['img_path']);
	}

	function get_path ($id, $base)
	{
		$path = $base . ($id % 50) .'/'. $id .'.'. $this->img_ext;
		return preg_replace("#/($id)(\.{$this->img_ext})\$#", '/'. md5($this->cfg['secret_key'] . md5($id)) .'$2', $path);
	}

	/**
	*  Генерит валидное имя ключа для получения введенного кода капчи из $_POST
	*/
	function get_key_name ($tm)
	{
		return 'cap_code_'. md5($this->cfg['secret_key'] . md5($tm - ($tm % $this->key_ttl)));
	}
}

class captcha_kcaptcha extends captcha_common
{
	// generates keystring and image
	function gen_img ($cap_id)
	{
		global $bb_cfg;

		// do not change without changing font files!
		$alphabet = "0123456789abcdefghijklmnopqrstuvwxyz";

		# symbols used to draw CAPTCHA - alphabet without similar symbols (o=0, 1=l, i=j, t=f)
		$allowed_symbols = "23456789abcdeghkmnpqsuvxyz";

		# folder with fonts
		$fontsdir = INC_DIR .'captcha/kcaptcha/fonts/';

		$fonts = array(
			'antiqua.png',
			'baskerville.png',
			'batang.png',
			'bookman.png',
			'calisto.png',
			'cambria.png',
			'centaur.png',
			'century.png',
			'chaparral.png',
			'constantia.png',
			'footlight.png',
			'garamond.png',
			'georgia.png',
			'goudy_old.png',
			'kozuka.png',
			'lucida.png',
			'minion.png',
			'palatino.png',
			'perpetua.png',
			'rockwell.png',
			'times.png',
			'warnock.png',
		);

		# CAPTCHA string length
		$length = mt_rand($this->cap_min_chars, $this->cap_max_chars);

		# CAPTCHA image size (you do not need to change it, whis parameters is optimal)
		$width = 120;
		$height = 60;

		# symbol's vertical fluctuation amplitude divided by 2
		$fluctuation_amplitude = 5;

		# increase safety by prevention of spaces between symbols
		$no_spaces = true;

		# show credits
		$show_credits = true; # set to false to remove credits line. Credits adds 12 pixels to image height
		$credits = $bb_cfg['server_name']; # if empty, HTTP_HOST will be shown

		# CAPTCHA image colors (RGB, 0-255)
		$foreground_color = array(mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
		$background_color = array(mt_rand(200,255), mt_rand(200,255), mt_rand(200,255));

		# JPEG quality of CAPTCHA image (bigger is better quality, but larger file size)
		$jpeg_quality = 90;

		$alphabet_length=strlen($alphabet);

		do{
			// generating random keystring
			while(true){
				$this->keystring='';
				for($i=0;$i<$length;$i++){
					$this->keystring.=$allowed_symbols[mt_rand(0,strlen($allowed_symbols)-1)];
				}
				if(!preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $this->keystring)) break;
			}

			$font_file = $fontsdir . $fonts[mt_rand(0, count($fonts)-1)];
			$font=imagecreatefrompng($font_file);
			imagealphablending($font, true);
			$fontfile_width=imagesx($font);
			$fontfile_height=imagesy($font)-1;
			$font_metrics=array();
			$symbol=0;
			$reading_symbol=false;

			// loading font
			for($i=0;$i<$fontfile_width && $symbol<$alphabet_length;$i++){
				$transparent = (imagecolorat($font, $i, 0) >> 24) == 127;

				if(!$reading_symbol && !$transparent){
					$font_metrics[$alphabet[$symbol]]=array('start'=>$i);
					$reading_symbol=true;
					continue;
				}

				if($reading_symbol && $transparent){
					$font_metrics[$alphabet[$symbol]]['end']=$i;
					$reading_symbol=false;
					$symbol++;
					continue;
				}
			}

			$img=imagecreatetruecolor($width, $height);
			imagealphablending($img, true);
			$white=imagecolorallocate($img, 255, 255, 255);
			$black=imagecolorallocate($img, 0, 0, 0);

			imagefilledrectangle($img, 0, 0, $width-1, $height-1, $white);

			// draw text
			$x=1;
			for($i=0;$i<$length;$i++){
				$m=$font_metrics[$this->keystring[$i]];

				$y=mt_rand(-$fluctuation_amplitude, $fluctuation_amplitude)+($height-$fontfile_height)/2+2;

				if($no_spaces){
					$shift=0;
					if($i>0){
						$shift=10000;
						for($sy=7;$sy<$fontfile_height-20;$sy+=1){
							for($sx=$m['start']-1;$sx<$m['end'];$sx+=1){
										$rgb=imagecolorat($font, $sx, $sy);
										$opacity=$rgb>>24;
								if($opacity<127){
									$left=$sx-$m['start']+$x;
									$py=$sy+$y;
									if($py>$height) break;
									for($px=min($left,$width-1);$px>$left-12 && $px>=0;$px-=1){
												$color=imagecolorat($img, $px, $py) & 0xff;
										if($color+$opacity<190){
											if($shift>$left-$px){
												$shift=$left-$px;
											}
											break;
										}
									}
									break;
								}
							}
						}
						if($shift==10000){
							$shift=mt_rand(4,6);
						}

					}
				}else{
					$shift=1;
				}
				imagecopy($img, $font, $x-$shift, $y, $m['start'], 1, $m['end']-$m['start'], $fontfile_height);
				$x+=$m['end']-$m['start']-$shift;
			}
		}while($x>=$width-10); // while not fit in canvas

		$center=$x/2;

		// credits
		$img2=imagecreatetruecolor($width, $height+($show_credits?12:0));
		$foreground=imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2]);
		$background=imagecolorallocate($img2, $background_color[0], $background_color[1], $background_color[2]);
		imagefilledrectangle($img2, 0, 0, $width-1, $height-1, $background);
		imagefilledrectangle($img2, 0, $height, $width-1, $height+12, $foreground);
		$credits=empty($credits)?$bb_cfg['server_name']:$credits;
		imagestring($img2, 2, $width/2-imagefontwidth(2)*strlen($credits)/2, $height-2, $credits, $background);

		// periods
		$rand1=mt_rand(750000,1200000)/10000000;
		$rand2=mt_rand(750000,1200000)/10000000;
		$rand3=mt_rand(750000,1200000)/10000000;
		$rand4=mt_rand(750000,1200000)/10000000;
		// phases
		$rand5=mt_rand(0,31415926)/10000000;
		$rand6=mt_rand(0,31415926)/10000000;
		$rand7=mt_rand(0,31415926)/10000000;
		$rand8=mt_rand(0,31415926)/10000000;
		// amplitudes
		$rand9=mt_rand(330,420)/110;
		$rand10=mt_rand(330,450)/110;

		//wave distortion

		for($x=0;$x<$width;$x++){
			for($y=0;$y<$height;$y++){
				$sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$width/2+$center+1;
				$sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;

				if($sx<0 || $sy<0 || $sx>=$width-1 || $sy>=$height-1){
					continue;
				}else{
					$color=imagecolorat($img, $sx, $sy) & 0xFF;
					$color_x=imagecolorat($img, $sx+1, $sy) & 0xFF;
					$color_y=imagecolorat($img, $sx, $sy+1) & 0xFF;
					$color_xy=imagecolorat($img, $sx+1, $sy+1) & 0xFF;
				}

				if($color==255 && $color_x==255 && $color_y==255 && $color_xy==255){
					continue;
				}else if($color==0 && $color_x==0 && $color_y==0 && $color_xy==0){
					$newred=$foreground_color[0];
					$newgreen=$foreground_color[1];
					$newblue=$foreground_color[2];
				}else{
					$frsx=$sx-floor($sx);
					$frsy=$sy-floor($sy);
					$frsx1=1-$frsx;
					$frsy1=1-$frsy;

					$newcolor=(
						$color*$frsx1*$frsy1+
						$color_x*$frsx*$frsy1+
						$color_y*$frsx1*$frsy+
						$color_xy*$frsx*$frsy);

					if($newcolor>255) $newcolor=255;
					$newcolor=$newcolor/255;
					$newcolor0=1-$newcolor;

					$newred=$newcolor0*$foreground_color[0]+$newcolor*$background_color[0];
					$newgreen=$newcolor0*$foreground_color[1]+$newcolor*$background_color[1];
					$newblue=$newcolor0*$foreground_color[2]+$newcolor*$background_color[2];
				}

				imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newred, $newgreen, $newblue));
			}
		}

		$img_path = $this->get_img_path($cap_id);
		file_write('', $img_path, null, true, true);

		imagejpeg($img2, $img_path, $jpeg_quality);

		imagedestroy($img2);

		return $this->keystring;
	}
}