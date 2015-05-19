<?php
/**
 * 调试期异常处理类
 * 用于在开发过程中调试用
 * 如，接口传递参数错误等类似异常，这些异常在程序发布前必须解决
 * 在发布时遇到该类错误均应记录日志，并根据日志分析后处理相应问题。
 */
 
class core_exception_program extends core_exception {
    public function __construct($msg_no,$message=null) {
        parent::__construct($msg_no,$message);
    }
}
