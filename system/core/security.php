<?php if(!defined('BASEPATH')){ exit('No direct script access allowed'); }
class _security{

	protected $_xss_hash = '';
	protected $_never_allowed_str = array(
		'document.cookie' => '[removed]',
		'document.write' => '[removed]',
		'.parentNode' => '[removed]',
		'.innerHTML' => '[removed]',
		'window.location' => '[removed]',
		'-moz-binding' => '[removed]',
		'<!--' => '&lt;!--',
		'-->' => '--&gt;',
		'<![CDATA[' => '&lt;![CDATA[',
		'<comment>' => '&lt;comment&gt;'
	);

	protected $_never_allowed_regex = array(
		'javascript\s*:',
		'expression\s*(\(|&\#40;)', // CSS and IE
		'vbscript\s*:', // IE, surprise!
		'Redirect\s+302',
		"([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
	);

	public function __construct(){

	}

	public function __destruct(){

	}

	public function _secured($input_value = NULL, $data_type = NULL){
		if(!isset($data_type) || !isset($input_value)){
			return NULL;
		}

		if($data_type === 'string'){
			return $this->_simple_clean($input_value);
		}elseif($data_type === 'xss'){
			return $this->_xss_clean($input_value);
		}elseif($data_type === 'int'){
			return (int) $input_value;
		}elseif($data_type === 'float'){
			return (float) $input_value;
		}elseif($data_type === 'bool'){
			if($input_value === TRUE || strtolower($input_value) === 'yes' || $input_value === '1' || $input_value === 'true' || $input_value === 'TRUE'){
				return TRUE;
			}elseif($input_value === FALSE || strtolower($input_value) === 'no' || $input_value === '0' || $input_value === 'false' || $input_value === 'FALSE'){
				return FALSE;
			}
		}elseif($data_type === 'json'){
			return json_decode($input_value);
		}else{
			trigger_error('DXOLUI', E_USER_NOTICE);
			return NULL;
		}
	}


	public function _simple_clean($str){
		return htmlentities(trim($str));
	}

	public function _remove_invisible_characters($str){
		$non_displayables = array();
		$non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';   // 00-08, 11, 12, 14-31, 127

		$str = preg_replace($non_displayables, '', $str, -1);

		return $str;
	}

	public function _xss_clean($str, $is_image = FALSE){
		if (is_array($str)){
			while (list($key) = each($str)){
				$str[$key] = $this->_xss_clean($str[$key]);
			}

			return $str;
		}

		$str = $this->_remove_invisible_characters($str);

		$str = $this->validate_entities($str);

		$str = rawurldecode($str);

		$str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", array($this, 'convert_attribute'), $str);

		$str = preg_replace_callback("/<\w+.*?(?=>|<|$)/si", array($this, 'decode_entity'), $str);

		$str = $this->_remove_invisible_characters($str);

		if (strpos($str, "\t") !== FALSE){
			$str = str_replace("\t", ' ', $str);
		}

		$converted_string = $str;

		$str = $this->do_never_allowed($str);

		if ($is_image === TRUE){
			$str = preg_replace('/<\?(php)/i', "&lt;?\\1", $str);
		}
		else
		{
			$str = str_replace(array('<?', '?'.'>'),  array('&lt;?', '?&gt;'), $str);
		}

		$words = array(
			'javascript', 'expression', 'vbscript', 'script', 'base64',
			'applet', 'alert', 'document', 'write', 'cookie', 'window'
		);

		foreach($words as $word){
			$temp = '';

			for ($i = 0, $wordlen = strlen($word); $i < $wordlen; $i++){
				$temp .= substr($word, $i, 1)."\s*";
			}

			$str = preg_replace_callback('#('.substr($temp, 0, -3).')(\W)#is', array($this, 'compact_exploded_words'), $str);
		}

		do{
			$original = $str;

			if (preg_match("/<a/i", $str)){
				$str = preg_replace_callback("#<a\s+([^>]*?)(>|$)#si", array($this, 'js_link_removal'), $str);
			}

			if (preg_match("/<img/i", $str)){
				$str = preg_replace_callback("#<img\s+([^>]*?)(\s?/?>|$)#si", array($this, 'js_img_removal'), $str);
			}

			if (preg_match("/script/i", $str) OR preg_match("/xss/i", $str)){
				$str = preg_replace("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $str);
			}
		}while($original != $str);

		unset($original);

		$str = $this->remove_evil_attributes($str, $is_image);

		$naughty = 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
		$str = preg_replace_callback('#<(/*\s*)('.$naughty.')([^><]*)([><]*)#is', array($this, 'sanitize_naughty_html'), $str);

		$str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);

		$str = $this->do_never_allowed($str);

		if ($is_image === TRUE){
			return ($str == $converted_string) ? TRUE: FALSE;
		}

		return $str;
	}

