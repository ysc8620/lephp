<?php
define('F_NOCLEAN',     0);  // no change
define('F_BOOL',     	1);  // force boolean
define('F_INT',      	2);  // force integer
define('F_UINT',     	3);  // force unsigned integer
define('F_NUM',      	4);  // force number
define('F_UNUM',     	5);  // force unsigned number
define('F_UNIXTIME', 	6);  // force unix datestamp (unsigned integer)
define('F_STR',      	7);  // 转义所有HTML，并将\r\n转换为<br/>
define('F_NOHTML',   	8);  // 转义所有HTML
define('F_SAFE_HTML',   9);  // 将内容处理未安全的HTML
define('F_ARRAY',   	10); // force array
//define('F_FILE',    	11); // force file
define('F_EMAIL',		12); // force email
define('F_MOBILE',		13); // force mobile
define('F_FORMAT',      14); // 指定格式
define('F_DATE'  ,      15); // 日期
define('F_TELEPHONE',	16); // force telephone

define('F_ARRAY_BOOL',     101);
define('F_ARRAY_INT',      102);
define('F_ARRAY_UINT',     103);
define('F_ARRAY_NUM',      104);
define('F_ARRAY_UNUM',     105);
define('F_ARRAY_UNIXTIME', 106);
define('F_ARRAY_STR',      107);
define('F_ARRAY_NOHTML',   108);
define('F_ARRAY_ARRAY',    110);
define('F_ARRAY_EMAIL',    112);
define('F_ARRAY_MOBILE',   113);



/**
 * 外部数据过滤工具
 * 该工具只过滤并返回结果信息，不做校验用
 * @author aloner
 */
class tools_filter{

	/**
	 * 已校验数据缓存，避免重复操作
	 */
	static $gpc;



	/**
	 * 初始化过滤器
	 */
	static function init(){
		self::$gpc['p'] = $_POST;
		self::$gpc['g'] = $_GET;
		self::$gpc['r'] = $_REQUEST;
		self::$gpc['c'] = $_COOKIE;
	}

	/**
	 * $_GET数据校验
	 * @param $variable 变量名
	 * @param $vartype 数据类型
	 * @return mixed
	 */
	static function g($variable,$vartype){
		return self::gpc('g',$variable, $vartype);
	}

	/**
	 * $_POST数据校验
	 * @param $variable 变量名
	 * @param $vartype 数据类型
	 * @return mixed
	 */
	static function p($variable,$vartype){
		return self::gpc('p',$variable, $vartype);
	}

	/**
	 * $_COOKIE数据校验
	 * @param $variable 变量名
	 * @param $vartype 数据类型
	 * @return mixed
	 */
	static function c($variable,$vartype){
		return self::gpc('c',$variable, $vartype);
	}

	/**
	 * 校验 post/get信息，优先post
	 * @param $variable 变量名
	 * @param $vartype 数据类型
	 * @return mixed
	 */
	static function r($variable,$vartype,$rule=null){
		return self::gpc('r',$variable, $vartype,$rule);
	}

	static function magic_quotes_gpc($data)
    {
        if(get_magic_quotes_gpc())
        {
            $data = stripslashes($data); //将字符串进行处理
        }
        return $data;
    }

