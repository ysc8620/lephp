<?php 
class tools_file{
	/**
	 * 文件类型
	 * @var array
	 */
	static $file_ext = array(
		'application/octet-stream'		=>		'chm',
		'application/vnd.ms-powerpoint'	=>		'ppt',
		'application/vnd.ms-excel'		=>		'xls',
		'application/msword'			=>		'doc',
		'application/octet-stream'		=>		'exe',
		'application/octet-stream'		=>		'rar',
		"javascript/js"					=>		'js',
		"text/css"						=>		'css',
		"application/pdf"				=>		'pdf',
		"application/postsrcipt"		=>		'ai',
		"application/zip"				=>		'zip',
		"image/gif"						=>		'gif',
		"image/pjpeg"					=>		'jpg',
		"image/jpeg"					=>		'jpg',
		"image/png"						=>		'png',
		"text/plain"					=>		'txt',
		"text/html"						=>		'html',
		"video/mpeg"					=>		'mpg',
		"application/x-shockwave-flash"	=>		'swf'
	);
	
	/**
	 * 获取文件后缀
	 * @param str $mine
	 */
	static function get_ext($file_name){
		$extend =explode("." , $file_name);
		$va=count($extend)-1;
		return $extend[$va];
		/* if(isset(self::$file_ext[$mine])){
			return strtolower(self::$file_ext[$mine]);
		}
		return false; */
	}
	

	
	
	/**
	 * 下载一个文件
	 * @param str $file_name 文件路径 
	 * @param array $allowed 允许下载的文件类型
	 */
	static function download($file_name,$allowed=array('txt','doc','pdf','rar')){
		if(!file_exists($file_name)){
			throw new core_exception(902004);
		}
		
		//获取文件后缀
	//	$mine = mime_content_type($file_name);
		$ext = self::get_ext($file_name);
		
		//该类型文件不允许被下载
		core_assert::true(self::check_ext($ext,$allowed),902002);
		
		$fn = array_pop( explode( '/', strtr($file_name, '\\', '/' ) ) );
		header( "Pragma: public" );
		header( "Expires: 0" ); // set expiration time
		header( "Cache-Component: must-revalidate, post-check=0, pre-check=0" );
		header( "Content-type:" . $mine);
		header( "Content-Length: " . filesize($file_name) );
		header( "Content-Disposition: attachment; filename=\"$fn\"" );
		header( 'Content-Transfer-Encoding: binary' );
		readfile($file_name);
		return true;
	}
	
