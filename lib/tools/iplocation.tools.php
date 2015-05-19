<?php 
class tools_iplocation{
	
	/**
	 * 根据IP地址获取城市信息
	 * @param str $ip ip地址
	 */
	static function get_location_by_id($ip){
		$iplocation = new core_iplocation();
		$separator = $iplocation->separate(1000);//分成1000块
		$location = $iplocation->getlocation($ip, $separator);//含有分块的查询
		$city_name = iconv("GBK", "UTF-8", $location['country']);
		return $city_name;
	}
}
?>