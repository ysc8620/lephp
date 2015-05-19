<?php 
class core_table_hash extends core_table{
	public $db_num;						//分库数量
	public $table_num;					//分表数
	public $create_id_auto = true;		//使用发号器自动发号
	public $hash_key = 'id';
	public $hash_type = 'hash';
	
	protected $hash_value;				//散列参考值
	
	
	/**
	 * 设置散列信息
	 */
	function set_hash_value($hash_value){
		$this -> hash_value = $hash_value;
	}
	
	/**
	 * 获取数据操作对象
	 * 散列表需要同时分配散列表信息
	 * 
	 * 该对象重写父对象方法
	 */
	function dao(){
		if(!$this -> hash_value && $this -> values[$this -> hash_key]){
			$this -> set_hash_value($this -> values[$this -> hash_key]);
		}
		core_assert::true($this -> hash_value, 904105);
		return core_comm::dao($this -> get_called_class()) -> hash($this -> hash_value);
	}
}
?>