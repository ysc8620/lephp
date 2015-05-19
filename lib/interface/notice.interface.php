<?php 
class interface_notice {
	
	/**
	 * 获取未读通知列表
	 * @param unknown_type $user_id
	 */
	static function get_unread($user_id){
	    $notice_cache_key = self::get_cache_key($user_id);
	    $unread_list = core_cache::pools() -> get($notice_cache_key);
	    if($unread_list === false){
	        $rt = self::change_unread_cache($user_id);
	    }else{
	        $ids = unserialize($unread_list);
	        foreach ($ids as $id){
	            $rt[] = core_comm::dao('notice_info') -> get_vo($id);
	        }
	    }
	    return $rt;
	}
	
	
	/**
	 * 更新未读缓存
	 * @param unknown_type $user_id
	 */
	static function change_unread_cache($user_id){
	    $notice_cache_key = self::get_cache_key($user_id);
	    $unread_list = core_comm::dao('notice_info') -> get_unread($user_id);
	    $ids = array();
	    if($unread_list['list']){
	        foreach($unread_list['list'] as $r){
	            $ids[] = $r -> id;
	        }
	    }
	    core_cache::pools() -> set($notice_cache_key,serialize($ids),1800);
	    $rt = $unread_list['list'];
	    return $rt;
	}
	
	
	/**
	 * 发送一个通知
	 * @param str $type 通知类型 store/user/everyone
	 * @param str $to_ids 
	 * @param str $title
	 * @param str $content
	 */
	static function send_notice($type,$to_ids,$title,$msg){
		$notice_info = core_comm::dao('notice_info') -> add_item($type,$to_ids,$title,$msg);
		if($to_ids != 0){
		   self::change_cache($notice_info -> id,$to_ids);
		}
		return true;
	}
	
	/**
	 * 将某条消息设置为已读
	 * @param int $notice_id
	 * @param int $user_id
	 */
	static function set_read($notice_id,$user_id){
	    $rs = core_comm::dao('notice_read') -> set_read($notice_id,$user_id);
	    self::change_cache($user_id);
	    return $rs;
	}
	
	
	/**
	 * 更新指定用户通知缓存
	 * @param unknown_type $notice_id
	 * @param unknown_type $to_ids
	 */
	static function change_cache($to_ids){
	    if(!is_array($to_ids)){
	        $to_ids = explode(',',$to_ids);
	    }
	    if(!$to_ids){
	        return ;
	    }
	    foreach($to_ids as $uid){
	        self::change_unread_cache($uid);
	    }
	}
	
	/**
	 * 用户通知缓存KEY
	 * @param unknown_type $uid
	 */
	static function get_cache_key($uid){
	    return "notice_unread_" . $uid;
	}
	
	
	
	/**
	 * 更新所有用户通知缓存
	 * @param unknown_type $notice_id
	 
	static function change_cache_everyone($notice_id){
	    $unread = self::get_everyone_unread();
	    array_unshift($unread,$notice_id);
	    $notice_cache_key = self::get_everyone_cacke_key();
	    core_cache::pools() -> set($notice_cache_key,$unread,1800);
	}
	*/
	
	
	
	
	/**
	 * 系统通知缓存KEY
	 
	static function get_everyone_cacke_key(){
	    return "notice_everyone";
	}
	*/
}
?>