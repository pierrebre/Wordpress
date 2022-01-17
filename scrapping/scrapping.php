<?php

/**
 
 *
 * @link              Pierre VPCrazy
 * @since             1.0.0
 * @package           Scrapping
 *
 * @wordpress-plugin
 * Plugin Name:       Scrapping
 * Plugin URI:        https://vpcrazy.com/
 * Description:       Scrapping webpage.
 * Version:           1.0.0
 * Author:            Pierre VPCrazy 
 * Author URI:        https://vpcrazy.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       scrapping
 * Domain Path:       /languages
 */



/*

This plugin is use for scrapping a webpage 

*/


// If this file is called firectly, abort!!!

if (!defined('ABSPATH')) {
    die;
}

// Require once the Composer Autoload
if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

/**
 * The code that runs during plugin activation
 */
function activate_scrapping()
{
    Inc\Base\Activate::activate();
}
register_activation_hook(__FILE__, 'activate_scrapping');

/**
 * The code that runs during plugin deactivation
 */
function deactivate_scrapping()
{
    Inc\Base\Deactivate::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_scrapping');


/**
 * Initialize all the core classes of the plugin
 */
if (class_exists('Inc\\Init')) {
    Inc\Init::register_services();
}



