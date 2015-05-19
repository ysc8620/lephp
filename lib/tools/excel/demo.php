<?php
//导出数据demo，目前不支持多工作博。
$array = array(
		array('第一列','第二列','第三列','第四列'),			//这是表头
		array('11','12','13','14'),		//这是第一行数据
		array('21','22','23','24'),		//这是第二行数据
		array('31','32','33','34'),		//这是第三行数据
);
$excel_write = new tools_excel_write();
$excel_write -> addArray($array);
$excel_write ->setWorksheetTitle('工作博1');	//可设置工作簿名称
$excel_write -> generateXML('test');		//test为导出的文件名。不带后缀



exit;
//导入数据demo


//创建 Reader
$excel_read = new tools_excel_read();
//设置文本输出编码
$excel_read->setOutputEncoding('UTF-8');
//读取Excel文件
$excel_read->read('../../test.xls');

$row_num = $excel_read->sheets[0]['numRows'];		//为Excel导入数据行数
$cols_num = $excel_read->sheets[0]['numCols'];		//为Excel导入数据列数
$result = array();

for ($i = 1; $i <= $row_num; $i++) {
	for ($j = 1; $j <= $cols_num; $j++) {
		//显示每个单元格内容
		$result[$i][$j] = $excel_read->sheets[0]['cells'][$i][$j];
	}
}
var_dump($result);		
exit;