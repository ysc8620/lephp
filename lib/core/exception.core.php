<?php
/**
 *  错误处理类
 *
 */
class core_exception extends exception {
    protected $type_code = '100';
    public static $show_trace_info = false;
    static $php_errors;
    public $code = '';

    public function __construct($msg_no,$message=null) {
        $msg = core_msg::message($msg_no);
    	if($message){
    	    $msg = $msg . "( " . $message . " )";
    	}
        parent::__construct(strval($msg),intval($msg_no));
    }

    public function debug_code() {
        return $this->type_code.$this->get_file_code();
    }

    public function get_file_code(){
        return $this->file;
    }

    /**
     * 输出exception信息
     *
     * @return  string
     */
    public function __toString() {
        $text = sprintf('%s [ %s ] %s [ line %d ]', get_class($this), $this->getCode(), self::debug_path($this->getFile()), $this->getLine());
        $msg = strip_tags($this->getMessage());
        if (!empty($msg)) {
            $text .= " : " . $msg;
        }
        return $text;
    }


    /**
     * PHP 错误处理, 把所有的PHP错误转化为Errorexception.
     * 这里的错误处理要根据error_reporting的设置来处理.
     *
     * @throws Errorexception
     * @return void
     */
    public static function error_handler($code, $error, $file = NULL, $line = NULL) {
        $need_ignore_errors = self::get_ignore_error_types();

        if ((error_reporting() & $code &~ $need_ignore_errors) === $code) {
            throw new Errorexception($error, $code, 0, $file, $line);
        }elseif (error_reporting() & $code){
        	echo "NOTICE[{$code}]:{$error} @{$file}[{$line}]\n";
        }
    }

    /**
     * PHP exception 处理, 显示错误信息，exception类型，以及生成trace tree
     *
     * @uses  Swift_core::exception_text
     * @param object exception 对象
     * @return boolean
     */
    public static function exception_handler(exception $e) {
        try {
            $type = get_class($e);
            $code = $e->getCode();
            $message = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();
            $exception_txt = self::exception_text($e);

            $trace = $e->getTrace();
            if ($e instanceof Errorexception) {
                // 替换为human readable
                if (isset(self::$php_errors[$code])) {
                    $code = self::$php_errors[$code];
                }

                if (version_compare(PHP_VERSION, '5.3', '<')) {
                    // 修复php 5.2下关于getTrace的bug
                    //@TODO bug url
                    for($i = count($trace) - 1; $i > 0; --$i) {
                        if (isset($trace[$i - 1]['args'])) {
                            $trace[$i]['args'] = $trace[$i - 1]['args'];
                            unset($trace[$i - 1]['args']);
                        }
                    }
                }
            }
            ob_start();
            require_once 'debug.core.php';
            echo ob_get_clean();

        } catch (exception $e) {
            ob_get_level() and ob_clean();
            echo self::exception_text($e), "\n";
            exit(1);
        }
    }

    /**
     * 生成exception信息
     * 将实际路径替换为LIBPATH、APPPATH、SWFPATH
     *
     * exception [ Code ] File [ Line x ] : Message
     *
     * @param object exception
     * @return string
     */
    public static function exception_text(exception $e) {
        $text = sprintf('%s [ %s ] %s [ line %d ]', get_class($e), $e->getCode(), self::debug_path($e->getFile()), $e->getLine());

        $msg = strip_tags($e->getMessage());
        if (!empty($msg)) {
            $text .= " : " . $msg;
        }

        return $text;
    }

    /**
     * self::$shutdown_errors中的错误不会触发error_handler (php默认机制)
     * 如果开启了show_trace_info选项，
     * 为了确保所有错误都能显示错误消息，在init将register_shutdown_func此方法
     */
    public static function shutdown_handler() {
    	$need_ignore_errors = self::get_ignore_error_types();
        if (self::$show_trace_info && ($error = error_get_last()) && ((error_reporting() & $error['type'] &~ $need_ignore_errors) == $error['type'])) {
            ob_get_level() and ob_clean();
            self::exception_handler(new Errorexception($error['message'], $error['type'], 0, $error['file'], $error['line']));
            exit(1);
        }
    }

