<?php
/**
 * User: ShengYue
 * Email: ysc8620@163.com
 * QQ: 372613912
 */
abstract class core_dao {

	/**
	 * @var $vo_name 数据对象名称
	 */
	
	protected $base_db;				//数据库名称
	protected $base_table;			//表名称
	protected $fields;				//数据字段
	protected $cache_time;
	protected $cache_fields;
	protected $is_cache = false;	//是否需要缓存
	protected $cache_type = null;
	
	protected $salve_link = null;	//从库连接
	protected $master_link = null;	//主库连接
	protected $trun_page;   		//翻页
	protected $options;		
	
	function __construct(){
		$table = core_comm::table($this -> get_called_class());
		//初始化DAO
		$this -> base_db = core_config::get('db.db.' . $table -> base_db);				//基库名称
		$this -> base_table = $table -> base_table;			//基表名称
		$this -> fields = @array_keys( $table -> fields);	//表字段设置
		$this -> cache_fields = $table -> cache_fields;		//缓存字段
		$this -> cache_time = $table -> cache_time;			//缓存周期
        $this -> cache_type = core_config::get('base.cache_type');
		if($this -> cache_fields){
			$this -> is_cache = true;						//是否需要缓存
		}
	}
	
	function __call($method, $args) {
		if (in_array ( strtolower ( $method ), array ('db','host', 'field', 'table', 'where', 'order', 'limit', 'page', 'having', 'group', 'lock', 'distinct' , 'join' ), true )) {
			// 连贯操作的实现
			$method = strtolower ( $method );
			if ($args [0]) {
				$this -> options [$method] = $args [0];
			} else {
				$this -> options [$method] = $this -> $method;
			}
			if ($method == 'page') {
				$this -> trun_page = true;
			}
			return $this;
		} elseif (in_array ( $method, array ('count', 'sum', 'min', 'max', 'avg' ), true )) {
			$field = isset ( $args [0] ) ? $args [0] : '*'; //是否设置统计字段
			return $this -> get_one_field ( $method . '(' . $field . ') AS tp_' . $method );
		} else {
			throw new core_exception_program(904006,"dao:no method ".$method);
		}
	}

	/**
	 * 获取数据库操作服务器
	 * @param array $options 
	 * @param str $default 
	 * @return
	 */
	private function get_db($options,$default='master'){
		//获取主从服务器
		$host = isset($options['host']) ? $options['host'] : $default;
		if($host != 'salve'){
			$host = 'master';
		}
		
		//获取数据库名，未设置则取当前dao默认配置
		$db_name = isset($options['db']) ? $options['db'] : $this -> base_db;
		if(!$db_name){
		    throw new core_exception_program(904008,"dao:database has not config:(" . $db_name . ")");
		}
		
		//获取服务器连接
		if($host == 'master' && isset($this -> master_link[$db_name])){
			return $this -> master_link[$db_name];
		}
		
		if($host == 'salve' && isset($this -> salve_link[$db_name])){
			return $this -> salve_link[$db_name];
		}
		
		$host_config = core_config::get('db.dbhost');
		$server_config = core_config::get('db.dbserver');
		
		//数据库所在服务器名
		$server_name = $host_config[$db_name];
		if(!$server_name){
			$server_name = $host_config['default'];
		}
		if(!$server_name){
		    throw new core_exception_program(904004,"dao:database server has not config:(" . $server_name . ")");
		}
		
		//数据库资源
		$server_pools = $server_config[$server_name];
		if(!$server_pools){
		    throw new core_exception_program(904004,"dao:database server has not config:(" . $server_name . ")");
		}
		
		if($host == 'salve'){		//从库
			$host_num = count($server_pools['salve']);
			$server_config = $server_pools['salve'][mt_rand(0,$host_num-1)];
		}else{						//主库
			$server_config = $server_pools['master'];
		}
		
		if(!$server_config){
			throw new core_exception_program(904004,"dao:database server has not config:(" . $server_name . ")");
		}

		try{
    		//数据库后缀
    		$real_db_name = $db_name . DB_SUFFIX;
    		
    		//获取数据库连接实例
    		$db_link = core_db::getInstance ($server_config,$real_db_name );
    		$_host = $host."_link";
    		if($db_link){
    		    $this -> $_host = @ array_merge($this -> $_host,array($db_name=>$db_link));
    		    return $db_link;
    		}else{
    		    //==================发生严重错误，数据库服务器异常===========================//
    		    //启动灾备处理方案
    		}
		}catch (core_exception_program $e){
		    throw $e;
		}
	}
	
