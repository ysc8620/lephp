<?php
/**
 * 首页
 * @author aloner
 *
 */

class controller_www_index extends controller_www_abstract{

	function indexAction(){
		$assign_list = array();
		$this -> show_result($assign_list,'s.html');
	}

}