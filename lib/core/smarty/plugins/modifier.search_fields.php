<?php
function smarty_modifier_search_fields($field_value,$field_name,$field=null){
   //
    if($field_name == 'props'){
        list($n, $v) = explode(':', $field_value);
        if(empty($v)){
            unset($field['props'][$n]);
        }else{
            $field['props'][$n] = $field_value;
        }

    }else{
        if($field_value != ''){
            $field[$field_name] = $field_value;
        }else{
            unset($field[$field_name]);
        }
    }


    $str_fields = '';
    foreach($field as $k=>$v){
        if($k == 'props'){
            $str_fields .= "&props=".join('@', $v);
        }else{
            $str_fields .= "&$k=$v";
        }

    }
    return trim($str_fields, '&');

}
?>