<?php 
/**
 * 配置图片缩略信息
 * 系统将根据配置信息自动检测文件是否存在，不存在则自动生成
 */

$config = array(
	//商品图片
	's' => array(
		'size'			=>	'100_100',
		'mask'			=>	false,
		'mask_word'		=>	'',
		'mask_pic'		=>	'',
		'pic_quality'	=>	'',
		'default'		=>	'',
	),
	//商品图片
	'm' => array(
		'size'			=>	'400_400',
		'mask'			=>	false,
		'mask_word'		=>	'',
		'mask_pic'		=>	'',
		'pic_quality'	=>	'',
		'default'		=>	'',
	),
    'l' => array(
        'size'			=>	'600_600',
        'mask'			=>	false,
        'mask_word'		=>	'',
        'mask_pic'		=>	'',
        'pic_quality'	=>	'',
        'default'		=>	'',
    ),
	//商品图片
	'b' => array(
		'size'			=>	'800_800',
		'mask'			=>	false,
		'mask_word'		=>	'',
		'mask_pic'		=>	'',
		'pic_quality'	=>	'',
		'default'		=>	'',
	)
);

return $config;
?>