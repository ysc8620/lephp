<?php
// Unique error identifier
$error_id = uniqid('error');
if(!core_config::get('base.debug')){
    return false;
}
function dump(& $var, $length = 128, $level = 0)
{

    if ($var === NULL) {
        return '<small>NULL</small>';
    } elseif (is_bool($var)) {
        return '<small>bool</small> ' . ($var ? 'true' : 'false');
    } elseif (is_float($var)) {
        return '<small>float</small> ' . $var;
    } elseif (is_resource($var)) {
        if (($type = get_resource_type($var)) === 'stream' and $meta = stream_get_meta_data($var)) {
            $meta = stream_get_meta_data($var);

            if (isset($meta['uri'])) {
                $file = $meta['uri'];

                if (function_exists('stream_is_local')) {
                    // Only exists on PHP >= 5.2.4
                    if (stream_is_local($file)) {
                        $file = core_comm::debug_path($file);
                    }
                }

                return '<small>resource</small><span>(' . $type . ')</span> ' . htmlspecialchars($file, ENT_NOQUOTES, "utf-8");
            }
        } else {
            return '<small>resource</small><span>(' . $type . ')</span>';
        }
    } elseif (is_string($var)) {
        /*
         * Clean invalid multibyte characters. iconv is only invoked
         * if there are non ASCII characters in the string, so this
         * isn't too much of a hit.
         */
        $var = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $var);

        if (mb_strlen($var) > $length) {
            // Encode the truncated string
            $str = @ htmlspecialchars(mb_substr($var, 0, $length), ENT_NOQUOTES, "utf-8") . '&nbsp;&hellip;';
        } else {
            // Encode the string
            $str = @ htmlspecialchars($var, ENT_NOQUOTES, "utf-8");
        }

        return '<small>string</small><span>(' . strlen($var) . ')</span> "' . $str . '"';
    } elseif (is_array($var)) {
        $output = array();
        $space = str_repeat($s = '    ', $level);

        static $marker;
        if ($marker === NULL) {
            $marker = uniqid("\x00");
        }

        if (empty($var)) {
            // Do nothing
        } elseif (isset($var[$marker])) {
            $output[] = "(\n$space$s*RECURSION*\n$space)";
        } elseif ($level < 5) {
            $output[] = "<span>(";

            $var[$marker] = true;
            foreach ($var as $key => & $val) {
                if ($key === $marker)
                    continue;
                if (!is_int($key)) {
                    $key = '"' . htmlspecialchars($key, ENT_NOQUOTES, "utf-8") . '"';
                }
                $output[] = "$space$s$key => " . dump($val, $length, $level + 1);
            }
            unset($var[$marker]);
            $output[] = "$space)</span>";
        } else {
            // Depth too great
            $output[] = "(\n$space$s...\n$space)";
        }

        return '<small>array</small><span>(' . count($var) . ')</span> ' . implode("\n", $output);
    } elseif (is_object($var)) {
        // Copy the object as an array
        $array = (array)$var;

        $output = array();

        // Indentation for this variable
        $space = str_repeat($s = '    ', $level);

        $hash = spl_object_hash($var);

        // Objects that are being dumped
        static $objects = array();

        if (empty($var)) {
            // Do nothing
        } elseif (isset($objects[$hash])) {
            $output[] = "{\n$space$s*RECURSION*\n$space}";
        } elseif ($level < 10) {
            $output[] = "<code>{";
            $objects[$hash] = true;
            foreach ($array as $key => & $val) {
                if ($key[0] === "\x00") {
                    $access = '<small>' . ($key[1] === '*' ? 'protected' : 'private') . '</small>';
                    $key = substr($key, strrpos($key, "\x00") + 1);
                } else {
                    $access = '<small>public</small>';
                }
                $output[] = "$space$s$access $key => " . dump($val, $length, $level + 1);
            }
            unset($objects[$hash]);
            $output[] = "$space}</code>";
        } else {
            $output[] = "{\n$space$s...\n$space}";
        }

        return '<small>object</small> <span>' . get_class($var) . '(' . count($array) . ')</span> ' . implode("\n", $output);
    } else {
        return '<small>' . gettype($var) . '</small> ' . htmlspecialchars(print_r($var, true), ENT_NOQUOTES, "utf-8");
    }
}

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style type="text/css">
    #swift_error {
        background: #ddd;
        font-size: 1em;
        font-family: sans-serif;
        text-align: left;
        color: #111;
    }

    #swift_error h1,
    #swift_error h2 {
        margin: 0;
        padding: 1em;
        font-size: 1em;
        font-weight: normal;
        background: #911;
        color: #fff;
    }

    #swift_error h1 a,
    #swift_error h2 a {
        color: #fff;
    }

    #swift_error h2 {
        background: #222;
    }

    #swift_error h3 {
        margin: 0;
        padding: 0.4em 0 0;
        font-size: 1em;
        font-weight: normal;
    }

    #swift_error p {
        margin: 0;
        padding: 0.2em 0;
    }

    #swift_error a {
        color: #1b323b;
    }

    #swift_error pre {
        overflow: auto;
        white-space: pre-wrap;
    }

    #swift_error table {
        width: 100%;
        display: block;
        margin: 0 0 0.4em;
        padding: 0;
        border-collapse: collapse;
        background: #fff;
    }

    #swift_error table td {
        border: solid 1px #ddd;
        text-align: left;
        vertical-align: top;
        padding: 0.4em;
    }

    #swift_error div.content {
        padding: 0.4em 1em 1em;
        overflow: hidden;
    }

    #swift_error pre.source {
        margin: 0 0 1em;
        padding: 0.4em;
        background: #fff;
        border: dotted 1px #b7c680;
        line-height: 1.2em;
    }

    #swift_error pre.source span.line {
        display: block;
    }

    #swift_error pre.source span.highlight {
        background: #f0eb96;
    }

    #swift_error pre.source span.line span.number {
        color: #666;
    }

    #swift_error ol.trace {
        display: block;
        margin: 0 0 0 2em;
        padding: 0;
        list-style: decimal;
    }

    #swift_error ol.trace li {
        margin: 0;
        padding: 0;
    }

    .js .collapsed {
        display: none;
    }
