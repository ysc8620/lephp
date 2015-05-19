<?php 
function smarty_function_chip($params){
	extract($params);
	$chipInfo = core_comm::dao('cms_chip') -> get_chip_by_tag($tag);
	if($chipInfo->data_format != 'fixed'){
		$data = unserialize($chipInfo->chip_data);
		preg_match_all("|{start}(.*){end}|Us",$chipInfo->chip_tpl,$_row); 
		$content = '';
		$row = $_row[1][0];
		preg_match_all("|{tag:(.*)}|Us",$row,$_field);
		$field = $_field[1];
		if($data){
			foreach($data as $d){
				$v = '';
				foreach($field as $f){
					$v[] = $d[$f];
				}
				$content .= str_replace($_field[0],$v,$row);
			}
		}
		$content = str_replace($_row[0][0],$content,$chipInfo->chip_tpl);
	}else{
		$content = $chipInfo->chip_tpl;
	}
	return $content;
}

?>