<?php

/**
 * MF Cron Jobs
 *
 * This file is used to manage all Mega Forms custom cron jobs.
 *
 * @link       https://wpali.com
 * @since      1.0.7
 *
 * @package    Mega_Forms
 * @subpackage Mega_Forms/common/partials */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MF_Crons
{

    public static $cron_daily_hook = 'mf_daily_tasks';
    public static $cron_weekly_hook = 'mf_weekly_tasks';
    public static $cron_monthly_hook = 'mf_monthly_tasks';

    public function __construct()
    {

        # Add custom cron intervals
        add_filter('cron_schedules', array($this, 'add_cron_intervals'));

    }

    public function add_cron_intervals($schedules)
    {
        $day_in_seconds = 86400;

        $schedules['weekly'] = array(
            'interval' => 7 * $day_in_seconds,
            'display' => __('Once a week')
        );

        $schedules['monthly'] = array(
            'interval' => 30 * $day_in_seconds,
            'display' => __('Once a month')
        );

        return $schedules;
    }

    public static function setup_daily_cron()
    {

        if (!wp_next_scheduled(self::$cron_daily_hook)) {
            $firstRun = time();
            wp_schedule_event($firstRun, 'daily', self::$cron_daily_hook);
        }
    }

    public static function setup_weekly_cron()
    {

        if (!wp_next_scheduled(self::$cron_weekly_hook)) {
            $firstRun = time();
            wp_schedule_event($firstRun, 'weekly', self::$cron_weekly_hook);
        }
    }

    public static function setup_monthly_cron()
    {

        if (!wp_next_scheduled(self::$cron_monthly_hook)) {
            $firstRun = time();
            wp_schedule_event($firstRun, 'monthly', self::$cron_monthly_hook);
        }
    }


    public static function clear_all()
    {
        if (wp_next_scheduled(self::$cron_daily_hook)) {
            wp_clear_scheduled_hook(self::$cron_daily_hook);
        }
        if (wp_next_scheduled(self::$cron_weekly_hook)) {
            wp_clear_scheduled_hook(self::$cron_weekly_hook);
        }
        if (wp_next_scheduled(self::$cron_monthly_hook)) {
            wp_clear_scheduled_hook(self::$cron_monthly_hook);
        }
    }
}

new MF_Crons();