	static function gpc($input_type,$variable,$vartype,$rule=null){
		if (!isset(self::$gpc["$input_type"]["$variable"])){
			switch ($input_type){
				case "g":
					$data = self::magic_quotes_gpc($_GET[$variable]);
					break;
				case "p":
                    $data = self::magic_quotes_gpc($_POST[$variable]);
					break;
				case "r":
					$data = self::magic_quotes_gpc($_REQUEST[$variable]);
					break;
				case "c":
					$data = $_COOKIE[$variable];
					break;
			}
			if(!isset($data)){
				$data = null;
			}else{
				switch($vartype){
					case F_NOCLEAN:
						break;
					case F_BOOL:
						$data = self::_bool($data,$rule);
						break;
					case F_INT:					//整数
						$data = self::_int($data,$rule);
						break;
					case F_UINT:				//正整数
						$data = self::_uint($data,$rule);
						break;
					case F_NUM:					//数字
						$data = self::_num($data,$rule);
						break;
					case F_UNUM:				//不小于零的数字
						$data = self::_unum($data,$rule);
						break;
					case F_STR:					//转义所有HTML，并将\r\n转换为<br/>
						$data = self::_str($data,$rule);
						break;
					case F_NOHTML:				//转义所有HTML
						$data = self::_nohtml($data,$rule);
						break;
					case F_SAFE_HTML:			//安全的HTML
						$data = self::_salf_html($data,$rule);
						break;
					case F_ARRAY:
						$data = self::_array($data,$rule);
						break;
					case F_EMAIL:
						$data = self::_email($data,$rule);
						break;
					case F_MOBILE:
						$data = self::_mobile($data,$rule);
						break;
					case F_TELEPHONE:
						$data = self::_telephone($data,$rule);
						break;
					case F_UNIXTIME:
						$data = self::_unixtime($data,$rule);
						break;
					case F_NOTRIM:
						$data = self::_notrim($data,$rule);
						break;
					case F_FORMAT:
					    $data = self::_format($data,$rule);
					    break;
					case F_DATE:
					    $data = self::_date($data,$rule);
					    break;
					case F_ARRAY_BOOL:
						if($data){
							foreach($data as $k=>$r){
								$data[$k] = self::_bool($r,$rule);
							}
						}
						break;
					case F_ARRAY_INT:
						if($data){
							foreach($data as $k=>$r){
								$data[$k] = self::_int($r,$rule);
							}
						}
						break;
					case F_ARRAY_ARRAY:
						if($data){
							foreach($data as $k=>$r){
								$data[$k] = self::_array($r,$rule);
							}
						}
						break;
					case F_ARRAY_EMAIL:
						if($data){
							foreach($data as $k=>$r){
								$data[$k] = self::_email($r,$rule);
							}
						}
						break;
					case F_ARRAY_MOBILE:
						if($data){
							foreach($data as $k=>$r){
								$data[$k] = self::_mobile($r,$rule);
							}
						}
						break;
					case F_ARRAY_NOHTML:
						if($data){
							foreach($data as $k=>$r){
								$data[$k] = self::_nohtml($r,$rule);
							}
						}
						break;
					case F_ARRAY_NUM:
						if($data){
							foreach($data as $k=>$r){
								$data[$k] = self::_num($r,$rule);
							}
						}
						break;
					case F_ARRAY_STR:
						if($data){
							foreach($data as $k=>$r){
								$data[$k] = self::_str($r,$rule);
							}
						}
						break;
					case F_ARRAY_UINT:
						if($data){
							foreach($data as $k=>$r){
								$data[$k] = self::_uint($r,$rule);
							}
						}
						break;
					case F_ARRAY_UNIXTIME:
						if($data){
							foreach($data as $k=>$r){
								$data[$k] = self::_unixtime($r,$rule);
							}
						}
						break;
					case F_ARRAY_UNUM:
						if($data){
							foreach($data as $k=>$r){
								$data[$k] = self::_unum($r,$rule);
							}
						}
						break;
				}
			}
			self::$gpc["$input_type"]["$variable"] = $data;
		}
		return self::$gpc["$input_type"]["$variable"];
	}

	//bool
	static function _bool($data,$rule=null){
		$booltypes = array('1', 'yes', 'y', 'true');
		return in_array(strtolower($data), $booltypes) ? 1 : 0;
	}

	//email
	static function _email($val,$rule=null){
		return filter_var($val,FILTER_SANITIZE_EMAIL);
	}

	//int
	static function _int($val,$rule=null){
		return $val + 0;
	}

	//unit
	static function _uint($val,$rule=null){
		return ($val = intval($val)) < 0 ? 0 : $val;
	}

	//num
	static function _num($val,$rule=null){
		return strval($val) + 0;
	}

	//unum
	static function _unum($val,$rule=null){
		$val = strval($val) + 0;
		return $val > 0 ? $val : 0 ;
	}

	//str
	static function _str($val,$rule=null){
		return strval(tools_String::trim(tools_String::htmlspecialchars_uni($val)));
	}

	//mobile
	static function _mobile($val,$rule=null){
		if(!$val){
			return null;
		}

		//return $val;
		return preg_match("/(13[0-9]|145|147|15[0-3]|15[5-9]|180|182|18[5-9]|17[6-8])[0-9]{8}/i",$val)? $val : null;
	}