</style>
<script type="text/javascript">
    document.documentElement.className = 'js';
    function koggle(elem) {
        elem = document.getElementById(elem);

        if (elem.style && elem.style['display'])
        // Only works with the "style" attr
            var disp = elem.style['display'];
        else if (elem.currentStyle)
        // For MSIE, naturally
            var disp = elem.currentStyle['display'];
        else if (window.getComputedStyle)
        // For most other browsers
            var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');

        // Toggle the state of the "display" style
        elem.style.display = disp == 'block' ? 'none' : 'block';
        return false;
    }
</script>
<div id="swift_error">
    <h1><span class="type"><?php echo $type ?> [ <?php echo $code ?> ]:</span> <span
            class="message"><?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8", true) ?></span></h1>

    <div id="<?php echo $error_id ?>" class="content">
        <p><span class="file"><?php echo $file ?> [ <?php echo $line ?> ]</span></p>
        <?php echo core_exception::debug_source($file, $line) ?>
        <ol class="trace">
            <?php foreach (core_exception::trace($trace) as $i => $step): ?>
            <li>
                <p>
					<span class="file">
						<?php if ($step['file']): $source_id = $error_id . 'source' . $i; ?>
                        <a href="#<?php echo $source_id ?>"
                           onclick="return koggle('<?php echo $source_id ?>')"><?php echo core_exception::debug_path($step['file']) ?>
                            [ <?php echo $step['line'] ?> ]</a>
                        <?php else: ?>
                        {PHP internal call}
                        <?php endif ?>
					</span>
                    &raquo;
                    <?php echo $step['function'] ?>(<?php if ($step['args']): $args_id = $error_id . 'args' . $i; ?><a
                        href="#<?php echo $args_id ?>"
                        onclick="return koggle('<?php echo $args_id ?>')">arguments</a><?php endif ?>)
                </p>
                <?php if (isset($args_id)): ?>
                <div id="<?php echo $args_id ?>" class="collapsed">
                    <table cellspacing="0">
                        <?php foreach ($step['args'] as $name => $arg): ?>
                        <tr>
                            <td><code><?php echo $name ?></code></td>
                            <td>
                                <pre><?php echo dump($arg) ?></pre>
                            </td>
                        </tr>
                        <?php endforeach ?>
                    </table>
                </div>
                <?php endif ?>
                <?php if (isset($source_id)): ?>
                <pre id="<?php echo $source_id ?>"
                     class="source collapsed"><code><?php echo $step['source'] ?></code></pre>
                <?php endif ?>
            </li>
            <?php unset($args_id, $source_id); ?>
            <?php endforeach ?>
        </ol>
    </div>
    <h2><a href="#<?php echo $env_id = $error_id . 'environment' ?>" onclick="return koggle('<?php echo $env_id ?>')">Environment</a>
    </h2>

    <div id="<?php echo $env_id ?>" class="content collapsed">
        <?php $included = get_included_files() ?>
        <h3><a href="#<?php echo $env_id = $error_id . 'environment_included' ?>"
               onclick="return koggle('<?php echo $env_id ?>')">Included files</a> (<?php echo count($included) ?>)</h3>

        <div id="<?php echo $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($included as $file): ?>
                <tr>
                    <td><code><?php echo core_comm::debug_path($file) ?></code></td>
                </tr>
                <?php endforeach ?>
            </table>
        </div>
        <?php $included = get_loaded_extensions() ?>
        <h3><a href="#<?php echo $env_id = $error_id . 'environment_loaded' ?>"
               onclick="return koggle('<?php echo $env_id ?>')">Loaded extensions</a> (<?php echo count($included) ?>)
        </h3>

        <div id="<?php echo $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($included as $file): ?>
                <tr>
                    <td><code><?php echo core_comm::debug_path($file) ?></code></td>
                </tr>
                <?php endforeach ?>
            </table>
        </div>
        <?php foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER') as $var): ?>
        <?php if (empty($GLOBALS[$var]) OR !is_array($GLOBALS[$var])) continue ?>
        <h3><a href="#<?php echo $env_id = $error_id . 'environment' . strtolower($var) ?>"
               onclick="return koggle('<?php echo $env_id ?>')">$<?php echo $var ?></a></h3>
        <div id="<?php echo $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($GLOBALS[$var] as $key => $value): ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($key, ENT_QUOTES, "UTF-8", true) ?></code></td>
                    <td>
                        <pre><?php echo dump($value) ?></pre>
                    </td>
                </tr>
                <?php endforeach ?>
            </table>
        </div>
        <?php endforeach ?>
    </div>
</div>