	/**
     * 移除文件名中 application, system, modpath, or docroot 的 绝对地址，并用字符串取代他们
     *
     * echo Swift_core::debug_path(Swift_core::find_file('classes', 'swift'));
     *
     * @param string path to debug
     * @return string
     */
    public static function debug_path($file) {

        if (strpos($file, ROOT_PATH) === 0) {
            $file = 'ROOT_PATH' . substr($file, strlen(ROOT_PATH));
        } elseif (strpos($file, CORE_PATH) === 0) {
            $file = 'CORE_PATH' . substr($file, strlen(CORE_PATH));
        }elseif(strpos($file, (APP_PATH)) === 0) {
            $file = 'APP_PATH' . substr($file, strlen(APP_PATH));
     //   } elseif (strpos($file, LIBPATH) === 0) {
     //       $file = 'LIBPATH' . substr($file, strlen(LIBPATH));
    //    } elseif (defined('T3PPATH') && strpos($file, T3PPATH) === 0) {
     //       $file = 'T3PPATH' . substr($file, strlen(T3PPATH));
        }
        return $file;
    }


/**
     * 返回HTML字符串
     * 高亮显示文件中指定的行
     *
     * @param string file to open
     * @param integer line number to highlight
     * @param integer number of padding lines
     * @return string source of file
     * @return false file is unreadable
     */
    public static function debug_source($file, $line_number, $padding = 5) {
        if (!$file or !is_readable($file)) {
            return false;
        }

        $file = fopen($file, 'r');
        $line = 0;

        $range = array('start' => $line_number - $padding, 'end' => $line_number + $padding);

        $format = '% ' . strlen($range['end']) . 'd';

        $source = '';
        while (($row = fgets($file)) !== false) {
            if (++$line > $range['end']) {
                break;
            }

            if ($line >= $range['start']) {
                $row = @ htmlspecialchars($row, ENT_NOQUOTES, "utf-8");
                $row = '<span class="number">' . sprintf($format, $line) . '</span> ' . $row;

                if ($line === $line_number) {
                    // 对该行高亮
                    $row = '<span class="line highlight">' . $row . '</span>';
                } else {
                    $row = '<span class="line">' . $row . '</span>';
                }
                $source .= $row;
            }
        }
        fclose($file);

        return '<pre class="source"><code>' . $source . '</code></pre>';
    }

    /**
     * 返回展现跟踪中每个步骤的HTML字符串
     *
     *
     * @param string path to debug
     * @return string
     */
    public static function trace(array $trace = NULL) {
        if ($trace === NULL) {
            $trace = debug_backtrace();
        }

        $statements = array('include', 'include_once', 'require', 'require_once');

        $output = array();
        foreach ($trace as $step) {
            if (!isset($step['function'])) {
                continue;
            }

            if (isset($step['file']) and isset($step['line'])) {
                $source = self::debug_source($step['file'], $step['line']);
            }

            if (isset($step['file'])) {
                $file = $step['file'];

                if (isset($step['line'])) {
                    $line = $step['line'];
                }
            }

            // function()
            $function = $step['function'];

            if (in_array($step['function'], $statements)) {
                if (empty($step['args'])) {
                    $args = array();
                } else {
                    $args = array($step['args'][0]);
                }
            } elseif (isset($step['args'])) {
                if (!function_exists($step['function']) or strpos($step['function'], '{closure}') !== false) {
                    // Introspection on closures or language constructs in a stack trace is impossible
                    $params = NULL;
                } else {
                    if (isset($step['class'])) {
                        if (method_exists($step['class'], $step['function'])) {
                            $reflection = new ReflectionMethod($step['class'], $step['function']);
                        } else {
                            $reflection = new ReflectionMethod($step['class'], '__call');
                        }
                    } else {
                        $reflection = new ReflectionFunction($step['function']);
                    }

                    $params = $reflection->getParameters();
                }
                $args = array();

                foreach ($step['args'] as $i => $arg) {
                    if (isset($params[$i])) {
                        $args[$params[$i]->name] = $arg;
                    } else {
                        // Assign the argument by number
                        $args[$i] = $arg;
                    }
                }
            }

            if (isset($step['class'])) {
                // Class->method() or Class::method()
                $function = $step['class'] . $step['type'] . $step['function'];
            }

            $output[] = array('function' => $function, 'args' => isset($args) ? $args : NULL, 'file' => isset($file) ? $file : NULL, 'line' => isset($line) ? $line : NULL, 'source' => isset($source) ? $source : NULL);

            unset($function, $args, $file, $line, $source);
        }

        return $output;
    }


 	/**
     * 需要忽略处理的错误类型
     *
     * 在PHP<5.3.0时，应该为 E_STRICT, E_NOTICE, E_USER_NOTICE；否则，应该再加上E_DEPRECATED和E_USER_DEPRECATED。
     */
    final static protected function get_ignore_error_types(){
    	$need_ignore_errors = E_STRICT | E_NOTICE | E_USER_NOTICE;
    	if(version_compare(PHP_VERSION, '5.3.0', '>=')){
    		$need_ignore_errors = $need_ignore_errors | E_DEPRECATED | E_USER_DEPRECATED;
    	}
    	return $need_ignore_errors;
    }
}
