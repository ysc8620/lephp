<?php
/**
 * $_
 */
class core_url{

    /**
     * URL组装 支持不同URL模式
     * @param string $url URL表达式，格式：'[分组/模块/操作#锚点@域名]?参数1=值1&参数2=值2...'
     * @param string|array $vars 传入的参数，支持数组和字符串
     * @param string $suffix 伪静态后缀，默认为true表示获取配置值
     * @param boolean $redirect 是否跳转，如果设置为true则表示跳转到该URL地址
     * @param boolean $domain 是否显示域名
     * @return string
     */
    public static function tsurl($url='',$vars='',$suffix=true,$redirect=false,$domain=false) {
        // 解析URL
        $info   =  parse_url($url);
        $url    =  !empty($info['path'])?$info['path']:ACTION_NAME;
        if(isset($info['fragment'])) { // 解析锚点
            $anchor =   $info['fragment'];
            if(false !== strpos($anchor,'?')) { // 解析参数
                list($anchor,$info['query']) = explode('?',$anchor,2);
            }
            if(false !== strpos($anchor,'@')) { // 解析域名
                list($anchor,$host)    =   explode('@',$anchor, 2);
            }
        }elseif(false !== strpos($url,'@')) { // 解析域名
            list($url,$host)    =   explode('@',$info['path'], 2);
        }
        // 解析子域名
        if(isset($host)) {
            $domain = $host.(strpos($host,'.')?'':strstr($_SERVER['HTTP_HOST'],'.'));
        }elseif($domain===true){
            $domain = $_SERVER['HTTP_HOST'];
            if(core_config::get('base.app_sub_domain_deploy') ) { // 开启子域名部署
                $domain = $domain=='localhost'?'localhost':'www'.strstr($_SERVER['HTTP_HOST'],'.');
                // '子域名'=>array('项目[/分组]');
                foreach (core_config::get('base.app_sub_domain_rules') as $key => $rule) {
                    if(false === strpos($key,'*') && 0=== strpos($url,$rule[0])) {
                        $domain = $key.strstr($domain,'.'); // 生成对应子域名
                        $url    =  substr_replace($url,'',0,strlen($rule[0]));
                        break;
                    }
                }
            }
        }

        // 解析参数
        if(is_string($vars)) { // aaa=1&bbb=2 转换成数组
            parse_str($vars,$vars);
        }elseif(!is_array($vars)){
            $vars = array();
        }
        if(isset($info['query'])) { // 解析地址里面参数 合并到vars
            parse_str($info['query'],$params);
            $vars = array_merge($params,$vars);
        }

        // URL组装
        $depr = core_config::get('base.url_pathinfo_depr');
        if($url) {
            if(0=== strpos($url,'/')) {// 定义路由
                $route      =   true;
                $url        =   substr($url,1);
                if('/' != $depr) {
                    $url    =   str_replace('/',$depr,$url);
                }
            }else{
                if('/' != $depr) { // 安全替换
                    $url    =   str_replace('/',$depr,$url);
                }
                // 解析分组、模块和操作
                $url        =   trim($url,$depr);
                $path       =   explode($depr,$url);
                $var        =   array();
                $var[core_config::get('base.var_action')]       =   !empty($path)?array_pop($path):ACTION_NAME;
                $var[core_config::get('base.var_module')]       =   !empty($path)?array_pop($path):MODULE_NAME;

                if(core_config::get('base.url_case_insensitive')) {
                    $var[core_config::get('base.var_module')]   =   parse_name($var[core_config::get('base.var_module')]);
                }
                if(!core_config::get('base.app_sub_domain_deploy') && core_config::get('base.app_group_list')) {
                    if(!empty($path)) {
                        $group                  =   array_pop($path);
                        $var[core_config::get('base.var_group')]    =   $group;
                    }else{
                        if(GROUP_NAME != core_config::get('base.default_group')) {
                            $var[core_config::get('base.var_group')]=   GROUP_NAME;
                        }
                    }
                    if(core_config::get('base.url_case_insensitive') && isset($var[core_config::get('base.var_group')])) {
                        $var[core_config::get('base.var_group')]    =  strtolower($var[core_config::get('base.var_group')]);
                    }
                }
            }
        }

        if(core_config::get('base.url_model') == 0) { // 普通模式URL转换
            $url        =   __APP__.'?'.http_build_query(array_reverse($var));
            if(!empty($vars)) {
                $vars   =   urldecode(http_build_query($vars));
                $url   .=   '&'.$vars;
            }
        }else{ // PATHINFO模式或者兼容URL模式
            if(isset($route)) {
                $url    =   __APP__.'/'.rtrim($url,$depr);
            }else{
                $url    =   __APP__.'/'.implode($depr,array_reverse($var));
            }
            if(!empty($vars)) { // 添加参数
                foreach ($vars as $var => $val){
                    if('' !== trim($val))   $url .= $depr . $var . $depr . urlencode($val);
                }
            }
            if($suffix) {
                $suffix   =  $suffix===true?core_config::get('base.url_html_suffix'):$suffix;
                if($pos = strpos($suffix, '|')){
                    $suffix = substr($suffix, 0, $pos);
                }
                if($suffix && '/' != substr($url,-1)){
                    $url  .=  '.'.ltrim($suffix,'.');
                }
            }
        }
        if(isset($anchor)){
            $url  .= '#'.$anchor;
        }
        if($domain) {
            $url   =  (self::is_ssl()?'https://':'http://').$domain.$url;
        }
        if($redirect) // 直接跳转URL
            self::redirect($url);
        else
            return $url;
    }



    /**
     * 判断是否SSL协议
     * @return boolean
     */
    static function is_ssl() {
        if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
            return true;
        }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
            return true;
        }
        return false;
    }

    /**
     * URL重定向
     * @param string $url 重定向的URL地址
     * @param integer $time 重定向的等待时间（秒）
     * @param string $msg 重定向前的提示信息
     * @return void
     */
    static function redirect($url, $time=0, $msg='') {
        //多行URL地址支持
        $url        = str_replace(array("\n", "\r"), '', $url);
        if (empty($msg))
            $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
        if (!headers_sent()) {
            // redirect
            if (0 === $time) {
                header('Location: ' . $url);
            } else {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        } else {
            $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0)
                $str .= $msg;
            exit($str);
        }
    }

}
