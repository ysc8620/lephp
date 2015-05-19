<?php  
	/**
	 * tools_service_server 
	 * 
	 * webservice 服务器端执行类
	 * 
	 * @version  v1.0 
	 * @update   2011-12-22 
	 * @author   homingway 
	 * @contact  homingway@gmail.com 
	 * @package  webservice  
	*/
    define('API_AUTH_KEY',  'i8XsJb$fJ!87FblnW');  
    class tools_webservice_server{  
      
        
        public $request = array();  			//请求参数  
        public $ip_limit = false;  			//是否ip限制  
        public $ip_allow = array();  			//允许访问的IP列表  
        public $service_method = array();  	//资源池
        private static $_instance = null;   	//私有静态单例变量 
      
        
        
        /** 
         * 构造方法，处理请求参数 
         */  
        private function __construct($auth_key=null){
        	$this -> auth_key = $auth_key;  
            $this->deal_request();  
        }
        
        
        function set_auth(){}
        
        /** 
         * 单例运行 
         */  
        public static function getInstance(){  
            if(self::$_instance === null){  
                self::$_instance = new self();  
            }  
            return self::$_instance;  
        }  
      
        /** 
         * 运行 
         */  
        public function run(){  
            //授权  
            if(!$this->check_auth()){  
                exit('3|Access Denied');
            }  
            $this->get_api_method();  
            include_once(API_SERVICE_PATH.'/'.$this->service_method['service'].'.php');  
            $serviceObject = new $this->service_method['service'];  
            if($this->request['param']){  
                $result = call_user_func_array(array($serviceObject,$this->service_method['method']),$this->request['param']);  
            } else {  
                $result = call_user_func(array($serviceObject,$this->service_method['method']));  
            }  
            if(is_array($result)){  
                $result = json_encode($result);  
            }  
            $result = gzencode($result);  
            exit($result);  
        }  
      
        /** 
         * 检查授权 
         */  
        public function check_auth(){  
            //检查参数是否为空  
            if(!$this->request['time'] || !$this->request['method']   || !$this->request['auth']){  
                return false;  
            }
      
            //检查auth是否正确  
            $server_auth = md5(md5($this->request['time'].'|'.$this->request['method'].'|'.API_AUTH_KEY));
            if($server_auth !== $this->request['auth']){  
                return false;  
            }  

            //ip限制  
            if($this -> ip_limit){  
                $remote_ip = core_comm::get_ip();
                if(!in_array($remote_ip,$this -> ip_allow)){
                	return false;
                }
            }
            return true;  
        }  
      
        /** 
         * 获取服务名和方法名 
         */  
        public function get_api_method(){  
            if(strpos($this->request['method'], '.') === false){  
                $method = $this->default_method;
            } else {  
                $method = $this->request['method'];  
            }  
            $tmp = explode('.', $method);  
            $this->service_method = array('service'=>$tmp[0],'method'=>$tmp[1]);  
            return $this->service_method;  
        }  
      
        /** 
         * 获取和处理请求参数 
         */  
        public function deal_request(){  
            $this->request['time'] = $this->_request('time');  
            $this->request['method'] = $this->_request('method');  
            $this->request['param'] = $this->_request('param');  
            $this->request['auth'] = $this->_request('auth');  
            if($this->request['param']){  
                $this->request['param'] = json_decode(urldecode($this->request['param']),true);  
            }  
        }  
      
        /** 
         * 获取request变量 
         * @param string $item 
         */  
        private function _request($item){  
            return isset($_REQUEST[$item]) ? trim($_REQUEST[$item]) : '';  
        }  
      
        /** 
         * 设置IP限制 
         * @param bool $limit 
         */  
        public function set_ip_limit($limit=true){  
            $this->ip_limit = $limit;  
        }  
    }  