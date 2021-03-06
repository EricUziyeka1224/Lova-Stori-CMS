<?php

	defined('BASEPATH') OR exit('No direct script access allowed');


	function printr($string)
	{	
		echo "<pre>".print_r($string, true)."</pre>";
	}




	/**
	* Check if a session exists
	*-------------------------------------------------
	* @param $session_name : a session name
	* @return bool : TRUE on success, FALSE on failure
	*/
	function session_exists(string $session_name): bool
	{
		return isset($_SESSION[$session_name]);
	}




	


	/**
	* Check if a couple of sessions exists either concomitantly
	* or individually
	*-----------------------------------------------------------------
	* @param $sessions_names : an array of sessions names to check for
	* @param $or : whether to check for a simultaneous existance (TRUE)
	* or an individual existance (FALSE)
	*        Default value : FALSE
	* @return bool : TRUE on success, FALSE on failure
	*/
	function sessions_exist(array $sessions_names, $or = FALSE): bool
	{
		$sessions_set  = 	array_filter($sessions_names, function($session_name){
								return isset($_SESSION[$session_name]);
						  	});

		return  ($or) 
				? count($sessions_set)
				: (count($sessions_names) === count($sessions_set));
	}











	/**
	* Generate a CSRF token using CI security class
	*-----------------------------------------------
	* @return string : token
	*/
	function get_csrf_token(): string
	{
		$CI =& get_instance();

		return $CI->security->get_csrf_hash();
	}




	/**
	* Encrypt a string based on the encryption 
	* key found in config.php file
	*------------------------------------------
	* @param $str : the string to encrypt
	* @return string : the encrypted string
	*/
	function encrypt($str)
	{
		$CI =& get_instance();

		return $CI->encryption->encrypt($str);
	}






	/**
	* Decrypt an encypted string based on the encryption 
	* key found in config.php file
	*---------------------------------------------------
	* @param $cipher_str : the encrypted string
	* @return string : the decrypted string
	*/
	function decrypt($cipher_str)
	{
		$CI =& get_instance();

		return $CI->encryption->decrypt($cipher_str);
	}






	/**
	* Format iso_date format to '{time} ago'
	*---------------------------------------------------
	* @param $iso_date
	* @return string: 'n(seconds|minutes|hours|...) ago'
	*/
	function get_time_ago_in_words($iso_date)
	{
		$date    = new DateTime($iso_date);
		$formats = ['y'=>'tahun', 'm'=>'bulan', 'd'=>'hari', 'h'=>'jam', 'i'=>'menit', 's'=>'detik'];

		foreach($formats as $abv_format => $full_format)
		{	
			if($diff = (new DateTime(date("Y-m-d H:i:s")))->diff($date)->format("%$abv_format"))
			{
				$diff = "$diff ".($diff === "1" ? $full_format : "{$full_format}")." yg lalu";
				break;
			}
		}

		return (@$diff === '0') ? '1 detik yg lalu' : @$diff;
	}






	/**
	* Check if $array has the required $keys (as keys)
	*--------------------------------------------------------------------
	* @param $keys : an array of required keys
	* @param $array : the array where the required keys should be checked
	* @return bool (TRUE on success & FALSE on failure)
	*/
	function array_keys_exist(array $keys, array $array)
	{
		return empty(array_diff($keys, array_keys($array)));
	}




	function get_token($length, $url_encode = TRUE)
	{
		$CI =& get_instance();

		$token = base64_encode($CI->security->get_random_bytes($length));

		return $url_encode ? urlencode($token) : $token;
	}


	/*function get_config_item($item_name, $index = NULL)
	{
		$CI =& get_instance();

		$CI->load->config($item_name);

		return $CI->config->item($item_name)[$index]
			   ?? $CI->config->item($item_name);
	}*/



	/**
	* Compile scss content to css content
	* --------------------------------------------------------------
	* the configuration can be either done via a config file withing 
	* 'Config' directory or passed directly as paramater
	* @return NULL;
	*/
	function compileScss()
	{		
		$CI = get_instance();

		$CI->load->library('Scss_Compiler');
		$CI->load->config('scss');

		require(APPPATH.'libraries/leafo/scss.inc.php');

		$Scss_Compiler = new Scss_Compiler();

		if(!is_null($CI->config->item('scss')))
		{
			$scss_configs = array_filter($CI->config->item('scss'));

			foreach($scss_configs as $scss_config)
			{
				$Scss_Compiler->init(...array_values($scss_config))
					  		  ->compileScss();
			}
		}

        //print_r($Scss_Compiler->getError());
	}



	function get_html_pagination($pagination, $inverted = false)
	{
		return $pagination 
			   ? '<div class="ui pagination menu '. ($inverted ? 'inverted' : '') .'">'. $pagination .'</div>' 
			   : "&nbsp;";
	}


	function session_get($dotted_keys, $default_value = null)
	{
		$dot = new \Adbar\Dot($_SESSION ?? []);

		return $dot->get($dotted_keys, $default_value);
	}

	
	function get_form_response($flash_session_name)
	{
		if(!session_exists($flash_session_name) || 
		   !array_keys_exist(['type', 'response'], $_SESSION[$flash_session_name]))
			return false;

		$output = '';
		$type   = $_SESSION[$flash_session_name]['type'];

		if(is_array($_SESSION[$flash_session_name]['response']))
		{
			$mb = 'style="margin-bottom: 1rem !important;"';

			foreach($_SESSION[$flash_session_name]['response'] as $message)
			{
				$output .= 	'<div class="ui fluid '. $type .' small message" '. $mb .'>
								<i class="close icon"></i>
								'. $message .'
							</div>';
			}
		}
		else
		{
			$output = 	'<div class="ui fluid '. $type .' small message">
							<i class="close icon"></i>
							'. $_SESSION[$flash_session_name]['response'] .'
						</div>';
		}

		return $output;
	}


	function getStyle()
	{
		$CI =& get_instance();

		return get_cookie('style') 
					?? $CI->settings['site_style'] 
					?? 'light';
	}

	function styleIsDark()
	{
		return getStyle() !== 'light';
	}


	function user_role()
	{
		return $_SESSION['user_role'] ?? NULL;
	}


	function has_admin_access()
	{
		return is_logged_in()
			   && preg_match('/^(main|administrator|moderator)$/i', user_role());
	}


	
	function can_create_posts($user_role)
	{
		if($user_role === 'member')
			return false;
		
		$CI =& get_instance();

		$permissions = get_permissions($CI);

		return $permissions->$user_role->posts->add ?? null;
	}



	function is_logged_in()
	{
		return sessions_exist(['user_id', 'user_name', 
							   'user_email', 'user_role']);
	}
		

	function is_admin()
	{
		return is_logged_in()
			   && (user_role() === 'administrator');
	}


	function is_moderator()
	{
		return is_logged_in()
			   && (user_role() === 'moderator');
	}


	function is_author()
	{
		return is_logged_in()
			   && (user_role() === 'author');
	}

	function is_member()
	{
		return is_logged_in()
			   && (user_role() === 'member');
	}


	function is_main()
	{
		return is_logged_in()
			   && (user_role() === 'main');
	}


	function match($string, $pattern, $default = NULL)
	{
		preg_match("/^(?P<match>$pattern)$/i", $string, $matches);

		return $matches['match'] ?? $default;
	}


	function get_auto_increment($table_name)
	{
		$CI =& get_instance();

		return $CI->db->query("SHOW TABLE STATUS LIKE ?", [$table_name])->row()->Auto_increment ?? null;
	}


	function check_permission($closure = NULL)
	{
		if(!has_admin_access()) show_404();

		if(user_role() === 'main')
			return TRUE;

		# ----------------------------------- #

		$CI =& get_instance();

		$permissions = get_permissions($CI);

		if(!$permissions) show_404();

		$user_role  = user_role();
		$controller = $CI->router->class;
		$method 	= $CI->router->method;
		$response   = @$permissions->$user_role->$controller->$method ?? FALSE;

		if(is_callable($closure))
			return call_user_func($closure, $response);

		return $response;
	}




	function get_permissions($CI_instance)
	{		
		return json_decode($CI_instance->settings['site_permissions']);
	}




	function has_permission($controller, $method = 'index')
	{
		if(user_role() === 'main')
			return TRUE;

		$base = @$_ENV['perms']->{$_SESSION['user_role']}->$controller;

		if(is_array($method))
		{
			list($condition, $methods) = $method;

			$methods_set  = array_filter($methods, function($method) use($base){
								settype($base, 'array');
								return isset($base[$method]) && $base[$method];
							});

			return  ($condition === 'or')
					? count($methods_set)
					: (count((array)$methods) === count($methods_set));
		}

		return @$_ENV['perms']->{$_SESSION['user_role']}->$controller->$method
			   ?? FALSE;
	}




	function round_int(int $int)
	{
		if($int >= 1000)
			$int = round(($int / 1000), 1).'k';

		return $int;
	}


    function init_increase_val(&$object, $property)
	{
		if(isset($object->$property))
			$object->$property += 1;
		else
			$object->$property = 1;
	}
	
	
	function get_ad_units($type = NULL)
	{
		$CI =& get_instance();
		
		$ad_units = '';

		if($type === 'rectangle')
		{
			if($CI->settings['site_728x90_unit_ad'])
			{
				$ad_units .= '<div class="ad-units ad-728x90 my-4 center aligned">'.base64_decode($CI->settings['site_728x90_unit_ad']).'</div>';
			}

			if($CI->settings['site_468x60_unit_ad'])
			{
				$ad_units .= '<div class="ad-units ad-468x60 my-4 center aligned">'.base64_decode($CI->settings['site_468x60_unit_ad']).'</div>';
			}

			if($CI->settings['site_320x100_unit_ad'])
			{
				$ad_units .= '<div class="ad-units ad-320x100 my-4 center aligned">'.base64_decode($CI->settings['site_320x100_unit_ad']).'</div>';
			}
		}
		elseif($type === 'square')
		{
			if($CI->settings['site_250x250_unit_ad'])
			{
				$ad_units .= '<div class="ad-units ad-250x250 my-4 center aligned">'.base64_decode($CI->settings['site_250x250_unit_ad']).'</div>';
			}
		}
		elseif($type === 'feed')
		{
			if($CI->settings['site_in_feed_ad'])
			{
				$ad_units .= '<div class="ui fluid card"><div class="ad-units ad-in-feed center aligned">'.base64_decode($CI->settings['site_in_feed_ad']).'</div></div>';
			}
		}
		elseif($type === 'article')
		{
			if($CI->settings['site_in_article_ad'])
			{
				$ad_units .= '<div class="ad-units ad-in-article center aligned">'.base64_decode($CI->settings['site_in_article_ad']).'</div>';
			}
		}
		elseif($type === 'link')
		{
			if($CI->settings['site_link_ad'])
			{
				$ad_units .= '<div class="ad-units ad-link center aligned">'.base64_decode($CI->settings['site_link_ad']).'</div>';
			}
		}

		return $ad_units;
	}


	function url_title($str, $separator = '-', $lowercase = FALSE)
	{
		$converTable = [
	    '&amp;' => 'and',   '@' => 'at',    '??' => 'c', '??' => 'r', '??' => 'a',
	    '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'ae','??' => 'c',
	    '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'i', '??' => 'i', '??' => 'i',
	    '??' => 'i', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o',
	    '??' => 'o', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'y',
	    '??' => 'ss','??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a',
	    '??' => 'ae','??' => 'c', '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e',
	    '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'o', '??' => 'o',
	    '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'u', '??' => 'u',
	    '??' => 'u', '??' => 'u', '??' => 'y', '??' => 'p', '??' => 'y', '??' => 'a',
	    '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'c',
	    '??' => 'c', '??' => 'c', '??' => 'c', '??' => 'c', '??' => 'c', '??' => 'c',
	    '??' => 'c', '??' => 'd', '??' => 'd', '??' => 'd', '??' => 'd', '??' => 'e',
	    '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'e',
	    '??' => 'e', '??' => 'e', '??' => 'e', '??' => 'g', '??' => 'g', '??' => 'g',
	    '??' => 'g', '??' => 'g', '??' => 'g', '??' => 'g', '??' => 'g', '??' => 'h',
	    '??' => 'h', '??' => 'h', '??' => 'h', '??' => 'i', '??' => 'i', '??' => 'i',
	    '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i',
	    '??' => 'i', '??' => 'ij','??' => 'ij','??' => 'j', '??' => 'j', '??' => 'k',
	    '??' => 'k', '??' => 'k', '??' => 'l', '??' => 'l', '??' => 'l', '??' => 'l',
	    '??' => 'l', '??' => 'l', '??' => 'l', '??' => 'l', '??' => 'l', '??' => 'l',
	    '??' => 'n', '??' => 'n', '??' => 'n', '??' => 'n', '??' => 'n', '??' => 'n',
	    '??' => 'n', '??' => 'n', '??' => 'n', '??' => 'o', '??' => 'o', '??' => 'o',
	    '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'oe','??' => 'oe','??' => 'r',
	    '??' => 'r', '??' => 'r', '??' => 'r', '??' => 'r', '??' => 'r', '??' => 's',
	    '??' => 's', '??' => 's', '??' => 's', '??' => 's', '??' => 's', '??' => 's',
	    '??' => 's', '??' => 't', '??' => 't', '??' => 't', '??' => 't', '??' => 't',
	    '??' => 't', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u',
	    '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u',
	    '??' => 'u', '??' => 'w', '??' => 'w', '??' => 'y', '??' => 'y', '??' => 'y',
	    '??' => 'z', '??' => 'z', '??' => 'z', '??' => 'z', '??' => 'z', '??' => 'z',
	    '??' => 'z', '??' => 'e', '??' => 'f', '??' => 'o', '??' => 'o', '??' => 'u',
	    '??' => 'u', '??' => 'a', '??' => 'a', '??' => 'i', '??' => 'i', '??' => 'o',
	    '??' => 'o', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u',
	    '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'a',
	    '??' => 'a', '??' => 'ae','??' => 'ae','??' => 'o', '??' => 'o', '??' => 'e',
	    '??' => 'jo','??' => 'e', '??' => 'i', '??' => 'i', '??' => 'a', '??' => 'b',
	    '??' => 'v', '??' => 'g', '??' => 'd', '??' => 'e', '??' => 'zh','??' => 'z',
	    '??' => 'i', '??' => 'j', '??' => 'k', '??' => 'l', '??' => 'm', '??' => 'n',
	    '??' => 'o', '??' => 'p', '??' => 'r', '??' => 's', '??' => 't', '??' => 'u',
	    '??' => 'f', '??' => 'h', '??' => 'c', '??' => 'ch','??' => 'sh','??' => 'sch',
	    '??' => '-', '??' => 'y', '??' => '-', '??' => 'je','??' => 'ju','??' => 'ja',
	    '??' => 'a', '??' => 'b', '??' => 'v', '??' => 'g', '??' => 'd', '??' => 'e',
	    '??' => 'zh','??' => 'z', '??' => 'i', '??' => 'j', '??' => 'k', '??' => 'l',
	    '??' => 'm', '??' => 'n', '??' => 'o', '??' => 'p', '??' => 'r', '??' => 's',
	    '??' => 't', '??' => 'u', '??' => 'f', '??' => 'h', '??' => 'c', '??' => 'ch',
	    '??' => 'sh','??' => 'sch','??' => '-','??' => 'y', '??' => '-', '??' => 'je',
	    '??' => 'ju','??' => 'ja','??' => 'jo','??' => 'e', '??' => 'i', '??' => 'i',
	    '??' => 'g', '??' => 'g', '??' => 'a', '??' => 'b', '??' => 'g', '??' => 'd',
	    '??' => 'h', '??' => 'v', '??' => 'z', '??' => 'h', '??' => 't', '??' => 'i',
	    '??' => 'k', '??' => 'k', '??' => 'l', '??' => 'm', '??' => 'm', '??' => 'n',
	    '??' => 'n', '??' => 's', '??' => 'e', '??' => 'p', '??' => 'p', '??' => 'C',
	    '??' => 'c', '??' => 'q', '??' => 'r', '??' => 'w', '??' => 't', '???' => 'tm',
		];

		$str = strtr($str, $converTable);
		
		if ($separator === 'dash')
		{
			$separator = '-';
		}
		elseif ($separator === 'underscore')
		{
			$separator = '_';
		}

		$q_separator = preg_quote($separator, '#');

		$trans = array(
			'&.+?;'			=> '',
			'[^\w\d _-]'		=> '',
			'\s+'			=> $separator,
			'('.$q_separator.')+'	=> $separator
		);

		$str = strip_tags($str);
		foreach ($trans as $key => $val)
		{
			$str = preg_replace('#'.$key.'#i'.(UTF8_ENABLED ? 'u' : ''), $val, $str);
		}

		if ($lowercase === TRUE)
		{
			$str = strtolower($str);
		}

		return trim(trim($str, $separator));
	}


	function site_in_maintenance()
	{
		return file_exists(FCPATH.'.maintenance');
	}


	function maintenance_access_allowed()
	{
		if(site_in_maintenance())
		{
			if($allowed_ip_addresses = json_decode(file_get_contents(FCPATH.'.maintenance'), TRUE))
				return in_array($_SERVER['REMOTE_ADDR'], $allowed_ip_addresses);

			return false;
		}

		return true;
	}


	function maintenance_allowed_ips()
	{
		if(site_in_maintenance())
		{
			if($allowed_ip_addresses = json_decode(file_get_contents(FCPATH.'.maintenance'), TRUE))
				return $allowed_ip_addresses;
		}

		return [];
	}


	function get_verification_code()
	{
		$code = mt_rand(111111, 999999);

		$CI = get_instance();

		$CI->load->library('session');

		$CI->session->set_tempdata('contributor_verification_code', $code, 3600);

		return $code;
	}


	function number_shortener($number)
	{
		if($number > 1000)
		{
			return number_format($number / 1000, 2).'K';
		}

		return $number;
	}
	
	
    function watermark_img($img_name = null)
	{
		if(!$img_name) return;

		$CI =& get_instance();

		$CI->load->library('image_lib');
						
		$config['image_library'] = 'gd2';
		$config['source_image'] = "./uploads/images/{$img_name}";
		$config['wm_overlay_path'] = './assets/images/watermark.png';
		$config['wm_type'] = 'overlay';
		$config['padding'] = '20';
		$config['wm_opacity'] = '100';
		$config['wm_vrt_alignment'] = 'bottom';
		$config['wm_hor_alignment'] = 'right';
		$config['wm_hor_offset'] = '20';
		$config['wm_vrt_offset'] = '20';

		$CI->image_lib->initialize($config);

		$CI->image_lib->watermark();
	}



	function ad_banner_img($img = null, $default = 'ad-placeholder.webp')
	{
		if(is_null($img))
		{	
			return base_url("assets/images/{$default}?v=".time());
		}

		return base_url("uploads/banners/{$img}");
	}


	function ad_banner_link($link = null)
	{
		if(is_null($link))
		{
			if(is_member())
			{
				return base_url('/advertiser');
			}
			else
			{
				return 'javascript:void(0)';
			}
		}

		return $link;
	}


	function ad_banner_class($link = null)
	{
		if(is_null($link))
		{
			if(!is_logged_in())
			{
				return 'sign-in-form-toggler';
			}
		}
	}