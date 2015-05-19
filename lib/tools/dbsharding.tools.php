<?php
/**
 * 分库分表
 */

class tools_dbsharding {
    /**
     * 批量运算表hash
     * @param array $ids ID列
     * @param integer $dbNum 库数量
     * @param integer $tbNum 表数量
     */
    public static function get_hash($ids, $base_dbname, $base_tbname, $db_num = 1, $tb_num = 64) {
        if (empty($ids))
            return false;
        $result = array();
        foreach ($ids as $id) {
            $dec = intval(sprintf('%u', crc32($id)) / $tb_num) % $tb_num;
            $dec2 = intval($dec / intval($tb_num / $db_num));
            $tb_name = $base_tbname . "_" . sprintf("%02s", dechex($dec));
            if($dec2){
	            $db_name = $base_dbname."_".sprintf("%02s", dechex($dec2));
            }else{
            	$db_name = $base_dbname;
            }
            $result[$db_name][$tb_name][] = $id;
        }
        return $result;
    }
}

?>