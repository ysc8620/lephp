<?php
/**
 * Swift 断言错误
 *
 */

class core_exception_assert extends core_exception {
    public function __construct($msg_no,$message=null) {
        parent::__construct($msg_no,$message);
    }
}