<?php
class core_page{

	public $total_num;                    //纪录总数量
	public $total_page;                   //总页数
	public $curr_num ;                    //当前页码

	public $total_model = "共有%TOTAL_NUM%条纪录 每页显示%PER_NUM%条 共%TOTAL_PAGE%页";

	private $per_num = 10;                //每页显示纪录数量
	private $start_num;
	private $end_num;
	private $first_num = '1';
	private $last_num;
	private $model = 1;        //1=>普通模式  2=>上下页模式   3=>

	public $show_config = array(
		'pre_page'		=>	'&lsaquo;&lsaquo;',
		'next_page'		=>	'&rsaquo;&rsaquo;',
		'first_page'	=>	'第一页',
		'last_page'		=>	'最后页',
		'max_page'		=>	false,
		'key'			=>	'p',
		'per_num'		=>	'20',		//每页显示记录数
		'show_num'		=>	'10',		//显示几个页码
		"url"			=>	"",
		'showType'		=>	1,
	);


	function __construct($total_num,$per_num,$key=null,$max_page=null){
		$this -> total_num = $total_num;
		if($per_num){
			$this -> per_num = $per_num;
		}
		if($key){
			$this -> key = $key;
		}
		if($max_page){
			$this -> max_page = $max_page;
		}


		$this -> last_num = ceil($this -> total_num / $this -> per_num);
		$this -> total_page = $this -> last_num;
		if($this -> max_page && $this -> last_num >= $this -> max_page){
			$this -> last_num = $this -> max_page;
		}
		$curr_num = intval($_GET[$this -> key]);

		$this -> curr_num = $curr_num > 0 ? $curr_num : 1 ;
		if($this -> curr_num > $this -> max_page && $this -> max_page){
			$this -> curr_num = $this -> max_page;
		}
	}


	function __get($arg){
		if(isset($this -> show_config[$arg])){
			return $this -> show_config[$arg];
		}
		echo '分页函数出错，文件名为 <b>'.__FILE__.'</b> <br/>错误信息：您调用的变量（'.$arg.'）不存在！';
	}

	function __set($arg,$val){
		if(isset($this -> show_config[$arg])){
			$this -> show_config[$arg] = $val;
		}else{
			echo '分页函数出错，文件名为 <b>'.__FILE__.'</b> <br/>错误信息：您所赋值的变量（'.$arg.'）不存在！';
		}
	}


	function set($show_config=null){
		if(is_array($show_config)){
			foreach($show_config as $k => $val){
				$this -> $k = $val;
			}
		}
	}

	/**
	 * 显示分页
	 * @param 显示模式 $model 1=>普通模式 2=>上下页模式
	 * @param bools $show_total 是否显示统计信息
	 * @return string
	 */
	function show($model=1){
		$this -> end_num = $this -> curr_num + ceil($this -> show_num/2);
		if($this -> end_num > $this -> last_num){
			$this -> end_num = $this -> last_num;
		}
		$this -> start_num = $this -> end_num - $this -> show_num;
		if($this -> start_num < 1){
			$this -> start_num = 1;
			if($this -> end_num < $this -> last_num){
				$this -> end_num = $this -> start_num + $this -> show_num - 1;
			}
			if($this -> end_num > $this -> last_num){
				$this -> end_num = $this -> last_num;
			}
		}
		$page = '';

		//@todo  统计信息



		for($i = $this -> start_num; $i<= $this -> end_num;$i++){
			if($i == $this -> curr_num){
				$page .= '<span class=\'curr\'>'.$i.'</span>';
			}else{
				$page .= '<a href=\''.$this -> setUrl($i).'\'>'.$i.'</a>';
			}
		}
		return "<div data-role='bx-page'>" . $this -> first_page() . $this -> pre_page() . $this -> preMore() . $page . $this -> nextMore() . $this -> next_page() . $this -> last_page() . "</div>";
	}


	//显示统计信息
	function show_total(){
	    $str = str_replace ( array ('%TOTAL_NUM%', '%TOTAL_PAGE%', '%PER_NUM%'), array ($this -> total_num, $this -> total_page, $this -> per_num), $this -> total_model );
	    return $str;
	}

	function show_other(){
		$this -> end_num = $this -> curr_num + ceil($this -> show_num/2);
		if($this -> end_num > $this -> last_num){
			$this -> end_num = $this -> last_num;
		}
		$this -> start_num = $this -> end_num - $this -> show_num;
		if($this -> start_num < 1){
			$this -> start_num = 1;
			if($this -> end_num < $this -> last_num){
				$this -> end_num = $this -> start_num + $this -> show_num - 1;
			}
			if($this -> end_num > $this -> last_num){
				$this -> end_num = $this -> last_num;
			}
		}


		$page = '';
		for($i = $this -> start_num; $i<= $this -> end_num;$i++){
			if($i == $this -> curr_num){
				$page .= '<span class=\'curr\'>'.$i.'</span>';
			}else{
				$page .= '<a href=\''.$this -> setUrl($i).'\'>'.$i.'</a>';
			}
		}
		return  $this -> pre_page() . $this -> preMore() . $page . $this -> nextMore() . $this -> next_page() . $this -> last_page();
	}


