<?php
class Logger
{
    static $LEVEL_NOTICE = 5;
    static $LEVEL_DEBUG = 3;
    static $LEVEL_FATAL = 1;
    static $LEVEL_DEVELOP = 0;

    protected $time = false;
    protected $level = false;
    protected $log_dir = '';
    protected $log_file = '';
    
    function __construct()
    {
        $setting = CustomizerFormSettings::get('backbone');
        $setting = CustomizerConfig::getFieldSetting($setting, 'log');
        $output_dir = CustomizerConfig::getInputSetting($setting, 'output_dir');
        $level = CustomizerConfig::getInputSetting($setting, 'level');
        $include_time = CustomizerConfig::getInputSetting($setting, 'include_time');
        
        $today = (new \DateTime())->format('Y-m-d');
        $this->level = CustomizerDatabase::getOption('backbone_log_level', $level[SI_DEFAULT], true);
        $this->time = CustomizerDatabase::getOption('backbone_log_include_time', $include_time[SI_DEFAULT], true);
        $this->log_dir = CustomizerDatabase::getOption('backbone_log_output_dir', $output_dir[SI_DEFAULT], true);
        $this->log_file = $this->log_dir. '/wp-' .$today;
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set('error_log', $this->log_dir.'/error.log');
    }

    function logging($log_object, $level, $other_file = null, $is_append = true)
    {
        if ($level <= $this->level) {
            $value = $log_object;
            if (is_array($value) || is_object($value)) {
                $value = print_r($value, true);
            }

            if ($this->time) {
                $time = (new \DateTime())->format('Y/m/d H:i:s');
                $value = $time . ' ' . $value;
            }

            /*
             * ディレクトリに書き込み権限があり
             * まだファイルが存在しない、または、ファイルに書き込み権限があるなら書き込む
             */
            $write_file = is_null($other_file) ? $this->log_file.'.log' : $this->log_file . $other_file;

            if (is_writable($this->log_dir) && (!is_file($write_file) || is_writable($write_file))) {
                $options = $is_append ? FILE_APPEND | LOCK_EX : LOCK_EX;
                file_put_contents($write_file, PHP_EOL .  $value, $options);
            } else {
                error_log('[Warning] System has not permission. => ' . $write_file);
            }
        }
    }

    /**
     * 途中からレベルを変更する際に利用
     * 
     * @param $level
     */
    function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * このメソッドは、ソースに残っていたら検索して消す
     * 
     * @param $log_object
     * @param null $other_file
     * @param string $keyword
     * @param bool $is_append
     */
    function develop($log_object, $other_file = null, $keyword = '', $is_append = true)
    {
        $this->mark($keyword, self::$LEVEL_DEVELOP);
        $this->logging($log_object, self::$LEVEL_DEVELOP);
        if (!is_null($other_file)) {
            $this->mark($keyword, self::$LEVEL_DEVELOP, $other_file, $is_append);
            $this->logging($log_object, self::$LEVEL_DEVELOP, $other_file);
        }
    }

    function mark($keyword, $level, $other_file = null, $is_append = true)
    {
        if (!empty($keyword)) {
            $this->logging("!!!!!!!!!!!!!!!!!!!!!!!!!!! $keyword !!!!!!!!!!!!!!!!!!!!!!!!!!!", $level, $other_file, $is_append);
        }
    }
    /**
     * このメソッドは、ソースに残っていたら検索して消す
     * 
     * @param $log_object
     * @param string $other_file
     * @param bool $pretty_print
     */
    function json_develop($log_object, $other_file = '.json', $pretty_print = true)
    {
        $options = JSON_UNESCAPED_UNICODE;
        if ($pretty_print) {
            $options = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        }
        
        $this->logging(
            json_encode($log_object, $options),
            self::$LEVEL_DEVELOP, $other_file, false);
    }

    /**
     * debug log
     *
     * @param $log_object
     * @param null $other_file
     */
    function debug($log_object, $other_file = null)
    {
        $this->logging($log_object, self::$LEVEL_DEBUG);
        if (!is_null($other_file)) {
            $this->logging($log_object, self::$LEVEL_DEBUG, $other_file);
        }
    }

    /**
     * notice log
     *
     * @param $log_object
     * @param null $other_file
     */
    function notice($log_object, $other_file = null)
    {
        $this->logging($log_object, self::$LEVEL_NOTICE);
        if (!is_null($other_file)) {
            $this->logging($log_object, self::$LEVEL_NOTICE, $other_file);
        }
    }

    /**
     * fatal log
     * 
     * @param $log_object
     * @param null $other_file
     */
    function fatal($log_object, $other_file = null)
    {
        $this->logging($log_object, self::$LEVEL_FATAL);
        if (!is_null($other_file)) {
            $this->logging($log_object, self::$LEVEL_FATAL, $other_file);
        }
    }
}