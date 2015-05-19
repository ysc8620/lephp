<?php
define ( 'CLIENT_MULTI_RESULTS', 131072 );
class core_driver_mysql extends core_db {
	
	/**
     +----------------------------------------------------------
	 * 架构函数 读取数据库配置信息
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param array $config 数据库配置数组
     +----------------------------------------------------------
	 */
	public function __construct($host, $db_name) {
		$this -> host = $host;
		$this -> db_name = $db_name;
	}
	
	/**
	 * 获取当前连接句柄
	 */
	function _getLink() {
		if (!isset($this -> link)) {
			try{
				if($this -> host['hostport']){
					$host =  $this -> host['hostname'] . ':' . $this -> host['hostport'];
				}else{
					$host = $this -> host['hostname'];
				}
				if ($this -> pconnect) {
					$this -> link = mysql_pconnect ( $host, $this -> host ['username'], $this -> host ['password'], CLIENT_MULTI_RESULTS );
				} else {
					$this -> link = mysql_connect ( $host, $this -> host ['username'],$this -> host ['password'], true, CLIENT_MULTI_RESULTS );
				}
				try{
		            mysql_select_db ( $this -> db_name, $this -> link) or die('select db error');
				}catch (core_exception $e){
					throw $e;
				}
	            $this -> execute("set names 'utf8'");
			}catch (ErrorException $e){
				throw new core_exception(905001);
			}
		}
		return $this -> link;
	}
	
	/**
     +----------------------------------------------------------
	 * 释放查询结果
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 */
	public function free() {
		@mysql_free_result ( $this -> query_id );
		$this -> query_id = 0;
	}
	
	/**
     +----------------------------------------------------------
	 * 执行查询 返回数据集
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param string $str  sql指令
     +----------------------------------------------------------
	 * @return mixed
     +----------------------------------------------------------
	 * @throws ThinkExecption
     +----------------------------------------------------------
	 */
	public function query($str) {
	
		if ($this -> query_id) {
			$this -> free (); //释放前次的查询结果
		}
		$this -> query_id = mysql_query ( $str, $this -> link );
		$this -> sql_str = $str;
		//echo $str . "<br/>";
		if (false === $this -> query_id) {
			$this -> error ();
			return false;
		} else {
			$this -> num_rows = mysql_num_rows ( $this -> query_id );
			return $this -> get_all ();
		}
	}
	
	/**
     +----------------------------------------------------------
	 * 执行语句
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param string $str  sql指令
     +----------------------------------------------------------
	 * @return integer
     +----------------------------------------------------------
	 * @throws ThinkExecption
     +----------------------------------------------------------
	 */
	public function execute($str) {
		//释放前次的查询结果
		if ($this -> query_id) {
			$this -> free ();
		}
		$result = mysql_query ( $str, $this -> link );
		$this -> sql_str = $str;
		//echo $str;
		if (false === $result) {
			$this -> error ();
		} else {
			$this -> num_rows = mysql_affected_rows ( $this -> link );
			$this -> last_insert_id = mysql_insert_id ( $this -> link );
			if($this -> num_rows){
				return $this -> num_rows;
			}
			if($this -> last_insert_id){
				return $this -> last_insert_id;
			}
		    return true;
		}
	}
	
	/**
     +----------------------------------------------------------
	 * 获得所有的查询数据
     +----------------------------------------------------------
	 * @access private
     +----------------------------------------------------------
	 * @return array
     +----------------------------------------------------------
	 * @throws ThinkExecption
     +----------------------------------------------------------
	 */
	private function get_all() {
		//返回数据集
		$result = array ();
		if ($this -> num_rows > 0) {
			while ( $row = mysql_fetch_assoc ( $this -> query_id ) ) {
				$result [] = $row;
			}
			mysql_data_seek ( $this -> query_id, 0 );
		}
		return $result;
	}
	
	/**
     +----------------------------------------------------------
	 * 取得数据表的字段信息
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 */
	public function get_fields($tableName) {
		$result = $this -> query ( 'SHOW COLUMNS FROM ' . $tableName );
		$info = array ();
		if ($result) {
			foreach ( $result as $key => $val ) {
				$info [$val ['Field']] = array ('name' => $val ['Field'], 'type' => $val ['Type'], 'notnull' => ( bool ) ($val ['Null'] === ''), // not null is empty, null is yes
'default' => $val ['Default'], 'primary' => (strtolower ( $val ['Key'] ) == 'pri'), 'autoinc' => (strtolower ( $val ['Extra'] ) == 'auto_increment') );
			}
		}
		return $info;
	}
	
