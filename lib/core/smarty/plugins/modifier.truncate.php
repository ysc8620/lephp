<?php
/**
* Smarty plugin
* @package Smarty
* @subpackage plugins
*/


/**
* Smarty truncate modifier plugin
*
* Type: modifier<br>
* Name: truncate<br>
* Purpose: Truncate a string to a certain length if necessary,
* optionally splitting in the middle of a word, and
* appending the $etc string or inserting $etc into the middle.
* @link http://smarty.php.net/manual/en/lang...r.truncate.php
* truncate (Smarty online manual)
* @author Monte Ohrt <monte at ohrt dot com>
* @param string
* @param integer
* @param string
* @param boolean
* @param boolean
* @return string
*/
function smarty_modifier_truncate($string, $length = 80, $etc = '...',
$break_words = false, $middle = false)
{
if ($length == 0)
return '';

if (strlen($string) > $length) {
$length -= strlen($etc);
if (!$break_words && !$middle) {
$string = preg_replace('/s+?(S+)?$/', '', CnSubstr($string, 0, $length+1));
}
if(!$middle) {
	return CnSubstr($string, 0, $length).$etc;
} else {
	return CnSubstr($string, 0, $length/2) . $etc . CnSubstr($string, -$length/2);
}
} else {
	return $string;
}
}

function CnSubstr($str,$start,$len)
{
for($i=0;$i<$start+$len;$i++)
{
$tmpstr=(ord($str[$i])>=161 && ord($str[$i])<=254&& ord($str[$i+1])>=161 && ord($str[$i+1])<=254)?$str[$i].$str[++$i]:$tmpstr=$str[$i];
if ($i>=$start&&$i<($start+$len))$tmp .=$tmpstr;
}
return $tmp;
}
/* vim: set expandtab: */
?>