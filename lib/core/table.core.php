<?php 
/**
 * 表对象
 * 该对象完整反映数据表信息，包括分表分库及缓存信息
 * @author aloner
 * 
 */
abstract class core_table {
	public $db;					//数据库
	public $table;					//基表名称（有分表则实际操作表名为“基表名_序号”）
	public $fields = array();		//表内字段设置，包括字段约束及缓存设置等
	public $split = false;			//是否分表，此处设置为false则下面的db_num及table_num无效
	public $db_num = 1;			//----分库数量
	public $table_num = 1;			//----分表数量
	public $create_sql = '';		//----自动建表语句，必须与最终表结构完全一致，否则会发生致命错误
	public $id ;					//记录ID
	public $hash_type = null;		
	public $hash_key = null;		
	protected $values = array();	//记录的值
}