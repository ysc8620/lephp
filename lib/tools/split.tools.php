<?php 
/**
 * 二元分词
 * @author aloner
 */
class tools_split {
	static $sign = array('\r','\n',' \t','`','~','!','@','#','$','%','^','&','*','(',')','- ','_','+','=','|','\\','\'','"',';',':','/','?','.','>',',','< ','[','{',']','}','·','～','！','＠','＃','￥','％','……','＆','×','（','）','－',' ——','＝','＋','＼','｜','【','｛','】','｝','‘','“','”','；','：','、','？','。','》 ','，','《',' ','　');
	
	/**
	 * 分词
	 * 将字符串按从$min到$max个字母分割
	 * @param str $str 待拆分字符串
	 * @param int $min 拆分最小长度
	 * @param int $max 拆分最大长度
	 * @return array 以'分词长度'为下标的二维数组
	 */
	static public function split($str,$min=2,$max=5){
		$strs = explode(' ',trim(str_replace(self::$sign, " ", $str)));
		if($strs){
			$result = array();
			foreach($strs as $s){
				$_r = self::_sign($s,$min,$max);
				if($_r){
					for($i=$min;$i<=$max;$i++){
						if($_r[$i] && $result[$i]){
							$result[$i] = array_merge($_r[$i],$result[$i]);
						}elseif($_r[$i]){
							$result[$i] = $_r[$i];
						}
					}
				}
			}
			return $result;
		}
		return null;
	}
	
	
	/**
	 * 将指定字符串按字拆分并组合
	 * @param str $str 待拆分字符串
	 * @param int $min 拆分最小长度
	 * @param int $max 拆分最大长度
	 * @return
	 */
	private static function _sign($str,$min,$max){
		if(!$str){
			return null;
		}
		$s = urlencode($str);
		$d = str_split($s);
		$count = count($d);
		$_n = 0;
		$_s = '';
		for($i=0;$i<=$count;$i++){
			$_c = $d[$i];
			if($_n%9==0 && $_c=='%' ){		//utf8格式字符，每9个字节表示一个字。
				$char[] = urldecode($_s);
				$_n = 1;
				$_s = '';
			}elseif($_n%9==0 && ord($_c)<127){
				if($_n==9){
					$char[] = urldecode($_s);
					$_s = '';
					$_n = 0;
				}
			}else{
				$_n ++;
			}
			$_s .= $_c;
			if($i==$count){
				$char[] = urldecode($_s);
			}
		}
		$char = array_values(array_filter($char));
		$result = array();
		if($char){
			$num = count($char);
			for($i=0;$i<=$num-$min;$i++){
				for($j=$min;$j<=$max;$j++){
					if(($i+$j)<=$num){
						$_s = '';
						for($m=0;$m<$j;$m++){
							$_s .= $char[$i+$m];
						}
						$result[$j][] = $_s;
					}
				}
			}
		}
		return $result;
	}
}

?>