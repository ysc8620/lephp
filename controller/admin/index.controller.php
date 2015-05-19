<?php
/**
 * 首页
 * @author aloner
 *
 */

class controller_admin_index extends controller_admin_abstract{

	function indexAction(){
        echo core_url::tsurl('/goods/show', array('id'=>100)) . "<br/>";
		$assign_list = array();
		$this -> show_result($assign_list,'index.html');
	}

}