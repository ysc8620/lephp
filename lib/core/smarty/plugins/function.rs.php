<?php 
function smarty_function_rs($params, &$smarty){
	
	$rs_domain = core_config::get('base.rs_server');
	if (empty($params['file'])) {
        $smarty->_trigger_fatal_error("[plugin] parameter 'file' cannot be empty");
        return;
    }
	if (empty($params['type'])) {
        $smarty->_trigger_fatal_error("[plugin] parameter 'type' cannot be empty");
        return;
    }
	extract($params);
	$files = explode(',',$file);
	if($files){
		if($merge){
			//获取各文件版本
			if($type=='css'){
				$content = "<link href='" . $rs_domain . "compress.css?f=" . implode(',',$files) . "' type='text/css' rel='stylesheet' />" ;
			}else{
				$content = "<script src='" . $rs_domain . "compress.js?f=" . implode(',',$files) . "' type='text/javascript'></script>"; 
			}
			return $content;
		}else{
			foreach($files as $f){
				//@todo 获取文件版本
				if($type=='css'){
					$return[] = "<link href='" . $rs_domain  . $f . "' type='text/css' rel='stylesheet' />" ; 
				}elseif($type=='js'){
					$return[] = "<script src='" . $rs_domain  . $f . "' type='text/javascript'></script>"; 
				}
			}
			return implode("\r\n",$return);
		}
	}
	return null;
}