<?php 
class tools_image{
	static $pic_server = array();
	static $pic_server_num;
	static $upload_file_type = 'goods';			//上传默认类型为图片，支持'gif','png','jpg'（可在upload.config中配置）
	
	/**
	 * 上传（一组）本地图片
	 * 表单必须以数组形式命名，如：file[]
	 * @param str $form_name 上传图片的表单名
	 * @param bool $type 图片类型
	 * @throws core_Exception
	 * @return array 图片ID数组
	 */
	static public function upload($form_name,$type,$topath=null){
		if(!$_FILES[$form_name]['tmp_name'][0]){
			return false;
		}
		$type = $type ? $type : self::$upload_file_type;
		$st = tools_file::upload($form_name,$type,$topath);
		//return str_replace(UPLOAD_PATH , '', $st);
		return $st;
	}
	
	/**
	 * 图片服务器自动负载均衡
	 * 根据图像来源获取服务器路径
	 * 此方法解决多图片服务器缓存问题
	 */
	static public function get_server($pic_origin){
		if(empty(self::$pic_server)){
			self::$pic_server = explode(',', core_config::get('base.image_server'));
		}
		if(empty(self::$pic_server_num)){
			self::$pic_server_num = count(self::$pic_server);
		}
		if(self::$pic_server_num==1){
			return self::$pic_server[0];
		}
		$_c = ord(substr($pic_origin,0,1)) + 0;
		$_n = $_c % self::$pic_server_num + 0;
		return self::$pic_server[$_n];
	}
	
	
	/**
	 * 存储图片二进制代码
	 * Enter description here ...
	 */
	static public function save(){
		
	}
	

	/**
	 * 从URL获取图片并保持到本地
	 * @param unknown_type $url
	 */
	static public function upload_by_url($url,$type,$to_path=null){
		$type = $type ? $type : self::$upload_file_type;
		$rs = tools_file::upload_by_url($url,$type,$to_path);
		return $rs;
	}
	
	/**
	 * 远程图片上传
	 * 需要预先定义远程文件接收地址
	 * @param str $form_name
	 * @param bool $type 图片类型 （需要预先配置config）
	 */
	static public function cross_upload($form_name='file',$type){
		if(!@core_config::get('base.image_server')){
			return false;
		}
		$pic_server = core_config::get('base.image_server');
		tools_file::cross_upload($form_name,$type,$pic_server[0]);
	}
	
	/**
	 * 根据需求获取图片
	 * @param str $origin 图片源文件
	 * @param bool $pic_type 图片规格（b,s,m）等
	 */
	static public function get_thumb_name($pic_origin,$pic_rule){
		$path = pathinfo($pic_origin);
		$to_file = strtolower( $path["dirname"] . '/' . $path["filename"]  . "_" . $pic_rule . "." . $path["extension"]);
		return $to_file;
	}
	
	
	/**
	 * 生成缩略图
	 * 在image.config.php中可配置生成图片缩略图的各种属性
	 * @param str $pic_origin 图片源文件
	 * @param str $pic_type 图片类型
	 */
	static public function make_thumb($pic_origin,$pic_type){
		try {
            if(!file_exists(PIC_UPLOAD_PATH . $pic_origin)){
                return false;
            }
			
			$image = new core_image();
			$image -> setSrcImg(PIC_UPLOAD_PATH . $pic_origin);
			if($pic_type){
				$to_file = self::get_thumb_name($pic_origin,$pic_type);
				$rule = core_config::get('image.'.$pic_type.".size");
				$size = @ explode ("_",$rule);
				$image -> setDstImg(PIC_VIEW_PATH . $to_file);
				$image -> createImg($size[0],$size[1]);
			}else{
				$a = tools_file::copy_file(PIC_UPLOAD_PATH . $pic_origin,PIC_VIEW_PATH . $pic_origin);
				$to_file = $pic_origin;
			}
			
		}catch (Exception $e){
			throw new core_exception_program(902101);
		}
		return $to_file;
	}
}
?>