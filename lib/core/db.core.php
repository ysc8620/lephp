<?php
class core_db {
	protected $host;
	protected $db_name;
	static $links;

	protected $autoFree = false; // 是否自动释放查询结果
	public $debug = true; // 是否显示调试信息 如果启用会在日志文件记录sql语句
	protected $pconnect = false; // 是否使用永久连接
	protected $sql_str = ''; // 当前SQL指令
	protected $last_insert_id = null; // 最后插入ID
	protected $num_rows = 0; // 返回或者影响记录数
	protected $num_cols = 0; // 返回字段数
	protected $transTimes = 0; // 事务指令数
	protected $error = ''; // 错误信息
	protected $errno = '';	//错误号
	protected $db_config = ''; // 数据库连接参数配置
	protected $beginTime; // SQL 执行时间记录
	protected $query_id; //查询句柄


	// 数据库表达式
	protected $comparison = array ('eq' => '=', 'neq' => '!=', 'gt' => '>', 'egt' => '>=', 'lt' => '<', 'elt' => '<=', 'notlike' => 'NOT LIKE', 'like' => 'LIKE' );
	// 查询表达式
	protected $selectSql = 'SELECT%DISTINCT% %FIELDS% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT%';

	/**
	 * 取得数据库连接实例
	 */
	public static function getInstance() {
		$args = func_get_args ();
		return core_comm::get_instance_of ( __CLASS__, 'factory', $args );
	}

	public function factory($host, $db_name) {
		return new core_driver_mysql ( $host, $db_name );
	}

	public function __get($name) {
		//获取数据库连接池
		$array = array ("link" );
		if (in_array ( strtolower ( $name ), $array )) {
			return $this -> {"_get" . ucwords ( $name )} ();
		}
	}

	public function select($options = array()) {
		if (isset ( $options ['page'] )) {
			// 根据页数计算limit
			list ( $page, $listRows ) = explode ( ',', $options ['page'] );
			$listRows = $listRows ? $listRows : ((isset ( $options ['limit'] ) && is_numeric ( $options ['limit'] )) ? $options ['limit'] : 20);
			$offset = $listRows * (( int ) $page - 1);
			$options ['limit'] = $offset . ',' . $listRows;
		}
		$sql = str_replace ( array ('%TABLE%', '%DISTINCT%', '%FIELDS%', '%JOIN%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%' ), array ($this -> parseTable ( $options ['table'] ), $this -> parseDistinct ( isset ( $options ['distinct'] ) ? $options ['distinct'] : false ), $this -> parseField ( isset ( $options ['field'] ) ? $options ['field'] : '*' ), $this -> parseJoin ( isset ( $options ['join'] ) ? $options ['join'] : '' ), $this -> parseWhere ( isset ( $options ['where'] ) ? $options ['where'] : '' ), $this -> parseGroup ( isset ( $options ['group'] ) ? $options ['group'] : '' ), $this -> parseHaving ( isset ( $options ['having'] ) ? $options ['having'] : '' ), $this -> parseOrder ( isset ( $options ['order'] ) ? $options ['order'] : '' ), $this -> parseLimit ( isset ( $options ['limit'] ) ? $options ['limit'] : '' ) ), $this -> selectSql );
		$sql .= $this -> parseLock ( isset ( $options ['lock'] ) ? $options ['lock'] : false );

		return $this -> query ( $sql );
	}

	public function insert($data, $options = array()) {
		foreach ( $data as $key => $val ) {
			$value = $this -> parseValue ( $val );
			if (is_scalar ( $value )) { // 过滤非标量数据
				$values [] = $value;
				$fields [] = $this -> add_special_char ( $key );
			}
		}
		$sql = 'INSERT INTO ' . $this -> parseTable ( $options ['table'] ) . ' (' . implode ( ',', $fields ) . ') VALUES (' . implode ( ',', $values ) . ')';
		$sql .= $this -> parseLock ( isset ( $options ['lock'] ) ? $options ['lock'] : false );
		try{
			$rs = $this -> execute ( $sql );
			return $this -> last_insert_id;
		}catch (core_exception_program $e){
			throw $e;
		}
	}

