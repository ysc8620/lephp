<?php
$start = microtime(true);
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 2010 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");
//加载系统配置
include_once ("../lib/config/app.config.php");
//加载核心库
include_once (CORE_PATH . "comm.core.php");

if($_SERVER['HTTP_HOST'] == 'ht.lephp.cn'){
    # 设置当前默认group
    core_config::set('base.default_group', 'admin');
}

core_app::run ();
