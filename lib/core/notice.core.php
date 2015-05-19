<?php 
class core_notice{
    
    /**
     * 显示提示信息
     * @return
     */
    static function notice($msg,$location='javascript:history.go(-1)'){
        if(is_numeric($msg)){
            $error_msg = core_msg::message($msg,LANGUAGE);
        }else{
            $error_msg = $msg;
            $msg = null;
        }
        
        if(!core_comm::by_js()){
            $notice = array(
                'error_no'    =>    $msg,
                'error_msg'   =>    $error_msg,
                'location'    =>    $location,
            );
            $assignList = array(
                'notice'        =>    $notice
            );
            $temp = isset($temp) ? $temp : 'notice.html';
            core_comm::tpl() -> show_result($assignList,$temp);
        }else{
            $notice = array(
                'msg_code'    =>    $msg,
                'msg_content' =>    $error_msg,
                'location'    =>    $location,
            );
            self::return_json($notice);
        }
        exit;
    }
    
    /**
     * 接口返回josn数据或错误信息
     * @return
     */
    static function api_data($msg,$data = ''){
        if(is_numeric($msg)){
            $error_msg = core_msg::message($msg,LANGUAGE);
        }

        $rs['result'] = $msg;

        if($msg != '100001'){
            $rs['data'] = $error_msg;
        }else{
            $rs['data'] = $data;
        }

        echo json_encode($rs);
        exit;
    }
    
    /**
     * 直接跳转
     */
    static function redirect($url=null){
        $url = $url ? $url : '/'; 
        header("location:".$url);
    }
    
    /**
     * 通过JSON格式返回数据
     * @param $data
     * @return
     */
    static function return_json($data){
        echo json_encode($data);
        exit;
    }
    
    /**
     * 400错误提示
     * @param unknown_type $assign
     * @param unknown_type $temp
     */
    static function error_404($assign=null,$temp=null){
        if(!$temp){
            $temp = '404.html';
        }
        core_comm::tpl('comm') -> show_data($assign,$temp);
    }
    
    /**
     * 500错误提示
     * @param unknown_type $assign
     * @param unknown_type $temp
     */
    static function error_500($assign=null,$temp=null){
    	if(!$temp){
    		$temp = '500.html';
    	}
    	core_comm::tpl('comm') -> show_result($assign,$temp);
    }
}
?>