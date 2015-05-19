<?php
/**
 * 配置数据库存放服务器
 * Key 表示table表中映射的数据库名，value表示实际数据库名
 
 */
return array (
    /**
     * 配置数据库资源池
     *
     * 每个数组为一组服务器
     * 每组服务器可设置一个主服务器（写）
     * 每组服务器可设置多组从服务器（读）
     */
    'dbserver' => array(
        'host_1'	=>	array(
            'driver'	=>	'mysql',
            'master'	=>	array("hostname" => "120.24.248.85", "username" => "dev", "password" => "rootSundan123", "hostport" => 3306),
            'salve'		=>	array(
                array("hostname" => "120.24.248.85", "username" => "dev", "password" => "rootSundan123", "hostport" => 3306),
            ),
        ),
    ),

    /**
     * 配置数据库存放服务器
     * Key 表示table表中映射的数据库名，value表示实际数据库名

     */
    'db'=> array(
        'default'	=> 'sd_new'
    ),

    /**
     * 配置数据库存放服务器
     * Key 表示 db 数据库别名， value 数据库对应服务器
     */
    'dbhost'=> array (
        "default"			=> 'host_1',
    )


);
?>
