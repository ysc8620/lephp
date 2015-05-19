<?php
function smarty_modifier_vother($vo_id,$search_filed, $table=null,$field=null){
	if(!($vo_id && $search_filed && $table && $field)){
		return 0;
	}

	$where[] = array( $search_filed => $vo_id );
	$vo = core_comm::dao($table) -> get_row();
	if($vo){
	    if($field){
    		return $vo -> $field;
	    }else{
	        return $vo;
	    }
	}else{
		return 0;
	}
}
?>