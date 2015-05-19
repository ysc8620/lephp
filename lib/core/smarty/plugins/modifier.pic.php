<?php
/**

ÊµÀý£º
<img src='<{$pic_path|pic:"m"}>'/>

*/
function smarty_modifier_pic($pic_from,$pic_rule=null){
	//return core_comm::get_pic($pic_from, $pic_type, $pic_rule);
	$server = tools_image::get_server($pic_from);
	return $server . $pic_from . "?r=".$pic_rule;
}

