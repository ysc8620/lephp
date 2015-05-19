<?php

class tools_array {
    /**
     * 根据key从数组中找到相关值，其中key是依据$delimiter分离的，默认为“.”
     *
     * // 比如获取值： $array['foo']['bar']
     * $value = Comm_Array::path($array, 'foo.bar');
     *
     * 使用 "*"作为匿名
     *
     * // Get the values of "color" in theme
     * $colors = Comm_Array::path($array, 'theme.*.color');
     *
     * @param array 数组
     * @param string key字符串，多维的由$delimiter连接
     * @param mixed 如果没有查到数组中的该值返回的默认值
     * @param string key path 的分隔符
     * @return mixed
     */
    public static function path($array, $path, $default = NULL) {
        if (array_key_exists($path, $array)) {
            return $array[$path];
        }
        
        $delimiter = ".";
        //$path = trim($path, "{$delimiter}* ");
        $keys = explode($delimiter, $path);
        do {
            $key = array_shift($keys);
            
            if (isset($array[$key])) {
                if ($keys) {
                    if (is_array($array[$key])) {
                        $array = $array[$key];
                    } else {
                        break;
                    }
                } else {
                    return $array[$key];
                }
            } elseif ($key === '*') {
                $values = array();
                $inner_path = implode($delimiter, $keys);
                foreach ($array as $arr) {
                    $value = is_array($arr) ? self::path($arr, $inner_path) : $arr;
                    if ($value) {
                        $values[] = $value;
                    }
                }
                
                if ($values) {
                    return $values;
                } else {
                    break;
                }
            } else {
                break;
            }
        } while ($keys);
        
        return $default;
    }
    
    /**
     * 递归合并两个或多个数组
     * 本函数内使用for语句，以及func_get_arg函数，实现多个数组递归合并
     * $john = array('name' => 'john', 'children' => array('fred', 'paul', 'sally', 'jane'));
     * $mary = array('name' => 'mary', 'children' => array('jane'));
     *
     * $john = Comm_Array::merge($john, $mary);
     *
     * array('name' => 'mary', 'children' => array('fred', 'paul', 'sally', 'jane'))
     *
     * @param a1 原始数组
     * @param a2 需要合并的数组
     * @return array
     */
    public static function merge(array $a1, array $a2) {
        $result = array();
        for($i = 0, $total = func_num_args(); $i < $total; $i ++) {
            $arr = func_get_arg($i);
            $assoc = tools_Array::is_assoc($arr);
            foreach ($arr as $key => $val) {
                if (isset($result[$key])) {
                    if (is_array($val) && is_array($result[$key])) {
                        if (tools_Array::is_assoc($val)) {
                            $result[$key] = tools_Array::merge($result[$key], $val);
                        } else {
                            $diff = array_diff($val, $result[$key]);
                            $result[$key] = array_merge($result[$key], $diff);
                        }
                    } else {
                        if ($assoc) {
                            $result[$key] = $val;
                        } elseif (!in_array($val, $result, true)) {
                            $result[] = $val;
                        }
                    }
                } else {
                    $result[$key] = $val;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * 是否为关联数组
     *
     * @param array array to check
     * @return boolean
     */
    public static function is_assoc(array $array) {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }
    
    /**
     * 将数组中的两组数据交换位置
     * @param array $array
     * @param index $fir_index 下标
     * @param index $sec_index 下标
     * @return
     */
    public static function exchange($array,$fir_index,$sec_index){
    	$_ = $array[$sec_index];
		$array[$sec_index] = $array[$fir_index];
		$array[$fir_index] = $_;
		return $array;
    }
    
    
    /**
     * 在数组指定位置插入一个元素
     * @param array $array
     * @param mixed $value
     * @param int $position 
     * @return array
     */
    public static function insert($array,$value,$position=0){
    	foreach($array as $k=>$v){
    		$result[$k] = $v;
    	}
    	$fore = ($position<1) ? array() : array_splice($array,0,$position);
		$fore[] = $value;
   		return array_merge($fore,$array);
    }
    
    
    public static function del($array,$position=0){
    	$fore = ($position<1) ? array() : array_splice($array,0,$position);
    	$array = array_splice($array,$position+1);
    	return array_merge($fore,$array);
    }
}

?>