	public function _xss_hash(){
		if ($this->_xss_hashed == ''){
			mt_srand();
			$this->_xss_hashed = md5(time() + mt_rand(0, 1999999999));
		}

		return $this->_xss_hashed;
	}

	public function _entity_decode($str, $charset='UTF-8'){
		if (stristr($str, '&') === FALSE){
			return $str;
		}

		$str = html__entity_decode($str, ENT_COMPAT, $charset);
		$str = preg_replace('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);
		return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
	}

	public function _sanitize_filename($str, $relative_path = FALSE){
		$bad = array(
			"../",
			"<!--",
			"-->",
			"<",
			">",
			"'",
			'"',
			'&',
			'$',
			'#',
			'{',
			'}',
			'[',
			']',
			'=',
			';',
			'?',
			"%20",
			"%22",
			"%3c",		// <
			"%253c",	// <
			"%3e",		// >
			"%0e",		// >
			"%28",		// (
			"%29",		// )
			"%2528",	// (
			"%26",		// &
			"%24",		// $
			"%3f",		// ?
			"%3b",		// ;
			"%3d"		// =
		);

		if ( ! $relative_path){
			$bad[] = './';
			$bad[] = '/';
		}

		$str = _remove_invisible_characters($str, FALSE);
		return stripslashes(str_replace($bad, '', $str));
	}

	protected function compact_exploded_words($matches){
		return preg_replace('/\s+/s', '', $matches[1]).$matches[2];
	}

	protected function remove_evil_attributes($str, $is_image){
		$evil_attributes = array('on\w*', 'style', 'xmlns', 'formaction');

		if ($is_image === TRUE){
			unset($evil_attributes[array_search('xmlns', $evil_attributes)]);
		}

		do {
			$count = 0;
			$attribs = array();

			preg_match_all('/('.implode('|', $evil_attributes).')\s*=\s*([^\s>]*)/is', $str, $matches, PREG_SET_ORDER);

			foreach ($matches as $attr){

				$attribs[] = preg_quote($attr[0], '/');
			}

			preg_match_all("/(".implode('|', $evil_attributes).")\s*=\s*(\042|\047)([^\\2]*?)(\\2)/is",  $str, $matches, PREG_SET_ORDER);

			foreach ($matches as $attr){
				$attribs[] = preg_quote($attr[0], '/');
			}

			if (count($attribs) > 0){
				$str = preg_replace("/<(\/?[^><]+?)([^A-Za-z<>\-])(.*?)(".implode('|', $attribs).")(.*?)([\s><])([><]*)/i", '<$1 $3$5$6$7', $str, -1, $count);
			}

		} while ($count);

		return $str;
	}

	protected function sanitize_naughty_html($matches){
		$str = '&lt;'.$matches[1].$matches[2].$matches[3];

		$str .= str_replace(array('>', '<'), array('&gt;', '&lt;'),
							$matches[4]);

		return $str;
	}

	protected function js_link_removal($match){
		return str_replace(
			$match[1],
			preg_replace(
				'#href=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si',
				'',
				$this->filter_attributes(str_replace(array('<', '>'), '', $match[1]))
			),
			$match[0]
		);
	}

	protected function js_img_removal($match){
		return str_replace(
			$match[1],
			preg_replace(
				'#src=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si',
				'',
				$this->filter_attributes(str_replace(array('<', '>'), '', $match[1]))
			),
			$match[0]
		);
	}

	protected function convert_attribute($match){
		return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
	}

	protected function filter_attributes($str){
		$out = '';

		if(preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches)){
			foreach($matches[0] as $match){
				$out .= preg_replace("#/\*.*?\*/#s", '', $match);
			}
		}

		return $out;
	}

	protected function decode_entity($match){
		return $this->_entity_decode($match[0]);
	}

	protected function validate_entities($str){

		$str = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-]+)|i', $this->_xss_hash()."\\1=\\2", $str);

		$str = preg_replace('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', "\\1;\\2", $str);

		$str = preg_replace('#(&\#x?)([0-9A-F]+);?#i',"\\1\\2;",$str);

		$str = str_replace($this->_xss_hash(), '&', $str);

		return $str;
	}

	protected function do_never_allowed($str){
		$str = str_replace(array_keys($this->_never_allowed_str), $this->_never_allowed_str, $str);

		foreach($this->_never_allowed_regex as $regex){
			$str = preg_replace('#'.$regex.'#is', '[removed]', $str);
		}

		return $str;
	}

	protected function csrf_set_hash(){
		if($this->_csrf_hash == ''){
			if(isset($_COOKIE[$this->_csrf_cookie_name]) &&
				preg_match('#^[0-9a-f]{32}$#iS', $_COOKIE[$this->_csrf_cookie_name]) === 1){
				return $this->_csrf_hash = $_COOKIE[$this->_csrf_cookie_name];
			}

			return $this->_csrf_hash = md5(uniqid(rand(), TRUE));
		}

		return $this->_csrf_hash;
	}

}
?>
