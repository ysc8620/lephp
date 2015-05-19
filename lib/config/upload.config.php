<?php
/**
 * 文件上传相关配置信息
 * 
 * path       上传路径
 * allow      允许上传的后缀
 * rename     是否重命名
 * hash_path  是否散列存放目录
 * 
 * @var unknown_type
 */

$upload = array(
	//商品图片
	"goods" => array(
		'path'	=>	'goods/',
		'allow'	=>	array('gif','png','jpg'),
        'rename'=> true,
        'hash_path' => true
	),
	//file
	"file" => array(
		'path'	=>	'file/',
		'allow'	=>	array('rar','doc','docx','xls'),
	    'rename'=> true,
        'hash_path' => true
	),
	//xls	
	"import" => array(
		'path'	=>	'file/import/',
		'allow'	=>	array('xls'),
        'rename'=> true,
        'hash_path' => true
	),
	//xls
	"xml" => array(
		'path'	=>	'file/import/',
		'allow'	=>	array('xls'),
        'rename'=> true,
        'hash_path' => true
	),
	//pdf
	"pdf" => array(
		'path'	=>	'file/',
		'allow'	=>	array('pdf'),
        'rename'=> true,
        'hash_path' => true
	),
    //资源文件    
    "rs" => array(
        'path'  => '/',
        'allow' => array('zip','png','jpg','gif','css'),
        'rename'=> false,
        'hash_path' => false
    ),
    //资源文件
    "goodspic" => array(
        'path'  => 'product/',
        'allow' => array('zip','png','jpg','gif'),
        'rename'=> false,
        'hash_path' => false
    ),
	//csv
	"csv" => array(
		'path'	=>	'/contrast_data/',
		'allow'	=>	array('csv'),
        'rename'=> true,
        'hash_path' => true
	),
);
return $upload;
?>