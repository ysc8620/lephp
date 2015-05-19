<?php 
class core_tpl extends core_smarty_smarty{
	function __construct($template_dir=null){ 
		$this -> _init($template_dir);
	}
	
	/**
	 * smarty初始化
	 * @return
	 */
	function _init($temp_dir){
		$this -> left_delimiter = "<{";
		$this -> right_delimiter = "}>";
		$this -> set_template($temp_dir);
		//是否为JS 操作
		$this -> assign('byJs',core_comm::by_js());
	}
	
	/**
	 * 获取待输出的内容（联合语句）
	 * @param unknown_type $assign_list
	 * @param unknown_type $template
	 * @param unknown_type $temp_dir
	 * @return
	 */
	public function to_fetch($assign_list,$template=null,$temp_dir=null){
		if($temp_dir){
			$this -> set_template($temp_dir);
		}
		$this -> assign($assign_list);
		$content = $this -> fetch($template);
		return $content;
	}
	
	/**
	 * 显示内容（联合语句）
	 * @param unknown_type $assign_list
	 * @param unknown_type $template
	 * @param unknown_type $temp_dir
	 * @return
	 */
	public function show_result($assign_list,$template=null,$temp_dir=null,$cache_id=null){
		if($temp_dir){
			$this -> set_template($temp_dir);
		}
		$this -> assign($assign_list);
		if($cache_id){
			
		}
		$this -> display($template,$cache_id);
	}
	
	function show_notes($msg_no,$redirect='',$error_tpl=null,$is_log=false){
		$msg = core_msg::message($msg_no);
		$msg['redirect'] = $redirect;
		if(core_comm::by_js()){
			echo json_encode($msg);
		}else{
			if(!$error_tpl){
				$error_tpl = 'tips.html';
			}
			$jump_time = 3000;
			$assign_list = array(
				'msg'			=>	$msg,
				'jumpTime'		=>	$jump_time,
				'mainFileName'	=>	'tips.tpl'
			);
			$this -> show_result($assign_list,$error_tpl);
		}
		exit;
	}
	
	
	/**
	 * 写入文件（联合语句）
	 * @param fixed $assign_list 待渲染内容 
	 * @param str $file_path 保存文件路径
	 * @param str $template 模板文件名
	 * @param str $temp_dir 模板文件路径
	 * @return
	 */
	public function to_file($assign_list,$file_path,$template=null,$temp_dir=null){
		$content = $this -> to_fetch($assign_list,$template,$temp_dir);
		if(tools_file::to_file($file_path,$content)){
			return true;
		}
		return false;
	}
	
	
	/**
	 * 设置目录结构
	 * @param $tpl_path
	 * @return
	 */
	public function set_template($tpl_path){
		$this -> template_dir	=	TEMPLATES_PATH . $tpl_path . "/";
		$this -> compile_dir	=	TEMPLATES_C_PATH . $tpl_path . "/";
		$this -> cache_dir		=	TEMPLATES_CACHE_PATH . $tpl_path . "/";
		$this -> config_dir		=   TEMPLATES_CONFIG_PATH ;
	
		if(!file_exists($this -> compile_dir)){
			tools_file::create_dir($this -> compile_dir);
		}
		if(!file_exists($this -> cache_dir)){
			tools_file::create_dir($this -> cache_dir);
		}
	}
}
?>