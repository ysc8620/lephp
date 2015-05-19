<?php
function smarty_modifier_vo($vo_id,$vo_name,$field=null){
	if(!$vo_id){
		return false;
	}
	$vo = core_comm::dao($vo_name) -> get_vo($vo_id);
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