	/**
	 * 添加一条信息
	 * @param array $data
	 */
	public function add($data) {
	    try{
    		$options = $this -> _parse_options ( $this -> options );
		    $db = $this -> get_db($options,'master');

		    $id = $db -> insert ( $data, $options );
		    return $id;
	    }catch(core_exception_program $e){
	        throw $e;
	    }
	}
	
	
	public function add_all($data_array){
	    try{
    		$options = $this -> _parse_options ( $this -> options );
    		$db = $this -> get_db($options,'master');
    		$id = $db -> insert_all ( $data_array, $options );
    		return true;
	    }catch(core_exception_program $e){
	        throw $e;
	    }
	}
	
	/**
	 * 替换插入一条记录
	 * @param array $data
	 */
	public function replace_add($data) {
	    try{
	        $options = $this -> _parse_options ( $this -> options );
    		$db = $this -> get_db($options,'master');
    		$id = $db -> replaceInsert ( $data, $options );
    		//@todo 重置当前缓存信息
    		$this -> del_cache($data['id']);
    		return $id;
	    }catch(core_exception_program $e){
	        throw $e;
	    }
	}
	
	/**
	 * 编辑信息
	 * @param array/sql $data 如果此处指定sql语句，则不再解析where等其他操作
	 */
	public function edit($data) {
	    try{
    		$options = $this -> _parse_options ( $this -> options );
    		$db = $this -> get_db($options,'master');
    		if (is_string ( $data )) {
    			return $db -> execute ( $data );
    		} else {
    			return $db -> update ( $data, $options );
    		}
		}catch(core_exception_program $e){
		    throw $e;
		}
	}
	
	
	/**
	 * 删除信息
	 * @param sql $str 如果此处指定sql语句，则不再解析where等其他操作
	 */
	public function del($str = null) {
	    try{
	        $options = $this -> _parse_options ( $this -> options );
    		$db = $this -> get_db($options,'master');
    		if ($str) {
    			return $db -> execute ( $str );
    		} else {
    			return $db -> delete ( $options );
    		}
		}catch(core_exception_program $e){
		    throw $e;
		}
	}
	

	/**
	 * 根据ID编辑信息，并将同步更新缓存
	 * @param unknown_type $data
	 * @param unknown_type $id
	 */
	public function edit_by_id($data,$ids){
	    try{
	        $where [] = array ('id', $ids,'in');
	    
    		$rs = $this -> where($where) -> edit($data);
    		if($rs){
    			$this -> del_cache($ids);
    		}
    		return $rs;
		}catch(core_exception_program $e){
		    throw $e;
		}
	}
	
	
	/**
	 * 根据ID删除记录并同步更新缓存
	 * @param $id int 
	 */
	public function del_by_ids($ids) {
	    try{
    		$where [] = array ('id', $ids,'in');
    		$rs = $this -> where ( $where ) -> del ();
    		if($rs){
    			$this -> del_cache($ids);
    		}
    		return $rs;
		}catch(core_exception_program $e){
		    throw $e;
		}
	}
	
