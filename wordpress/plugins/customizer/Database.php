<?php
class CustomizerDatabase
{
    static $table = 'option_with_sequence';
    static $CACHE_GROUP_KEY = 'options_sequence';
    static $CACHE_NO_EXIST_KEY = 'no_exist_options_sequence';
    static $CACHE_ALL_KEY = 'all_options_sequence';

    static function createTable()
    {
        global $wpdb, $option_with_sequence_db_version;
        // TABLE
        $table_name = $wpdb->prefix . self::$table;
        // CHARSET
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
    option_key varchar(50) NOT NULL,
    option_sequence int(11) DEFAULT 0 NOT NULL,
    option_value longtext DEFAULT NULL,
    autoload varchar(20) DEFAULT 'yes' NOT NULL,
    PRIMARY KEY  id (option_key, option_sequence)
    ) $charset_collate;";

        /*
         * クエリ実行
         */
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta("DROP TABLE IF EXISTS $table_name");
        dbDelta($sql);

        /*
         * インストール済みのDBバージョンをDB保存
         */
        add_option('option_with_sequence_db_version', $option_with_sequence_db_version);
    }

    static function insertInitialData()
    {
        global $wpdb;

//        $welcome_name = 'Wordpress さん';
//        $welcome_text = 'おめでとうございます、インストールに成功しました！';
//
//        $table_name = $wpdb->prefix . 'liveshoutbox';
//
//        $wpdb->insert(
//            $table_name,
//            array(
//                'time' => current_time( 'mysql' ),
//                'name' => $welcome_name,
//                'text' => $welcome_text,
//            )
//        );
    }

    /* *******************************
     *       OPTION_WITH_SEQUENCE
     * *******************************/
    /**
     * WPの update_optionに相当するメソッド
     *
     * @param $key
     * @param $value
     * @param int $sequence
     * @param bool $autoload
     * @return bool
     */
    static function updateOption($key, $value, $sequence = 0, $autoload = false)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table;

        $option = trim($key);
        if (empty($option)) {
            return false;
        }

        if (is_object($value)) {
            $value = clone $value;
        }

        $old_value = self::getOption($option);

        /*
         * If the new and old values are the same, no need to update.
         *
         * Unserialized values will be adequate in most cases. If the unserialized
         * data differs, the (maybe) serialized data is checked to avoid
         * unnecessary database calls for otherwise identical object instances.
         *
         * See https://core.trac.wordpress.org/ticket/38903
         */
        if ($value === $old_value || maybe_serialize($value) === maybe_serialize($old_value)) {
            return false;
        }

        $serialized_value = maybe_serialize($value);

        $update_args = array(
            'option_value' => $serialized_value,
            'option_sequence' => $sequence
        );

        if (null !== $autoload) {
            $update_args['autoload'] = ('no' === $autoload || false === $autoload) ? 'no' : 'yes';
        }

        $result = $wpdb->update($table_name, $update_args, array(
            'option_key' => $option,
            'option_sequence' => $sequence,
        ));
        if (!$result) {
            return false;
        }

        $not_options = wp_cache_get(self::$CACHE_NO_EXIST_KEY, self::$CACHE_GROUP_KEY);
        if (is_array($not_options) && isset($not_options[$option])) {
            unset($not_options[$option]);
            wp_cache_set(self::$CACHE_NO_EXIST_KEY, $not_options, self::$CACHE_GROUP_KEY);
        }

        if (!wp_installing()) {
            $all_options = wp_load_alloptions();
            if (isset($all_options[$option])) {
                $all_options[$option] = $serialized_value;
                wp_cache_set(self::$CACHE_ALL_KEY, $all_options, self::$CACHE_GROUP_KEY);
            } else {
                wp_cache_set($option, $serialized_value, self::$CACHE_GROUP_KEY);
            }
        }

        return true;
    }

    /**
     * WPの get_optionに相当するメソッド
     * @param $key
     * @param bool $default
     * @param bool $single
     * @return bool|mixed
     */
    static function getOption($key, $default = false, $single = false)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table;

        $option = trim($key);
        if (empty($option)) {
            return false;
        }


