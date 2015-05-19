<?php 
/**
 * 发送短信工具
 * @author aloner
 *
 */
class tools_sms{
	
	/**
	 * 将信息加入待发送队列
	 * 
	 * @param $msg 发送消息内容
	 * @param str/array $mobiles 发送手机号码 格式如：13910607777,123222222 或者 array(13919999999,15910101010); 
	 * @return 
	 */
	static function to_queue($msg,$mobiles){
		//加入消息队列
		$rs = core_comm::dao('comm_smsqueue') -> to_queue($msg,$mobiles);
		return $rs;
	}
	
	
	 /**
	  * 处理发送短信操作
	  * @param int $per_num 每次处理记录数
	  */
	static function to_send($per_num=10){
		$list = core_comm::dao('comm_smsqueue') -> get_wait_queue($per_num);
		if($list['list']){
			foreach($list['list'] as $row){
				$rs[$row->id] = thirdpart_sms::send_sms($row -> to_msg,$row -> to_mobile);
			}
			//更新消息队列中的状态
			core_comm::dao('comm_smsqueue') -> reset_queue($rs);
		}
	}
}
?>