	public function replaceInsert($data, $options) {
		foreach ( $data as $key => $val ) {
			$value = $this -> parseValue ( $val );
			if (is_scalar ( $value )) { // 过滤非标量数据
				$values [] = $value;
				$fields [] = $this -> add_special_char ( $key );
			}
		}
		$sql = 'REPLACE INTO ' . $this -> parseTable ( $options ['table'] ) . ' (' . implode ( ',', $fields ) . ') VALUES (' . implode ( ',', $values ) . ')';
		$sql .= $this -> parseLock ( isset ( $options ['lock'] ) ? $options ['lock'] : false );
		try{
		    $rs = $this -> execute ( $sql );
		    if ($this -> last_insert_id) {
		        return $this -> last_insert_id; //如果是插入，则返回新ID
		    }
		    return true;
		}catch (core_exception_program $e){
		    throw $e;
		}
	}


    /**
     +----------------------------------------------------------
     * 通过Select方式插入记录
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $fields 要插入的数据表字段名
     * @param string $table 要插入的数据表名
     * @param array $option  查询数据参数
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function selectInsert($fields,$table,$options=array()) {
        if(is_string($fields))   $fields    = explode(',',$fields);
        array_walk($fields, array($this, 'add_special_char'));
        $sql   =    'INSERT INTO '.$this -> parseTable($table).' ('.implode(',', $fields).') ';
        $sql  .= str_replace(
            array('%TABLE%','%DISTINCT%','%FIELDS%','%JOIN%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%'),
            array(
                $this -> parseTable($options['table']),
                $this -> parseDistinct(isset($options['distinct'])?$options['distinct']:false),
                $this -> parseField(isset($options['field'])?$options['field']:'*'),
                $this -> parseJoin(isset($options['join'])?$options['join']:''),
                $this -> parseWhere(isset($options['where'])?$options['where']:''),
                $this -> parseGroup(isset($options['group'])?$options['group']:''),
                $this -> parseHaving(isset($options['having'])?$options['having']:''),
                $this -> parseOrder(isset($options['order'])?$options['order']:''),
                $this -> parseLimit(isset($options['limit'])?$options['limit']:'')
            ),$this -> selectSql);
        $sql   .= $this -> parseLock(isset($options['lock'])?$options['lock']:false);
        try{
            $rs = $this -> execute ( $sql );
            return $this -> num_rows;
        }catch (core_exception_program $e){
            throw $e;
        }
    }

    /**
     +----------------------------------------------------------
     * 更新记录
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data 数据
     * @param array $options 表达式
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function update($data,$options) {
        $sql   = 'UPDATE '
            .$this -> parseTable($options['table'])
            .$this -> parseSet($data)
            .$this -> parseWhere(isset($options['where'])?$options['where']:'')
            .$this -> parseOrder(isset($options['order'])?$options['order']:'')
            .$this -> parseLimit(isset($options['limit'])?$options['limit']:'')
            .$this -> parseLock(isset($options['lock'])?$options['lock']:false);

        try{
            $rs = $this -> execute ( $sql );
            return true;
        }catch (core_exception_program $e){
            throw $e;
        }
    }

    /**
     +----------------------------------------------------------
     * 删除记录
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $options 表达式
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function delete($options=array())
    {
        $sql   = 'DELETE FROM '
            .$this -> parseTable($options['table'])
            .$this -> parseWhere(isset($options['where'])?$options['where']:'')
            .$this -> parseOrder(isset($options['order'])?$options['order']:'')
            .$this -> parseLimit(isset($options['limit'])?$options['limit']:'')
            .$this -> parseLock(isset($options['lock'])?$options['lock']:false);
        try{
            $rs = $this -> execute ( $sql );
            return $this -> num_rows;
        }catch (core_exception_program $e){
            throw $e;
        }
    }

	/**
	 * 设置锁机制
	 *
	 * @param unknown_type $lock
	 * @return unknown
	 */
	protected function parseLock($lock = false) {
		if (! $lock)
			return '';
		if ('ORACLE' == $this -> dbType) {
			return ' FOR UPDATE NOWAIT ';
		}
		return ' FOR UPDATE ';
	}

	/**
	 * set分析
	 *
	 * @param unknown_type $data
	 * @return unknown
	 */
	protected function parseSet($data) {
		foreach ( $data as $key => $val ) {
			$value = $this -> parseValue ( $val );
			if (is_scalar ( $value )) // 过滤非标量数据
				$set [] = $this -> add_special_char ( $key ) . '=' . $value;
		}
		return ' SET ' . implode ( ',', $set );
	}

	/**
	 * value分析
	 * @access protected
	 * @param mixed $value
	 * @return string
	 */
	protected function parseValue(&$value) {
		if (is_string ( $value )) {
			$value = '\'' . $this -> escape_string ( $value ) . '\'';
		} elseif (isset ( $value [0] ) && is_string ( $value [0] ) && strtolower ( $value [0] ) == 'exp') {
			$value = $this -> escape_string ( $value [1] );
		} elseif (is_null ( $value )) {
			$value = 'null';
		}elseif($value===''){
			$value="''";
		}
		return $value;
	}

