<?php

class CustomizerCron
{
    private $cron_config = CUSTOMIZER_CRON;

    function __construct()
    {
        // 実行間隔1分の設定を作成
        add_filter( 'cron_schedules', array($this, 'cronAdd1Min'));

        foreach ($this->cron_config as $class_name => $is_use) {
            $event = new $class_name();
            if ($is_use) {
                $event->activate();
                $event->setHookEvents();    
            } else {
                $event->deactivate();
            }

            // プラグインの有効化、無効化時にも実行
            if (is_admin()) {
                register_activation_hook(__FILE__, array($event, 'activate'));
                register_deactivation_hook(__FILE__, array($event, 'deactivate'));
            }
        }
    }


    function cronAdd1Min( $schedules ) {
        $schedules['1min'] = array(
            'interval' => 5,
            'display' => __( 'Once every 1 minutes' )
        );

        return $schedules;
    }
}

abstract class BaseCron
{
    protected $hook_events = array();

    abstract function getHookEventsSetting();
    abstract function setHookEvents();
    
    function __construct()
    {
        $this->hook_events = $this->getHookEventsSetting();
    }

    function activate()
    {
        foreach ($this->hook_events as $hook_event_name => $setting) {
            if (!wp_next_scheduled($hook_event_name)) {
                wp_schedule_event($setting[SI_CRON_START], $setting[SI_CRON_TYPE], $hook_event_name);
            }
        }
    }

    function deactivate()
    {
        foreach ($this->hook_events as $hook_event_name => $setting) {
            if (wp_next_scheduled($hook_event_name)) {
                wp_clear_scheduled_hook($hook_event_name);
            }
        }
    }
}

class ReservationPost extends BaseCron
{
    function __construct()
    {
        parent::__construct();
    }
    
    function getHookEventsSetting()
    {
        return [
            'action_schedule_reservation_post' => [
                SI_CRON_START => time(),
                SI_CRON_TYPE => 'daily'
            ]
        ];
    }

    function setHookEvents()
    {
        add_action('action_schedule_reservation_post', array($this, 'reservationPost'));
    }

    function reservationPost()
    {
        $post_types = CustomizerUtils::getCustomizePostTypes();
        $post_types[] = 'post';
        
        $posts = get_posts(array(
            SI_GET_P_POST_TYPE => $post_types,
            SI_GET_P_STATUS => SI_GET_P_STATUS_FUTURE
        ));

        $now = new DateTime();
        foreach ($posts as $post) {
            $post_date = new DateTime($post->post_date);
            if ($post_date <= $now) {
                $post->post_status = SI_GET_P_STATUS_PUBLISH;
                $post->edit_date = true;
                wp_update_post($post, true);

                if ( is_wp_error( $post->ID ) ) {
                    $errors = $post->ID->get_error_messages();
                    siLog($errors);
                }
            }
        }
    }
}
