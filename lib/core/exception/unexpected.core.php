<?php
/**
 *  非预期错误，和Comm_Exception_Program相比，抛出该异常意味着发生了程序不可控制的错误
 * 	如用户输入错误的参数、依赖的服务出现了异常
 * 
 */
 
class core_exception_unexpected extends core_exception {
    protected $type_code = '200';
    
    public function __construct($message,$code=null) {
        parent::__construct($message,$code);
    }
    
    public function action(){
        // when unexpected throwed, you should do something
        throw $this;
    }
}
