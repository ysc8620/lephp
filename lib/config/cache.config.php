<?php 
/**
 * cache 服务器配置类
 * @author aloner <qiang_killer@126.com>
 */
$cache_server = array(
	'memcache'	=>	array(
		array('127.0.0.1',11211,100),
	),
	'memcached'	=>	array(
		array('127.0.0.1', 11211, 33),
	),
	'file'		=>	array(),
);

return $cache_server;
?>