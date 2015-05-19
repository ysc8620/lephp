<?php
/**
 * 应用类
 * Created by ShengYue
 * User: ShengYue
 * Email: ysc8620@163.com
 * QQ: 372613912
 */
class core_app {
	static function run() {
		try {
			//获取系统初始化配置
	    	$init_config = core_config::get('init');
	    	
	    	setlocale(LC_ALL, 'zh_CN.utf-8');
	    	date_default_timezone_set('Asia/Chongqing');
	    	ini_set("session.cookie_domain",substr(core_config::get('base.site_domain'),1));
	    	session_start();
			
	    	//项目初始化
	    	self::init($init_config);

            // 路由规则
            core_dispatcher::dispatch();
	    	
	    	/*启动前置插件*/  
	    	self::befor_plug();
	    	/*项目启动          */  
	    	self::execute();
	    	/* 性能分析 */
	    	self::xhprof();
	    	/*启动前置插件*/  
	    	self::after_plug();
		} catch (core_exception_unexpected $e) {
			//致命错误，需要记录日志
			//抛出该异常意味着发生了程序不可控制的错误
			if (core_comm::by_js()) {		//js请求
		        //TODO normal json out
		        $rs = array(
		        	'msg_code'		=>	$e -> code,
		        	'msg_content'	=>	$e -> getMessage()
		        );
		        echo json_encode($rs);
			}else{
				throw $e;
				//core_notice::notice($e -> code);
			}
		}catch (core_exception_assert $e) {
			if (core_comm::by_js()) {		//js请求
		        //TODO normal json out
		        $rs = array(
		        	'msg_code'		=>	$e -> code,
		        	'msg_content'	=>	$e -> getMessage()
		        );
		        echo json_encode($rs);
			}else{
				//throw $e;
				core_notice::notice($e -> code);
			}
		}catch(core_exception_404 $e){
			echo '404 error:'.$e -> getMessage();
		}catch(core_exception_program $e){
			//致命错误，需要记录日志
			//抛出该异常意味着发生了程序不可控制的错误
			if (core_comm::by_js()) {		//js请求
		        $rs = array(
		        	'msg_code'		=>	$e -> code,
		        	'msg_content'	=>	$e -> getMessage()
		        );
		        echo json_encode($rs);
			}else{
				throw $e;
				//core_notice::notice($e -> code);
			}
		}catch (core_exception $e){
			if (core_comm::by_js()) {		//js请求
		        //TODO normal json out
		        $rs = array(
		        	'msg_code'		=>	$e -> code,
		        	'msg_content'	=>	$e -> getMessage()
		        );
		        echo json_encode($rs);
			}else{
				throw $e;
				//core_notice::notice($e -> code);
			}
		}
	}
	
	/**
	 * 路由器初始化
	 * @param unknown_type $settings
	 * @throws core_exception_program
	 * @return
	 */
	static function init($settings){
		
		//@todo 启动公共数据池
		core_context::init();
		
		$xhprof_debug = isset($_GET['xhprof_debug']) ? TRUE : FALSE;
		core_context::set('xhprof_debug', $xhprof_debug);
		//@todo 设置断言调试模式
		core_assert::as_dumb();
		
		//@todo 设置xhprof
		if($xhprof_debug){
			xhprof_enable();
		}

		//使用自定义的错误处理函数，将所有错误当成exception来处理
		if (isset($settings['error_to_exception']) && $settings['error_to_exception'] == true){
            set_error_handler(array('core_exception', 'error_handler'));
        }

        //是否显示跟踪调试信息
        if (isset($settings['show_trace_info']) && $settings['show_trace_info'] === true) {
            core_exception::$show_trace_info = true;
            set_exception_handler(array('core_exception', 'exception_handler'));
            register_shutdown_function(array('core_exception', 'shutdown_handler'));
        }
        
        //检查register_globals状态
        if (ini_get('register_globals')) {
            throw new core_exception_program("register_globals can not be enable");
        }
        
        //设置编码格式
        if (function_exists('mb_internal_encoding')) {
            //mb_internal_encoding('utf-8');
        }
        
	}
	
	/**
	 * 获取 controller
	 * @param str $uri 请求地址
	 */
	static private function get_controller(){
		return "controller_" .GROUP_NAME.'_'.MODULE_NAME ;
	}
	
	
	
	/**
	 * 获取action
	 * @param str $uri 请求地址
	 */
	static private function get_action(){
        $uri = ACTION_NAME;
		$_uri = str_ireplace('byjs', '', $uri);
		return $_uri;
	}
	
	
	//执行请求
	static private function execute(){
		$uri = tools_request::get_uri();
		$controller_name = self::get_controller();
		$action = self::get_action();
		$controller = new $controller_name();
		$controller -> run ($action,$controller_name);
	}
	
	//前置插件
	static private function befor_plug(){
		//兼容性插件
	//	core_comm::load(ROOT_PATH . "core/function.core.php");
		//@todo 
		//do something as filter input and some about xss and so on
		
		//生成全站唯一token
	}
	
	
	//后置插件
	static private function after_plug(){
		//@todo 
		//do something as clear pools
	}
	
	//性能分析
	static private function xhprof(){
		$xhprof_debug = core_context::get('xhprof_debug');
		if($xhprof_debug){
			$xhprof_data = xhprof_disable();
			include_once CORE_PATH . "xhprof/utils/xhprof_lib.php";
			include_once CORE_PATH . "xhprof/utils/xhprof_runs.php";
			$xhprof_runs = new XHProfRuns_Default();
			$xhprof_run_id = $_GET['xhprof_run_id'];
		//	$xhprof_run_id = tools_request::get_uri();
			if(!empty($xhprof_run_id)){
				$xhprof_run_id = str_replace('.', '_', $xhprof_run_id) . "_";
			}
			$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo", $xhprof_run_id);
			$content = "<a href='http://xhprof.31fen.cn/callgraph.php?run=$xhprof_run_id&source=xhprof_foo'>http://www.31fen.cn".$_SERVER['REQUEST_URI']."</a> <br/> ";
			//写入到文件末尾
			tools_file::to_file( ROOT_PATH . 'htdocs/www/xhprof/index.html', $content, 'a+');
		}
	}
}
?>