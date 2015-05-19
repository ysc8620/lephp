<?php

return array(
    'site_domain' => '.sundanimg.com',
    'site_suffix' => 'sd',
    'rs_server' => 'rs.sundanimg.com',
    //图片服务器路径
     "image_server" => 'http://img5.sundanimg.com/',
    // 是否开启调试
    'debug' => true,
    'tpl_theme'=>'default',
    'use_cache'=> false,
    'cache_type' =>'memcache',

    /* 项目设定 */
    'app_sub_domain_deploy' => true,   // 是否开启子域名部署
    'app_sub_domain_rules'  => array(), // 子域名部署规则
    'app_sub_domain_deny'   => array(), //  子域名禁用列表
    'app_group_list'        => 'www,admin,api',      // 项目分组设定,多个组之间用逗号分隔,例如'Home,Admin'
    'app_group_deny'=>'',

    /* 默认设定 */
    'default_group'         => 'www',  // 默认分组
    'default_module'        => 'index', // 默认模块名称
    'default_action'        => 'index', // 默认操作名称

    /* URL设置 */
    'url_case_insensitive'  => false,   // 默认false 表示URL区分大小写 true则表示不区分大小写
    'url_model'             => 3,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式，提供最好的用户体验和SEO支持
    'url_pathinfo_depr'     => '/',	// PATHINFO模式下，各参数之间的分割符号
    'url_pathinfo_fetch'    =>   'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL', // 用于兼容判断PATH_INFO 参数的SERVER替代变量列表
    'url_html_suffix'       => 'html',  // URL伪静态后缀设置
    'url_deny_suffix'       =>  'ico|png|gif|jpg', // URL禁止访问的后缀设置

    /* 系统变量名称设置 */
    'var_group'             => 'g',     // 默认分组获取变量
    'var_module'            => 'm',		// 默认模块获取变量
    'var_action'            => 'a',		// 默认操作获取变量
    'var_pathinfo'          => 's',	// PATHINFO 兼容模式获取变量例如 ?s=/module/action/id/1 后面的参数取决于url_pathinfo_depr
    'var_url_params'        => '_URL_', // PATHINFO URL参数变量
    'var_template'          => 't',		// 默认模板切换变量

    /*路由规则*/
    'url_router_on'=>true,
    'url_route_rules'=>array(
        'show/:id'               => 'admin/goods/index',
    ),
);