<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * ThinkPHP内置的Dispatcher类
 * 完成URL解析、路由和调度
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 */
class core_dispatcher {

    /**
     * URL映射到控制器
     * @access public
     * @return void
     */
    static public function dispatch() {

        $urlMode  =  core_config::get('base.url_model');

        if(isset($_GET[core_config::get('base.var_pathinfo')])) { // 判断URL里面是否有兼容模式参数
            $_SERVER['PATH_INFO']   = $_GET[core_config::get('base.var_pathinfo')];
            unset($_GET[core_config::get('base.var_pathinfo')]);
        }
        if($urlMode == URL_COMPAT ){
            // 兼容模式判断
            define('PHP_FILE',_PHP_FILE_.'?'.core_config::get('base.var_pathinfo').'=');
        }elseif($urlMode == URL_REWRITE ) {
            //当前项目地址
            $url    =   dirname(_PHP_FILE_);
            if($url == '/' || $url == '\\')
                $url    =   '';
            define('PHP_FILE',$url);
        }else {
            //当前项目地址
            define('PHP_FILE',_PHP_FILE_);
        }

        // 开启子域名部署
        if(core_config::get('base.app_sub_domain_deploy')) {
            $rules      = core_config::get('base.app_sub_domain_rules');
            if(isset($rules[$_SERVER['HTTP_HOST']])) { // 完整域名或者IP配置
                $rule = $rules[$_SERVER['HTTP_HOST']];
            }else{
                $subDomain  = strtolower(substr($_SERVER['HTTP_HOST'],0,strpos($_SERVER['HTTP_HOST'],'.')));
                define('SUB_DOMAIN',$subDomain); // 二级域名定义
                if($subDomain && isset($rules[$subDomain])) {
                    $rule =  $rules[$subDomain];
                }elseif(isset($rules['*'])){ // 泛域名支持
                    if('www' != $subDomain && !in_array($subDomain,core_config::get('base.app_sub_domain_deny'))) {
                        $rule =  $rules['*'];
                    }
                }                
            }

            if(!empty($rule)) {
                // 子域名部署规则 '子域名'=>array('分组名/[模块名]','var1=a&var2=b');
                $array  =   explode('/',$rule[0]);
                $module =   array_pop($array);
                if(!empty($module)) {
                    $_GET[core_config::get('base.var_module')]  =   $module;
                    $domainModule           =   true;
                }
                if(!empty($array)) {
                    $_GET[core_config::get('base.var_group')]   =   array_pop($array);
                    $domainGroup            =   true;
                }
                if(isset($rule[1])) { // 传入参数
                    parse_str($rule[1],$parms);
                    $_GET   =  array_merge($_GET,$parms);
                }
            }
        }
        // 分析PATHINFO信息
        if(!isset($_SERVER['PATH_INFO'])) {
            $types   =  explode(',',core_config::get('base.url_pathinfo_fetch'));
            foreach ($types as $type){
                if(0===strpos($type,':')) {// 支持函数判断
                    $_SERVER['PATH_INFO'] =   call_user_func(substr($type,1));
                    break;
                }elseif(!empty($_SERVER[$type])) {
                    $_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type],$_SERVER['SCRIPT_NAME']))?
                        substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME']))   :  $_SERVER[$type];
                    break;
                }
            }
        }
        $depr = core_config::get('base.url_pathinfo_depr');
        if(!empty($_SERVER['PATH_INFO'])) {
            $part =  pathinfo($_SERVER['PATH_INFO']);
            define('__EXT__', isset($part['extension'])?strtolower($part['extension']):'');
            if(__EXT__){
                if(core_config::get('base.url_deny_suffix') && preg_match('/\.('.trim(core_config::get('base.url_deny_suffix'),'.').')$/i', $_SERVER['PATH_INFO'])){
                    send_http_status(404);
                    exit;
                }
                if(core_config::get('base.url_html_suffix')) {
                    $_SERVER['PATH_INFO'] = preg_replace('/\.('.trim(core_config::get('base.url_html_suffix'),'.').')$/i', '', $_SERVER['PATH_INFO']);
                }else{
                    $_SERVER['PATH_INFO'] = preg_replace('/.'.__EXT__.'$/i','',$_SERVER['PATH_INFO']);
                }
            }

            if(!self::routerCheck()){   // 检测路由规则 如果没有则按默认规则调度URL
                $paths = explode($depr,trim($_SERVER['PATH_INFO'],'/'));
                if(core_config::get('base.var_url_params')) {
                    // 直接通过$_GET['_URL_'][1] $_GET['_URL_'][2] 获取URL参数 方便不用路由时参数获取
                    $_GET[core_config::get('base.var_url_params')]   =  $paths;
                }
                $var  =  array();
                if (core_config::get('base.app_group_list') && !isset($_GET[core_config::get('base.var_group')])){
                    $var[core_config::get('base.var_group')] = in_array(strtolower($paths[0]),explode(',',strtolower(core_config::get('base.app_group_list'))))? array_shift($paths) : '';
                    if(core_config::get('base.app_group_deny') && in_array(strtolower($var[core_config::get('base.var_group')]),explode(',',strtolower(core_config::get('base.app_group_deny'))))) {
                        // 禁止直接访问分组
                        exit;
                    }
                }
                if(!isset($_GET[core_config::get('base.var_module')])) {// 还没有定义模块名称
                    $var[core_config::get('base.var_module')]  =   array_shift($paths);
                }
                $var[core_config::get('base.var_action')]  =   array_shift($paths);
                // 解析剩余的URL参数
                preg_replace('@(\w+)\/([^\/]+)@e', '$var[\'\\1\']=strip_tags(\'\\2\');', implode('/',$paths));
                $_GET   =  array_merge($var,$_GET);
            }
            define('__INFO__',$_SERVER['PATH_INFO']);
        }else{
            define('__INFO__','');
        }

        // URL常量
        define('__SELF__',strip_tags($_SERVER['REQUEST_URI']));
        // 当前项目地址
        define('__APP__',strip_tags(PHP_FILE));

        // 获取分组 模块和操作名称
        if (core_config::get('base.app_group_list')) {
            define('GROUP_NAME', self::getGroup(core_config::get('base.var_group')));
            // 分组URL地址
            define('__GROUP__',(!empty($domainGroup) || strtolower(GROUP_NAME) == strtolower(core_config::get('base.default_group')) )?__APP__ : __APP__.'/'.(core_config::get('base.url_case_insensitive') ? strtolower(GROUP_NAME) : GROUP_NAME));
        }

        if(defined('GROUP_NAME')) {

                $config_path    =   CONFIG_PATH.GROUP_NAME.'/';
               // var_dump( $config_path);
            // 加载分组配置文件
           # if(is_file($config_path.'base.config.php'))
                #(include $config_path.'config.php');
        }

        define('MODULE_NAME',self::getModule(core_config::get('base.var_module')));
        define('ACTION_NAME',self::getAction(core_config::get('base.var_action')));
        
        // 当前模块和分组地址
        $moduleName    =   MODULE_NAME;
        if(defined('GROUP_NAME')) {
            define('__URL__',!empty($domainModule)?__GROUP__.$depr : __GROUP__.$depr.( core_config::get('base.url_case_insensitive') ? strtolower($moduleName) : $moduleName ) );
        }else{
            define('__URL__',!empty($domainModule)?__APP__.'/' : __APP__.'/'.( core_config::get('base.url_case_insensitive') ? strtolower($moduleName) : $moduleName) );
        }
        // 当前操作地址
        define('__ACTION__',__URL__.$depr.(ACTION_NAME));
        //保证$_REQUEST正常取值
        $_REQUEST = array_merge($_POST,$_GET);
    }

    /**
     * 路由检测
     * @access public
     * @return void
     */
    static public function routerCheck() {
        $return   =  false;
        // 路由检测标签
        // tag('route_check',$return);
        $router = new core_checkroute();
        $router->run($return);
        #core_checkroute::run($return);
        return $return;
    }

    /**
     * 获得实际的模块名称
     * @access private
     * @return string
     */
    static private function getModule($var) {
        $module = (!empty($_GET[$var])? $_GET[$var]:core_config::get('base.default_module'));
        unset($_GET[$var]);

        if(core_config::get('base.url_case_insensitive')) {
            // URL地址不区分大小写
            // 智能识别方式 index.php/user_type/index/ 识别到 UserTypeAction 模块
            $module = ucfirst(parse_name($module,1));
        }
        return strip_tags($module);
    }

    /**
     * 获得实际的操作名称
     * @access private
     * @return string
     */
    static private function getAction($var) {
        $action   = !empty($_POST[$var]) ?
            $_POST[$var] :
            (!empty($_GET[$var])?$_GET[$var]:core_config::get('base.default_action'));
        unset($_POST[$var],$_GET[$var]);
        return strip_tags(core_config::get('base.url_case_insensitive')?strtolower($action):$action);
    }

    /**
     * 获得实际的分组名称
     * @access private
     * @return string
     */
    static private function getGroup($var) {
        $group   = (!empty($_GET[$var])?$_GET[$var]:core_config::get('base.default_group'));
        unset($_GET[$var]);
        return strip_tags(core_config::get('base.url_case_insensitive') ?ucfirst(strtolower($group)):$group);
    }

}
