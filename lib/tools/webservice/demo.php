<?php 
//server

class welcome{

	public function index(){
		
		tools_webservice_server::getInstance()->run();
		
		
		return 'hello service';
	}

}


//运行


//client

$server_uri = 'http://localhost/webservice/server/server.php';
print_r(tools_webservice_client::send($server_uri,'welcome.index'));

?>