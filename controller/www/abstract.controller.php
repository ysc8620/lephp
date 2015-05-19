<?php
class controller_www_abstract extends controller_abstract{

	function __construct(){
		parent::__construct();
	}


	function run($action=null,$controller=null){
		$action_tag = (str_ireplace ( array (
			'www', 'byjs'
		), array (
			'', ''
		), tools_request::get_uri () ));
		parent::run ( $action );
	}

	//前置任务
	function exec_before(){
		parent::exec_before();
	}

	function show_result($assign_list,$template='frame.html'){
		$assign_list['site_domain'] = core_config::get('base.site_domain') ;
		$assign_list['rs_server'] = core_config::get('base.rs_server') ;
		$assign_list['user_id'] = $this -> user_id;
        $template_dir = GROUP_NAME.'/'.core_config::get('base.tpl_theme');
		if (core_comm::by_js ()) {
			$content = core_comm::tpl ( $template_dir ) -> to_fetch ( $assign_list, $assign_list['main_file'].".tpl");
			$data = array(
				'msg_code'		=>  100000,
				'msg_content'	=>  $content
			);
			core_notice::return_json($data);
		} else {
			$content = core_comm::tpl ( $template_dir ) -> to_fetch ( $assign_list, $template );
			echo $content;
		}
	}

	private function set_cache(){

	}

	private function get_cache(){

	}
}
?>