	/**
     +----------------------------------------------------------
	 * 取得数据库的表信息
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 */
	public function get_tables($db_name = '') {
		if (! empty ( $db_name )) {
			$sql = 'SHOW TABLES FROM ' . $db_name;
		} else {
			$sql = 'SHOW TABLES ';
		}
		$result = $this -> query ( $sql );
		$info = array ();
		foreach ( $result as $key => $val ) {
			$info [$key] = current ( $val );
		}
		return $info;
	}
	
	/**
     +----------------------------------------------------------
	 * 替换记录
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param mixed $data 数据
	 * @param array $options 参数表达式
     +----------------------------------------------------------
	 * @return false | integer
     +----------------------------------------------------------
	 */
	public function replace($data, $options = array()) {
		foreach ( $data as $key => $val ) {
			$value = $this -> parseValue ( $val );
			if (is_scalar ( $value )) { // 过滤非标量数据
				$values [] = $value;
				$fields [] = $this -> add_special_char ( $key );
			}
		}
		$sql = 'REPLACE INTO ' . $this -> parseTable ( $options ['table'] ) . ' (' . implode ( ',', $fields ) . ') VALUES (' . implode ( ',', $values ) . ')';
		return $this -> execute ( $sql );
	}
	
	/**
     +----------------------------------------------------------
	 * 插入记录
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param mixed $datas 数据
	 * @param array $options 参数表达式
     +----------------------------------------------------------
	 * @return false | integer
     +----------------------------------------------------------
	 */
	public function insert_all($datas, $options = array()) {
		if (! is_array ( $datas [0] ))
			return false;
		$fields = array_keys ( $datas [0] );
		array_walk ( $fields, array ($this, 'add_special_char' ) );
		$values = array ();
		foreach ( $datas as $data ) {
			$value = array ();
			foreach ( $data as $key => $val ) {
				$val = $this -> parseValue ( $val );
				if (is_scalar ( $val )) { // 过滤非标量数据
					$value [] = $val;
				}
			}
			$values [] = '(' . implode ( ',', $value ) . ')';
		}
		$sql = 'INSERT INTO ' . $this -> parseTable ( $options ['table'] ) . ' (' . implode ( ',', $fields ) . ') VALUES ' . implode ( ',', $values );
		return $this -> execute ( $sql );
	}
	
	/**
     +----------------------------------------------------------
	 * 关闭数据库
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @throws ThinkExecption
     +----------------------------------------------------------
	 */
	public function close() {
		if (! empty ( $this -> query_id ))
			mysql_free_result ( $this -> query_id );
		$this -> link = null;
	}
	
	/**
     +----------------------------------------------------------
	 * 清空表
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @throws ThinkExecption
     +----------------------------------------------------------
	 */
	function truncate($table){
		$sql = 'truncate table ' . $table;
		return $this -> execute ( $sql );
	}
	
	/**
     +----------------------------------------------------------
	 * 数据库错误信息
	 * 并显示当前的SQL语句
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @return string
     +----------------------------------------------------------
	 */
	public function error() {
		$this -> error = mysql_error ( $this -> link );
		$this -> errno = mysql_errno( $this -> link );
		if ($this -> debug && '' != $this -> sql_str) {
			$this -> error .= "\n [ SQL语句 ] : " . $this -> sql_str;
		}
        echo "[错误号]：" . $this -> errno . "\n  [ 错误提示 ]:".$this -> error;
		throw new core_exception_program(905003,"[错误号]：" . $this -> errno . "\n  [ 错误提示 ]:".$this -> error);
		return $this -> error;
	}
	
	/**
     +----------------------------------------------------------
	 * SQL指令安全过滤
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @param string $str  SQL字符串
     +----------------------------------------------------------
	 * @return string
     +----------------------------------------------------------
	 */
	public function escape_string($str) {
		return mysql_escape_string ( $str );
	}
	
	/**
     +----------------------------------------------------------
	 * 析构方法
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 */
	public function __destruct() {
		// 关闭连接
		$this -> close ();
	}
}
?>