	/**
	 * field分析
	 * @access protected
	 * @param mixed $fields
	 * @return string
	 */
	protected function parseField($fields) {
		if (is_array ( $fields )) {
			$array = array ();
			foreach ( $fields as $key => $field ) {
				if (! is_numeric ( $key ))
					$array [] = $this -> add_special_char ( $key ) . ' AS ' . $this -> add_special_char ( $field );
				else
					$array [] = $this -> add_special_char ( $field );
			}
			$fieldsStr = implode ( ',', $array );
		} elseif (is_string ( $fields ) && ! empty ( $fields )) {
			$fieldsStr = $this -> add_special_char ( $fields );
		} else {
			$fieldsStr = '*';
		}
		return $fieldsStr;
	}

	/**
	 * table分析
	 * @access protected
	 * @param mixed $table
	 * @return string
	 */
	protected function parseTable($tables) {
		if (is_string ( $tables ))
			$tables = explode ( ',', $tables );
		$array = array ();
		foreach ( $tables as $key => $table ) {
			if (is_numeric ( $key )) {
				$array [] = $this -> add_special_char ( $table );
			} else {
				$array [] = $this -> add_special_char ( $key ) . ' ' . $this -> add_special_char ( $table );
			}
		}
		return implode ( ',', $array );
	}

	/**
	 * where分析
	 * @access protected
	 * @param mixed $where
	 * @return string
     +----------------------------------------------------------
	 */
	protected function parseWhere($where) {
		$whereStr = '';
		if (is_string ( $where )) {
			$whereStr = $where;
		} elseif (is_array ( $where )) {
			$whereStr = $this -> _parseWhereArray ( $where );
		}
		return empty ( $whereStr ) ? '' : ' WHERE ' . $whereStr;
	}

	function _parseWhereArray($array) {
		if (! is_array ( $array )) {
			return false;
		}
		$whereStr = '';
		foreach ( $array as $v ) {
			$whereStr .= "( ";
			if (is_array ( $v [0] )) {
				$whereStr .= $this -> _parseWhereArray ( $v );
			} else {
				$key = $v [0];
				$value = $v [1];
				$math = $v [2] ? $v [2] : "eq";
				$operate = isset($v [3]) ? $v [3] : "AND";
				if (preg_match ( '/^(EQ|NEQ|GT|EGT|LT|ELT|NOTLIKE|LIKE)$/i', $math )) { // 比较运算
					$whereStr .= '`' . $key . '`' . ' ' . $this -> comparison [strtolower ( $math )] . ' ' . $this -> parseValue ( $value );
				} elseif ('exp' == strtolower ( $math )) { // 使用表达式
					$whereStr .= ' (' . '`' . $key . '`' . ' ' . $value . ') ';
				} elseif (preg_match ( '/IN/i', $math )) { // IN 运算
					if (is_array ( $value )) {
						array_walk ( $value, array ($this, 'parseValue' ) );
						$zone = implode ( ",", $value );
					} else {
						$zone = $value;
					}
					$whereStr .= '`' . $key . '`' . " in ($zone)";
				} elseif(preg_match ( '/NOTIN/i', $math )){
					if (is_array ( $value )) {
						array_walk ( $value, array ($this, 'parseValue' ) );
						$zone = implode ( ",", $value );
					} else {
						$zone = $value;
					}
					$whereStr .= '`' . $key . '`' . "not in ($zone)";
				}elseif (preg_match ( '/BETWEEN/i', $math )) { // BETWEEN运算
					$data = is_string ( $value ) ? explode ( ',', $value ) : $value;
					$whereStr .= ' (`' . $key . '` ' . strtoupper ( $math ) . ' ' . $this -> parseValue ( $data [0] ) . ' AND ' . $this -> parseValue ( $data [1] ) . ' )';
				}
			}
			$whereStr .= " ) " . $operate . ' ';
		}
		$whereStr = substr ( $whereStr, 0, - 4 );

		return $whereStr;
	}

	/**
     +----------------------------------------------------------
	 * distinct分析
     +----------------------------------------------------------
	 * @access protected
     +----------------------------------------------------------
	 * @param mixed $distinct
     +----------------------------------------------------------
	 * @return string
     +----------------------------------------------------------
	 */
	protected function parseDistinct($distinct) {
		return ! empty ( $distinct ) ? ' DISTINCT ' : '';
	}

