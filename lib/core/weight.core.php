<?php
/**
 *
 */
class core_weight{
    static $_weight = 50;
    static $_full_weight = 100;

    /**
     * @param $title
     * @param array $keywords
     * @param array $ignore
     * @param string $charset
     * @return array|bool
     */
    static function weight($title, $keywords=array(), $ignore = array(), $charset='utf-8'){
        if (empty($title) or empty($keywords)){
            return False;
        }

        foreach($keywords as &$keyword){
            $key_list = core_weight::get_keywords($keyword['keyword'],$charset);
            if ($key_list){
                core_weight::countWeight($title,$key_list, $keyword, $ignore,$charset);
            }
        }

        $data = core_weight::sortWeight($keywords);
        return $data;
    }

    /**
     * 统计权重
     * @param $title
     * @param $key_list
     * @param $keyword
     * @param array $ignore
     * @return bool
     */
    static function countWeight($title, $key_list, &$keyword,$ignore=array(), $charset='utf-8'){

        // 完全匹配满权重
        foreach($ignore as $i){

            if($keyword['keyword'] == $title){
                $keyword['weight'] = core_weight::$_full_weight;
                return true;
            }

            if($i == $title){
                $keyword['weight'] = core_weight::$_full_weight * 0.8;
                return true;
            }

            if($i == $keyword['keyword']){

                $keyword['weight'] = core_weight::$_full_weight * 0.6;
                return true;
            }

            if(mb_strpos($keyword['keyword'], $title)>-1){
                $keyword['weight'] = core_weight::$_full_weight * 0.6;
                return true;
            }

            if(mb_strpos($title, $keyword['keyword'])>-1){
                $keyword['weight'] = core_weight::$_full_weight * 0.6;
                return true;
            }

        }

        // 消减相同部分权重
        foreach($ignore as $i){
            if(mb_strpos($keyword['keyword'], $i) > -1){
//                 print_r($keyword['keyword']);
//                print " , $i <br/>";
                $_weight = core_weight::$_weight - mb_strlen($i, $charset);
                break;
            }
        }

        $keyword['weight'] = $_weight;
        foreach ($key_list as $key){
            if(mb_strpos($title, $key) > -1){
                $keyword['weight'] += 1;
            }
        }
        return True;
    }

    /**
     * 获取关键字匹配列表
     * @param $keyword
     * @param string $charset
     * @return array
     */
    static function  get_keywords($keyword, $charset='utf-8'){
        $ks = array();
        // 切词处理
        //$klist = preg_split("/\s/i", $keyword);
        //foreach($klist as $k)
        {
            $last = '';
            for($i=0; $i<mb_strlen($keyword, $charset); $i++){
                $ktext = trim(mb_substr($keyword,$i, 1,$charset));
                if( empty( $ktext) )
                {
                    if($last){
                        $ks[] = $last;
                        $last = '';
                    }
                    continue;
                }
                if(preg_match("/[\x{4e00}-\x{9fa5}]+$/u",$ktext)){
                    $ks[] = $ktext;
                    if($last){
                        $ks[] = $last;
                    }
                    $last = '';
                }elseif(preg_match("/\w+$/",$ktext)){
                    $last .= $ktext;
                }else{
                    if($last){
                        $ks[] = $last;
                        $last = '';
                    }
                }
            }
            if($last){
                $ks[] = $last;
            }
        }
        return $ks;
    }

    /**
     * 整理权重
     * @param $keywords
     * @return array|bool
     */
    function sortWeight($keywords){
         if(empty($keywords)){
             return False;
         }
        $result = array();
        foreach($keywords as $v){
            if($v['weight'] > core_weight::$_weight){
                $result[] = $v;
            }
        }
        if(empty($result)){
            $result = $keywords;
        }
        foreach($result as &$k){
            unset($k['weight']);
        }
        return $result;
    }
}