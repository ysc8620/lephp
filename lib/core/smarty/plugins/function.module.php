<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_module($params, &$smarty)
{
	extract($params);
	include_once(API_PATH . "shop.api.php");
	$shopApi = new shopApi();
	if($arg){
		$t = @explode(",",$arg);
		foreach($t as $r){
			$arg = @explode("=",$r);
			$args[$arg[0]] = $arg[1];
		}
	}
	$moduleExe = vo($moduleId,"shop_moduleinfo:module_function");
	if(method_exists($shopApi,$moduleExe)){
		$content = $shopApi -> $moduleExe($args);
	}
	$smarty -> assign($content);
}

/* vim: set expandtab: */

?>
