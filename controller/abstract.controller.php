<?php
/**
 *
 */
class controller_abstract extends core_controller{
	protected $user_id;			//当前用户ID
	protected $member_id;		

	function __construct($action=null,$controller=null){
		$login_user_id = tools_session::get_session('user_id');

        if($login_user_id){
			$this -> member_id = $login_user_id;
			$this -> user_id = $login_user_id;
		}
	}


	private function set_cache(){

	}

	private function get_cache(){

	}

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method,$args) {

            switch(strtolower($method)) {
                // 判断提交方式
                case 'ispost'   :
                case 'isget'    :
                case 'ishead'   :
                case 'isdelete' :
                case 'isput'    :
                    return strtolower($_SERVER['REQUEST_METHOD']) == strtolower(substr($method,2));
                // 获取变量 支持过滤和默认值 调用方式 $this->_post($key,$filter,$default);
                case '_get'     :   $input =& $_GET;break;
                case '_post'    :   $input =& $_POST;break;
                case '_put'     :   parse_str(file_get_contents('php://input'), $input);break;
                case '_param'   :
                    switch($_SERVER['REQUEST_METHOD']) {
                        case 'POST':
                            $input  =  $_POST;
                            break;
                        case 'PUT':
                            parse_str(file_get_contents('php://input'), $input);
                            break;
                        default:
                            $input  =  $_GET;
                    }

                    if(core_config::get('base.var_url_params') && isset($_GET[core_config::get('base.var_url_params')])){
                        $input  =   array_merge($input,$_GET[core_config::get('base.var_url_params')]);
                    }
                    break;
                case '_request' :   $input =& $_REQUEST;   break;
                case '_session' :   $input =& $_SESSION;   break;
                case '_cookie'  :   $input =& $_COOKIE;    break;
                case '_server'  :   $input =& $_SERVER;    break;
                case '_globals' :   $input =& $GLOBALS;    break;
            }

            if(!isset($args[0])) { // 获取全局变量
                $data       =   $input; // 由VAR_FILTERS配置进行过滤
            }elseif(isset($input[$args[0]])) { // 取值操作
                $data       =	$input[$args[0]];
            }else{ // 变量默认值
                $data       =	 isset($args[2])?$args[2]:NULL;
            }
           // Log::record('建议使用I方法替代'.$method,Log::NOTICE);
            return $data;
        }

}
?>