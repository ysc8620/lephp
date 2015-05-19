<?php 
class tools_goto{
	
	/**
	 * 根据邮件地址获取邮件服务器地址
	 * 如果未找到则跳转到默认地址
	 * @param str $email
	 * @param str $default
	 */
	static function get_email_server_by_email($email,$default='/'){
		$email_servers = array(
			'163.com'		=> 'http://mail.163.com',
			'126.com'		=> 'http://mail.126.com',
			'sina.com.cn'	=> 'http://mail.sina.com.cn',
			'sohu.com'		=> 'http://mail.sohu.com',
			'yahoo.com.cn'	=> 'http://mail.cn.yahoo.com',
			'yahoo.cn'		=> 'http://mail.cn.yahoo.com',
			'hotmail.com'	=> 'http://www.hotmail.com',
			'gmail.com'		=> 'http://www.gmail.com',
			'qq.com'		=> 'https://mail.qq.com'
		);
		
		$_ = explode('@', $email);
		$mail_domain = $_[1];
		$server = isset($email_servers[$mail_domain]) ? $email_servers[$mail_domain] : $default ;
		return $server;
	}
}
?>