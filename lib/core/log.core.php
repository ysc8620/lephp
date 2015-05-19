<?php
/**
 * 日志类
 * 通过register_shutdown_function在php gc时完成写入
 * 
 */
class core_log {
    /**
     * @var comm_Log_Formatter 容器
     */
    private static $formatters = array();
    
    /**
     * @var comm_Log_Writer 容器
     */
    private static $writers = array();
    
    /**
     * 加载log writer
     *
     * @param object comm_Log_Writer instance
     * @param array or string 将当前writter适配formatter的type
     */
    public static function attach(comm_Log_Writer $writer, $formatter_type) {
        self::$writers[$formatter_type] = $writer;
    }
    
    /**
     * 添加日志到formatter容器
     * write_on_add为true将立即执行写入，否则待php gc时统一写入
     * 以此避免频繁disk IO
     *
     * @param object comm_Log_Formatter实例
     * @param bool 是否立即写入
     */
    public static function add(comm_Log_Formatter $formatter, $write_now = false) {
        if ($write_now) {
            self::write_single($formatter);
            return;
        }
        
        self::$formatters[$formatter->get_type()][] = $formatter;
    }
    
    /**
     * 在php gc时批量写入当前请求的所有日志
     *
     * @return void
     */
    public static function write_all() {
        if (empty(self::$formatters)) {
            return;
        }
        
        if (empty(self::$writers)) {
            throw new core_exception_program("no log writter registered");
        }
        
        $formatters = self::$formatters;
        self::$formatters = array();
        
        foreach ($formatters as $formatter_type => $formatter_set) {
            self::write_down($formatter_type, $formatter_set);
        }
    }
    
    /**
     * 立即写入单条日志
     * 
     * @param comm_Log_Formatter $formatter
     * @throws comm_exception_program
     */
    public static function write_single($formatter) {
        self::write_down($formatter->get_type(), array($formatter));
    }
    
    /**
     * 写入
     * 
     * @param string $formatter_type
     * @param array $formatter_set
     * @throws comm_exception_program
     */
    private static function write_down($formatter_type, $formatter_set) {
        if (isset(self::$writers[$formatter_type])) {
            self::$writers[$formatter_type]->write($formatter_set);
        } else {
            throw new core_exception_program("no writer for this formatter");
        }
    }
}
