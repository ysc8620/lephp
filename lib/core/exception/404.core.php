<?php
/**
 * 404错误类
 */
 
class core_exception_404 extends core_exception {
    public function __construct($msg_no=null,$message=null) {
        parent::__construct($msg_no,$message);
    }
}
