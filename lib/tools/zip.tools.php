<?php 
/**
 * 压缩/解压缩类
 * 
 * @author aloner
 * 
 * 应用示例：
 * 
 * 压缩：tools_zip::zip($from_path,$zip_filename);
 * 解压：tools_zip::unzip($zip_file,$to_dir);
 *
 */


class tools_zip{
    
    //构造函数
    public function __construct(){}
    
    
    static function zip($from_path,$zip_filename){
        try{
            if(file_exists($zip_filename)){
                throw new core_exception('','tools_zip:'."zip file is exists");
            }
            
            $files = array();
            self::get_files($from_path,$files);
            
            $zip = new ziparchive();
            $res = $zip -> open($zip_filename,ziparchive::CREATE);
            if($res === true){
                if(empty($files)){
                    $zip -> addemptydir($from_path);
                }else{
                    foreach($files as $value){
                        if(is_dir($value)){
                            $zip -> addemptydir($value);
                        }else{
                            $zip -> addfile($value,$value);
                        }
                    }
                }
                $zip->close();
           }
           return true;
        }catch(core_exception $e){
            throw $e;
        }
    }
    
    //获取文件
    static private function get_files($dir,&$files=array()){
        $temp = scandir($dir,0);
        foreach($temp as $value){
            if($value !="." && $value!=".."){
                $make_path = $dir."/".$value;
                if(is_dir($make_path)){
                    self::get_files($make_path,$files);
                }
                $files[]=$make_path;
            }
        }
    }
   
    
    //解压
    static function unzip($zip_file,$to_dir){
        try{
            tools_file::create_dir($to_dir);
            $zip = new ziparchive();
            if($zip -> open($zip_file) === true){
                $zip -> extractto($to_dir);
                $zip -> close();
            }
            return true;
        }catch(core_exception $e){
            throw $e;
        }
    }
}
?>