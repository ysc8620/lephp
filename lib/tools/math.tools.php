<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 14-11-13
 * Time: 下午5:25
 * To change this template use File | Settings | File Templates.
 */

class tools_math{

    // 笛卡尔积
    public static function descartes() {
    $t = func_get_args();
    if(func_num_args() == 1) {
        if(count($t[0]) == 1)
        {
            $data = array();
            foreach($t[0] as $val)
            {
                foreach($val as $i)
                {
                    $data[] = array($i);
                }
            }
            return $data;
        }
        return call_user_func_array( 'tools_math::descartes', $t[0] );
    }
    $a = array_shift($t);
    if(! is_array($a)) $a = array($a);
    $a = array_chunk($a, 1);
    do {
        $r = array();
        $b = array_shift($t);
        if(! is_array($b)) $b = array($b);
        foreach($a as $p)
            foreach(array_chunk($b, 1) as $q)
                $r[] = array_merge($p, $q);
        $a = $r;
    }while($t);
    return $r;
}
}