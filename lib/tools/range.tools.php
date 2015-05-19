<?php
/**
 * 获取随机码
 * Enter description here ...
 * @param int $num 随机码长度
 * @param str $type 随机码类型   N=>仅数字   S=>仅字母   NS=>字母数字混排，不区分大小写  NSI=>区分大小写
 */
class tools_range{

    static function mackcode($num,$type='NS'){
        //去除0,1,o,l
        $result = '';
        switch($type){
            case "N":
                return rand(pow(10, $num-1),pow(10, $num)-1);
            case "S":
                $haystack = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
                for($i=1;$i<=$num;$i++){
                    $result .= $haystack[array_rand($haystack,1)];
                }
                return $result;
            case "SI":
                $haystack = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
                for($i=1;$i<=$num;$i++){
                    $result .= $haystack[array_rand($haystack,1)];
                }
                return $result;
            case "NS":
                $haystack = array('2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z');

                for($i=1;$i<=$num;$i++){
                    $result .= $haystack[array_rand($haystack,1)];
                }
                return $result;
            case "NSI":
                $haystack = array('2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','J','K','L','M','N','P','Q','R','S','T','U','V','W','X','Y','Z');
                for($i=1;$i<=$num;$i++){
                    $result .= $haystack[array_rand($haystack,1)];
                }
                return $result;
            default:
                return false;
                break;
        }
    }
}
?>