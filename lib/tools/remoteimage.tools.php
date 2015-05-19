<?PHP
/**
 * 获取远程图片
 * @author aloner
 *
 */
class tools_remoteimage {

	public $tempDir;
	public $error;
	public $validExtensions;
	public $validType;
	public $minDimensions;
	public $maxDimensions;
	public $file = null;

	private $lang;		//$LANGUAGE_PACK
	private $set;		//settings
	private $source;	//url
	private $getImageSize; //getImageSize(source)

	function __construct($tempDir) {
		$this->tempDir	= $tempDir;
		//$this->set		= Comm_config::get('set');
		//$this->lang		= Comm_config::get('lang');
	}

	public function getImage($url,$type='fopen'){
		$url = $this->input($url);
		if(empty($url)){
			return false;
		}

		$this->source = $url;
		$this->imgTempName= $this->tempDir.rand(0,7).time().rand(0,7).'.'.strtolower(substr($this->source, -3));

		if(function_exists('curl_init')){
			$type='curl';
		}

	// connection was made to server at domain example.com
		if($type == 'fopen' && $fp = @fopen($this->source, 'r')){
			fclose($fp);
			$this->imgSize		= $this->get_remote_file_size($url);
			$this->getImageSize = @getImageSize($this->source);
			if(!$this->check()){
				return false;
			}
			if($this->saveImageFopen($this->source, $this->imgTempName)){
				$this->file = $this->imgFileArray();
				return true;
			}
		}
	//cURL
		elseif($this->saveImageCURL($this->source, $this->imgTempName)){
		
			$this->imgSize		= filesize($this->imgTempName);
			$this->getImageSize = @getImageSize($this->imgTempName);

			if(!$this->check()){
				unlink($this->imgTempName);// remove file
				return false;
			}
			$this->file	= $this->imgFileArray();
			return true;
		}
	// error no file
		$this->error = $this->lang["site_upload_err_no_image"];
		return false;
	}

	// Upload checks
	private function check(){
		$filePieces	= pathinfo($this->source);
		if(!$this->checkDimensions($this->getImageSize)) return false;
		if(!$this->checkFileSize($this->imgSize)) return false;
		if(!$this->checkFileType($filePieces['extension'],$this->getImageSize['mime'])) return false;
		return true;
	}

	private function get_remote_file_size($url){
		$parsed = parse_url($url);
		$host = $parsed["host"];
		$fp = @fsockopen($host, 80, $errno, $errstr, 20);
		if(!$fp){
			return false;
		}else {
			@fputs($fp, "HEAD $url HTTP/1.1\r\n");
			@fputs($fp, "HOST: $host\r\n");
			@fputs($fp, "Connection: close\r\n\r\n");
			$headers = "";
			while(!@feof($fp))$headers .= @fgets ($fp, 128);
		}
		@fclose ($fp);
		$return = false;
		$arr_headers = explode("\n", $headers);
		foreach($arr_headers as $header) {
			// follow redirect
			$s = 'Location: ';
			if(substr(strtolower ($header), 0, strlen($s)) == strtolower($s)) {
				$url = trim(substr($header, strlen($s)));
				return $this->get_remote_file_size($url);
			}
			// parse for content length
			$s = "Content-Length: ";
			if(substr(strtolower ($header), 0, strlen($s)) == strtolower($s)) {
				$return = trim(substr($header, strlen($s)));
				break;
			}
		}
		return $return;
	}

	//Download images from remote server
	private function saveImageFopen($inPath,$outPath){ 
		$in	= fopen($inPath, "rb");
		$out= fopen($outPath, "wb");
		while($chunk = fread($in,8192)){
			fwrite($out, $chunk, 8192);
		}
		fclose($in);
		fclose($out);
		return((bool) file_exists($outPath));
	}

	private function saveImageCURL($inPath,$outPath){
		$ch = curl_init($inPath);
		$fp = fopen($outPath, "wb");

	// set URL and other appropriate options
		$options = array(CURLOPT_FILE => $fp,
						 CURLOPT_HEADER => 0,
						 CURLOPT_FOLLOWLOCATION => 1,
						 CURLOPT_TIMEOUT => 60); // 1 minute timeout (should be enough)

		curl_setopt_array($ch, $options);

		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		return((bool) file_exists($outPath));
	}

	private function input($in){
		$in = trim($in);
		if (strlen($in) == 0)
			return;
		return htmlspecialchars(stripslashes($in));
	}

	//check size(pixels)
	private function checkDimensions($getImgSize){
	//min size
		if ($getImgSize[0] < $this->minDimensions ||
			$getImgSize[1] < $this->minDimensions ){
			$this->error = sprintf($this->lang["site_upload_to_small"],' '.$this->minDimensions.'x'.$this->minDimensions);
			return false;
		}

	// max size
		if ($getImgSize[0] > $this->maxDimensions ||
			$getImgSize[1] > $this->maxDimensions ){
			$this->error = sprintf($this->lang["site_upload_to_big"],$this->maxDimensions.'x'.$this->maxDimensions);
			return false;
		}
		return true;
	}

	//Check file size (kb)
	private function checkFileSize($fileSize){
		if($fileSize >= $this->set['SET_MAXSIZE']){
			$this->error = sprintf($this->lang["site_upload_size_accepted"],format_size($this->set['SET_MAXSIZE']));
			return false;
		}
		return true;
	}

	//check file type
	private function checkFileType($ext,$type){
		$ext = explode("?", $ext);
		$ext = $ext[0];
		if (empty($ext) ||
			!(in_array(strtolower($ext), $this->validExtensions)) ||
			!(in_array($type, $this->validType))){
			$this->error = sprintf($this->lang["site_upload_types_accepted"],implode(", ",$this->validExtensions)).'<br/>'.'file_type = '.$type.' / extension = '.$ext.'<br/>'; // bebug file type strtolower($_FILES["file"]["type"]);
			return false;
		}
		return true;
	}
	
	//make image file array $_FILES
	private function imgFileArray(){
		$_FILES = Array (
			'file' => Array (
				'name' => Array ( basename($this->source)),
				'type' => Array ( $this->getImageSize['mime'] ),
				'tmp_name' => Array ( $this->imgTempName ),
				'error' => Array ( 0 ),
				'size' => Array ( $this->imgSize )
			)
		);
		return $_FILES;
	}
}