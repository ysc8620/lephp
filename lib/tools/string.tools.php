<?php 
class tools_string{
	
	/**
	 * 升级版的trim
	 * 该方法去掉字符串中的所有“多余空白”字符，包括全角空格及assic码空格以及换行和制表符(\n\t)
	 * @param str $str
	 */
	static public function trim($str){
		
		//$search = array ("'(&(nbsp|#160);)+'i","'(\r\n)+'","'\s+'i");
		//$replace = array (' ','<br/>',' ');
		$search = array ("'(\r\n)+'","'\s+'i");
		$replace = array ('<br/>',' ');
		return trim(preg_replace($search, $replace, $str));
	}
	
	
	//去除所有html标签及双引号
	static function htmlspecialchars_uni($text, $entities = true){ 
		return str_replace(
			// replace special html characters
			array('<', '>', '"'),
			array('&lt;', '&gt;', '&quot;'),
			preg_replace(
				// translates all non-unicode entities
				'/&(?!' . ($entities ? '#[0-9]+' : '(#[0-9]+|[a-z]+)') . ';)/si',
				'&amp;',
				$text
			)
		);
	}
	
	static function clear_tag($tag){
		
	}
	
	
	static function is_utf8($str){
		if (preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/",$str) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/",$str) == true || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/",$str) == true){
			return true;
		}
		return false;
	}
}
?>