	/**
	 * 根据ID获取VO
	 * @param unknown_type $vo_id
	 * @return core_vo
	 */
	function get_vo($vo_id){
	    try{
	        if(!$vo_id){
    			return null;
    		}
    	    $vo_name = $this -> get_vo_name();
    	    $vo = core_comm::vo($vo_name,$vo_id);
			if($vo -> exists()){
				return $vo;
			}else{
				return null;
			}
	    }catch(core_exception_program $e){
	        throw $e;
	    }
	}
	
	/**
	 * 根据ID获取vo（数组）
	 * @param unknown_type $vo_id
	 * @return core_vo
	 */
	function get_one($vo_id){
	    $array = $this->where(array(array('id',$vo_id)))->get_array();
	    return $array['list'][0];
	}
	
	/**
	 * 新生成一个VO
	 * @return core_vo
	 */
	function create_vo(){
	    try{
    		$vo_name = $this -> get_vo_name();
	    	return new core_vo($vo_name);
	    }catch(core_exception_program $e){
	        throw $e;
	    }
	}
	
	protected function get_vo_name(){
	    return $this -> get_called_class();
	}
	
	/**
	 * 根据ID从缓存中获取记录
	 * @param INT $id
	 */
	public function get_vo_from_cache($id){
		if(true !== $this -> is_cache){
			return null;
		}
		$data = $this -> get_cache(array($id));
	
		return $data['has'][$id];
	}
	
	
	/**
	 * 根据ID从数据库中获取记录
	 * @param INT $id
	 * @param array/str $fields
	 */
	public function get_vo_from_db($id,$fields='*'){
		$where [] = array ('id', $id );
		$vo = $this -> field($fields) -> where ( $where ) -> get_row();
		if($vo && is_object($vo)){
			$this -> set_caches(array($vo->to_array()));
		}
		return $vo;
	}
	
	
	
	
	/**
	 * 根据ID列表获取数据
	 * @param array $ids
	 */
	public function get_rows_by_ids(array $ids){
		if(!$ids){
			return null;
		}
		//先从缓存中获取数据
		$data_from_cache = $this -> get_cache($ids);
		
		//未命中数据
		$filter_ids = $data_from_cache['no'];
		
		if($filter_ids){
			$where[] = array('id',$filter_ids,'in');
			$data_from_db = $this -> where($where) -> get_list();
		}
		
		//@todo
		if($data_from_cache['has'] && $data_from_db['list']){
			return $data_from_cache['has'] + $data_from_db['list'];
		}elseif($data_from_cache['has']){
			return $data_from_cache['has'];
		}else{
			return $data_from_db['list'];
		}
	}
	
	
	
	/**
	 * 获取一条记录
	 * @param sql $str  
	 * @return vo 
	 */
	public function get_row($str = null) {
	    try{
    		$this -> options ['limit'] = '0,1';
    		$result = $this -> get_list($str);
    		if($result['list']){
    			return $result['list'][0];
    		}else{
    			return null;
    		}
		}catch(core_exception_program $e){
		    throw $e;
		}
	}

	/**
	 * 获取一条数组记录
	 * @param sql $str  
	 * @return vo 
	 */
	public function get_item($str = null) {
	    try{
    		$this -> options ['limit'] = '0,1';
    		$result = $this -> get_array($str);
    		if($result['list']){
    			return $result['list'][0];
    		}else{
    			return null;
    		}
		}catch(core_exception_program $e){
		    throw $e;
		}
	}
	
	/**
	 * 获取多条数据
	 * @param sql $str
	 * @return array $vo_list 返回值对象数组 
	 */
	public function get_list($str = null) {
	    try{
    		$result = $this -> select ( $str );
    		//@todo 缓存数据
    		$this -> set_caches($result['list']);
    		$data = $result['list'];
    		$result['list'] = array();
    		if(isset($data[0])){
    			$class_name = $this -> get_called_class();
    			foreach($data as $r){
    				$result['list'][] = core_comm::vo($class_name,$r['id']) -> set($r);
    			}
    		}
    		return $result;
		}catch(core_exception_program $e){
		    throw $e;
		}
	}
	
