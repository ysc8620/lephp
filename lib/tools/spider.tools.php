<?php
/**
 * 爬虫工具类
 * @author aloner
 *
 */ 
class tools_spider{
	
	
	/**
	 * 从指定地址获取内容
	 * @param str $url
	 */	
	function http_request($remote_url,$is_proxy=false){
		//获取蜘蛛列表，并随即选择一个用以伪造
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
		//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 3000); //等待时间，设置为3秒
		//向远程地址发送数据
		//curl_setopt($ch, CURLOPT_POST, 1);  设置为post方式请求
		//$data = array('name' => 'Foo', 'file' => '@/home/user/test.png');
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if($is_proxy){
			//获取代理服务器信息
			$proxy_list = '';
			//以下代码设置代理服务器
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true); //是否启用代理 是否开启http隧道
			curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
			curl_setopt($ch, CURLOPT_PROXY, '41.196.22.244'); //代理服务器地址
			curl_setopt($ch, CURLOPT_PROXYPORT, 80); //代理服务器端口
			//curl_setopt($ch, CURLOPT_PROXYUSERPWD, ':'); //http代理认证帐号，username:password的格式，这里既然是socket5模式就打开
			curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5); //代理模式，这里用socket5的方式
		}
		
		$content = curl_exec ($ch);
		//获取返回信息
		$return_msg = curl_getinfo($ch);
		curl_close($ch);
		
		//返回正常
		if($return_msg['http_code'] ==200){
			return $content;
		}else{
			return $return_msg['http_code'];
		}
	}
	
	
	/**
	 * 从内容中获取地址
	 * @param unknown_type $content
	 * @param unknown_type $url_rule
	 * @param unknown_type $rule_must
	 * @param unknown_type $url_cannot
	 * @param unknown_type $replace
	 */
	static function parse_url_from_content($base_url,$content){
		$href_parter = "/<a[\s\S]*href\s*=\s*[\"\']([^\"\']*)[\"\']/imsU";
		preg_match_all($href_parter,$content,$a);
		$url = null;
		if($a[1]){
			$url = self::repair_url($base_url, $a[1]);
		}
		if($url){
			$url = array_unique($url);
		}
		if($url){
			return array_filter($url);
		}
		return null;
	}
	
	/**
	 * 根据规则筛选URL
	 * @param unknown_type $rule
	 * @param unknown_type $urls
	 */
	static public function url_filter($urls,$url_rule=null,$rule_must=null,$url_cannot=null,$url_replace=null){
		if(!$url_rule){
			return $urls;
		}
		if(!$urls){
			return null;
		}
		$url = preg_grep("|".$url_rule."|", $urls);
		if($url_replace){
			$url_replace = explode(',',$url_replace);
			foreach($url_replace as $_){
				$__ = explode('|',$_);
				$patterns[] = "/" . $__[0] . "/";
				$replace[] = $__[1];
			}
		}
		if($url){
			foreach($url as $u){
				$_u = $u;
				if($rule_must){
					$_u = preg_replace("/" . $rule_must . "/", '', $u);
					if($_u == $u){
						continue;
					}
				}
				
				if($url_cannot){
					$_u = preg_replace("/" . $url_cannot . "/", '', $u);
					if($_u != $u){
						continue;
					}
				}
				if($url_replace){
					$_u = preg_replace($patterns,$replace, $_u);
				}
				$result[] = $_u;
			}
		}
		if($result){
			$result = array_unique($result);
		}
		
		return $result;
	}
	
	static public function repair_url($from_url,$src_url){
		if(is_array($src_url)){
			foreach($src_url as $u){
				$rs[] = self::_repair_url($from_url, $u);
			}
		}else{
			$rs = self::_repair_url($from_url, $src_url);
		}
		return $rs;
	}
	
	
	static private function _repair_url($from_url,$src_url){
		$from_url = parse_url($from_url);
		$base_url = $from_url['scheme'].'://'.$from_url['host'];
		
		$_src = parse_url($src_url);
		if(isset($_src['scheme'])) {
			if($_src['scheme'] == 'javascript'){
				return null;
			}else{
				return $src_url;
			}
		}
		if(substr($_src['path'], 0, 1) == '/') {
			$path = $_src['path'];
		}else{
			$path = dirname($from_url['path']).'/'.$_src['path'];
		}
		$rst = array();
		$path_array = explode('/', $path);
		if(!$path_array[0]) {
			$rst[] = '';
		}
		foreach ($path_array AS $key => $dir) {
			if ($dir == '..') {
				if (end($rst) == '..') {
					$rst[] = '..';
				}elseif(!array_pop($rst)) {
					$rst[] = '..';
				}
			}elseif($dir && $dir != '.') {
				$rst[] = $dir;
			}
		}
		if(!end($path_array)) {
			$rst[] = '';
		}
		$url .= implode('/', $rst);
		
		$return_url = $base_url . str_replace('\\', '/', $url);
		
		//参数
		if($_src['query']){
			$return_url .= "?".$_src['query'];
		}
		//锚点
		if($_src['fragment']){
			$return_url .= "#".$_src['fragment'];
		}
		return $return_url;
	}
	
	/**
	 * 从指定内容中解析目标数据
	 */
	function parse_tag_from_content($content,$start,$end,$regexp_pattern=null){
		//先获取匹配范围
		if($regexp_pattern){
			$regexp_pattern = "/" . str_replace('/','\/',$regexp_pattern) . "/imsU"; 
		}else{
			$regexp_pattern = "/" . str_replace('/','\/',(preg_quote($start) . "(.*)" . preg_quote($end))) . "/imsU";
		}
		//是否循环匹配
//		echo $regexp_pattern . "<br>";
		preg_match_all($regexp_pattern, $content, $m);
		
		//如果是单循环则只返回一个
		return $m[1][0];
	}
}
