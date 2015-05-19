<?php 
/**
 * 服务器端授权配置文件
 * @var unknown_type
 */

if(version_compare(PHP_VERSION,'5.2.0','<'))  die('require PHP > 5.2.0 !');
//   系统信息
if(version_compare(PHP_VERSION,'5.4.0','<')) {
    ini_set('magic_quotes_runtime',0);
    define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?True:False);
}else{
    define('MAGIC_QUOTES_GPC',false);
}
define('IS_CGI',substr(PHP_SAPI, 0,3)=='cgi' ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);
// 项目名称
defined('APP_NAME') or define('APP_NAME', basename(dirname($_SERVER['SCRIPT_FILENAME'])));

if(!IS_CLI) {
    // 当前文件名
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGI模式下
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            define('_PHP_FILE_', rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_', rtrim($_SERVER['SCRIPT_NAME'],'/'));
        }
    }
    if(!defined('__ROOT__')) {
        // 网站URL根目录
        if( strtoupper(APP_NAME) == strtoupper(basename(dirname(_PHP_FILE_))) ) {
            $_root = dirname(dirname(_PHP_FILE_));
        }else {
            $_root = dirname(_PHP_FILE_);
        }
        define('__ROOT__',   (($_root=='/' || $_root=='\\')?'':$_root));
    }
}

//支持的URL模式
define('URL_COMMON',      0);   //普通模式
define('URL_PATHINFO',    1);   //PATHINFO模式
define('URL_REWRITE',     2);   //REWRITE模式
define('URL_COMPAT',      3);   // 兼容模式

define('DS',DIRECTORY_SEPARATOR);
define ( "ROOT_PATH", realpath(dirname ( __FILE__ ) . "/../")."/" );
define ( "CORE_PATH", ROOT_PATH . "core/" );
define ( "TOOLS_PATH", ROOT_PATH . "tools/");
//配置文件目录
define ( "CONFIG_PATH", ROOT_PATH . "config/" );
//信息文件目录
define ( "LANGUAGE_PATH", ROOT_PATH . "msg/" );

# 网站
define ( "APP_PATH", realpath(ROOT_PATH."../")."/");
define ( "RS_PATH", APP_PATH . "htdocs/rs");
define ( "LANGUAGE" , 'chinese');
//模板文件目录
define ( "TEMPLATES_PATH", APP_PATH . "tpl/templates/" );
define ( "TEMPLATES_C_PATH", APP_PATH . "tpl/templates_c/" );
define ( "TEMPLATES_CACHE_PATH", APP_PATH . "tpl/templates_cache/" );
define ( "TEMPLATES_CONFIG_PATH", APP_PATH . "tpl/templates_config/" );

//临时文件目录
define ("TMP_PATH",APP_PATH . 'tmp/');

//临时上传文件存放目录，多用于跨域文件传输
define ("TMP_UPLOAD_PATH",TMP_PATH . 'upload/');

//缓存文件目录
define ("TMP_CACHE_PATH",TMP_PATH . 'cache/');

//上传文件目录
define ( "UPLOAD_PATH", ROOT_PATH . "/upload/" );

//图片存放目录
define ( "PIC_UPLOAD_PATH", UPLOAD_PATH);

//图片访问目录
define ( "PIC_VIEW_PATH", ROOT_PATH . 'htdocs/pic/' );

define ( "DB_SUFFIX", "" );
define ( "CACHE_SUFFIX", "sd" );
error_reporting(E_ERROR | E_WARNING | E_PARSE);