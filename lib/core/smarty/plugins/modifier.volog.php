<?php
function smarty_modifier_volog($vo_id,$field=null){
	if(!($vo_id)){
		return false;
	}
	$vo = core_comm::dao('user_loginlog') -> get_last_login($vo_id);
	if($vo){
	    if($field){
    		return $vo -> $field;
	    }else{
	        return $vo;
	    }
	}else{
		return '';
	}
}
?>