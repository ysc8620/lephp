<?php 
class tools_coding{
	
	//加密算法函数
	static function encrypt($code,$Skey){
		$code   =  base64_encode(strrev(str_rot13($code)));
		$code   =  (string)$code;
		$c_l   =   strlen($code);
		$s_m   =   $Skey;
		$s_l   =   strlen($s_m);  
		$a=0;
        $str = '';
		while ($a <$c_l){
			$str .= sprintf( "%'02s",base_convert(ord($code{$a})+ord($s_m{$s_l % ($a+1)}),10,32));
			$a++;
		}
		return   $str;
	}

	//解密算法函数
	static function decrypt($code,$Skey){
		preg_match_all( "/.{2}/ ", $code, $arr);
		$arr   =   $arr[0];
		$s_m   =   $Skey;
		$s_l   =   strlen($s_m);
		$a     =   0;
        $str = '';
		foreach   ($arr   as   $value){
			$str .= chr(base_convert($value,32,10)-ord($s_m{$s_l%($a+1)}));
			$a++;
		}
		$str   =   str_rot13(strrev(base64_decode($str)));
		return   $str;
	} 
	
	static $ab = array('a','b','c','7','g','u','2','h','i','f','v','w','z','1','m','o','5','x','y','3','j','k','l','m','8','d','e','p','q','r','s','t','4','6','9','0');

	static function a($num,$salt){
		$count = count(self::$ab);
		$salt = str_split($salt,1);
		foreach($salt as $s){
			$index = ord($s) % 10;
			if($index>0){
				$num = substr($num,0,$index) . $index . substr($num,$index);
			}
		}
		
		$n = str_split($num,3);
		$str = '';
		foreach($n as $k=>$v){
			$str .= self::$ab[intval($v/$count)] . self::$ab[$v%$count];
		}
		echo $str;
		echo "<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>";
		return strtoupper($str.substr(md5($num),0,2));
	}
	
	static function b($str,$salt){var_dump($str);
		$count = count(self::$ab);
		$sc = substr($str,-2);
		$str = strtolower(substr($str,0,-2));
		$b = array_flip(self::$ab);
		$n = str_split($str,1);
		$num = '';
		
		for($i=0;$i<count($n);$i=$i+2){
			$num .= sprintf( "%'03s",$b[$n[$i]] * $count + $b[$n[$i+1]]);
		}
		var_dump($num);
		echo "<br/>";
		
		$salt = str_split($salt,1);
		for($i=count($salt)-1;$i>=0;$i--){
			$index = ord($salt[$i]) % 10;
			echo $index . "--";
			$num = substr($num,0,$index) . substr($num,$index+1);
			echo $num . "<br/>" ;
		}
		
		exit;
		echo implode('', $num) - 10000000000;
		exit;
		
		
		if(substr(md5($num),0,2) != $sc){ //md5校验
			return '';
		}
		return $num;
	}
}
?>