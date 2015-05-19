<?php

function smarty_function_block($params){
    extract($params);
	$block_info = core_comm::dao('cms_block') -> get_block_by_tag($tag);

	$curr_version = core_comm::dao('cms_blockdata') -> get_curr_version($block_info->id);

	if($block_info->data_format == 'editable'){
		$data = unserialize($curr_version->block_data);

		preg_match_all("|{start}(.*){end}|Us",$block_info->block_html,$_row);

        $template = $block_info->block_html;
        foreach($_row[1] as $i=>$row)
        {
            $content = '';
            # $row = $_row[1][0];
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
            $template = str_replace($_row[0][$i],$content, $template);
        }
        $content = $template;
        //$content = smarty_function_block_base($data, $block_info->block_html);
	}elseif($block_info->data_format == 'fixed'){

		$content = $curr_version->block_data;

		//$content = $block_info->block_html; //固定内容保存在block_html字段中 —— 20140911 modify
	}elseif($block_info->data_format == 'interface'){
        $block = unserialize($block_info->block_field);
       // print_r($params);
        $data = interface_cms::run($block[0], $block[1], $params);
        preg_match_all("|{start}(.*){end}|Us",$block_info->block_html,$_row);
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

        $content = str_replace($_row[0][0],$content, $block_info->block_html);
	}
	return $content;
}
?>