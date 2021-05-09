<?php
/**
 * Plugin Name: Publik
 * Description: An interface to bulk post on Wordpress and social media
 * Author: Jonathan Oliveira
 * Text Domain: publik
 * Version: 2.0.0
 */

require "vendor/autoload.php";

define( 'PUBLIK_URL', plugin_dir_url( __FILE__ ) );

use Publik\Setup;
use Publik\Services\Cron;

class Publik
{
    public static function init()
    {
        Setup::init();
        //Cron::init();
    }
}

Publik::init();