	/**
	 * 上传文件
	 * @param str $form_name 上传表单名称
	 * @param str $type 上传文件类型，需要在upload.config中预定义
	 * @param str $to_path 特定上传目录，默认为null
	 * @return array $st 返回去除上传文件根目录的相对路径。如/pic/food/2012/12/12/12/1111.jpg
	 */
	static function upload($form_name,$type,$to_path=null){
	    $config = core_config::get("upload.".$type);
		//允许上传的文件类型
	    $allow_ext = $config['allow'];
	    //重新命名文件
		$rename = $config['rename'];
		//散列存储
		$hash_path = $config['hash_path'];
		
		if($hash_path){
		    $hash_path = date('Y/m/d/H/',time());
		}else{
		    $hash_path = '/';
		}
		
		foreach($_FILES[$form_name]['tmp_name'] as $k=>$tmp_file){
			$ext = tools_file::get_ext($_FILES[$form_name]['name'][$k]);
			core_assert::true(self::check_ext($ext,$allow_ext),902002);
			if($rename){
    			$file_name = date('is',time()) . tools_range::mackcode(3,'NS'). '.' . $ext;
			}else{
			    $file_name = $_FILES[$form_name]['name'][$k];
			}
			
			if($to_path){
				$return_name = $to_path .'/'. $file_name;
				$upload_path = $return_name;
			}else{
				$return_name = $config['path'] . $hash_path . $file_name;
				$upload_path = UPLOAD_PATH . $return_name;
			}

			//自动创建目录
			self::create_dir(dirname($upload_path));
			if(!is_uploaded_file($tmp_file)){
				throw new core_exception(902005);
			}
			if(!move_uploaded_file($tmp_file,$upload_path)){
				throw new core_exception(902005);
			}
			$st[] = $return_name;
		}
		return $st;
	}
	
	
	/**
	 * 格式化路径格式
	 * @param unknown_type $path
	 * @return mixed
	 */
	static function format_path($path){
	    $a = explode("/",str_replace(array(' ',"/./"), array('','/'), $path));
	    //print_r($a);
	    for($z=0;$z<sizeof($a);$z++){
	        if($a[$z]==".."){
	            if(!$a[$z-1]){
	                //@todo
	                echo 'cuowu';
	            }
	            unset($a[$z]);
	            unset($a[$z-1]);
	            //echo implode('/',$a) ."$z <br />";
	            $path=self::format_path(implode('/',$a));
	            break;
	        }
	    }
	    return ($path);
	}

	
	/**
	 * 跨域文件传输
	 * @param str $from_file
	 * @param str $to_file
	 * @param array $allow_ext
	 * @param str $upload_url
	 */
	static function cross_upload($form_name,$type,$upload_url=null){
        if($upload_url == null){
            $upload_url = core_config::get('base.image_server');
        }

		//@todo 文件类型检查
		//先将文件上传至本地临时目录
		$to_path = TMP_UPLOAD_PATH;
		$files = self::upload($form_name,$type,$to_path);
		foreach($files as $k=>$f){
			$file_mine = mime_content_type($f);
			$fields['file['.$k.']'] =  '@'.realpath($f) .";type=".$file_mine;
		}
		
		//上传文件类型，需要在upload文件中预定义
		$fields['type'] = $type;
		
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $upload_url);
	    //@todo 文件大小
	    curl_setopt($ch, CURLOPT_INFILESIZE, $upload_url);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields );
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//不直接输出，返回到变量
	    curl_setopt($ch, CURLOPT_TIMEOUT, 5);	// 函数执行时间
	    $content  = curl_exec($ch);       //可以将$content打印出来调试
		curl_close($ch);
		if($files){
			foreach($files as $k=>$f){
				@ unlink($f);    //删除临时文件
			}
		}
		//接收地址返回类型如：json_encode();
	    $data = json_decode($content, TRUE);
		if (!$data['code']=='10000') {
		    return FALSE;
		}
		return $data['data'];
	}
	
	/**
	 * 新建目录
	 * @param $path 待创建目录
	 * @return
	 */
    static public function create_dir($path,$mod=0755){
		$realpath = (str_replace('//', '/', $path));
		if(!$realpath or file_exists($realpath)){
			return true;
		}
		try{
			mkdir($realpath,$mod,true);
		}catch (ErrorException $e){
			throw $e;
		}
	}
	
	/**
	 * 将内容写入文件（自动建立目录）
	 * @param $fileName
	 * @param $content
	 */
	static public function to_file($file_name,$content,$mode="w+"){
		$path_parts = pathinfo($file_name);
		self::create_dir($path_parts['dirname']);

		$fp = fopen($file_name,$mode);
		if(!fwrite($fp,$content)){
		   throw new core_exception(902003);
		   return false;
		}
		fclose($fp);
		return true;
	}

    /**
     * 日志记录
     * @param $file_name
     * @param $content
     * @return bool
     * @throws core_exception
     */
    static public function to_log($file_name, $content){
        $fp = fopen( $file_name, 'a' );
        if (flock($fp, LOCK_EX)) { // 进行排它型锁定
            $res = fwrite($fp,$content."\r\n");
            flock($fp, LOCK_UN); // 释放锁定
        }
        fclose($fp);
        if( !$res ){
            throw new core_exception(902003);
            return false;
        }
        return true;
    }

	/**
	 * 检查后缀名是否被允许
	 * @param 提交表单名 $form_name
	 * @param array $allow_ext
	 */
	static public function check_ext($ext,$allow_ext=array()){
		if(!@in_array(strtolower($ext),$allow_ext)){
			return false;
		}
		return true;
	}
	
	
	/**
	 * 获取远程文件，并保存到本地
	 * @param str $url 来源文件地址
	 * @param str $type 上传文件类型，通过config/upload配置
	 * @param bool $re_name  是否重命名，默认为false
	 * @param str $to_path 指定上传路径，默认为系统匹配
	 */
	function upload_by_url($url,$type,$to_path=null){
		$config = core_config::get("upload.".$type);
		//允许上传的文件类型
	    $allow_ext = $config['allow'];
	    //重新命名文件
		$rename = $config['rename'];
		//散列存储
		$hash_path = $config['hash_path'];
		
		if($hash_path){
		    $hash_path = date('Y/m/d/H/',time());
		}else{
		    $hash_path = '/';
		}


		$file_name = basename($url);
		$ext = tools_file::get_ext($file_name);

		if($rename){
			$file_name = date('is',time()) . tools_range::mackcode(3,'NS'). '.' . $ext;
		}
		core_assert::true(self::check_ext($ext,$allow_ext),902002);
		if($to_path){
			$return_name = $to_path . $file_name;
			$upload_path = $return_name;
		}else{
			$return_name = $config['path'] . $hash_path . $file_name;
			$upload_path = UPLOAD_PATH . $return_name;
		}
		//自动创建目录
		self::create_dir(dirname($upload_path));
		$rs =  @copy($url,$upload_path);
		if($rs){
			return $return_name ; 
		}
		return false;
	}
	
	
	/**
	 * 获取远程文件内容
	 * @param unknown_type $remote_url
	 * @param unknown_type $is_proxy
	 */
	function get_content_by_url($remote_url,$is_proxy=false){
		
		//需要返回的user_agent信息 如“baiduspider”或“googlebot”，伪装成为谷歌或百度蜘蛛
		$user_agent = 'baiduspider+(+http://www.baidu.com/search/spider.htm)';
		//来源地址，用于欺骗服务器进行来源伪造
		$referer_url = 'http://www.baidu.com';
		
		//初始化curl
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $remote_url);
		curl_setopt ($ch, CURLOPT_USERAGENT, $user_agent);
		curl_setopt ($ch, CURLOPT_REFERER, $referer_url);		
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		
		curl_setopt($ch, CURLOPT_NOPROGRESS, false); //是否关闭进度条，调试时不关闭
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 3000); //等待时间，设置为3秒
		//向远程地址发送数据
		//curl_setopt($ch, CURLOPT_POST, 1);  设置为post方式请求
		//$data = array('name' => 'Foo', 'file' => '@/home/user/test.png');
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if($is_proxy){
			//获取代理服务器信息
			//以下代码设置代理服务器
			//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true); //是否启用代理 是否开启http隧道
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true); //是否启用代理 是否开启http隧道
			curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
			curl_setopt($ch, CURLOPT_PROXY, '41.196.22.244'); //代理服务器地址
			curl_setopt($ch, CURLOPT_PROXYPORT, 80); //代理服务器端口
			//curl_setopt($ch, CURLOPT_PROXYUSERPWD, ':'); //http代理认证帐号，username:password的格式，这里既然是socket5模式就打开
			curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5); //代理模式，这里用socket5的方式
		}
		
		
		$content = curl_exec ($ch);
		curl_close($ch);
		
		//获取返回信息
		$return_msg = curl_getinfo($ch);
		
		//返回正常
		if($return_msg['http_code'] ==200){
			return $content;
		}else{
			return '';
		}
	}
	
	
	/**
	 * 逐行读取文件
	 * @param unknown_type $file
	 * @return string
	 */
	static function get_lines_from_file($file){
	    if(!file_exists($file)){
	        return false;
	    }
	    if(!is_readable($file)){
	        return false;
	    }
	    $handle = @fopen($file, "r");
	    if ($handle) {
	    	while (($buffer = fgets($handle, 4096)) !== false) {
	    		$rs[] = $buffer;
	    	}
	    	if (!feof($handle)) {
	    		echo "Error: unexpected fgets() fail\n";
	    	}
	    	fclose($handle);
	    }
	    return $rs;
	}
	
	/**
	 * 获取目录下文件,不包括子目录
	 * 
	 * @param unknown_type $dir
	 */
	static function get_files($dir,$next_all=false){
	    $handler = opendir($dir);
	    $files['file'] = $files['dir'] = array();
	    while (($filename = readdir($handler)) !== false) { //务必使用!==，防止目录下出现类似文件名“0”等情况
	        if ($filename != "." && $filename != "..") {
	            $file_path = $dir . '/' . $filename;
	            if(!is_dir($file_path)){
	                $infos = stat($file_path);
	                $_file = pathinfo($file_path);
	                $files['file'][] = array(
	                    'file_name'    =>    $filename,
	                    'file_path'    =>    $dir . '/' . $filename,
	                    'file_size'    =>    $infos['size'],
	                    'last_viewtime'=>    $infos['atime'],
	                    'last_uptime'  =>    $infos['mtime'],
	                    'last_uptime'  =>    $infos['mtime'],
	                    'file_ext'     =>    $_file['extension']
	                );
	            }else{
	                $files['dir'][] = array(
	                    'file_name'    =>    $filename,
	                        'file_path'    =>    $dir . '/' . $filename,
	                ) ;
	                if($next_all){
    	                $_files = self::get_files($dir . '/' . $filename,$next_all);
    	                $files['dir'] = array_merge($files['dir'],$_files['dir']);
    	                $files['file'] = array_merge($files['file'],$_files['file']);
	                }
	            }
	        }
	    }
	    closedir($handler);
	    return $files;
	}
	
	/**
	 * 删除文件(文件夹)
	 * @param unknown_type $file_name
	 */
	static function remove($file_name){
	    if(is_dir($file_name)){
	        $files = self::get_files($file_name);
	        if($files['file']){
	            foreach($files['file'] as $f){
	                unlink($f['file_path']);
	            }
	        }
	        if($files['dir']){
	            foreach($files['dir'] as $f){
	                self::remove($f['file_path']);
	            }
	        }
	        rmdir($file_name);
	        return true;
	    }else{
	        return unlink($file_name);
	    }
	}
	
	
	/**
	 * 移动文件（重命名）
	 * @param unknown_type $from
	 * @param unknown_type $to_file
	 */
	static function move_file($from_file,$to_file){
	    if(is_dir($from_file)){
	        $files = self::get_files($from_file);
	        if($files['file']){
	            foreach($files['file'] as $f){
	                $to_file_name = str_replace($from_file,$to_file,$f['file_path']);
	                $path_parts = pathinfo($to_file_name);
	                self::create_dir($path_parts['dirname']);
	                rename($f['file_path'],$to_file_name);
	            }
	        }
	        
	        if($files['dir']){
	            foreach($files['dir'] as $d){
	                $to_dir_name = str_replace($from_file,$to_file,$d['file_path']);
    	            self::move_file($d['file_path'],$to_dir_name);
	            }
	        }
	        return true;
	    }else{
    	    return rename($from_file,$to_file);
	    }
	}


	static function copy_file($from_file,$to_file){
		//自动创建目录
		self::create_dir(dirname($to_file));
		$rs =  @copy($from_file,$to_file);
		if($rs){
			return $to_file ; 
		}
		return false;
	}
}
?>