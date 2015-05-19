<?php
/**
 * 以List形式分表的数据库操作对象
 * Enter description here ...
 * @author aloner
 *
 */
class core_dao_list extends core_dao {
	protected $hash_key = 'id';			//分表规则字段
	protected $create_sql;				//自动建表语句，当分表不存在时将自动调用该语句进行创建
	protected $create_id_auto;			//使用发号器自动发号
	protected $hash_list;				//hash配置信息
	
	function __construct(){
		parent::__construct();
		$table = core_comm::table($this -> get_called_class());
		$this -> hash_key = $table -> hash_key ? $table -> hash_key : 'id';
		$this -> hash_list = $table -> hash_list;
		$this -> create_id_auto = $table -> create_id_auto;
		$this -> create_sql = $table -> create_sql;
	}
	
	function add($data){
		if(!$data['id']){
			if($this -> create_id_auto){
				$data['id'] = core_comm::dao('comm_createid') -> create_id($this -> base_table);		//使用ID分发器来分配ID
			}else{
				throw new core_exception_assert(904104);
			}
		} 
		try{
			$this -> hash($data[$this -> hash_key]);
			$range = $this -> get_range();
			$data = parent :: add($data);
		}catch (core_exception $e){
			if($e -> code == '1146'){
				//数据库需要提前建好
				$this -> db($range['db']) -> execute(sprintf($this -> create_sql,$range['table']));
				$this -> hash($data[$this -> hash_key]);
				$data = parent :: add($data);
			}else{
				throw $e;
			}
		}
		return $data;
	}
	
	function replace_add($data){
		if(!$data['id']){
			if($this -> create_id_auto){
				$data['id'] = core_comm::dao('comm_createid') -> create_id($this -> base_table);		//使用ID分发器来分配ID
			}else{
				throw new core_exception_assert(904104);
			}
		}
		
		try{
			$this -> check_hash_set();
			$range = $this -> get_range();
			$data = parent :: replace_add($data);
		}catch (core_exception $e){
			if($e -> code == '1146'){
				//数据库需要提前建好
				$this -> db($range['db']) -> execute(sprintf($this -> create_sql,$range['table']));
				$this -> hash($data[$this -> hash_key]);
				$data = parent :: replace_add($data);
			}else{
				throw $e;
			}
		}
		return $data;
	}

	
	/**
	 * 编辑信息
	 * @param array/sql $data 如果此处指定sql语句，则不再解析where等其他操作
	 */
	public function edit($sql=null) {
		//分表分库设置检查
		$this -> check_hash_set();
		$data = parent :: edit($sql);
		return $data;
	}
	
	
	/**
	 * 删除信息
	 * @param sql $str 如果此处指定sql语句，则不再解析where等其他操作
	 */
	public function del($data) {
		//分表分库设置检查
		$this -> check_hash_set();
		$data = parent :: del($data);
	}
	

	/**
	 * 编辑记录
	 * 分库不支持批量编辑
	 */
	public function edit_by_id($values,$ids){
		core_assert::true($ids, 904103);
		$this -> check_hash_set();
		$rs = parent :: edit_by_id($values,$ids);
		return $rs;
	}
	
	
	/**
	 * 根据ID删除记录并同步更新缓存
	 * @param $hash_key 表散列参考值
	 * @param $id int 
	 */
	public function del_by_ids($ids) {
		core_assert::true($ids, 904103);
		$this -> check_hash_set();
		$rs = parent :: del_by_ids($ids);
		return $rs;
	}
	/**
	 * 根据ID从数据库中获取记录
	 * @param INT $id
	 * @param array/str $fields
	 */
	public function get_vo_from_db($id,$fields='*'){
		$where [] = array ('id', $id );
		$this -> check_hash_set();
		$vo = parent::get_vo_from_db($id,$fields='*');
		return $vo;
	}

	
	/**
	 * 根据ID列表获取数据
	 * @param array $ids
	 */
	public function get_rows_by_ids(array $ids){
		//先从缓存中获取数据
		//$data_from_cache = $this -> get_cache($ids);
		$data_from_cache = array();
		
		//@todo
		//剔除未命中数据
		$filter_ids = $ids;
		$this -> check_hash_set();
		$rs = parent :: get_rows_by_ids($filter_ids);
		$data_from_db = $data_from_db + $rs['list'];
		return $data_from_cache + $data_from_db;
	}	
	
	/**
	 * 获取一条记录
	 * @param sql $str  
	 * @return Vo 
	 */
	public function get_row($str = null) {
		$this -> options ['limit'] = '0,1';
		$result = $this -> get_list($str);
		return $result['list'][0];
	}	
	
	
	//---------------------------
	
	/**
	 * 获取多条数据
	 * @param sql $str
	 * @return array $vo_list 返回值对象数组 
	 */
	public function get_list($str = null) {
		//分表分库设置检查
		$this -> check_hash_set();
		return parent::get_list($str);
	}
	
	/**
	 * 获取查询结果统计数
	 * @param sql $sql
	 */
	public function get_num($sql = null) {
		//分表分库设置检查
		$this -> check_hash_set();
		if($sql){
			$result = $this -> select ( $sql );
			$result = $result['list'];
		}else{
			$result = $this -> count ( 1 );
		}
		return $result [0] ['num'];
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
    
    /**
     * 检查分表信息是否设置
     * 分库分表的数据操作模型中必须显式申明所操作的库名及表明
     */
    function check_hash_set(){
		//@todo //必须指定数据库
		core_assert::true($this -> options ['table'], 904101);
		//@todo //必须指定数据表
		core_assert::true($this -> options ['db'], 904102);
    }	
	
	
	/**
	 * 获取表散列信息
	 * @return
	 */
	function get_range(){
		$result['db'] = $this -> options ['db'];
		$result['table'] = $this -> options ['table'];
		return $result;
	}
		
	
	/**
	 * 配置散列表
	 * 
	 * 系统将根据参考值进行表散列计算
	 * @param mixed $keys  表散列参考值
	 */
	public function hash($key){
		core_assert::true(isset($key),904105);
		//根据该值获取散列信息
		$hash = $this -> get_hash(array($key));
		$db = key($hash);
		$table = key(current($hash));
        return $this -> db($db) -> table($table);
	}
	
	/**
	 * 根据获取分表信息
	 */
	protected function get_hash($keys){
		if (empty($keys)){
            return false;
        }
        $result = array();
        foreach ($keys as $key) {
        	if($this -> hash_list[$key]){
        		$hash_info = $this -> hash_list[$key];
        	}else{
        		$hash_info = $this -> hash_list['other'];
        	}
        	$db_name = $this -> base_db . "_" .  $hash_info['db_key'];
        	$tb_name = $this -> base_table . "_" .  $hash_info['table_key'];
        	$result[$db_name][$tb_name][] = $key;
        }
        return $result;
	}
}
?>