	//mobile
	static function _telephone($val,$rule=null){
		if(!$val){
			return null;
		}

		//return $val;
		return preg_match("/^(\d{11})|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$/",$val)? $val : null;
	}


	//notrim
	static function _notrim($val,$rule=null){
		return strval(tools_String::htmlspecialchars_uni($val));
	}

	//array
	static function _array($val,$rule=null){
		return (is_array($val)) ? $val : array();
	}

	//剔除所有HTML标签
	static function _nohtml($val,$rule=null){
		return tools_String::htmlspecialchars_uni(tools_String::trim(strval($val)));
	}

	//安全的html，去除不允许的标签和属性
	static function _salf_html($val,$rule=null){
		$search = array (
				 "'<(\/?)div([^>]*)?>'si",				//将div转换为P
				 "'<script[^>]*?>.*?</script>'si",      // 去掉script
				 "'<(meta|iframe|frame|layer|link|style|form|input|select|option)([^>]*)>'si",      // 去掉iframe
				 "'<\/(iframe|frame|meta|layer|link|style|form|input|select|option)[^>]*>'si",
	//			 "'<([\w]+[\d]*)(style|href|src)*(style|href|src)*.*>'si",     //$search = array("/<(\w+\d?)\s+[^(style|href|src)]*=(.*)>/si");
				 //去除除title,alt,src,href,target,style,name外的所有标签属性
				 "/<(\w+\d?)[^>]*?(?:(?:
				 (src\s*=(?:(?:\s*\"[^\"]*\")|(?:\s*'[^']*')|(?:[^\s]*\s)))|
				 (alt\s*=(?:(?:\s*\"[^\"]*\")|(?:\s*'[^']*')|(?:[^\s]*\s)))|
				 (target\s*=(?:(?:\s*\"[^\"]*\")|(?:\s*'[^']*')|(?:[^\s]*\s)))|
				 (title\s*=(?:(?:\s*\"[^\"]*\")|(?:\s*'[^']*')|(?:[^\s]*\s)))|
				 (href\s*=(?:(?:\s*\"[^\"]*\")|(?:\s*'[^']*')|(?:[^\s]*\s)))|
				 (style\s*=(?:(?:\s*\"[^\"]*\")|(?:\s*'[^']*')|(?:[^\s]*\s)))|
				 (name\s*=(?:(?:\s*\"[^\"]*\")|(?:\s*'[^']*')|(?:[^\s]*\s)))|
				 )[^>]*?)(\/?)>/i",
				 "'([\r\n])[\s]+'",                     // 去掉空白字符
				 "'\\$'i",
				 "'script'si",
				 "'import'si"
     			);                    //

				$replace = array (
					"<\\1p\\2>",
					"",
					"&#60\\1\\2&#62",
					"&#60/\\1\\2&#62",
		//			'<\\1 \\2 \\3>',
					'<$1 $2 $3 $4 $5 $6 $7 $8>',
					"\\1",
					'&#036;',
					'&#115;cript',
					'&#105;mport',
					);
		$val = preg_replace ($search, $replace, $val);
		$val = tools_string::trim($val);
		$val = str_replace(' >', '>', $val);
		return $val;
	}

	/**
	 * 输出格式化后的字符串
	 * @param unknown_type $data
	 * @param unknown_type $rule
	 * @return unknown
	 */
	function _format($data,$rule=null){
	    return $data;
//	    str_format();
	}


	/**
	 * 输出日期格式
	 * 支持 2012/12/31及 12/31/2012 两种格式
	 * @param unknown_type $data
	 * @param unknown_type $rule
	 * @return unknown
	 */
	function _date($data,$format='Y-m-d'){
		$timestamp = strtotime($data);
		if(!$format){
			$format = 'Y-m-d';
		}
		if($timestamp){
			return date($format,$timestamp);
		}else{
			return null;
		}
	}

	/**
	 * _unixtime
	 */
	function _unixtime(){

	}

	static function _vbmktime($hours = 0, $minutes = 0, $seconds = 0, $month = 0, $day = 0, $year = 0){
	    return mktime($hours, $minutes, $seconds, $month, $day, $year) + 8;
	}
}
?>