<?php 
class tools_email{
	static function send($to_mail,$subject,$content,$from_name=null,$from=null){
		$mail ['host'] = 'smtp.exmail.qq.com'; //smtp服务器地址
		if ($from) {
			$mail ['from'] = $from;
		} else {
			$mail ['from'] = 'service@31fen.com'; //自己的邮件地址
		}
		$mail ['port'] = 25; //端口
		$mail ['errno'] = 0; //错误返回号
		$mail ['errstr'] = ''; //错误返回内容
		$mail ['timeout'] = 10; //系统运行超时
		$mail ['auth'] = 1; //是否需要 AUTH LOGIN 验证, 1=是, 0=否
		$mail ['user'] = 'service@31fen.com'; //smtp服务器用户名
		$mail ['pass'] = '29y3iUP7ZH'; //smtp服务器密码
		if ($from_name) {
			$mail ['from_name'] = $from_name; //联系人名称
		} else {
			$mail ['from_name'] = '31fen'; //联系人名称
		}
		try{
			$em = new core_email ();
			$em -> email_sock ( $mail );
			$rs = $em -> send_mail_sock ( $subject, $content, $to_mail, $mail ['from_name'], '1' );
		}catch (ErrorException $e){
			//@todo 记录日志
			throw $e;
		}
		return $rs;
	}
	
	/**
	 * 获取固定邮件格式
	 * @param $assign_list
	 * @param $type
	 */
	static function get_email_format($assign_list,$type='active'){
		core_assert::true($type, 908001);
		$template = $type . '.tpl';
		//检查邮件模板是否存在
		core_assert::true(core_comm::tpl('comm') -> template_exists('email_format/' . $template), 908003);
		
		$assign_list['site_domain'] = core_config::get('base.site_domain') ;
		//将输出内容加载至变量，并返回
		$content = core_comm::tpl('comm') -> to_fetch($assign_list,'email_format/'.$template);
		return $content;
	}
}

?>