	/**
	 * 获取多条数据
	 * @param unknown_type $str
	 * @return 返回二维数组
	 */
	function get_array($str = null){
	    try{
	         $result = $this -> select ( $str );
	    	return $result;
		}catch(core_exception_program $e){
		    throw $e;
		}
	}
	
	/**
	 * 获取查询结果统计数
	 * @param sql $sql
	 */
	public function get_num($sql = null) {
	    try{
	        if($sql){
    			$result = $this -> select ( $sql );
    			$result = $result['list'];
    		}else{
    			$result = $this -> count ( 1 );
    		}
    		return $result;
		}catch(core_exception_program $e){
		    throw $e;
		}
	}
	
	/**
	 * 数据库操作
	 */
	public function select($str = null,$db='r') {
	    try{
	        $this -> page = null;
    		if ($this -> trun_page) {
    			$options = $this -> options;
    			$total = $this -> count ( "1" );
    			$this -> options = $options;
    			$page_info = explode ( ":", $this -> options ['page'] );
    			$result ['page'] = new core_page ( $total['tp_count'], $page_info [0], $page_info [1] );
    			$this -> options ['page'] = $result ['page'] -> curr_num . ',' . $page_info [0];
    		}
    		$options = $this -> _parse_options ( $this -> options );
    		$db = $this -> get_db($options,'salve');
    		if ($str) {
    			$result ['list'] = $db -> query ( $str );
    		} else {
    			$result ['list'] = $db -> select ( $options );
    		}
    		return $result;
		}catch(core_exception_program $e){
		    throw $e;
		}
	}
	
	
	/**
	 * 执行一个操作(insert,update,del)
	 * 默认操作主服务器,如果手动指定了host则操作host指定服务器
	 * @param unknown_type $str
	 * @return
	 */
	public function execute($str=null){
	    try{
	        $options = $this -> _parse_options ( $this -> options );
    		$db = $this -> get_db($options,'master');
    		$result = $db -> execute ( $str );
    		return $result;
		}catch(core_exception_program $e){
		    throw $e;
		}
	}

	
	/**
	 * 获取一条记录的某个字段值
	 * @param string $field  字段名
	 */
	public function get_one_field($field) {
	    try{
	        $this -> options ['page'] = null;
    		$this -> options ['field'] = $field;
    		$this -> options ['limit'] = "0,1";
    		$this -> trun_page = false;
    		$result = $this -> select();
    		return $result['list'][0];
		}catch(core_exception_program $e){
		    throw $e;
		}
	}

	
    /**
     * 清空表
     * @return
     */
    public function truncate($str=null){
        try{
            $options = $this -> _parse_options ( $this -> options );
    		$db = $this -> get_db($options,'write');
    		$result = $db -> truncate ( $options );
    		return $result;
		}catch(core_exception_program $e){
		    throw $e;
		}
    }
	
	/**
	 * 获取实例名称
	 */
	protected function get_obj_name(){
		return str_replace('dao_','',get_called_class());
	}
	
	
	function get_called_class(){
		return substr($this -> get_obj_name(),4);
	}
	
	
	/**
	 * 批量设置缓存
	 */
	public function set_caches($list){
		if(!$list[0]['id'] || false === $this -> is_cache){
			return ;
		}
		
		$data = array();
		$cache_feilds = @ array_flip($this -> cache_fields);
		foreach($list as $v){
			if($v['id']){
				$key = $this -> get_cache_key($v['id']);
				$data[$key] = @ array_intersect_key($v,$cache_feilds);
			}
		}
		core_cache::pools($this -> cache_type) -> mset($data,$this -> cache_time);
	}
	