	//当前页
	function curr_page(){
		return '<span class=\'curr\'>'.$this -> curr_num.'</span>';
	}


	//上一页
	function pre_page(){
		if($this -> curr_num != '1'){
			return '<a href=\''.$this -> setUrl($this -> curr_num - 1).'\'>' . $this -> pre_page .'</a>';
		}
	}

	//下一页
	function next_page(){
		if($this -> curr_num != $this -> last_num && $this -> last_num){
			return '<a href=\''.$this -> setUrl($this -> curr_num + 1).'\'>'. $this -> next_page .'</a>';
		}
	}

	//第一页
	function first_page(){
		if($this -> curr_num != 1){
			return '<a href=\''.$this -> setUrl(1).'\'>'.$this -> first_page.'</a>';
		}else{
			return '<span>'.$this -> first_page.'</span>';
		}
	}

	//最后页
	function last_page(){
	if($this -> curr_num != $this -> last_num && $this -> last_num){
			return '<a href=\''.$this -> setUrl($this -> last_num).'\'>'.$this -> last_page.'</a>';
		}else{
			return '<span>'.$this -> last_page.'</span>';
		}
	}

	//前面的省略页
	function preMore(){
		if($this -> start_num != $this -> first_num){
			return '<span class=\'more\'>...</span>';
		}
	}

	//后面的省略页
	function nextMore(){
		if($this -> last_num != $this -> end_num){
			return '<span class=\'more\'>...</span>';
		}
	}


	function setUrl($page_num){
		if($this -> url){//手动设置
            $url = $this -> url.((stristr($url,'?'))?'&':'?') . $this -> key . "=";
        }else{//自动获取
            if(empty($_SERVER['QUERY_STRING'])){//不存在QUERY_STRING时
               $url = $_SERVER['REQUEST_URI'] . "?" . $this -> key . "=";
            }else{
                if(stristr($_SERVER['QUERY_STRING'],$this -> key . '=')){ //地址存在页面参数
                    $url  = str_replace($this -> key . '=' . $this -> curr_num , '' , $_SERVER['REQUEST_URI']);
					$last = substr($url,-1,1);
                    if($last=='?'||$last=='&'){
                        $url .= $this -> key . "=";
                    }else{
                        $url .= '&' . $this -> key ."=";
                    }
                }else{
                	if(!stristr($_SERVER['REQUEST_URI'],'?')){
                		$url = $_SERVER['REQUEST_URI'] . '?' . $this -> key . '=';
                	}else{
	                    $url = $_SERVER['REQUEST_URI'] . '&' . $this -> key . '=';
                	}
                }
            }
        }
        return $url . $page_num;
	}


	public function getPageObject(){
		$this -> end_num = $this -> curr_num + ceil($this -> show_num/2);
		if($this -> end_num > $this -> last_num){
			$this -> end_num = $this -> last_num;
		}
		$this -> start_num = $this -> end_num - $this -> show_num;
		if($this -> start_num < 1){
			$this -> start_num = 1;
			if($this -> end_num < $this -> last_num){
				$this -> end_num = $this -> start_num + $this -> show_num - 1;
			}
			if($this -> end_num > $this -> last_num){
				$this -> end_num = $this -> last_num;
			}
		}

		return $this;
	}


	//获取分页列表地址
	public function get_page_list(){
		$result = array();

		for($i = $this -> start_num; $i<= $this -> end_num;$i++){
			if($i == $this -> curr_num){
				$result['curr'] = $i;
			}else{
				$result[$i] = $this -> setUrl($i);
			}
		}

		return $result;
	}


	//得到总页数
	public function get_page_total(){
		return $this -> total_page?$this -> total_page:0;
	}


	//得到总记录数
	public function get_num(){
		return $this -> total_num?$this -> total_num:0;
	}


	//上一页URL地址
	public function pre_page_url(){
		$result = false;
		if($this -> curr_num != '1'){
			$result = $this -> setUrl($this -> curr_num - 1);
		}

		return $result;
	}

	//下一页URL地址
	public function next_page_url(){
		$result = false;
		if($this -> curr_num != $this -> last_num && $this -> last_num){
			$result = $this -> setUrl($this -> curr_num + 1);
		}

		return $result;
	}

	//第一页URL地址
	public function first_page_url(){
		$result = false;
		if($this -> curr_num != 1){
			$result = $this -> setUrl(1);
		}

		return $result;
	}

	//最后页URL地址
	public function last_page_url(){
		$result = false;
		if($this -> curr_num != $this -> last_num && $this -> last_num){
			$result = $this -> setUrl($this -> last_num);
		}

		return $result;
	}

	//是否显示前面的省略页
	public function preMore_display(){
		$result = false;
		if($this -> start_num != $this -> first_num){
			$result = true;
		}

		return $result;
	}

	//是否显示后面的省略页
	public function nextMore_display(){
		$result = false;
		if($this -> last_num != $this -> end_num){
			$result = true;
		}

		return $result;
	}
}