//        if (defined('WP_SETUP_CONFIG')) {
//            return false;
//        }

        if (!wp_installing()) {
            // 存在しないOptionが複数のクエリを実行してしまうのを防ぐ
            $not_options = wp_cache_get(self::$CACHE_NO_EXIST_KEY, self::$CACHE_GROUP_KEY);
            if (isset($not_options[$option])) {
                return $default;
            }

            $all_options = self::loadAllOptions();
            if (isset($all_options[$option])) {
                $value = $all_options[$option];
            } else {
                $value = wp_cache_get($option, self::$CACHE_GROUP_KEY);

                if (false === $value) {
                    $rows = $wpdb->get_results($wpdb->prepare("SELECT option_value, option_sequence FROM $table_name WHERE option_key = %s ORDER BY option_key, option_sequence", $option));
                    $options = [];
                    foreach ($rows as $row) {
                        $options[$row->option_sequence] = $row->option_value;
                    }

                    if (!empty($options)) {
                        wp_cache_add($option, $options, self::$CACHE_GROUP_KEY);
                        $value = $options;
                    } else {
                        // Optionが見つからなかったら、見つからなかったという情報を保存
                        if (!is_array($not_options)) {
                            $not_options = array();
                        }
                        $not_options[$option] = true;
                        wp_cache_set(self::$CACHE_NO_EXIST_KEY, $not_options, self::$CACHE_GROUP_KEY);

                        return $default;
                    }
                }
            }
        } else {
            $suppress = $wpdb->suppress_errors();
            $rows = $wpdb->get_results($wpdb->prepare("SELECT option_value, option_sequence FROM $table_name WHERE option_key = %s ORDER BY option_key, option_sequence", $option));
            $options = [];
            foreach ($rows as $row) {
                $options[$row->option_sequence] = $row->option_value;
            }
            $wpdb->suppress_errors($suppress);
            if (!empty($options)) {
                $value = $options;
            } else {
                return $default;
            }
        }

        if (is_array($value)) {
            $value = array_reduce($value, function ($reduced, $one) {
                $reduced[] = maybe_unserialize($one);
                return $reduced;
            }, []);
        } else {
            $value = maybe_unserialize($value);
        }

        if ($single && is_array($value)) {
            $value = array_shift($value);
        }
        return $value;
    }

    /**
     * WP の wp_load_alloptionsに相当するメソッド
     * @return array|bool|mixed
     */
    static function loadAllOptions()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table;

        $all_options = false;
        if (!wp_installing() || !is_multisite()) {
            $all_options = wp_cache_get(self::$CACHE_ALL_KEY, self::$CACHE_GROUP_KEY);
        }

        if (!$all_options) {
            $suppress = $wpdb->suppress_errors();
            if (!$all_options_db = $wpdb->get_results("SELECT option_key, option_value, option_sequence FROM $table_name WHERE autoload = 'yes' ORDER BY option_key, option_sequence"))
                $all_options_db = $wpdb->get_results("SELECT option_key, option_value, option_sequence FROM $table_name ORDER BY option_key, option_sequence");
            $wpdb->suppress_errors($suppress);
            $all_options = array();
            foreach ((array)$all_options_db as $o) {
                $all_options[$o->option_key][$o->option_sequence] = $o->option_value;
            }
            if (!wp_installing() || !is_multisite()) {
                wp_cache_add(self::$CACHE_ALL_KEY, $all_options, self::$CACHE_GROUP_KEY);
            }
        }

        return $all_options;
    }


    /**
     * @param $key
     * @param mixed $value
     * @param mixed $sequence
     * @param string $autoload
     * @param bool $force
     * @return bool
     */
    static function addOption($key, $value = '', $sequence = null, $autoload = 'yes', $force = false)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table;

        $option = trim($key);
        if (empty($option)) { return false; }

        if (is_object($value)) { $value = clone $value; }

        /*
         * [ 存在チェック ]
         * 存在していたら追加しない
         * ただし、 $force フラグが trueなら更新する
         */
        $not_options = wp_cache_get(self::$CACHE_NO_EXIST_KEY, self::$CACHE_GROUP_KEY);
        if (!$force && (!is_array($not_options) || !isset($not_options[$option]))) {
            if (false !== self::getOption($option)) {
                return false;
            }
        }

        $serialized_value = maybe_serialize($value);
        $autoload = ('no' === $autoload || false === $autoload) ? 'no' : 'yes';

        if (!is_numeric($sequence)) {
            $sequence = $wpdb->get_var($wpdb->prepare("SELECT max(`option_sequence`) FROM `$table_name` WHERE `option_key` = %s"));
            if (empty($sequence)) {
                $sequence = 0;
            } else {
                $sequence++;
            }
        }

        $result = $wpdb->query($wpdb->prepare("INSERT INTO `$table_name` (`option_key`, `option_value`, `option_sequence`, `autoload`) VALUES (%s, %s, %s, %s) ON DUPLICATE KEY UPDATE `option_key` = VALUES(`option_key`), `option_value` = VALUES(`option_value`), `option_sequence` = VALUES(`option_sequence`), `autoload` = VALUES(`autoload`)", $option, $serialized_value, $sequence, $autoload));

        if (!$result) { return false; }

        if (!wp_installing()) {
            if ('yes' == $autoload) {
                $all_options = self::loadAllOptions();
                $all_options[$option][$sequence] = $serialized_value;
                wp_cache_set(self::$CACHE_ALL_KEY, $all_options, self::$CACHE_GROUP_KEY);
            } else {
                wp_cache_set($option, [$sequence => $serialized_value], self::$CACHE_GROUP_KEY);
            }
        }

        // This option exists now
        $not_options = wp_cache_get(self::$CACHE_NO_EXIST_KEY, self::$CACHE_GROUP_KEY); // yes, again... we need it to be fresh
        if (is_array($not_options) && isset($not_options[$option])) {
            unset($not_options[$option]);
            wp_cache_set(self::$CACHE_NO_EXIST_KEY, $not_options, self::$CACHE_GROUP_KEY);
        }

        return true;
    }

    /**
     * @param $key
     * @param null $sequence
     * @return bool
     */
    static function deleteOption($key, $sequence = null)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table;

        $option = trim($key);
        if (empty($option)) { return false; }

        // Get the ID, if no ID then return
        $where_query = 'option_key = %s';
        $where_query_arr = ['option_key' => $option];
        $where_args = [$option];
        if (is_numeric($sequence)) {
            $where_query .= ' AND option_sequence = %s';
            $where_query_arr['option_sequence'] = $sequence;
            $where_args[] = $sequence;
        }

        $rows = $wpdb->get_results($wpdb->prepare("SELECT option_key, option_sequence, autoload FROM $table_name WHERE $where_query", ...$where_args));
        if (empty($rows)) { return false; }

        $result = $wpdb->delete($table_name, $where_query_arr);
        if (!wp_installing()) {
            $autoload_options = [];
            foreach ($rows as $row) {
                if ('no' === $row->autoload) { continue; }
                $autoload_options[] = $row;
            }

            if (!empty($autoload_options)) {
                $all_options = self::loadAllOptions();
                foreach ($autoload_options as $autoload_option) {
                    if (is_array($all_options) && isset($all_options[$option])) {
                        unset($all_options[$option][$autoload_option->option_sequence]);
                    }
                }
                if (empty($all_options[$option])) {
                    unset($all_options[$option]);
                }
                wp_cache_set(self::$CACHE_ALL_KEY, $all_options, self::$CACHE_GROUP_KEY);
            } else if (is_null($sequence)){
                wp_cache_delete($option, self::$CACHE_GROUP_KEY);
            } else {
                $cached_option = wp_cache_get($option, self::$CACHE_GROUP_KEY);
                foreach ($autoload_options as $autoload_option) {
                    if (is_array($cached_option) && isset($cached_option[$autoload_option->option_sequence])) {
                        unset($cached_option[$autoload_option->option_sequence]);
                    }
                }
                wp_cache_set($option, $cached_option, self::$CACHE_GROUP_KEY);
            }
        }

        return $result ? true : false;
    }
}