	/**
     +----------------------------------------------------------
	 * join分析
     +----------------------------------------------------------
	 * @access protected
     +----------------------------------------------------------
	 * @param mixed $join
     +----------------------------------------------------------
	 * @return string
     +----------------------------------------------------------
	 */
	protected function parseJoin($join) {
		$joinStr = '';
		if (! empty ( $join )) {
			if (is_array ( $join )) {
				foreach ( $join as $key => $_join ) {
					if (false !== stripos ( $_join, 'JOIN' ))
						$joinStr .= ' ' . $_join;
					else
						$joinStr .= ' LEFT JOIN ' . $_join;
				}
			} else {
				$joinStr .= ' LEFT JOIN ' . $join;
			}
		}
		return $joinStr;
	}

	/**
     +----------------------------------------------------------
	 * group分析
     +----------------------------------------------------------
	 * @access protected
     +----------------------------------------------------------
	 * @param mixed $group
     +----------------------------------------------------------
	 * @return string
     +----------------------------------------------------------
	 */
	protected function parseGroup($group) {
		return ! empty ( $group ) ? ' GROUP BY ' . $group : '';
	}

	/**
     +----------------------------------------------------------
	 * having分析
     +----------------------------------------------------------
	 * @access protected
     +----------------------------------------------------------
	 * @param string $having
     +----------------------------------------------------------
	 * @return string
     +----------------------------------------------------------
	 */
	protected function parseHaving($having) {
		return ! empty ( $having ) ? ' HAVING ' . $having : '';
	}

	/**
     +----------------------------------------------------------
	 * order分析
     +----------------------------------------------------------
	 * @access protected
     +----------------------------------------------------------
	 * @param mixed $order
     +----------------------------------------------------------
	 * @return string
     +----------------------------------------------------------
	 */
	protected function parseOrder($order) {
		if (is_array ( $order )) {
			$array = array ();
			foreach ( $order as $key => $val ) {
				if (is_numeric ( $key )) {
					$array [] = $this -> add_special_char ( $val );
				} else {
					$array [] = $this -> add_special_char ( $key ) . ' ' . $val;
				}
			}
			$order = implode ( ',', $array );
		}
		return ! empty ( $order ) ? ' ORDER BY ' . $order : '';
	}

	/**
     +----------------------------------------------------------
	 * limit分析
     +----------------------------------------------------------
	 * @access protected
     +----------------------------------------------------------
	 * @param mixed $lmit
     +----------------------------------------------------------
	 * @return string
     +----------------------------------------------------------
	 */
	protected function parseLimit($limit) {
		return ! empty ( $limit ) ? ' LIMIT ' . $limit . ' ' : '';
	}

	/**
     +----------------------------------------------------------
	 * 字段和表名添加`
	 * 保证指令中使用关键字不出错 针对mysql
     +----------------------------------------------------------
	 * @access protected
     +----------------------------------------------------------
	 * @param mixed $value
     +----------------------------------------------------------
	 * @return mixed
     +----------------------------------------------------------
	 */
	protected function add_special_char(&$value) {
		if (0 === strpos ( $this -> dbType, 'MYSQL' )) {
			$value = trim ( $value );
			if (false !== strpos ( $value, ' ' ) || false !== strpos ( $value, ',' ) || false !== strpos ( $value, '*' ) || false !== strpos ( $value, '(' ) || false !== strpos ( $value, '.' ) || false !== strpos ( $value, '`' )) {
				//如果包含* 或者 使用了sql方法 则不作处理
			} else {
				$value = '`' . $value . '`';
			}
		}
		return $value;
	}

	/**
     +----------------------------------------------------------
	 * 获取最近一次查询的sql语句
     +----------------------------------------------------------
	 * @access public
     +----------------------------------------------------------
	 * @return string
     +----------------------------------------------------------
	 */
	public function last_sql() {
		return $this -> sql_str;
	}

	/**
     +----------------------------------------------------------
	 * 数据库调试 记录当前SQL
     +----------------------------------------------------------
	 * @access protected
     +----------------------------------------------------------
	 */
	protected function debug() {
		// 记录操作结束时间
		if ($this -> debug) {
			$runtime = number_format ( microtime ( TRUE ) - $this -> beginTime, 6 );
		//	core_log::record ( " RunTime:" . $runtime . "s SQL = " . $this -> sql_str, Log::SQL );
		}
	}
}
?>