	/**
	 * 根据ID列表获取缓存信息
	 * @param str $ids
	 */
	private function get_cache(array $ids){

		if(false === $this -> is_cache || empty($ids) || !is_array($ids)){
			return null;
		}
		foreach($ids as $i){
			$key[$i] = $this -> get_cache_key($i);
		}
		$data = core_cache::pools($this -> cache_type) -> mget($key);
		foreach($key as $id=>$k){
			if($data[$k]){
				$rs[$id] = core_comm::vo($this -> get_called_class(),$id) -> set($data[$k]);
			}else{
				$no[] = $id;
			}
		}
		return array('has'=>$rs,'no'=>$no);
	}
	
	/**
	 * 删除数据缓存
	 * @param int/array $ids
	 */
	private function del_cache($ids){
		if(false === $this -> is_cache){
			return ;
		}
		if(is_array($ids)){
			foreach($ids as $i){
				$key[] = $this -> get_cache_key($i);
			}
		}else{
			$key[] = $this -> get_cache_key($ids);
		}
		return core_cache::pools($this -> cache_type) -> mdel($key);
	}
	
	/**
	 * 获取缓存名
	 */
	private function get_cache_key($id){
		return "vo_" . strtolower($this -> get_called_class()) . "_" . $id;
	}
	
	
	/**
	 * 获取待查询字段
	 * @param unknown_type $fields
	 */
	private function get_fields($fields=null){
		if($fields =='*' || !$fields){
			$fields = $this -> fields;
		}elseif(false === strpos ( $fields, ' ' ) || false === strpos ( $fields, ' tp_' )){
			if(!is_array($fields)){
				$fields = @ explode(',',$fields);
			}
			$fields[] = 'id';
			if($this -> cache_fields){
				$fields = array_merge($fields,$this -> cache_fields);
			}
			$fields = @array_unique(@array_filter($fields));
		}
		return $fields;
	}
	
	
	/**
	 * 检查字段是否被缓存
	 * @param str/array $field
	 */
	private function field_is_cached($field){
		if($field =='*'){
			$field = $this -> fields;
		}
		if(!is_array($field) && $field){
			$field = explode(',',$field);
		}

		return @array_diff($field, $this -> cache_fields) ? false : true;
	}
	
	
	/**
	 * 分析表达式
	 * @access private
	 * @param array $options 表达式参数
	 * @return array
	 */
	protected function _parse_options($options) {
		if (is_array ( $options )){
			$options = array_merge ( $this -> options, $options );
		}
		$this -> options = array (); // 查询过后清空sql表达式组装 避免影响下次查询
		
		if (! isset ( $options ['table'] )){ // 自动获取表名
			$options ['table'] = $this -> base_table;
		}
		//屏蔽$options['field']未定义提示
		@ $options['field'] = $this -> get_fields($options['field']);
		return $options;
	}
	
	/**
	 * 将数据放进缓存
	 * @param str $key
	 * @param fixed $data
	 */
	protected function set_data_to_cache($key,$data,$expire){
		return core_cache::pools() -> set($key,$data,$expire);
	}
	
	/**
	 * 从缓存中获取数据
	 * @param str $key
	 */
	protected function get_data_from_cache($key){
		return core_cache::pools() -> get($key);
	}
	
	/**
	 * 从缓存中删除数据
	 * @param str $key
	 */
	protected function del_data_from_cache($key){
		return core_cache::pools() -> del($key);
	}
	
	
	/**
	 * DAO数据有效性校验方法 true
	 * @param unknown_type $condition
	 * @param unknown_type $msg_no
	 * @throws core_exception_program
	 */
	static function true($condition,$msg_no=null){
	    if(!$condition){
	        if($msg_no){
	            $message = core_msg::message($msg_no);
	        }
			throw new core_exception_program(100031,$message);
	        exit;
		}
	}
	
	
	/**
	 * DAO数据有效性校验方法 false
	 * @param unknown_type $condition
	 * @param unknown_type $msg_no
	 * @throws core_exception_program
	 */
	static function false($condition,$msg_no=null){
	    if($condition){
	        if($msg_no){
	            $message = core_msg::message($msg_no);
	        }
	        throw new core_exception_program(100031,$message);
	        exit;
	    }
	}
}
?>