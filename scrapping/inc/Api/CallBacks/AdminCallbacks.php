<?php

/**
 * @link              Pierre VPCrazy
 * @since             1.0.0
 * @package           Scrapping
 */

namespace Inc\Api\Callbacks;

use Inc\Base\BaseController;
use Inc\Base\VPCrazy_Alert;

class AdminCallbacks extends BaseController
{
    
    public function adminDashboard()
    {
        if (isset($_POST['modifier']) && isset($_POST['parametre-nonce'])) {

            if (!wp_verify_nonce($_POST['parametre-nonce'], 'parametre')) {
                var_dump("error02");
                // VPCrazy_Alert::set('La nonce est erronée.', 'danger');
                return false;
            }
            if (empty($_POST['nb_curl'])) {
                // VPCrazy_Alert::set('Veuillez saisir le nombre de threads nécéssaire', 'danger');
                return false;
            }
            if (empty($_POST['email_notif'])) {
                //VPCrazy_Alert::set('L\'email est nécessaire.', 'danger');
                return false;
            }
            if (empty($_POST['tel_notif'])) {
                // VPCrazy_Alert::set('Le nom est nécessaire.', 'danger');
                return false;
            }
            if (empty($_POST['nb_archive'])) {
                //  VPCrazy_Alert::set('Le nom est nécessaire.', 'danger');
                return false;
            }

            update_option('nb_threads', $_POST['nb_curl']);
            update_option('email_notif', $_POST['email_notif']);
            update_option('tel_notif', $_POST['tel_notif']);
            update_option('nb_archives',  $_POST['nb_archive']);
        } else {
            //  VPCrazy_Alert::set('Formulaie incorrect.', 'danger');
            return false;
        }

        return require_once("$this->plugin_path/templates/admin.php");
    }

    public function add_pageDashboard()
    {
        if (isset($_POST['btn_cron']) && isset($_POST['cron-nonce'])) {
            if (!wp_verify_nonce($_POST['cron-nonce'], 'cron_monitoring')) {
                // VPCrazy_Alert::set('La nonce est erronée.', 'danger');
                return false;
            }
            if(isse($_POST['choix'])){
                return false;
            }

            
        } else {
            //  VPCrazy_Alert::set('Formulaie incorrect.', 'danger');
            return false;
        }

        return require_once("$this->plugin_path/templates/add_page.php");
    }
}
