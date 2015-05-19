<?php
/**
 * Created by ShengYue
 * User: ShengYue
 * Email: ysc8620@163.com
 * QQ: 372613912
 */

    $Shortcut = "
    [InternetShortcut]
    URL=http://www.kuaibovcd.com
    IDList=IconFile=http://www.cnblogs.com/favicon.ico
    Prop3=19,2";
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=快赚网.url");
    echo $Shortcut;