<?php 
class tools_request{
    static private $uri = null;
    static private $by_js = null;
    
    
    static public function get_uri(){
        if(!isset(self::$uri)){ 

            self::$uri = GROUP_NAME.'/'.MODULE_NAME .'/'.ACTION_NAME;
        }
        return self::$uri;
    }
    
    
    static public function by_js(){
        if(!isset(self::$by_js)){
            //@todo
            $_uri = str_ireplace('byjs', '', self::get_uri());
            $by_js = true;
            if($_uri == self::get_uri()){
                $by_js = false;
            }
            self::$by_js = $by_js;
        }
        return self::$by_js;
    }
    
    /**
     * 获取请求发起页
     */
    static function from_url(){
        $url = $_SERVER['HTTP_REFERER'];
        return $url;
    }
    
    
    /**
     * post请求安全探针
     * 用于校验跨站请求伪造(csrf)攻击
     * @param $key 探针名
     * @param $salt 探针密钥
     */
    static function post_safe(){
        $uniqid = self::get_uniqid();
        $csrf_form_name = 'csrf_key_' . $uniqid;
        if(core_comm::by_post()){
            $salt = $_SERVER['HTTP_REFERER'];
            $csrf_token = tools_Filter::p($csrf_form_name,F_STR);
            if($csrf_token != md5($salt)){
                throw new core_Exception_500('this request no safe','500');
            }
        }else{
            $salt = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $csrf_salt = md5($salt);
            $token = "<input type='hidden' name='$csrf_form_name' value='" . $csrf_salt . "'/>";
            core_comm::tpl() -> assign('csrf_key',$token);
        }
    }
    
    
    /**
     * 检查是否包含有可能引起XSS的危险字符
     * 
     * @param string $string
     * @param array $additional_strings
     * @return bool
     */
    public static function check_insecure_string($string, array $additional_strings = NULL) {
        $insecure_patterns = array(
            /*document xss中常用到的js对象 */'(document.)+',  
            /*Element dom调用的关键字*/ '(.)?([a-zA-Z]+)?(Element)+(.*)?(\()+(.)*(\))+',  
            /*script 脚本标签关键字*/ '(<script)+[\s]?(.)*(>)+',
            /*src 外调源地址属性*/ 'src[\s]?(=)+(.)*(>)+',
            /*on**(事件) 一些标签事件,比如onload等*/ '[\s]+on[a-zA-Z]+[\s]?(=)+(.)*',  
            /*XMLHttp ajax提交请求关键字*/ 'new[\s]+XMLHttp[a-zA-Z]+', 
            /*import 外部css调用*/ '\@import[\s]+(\")?(\')?(http\:\/\/)?(url)?(\()?(javascript:)?',
        );
        
        if ($additional_strings !== null) {
            $insecure_patterns += $additional_strings;
        }
        
        foreach ($insecure_patterns as $pattern){
            if(preg_match('/' . $pattern . '/i', $string)){
                return false;
            }
        }
        
        return true;
    }
    
    
    /**
     * 获取用户唯一身份标志
     */
    static public function get_uniqid(){
        $token = $_COOKIE['salf_token'];
        if(!$token){
            $token = md5(uniqid());
            setcookie('salf_token',$token,0,'/',core_config::get('base.site_domain'),false,true);
        }
        return $token;
    }
}
?>