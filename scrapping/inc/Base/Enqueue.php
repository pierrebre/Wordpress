<?php

/**
 * @link              Pierre VPCrazy
 * @since             1.0.0
 * @package           Scrapping
 */

namespace Inc\Base;

use \Inc\Base\BaseController;


class Enqueue extends BaseController
{

    public function register()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
    }

    function enqueue()
    {
        // enqueue all scripts
        wp_enqueue_style('mypluginstyle', $this->plugin_url . 'assets/scrapping_css.css');
        wp_enqueue_script('mypluginscript', $this->plugin_url . 'assets/scrapping_js.js');
    }
}
