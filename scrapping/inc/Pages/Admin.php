<?php

/**
 * @link              Pierre VPCrazy
 * @since             1.0.0
 * @package           Scrapping
 */

namespace Inc\Pages;

use \Inc\Base\BaseController;
use \Inc\Api\SettingsApi;
use Inc\Api\Callbacks\AdminCallbacks;

class Admin extends BaseController
{
   public $settings;
   public $callbacks;
   public $pages = array();
   public $subpages = array();


   public function register()
   {
      $this->settings = new SettingsApi();

      $this->callbacks = new AdminCallbacks();

      $this->setPages();

      $this->setSubPages();

      $this->settings->addPages($this->pages)->withSubPage('Paramètres')->addSubPages($this->subpages)->register();
   }

   public function setPages()
   {

      $this->pages = array(
         array(
            'page_title' => 'Scrapping Plugin',
            'menu_title' => 'Scrapping',
            'capability' => 'manage_options',
            'menu_slug' => 'scrapping_plugin',
            'callback' => array($this->callbacks, 'adminDashboard'),
            'icon_url' => 'dashicons-code-standards',
            'position' => 110

         )
      );
   }

   public function setSubPages()
   {

      $this->subpages = array(
         array(
            'parent_slug' => 'scrapping_plugin',
            'page_title' => 'Custom Post Types',
            'menu_title' => 'Page a monitorer',
            'capability' => 'manage_options',
            'menu_slug' => 'add_page',
            'callback' => array($this->callbacks, 'add_pageDashboard')
         ),
      );
   }
}
