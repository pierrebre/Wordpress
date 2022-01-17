<?php

/**
 * Plugin Name: WPvivid Backup MainWP
 * Plugin URI: https://mainwp.com/
 * Description: WPvivid Backup for MainWP enables you to create and download backups of a specific child site, set backup schedules, connect with your remote storage and set settings for all of your child sites directly from your MainWP dashboard.
 * Version: 0.9.15
 * Author: WPvivid Team
 * Author URI: https://wpvivid.com
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/copyleft/gpl.html
 * Documentation URI: https://wpvivid.com/get-started-mainwp-wpvivid-backup-extension.html
 */

define('MAINWP_WPVIVID_EXTENSION_PLUGIN_DIR',dirname(__FILE__));
define('MAINWP_WPVIVID_EXTENSION_PLUGIN_URL',plugins_url('',__FILE__));
define('MAINWP_WPVIVID_SUCCESS','success');
define('MAINWP_WPVIVID_FAILED','failed');

use MainWP\Dashboard;

class Mainwp_WPvivid_Extension_Activator
{
    protected $plugin_handle = 'wpvivid-backup-mainwp';
    protected $product_id = 'WPvivid Backup MainWP';
    protected $version = '0.9.15';
    protected $childEnabled;
    public $childKey;
    public $childFile;
    protected $mainwpMainActivated;

    private $remote;

    public function __construct()
    {
        $this->load_dependencies();

        $this->remote=new Mainwp_WPvivid_Remote_collection();
        $this->childFile = __FILE__;
        add_filter( 'mainwp_getextensions', array( &$this, 'get_this_extension' ) );
        add_action( 'admin_init', array( &$this, 'admin_init' ) );

        $this->mainwpMainActivated = apply_filters( 'mainwp_activated_check', false );
        if ( $this->mainwpMainActivated !== false )
        {
            $this->activate_this_plugin();
        } else {
            add_action( 'mainwp_activated', array( &$this, 'activate_this_plugin' ) );
        }

        Mainwp_WPvivid_Extension_Option::get_instance()->init_options();
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->init_db_options();
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->import_settings();
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->import_global_settings();

        $this->load_ajax_hook();
        add_filter( 'mainwp_getsubpages_sites', array( &$this, 'managesites_subpage' ), 10, 1 );
        add_filter( 'mainwp_sync_others_data', array( $this, 'sync_others_data' ), 10, 2 );
        add_action( 'mainwp_site_synced', array( $this, 'synced_site' ), 10, 2 );

        //add_filter( 'mainwp-sync-extensions-options', array( &$this, 'mainwp_sync_extensions_options' ), 10, 1 );

        add_action( 'mainwp_delete_site', array( &$this, 'delete_site_data' ), 10, 1 );
        add_filter( 'mainwp_getprimarybackup_methods', array( $this, 'primary_backups_method' ), 10, 1 );

        add_filter('mwp_wpvivid_set_schedule_notice', array($this, 'set_schedule_notice'), 10, 2);
        add_filter('mwp_wpvivid_add_remote_storage_list', array( $this, 'add_remote_storage_list' ), 10);

        if(!defined( 'DOING_CRON' ))
        {
            if(wp_get_schedule('mwp_wpvivid_check_version_event')===false)
            {
                wp_schedule_event(time()+10, 'hourly', 'mwp_wpvivid_check_version_event');
            }
            if(wp_get_schedule('mwp_wpvivid_refresh_latest_pro_version_event')===false)
            {
                wp_schedule_event(time()+10, 'daily', 'mwp_wpvivid_refresh_latest_pro_version_event');
            }
        }
    }

    public function wpvivid_cron_schedules($schedules)
    {
        if(!isset($schedules["hourly"])){
            $schedules["hourly"] = array(
                'interval' => 3600,
                'display' => __('Once Hourly'));
        }
        return $schedules;
    }

    public function load_dependencies()
    {
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. 'wpvivid-backup-mainwp-setting.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. 'wpvivid-backup-mainwp-subpage.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. 'wpvivid-backup-mainwp-option.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. 'wpvivid-backup-mainwp-db-option.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/admin/wpvivid-backup-mainwp-backuppage.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/admin/wpvivid-backup-mainwp-settingpage.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/admin/wpvivid-backup-mainwp-schedulepage.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/admin/wpvivid-backup-mainwp-remotepage.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/admin/wpvivid-backup-mainwp-backuprestorepage.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/admin/wpvivid-backup-mainwp-loginpage.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/admin/wpvivid-backup-mainwp-dashboardpage.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/admin/wpvivid-backup-mainwp-capabilitypage.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/admin/wpvivid-backup-mainwp-incremental-backup.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/admin/wpvivid-backup-mainwp-white-label.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/includes/class-wpvivid-mainwp-connect-server.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/includes/class-wpvivid-crypt.php';
        include_once dirname(__FILE__) .DIRECTORY_SEPARATOR. '/includes/class-wpvivid-remote-collection.php';
    }

    public function load_ajax_hook()
    {
        add_action('wp_ajax_mwp_wpvivid_sync_schedule', array($this, 'sync_schedule'));
        add_action('wp_ajax_mwp_wpvivid_sync_incremental_schedule', array($this, 'sync_incremental_schedule'));
        add_action('wp_ajax_mwp_wpvivid_sync_setting', array($this, 'sync_setting'));
        add_action('wp_ajax_mwp_wpvivid_sync_remote', array($this, 'sync_remote'));
        add_action('wp_ajax_mwp_wpvivid_sync_menu_capability', array($this, 'sync_menu_capability'));
        add_action('wp_ajax_mwp_wpvivid_sync_white_label', array($this, 'sync_white_label'));
        //
        add_action('wp_ajax_mwp_wpvivid_switch_pro_setting', array($this, 'switch_pro_setting'));
        add_action('wp_ajax_mwp_wpvivid_set_individual', array( $this, 'set_individual'));
        add_action('wp_ajax_mwp_wpvivid_active_plugin', array($this, 'active_plugin'));
        add_action('wp_ajax_mwp_wpvivid_upgrade_plugin', array($this, 'upgrade_plugin'));
        //check wpvivid login status
        add_action('wp_ajax_mwp_wpvivid_refresh_mainwp_status', array($this, 'refresh_mainwp_status'));
        add_action('wp_ajax_mwp_wpvivid_check_repair_pro', array($this, 'check_repair_pro'));
        add_action('wp_ajax_mwp_wpvivid_repair_pro', array($this, 'repair_pro'));
        add_action('wp_ajax_mwp_wpvivid_check_plugin_install_status', array($this, 'check_plugin_install_status'));
        add_action('wp_ajax_mwp_wpvivid_check_plugin_active_status', array($this, 'check_plugin_active_status'));
        add_action('wp_ajax_mwp_wpvivid_check_plugin_update_status', array($this, 'check_plugin_update_status'));
        add_action('wp_ajax_mwp_wpvivid_check_plugin_login_status', array($this, 'check_plugin_login_status'));
        add_action('wp_ajax_mwp_wpvivid_sync_childsite', array($this, 'sync_childsite'));
        //install wpvivid pro plugin
        add_action('wp_ajax_mwp_wpvivid_prepare_install_plugin_theme', array($this, 'prepare_install_plugin_theme'));
        add_action('wp_ajax_mwp_wpvivid_install_plugin_theme', array($this, 'install_plugin_theme'));
        //login wpvivid account
        add_action('wp_ajax_mwp_wpvivid_connect_account', array($this, 'connect_account'));
        add_action('wp_ajax_mwp_wpvivid_login_account_addon', array($this, 'login_account_addon'));
        //backup
        add_action('wp_ajax_mwp_wpvivid_get_status',array( $this,'get_status'));
        add_action('wp_ajax_mwp_wpvivid_get_backup_list',array($this,'get_backup_list'));
        add_action('wp_ajax_mwp_wpvivid_get_backup_schedule',array($this,'get_backup_schedule'));
        add_action('wp_ajax_mwp_wpvivid_get_default_remote',array($this,'get_default_remote'));
        add_action('wp_ajax_mwp_wpvivid_prepare_backup',array( $this,'prepare_backup'));
        add_action('wp_ajax_mwp_wpvivid_backup_now',array( $this,'backup_now'));
        add_action('wp_ajax_mwp_wpvivid_view_backup_task_log',array($this,'view_backup_task_log'));
        add_action('wp_ajax_mwp_wpvivid_backup_cancel',array($this, 'backup_cancel'));
        //schedule side bar
        add_action('wp_ajax_mwp_wpvivid_read_last_backup_log',array( $this,'read_last_backup_log'));
        //backup list
        add_action('wp_ajax_mwp_wpvivid_set_security_lock',array($this, 'set_security_lock'));
        add_action('wp_ajax_mwp_wpvivid_view_log',array( $this,'view_log'));
        add_action('wp_ajax_mwp_wpvivid_init_download_page',array($this, 'init_download_page'));
        add_action('wp_ajax_mwp_wpvivid_prepare_download_backup',array($this,'prepare_download_backup'));
        add_action('wp_ajax_mwp_wpvivid_get_download_task', array($this,'get_download_task'));
        add_action('wp_ajax_mwp_wpvivid_download_backup',array($this,'download_backup'));
        add_action('wp_ajax_mwp_wpvivid_delete_backup',array( $this,'delete_backup'));
        add_action('wp_ajax_mwp_wpvivid_delete_backup_array',array($this,'delete_backup_array'));
        //schedule
        add_action('wp_ajax_mwp_wpvivid_set_schedule', array($this, 'set_schedule'));
        //global schedule
        add_action('wp_ajax_mwp_wpvivid_set_global_schedule', array($this, 'set_global_schedule'));
        //setting
        add_action('wp_ajax_mwp_wpvivid_set_general_setting', array($this, 'set_general_setting'));
        //global setting
        add_action('wp_ajax_mwp_wpvivid_set_global_general_setting', array($this, 'set_global_general_setting'));
        //global remote
        add_action('wp_ajax_mwp_wpvivid_add_remote',array($this,'add_remote'));
        add_action('wp_ajax_mwp_wpvivid_delete_remote',array($this,'delete_remote'));
        add_action('wp_ajax_mwp_wpvivid_set_default_remote_storage',array($this,'set_default_remote_storage'));
        //check pro need update
        add_action('mwp_wpvivid_check_version_event',array( $this,'mwp_wpvivid_check_version_event'));
        add_action('mwp_wpvivid_refresh_latest_pro_version_event',array($this, 'mwp_wpvivid_refresh_latest_pro_version_event'));
        //custom addon
        add_action('wp_ajax_mwp_wpvivid_get_database_tables', array($this, 'get_database_tables'));
        add_action('wp_ajax_mwp_wpvivid_get_themes_plugins', array($this, 'get_themes_plugins'));
        add_action('wp_ajax_mwp_wpvivid_get_uploads_tree_data', array($this, 'get_uploads_tree_data'));
        add_action('wp_ajax_mwp_wpvivid_get_content_tree_data', array($this, 'get_content_tree_data'));
        add_action('wp_ajax_mwp_wpvivid_get_additional_folder_tree_data', array($this, 'get_additional_folder_tree_data'));
        add_action('wp_ajax_mwp_wpvivid_connect_additional_database_addon', array($this, 'connect_additional_database_addon'));
        add_action('wp_ajax_mwp_wpvivid_add_additional_database_addon', array($this, 'add_additional_database_addon'));
        add_action('wp_ajax_mwp_wpvivid_remove_additional_database_addon', array($this, 'remove_additional_database_addon'));
        add_action('wp_ajax_mwp_wpvivid_update_backup_exclude_extension_addon', array($this, 'update_backup_exclude_extension_addon'));
        //global custom addon
        add_action('wp_ajax_mwp_wpvivid_update_global_schedule_backup_exclude_extension_addon', array($this, 'update_global_schedule_backup_exclude_extension_addon'));
        //backup addon
        add_action('wp_ajax_mwp_wpvivid_get_default_remote_addon',array($this,'get_default_remote_addon'));
        add_action('wp_ajax_mwp_wpvivid_prepare_backup_addon', array($this, 'prepare_backup_addon'));
        add_action('wp_ajax_mwp_wpvivid_backup_now_addon', array($this, 'backup_now_addon'));
        add_action('wp_ajax_mwp_wpvivid_list_task_addon', array($this, 'list_task_addon'));
        add_action('wp_ajax_mwp_wpvivid_delete_ready_task_addon', array($this, 'delete_ready_task_addon'));
        add_action('wp_ajax_mwp_wpvivid_backup_cancel_addon', array($this, 'backup_cancel_addon'));
        //backup & restore addon
        add_action('wp_ajax_mwp_wpvivid_achieve_local_backup_addon', array($this, 'achieve_local_backup_addon'));
        add_action('wp_ajax_mwp_wpvivid_achieve_remote_backup_info_addon', array($this, 'achieve_remote_backup_info_addon'));
        add_action('wp_ajax_mwp_wpvivid_achieve_remote_backup_addon', array($this, 'achieve_remote_backup_addon'));
        add_action('wp_ajax_mwp_wpvivid_set_security_lock_addon', array($this, 'set_security_lock_addon'));
        add_action('wp_ajax_mwp_wpvivid_set_remote_security_lock_addon', array($this, 'set_remote_security_lock_addon'));
        add_action('wp_ajax_mwp_wpvivid_delete_local_backup_addon', array($this, 'delete_local_backup_addon'));
        add_action('wp_ajax_mwp_wpvivid_delete_local_backup_array_addon', array($this, 'delete_local_backup_array_addon'));
        add_action('wp_ajax_mwp_wpvivid_delete_remote_backup_addon', array($this, 'delete_remote_backup_addon'));
        add_action('wp_ajax_mwp_wpvivid_delete_remote_backup_array_addon', array($this, 'delete_remote_backup_array_addon'));
        add_action('wp_ajax_mwp_wpvivid_view_log_addon', array($this, 'view_log_addon'));
        add_action('wp_ajax_mwp_wpvivid_init_download_page_addon', array($this, 'init_download_page_addon'));
        add_action('wp_ajax_mwp_wpvivid_prepare_download_backup_addon',array($this,'prepare_download_backup_addon'));
        add_action('wp_ajax_mwp_wpvivid_get_download_progress_addon', array($this, 'get_download_progress_addon'));
        add_action('wp_ajax_mwp_wpvivid_rescan_local_folder_addon', array($this, 'rescan_local_folder_addon'));
        add_action('wp_ajax_mwp_wpvivid_get_backup_addon_list', array($this, 'get_backup_addon_list'));
        //schedule addon
        add_action('wp_ajax_mwp_wpvivid_get_schedules_addon', array($this, 'get_schedules_addon'));
        add_action('wp_ajax_mwp_wpvivid_create_schedule_addon', array($this, 'create_schedule_addon'));
        add_action('wp_ajax_mwp_wpvivid_update_schedule_addon', array($this, 'update_schedule_addon'));
        add_action('wp_ajax_mwp_wpvivid_delete_schedule_addon', array($this, 'delete_schedule_addon'));
        add_action('wp_ajax_mwp_wpvivid_save_schedule_status_addon', array($this, 'save_schedule_status_addon'));
        //global schedule addon
        add_action('wp_ajax_mwp_wpvivid_global_create_schedule_addon', array($this, 'global_create_schedule_addon'));
        add_action('wp_ajax_mwp_wpvivid_edit_global_schedule_addon', array($this, 'edit_global_schedule_addon'));
        add_action('wp_ajax_mwp_wpvivid_global_update_schedule_addon', array($this, 'global_update_schedule_addon'));
        add_action('wp_ajax_mwp_wpvivid_global_delete_schedule_addon', array($this, 'global_delete_schedule_addon'));
        add_action('wp_ajax_mwp_wpvivid_global_save_schedule_status_addon', array($this, 'global_save_schedule_status_addon'));
        add_action('wp_ajax_mwp_wpvivid_edit_global_schedule_mould_addon', array($this, 'edit_global_schedule_mould_addon'));
        add_action('wp_ajax_mwp_wpvivid_delete_global_schedule_mould_addon', array($this, 'delete_global_schedule_mould_addon'));
        //upgrade plugin addon
        add_action('wp_ajax_mwp_wpvivid_upgrade_plugin_addon', array($this, 'upgrade_plugin_addon'));
        add_action('wp_ajax_mwp_wpvivid_get_upgrade_progress_addon', array($this, 'get_upgrade_progress_addon'));
        //global remote addon
        add_action('wp_ajax_mwp_wpvivid_retrieve_global_remote_addon', array($this, 'retrieve_global_remote_addon'));
        add_action('wp_ajax_mwp_wpvivid_update_global_remote_addon', array($this, 'update_global_remote_addon'));
        add_action('wp_ajax_mwp_wpvivid_delete_global_remote_addon', array($this, 'delete_global_remote_addon'));
        add_action('wp_ajax_mwp_wpvivid_sync_global_remote_addon', array($this, 'sync_global_remote_addon'));
        //archieve pro website list
        add_action('wp_ajax_mwp_wpvivid_archieve_website_list', array($this, 'archieve_website_list'));
        add_action('wp_ajax_mwp_wpvivid_archieve_website_list_ex', array($this, 'archieve_website_list_ex'));
        add_action('wp_ajax_mwp_wpvivid_get_website_list', array($this, 'get_website_list'));
        add_action('wp_ajax_mwp_wpvivid_archieve_all_website_list', array($this, 'archieve_all_website_list'));
        add_action('wp_ajax_mwp_wpvivid_get_remote_storage_list', array($this, 'get_remote_storage_list'));
        add_action('wp_ajax_mwp_wpvivid_get_schedule_mould_list', array($this, 'get_schedule_mould_list'));
        //setting addon
        add_action('wp_ajax_mwp_wpvivid_set_general_setting_addon', array($this, 'set_general_setting_addon'));
        //global setting addon
        add_action('wp_ajax_mwp_wpvivid_set_global_general_setting_addon', array($this, 'set_global_general_setting_addon'));
        //capability addon
        add_action('wp_ajax_mwp_wpvivid_save_menu_capability_addon', array($this, 'save_menu_capability_addon'));
        //global capability addon
        add_action('wp_ajax_mwp_wpvivid_save_global_menu_capability_addon', array($this, 'save_global_menu_capability_addon'));
        //incremental backup addon
        add_action('wp_ajax_mwp_wpvivid_refresh_incremental_tables', array($this, 'refresh_incremental_tables'));
        add_action('wp_ajax_mwp_wpvivid_enable_incremental_backup', array($this, 'enable_incremental_backup'));
        add_action('wp_ajax_mwp_wpvivid_set_incremental_backup_schedule', array($this, 'set_incremental_backup_schedule'));
        add_action('wp_ajax_mwp_wpvivid_update_incremental_backup_exclude_extension_addon', array($this, 'update_incremental_backup_exclude_extension_addon'));
        add_action('wp_ajax_mwp_wpvivid_incremental_connect_additional_database_addon', array($this, 'incremental_connect_additional_database_addon'));
        add_action('wp_ajax_mwp_wpvivid_incremental_add_additional_database_addon', array($this, 'incremental_add_additional_database_addon'));
        add_action('wp_ajax_mwp_wpvivid_incremental_remove_additional_database_addon', array($this, 'incremental_remove_additional_database_addon'));
        add_action('wp_ajax_mwp_wpvivid_achieve_incremental_child_path_addon', array($this, 'achieve_incremental_child_path_addon'));
        add_action('wp_ajax_mwp_wpvivid_archieve_incremental_remote_folder_list_addon', array($this, 'archieve_incremental_remote_folder_list_addon'));
        //global incremental backup addon
        add_action('wp_ajax_mwp_wpvivid_set_global_incremental_backup_schedule', array($this, 'set_global_incremental_backup_schedule'));
        add_action('wp_ajax_mwp_wpvivid_update_global_incremental_backup_schedule', array($this, 'update_global_incremental_backup_schedule'));
        add_action('wp_ajax_mwp_wpvivid_edit_global_incremental_schedule_mould_addon', array($this, 'edit_global_incremental_schedule_mould_addon'));
        add_action('wp_ajax_mwp_wpvivid_delete_global_incremental_schedule_mould_addon', array($this, 'delete_global_incremental_schedule_mould_addon'));
        add_filter('mwp_wpvivid_custom_backup_data_transfer', array($this, 'mwp_wpvivid_custom_backup_data_transfer'), 10, 3);
        //white label addon
        add_action('wp_ajax_mwp_wpvivid_set_white_label_setting', array($this, 'set_white_label_setting'));
        //global white label addon
        add_action('wp_ajax_mwp_wpvivid_global_set_white_label_setting', array($this, 'global_set_white_label_setting'));
    }

    public function sync_others_data( $data, $pWebsite = null )
    {
        if ( ! is_array( $data ) )
        {
            $data = array();
        }

        $data['syncWPvividData'] = 1;

        return $data;
    }

    public function handle_custom_tree_data($options){
        if(isset($options['uploads_option']['exclude_uploads_list']) && !empty($options['uploads_option']['exclude_uploads_list'])){
            foreach ($options['uploads_option']['exclude_uploads_list'] as $key => $value){
                if($value['type'] === 'wpvivid-custom-li-folder-icon'){
                    $value['type'] = 'mwp-wpvivid-custom-li-folder-icon';
                }
                else if($value['type'] === 'wpvivid-custom-li-file-icon'){
                    $value['type'] = 'mwp-wpvivid-custom-li-file-icon';
                }
                $options['uploads_option']['exclude_uploads_list'][$key] = $value;
            }
        }
        if(isset($options['content_option']['exclude_content_list']) && !empty($options['content_option']['exclude_content_list'])){
            foreach ($options['content_option']['exclude_content_list'] as $key => $value){
                if($value['type'] === 'wpvivid-custom-li-folder-icon'){
                    $value['type'] = 'mwp-wpvivid-custom-li-folder-icon';
                }
                else if($value['type'] === 'wpvivid-custom-li-file-icon'){
                    $value['type'] = 'mwp-wpvivid-custom-li-file-icon';
                }
                $options['content_option']['exclude_content_list'][$key] = $value;
            }
        }
        if(isset($options['other_option']['include_other_list']) && !empty($options['other_option']['include_other_list'])){
            foreach ($options['other_option']['include_other_list'] as $key => $value){
                if($value['type'] === 'wpvivid-custom-li-folder-icon'){
                    $value['type'] = 'mwp-wpvivid-custom-li-folder-icon';
                }
                else if($value['type'] === 'wpvivid-custom-li-file-icon'){
                    $value['type'] = 'mwp-wpvivid-custom-li-file-icon';
                }
                $options['other_option']['include_other_list'][$key] = $value;
            }
        }
        return $options;
    }

    public function synced_site( $pWebsite, $information = array() )
    {
        if ( is_array( $information ) )
        {
            if ( isset( $information['syncWPvividData'] ) )
            {
                if(isset($information['syncWPvividSetting'])){
                    $data['settings'] = isset($information['syncWPvividSetting']['setting']) ? serialize($information['syncWPvividSetting']['setting']) : '';
                    $data_meta['settings'] = isset($information['syncWPvividSetting']['setting']) ? ($information['syncWPvividSetting']['setting']) : '';//
                    $data['settings_addon'] = isset($information['syncWPvividSetting']['setting_addon']) ? serialize($information['syncWPvividSetting']['setting_addon']) : '';
                    $data_meta['settings_addon'] = isset($information['syncWPvividSetting']['setting_addon']) ? ($information['syncWPvividSetting']['setting_addon']) : '';//
                    $data['schedule'] = isset($information['syncWPvividSetting']['schedule']) ? serialize($information['syncWPvividSetting']['schedule']) : '';
                    $data_meta['schedule'] = isset($information['syncWPvividSetting']['schedule']) ? ($information['syncWPvividSetting']['schedule']) : '';//
                    $data['schedule_addon'] = isset($information['syncWPvividSetting']['schedule_addon']) ? serialize($information['syncWPvividSetting']['schedule_addon']) : '';
                    $data_meta['schedule_addon'] = isset($information['syncWPvividSetting']['schedule_addon']) ? ($information['syncWPvividSetting']['schedule_addon']) : '';//
                    $data['remote'] = isset($information['syncWPvividSetting']['remote']) ? serialize($information['syncWPvividSetting']['remote']) : '';
                    $data_meta['remote'] = isset($information['syncWPvividSetting']['remote']) ? ($information['syncWPvividSetting']['remote']) : '';//
                    if(isset($information['syncWPvividSetting']['backup_custom_setting'])) {
                        $information['syncWPvividSetting']['backup_custom_setting'] = $this->handle_custom_tree_data($information['syncWPvividSetting']['backup_custom_setting']);
                        $data['backup_custom_setting'] = serialize($information['syncWPvividSetting']['backup_custom_setting']);
                        $data_meta['backup_custom_setting'] = ($information['syncWPvividSetting']['backup_custom_setting']);//
                    }
                    else{
                        $data['backup_custom_setting'] = '';
                        $data_meta['backup_custom_setting'] = '';//
                    }
                    $data_meta['menu_capability'] = isset($information['syncWPvividSetting']['menu_capability']) ? $information['syncWPvividSetting']['menu_capability'] : '';
                    $data_meta['white_label_setting'] = isset($information['syncWPvividSetting']['white_label_setting']) ? $information['syncWPvividSetting']['white_label_setting'] : '';

                    /*$data['incremental_backup_setting']
                    $data['enable_incremental_schedules']=WPvivid_Setting::get_option('wpvivid_enable_incremental_schedules',false);
                    $data['incremental_schedules']=WPvivid_Setting::get_option('wpvivid_incremental_schedules');
                    $data['incremental_history']=WPvivid_Setting::get_option('wpvivid_incremental_backup_history', array());
                    $data['incremental_backup_data']=WPvivid_Setting::get_option('wpvivid_incremental_backup_data',array());
                    $data['incremental_remote_backup_count']=WPvivid_Setting::get_option('wpvivid_incremental_remote_backup_count_addon', $default);*/

                    $data_meta['incremental_backup_setting'] = isset($information['syncWPvividSetting']['incremental_backup_setting']) ? $information['syncWPvividSetting']['incremental_backup_setting'] : array();
                    Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_sync_options($pWebsite->id, $data_meta);
                    $data['need_update'] = isset($information['syncWPvividSetting']['need_update']) ? $information['syncWPvividSetting']['need_update'] : '';
                    $data['current_version'] = isset($information['syncWPvividSetting']['current_version']) ? $information['syncWPvividSetting']['current_version'] : '';
                    if(isset($information['syncWPvividSetting']['is_pro'])) {
                        $data['is_pro'] = $information['syncWPvividSetting']['is_pro'] === true ? 1 : 0;
                        if($data['is_pro'] === 1){
                            $sync_first = $this->get_global_first_init();
                            if(!$sync_first){
                                $this->set_global_first_init('first');
                            }
                        }
                    }
                    else{
                        $data['is_pro'] = 0;
                    }
                    if(isset($information['syncWPvividSetting']['is_install'])){
                        $data['is_install'] = $information['syncWPvividSetting']['is_install'] === true ? 1 : 0;
                    }
                    else{
                        $data['is_install'] = 0;
                    }
                    if(isset($information['syncWPvividSetting']['is_login'])){
                        $data['is_login'] = $information['syncWPvividSetting']['is_login'] === true ? 1 : 0;
                    }
                    else{
                        $data['is_login'] = 0;
                    }
                    $data['latest_version'] = isset($information['syncWPvividSetting']['latest_version']) ? $information['syncWPvividSetting']['latest_version'] : '';
                    $data['time_zone'] = isset($information['syncWPvividSetting']['time_zone']) ? $information['syncWPvividSetting']['time_zone'] : 0;
                    $last_backup_report = isset($information['syncWPvividSetting']['last_backup_report']) ? $information['syncWPvividSetting']['last_backup_report'] : array();
                    $this->set_backup_report($pWebsite->id, $last_backup_report);
                    //$data['report_addon'] = isset($information['syncWPvividSetting']['report_addon']) ? base64_encode(serialize($information['syncWPvividSetting']['report_addon'])) : '';

                    $login_options = $this->get_global_login_addon();
                    if(isset($login_options['wpvivid_pro_login_cache']))
                    {
                        if (isset($data['current_version']))
                        {
                            if(version_compare($login_options['wpvivid_pro_login_cache']['pro']['version'], $data['current_version'],'>'))
                            {
                                $data['need_update']=1;
                                $data['latest_version']=$login_options['wpvivid_pro_login_cache']['pro']['version'];
                            }
                            else{
                                $data['need_update']=0;
                            }
                        }
                        else{
                            $data['need_update']=1;
                            $data['latest_version']=$login_options['wpvivid_pro_login_cache']['pro']['version'];
                        }
                    }

                    Mainwp_WPvivid_Extension_Option::get_instance()->sync_options($pWebsite->id,$data);
                    unset($data['wpvivid_setting']);
                    unset($data['backup_custom_setting']);
                    unset($data['need_update']);
                    unset($data['current_version']);
                    unset($data['is_pro']);
                    unset($data['is_install']);
                    unset($data['is_login']);
                    unset($data['latest_version']);
                    unset($data['time_zone']);
                    //unset($data['report_addon']);
                    if(!Mainwp_WPvivid_Extension_Option::get_instance()->is_set_global_options()) {
                        Mainwp_WPvivid_Extension_Option::get_instance()->set_global_options($data);
                    }
                    unset( $information['syncWPvividSetting'] );
                    unset( $information['syncWPvividData'] );
                }
                else{
                    $data['settings'] = serialize($information['syncWPvividSettingData']);
                    $data['schedule'] = serialize($information['syncWPvividScheduleData']);
                    Mainwp_WPvivid_Extension_Option::get_instance()->sync_options($pWebsite->id,$data);
                    $data['remote'] = serialize($information['syncWPvividRemoteData']);
                    if(!Mainwp_WPvivid_Extension_Option::get_instance()->is_set_global_options()) {
                        Mainwp_WPvivid_Extension_Option::get_instance()->set_global_options($data);
                    }
                    unset( $information['syncWPvividSettingData'] );
                    unset( $information['syncWPvividRemoteData'] );
                    unset( $information['syncWPvividScheduleData'] );
                    unset( $information['syncWPvividData'] );
                    $this->set_sync_error($pWebsite->id, 2);
                }
            }
            else{
                $this->set_sync_error($pWebsite->id, 1);
                Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_update_single_option($pWebsite->id, 'is_pro', 0);
                Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_update_single_option($pWebsite->id, 'is_install', 0);
                Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_update_single_option($pWebsite->id, 'is_login', 0);
            }
        }
    }

    public function delete_site_data($website)
    {
        if ( $website )
        {
            Mainwp_WPvivid_Extension_Option::get_instance()->delete_site($website->id );
        }
    }

    /*public function mainwp_sync_extensions_options($values = array()) {
        $values['wpvivid-backup-mainwp'] = array(
            'plugin_name' => 'WPvivid Backup Plugin',
            'plugin_slug' => 'wpvivid-backuprestore/wpvivid-backuprestore.php'
        );
        return $values;
    }*/

    public function primary_backups_method( $methods )
    {
        $methods[] = array( 'value' => 'wpvivid', 'title' => 'WPvivid Backup for MainWP' );
        return $methods;
    }

    public function set_schedule_notice($notice_type, $message)
    {
        $html = '';
        if($notice_type)
        {
            $html .= __('<div class="notice notice-success is-dismissible inline" style="margin: 0; padding-top: 10px; margin-bottom: 10px;"><p>'.$message.'</p>
                                    <button type="button" class="notice-dismiss" onclick="mwp_click_dismiss_notice(this);">
                                    <span class="screen-reader-text">Dismiss this notice.</span>
                                    </button>
                                    </div>');
        }
        else{
            $html .= __('<div class="notice notice-error inline" style="margin: 0; padding: 10px; margin-bottom: 10px;"><p>' . $message . '</p></div>');
        }
        return $html;
    }

    public function check_site_id_secure($site_id)
    {
        if(Mainwp_WPvivid_Extension_Option::get_instance()->is_vaild_child_site($site_id)){
            return true;
        }
        else{
            return false;
        }
    }

    public function admin_init()
    {
        wp_enqueue_style('Mainwp Wpvivid Extension', plugin_dir_url(__FILE__) . 'admin/css/wpvivid-backup-mainwp-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('Mainwp Wpvivid Extension'.'jstree', plugin_dir_url(__FILE__) . 'admin/js/jstree/dist/themes/default/style.min.css', array(), $this->version, 'all');

        wp_enqueue_script('Mainwp Wpvivid Extension', plugin_dir_url(__FILE__) . 'admin/js/wpvivid-backup-mainwp-admin.js', array('jquery'), $this->version, false);
        wp_enqueue_script('Mainwp Wpvivid Extension'.'jstree', plugin_dir_url(__FILE__) . 'admin/js/jstree/dist/jstree.min.js', array('jquery'), $this->version, false);
        wp_localize_script('Mainwp Wpvivid Extension', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
        if(isset($_GET['id']) && !empty($_GET['id'])) {
            wp_localize_script('Mainwp Wpvivid Extension', 'site_id', $_GET['id']);
        }
    }

    public function managesites_subpage( $subPage )
    {
        $subPage[] = array(
            'title' => __( 'WPvivid Backups', 'mainwp' ),
            'slug' => 'WPvivid',
            'sitetab' => true,
            'menu_hidden' => true,
            'callback' => array( $this, 'render' ),
        );
        return $subPage;
    }

    function render()
    {
        do_action( "mainwp_pageheader_sites", "WPvivid" );
        Mainwp_WPvivid_Extension_Subpage::renderSubpage();
        do_action( "mainwp_pagefooter_sites", "WPvivid" );
    }

    function get_this_extension( $pArray )
    {
        $extension['plugin']=__FILE__;
        $extension['mainwp']=false;
        $extension['callback']=array(&$this, 'settings');
        $extension['icon']=MAINWP_WPVIVID_EXTENSION_PLUGIN_URL.'/admin/images/logo.png';
        $pArray[] = $extension;
        return $pArray;
    }

    function activate_this_plugin()
    {
        $this->mainwpMainActivated = apply_filters( 'mainwp_activated_check', $this->mainwpMainActivated );
        $this->childEnabled = apply_filters( 'mainwp_extension_enabled_check', __FILE__ );
        $this->childKey = $this->childEnabled['key'];
    }

    function settings()
    {
        do_action( 'mainwp_pageheader_extensions', $this->childFile );
        Mainwp_WPvivid_Extension_Setting::renderSetting();
        do_action( 'mainwp_pagefooter_extensions', $this->childFile );
    }

    public function sync_schedule()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['id']) && !empty($_POST['id']) && is_string($_POST['id'])) {
                $site_id = sanitize_text_field($_POST['id']);
                $check_addon = '0';
                if(isset($_POST['addon']) && !empty($_POST['addon']) && is_string($_POST['addon'])) {
                    $check_addon = sanitize_text_field($_POST['addon']);
                }
                if($check_addon == '1'){
                    $schedule_mould_name = '';
                    if(isset($_POST['schedule_mould_name']) && !empty($_POST['schedule_mould_name'])){
                        $schedule_mould_name = sanitize_text_field($_POST['schedule_mould_name']);
                    }
                    $post_data['mwp_action'] = 'wpvivid_sync_schedule_addon_mainwp';
                    $schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_mould_addon', array());
                    $schedules = $schedule_mould[$schedule_mould_name];
                    if(isset($_POST['default_setting'])){
                        $default_setting = sanitize_text_field($_POST['default_setting']);
                    }
                    else{
                        $default_setting = 'default_only';
                    }
                    $post_data['schedule'] = $schedules;
                    $post_data['default_setting'] = $default_setting;
                }
                else {
                    $post_data['mwp_action'] = 'wpvivid_set_schedule_mainwp';
                    $schedule = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule', array());
                    Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'schedule', $schedule);
                    if ($schedule['enable'] == 1) {
                        $schedule_data['enable'] = $schedule['enable'];
                        $schedule_data['recurrence'] = $schedule['type'];
                        $schedule_data['event'] = $schedule['event'];
                        $schedule_data['backup_type'] = $schedule['backup']['backup_files'];
                        if ($schedule['backup']['remote'] == 1) {
                            $schedule_data['save_local_remote'] = 'remote';
                        } else {
                            $schedule_data['save_local_remote'] = 'local';
                        }
                        $schedule_data['lock'] = 0;
                    } else {
                        $schedule_data['enable'] = $schedule['enable'];
                    }
                    $post_data['schedule'] = json_encode($schedule_data);
                }

                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);

                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function sync_incremental_schedule()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['id']) && !empty($_POST['id']) && is_string($_POST['id'])) {
                $site_id = sanitize_text_field($_POST['id']);
                $check_addon = '0';
                if(isset($_POST['addon']) && !empty($_POST['addon']) && is_string($_POST['addon'])) {
                    $check_addon = sanitize_text_field($_POST['addon']);
                }
                if($check_addon == '1'){
                    $schedule_mould_name = '';
                    if(isset($_POST['schedule_mould_name']) && !empty($_POST['schedule_mould_name'])){
                        $schedule_mould_name = sanitize_text_field($_POST['schedule_mould_name']);
                    }
                    $post_data['mwp_action'] = 'wpvivid_sync_incremental_schedule_addon_mainwp';
                    $schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('incremental_backup_setting', array());
                    $schedules = $schedule_mould[$schedule_mould_name];
                    $post_data['schedule'] = $schedules;
                    $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);

                    if (isset($information['error'])) {
                        $ret['result'] = 'failed';
                        $ret['error'] = $information['error'];
                    } else {
                        $ret['result'] = 'success';
                    }
                    echo json_encode($ret);
                }
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function sync_setting()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['id']) && !empty($_POST['id']) && is_string($_POST['id'])) {
                $site_id = sanitize_text_field($_POST['id']);
                $check_addon = '0';
                if(isset($_POST['addon']) && !empty($_POST['addon']) && is_string($_POST['addon'])) {
                    $check_addon = sanitize_text_field($_POST['addon']);
                }
                if($check_addon == '1'){
                    $post_data['mwp_action'] = 'wpvivid_set_general_setting_addon_mainwp';
                    $setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('settings_addon', array());
                    Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'settings_addon', $setting);
                }
                else {
                    $post_data['mwp_action'] = 'wpvivid_set_general_setting_mainwp';
                    $setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('settings', array());
                    Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'settings', $setting);
                }
                $post_data['setting'] = json_encode($setting);
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }

                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function sync_remote()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['id']) && !empty($_POST['id']) && is_string($_POST['id'])) {
                $site_id = sanitize_text_field($_POST['id']);
                $post_data['mwp_action'] = 'wpvivid_set_remote_mainwp';
                $remote = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('remote', array());
                $post_data['remote'] = json_encode($remote);
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function sync_menu_capability()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['id']) && !empty($_POST['id']) && is_string($_POST['id'])) {
                $site_id = sanitize_text_field($_POST['id']);
                $post_data['mwp_action'] = 'wpvivid_set_menu_capability_addon_mainwp';

                $capability_addon = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('menu_capability', array());
                if(empty($capability_addon)){
                    $capability_addon = array();
                    $capability_addon['menu_export_import'] = '1';
                    $capability_addon['menu_setting'] = '1';
                    $capability_addon['menu_debug'] = '1';
                    $capability_addon['menu_tools'] = '1';
                    $capability_addon['menu_log'] = '1';
                    $capability_addon['menu_pro_page'] = '1';
                }
                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'menu_capability', $capability_addon);

                $post_data['menu_cap'] = json_encode($capability_addon);
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);

                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function sync_white_label()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['id']) && !empty($_POST['id']) && is_string($_POST['id'])) {
                $site_id = sanitize_text_field($_POST['id']);

                $white_label = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('white_label_setting', array());
                if(empty($white_label)){
                    $white_label = array();
                }
                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'white_label_setting', $white_label);

                $post_data['mwp_action'] = 'wpvivid_set_white_label_setting_addon_mainwp';
                $post_data['setting'] = json_encode($white_label);
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);

                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function switch_pro_setting()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['pro_setting']) && is_string($_POST['pro_setting'])){
                $pro_setting = sanitize_text_field($_POST['pro_setting']);
                if($pro_setting == '1'){
                    $this->set_global_switch_pro_setting_page(1);
                }
                else{
                    $this->set_global_switch_pro_setting_page(0);
                }
                $this->set_global_select_pro($pro_setting);
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function set_individual()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['individual']) && is_string($_POST['individual'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $individual = sanitize_text_field($_POST['individual']);
                $individual = intval($individual);
                Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_update_single_option($site_id, 'individual', $individual);
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function active_plugin()
    {
        $this->mwp_ajax_check_security();
        if(isset($_POST['site_id']) && !empty($_POST['site_id'])) {
            $_POST['websiteId'] = $_POST['site_id'];
            do_action('mainwp_activePlugin');
        }
        die();
    }

    public function upgrade_plugin()
    {
        $this->mwp_ajax_check_security();
        if(isset($_POST['site_id']) && !empty($_POST['site_id'])) {
            $_POST['websiteId'] = $_POST['site_id'];
            do_action('mainwp_upgradePluginTheme');
        }
        die();
    }

    public function mwp_wpvivid_get_website_plugins_list($site_id)
    {
        $plugins = array();
        $dbwebsites = $this->mwp_get_child_websites();
        foreach ($dbwebsites as $website)
        {
            if ($website)
            {
                if ($website->id === $site_id)
                {
                    $plugins = json_decode($website->plugins, 1);
                }
            }
        }
        return $plugins;
    }

    public function refresh_mainwp_status()
    {
        $this->mwp_ajax_check_security();
        try{
            $login_options = $this->get_global_login_addon();
            if($login_options !== false && isset($login_options['wpvivid_pro_account'])) {
                if(isset($login_options['wpvivid_pro_account']['license'])){
                    $email = false;
                    $user_info = $login_options['wpvivid_pro_account']['license'];
                    $use_token = true;
                }
                else{
                    $email = $login_options['wpvivid_pro_account']['email'];
                    $user_info = $login_options['wpvivid_pro_account']['password'];
                    $use_token = false;
                }

                $server=new Mainwp_WPvivid_Connect_server();
                $ret=$server->get_mainwp_status($email,$user_info,true,$use_token, true);
                if($ret['result']=='success') {
                    if($ret['status']['pro_user']) {
                        $ret['pro_user']=1;
                    }
                    else {
                        $ret['pro_user']=0;
                    }
                    $login_options = $this->get_global_login_addon();
                    if($login_options === false || !isset($login_options['wpvivid_pro_account'])){
                        $login_options = array();
                    }
                    $login_options['wpvivid_pro_login_cache'] = $ret['status'];
                    if(isset($login_options['wpvivid_pro_account']['license'])) {
                        $pro_info['license'] = $user_info;
                    }
                    else{
                        $pro_info['email'] = $email;
                        $pro_info['password'] = $user_info;
                    }
                    $login_options['wpvivid_pro_account'] = $pro_info;
                    $this->set_global_login_addon($login_options);
                }
                else{
                    $ret['result']='failed';
                    if(!isset($ret['error'])){
                        $ret['error'] = 'Failed to connect to authentication server, please try again later.';
                    }
                }
            }
            else{
                $ret['result'] = 'failed';
                $ret['error'] = 'Failed to get previously entered login information, please login again.';
            }
            echo json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function check_repair_pro()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $plugins = $this->mwp_wpvivid_get_website_plugins_list($site_id);
                $need_repair = false;
                $slug_name = '';
                if (is_array($plugins) && count($plugins) != 0)
                {
                    foreach ($plugins as $plugin) 
                    {
                        if (strpos($plugin['slug'], 'wpvivid-backup-pro.php') !== false)
                        {
                            if ((strcmp($plugin['slug'], "wpvivid-backup-pro/wpvivid-backup-pro.php") !== 0))
                            {
                                $need_repair = true;
                                $slug_name = $plugin['slug'];
                                $this->set_is_login($site_id, 0);
                                break;
                            }
                        }
                    }
                }
                $ret['result'] = 'success';
                if ($need_repair) {
                    $ret['check_status'] = 'need_repair';
                    $ret['slug'] = $slug_name;
                } else {
                    $ret['check_status'] = 'ok';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function repair_pro()
    {
        $this->mwp_ajax_check_security();
        if(isset($_POST['site_id']) && !empty($_POST['site_id'])) {
            $_POST['websiteId'] = $_POST['site_id'];
            do_action('mainwp_deletePlugin');
        }
        die();
    }

    public function check_plugin_install_status()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && isset($_POST['slug']) && !empty($_POST['slug'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $slug = sanitize_text_field($_POST['slug']);

                $ret['result'] = 'success';

                $plugins = $this->mwp_wpvivid_get_website_plugins_list($site_id);
                if($slug === 'wpvivid-backuprestore') {
                    $ret['check_status'] = 'need-install';
                    if (is_array($plugins) && count($plugins) != 0)
                    {
                        foreach ($plugins as $plugin)
                        {
                            $reg_string = 'wpvivid-backuprestore/wpvivid-backuprestore.php';
                            if ((strcmp($plugin['slug'], $reg_string) === 0))
                            {
                                $ret['check_status'] = 'ok';
                                break;
                            }
                        }
                    }
                }
                else {
                    $ret['check_status'] = 'need-install';
                    if ( is_array( $plugins ) && count( $plugins ) != 0 )
                    {
                        foreach ($plugins as $plugin)
                        {
                            $reg_string = 'wpvivid-backup-pro/wpvivid-backup-pro.php';
                            if ((strcmp($plugin['slug'], $reg_string) === 0))
                            {
                                $latest_version = false;
                                $login_options = $this->get_global_login_addon();
                                if($login_options !== false && isset($login_options['wpvivid_pro_login_cache'])){
                                    $addons_cache = $login_options['wpvivid_pro_login_cache'];
                                    if(isset($addons_cache['pro']['version'])){
                                        $latest_version = $addons_cache['pro']['version'];
                                    }
                                }
                                if($latest_version !== false){
                                    if(version_compare($latest_version, $plugin['version'],'<=')){
                                        $ret['check_status'] = 'ok';
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function check_plugin_active_status()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && isset($_POST['slug']) && !empty($_POST['slug'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $slug = sanitize_text_field($_POST['slug']);

                $ret['result'] = 'success';

                $plugins = $this->mwp_wpvivid_get_website_plugins_list($site_id);
                if($slug === 'wpvivid-backuprestore') {
                    $ret['check_status'] = 'need-active';
                    if (is_array($plugins) && count($plugins) != 0)
                    {
                        foreach ($plugins as $plugin)
                        {
                            $reg_string = 'wpvivid-backuprestore/wpvivid-backuprestore.php';
                            if ((strcmp($plugin['slug'], $reg_string) === 0))
                            {
                                if ($plugin['active'])
                                {
                                    $ret['check_status'] = 'ok';
                                }
                                break;
                            }
                        }
                    }
                }
                else {
                    $ret['check_status'] = 'need-active';
                    if ( is_array( $plugins ) && count( $plugins ) != 0 )
                    {
                        foreach ($plugins as $plugin)
                        {
                            $reg_string = 'wpvivid-backup-pro/wpvivid-backup-pro.php';
                            if ((strcmp($plugin['slug'], $reg_string) === 0))
                            {
                                if ($plugin['active'])
                                {
                                    $ret['check_status'] = 'ok';
                                }
                                break;
                            }
                        }
                    }
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function check_plugin_update_status()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && isset($_POST['slug']) && !empty($_POST['slug'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $slug = sanitize_text_field($_POST['slug']);

                $ret['result'] = 'success';
                $ret['check_status'] = 'ok';

                if($slug === 'wpvivid-backuprestore')
                {
                    $dbwebsites = $this->mwp_get_child_websites();
                    foreach ($dbwebsites as $website) {
                        if ($website)
                        {
                            if ($website->id === $site_id)
                            {
                                $plugin_upgrades = json_decode($website->plugin_upgrades, 1);
                                if (is_array($plugin_upgrades) && count($plugin_upgrades) > 0)
                                {
                                    if (isset($plugin_upgrades['wpvivid-backuprestore/wpvivid-backuprestore.php']))
                                    {
                                        $upgrade = $plugin_upgrades['wpvivid-backuprestore/wpvivid-backuprestore.php'];
                                        if (isset($upgrade['update']))
                                        {
                                            $ret['check_status'] = 'need-update';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                else{
                    $need_update = $this->get_need_update($site_id);
                    if($need_update == '1'){
                        $ret['check_status'] = 'need-update';
                    }
                    else{
                        $ret['check_status'] = 'ok';
                    }
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_is_login($site_id)
    {
        $is_login_pro = Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_get_single_option($site_id, 'is_login', false);
        if(empty($is_login_pro)){
            $is_login_pro = false;
        }
        return $is_login_pro;
    }

    public function set_is_login($site_id, $is_login)
    {
        Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_update_single_option($site_id, 'is_login', $is_login);
    }

    public function get_latest_version($site_id)
    {
        $latest_version = Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_get_single_option($site_id, 'latest_version', '');
        if(empty($latest_version)){
            $latest_version = '';
        }
        return $latest_version;
    }

    public function set_latest_version($site_id, $version)
    {
        Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_update_single_option($site_id, 'latest_version', $version);
    }

    public function get_current_version($site_id)
    {
        $current_version = Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_get_single_option($site_id, 'current_version', '');
        return $current_version;
    }

    public function set_current_version($site_id, $version)
    {
        Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_update_single_option($site_id, 'current_version', $version);
    }

    public function get_need_update($site_id)
    {
        $need_update = Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_get_single_option($site_id, 'need_update', '');
        return $need_update;
    }

    public function set_need_update($site_id, $need_update)
    {
        Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_update_single_option($site_id, 'need_update', $need_update);
    }

    public function set_sync_error($site_id, $sync_error)
    {
        Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_update_single_option($site_id, 'sync_error', $sync_error);
    }

    public function set_backup_report($site_id, $option)
    {
        $reports = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'report_addon', array());
        if(!empty($reports)){
            foreach ($option as $key => $value){
                $reports[$key] = $value;
                $reports = $this->clean_out_of_date_report($reports, 10);
                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'report_addon', $reports);
            }
        }
        else{
            Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'report_addon', $option);
        }
    }

    public static function get_oldest_backup_id($report_list)
    {
        $oldest_id='not set';
        $oldest=0;
        foreach ($report_list as $key=>$value)
        {
            if ($oldest == 0) {
                $oldest = $value['backup_time'];
                $oldest_id = $key;
            } else {
                if ($oldest > $value['backup_time']) {
                    $oldest_id = $key;
                }
            }
        }
        return $oldest_id;
    }

    function clean_out_of_date_report($report_list, $max_report_count)
    {
        $size=sizeof($report_list);
        while($size>$max_report_count)
        {
            $oldest_id=self::get_oldest_backup_id($report_list);

            if($oldest_id!='not set')
            {
                unset($report_list[$oldest_id]);
            }
            $new_size=sizeof($report_list);
            if($new_size==$size)
            {
                break;
            }
            else
            {
                $size=$new_size;
            }
        }
        return $report_list;
    }

    public function check_plugin_login_status()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);

                $ret['result'] = 'success';
                $ret['check_status'] = 'need-login';

                $is_login_pro = $this->get_is_login($site_id);
                if($is_login_pro !== false)
                {
                    if(intval($is_login_pro) === 1)
                    {
                        $ret['check_status'] = 'ok';
                    }
                    else{
                        $ret['check_status'] = 'need-login';
                    }
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_global_first_init()
    {
        $sync_init_addon_first=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('sync_init_addon_first', '');
        return $sync_init_addon_first;
    }

    public function set_global_first_init($first)
    {
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('sync_init_addon_first', $first);
    }

    public function get_global_switch_pro_setting_page()
    {
        $switch_pro_setting_page=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('switch_pro_setting_page', '');
        return $switch_pro_setting_page;
    }

    public function set_global_switch_pro_setting_page($pro_setting_page)
    {
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('switch_pro_setting_page', $pro_setting_page);
    }

    public function get_global_select_pro()
    {
        $select_pro=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('select_pro', '');
        return $select_pro;
    }

    public function set_global_select_pro($select_pro)
    {
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('select_pro', $select_pro);
    }

    public function get_global_login_addon()
    {
        $login_addon=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('login_addon', array());
        return $login_addon;
    }

    public function set_global_login_addon($login_addon)
    {
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('login_addon', $login_addon);
    }

    public function sync_childsite()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['wp_id']) && isset($_POST['isGlobalSync'])){
                MainWP\Dashboard\MainWP_Updates_Overview::dismiss_sync_errors( false );
                MainWP\Dashboard\MainWP_Updates_Overview::sync_site();
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function prepare_install_plugin_theme()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['website_id']) && !empty($_POST['website_id']) && is_string($_POST['website_id']) &&
                isset($_POST['slug']) && !empty($_POST['slug']) && is_string($_POST['slug'])){
                $websites_id = sanitize_text_field($_POST['website_id']);
                $slug = sanitize_text_field($_POST['slug']);

                $output = array();
                if($slug === 'wpvivid-backuprestore') {
                    include_once(ABSPATH . '/wp-admin/includes/plugin-install.php');
                    $api = plugins_api('plugin_information', array(
                        'slug' => $slug,
                        'fields' => array('sections' => false),
                    ));
                    $url = $api->download_link;
                    $output['url'] = $url;
                    $output['sites'] = array();
                    if (MainWP\Dashboard\MainWP_Utility::ctype_digit($websites_id)) {
                        $website = MainWP_DB::Instance()->getWebsiteById($websites_id);
                        $output['sites'][$website->id] = MainWP\Dashboard\MainWP_Utility::map_site($website, array(
                            'id',
                            'url',
                            'name',
                        ));
                    }
                }
                else if($slug === 'wpvivid-backup-pro'){
                    $url = 'https://wpvivid.com/download-for-mainwp/download-for-mainwp-2.0.0.php';
                    $ch = curl_init();
                    $timeout = 30;
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                    $contents = curl_exec($ch);
                    curl_close($ch);

                    $ret = json_decode($contents, true);
                    if($ret['result'] == 'success'){
                        $output['result'] = 'success';
                        $output['url']   = $ret['url'];
                        $output['sites'] = array();
                        if (MainWP\Dashboard\MainWP_Utility::ctype_digit($websites_id)) {
                            $website = MainWP_DB::Instance()->getWebsiteById($websites_id);
                            $output['sites'][$website->id] = MainWP\Dashboard\MainWP_Utility::map_site($website, array(
                                'id',
                                'url',
                                'name',
                            ));
                        }
                    }
                    else{
                        $output['result'] = 'failed';
                        $output['error'] = 'Failed to get WPvivid Backup Pro download url, please try again later.';
                    }
                }
                wp_send_json( $output );
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function install_plugin_theme()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['siteId']) && !empty($_POST['siteId']) && is_string($_POST['siteId']) &&
                isset($_POST['type']) && !empty($_POST['type']) && is_string($_POST['type']) &&
                isset($_POST['url']) && !empty($_POST['url']) && is_string($_POST['url'])){
                $websites_id = sanitize_text_field($_POST['siteId']);
                $type = sanitize_text_field($_POST['type']);
                $url = sanitize_text_field($_POST['url']);

                MainWP\Dashboard\MainWP_Utility::end_session();

                //Fetch info..
                $post_data = array(
                    'type' => $type,
                );
                if ( $_POST['activatePlugin'] == 'true' ) {
                    $post_data['activatePlugin'] = 'yes';
                }
                if ( $_POST['overwrite'] == 'true' ) {
                    $post_data['overwrite'] = true;
                }

                // hook to support addition data: wpadmin_user, wpadmin_passwd
                $post_data = apply_filters( 'mainwp_perform_install_data', $post_data );

                $post_data['url'] = json_encode( $url );

                $output         = new stdClass();
                $output->ok     = array();
                $output->errors = array();
                $websites       = array( MainWP_DB::Instance()->getWebsiteById( $websites_id ) );
                MainWP\Dashboard\MainWP_Connect::fetch_urls_authed( $websites, 'installplugintheme', $post_data, array(
                    MainWP\Dashboard\MainWP_Install_Bulk::get_class_name(),
                    'install_plugin_theme_handler',
                ), $output, null, array( 'upgrade' => true ) );

                if(isset($output->ok) && !empty($output->ok)){
                    Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_update_single_option($websites_id, 'is_install', 1);
                }

                wp_send_json( $output );
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function connect_account()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['password'])){
                if(empty($_POST['password'])) {
                    $ret['result']='failed';
                    $ret['error']='A license is required.';
                    echo json_encode($ret);
                    die();
                }

                $user_info=$_POST['password'];

                if(isset($_POST['token']) && $_POST['token'] == '1'){
                    $use_token = true;
                }
                else{
                    $use_token = false;
                }
                $server=new Mainwp_WPvivid_Connect_server();
                $ret=$server->get_mainwp_status(false, $user_info, true, $use_token, true);
                if($ret['result']=='success') {
                    if($ret['status']['pro_user']) {
                        $ret['pro_user']=1;
                    }
                    else {
                        $ret['pro_user']=0;
                    }
                    $login_options = $this->get_global_login_addon();
                    if($login_options === false || !isset($login_options['wpvivid_pro_account'])){
                        $login_options = array();
                    }
                    $login_options['wpvivid_pro_login_cache'] = $ret['status'];
                    $pro_info['license'] = $user_info;
                    $login_options['wpvivid_pro_account'] = $pro_info;
                    $this->set_global_login_addon($login_options);
                }
                else{
                    $ret['result']='failed';
                    if(!isset($ret['error'])){
                        $ret['error'] = 'Failed to login.';
                    }
                }
            }
            else{
                $ret['result']='failed';
                $ret['error']='Retrieving user information failed. Please try again later.';
            }
            echo json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function login_account_addon()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['siteId']) && !empty($_POST['siteId']) && is_string($_POST['siteId'])){
                $site_id = sanitize_text_field($_POST['siteId']);
                $login_options = $this->get_global_login_addon();
                if($login_options !== false && isset($login_options['wpvivid_pro_account'])){
                    if(isset($login_options['wpvivid_pro_account']['license'])){
                        $email = false;
                        $user_info = $login_options['wpvivid_pro_account']['license'];
                        $use_token = true;
                    }
                    else{
                        $email = $login_options['wpvivid_pro_account']['email'];
                        $user_info = $login_options['wpvivid_pro_account']['password'];
                        $use_token = false;
                    }

                    $server=new Mainwp_WPvivid_Connect_server();
                    $ret=$server->login($email,$user_info,$site_id,true,$use_token, true);
                    if($ret['result']=='success')
                    {
                        if($ret['status']['pro_user'])
                        {
                            $ret['pro_user']=1;
                            if($ret['status']['check_active'])
                            {
                                $ret['check_active']=1;
                            }
                            else {
                                $ret['check_active']=0;
                                $login_options = $this->get_global_login_addon();
                                if(isset($login_options['wpvivid_pro_user']['token'])){
                                    $user_info = $login_options['wpvivid_pro_user']['token'];
                                    $ret=$server->active_site($email, $user_info, $site_id);
                                    if($ret['result']=='success') {
                                    }
                                    else{
                                        $ret['result'] = 'failed';
                                        $ret['error'] = 'Failed to activate the site, please login again.';
                                        echo json_encode($ret);
                                        die();
                                    }
                                }
                                else{
                                    $ret['result'] = 'failed';
                                    $ret['error'] = 'Failed to get token, please login again.';
                                    echo json_encode($ret);
                                    die();
                                }
                            }
                            $login_options = $this->get_global_login_addon();
                            $data['wpvivid_pro_addons_cache'] = $ret['status'];
                            $data['wpvivid_pro_user'] = $login_options['wpvivid_pro_user'];
                            $data['wpvivid_connect_key'] = $login_options['wpvivid_connect_key'];
                            $post_data['mwp_action'] = 'wpvivid_login_account_addon_mainwp';
                            $post_data['login_info'] = $data;
                            $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                            if (isset($information['error'])) {
                                $ret['result'] = 'failed';
                                $ret['error'] = $information['error'];
                            } else {
                                $ret['result'] = 'success';
                                $this->set_is_login($site_id, 1);
                                if(isset($information['need_update'])){
                                    if($information['need_update']){
                                        $need_update = 1;
                                    }
                                    else{
                                        $need_update = 0;
                                    }
                                }
                                else{
                                    $need_update = 0;
                                }
                                $this->set_need_update($site_id, $need_update);
                                if(isset($information['current_version'])){
                                    $current_version = $information['current_version'];
                                    $this->set_current_version($site_id, $current_version);
                                }
                            }
                        }
                        else {
                            $ret['result'] = 'failed';
                            $ret['error'] = 'This is not a WPvivid Backup Pro account.';
                        }
                    }
                    else{
                        $ret['result'] = 'failed';
                        if(!isset($ret['error'])){
                            $ret['error'] = 'Failed to login to the site.';
                        }
                    }
                }
                else{
                    $ret['result'] = 'failed';
                    $ret['error'] = 'Failed to get previously entered login information, please login again.';
                }

                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_status()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_get_status_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['data'] = $information['wpvivid']['task'];
                    $ret['html'] = Mainwp_WPvivid_Extension_Subpage::output_backup_status($site_id, $information['wpvivid']['task'], $information['wpvivid']['backup_list'], $information['wpvivid']['schedule']);
                    $ret['schedule_html'] = Mainwp_WPvivid_Extension_Subpage::output_schedule_backup($information['wpvivid']['schedule']);
                    $ret['backup_list'] = Mainwp_WPvivid_Extension_Subpage::output_backup_list($information['wpvivid']['backup_list']);
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function get_backup_schedule(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_get_backup_schedule_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['schedule_html'] = Mainwp_WPvivid_Extension_Subpage::output_schedule_backup($information['wpvivid']['schedule']);
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function get_backup_list(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_get_backup_list_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['backup_list'] = Mainwp_WPvivid_Extension_Subpage::output_backup_list($information['wpvivid']['backup_list']);
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function get_default_remote(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_get_default_remote_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['default_remote_storage'] = $information['remote_storage_type'];
                    $ret['default_remote_pic'] = Mainwp_WPvivid_Extension_Subpage::output_default_remote($information['remote_storage_type']);
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function prepare_backup()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup']) && !empty($_POST['backup']) && is_array($_POST['backup'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                if(isset($_POST['backup']['mwp_backup_files']) && isset($_POST['backup']['mwp_local']) &&
                    isset($_POST['backup']['mwp_remote']) && isset($_POST['backup']['mwp_ismerge']) && isset($_POST['backup']['mwp_lock'])) {
                    $post_data['backup']['backup_files'] = sanitize_text_field($_POST['backup']['mwp_backup_files']);
                    $post_data['backup']['local'] = sanitize_text_field($_POST['backup']['mwp_local']);
                    $post_data['backup']['remote'] = sanitize_text_field($_POST['backup']['mwp_remote']);
                    $post_data['backup']['ismerge'] = sanitize_text_field($_POST['backup']['mwp_ismerge']);
                    $post_data['backup']['lock'] = sanitize_text_field($_POST['backup']['mwp_lock']);
                    $post_data['mwp_action'] = 'wpvivid_prepare_backup_mainwp';
                    $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                    if (isset($information['error'])) {
                        $ret['result'] = 'failed';
                        $ret['error'] = $information['error'];
                    } else {
                        $ret['result'] = 'success';
                        $ret['data'] = $information['task_id'];
                    }
                    echo json_encode($ret);
                }
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function backup_now()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['task_id']) && !empty($_POST['task_id']) && is_string($_POST['task_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['task_id'] = sanitize_key($_POST['task_id']);
                $post_data['mwp_action'] = 'wpvivid_backup_now_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['data'] = $information;
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function view_backup_task_log()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['id']) && !empty($_POST['id']) && is_string($_POST['id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['id'] = sanitize_key($_POST['id']);
                $post_data['mwp_action'] = 'wpvivid_view_backup_task_log_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret = $information;
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function backup_cancel()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_backup_cancel_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret = $information;
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function read_last_backup_log()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['log_file_name']) && !empty($_POST['log_file_name']) && is_string($_POST['log_file_name'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['log_file_name'] = sanitize_text_field($_POST['log_file_name']);
                $post_data['mwp_action'] = 'wpvivid_read_last_backup_log_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret = $information;
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function set_security_lock(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id']) &&
                isset($_POST['lock']) && is_string($_POST['lock'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['backup_id'] = sanitize_key($_POST['backup_id']);
                $post_data['lock'] = sanitize_text_field($_POST['lock']);
                $post_data['mwp_action'] = 'wpvivid_set_security_lock_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['backup_list'] = Mainwp_WPvivid_Extension_Subpage::output_backup_list($information['wpvivid']['backup_list']);
                }

                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function view_log()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['id']) && !empty($_POST['id']) && is_string($_POST['id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['id'] = sanitize_key($_POST['id']);
                $post_data['mwp_action'] = 'wpvivid_view_log_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret = $information;
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function init_download_page()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['backup_id'] = sanitize_key($_POST['backup_id']);
                $post_data['mwp_action'] = 'wpvivid_init_download_page_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret = Mainwp_WPvivid_Extension_Subpage::output_init_download_page($post_data['backup_id'], $information);
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function prepare_download_backup()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id']) &&
                isset($_POST['file_name']) && !empty($_POST['file_name']) && is_string($_POST['file_name'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['backup_id'] = sanitize_key($_POST['backup_id']);
                $post_data['file_name'] = sanitize_text_field($_POST['file_name']);
                $post_data['mwp_action'] = 'wpvivid_prepare_download_backup_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function get_download_task()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['backup_id'] = sanitize_key($_POST['backup_id']);
                $post_data['mwp_action'] = 'wpvivid_get_download_task_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret = Mainwp_WPvivid_Extension_Subpage::output_init_download_page($post_data['backup_id'], $information);
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function download_backup()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_REQUEST['site_id']) && !empty($_REQUEST['site_id']) && is_string($_REQUEST['site_id']) &&
                isset($_REQUEST['backup_id']) && !empty($_REQUEST['backup_id']) && is_string($_REQUEST['backup_id']) &&
                isset($_REQUEST['file_name']) && !empty($_REQUEST['file_name']) && is_string($_REQUEST['file_name'])){
                $site_id = sanitize_text_field($_REQUEST['site_id']);
                $post_data['backup_id'] = sanitize_key($_REQUEST['backup_id']);
                $post_data['file_name'] = sanitize_text_field($_REQUEST['file_name']);
                $post_data['mwp_action'] = 'wpvivid_download_backup_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['download_url'] = $information['download_url'];
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function delete_backup()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id']) &&
                isset($_POST['force']) && is_string($_POST['force'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['backup_id'] = sanitize_key($_POST['backup_id']);
                $post_data['force'] = sanitize_text_field($_POST['force']);
                $post_data['mwp_action'] = 'wpvivid_delete_backup_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['backup_list'] = Mainwp_WPvivid_Extension_Subpage::output_backup_list($information['wpvivid']['backup_list']);
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function delete_backup_array(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_array($_POST['backup_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $backup_ids = $_POST['backup_id'];
                foreach ($backup_ids as $backup_id){
                    $post_data['backup_id'][] = sanitize_key($backup_id);
                }
                $post_data['mwp_action'] = 'wpvivid_delete_backup_array_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['backup_list'] = Mainwp_WPvivid_Extension_Subpage::output_backup_list($information['wpvivid']['backup_list']);
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function set_schedule()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['schedule']) && !empty($_POST['schedule']) && is_string($_POST['schedule'])) {
                $schedule = array();
                $site_id = sanitize_text_field($_POST['site_id']);
                $json = stripslashes(sanitize_text_field($_POST['schedule']));
                $schedule = json_decode($json, true);
                $options = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'schedule', array());
                if ($schedule['mwp_enable'] == 1) {
                    $options['enable'] = $schedule['mwp_enable'];

                    $options['type'] = $schedule['mwp_recurrence'];
                    if (!defined('WPVIVID_MAIN_SCHEDULE_EVENT'))
                        define('WPVIVID_MAIN_SCHEDULE_EVENT', 'wpvivid_main_schedule_event');
                    $options['event'] = WPVIVID_MAIN_SCHEDULE_EVENT;
                    $options['start_time'] = 0;

                    $options['backup']['backup_files'] = $schedule['mwp_backup_type'];
                    if ($schedule['mwp_save_local_remote'] == 'remote') {
                        $options['backup']['local'] = 0;
                        $options['backup']['remote'] = 1;
                    } else {
                        $options['backup']['local'] = 1;
                        $options['backup']['remote'] = 0;
                    }
                    $options['backup']['ismerge'] = 1;
                    $options['backup']['lock'] = $schedule['mwp_lock'];
                } else {
                    $options['enable'] = $schedule['mwp_enable'];
                }

                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'schedule', $options);

                $new_schedule = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'schedule', array());

                if ($new_schedule['enable'] == 1) {
                    $schedule_data['enable'] = $new_schedule['enable'];
                    $schedule_data['recurrence'] = $new_schedule['type'];
                    $schedule_data['event'] = $new_schedule['event'];
                    $schedule_data['backup_type'] = $new_schedule['backup']['backup_files'];
                    if ($new_schedule['backup']['remote'] == 1) {
                        $schedule_data['save_local_remote'] = 'remote';
                    } else {
                        $schedule_data['save_local_remote'] = 'local';
                    }
                    $schedule_data['lock'] = 0;
                } else {
                    $schedule_data['enable'] = $new_schedule['enable'];
                }
                $post_data['mwp_action'] = 'wpvivid_set_schedule_mainwp';
                $post_data['schedule'] = json_encode($schedule_data);
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);

                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }

                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function set_global_schedule()
    {
        $this->mwp_ajax_check_security();
        try {
            $schedule = array();
            if (isset($_POST['schedule']) && !empty($_POST['schedule']) && is_string($_POST['schedule'])) {
                $json = stripslashes(sanitize_text_field($_POST['schedule']));
                $schedule = json_decode($json, true);
                $options = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule', array());
                if(empty($options)){
                    $options = array();
                }
                if ($schedule['mwp_enable'] == 1) {
                    $options['enable'] = $schedule['mwp_enable'];

                    $options['type'] = $schedule['mwp_recurrence'];
                    if (!defined('WPVIVID_MAIN_SCHEDULE_EVENT'))
                        define('WPVIVID_MAIN_SCHEDULE_EVENT', 'wpvivid_main_schedule_event');
                    $options['event'] = WPVIVID_MAIN_SCHEDULE_EVENT;
                    $options['start_time'] = 0;

                    $options['backup']['backup_files'] = $schedule['mwp_backup_type'];
                    if ($schedule['mwp_save_local_remote'] == 'remote') {
                        $options['backup']['local'] = 0;
                        $options['backup']['remote'] = 1;
                    } else {
                        $options['backup']['local'] = 1;
                        $options['backup']['remote'] = 0;
                    }
                    $options['backup']['ismerge'] = 1;
                    $options['backup']['lock'] = $schedule['mwp_lock'];
                } else {
                    $options['enable'] = $schedule['mwp_enable'];
                }

                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('schedule', $options);

                $ret['result'] = 'success';
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function set_general_setting()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['setting']) && !empty($_POST['setting']) && is_string($_POST['setting'])) {
                $setting = array();
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_set_general_setting_mainwp';
                $json = stripslashes(sanitize_text_field($_POST['setting']));
                $setting = json_decode($json, true);

                $options=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option(sanitize_text_field($_GET['id']), 'settings', array());

                $mwp_use_temp_file = isset($setting['mwp_use_temp_file']) ? $setting['mwp_use_temp_file'] : 1;
                $mwp_use_temp_size = isset($setting['mwp_use_temp_size']) ? $setting['mwp_use_temp_size'] : 16;
                $mwp_compress_type = isset($setting['mwp_compress_type']) ? $setting['mwp_compress_type'] : 'zip';

                $setting['mwp_use_temp_file'] = intval($mwp_use_temp_file);
                $setting['mwp_use_temp_size'] = intval($mwp_use_temp_size);
                $setting['mwp_exclude_file_size'] = intval($setting['mwp_exclude_file_size']);
                $setting['mwp_max_execution_time'] = intval($setting['mwp_max_execution_time']);
                $setting['mwp_max_backup_count'] = intval($setting['mwp_max_backup_count']);
                $setting['mwp_max_resume_count'] = intval($setting['mwp_max_resume_count']);

                $setting_data['wpvivid_compress_setting']['compress_type'] = $mwp_compress_type;
                $setting_data['wpvivid_compress_setting']['max_file_size'] = $setting['mwp_max_file_size'] . 'M';
                $setting_data['wpvivid_compress_setting']['no_compress'] = $setting['mwp_no_compress'];
                $setting_data['wpvivid_compress_setting']['use_temp_file'] = $setting['mwp_use_temp_file'];
                $setting_data['wpvivid_compress_setting']['use_temp_size'] = $setting['mwp_use_temp_size'];
                $setting_data['wpvivid_compress_setting']['exclude_file_size'] = $setting['mwp_exclude_file_size'];
                $setting_data['wpvivid_compress_setting']['subpackage_plugin_upload'] = $setting['mwp_subpackage_plugin_upload'];

                $setting_data['wpvivid_local_setting']['path'] = $setting['mwp_path'];
                $setting_data['wpvivid_local_setting']['save_local'] = isset($options['wpvivid_local_setting']['save_local']) ? $options['wpvivid_local_setting']['save_local'] : 1;

                $setting_data['wpvivid_common_setting']['max_execution_time'] = $setting['mwp_max_execution_time'];
                $setting_data['wpvivid_common_setting']['log_save_location'] = $setting['mwp_path'] . '/wpvivid_log';
                $setting_data['wpvivid_common_setting']['max_backup_count'] = $setting['mwp_max_backup_count'];
                $setting_data['wpvivid_common_setting']['show_admin_bar'] = isset($options['wpvivid_common_setting']['show_admin_bar']) ? $options['wpvivid_common_setting']['show_admin_bar'] : 1;
                $setting_data['wpvivid_common_setting']['estimate_backup'] = $setting['mwp_estimate_backup'];
                $setting_data['wpvivid_common_setting']['ismerge'] = $setting['mwp_ismerge'];
                $setting_data['wpvivid_common_setting']['domain_include'] = $setting['mwp_domain_include'];
                $setting_data['wpvivid_common_setting']['memory_limit'] = $setting['mwp_memory_limit'] . 'M';
                $setting_data['wpvivid_common_setting']['max_resume_count'] = $setting['mwp_max_resume_count'];
                $setting_data['wpvivid_common_setting']['db_connect_method'] = $setting['mwp_db_connect_method'];

                foreach ($setting_data as $option_name => $option) {
                    $options[$option_name] = $setting_data[$option_name];
                }

                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'settings', $options);

                $post_data['setting'] = json_encode($options);

                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);

                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function set_global_general_setting()
    {
        $this->mwp_ajax_check_security();
        try {
            $setting = array();
            $schedule = array();
            if (isset($_POST['setting']) && !empty($_POST['setting']) && is_string($_POST['setting'])) {
                $json = stripslashes(sanitize_text_field($_POST['setting']));
                $setting = json_decode($json, true);
                $options = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('settings', array());

                $mwp_use_temp_file = isset($setting['mwp_use_temp_file']) ? $setting['mwp_use_temp_file'] : 1;
                $mwp_use_temp_size = isset($setting['mwp_use_temp_size']) ? $setting['mwp_use_temp_size'] : 16;
                $mwp_compress_type = isset($setting['mwp_compress_type']) ? $setting['mwp_compress_type'] : 'zip';

                $setting['mwp_use_temp_file'] = intval($mwp_use_temp_file);
                $setting['mwp_use_temp_size'] = intval($mwp_use_temp_size);
                $setting['mwp_exclude_file_size'] = intval($setting['mwp_exclude_file_size']);
                $setting['mwp_max_execution_time'] = intval($setting['mwp_max_execution_time']);
                $setting['mwp_max_backup_count'] = intval($setting['mwp_max_backup_count']);
                $setting['mwp_max_resume_count'] = intval($setting['mwp_max_resume_count']);

                $setting_data['wpvivid_compress_setting']['compress_type'] = $mwp_compress_type;
                $setting_data['wpvivid_compress_setting']['max_file_size'] = $setting['mwp_max_file_size'] . 'M';
                $setting_data['wpvivid_compress_setting']['no_compress'] = $setting['mwp_no_compress'];
                $setting_data['wpvivid_compress_setting']['use_temp_file'] = $setting['mwp_use_temp_file'];
                $setting_data['wpvivid_compress_setting']['use_temp_size'] = $setting['mwp_use_temp_size'];
                $setting_data['wpvivid_compress_setting']['exclude_file_size'] = $setting['mwp_exclude_file_size'];
                $setting_data['wpvivid_compress_setting']['subpackage_plugin_upload'] = $setting['mwp_subpackage_plugin_upload'];

                $setting_data['wpvivid_local_setting']['path'] = $setting['mwp_path'];
                $setting_data['wpvivid_local_setting']['save_local'] = isset($options['wpvivid_local_setting']['save_local']) ? $options['wpvivid_local_setting']['save_local'] : 1;

                $setting_data['wpvivid_common_setting']['max_execution_time'] = $setting['mwp_max_execution_time'];
                $setting_data['wpvivid_common_setting']['log_save_location'] = $setting['mwp_path'] . '/wpvivid_log';
                $setting_data['wpvivid_common_setting']['max_backup_count'] = $setting['mwp_max_backup_count'];
                $setting_data['wpvivid_common_setting']['show_admin_bar'] = isset($options['wpvivid_common_setting']['show_admin_bar']) ? $options['wpvivid_common_setting']['show_admin_bar'] : 1;
                $setting_data['wpvivid_common_setting']['estimate_backup'] = $setting['mwp_estimate_backup'];
                $setting_data['wpvivid_common_setting']['ismerge'] = $setting['mwp_ismerge'];
                $setting_data['wpvivid_common_setting']['domain_include'] = $setting['mwp_domain_include'];
                $setting_data['wpvivid_common_setting']['memory_limit'] = $setting['mwp_memory_limit'] . 'M';
                $setting_data['wpvivid_common_setting']['max_resume_count'] = $setting['mwp_max_resume_count'];
                $setting_data['wpvivid_common_setting']['db_connect_method'] = $setting['mwp_db_connect_method'];

                if(empty($options)){
                    $options = array();
                }
                foreach ($setting_data as $option_name => $option) {
                    $options[$option_name] = $setting_data[$option_name];
                }

                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('settings', $options);

                $ret['result'] = 'success';

                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function add_remote_storage_list($html)
    {
        $html = '';
        $options=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('remote', array());
        $remoteslist=$options['upload'];
        $history=$options['history'];
        $default_remote_storage='';
        if(isset($history['remote_selected'])) {
            foreach ($history['remote_selected'] as $value) {
                $default_remote_storage = $value;
            }
        }
        $i=1;
        foreach ($remoteslist as $key=>$value)
        {
            if($key === 'remote_selected')
            {
                continue;
            }
            if ($key === $default_remote_storage)
            {
                $check_status = 'checked';
            }
            else
            {
                $check_status='';
            }
            $storage_type = $value['type'];
            $storage_type=apply_filters('wpvivid_storage_provider_tran', $storage_type);
            $html .= '<tr>
                <td>'.__($i++).'</td>
                <td><input type="checkbox" name="remote_storage" value="'.esc_attr($key).'" '.esc_attr($check_status).' /></td>
                <td>'.__($storage_type).'</td>
                <td class="row-title"><label for="tablecell">'.__($value['name']).'</label></td>
                <td>
                    <div><img src="'.esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL.'/admin/images/Delete.png').'" onclick="mwp_wpvivid_delete_remote_storage(\''.__($key).'\');" style="vertical-align:middle; cursor:pointer;" title="Remove the remote storage"/></div>
                </td>
                </tr>';
        }
        return $html;
    }

    public function add_remote()
    {
        $this->mwp_ajax_check_security();
        try {
            if (empty($_POST) || !isset($_POST['remote']) || !is_string($_POST['remote']) || !isset($_POST['type']) || !is_string($_POST['type'])) {
                die();
            }
            $json = sanitize_text_field($_POST['remote']);
            $json = stripslashes($json);
            $remote_options = json_decode($json, true);
            if (is_null($remote_options)) {
                die();
            }

            $remote_options['type'] = sanitize_text_field($_POST['type']);
            try {
                $ret = $this->remote->add_remote($remote_options);
            } catch (Exception $error) {
                $ret['result'] = 'failed';
                $message = 'An exception has occurred. class: ' . get_class($error) . ';msg: ' . $error->getMessage() . ';code: ' . $error->getCode() . ';line: ' . $error->getLine() . ';in_file: ' . $error->getFile() . ';';
                $ret['error'] = $message;
            }


            if ($ret['result'] == 'success') {
                $html = '';
                $html = apply_filters('mwp_wpvivid_add_remote_storage_list', $html);
                $ret['html'] = $html;
            }
            echo json_encode($ret);
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function delete_remote()
    {
        $this->mwp_ajax_check_security();
        try {
            if (empty($_POST) || !isset($_POST['remote_id']) || !is_string($_POST['remote_id'])) {
                die();
            }
            $id = sanitize_key($_POST['remote_id']);

            Mainwp_WPvivid_Extension_Option::get_instance()->delete_global_remote($id);
            $ret['result'] = 'success';
            $html = '';
            $html = apply_filters('mwp_wpvivid_add_remote_storage_list', $html);
            $ret['html'] = $html;
            echo json_encode($ret);
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function set_default_remote_storage()
    {
        $this->mwp_ajax_check_security();
        try {
            if (!isset($_POST['remote_storage']) || empty($_POST['remote_storage']) || !is_array($_POST['remote_storage'])) {
                $ret['result'] = 'failed';
                $ret['error'] = __('Choose one storage from the list to be the default storage.', 'wpvivid');
                echo json_encode($ret);
                die();
            }
            $remote_storage_array = $_POST['remote_storage'];
            $remote_storages = array();
            foreach ($remote_storage_array as $remote_storage_id){
                $remote_storages[] = sanitize_key($remote_storage_id);
            }
            Mainwp_WPvivid_Extension_Option::get_instance()->update_global_remote_default($remote_storages[0]);
            $ret['result'] = 'success';
            $html = '';
            $html = apply_filters('mwp_wpvivid_add_remote_storage_list', $html);
            $ret['html'] = $html;
            echo json_encode($ret);
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function mwp_wpvivid_check_version_event(){
        $websites=$this->get_websites_ex();
        foreach ( $websites as $website ){
            $site_id = $website['id'];
            if($website['slug'] === 'wpvivid-backup-pro/wpvivid-backup-pro.php'){
                $post_data['mwp_action'] = 'wpvivid_get_wpvivid_info_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    if(isset($information['need_update'])){
                        if($information['need_update']){
                            $need_update = 1;
                        }
                        else{
                            $need_update = 0;
                        }
                    }
                    else{
                        $need_update = 0;
                    }
                    $login_options = $this->get_global_login_addon();
                    if(isset($login_options['wpvivid_pro_login_cache'])){
                        if (isset($information['current_version'])) {
                            if(version_compare($login_options['wpvivid_pro_login_cache']['pro']['version'], $information['current_version'],'>')){
                                $this->set_need_update($site_id, 1);
                                $this->set_current_version($site_id, $information['current_version']);
                                $this->set_latest_version($site_id, $login_options['wpvivid_pro_login_cache']['pro']['version']);
                            }
                            else{
                                $this->set_need_update($site_id, 0);
                                $this->set_current_version($site_id, $information['current_version']);
                            }
                        }
                        else{
                            $this->set_need_update($site_id, 1);
                            $this->set_latest_version($site_id, $login_options['wpvivid_pro_login_cache']['pro']['version']);
                        }
                    }
                    else {
                        $this->set_need_update($site_id, $need_update);
                        if (isset($information['current_version'])) {
                            $current_version = $information['current_version'];
                            $this->set_current_version($site_id, $current_version);
                        }
                    }
                    if(isset($information['last_backup_report'])){
                        $last_backup_report = $information['last_backup_report'];
                        $this->set_backup_report($site_id, $last_backup_report);
                    }
                }
            }
        }
    }

    public function mwp_wpvivid_refresh_latest_pro_version_event(){
        $login_options = $this->get_global_login_addon();
        if($login_options !== false && isset($login_options['wpvivid_pro_account'])) {
            if(isset($login_options['wpvivid_pro_account']['license'])){
                $email = false;
                $user_info = $login_options['wpvivid_pro_account']['license'];
                $use_token = true;
            }
            else{
                $email = $login_options['wpvivid_pro_account']['email'];
                $user_info = $login_options['wpvivid_pro_account']['password'];
                $use_token = false;
            }

            $server=new Mainwp_WPvivid_Connect_server();
            $ret=$server->get_mainwp_status($email,$user_info,true,$use_token, true);
            if($ret['result']=='success') {
                if($ret['status']['pro_user']) {
                    $ret['pro_user']=1;
                }
                else {
                    $ret['pro_user']=0;
                }
                $login_options = $this->get_global_login_addon();
                $need_update = false;
                if($login_options === false || !isset($login_options['wpvivid_pro_account'])){
                    $login_options = array();
                    $need_update = true;
                }
                else{
                    if(isset($login_options['wpvivid_pro_login_cache'])){
                        if(version_compare($ret['status']['pro']['version'], $login_options['wpvivid_pro_login_cache']['pro']['version'],'>')){
                            $need_update = true;
                        }
                        else{
                            $need_update = false;
                        }
                    }
                    else{
                        $need_update = true;
                    }
                }
                if($need_update) {
                    $login_options['wpvivid_pro_login_cache'] = $ret['status'];
                    if(isset($login_options['wpvivid_pro_account']['license'])) {
                        $pro_info['license'] = $user_info;
                    }
                    else{
                        $pro_info['email'] = $email;
                        $pro_info['password'] = $user_info;
                    }
                    $login_options['wpvivid_pro_account'] = $pro_info;
                    $this->set_global_login_addon($login_options);
                    $this->check_child_site_need_update($ret['status']['pro']['version']);
                }
            }
        }
    }

    public function check_child_site_need_update($new_version)
    {
        $dbwebsites = $this->mwp_get_child_websites();
        foreach ($dbwebsites as $website)
        {
            if ($website)
            {
                $old_version = $this->get_latest_version($website->id);
                if(version_compare($new_version, $old_version,'>')){
                    $this->set_need_update($website->id, 1);
                    $this->set_latest_version($website->id, $new_version);
                }
            }
        }
    }

    public function get_database_tables(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_get_database_tables_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['database_tables'] = Mainwp_WPvivid_Extension_Subpage::output_database_table($information['base_tables'], $information['other_tables']);
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function get_themes_plugins(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_get_themes_plugins_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['themes_plugins_table'] = Mainwp_WPvivid_Extension_Subpage::output_themes_plugins_table($information['themes'], $information['plugins']);
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function get_uploads_tree_data(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['tree_node']) && !empty($_POST['tree_node']) && is_string($_POST['tree_node'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $json = stripslashes(sanitize_text_field($_POST['tree_node']));
                $post_data['tree_node'] = json_decode($json, true);
                $post_data['mwp_action'] = 'wpvivid_get_uploads_tree_data_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['uploads_tree_data'] = $information['nodes'];
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_content_tree_data(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['tree_node']) && !empty($_POST['tree_node']) && is_string($_POST['tree_node'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $json = stripslashes(sanitize_text_field($_POST['tree_node']));
                $post_data['tree_node'] = json_decode($json, true);
                $post_data['mwp_action'] = 'wpvivid_get_content_tree_data_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['content_tree_data'] = $information['nodes'];
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_additional_folder_tree_data(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['tree_node']) && !empty($_POST['tree_node']) && is_string($_POST['tree_node'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $json = stripslashes(sanitize_text_field($_POST['tree_node']));
                $post_data['tree_node'] = json_decode($json, true);
                $post_data['mwp_action'] = 'wpvivid_get_additional_folder_tree_data_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['additional_folder_tree_data'] = $information['nodes'];
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function connect_additional_database_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['database_info']) && !empty($_POST['database_info']) && is_string($_POST['database_info'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $data = sanitize_text_field($_POST['database_info']);
                $data = stripslashes($data);
                $json = json_decode($data, true);
                $post_data['mwp_action'] = 'wpvivid_connect_additional_database_addon_mainwp';
                $post_data['db_user'] = sanitize_text_field($json['db_user']);
                $post_data['db_pass'] = sanitize_text_field($json['db_pass']);
                $post_data['db_host'] = sanitize_text_field($json['db_host']);
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['html'] = Mainwp_WPvivid_Extension_Subpage::output_additional_database_table($information['database_array']);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function add_additional_database_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['database_info']) && !empty($_POST['database_info']) && is_string($_POST['database_info'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $data = sanitize_text_field($_POST['database_info']);
                $data = stripslashes($data);
                $json = json_decode($data, true);
                $post_data['mwp_action'] = 'wpvivid_add_additional_database_addon_mainwp';
                $post_data['db_user'] = $json['db_user'];
                $post_data['db_pass'] = $json['db_pass'];
                $post_data['db_host'] = $json['db_host'];
                $post_data['additional_database_list'] = $json['additional_database_list'];
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['html'] = Mainwp_WPvivid_Extension_Subpage::output_additional_database_list($information['data']);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function remove_additional_database_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['database_name']) && !empty($_POST['database_name']) && is_string($_POST['database_name'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $database_name = sanitize_text_field($_POST['database_name']);
                $post_data['mwp_action'] = 'wpvivid_remove_additional_database_addon_mainwp';
                $post_data['database_name'] = $database_name;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['html'] = Mainwp_WPvivid_Extension_Subpage::output_additional_database_list($information['data']);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function mwp_wpvivid_update_backup_exclude_extension_rule($site_id, $type, $value){
        $history = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'backup_custom_setting', array());
        if(!$history){
            $history = array();
        }
        if($type === 'uploads'){
            $history['uploads_option']['uploads_extension_list'] = array();
            $str_tmp = explode(',', $value);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $history['uploads_option']['uploads_extension_list'][] = $str_tmp[$index];
                }
            }
        }
        if($type === 'content'){
            $history['content_option']['content_extension_list'] = array();
            $str_tmp = explode(',', $value);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $history['content_option']['content_extension_list'][] = $str_tmp[$index];
                }
            }
        }
        if($type === 'additional_folder'){
            $history['other_option']['other_extension_list'] = array();
            $str_tmp = explode(',', $value);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $history['other_option']['other_extension_list'][] = $str_tmp[$index];
                }
            }
        }
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'backup_custom_setting', $history);
    }

    public function update_backup_exclude_extension_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['type']) && !empty($_POST['type']) && is_string($_POST['type']) &&
                isset($_POST['exclude_content']) && !empty($_POST['exclude_content']) && is_string($_POST['exclude_content'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $type = sanitize_text_field($_POST['type']);
                $exclude_content = sanitize_text_field($_POST['exclude_content']);
                $this->mwp_wpvivid_update_backup_exclude_extension_rule($site_id, $type, $exclude_content);
                $post_data['mwp_action'] = 'wpvivid_update_backup_exclude_extension_addon_mainwp';
                $post_data['type'] = $type;
                $post_data['exclude_content'] = $exclude_content;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function mwp_wpvivid_update_global_backup_exclude_extension_rule($type, $value){
        $history = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('backup_custom_setting', array());
        if(!$history){
            $history = array();
        }
        if($type === 'uploads'){
            $history['uploads_option']['uploads_extension_list'] = array();
            $str_tmp = explode(',', $value);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $history['uploads_option']['uploads_extension_list'][] = $str_tmp[$index];
                }
            }
        }
        if($type === 'content'){
            $history['content_option']['content_extension_list'] = array();
            $str_tmp = explode(',', $value);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $history['content_option']['content_extension_list'][] = $str_tmp[$index];
                }
            }
        }
        if($type === 'additional_folder'){
            $history['other_option']['other_extension_list'] = array();
            $str_tmp = explode(',', $value);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $history['other_option']['other_extension_list'][] = $str_tmp[$index];
                }
            }
        }
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('backup_custom_setting', $history);
    }

    public function update_global_schedule_backup_exclude_extension_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['type']) && !empty($_POST['type']) && is_string($_POST['type']) &&
                isset($_POST['exclude_content']) && !empty($_POST['exclude_content']) && is_string($_POST['exclude_content'])){
                $type = sanitize_text_field($_POST['type']);
                $exclude_content = sanitize_text_field($_POST['exclude_content']);
                $this->mwp_wpvivid_update_global_backup_exclude_extension_rule($type, $exclude_content);
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_default_remote_addon(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_get_default_remote_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['default_remote_storage'] = $information['remote_storage_type'];
                    $ret['default_remote_pic'] = Mainwp_WPvivid_Extension_Subpage::output_default_remote($information['remote_storage_type']);
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function mwp_wpvivid_update_backup_custom_setting($site_id, $options){
        $history = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'backup_custom_setting', array());
        $custom_option['database_option']['database_check'] = $options['database_check'];
        $custom_option['database_option']['exclude_table_list'] = isset($options['database_list']) ? $options['database_list'] : array();

        $custom_option['themes_option']['themes_check'] = $options['themes_check'];
        $custom_option['themes_option']['exclude_themes_list'] = isset($options['themes_list']) ? $options['themes_list'] : array();

        $custom_option['plugins_option']['plugins_check'] = $options['plugins_check'];
        $custom_option['plugins_option']['exclude_plugins_list'] = isset($options['plugins_list']) ? $options['plugins_list'] : array();

        $custom_option['uploads_option']['uploads_check'] = $options['uploads_check'];
        $custom_option['uploads_option']['exclude_uploads_list'] = isset($options['uploads_list']) ? $options['uploads_list'] : array();
        $custom_option['uploads_option']['uploads_extension_list'] = array();
        if(isset($options['upload_extension'])){
            $str_tmp = explode(',', $options['upload_extension']);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $custom_option['uploads_option']['uploads_extension_list'][] = $str_tmp[$index];
                }
            }
        }

        $custom_option['content_option']['content_check'] = $options['content_check'];
        $custom_option['content_option']['exclude_content_list'] = isset($options['content_list']) ? $options['content_list'] : array();
        $custom_option['content_option']['content_extension_list'] = array();
        if(isset($options['content_extension'])){
            $str_tmp = explode(',', $options['content_extension']);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $custom_option['content_option']['content_extension_list'][] = $str_tmp[$index];
                }
            }
        }

        $custom_option['core_option']['core_check'] = $options['core_check'];

        $custom_option['other_option']['other_check'] = $options['other_check'];
        $custom_option['other_option']['include_other_list'] = isset($options['other_list']) ? $options['other_list'] : array();
        $custom_option['other_option']['other_extension_list'] = array();
        if(isset($options['other_extension'])){
            $str_tmp = explode(',', $options['other_extension']);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $custom_option['other_option']['other_extension_list'][] = $str_tmp[$index];
                }
            }
        }

        if(isset($history['additional_database_option']))
        {
            $custom_option['additional_database_option'] = $history['additional_database_option'];
        }
        $custom_option['additional_database_option']['additional_database_check'] = $options['additional_database_check'];
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'backup_custom_setting', $custom_option);
    }

    public function prepare_backup_addon(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup']) && !empty($_POST['backup']) && is_string($_POST['backup'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $data = sanitize_text_field($_POST['backup']);
                $data = stripslashes($data);
                $json = json_decode($data, true);

                if(isset($json['mwp_lock'])){
                    $post_data['backup']['lock'] = $json['mwp_lock'];
                }
                if(isset($json['mwp_backup_to'])){
                    $post_data['backup']['backup_to'] = $json['mwp_backup_to'];
                }
                if(isset($json['mwp_backup_files'])){
                    $post_data['backup']['backup_files'] = $json['mwp_backup_files'];
                }
                if(isset($json['mwp_backup_prefix'])){
                    $post_data['backup']['backup_prefix'] = $json['mwp_backup_prefix'];
                }
                if(isset($json['custom_dirs'])){
                    $this->mwp_wpvivid_update_backup_custom_setting($site_id, $json['custom_dirs']);
                    if(isset($json['custom_dirs']['uploads_list']) && !empty($json['custom_dirs']['uploads_list'])){
                        foreach ($json['custom_dirs']['uploads_list'] as $key => $value){
                            if($value['type'] === 'mwp-wpvivid-custom-li-folder-icon'){
                                $value['type'] = 'wpvivid-custom-li-folder-icon';
                            }
                            else if($value['type'] === 'mwp-wpvivid-custom-li-file-icon'){
                                $value['type'] = 'wpvivid-custom-li-file-icon';
                            }
                            $json['custom_dirs']['uploads_list'][$key] = $value;
                        }
                    }
                    if(isset($json['custom_dirs']['content_list']) && !empty($json['custom_dirs']['content_list'])){
                        foreach ($json['custom_dirs']['content_list'] as $key => $value){
                            if($value['type'] === 'mwp-wpvivid-custom-li-folder-icon'){
                                $value['type'] = 'wpvivid-custom-li-folder-icon';
                            }
                            else if($value['type'] === 'mwp-wpvivid-custom-li-file-icon'){
                                $value['type'] = 'wpvivid-custom-li-file-icon';
                            }
                            $json['custom_dirs']['content_list'][$key] = $value;
                        }
                    }
                    if(isset($json['custom_dirs']['other_list']) && !empty($json['custom_dirs']['other_list'])){
                        foreach ($json['custom_dirs']['other_list'] as $key => $value){
                            if($value['type'] === 'mwp-wpvivid-custom-li-folder-icon'){
                                $value['type'] = 'wpvivid-custom-li-folder-icon';
                            }
                            else if($value['type'] === 'mwp-wpvivid-custom-li-file-icon'){
                                $value['type'] = 'wpvivid-custom-li-file-icon';
                            }
                            $json['custom_dirs']['other_list'][$key] = $value;
                        }
                    }
                    $post_data['backup']['custom_dirs'] = $json['custom_dirs'];
                }
                $post_data['mwp_action']='wpvivid_prepare_backup_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['data'] = $information['task_id'];
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function backup_now_addon(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['task_id']) && !empty($_POST['task_id']) && is_string($_POST['task_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['task_id'] = sanitize_key($_POST['task_id']);
                $post_data['mwp_action'] = 'wpvivid_backup_now_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['data'] = $information;
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function list_task_addon(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_list_tasks_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret = Mainwp_WPvivid_Extension_Subpage::output_backup_status_addon($site_id, $information);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_ready_task_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_delete_ready_task_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function backup_cancel_addon(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_backup_cancel_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret = $information;
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function achieve_local_backup_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['folder']) && !empty($_POST['folder']) && is_string($_POST['folder']) &&
                isset($_POST['page'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $folder  = sanitize_text_field($_POST['folder']);
                $page    = sanitize_text_field($_POST['page']);
                $post_data['mwp_action'] = 'wpvivid_achieve_local_backup_addon_mainwp';
                $post_data['folder'] = $folder;
                $post_data['page'] = $page;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $table = new Mainwp_WPvivid_Backup_List();
                    $table->set_backup_list($information['list_data'], $page);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['html'] = ob_get_clean();
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function achieve_remote_backup_info_addon(){
        $this->mwp_ajax_check_security();
        try{
            if (isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_achieve_remote_backup_info_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                }
                else {
                    $table = new Mainwp_WPvivid_Backup_List();
                    $table->set_backup_list($information['select_list_data'], 0);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['select_list_html'] = ob_get_clean();
                    $ret['remote_part_html'] = Mainwp_WPvivid_Extension_Subpage::output_remote_backup_page_addon($information['remote_list'], $information['select_remote_id']);
                    $ret['remote_list'] = $information['remote_list'];
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function achieve_remote_backup_addon(){
        $this->mwp_ajax_check_security();
        try {
            if (isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['remote_id']) && !empty($_POST['remote_id']) && is_string($_POST['remote_id']) &&
                isset($_POST['folder']) && !empty($_POST['folder']) && is_string($_POST['folder']) &&
                isset($_POST['page'])) {
                $site_id   = sanitize_text_field($_POST['site_id']);
                $remote_id = sanitize_text_field($_POST['remote_id']);
                $folder    = sanitize_text_field($_POST['folder']);
                $page      = sanitize_text_field($_POST['page']);
                $post_data['mwp_action'] = 'wpvivid_achieve_remote_backup_addon_mainwp';
                $post_data['remote_id'] = $remote_id;
                $post_data['folder'] = $folder;
                $post_data['page'] = $page;
                if(isset($_POST['incremental_path'])&&!empty($_POST['incremental_path']))
                {
                    $post_data['incremental_path'] = sanitize_text_field($_POST['incremental_path']);
                }
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $table = new Mainwp_WPvivid_Backup_List();
                    $table->set_backup_list($information['list_data'], $page);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['html'] = ob_get_clean();

                    $table = new Mainwp_WPvivid_Incremental_List();
                    $table->set_incremental_list($information['incremental_list']);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['incremental_list'] = ob_get_clean();
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function set_security_lock_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id']) &&
                isset($_POST['lock'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['backup_id'] = sanitize_key($_POST['backup_id']);
                $post_data['lock'] = sanitize_text_field($_POST['lock']);
                $post_data['mwp_action'] = 'wpvivid_set_security_lock_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    if($information['lock_status'] === 'lock'){
                        $backup_lock = '/admin/images/locked.png';
                    }
                    else{
                        $backup_lock = '/admin/images/unlocked.png';
                    }
                    $ret['html'] = '<img src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . $backup_lock) . '"  style="vertical-align:middle; cursor:pointer;"/>';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function set_remote_security_lock_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id']) &&
                isset($_POST['lock'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['backup_id'] = sanitize_key($_POST['backup_id']);
                $post_data['lock'] = sanitize_text_field($_POST['lock']);
                $post_data['mwp_action'] = 'wpvivid_set_remote_security_lock_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    if($information['lock_status'] === 'lock'){
                        $backup_lock = '/admin/images/locked.png';
                    }
                    else{
                        $backup_lock = '/admin/images/unlocked.png';
                    }
                    $ret['html'] = '<img src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . $backup_lock) . '"  style="vertical-align:middle; cursor:pointer;"/>';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_local_backup_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id']) &&
                isset($_POST['folder']) && !empty($_POST['folder']) && is_string($_POST['folder']) &&
                isset($_POST['page'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $backup_id = sanitize_key($_POST['backup_id']);
                $folder  = sanitize_text_field($_POST['folder']);
                $page    = sanitize_text_field($_POST['page']);
                $post_data['mwp_action'] = 'wpvivid_delete_local_backup_addon_mainwp';
                $post_data['backup_id'] = $backup_id;
                $post_data['folder'] = $folder;
                $post_data['page'] = $page;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $table = new Mainwp_WPvivid_Backup_List();
                    $table->set_backup_list($information['list_data'], $page);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['html'] = ob_get_clean();
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_local_backup_array_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_array($_POST['backup_id']) &&
                isset($_POST['folder']) && !empty($_POST['folder']) && is_string($_POST['folder']) &&
                isset($_POST['page'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $backup_ids = $_POST['backup_id'];
                $folder  = sanitize_text_field($_POST['folder']);
                $page    = sanitize_text_field($_POST['page']);
                foreach ($backup_ids as $backup_id){
                    $post_data['backup_id'][] = sanitize_key($backup_id);
                }
                $post_data['folder'] = $folder;
                $post_data['page'] = $page;
                $post_data['mwp_action'] = 'wpvivid_delete_local_backup_array_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $table = new Mainwp_WPvivid_Backup_List();
                    $table->set_backup_list($information['list_data'], $page);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['html'] = ob_get_clean();
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_remote_backup_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id']) &&
                isset($_POST['folder']) && !empty($_POST['folder']) && is_string($_POST['folder']) &&
                isset($_POST['page'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $backup_id = sanitize_key($_POST['backup_id']);
                $folder  = sanitize_text_field($_POST['folder']);
                $page    = sanitize_text_field($_POST['page']);
                $post_data['mwp_action'] = 'wpvivid_delete_remote_backup_addon_mainwp';
                $post_data['backup_id'] = $backup_id;
                $post_data['folder'] = $folder;
                $post_data['page'] = $page;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $table = new Mainwp_WPvivid_Backup_List();
                    $table->set_backup_list($information['list_data'], $page);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['html'] = ob_get_clean();
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_remote_backup_array_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_array($_POST['backup_id']) &&
                isset($_POST['folder']) && !empty($_POST['folder']) && is_string($_POST['folder']) &&
                isset($_POST['page'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $backup_ids = $_POST['backup_id'];
                $folder  = sanitize_text_field($_POST['folder']);
                $page    = sanitize_text_field($_POST['page']);
                foreach ($backup_ids as $backup_id){
                    $post_data['backup_id'][] = sanitize_key($backup_id);
                }
                $post_data['folder'] = $folder;
                $post_data['page'] = $page;
                $post_data['mwp_action'] = 'wpvivid_delete_remote_backup_array_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $table = new Mainwp_WPvivid_Backup_List();
                    $table->set_backup_list($information['list_data'], $page);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['html'] = ob_get_clean();
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function view_log_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['log']) && !empty($_POST['log']) && is_string($_POST['log'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $log = sanitize_text_field($_POST['log']);
                $post_data['log'] = $log;
                $post_data['mwp_action'] = 'wpvivid_view_log_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['data'] = $information['data'];
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function init_download_page_addon(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $backup_id = sanitize_key($_POST['backup_id']);
                $post_data['mwp_action'] = 'wpvivid_init_download_page_addon_mainwp';
                $post_data['backup_id'] = $backup_id;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['html'] = Mainwp_WPvivid_Extension_Subpage::output_init_download_page_addon($information['files'], $backup_id);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function prepare_download_backup_addon(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id']) &&
                isset($_POST['file_name']) && !empty($_POST['file_name']) && is_string($_POST['file_name'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['backup_id'] = sanitize_key($_POST['backup_id']);
                $post_data['file_name'] = sanitize_text_field($_POST['file_name']);
                $post_data['mwp_action'] = 'wpvivid_prepare_download_backup_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function get_download_progress_addon(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $backup_id = sanitize_key($_POST['backup_id']);
                $post_data['mwp_action'] = 'wpvivid_get_download_progress_addon_mainwp';
                $post_data['backup_id'] = $backup_id;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['need_update'] = $information['need_update'];
                    $ret['files'] = Mainwp_WPvivid_Extension_Subpage::output_download_progress_addon($information['files']);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function rescan_local_folder_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_rescan_local_folder_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_backup_addon_list()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $backup_id = sanitize_key($_POST['backup_id']);
                $post_data['mwp_action'] = 'wpvivid_init_download_page_addon_mainwp';
                $post_data['backup_id'] = $backup_id;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    if(isset($_POST['page'])) {
                        $page = $_POST['page'];
                    }
                    else{
                        $page = 1;
                    }
                    $ret['html'] = Mainwp_WPvivid_Extension_Subpage::output_init_download_page_addon($information['files'], $backup_id, $page);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_schedules_addon()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_get_schedules_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $table=new Mainwp_WPvivid_Schedule_List();
                    $table->set_schedule_list($information['schedule_info']);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                    $ret['html'] = $html;
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error){
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function create_schedule_addon()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['schedule']) && !empty($_POST['schedule']) && is_string($_POST['schedule'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $json = stripslashes(sanitize_text_field($_POST['schedule']));
                $post_data['mwp_action'] = 'wpvivid_create_schedule_addon_mainwp';


                $json = json_decode($json, true);
                if(isset($json['custom_dirs'])){
                    $this->mwp_wpvivid_update_backup_custom_setting($site_id, $json['custom_dirs']);
                    if(isset($json['custom_dirs']['uploads_list']) && !empty($json['custom_dirs']['uploads_list'])){
                        foreach ($json['custom_dirs']['uploads_list'] as $key => $value){
                            if($value['type'] === 'mwp-wpvivid-custom-li-folder-icon'){
                                $value['type'] = 'wpvivid-custom-li-folder-icon';
                            }
                            else if($value['type'] === 'mwp-wpvivid-custom-li-file-icon'){
                                $value['type'] = 'wpvivid-custom-li-file-icon';
                            }
                            $json['custom_dirs']['uploads_list'][$key] = $value;
                        }
                    }
                    if(isset($json['custom_dirs']['content_list']) && !empty($json['custom_dirs']['content_list'])){
                        foreach ($json['custom_dirs']['content_list'] as $key => $value){
                            if($value['type'] === 'mwp-wpvivid-custom-li-folder-icon'){
                                $value['type'] = 'wpvivid-custom-li-folder-icon';
                            }
                            else if($value['type'] === 'mwp-wpvivid-custom-li-file-icon'){
                                $value['type'] = 'wpvivid-custom-li-file-icon';
                            }
                            $json['custom_dirs']['content_list'][$key] = $value;
                        }
                    }
                    if(isset($json['custom_dirs']['other_list']) && !empty($json['custom_dirs']['other_list'])){
                        foreach ($json['custom_dirs']['other_list'] as $key => $value){
                            if($value['type'] === 'mwp-wpvivid-custom-li-folder-icon'){
                                $value['type'] = 'wpvivid-custom-li-folder-icon';
                            }
                            else if($value['type'] === 'mwp-wpvivid-custom-li-file-icon'){
                                $value['type'] = 'wpvivid-custom-li-file-icon';
                            }
                            $json['custom_dirs']['other_list'][$key] = $value;
                        }
                    }
                }
                $json = json_encode($json);

                $post_data['schedule'] = $json;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);

                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                    $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', false, $information['error']);
                } else {
                    $ret['result'] = 'success';
                    $table=new Mainwp_WPvivid_Schedule_List();
                    $table->set_schedule_list($information['schedule_info']);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                    $ret['html'] = $html;
                    $success_msg = 'You have successfully added a schedule.';
                    $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', true, $success_msg);
                    if(isset($information['enable_incremental_schedules'])){
                        if(empty($information['enable_incremental_schedules'])) $information['enable_incremental_schedules'] = 0;
                        $this->set_incremental_enable($site_id, $information['enable_incremental_schedules']);
                    }
                    if(isset($information['incremental_schedules'])){
                        $this->set_incremental_schedules($site_id, $information['incremental_schedules']);
                    }
                    if(isset($information['incremental_backup_data'])){
                        $this->set_incremental_backup_data($site_id, $information['incremental_backup_data']);
                    }
                    if(isset($information['incremental_output_msg'])){
                        $this->set_incremental_output_msg($site_id, $information['incremental_output_msg']);
                    }
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function update_schedule_addon()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['schedule']) && !empty($_POST['schedule']) && is_string($_POST['schedule'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $json = stripslashes(sanitize_text_field($_POST['schedule']));
                $post_data['mwp_action'] = 'wpvivid_update_schedule_addon_mainwp';

                $json = json_decode($json, true);
                if(isset($json['custom_dirs'])){
                    $this->mwp_wpvivid_update_backup_custom_setting($site_id, $json['custom_dirs']);
                    if(isset($json['custom_dirs']['uploads_list']) && !empty($json['custom_dirs']['uploads_list'])){
                        foreach ($json['custom_dirs']['uploads_list'] as $key => $value){
                            if($value['type'] === 'mwp-wpvivid-custom-li-folder-icon'){
                                $value['type'] = 'wpvivid-custom-li-folder-icon';
                            }
                            else if($value['type'] === 'mwp-wpvivid-custom-li-file-icon'){
                                $value['type'] = 'wpvivid-custom-li-file-icon';
                            }
                            $json['custom_dirs']['uploads_list'][$key] = $value;
                        }
                    }
                    if(isset($json['custom_dirs']['content_list']) && !empty($json['custom_dirs']['content_list'])){
                        foreach ($json['custom_dirs']['content_list'] as $key => $value){
                            if($value['type'] === 'mwp-wpvivid-custom-li-folder-icon'){
                                $value['type'] = 'wpvivid-custom-li-folder-icon';
                            }
                            else if($value['type'] === 'mwp-wpvivid-custom-li-file-icon'){
                                $value['type'] = 'wpvivid-custom-li-file-icon';
                            }
                            $json['custom_dirs']['content_list'][$key] = $value;
                        }
                    }
                    if(isset($json['custom_dirs']['other_list']) && !empty($json['custom_dirs']['other_list'])){
                        foreach ($json['custom_dirs']['other_list'] as $key => $value){
                            if($value['type'] === 'mwp-wpvivid-custom-li-folder-icon'){
                                $value['type'] = 'wpvivid-custom-li-folder-icon';
                            }
                            else if($value['type'] === 'mwp-wpvivid-custom-li-file-icon'){
                                $value['type'] = 'wpvivid-custom-li-file-icon';
                            }
                            $json['custom_dirs']['other_list'][$key] = $value;
                        }
                    }
                }
                $json = json_encode($json);


                $post_data['schedule'] = $json;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);

                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                    $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', false, $information['error']);
                } else {
                    $ret['result'] = 'success';
                    $table=new Mainwp_WPvivid_Schedule_List();
                    $table->set_schedule_list($information['schedule_info']);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                    $ret['html'] = $html;
                    $success_msg = 'You have successfully updated the schedule.';
                    $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', true, $success_msg);
                }

                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function delete_schedule_addon()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['schedule_id']) && !empty($_POST['schedule_id']) && is_string($_POST['schedule_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_delete_schedule_addon_mainwp';
                $post_data['schedule_id'] = sanitize_text_field($_POST['schedule_id']);
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                    $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', false, $information['error']);
                } else {
                    $ret['result'] = 'success';
                    $table=new Mainwp_WPvivid_Schedule_List();
                    $table->set_schedule_list($information['schedule_info']);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                    $ret['html'] = $html;
                    $success_msg = 'The schedule has been deleted successfully.';
                    $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', true, $success_msg);
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error){
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function save_schedule_status_addon()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['schedule_data']) && !empty($_POST['schedule_data']) && is_string($_POST['schedule_data'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_save_schedule_status_addon_mainwp';
                $post_data['schedule_data'] = stripslashes(sanitize_text_field($_POST['schedule_data']));
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                    $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', false, $information['error']);
                } else {
                    $ret['result'] = 'success';
                    $table=new Mainwp_WPvivid_Schedule_List();
                    $table->set_schedule_list($information['schedule_info']);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                    $ret['html'] = $html;
                    $success_msg = 'You have successfully saved the changes.';
                    $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', true, $success_msg);
                    if(isset($information['enable_incremental_schedules'])){
                        if(empty($information['enable_incremental_schedules'])) $information['enable_incremental_schedules'] = 0;
                        $this->set_incremental_enable($site_id, $information['enable_incremental_schedules']);
                    }
                    if(isset($information['incremental_schedules'])){
                        $this->set_incremental_schedules($site_id, $information['incremental_schedules']);
                    }
                    if(isset($information['incremental_backup_data'])){
                        $this->set_incremental_backup_data($site_id, $information['incremental_backup_data']);
                    }
                    if(isset($information['incremental_output_msg'])){
                        $this->set_incremental_output_msg($site_id, $information['incremental_output_msg']);
                    }
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error){
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function mwp_wpvivid_update_global_backup_custom_setting($options){
        $history = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('backup_custom_setting', array());

        $custom_option['database_option']['database_check'] = $options['database_check'];

        $custom_option['themes_option']['themes_check'] = $options['themes_check'];

        $custom_option['plugins_option']['plugins_check'] = $options['plugins_check'];

        $custom_option['uploads_option']['uploads_check'] = $options['uploads_check'];
        $custom_option['uploads_option']['uploads_extension_list'] = array();
        if(isset($options['upload_extension'])){
            $str_tmp = explode(',', $options['upload_extension']);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $custom_option['uploads_option']['uploads_extension_list'][] = $str_tmp[$index];
                }
            }
        }

        $custom_option['content_option']['content_check'] = $options['content_check'];
        $custom_option['content_option']['content_extension_list'] = array();
        if(isset($options['content_extension'])){
            $str_tmp = explode(',', $options['content_extension']);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $custom_option['content_option']['content_extension_list'][] = $str_tmp[$index];
                }
            }
        }

        $custom_option['core_option']['core_check'] = $options['core_check'];

        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('backup_custom_setting', $custom_option);
    }

    public function global_create_schedule_addon()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['schedule']) && !empty($_POST['schedule']) && is_string($_POST['schedule'])) {
                $json = stripslashes(sanitize_text_field($_POST['schedule']));
                $schedule = json_decode($json, true);
                if(isset($_POST['schedule_mould_name']) && !empty($_POST['schedule_mould_name'])){
                    $schedule_mould_name = sanitize_text_field($_POST['schedule_mould_name']);
                    if (isset($schedule['custom_dirs'])) {
                        $this->mwp_wpvivid_update_global_backup_custom_setting($schedule['custom_dirs']);
                    }

                    if(isset($_POST['first_create'])){
                        if($_POST['first_create'] == '1'){
                            $need_check_exist = true;
                        }
                        else{
                            $need_check_exist = false;
                        }
                    }
                    else{
                        $need_check_exist = true;
                    }

                    $schedule_mould_name_array = array();
                    $schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_mould_addon', array());
                    if(empty($schedule_mould)){
                        $schedule_mould = array();
                    }
                    else{
                        foreach ($schedule_mould as $schedule_name => $value){
                            $schedule_mould_name_array[] = $schedule_name;
                        }
                    }
                    if(!in_array($schedule_mould_name, $schedule_mould_name_array) || !$need_check_exist){
                        if(!$need_check_exist){
                            $schedules = $schedule_mould[$schedule_mould_name];
                        }
                        else{
                            $schedules = array();
                        }
                        $schedule_data = array();
                        $schedule_data['id'] = uniqid('wpvivid_schedule_event');
                        $schedule_data['status'] = $schedule['status'];
                        $schedule_data['type'] = $schedule['recurrence'];
                        $schedule_data['week'] = isset($schedule['week']) ? $schedule['week'] : 'sun';
                        $schedule_data['day'] = isset($schedule['day']) ? $schedule['day'] : '01';
                        $schedule['current_day_hour'] = isset($schedule['current_day_hour']) ? $schedule['current_day_hour'] : '00';
                        $schedule['current_day_minute'] = isset($schedule['current_day_minute']) ? $schedule['current_day_minute'] : '00';
                        $schedule_data['current_day'] = $schedule['current_day_hour'] . ':' . $schedule['current_day_minute'];
                        $schedule_data['start_time_local_utc'] = isset($schedule['start_time_zone']) ? $schedule['start_time_zone'] : 'utc';
                        if (isset($schedule['mwp_schedule_add_backup_type']) && !empty($schedule['mwp_schedule_add_backup_type'])) {
                            if ($schedule['mwp_schedule_add_backup_type'] === 'custom') {
                                $schedule_data['backup']['backup_select']['db'] = intval($schedule['custom_dirs']['database_check']);
                                $schedule_data['backup']['backup_select']['themes'] = intval($schedule['custom_dirs']['themes_check']);
                                $schedule_data['backup']['backup_select']['plugin'] = intval($schedule['custom_dirs']['plugins_check']);
                                $schedule_data['backup']['backup_select']['uploads'] = intval($schedule['custom_dirs']['uploads_check']);
                                $schedule_data['backup']['backup_select']['content'] = intval($schedule['custom_dirs']['content_check']);
                                $schedule_data['backup']['backup_select']['core'] = intval($schedule['custom_dirs']['core_check']);
                                $schedule_data['backup']['upload_extension'] = '';
                                $schedule_data['backup']['content_extension'] = '';
                                if (isset($schedule['custom_dirs']['upload_extension'])) {
                                    $schedule_data['backup']['upload_extension'] = $schedule['custom_dirs']['upload_extension'];
                                }
                                if (isset($schedule['custom_dirs']['content_extension'])) {
                                    $schedule_data['backup']['content_extension'] = $schedule['custom_dirs']['content_extension'];
                                }
                            } else {
                                $schedule_data['backup']['backup_files'] = $schedule['mwp_schedule_add_backup_type'];
                            }
                        }
                        $schedule_data['backup']['local'] = 1;
                        $schedule_data['backup']['remote'] = 0;
                        if ($schedule['mwp_schedule_add_save_local_remote'] == 'remote') {
                            $schedule_data['backup']['local'] = 0;
                            $schedule_data['backup']['remote'] = 1;
                        }
                        $schedule_data['backup']['lock'] = 0;
                        $schedules[$schedule_data['id']] = $schedule_data;

                        $schedule_mould[$schedule_mould_name] = $schedules;
                        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('schedule_mould_addon', $schedule_mould);

                        $table = new Mainwp_WPvivid_Schedule_Global_List();
                        $table->set_schedule_list($schedules);
                        $table->prepare_items();
                        ob_start();
                        $table->display();
                        $html = ob_get_clean();

                        $success_msg = 'You have successfully added a schedule.';
                        $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', true, $success_msg);
                        $ret['html'] = $html;
                        $ret['result'] = 'success';
                    }
                    else {
                        $ret['result'] = 'failed';
                        $error_msg = 'The schedule mould name already existed.';
                        $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', false, $error_msg);
                    }
                }
                else{
                    $ret['result'] = 'failed';
                    $error_msg = 'A schedule mould name is required.';
                    $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', false, $error_msg);
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function edit_global_schedule_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['schedule_id']) && !empty($_POST['schedule_id']) && is_string($_POST['schedule_id']) &&
                isset($_POST['mould_name']) && !empty($_POST['mould_name']) && is_string($_POST['mould_name'])) {
                $schedule_id = sanitize_text_field($_POST['schedule_id']);
                $schedule_mould_name = sanitize_text_field($_POST['mould_name']);
                $schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_mould_addon', array());
                $schedules = $schedule_mould[$schedule_mould_name];

                $ret['result'] = 'success';
                $ret['schedule_info'] = $schedules[$schedule_id];
                if(!isset($schedules[$schedule_id]['start_time_local_utc'])){
                    $ret['schedule_info']['start_time_local_utc'] = 'utc';
                }

                if(isset($ret['schedule_info']['current_day']))
                {
                    $dt = DateTime::createFromFormat("H:i", $ret['schedule_info']['current_day']);
                    $offset=get_option('gmt_offset');
                    $hours=$dt->format('H');
                    $minutes=$dt->format('i');

                    $hour=(float)$hours+$offset;

                    $whole = floor($hour);
                    $fraction = $hour - $whole;
                    $minute=(float)(60*($fraction))+(int)$minutes;

                    $hour=(int)$hour;
                    $minute=(int)$minute;

                    if($minute>=60)
                    {
                        $hour=(int)$hour+1;
                        $minute=(int)$minute-60;
                    }

                    if($hour>=24)
                    {
                        $hour=$hour-24;
                    }
                    else if($hour<0)
                    {
                        $hour=24-abs ($hour);
                    }

                    if($hour<10)
                    {
                        $hour='0'.(int)$hour;
                    }
                    else
                    {
                        $hour=(string)$hour;
                    }

                    if($minute<10)
                    {
                        $minute='0'.(int)$minute;
                    }
                    else
                    {
                        $minute=(string)$minute;
                    }

                    $ret['schedule_info']['hours']=$hour;
                    $ret['schedule_info']['minute']=$minute;
                }
                else
                {
                    $ret['schedule_info']['hours']='00';
                    $ret['schedule_info']['minute']='00';
                }

                echo json_encode($ret);
                die();
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function global_update_schedule_addon()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['schedule']) && !empty($_POST['schedule']) && is_string($_POST['schedule']) &&
                isset($_POST['mould_name']) && !empty($_POST['mould_name']) && is_string($_POST['mould_name'])) {
                $json = stripslashes(sanitize_text_field($_POST['schedule']));
                $schedule_data = json_decode($json, true);

                $schedule_mould_name = sanitize_text_field($_POST['mould_name']);
                $schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_mould_addon', array());
                $schedules = $schedule_mould[$schedule_mould_name];

                $schedule_tmp = array();
                $schedule_tmp['id'] = $schedule_data['schedule_id'];
                $schedule_tmp['status'] = $schedule_data['status'];
                $schedule_tmp['type'] = $schedule_data['recurrence'];
                $schedule_tmp['week'] = $schedule_data['week'];
                $schedule_tmp['day'] = $schedule_data['day'];
                $schedule_tmp['current_day'] = $schedule_data['current_day_hour'].':'.$schedule_data['current_day_minute'];
                $schedule_tmp['start_time_local_utc'] = isset($schedule_data['start_time_zone']) ? $schedule_data['start_time_zone'] : 'utc';
                if(isset($schedule_data['mwp_schedule_update_backup_type']) && !empty($schedule_data['mwp_schedule_update_backup_type'])){
                    if($schedule_data['mwp_schedule_update_backup_type'] === 'custom'){
                        $schedule_tmp['backup']['backup_select']['db'] = intval($schedule_data['custom_dirs']['database_check']);
                        $schedule_tmp['backup']['backup_select']['themes'] = intval($schedule_data['custom_dirs']['themes_check']);
                        $schedule_tmp['backup']['backup_select']['plugin'] = intval($schedule_data['custom_dirs']['plugins_check']);
                        $schedule_tmp['backup']['backup_select']['uploads'] = intval($schedule_data['custom_dirs']['uploads_check']);
                        $schedule_tmp['backup']['backup_select']['content'] = intval($schedule_data['custom_dirs']['content_check']);
                        $schedule_tmp['backup']['backup_select']['core'] = intval($schedule_data['custom_dirs']['core_check']);
                        $schedule_tmp['backup']['upload_extension'] = '';
                        $schedule_tmp['backup']['content_extension'] = '';
                        if(isset($schedule['custom_dirs']['upload_extension'])) {
                            $schedule_tmp['backup']['upload_extension'] = $schedule_data['custom_dirs']['upload_extension'];
                        }
                        if(isset($schedule['custom_dirs']['content_extension'])) {
                            $schedule_tmp['backup']['content_extension'] = $schedule_data['custom_dirs']['content_extension'];
                        }
                    }
                    else{
                        $schedule_tmp['backup']['backup_files'] = $schedule_data['mwp_schedule_update_backup_type'];
                    }
                }

                $schedule_tmp['backup']['local'] = $schedule_data['mwp_schedule_update_save_local_remote']==='local' ? 1 : 0;
                $schedule_tmp['backup']['remote'] = $schedule_data['mwp_schedule_update_save_local_remote']==='local' ? 0 : 1;
                $schedule_tmp['backup']['lock'] = intval($schedule_data['lock']);

                $schedules[$schedule_data['schedule_id']] = $schedule_tmp;

                $schedule_mould[$schedule_mould_name] = $schedules;
                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('schedule_mould_addon', $schedule_mould);

                $table=new Mainwp_WPvivid_Schedule_Global_List();
                $table->set_schedule_list($schedules);
                $table->prepare_items();
                ob_start();
                $table->display();
                $html = ob_get_clean();

                $success_msg = 'You have successfully updated the schedule. Please click on Save Changes and Sync button to synchronize the settings to child sites.';
                $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', true, $success_msg);
                $ret['html'] = $html;
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function global_delete_schedule_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['schedule_id']) && !empty($_POST['schedule_id']) && is_string($_POST['schedule_id']) &&
                isset($_POST['mould_name']) && !empty($_POST['mould_name']) && is_string($_POST['mould_name'])) {
                $schedule_id = sanitize_text_field($_POST['schedule_id']);

                $schedule_mould_name = sanitize_text_field($_POST['mould_name']);
                $schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_mould_addon', array());
                $schedules = $schedule_mould[$schedule_mould_name];

                if(isset($schedules[$schedule_id])) {
                    unset($schedules[$schedule_id]);
                }

                $schedule_mould[$schedule_mould_name] = $schedules;
                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('schedule_mould_addon', $schedule_mould);

                $table=new Mainwp_WPvivid_Schedule_Global_List();
                $table->set_schedule_list($schedules);
                $table->prepare_items();
                ob_start();
                $table->display();
                $html = ob_get_clean();

                $success_msg = 'The schedule has been deleted successfully. Please click on Save Changes and Sync button to synchronize the settings to child sites.';
                $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', true, $success_msg);
                $ret['html'] = $html;
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error){
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function global_save_schedule_status_addon()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['schedule_data']) && !empty($_POST['schedule_data']) && is_string($_POST['schedule_data'])) {
                $json = stripslashes(sanitize_text_field($_POST['schedule_data']));
                $schedule_data = json_decode($json, true);

                $schedules = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_addon', array());
                if(empty($schedules)){
                    $schedules = array();
                }

                foreach ($schedule_data as $schedule_id => $schedule_status){
                    $schedules[$schedule_id]['status'] = $schedule_status;
                }

                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('schedule_addon', $schedules);
                $schedules_list = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_addon', array());
                $table=new Mainwp_WPvivid_Schedule_Global_List();
                $table->set_schedule_list($schedules_list);
                $table->prepare_items();
                ob_start();
                $table->display();
                $html = ob_get_clean();

                $success_msg = 'You have successfully saved the changes.';
                $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', true, $success_msg);
                $ret['html'] = $html;
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error){
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function edit_global_schedule_mould_addon()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['mould_name']) && !empty($_POST['mould_name']) && is_string($_POST['mould_name'])){
                $mould_name = sanitize_text_field($_POST['mould_name']);
                $schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_mould_addon', array());
                $schedules = $schedule_mould[$mould_name];
                $table = new Mainwp_WPvivid_Schedule_Global_List();
                $table->set_schedule_list($schedules);
                $table->prepare_items();
                ob_start();
                $table->display();
                $html = ob_get_clean();
                $ret['html'] = $html;
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error){
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_global_schedule_mould_addon()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['mould_name']) && !empty($_POST['mould_name']) && is_string($_POST['mould_name'])){
                $mould_name = sanitize_text_field($_POST['mould_name']);
                $schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_mould_addon', array());
                if(isset($schedule_mould[$mould_name])){
                    unset($schedule_mould[$mould_name]);
                }
                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('schedule_mould_addon', $schedule_mould);

                $table = new Mainwp_WPvivid_Schedule_Mould_List();
                $table->set_schedule_mould_list($schedule_mould);
                $table->prepare_items();
                ob_start();
                $table->display();
                $html = ob_get_clean();
                $ret['html'] = $html;
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error){
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function upgrade_plugin_addon(){
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_upgrade_plugin_addon_mainwp_v2';
                $post_data['site_id'] = $site_id;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    if(isset($information['need_update'])){
                        if($information['need_update']){
                            $need_update = 1;
                        }
                        else{
                            $need_update = 0;
                        }
                    }
                    else{
                        $need_update = 0;
                    }
                    $this->set_need_update($site_id, $need_update);
                    if(isset($information['current_version'])){
                        $current_version = $information['current_version'];
                        $this->set_current_version($site_id, $current_version);
                    }
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_upgrade_progress_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_get_upgrade_progress_addon_mainwp_v2';
                $post_data['site_id'] = $site_id;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);

                $ret['result'] = $information['result'];
                if(isset($information['upgrade_task'])){
                    $ret['upgrade_task'] = $information['upgrade_task'];
                }
                if(isset($information['need_update'])){
                    if($information['need_update']){
                        $need_update = 1;
                    }
                    else{
                        $need_update = 0;
                    }
                    $this->set_need_update($site_id, $need_update);
                }
                if(isset($information['current_version'])){
                    $current_version = $information['current_version'];
                    $this->set_current_version($site_id, $current_version);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function retrieve_global_remote_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['remote_id']) && !empty($_POST['remote_id']) && is_string($_POST['remote_id'])){
                $remote_id = sanitize_text_field($_POST['remote_id']);
                $remote = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('remote_addon', array());
                $ret['result'] = 'success';
                if(isset($remote['upload'][$remote_id])) {
                    if(isset($remote['upload'][$remote_id]['is_encrypt']) && $remote['upload'][$remote_id]['is_encrypt'] == 1){
                        if($remote['upload'][$remote_id]['type'] === 'ftp' || $remote['upload'][$remote_id]['type'] === 'sftp'){
                            $remote['upload'][$remote_id]['password'] = base64_decode($remote['upload'][$remote_id]['password']);
                        }
                        else if($remote['upload'][$remote_id]['type'] === 'amazons3' || $remote['upload'][$remote_id]['type'] === 's3compat' || $remote['upload'][$remote_id]['type'] === 'wasabi'){
                            $remote['upload'][$remote_id]['secret'] = base64_decode($remote['upload'][$remote_id]['secret']);
                        }
                    }
                    $ret['data'] = $remote['upload'][$remote_id];
                    echo json_encode($ret);
                }
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function update_global_remote_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['remote']) && !empty($_POST['remote']) && is_string($_POST['remote']) &&
                isset($_POST['remote_id']) && !empty($_POST['remote_id']) && is_string($_POST['remote_id']) &&
                isset($_POST['type']) && !empty($_POST['type']) && is_string($_POST['type'])) {
                $json = sanitize_text_field($_POST['remote']);
                $json = stripslashes($json);
                $remote_options = json_decode($json, true);
                if (is_null($remote_options)) {
                    die();
                }
                $remote_id = sanitize_text_field($_POST['remote_id']);
                $remote_options['type'] = sanitize_text_field($_POST['type']);

                if($remote_options['type'] === 'ftp' || $remote_options['type'] === 'sftp'){
                    $remote_options['password'] = base64_encode($remote_options['password']);
                    $remote_options['is_encrypt'] = 1;
                }
                else if($remote_options['type'] === 'amazons3' || $remote_options['type'] === 's3compat' || $remote_options['type'] === 'wasabi'){
                    $remote_options['secret'] = base64_encode($remote_options['secret']);
                    $remote_options['is_encrypt'] = 1;
                }

                $remote_settings = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('remote_addon', array());
                if(empty($remote_settings)){
                    $remote_settings = array();
                }
                if(isset($remote_settings['upload'][$remote_id])){
                    $remote_settings['upload'][$remote_id] = $remote_options;
                }

                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('remote_addon', $remote_settings);
                //Mainwp_WPvivid_Extension_Option::get_instance()->update_global_remote_addon($remote_id, $remote_options, $default);

                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_global_remote_addon(){
        $this->mwp_ajax_check_security();
        try {
            if (empty($_POST) || !isset($_POST['remote_id']) || !is_string($_POST['remote_id'])) {
                die();
            }
            $id = sanitize_key($_POST['remote_id']);

            $remote_settings = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('remote_addon', array());
            if(empty($remote_settings)){
                $remote_settings = array();
            }
            if(isset($remote_settings['upload'][$id]))
            {
                unset($remote_settings['upload'][$id]);
            }

            Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('remote_addon', $remote_settings);

            //Mainwp_WPvivid_Extension_Option::get_instance()->delete_global_remote_addon($id);

            $remote_storages=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('remote_addon', array());
            $remote_list = '';
            if(isset($remote_storages['upload']) && !empty($remote_storages['upload'])){
                $remote_list = $remote_storages['upload'];
            }
            $table=new MainWP_WPvivid_Remote_Storage_Global_List();
            $table->set_storage_list($remote_list);
            $table->prepare_items();
            ob_start();
            $table->display();
            $html = ob_get_clean();
            $ret['result'] = 'success';
            $ret['html'] = $html;
            echo json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function sync_global_remote_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['default_setting']) && !empty($_POST['default_setting']) && is_string($_POST['default_setting']) &&
                isset($_POST['custom_path']) && !empty($_POST['custom_path']) && is_string($_POST['custom_path']) &&
                isset($_POST['remote_id']) && !empty($_POST['remote_id']) && is_string($_POST['remote_id'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $default_setting = sanitize_text_field($_POST['default_setting']);
                $custom_path = sanitize_text_field($_POST['custom_path']);
                $remote_id = sanitize_text_field($_POST['remote_id']);
                $post_data['mwp_action'] = 'wpvivid_sync_remote_storage_addon_mainwp';
                $remote = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('remote_addon', array());
                if(isset($remote['upload'][$remote_id])) {
                    $post_data['remote'] = json_encode($remote['upload'][$remote_id]);
                    $post_data['default_setting'] = $default_setting;
                    $post_data['custom_path'] = $custom_path;
                    $remote_option['custom_path'] = $custom_path;

                    $sync_remote_settings = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'sync_remote_setting', array());
                    if(empty($sync_remote_settings)){
                        $sync_remote_settings = array();
                    }
                    $sync_remote_settings[$remote_id] = $remote_option;
                    Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'sync_remote_setting', $sync_remote_settings);

                    $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                    if (isset($information['error'])) {
                        $ret['result'] = 'failed';
                        $ret['error'] = $information['error'];
                    } else {
                        $ret['result'] = 'success';
                        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'remote', $information['remote']);
                    }
                    echo json_encode($ret);
                }
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function archieve_website_list(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['remote']) && !empty($_POST['remote']) && is_string($_POST['remote']) &&
                isset($_POST['type']) && !empty($_POST['type']) && is_string($_POST['type'])) {
                $json = sanitize_text_field($_POST['remote']);
                $json = stripslashes($json);
                $remote_options = json_decode($json, true);
                if (is_null($remote_options)) {
                    die();
                }
                $remote_options['type'] = sanitize_text_field($_POST['type']);

                $ret = $this->remote->check_remote_options($remote_options);
                if($ret['result']=='success') {
                    $remote_settings = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('remote_addon', array());
                    if(empty($remote_settings)){
                        $remote_settings = array();
                    }
                    $remote_id = uniqid('wpvivid-remote-');
                    $remote_settings['upload'][$remote_id]=$ret['options'];

                    Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('remote_addon', $remote_settings);
                    //$remote_id = Mainwp_WPvivid_Extension_Option::get_instance()->add_global_remote_addon($remote_options);

                    $remote_storages = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('remote_addon', array());
                    $remote_list = '';
                    if (isset($remote_storages['upload']) && !empty($remote_storages['upload'])) {
                        $remote_list = $remote_storages['upload'];
                    }
                    $table = new MainWP_WPvivid_Remote_Storage_Global_List();
                    $table->set_storage_list($remote_list);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['remote_list'] = ob_get_clean();

                    global $mainwp_wpvivid_extension_activator;
                    $websites_with_plugin = $mainwp_wpvivid_extension_activator->get_websites_ex();
                    $new_website_list = array();
                    if ( is_array( $websites_with_plugin ) && count( $websites_with_plugin ) > 0 ) {
                        foreach ($websites_with_plugin as $website) {
                            if(!$website['check-status']){
                                continue;
                            }
                            if ($website['individual']) {
                                continue;
                            }
                            $new_website_list[] = $website;
                        }
                    }
                    if(isset($_POST['batch'])) {
                        $batch = $_POST['batch'];
                    }
                    else{
                        $batch = '0';
                    }
                    ob_start();
                    ?>
                    <div class="mwp-wpvivid-block-bottom-space">
                        <div>
                            <label>
                                <input type="radio" name="mwp_wpvivid_default_remote" value="default_only" checked />
                                <span>Set as the only remote storage (This will disable and replace the remote storage you’ve set on child sites).</span>
                            </label>
                        </div>
                        <div>
                            <label>
                                <input type="radio" name="mwp_wpvivid_default_remote" value="default_append" />
                                <span>Set as an additional remote storage (This will add the remote storage as another default remote storage on child sites, and will not disable the remote storage you've set).</span>
                            </label>
                        </div>
                        <div>
                            <label>
                                <input type="checkbox" id="mwp_wpvivid_check_all_websites" />
                                <span>Select all websites include other page</span>
                            </label>
                        </div>
                    </div>
                    <div id="mwp_wpvivid_website_list_addon">
                        <?php
                        $table = new MainWP_WPvivid_Website_List();
                        $table->set_website_list($new_website_list, $batch);
                        $table->prepare_items();
                        $table->display();
                        ?>
                    </div>

                    <div class="postbox" id="mwp_wpvivid_sync_task_progress" style="display: none; margin-top: 10px; margin-bottom: 0;">
                        <div class="mwp-action-progress-bar" id="mwp_wpvivid_sync_bar_percent">
                            <div class="mwp-action-progress-bar-percent" style="width:0; height:24px;"></div>
                        </div>
                        <div style="clear: both;"></div>
                        <div style="margin-left:10px; margin-bottom:10px; float: left; width:100%;"><p id="mwp_wpvivid_sync_current_doing"></p></div>
                        <div style="clear: both;"></div>
                    </div>
                    <div class="postbox" id="mwp_wpvivid_sync_summary" style="display: none; margin-top: 10px; margin-bottom: 0; padding: 10px;"></div>

                    <?php
                    $ret['html'] = ob_get_clean();
                    $ret['html'] .= '<div style="margin-top:10px;"><input class="ui green mini button mwp-wpvivid-return-remote" type="button" value="' . esc_attr('Return', 'mainwp-wpvivid-extension') . '" /></div>';
                    $ret['remote_id'] = $remote_id;
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function archieve_website_list_ex(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['remote_id']) && !empty($_POST['remote_id']) && is_string($_POST['remote_id'])) {
                $remote_id = sanitize_text_field($_POST['remote_id']);
                global $mainwp_wpvivid_extension_activator;
                $websites_with_plugin = $mainwp_wpvivid_extension_activator->get_websites_ex();
                $new_website_list = array();
                if ( is_array( $websites_with_plugin ) && count( $websites_with_plugin ) > 0 ) {
                    foreach ($websites_with_plugin as $website) {
                        if(!$website['check-status']){
                            continue;
                        }
                        if ($website['individual']) {
                            continue;
                        }
                        $new_website_list[] = $website;
                    }
                }
                if(isset($_POST['batch'])) {
                    $batch = $_POST['batch'];
                }
                else{
                    $batch = '0';
                }
                ob_start();
                ?>
                <div class="mwp-wpvivid-block-bottom-space">
                    <div>
                        <label>
                            <input type="radio" name="mwp_wpvivid_default_remote" value="default_only" checked />
                            <span>Set as the only remote storage (This will disable and replace the remote storage you’ve set on child sites).</span>
                        </label>
                    </div>
                    <div>
                        <label>
                            <input type="radio" name="mwp_wpvivid_default_remote" value="default_append" />
                            <span>Set as an additional remote storage (This will add the remote storage as another default remote storage on child sites, and will not disable the remote storage you've set).</span>
                        </label>
                    </div>
                    <div>
                        <label>
                            <input type="checkbox" id="mwp_wpvivid_check_all_websites" />
                            <span>Select all child sites</span>
                        </label>
                    </div>
                </div>
                <div id="mwp_wpvivid_website_list_addon">
                    <?php
                    $table = new MainWP_WPvivid_Website_List();
                    $table->set_website_list($new_website_list, $batch, $remote_id);
                    $table->prepare_items();
                    $table->display();
                    ?>
                </div>

                <div class="postbox" id="mwp_wpvivid_sync_task_progress" style="display: none; margin-top: 10px; margin-bottom: 0;">
                    <div class="mwp-action-progress-bar" id="mwp_wpvivid_sync_bar_percent">
                        <div class="mwp-action-progress-bar-percent" style="width:0; height:24px;"></div>
                    </div>
                    <div style="clear: both;"></div>
                    <div style="margin-left:10px; margin-bottom:10px; float: left; width:100%;"><p id="mwp_wpvivid_sync_current_doing"></p></div>
                    <div style="clear: both;"></div>
                </div>
                <div class="postbox" id="mwp_wpvivid_sync_summary" style="display: none; margin-top: 10px; margin-bottom: 0; padding: 10px;"></div>

                <?php
                $ret['html'] = ob_get_clean();
                $ret['html'] .= '<div style="margin-top:10px;"><input class="ui green mini button mwp-wpvivid-return-remote" type="button" value="' . esc_attr('Return', 'mainwp-wpvivid-extension') . '" /></div>';
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_website_list(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['page'])){
                $page = sanitize_text_field($_POST['page']);

                global $mainwp_wpvivid_extension_activator;
                $websites_with_plugin = $mainwp_wpvivid_extension_activator->get_websites_ex();
                $new_website_list = array();
                if ( is_array( $websites_with_plugin ) && count( $websites_with_plugin ) > 0 ) {
                    foreach ($websites_with_plugin as $website) {
                        if(!$website['check-status']){
                            continue;
                        }
                        if ($website['individual']) {
                            continue;
                        }
                        $new_website_list[] = $website;
                    }
                }
                if(isset($_POST['batch'])) {
                    $batch = $_POST['batch'];
                }
                else{
                    $batch = '0';
                }
                ob_start();
                $table = new MainWP_WPvivid_Website_List();
                $table->set_website_list($new_website_list, $batch, '', $page);
                $table->prepare_items();
                $table->display();
                $ret['html'] = ob_get_clean();
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function archieve_all_website_list(){
        $this->mwp_ajax_check_security();
        try{
            global $mainwp_wpvivid_extension_activator;
            $websites_with_plugin = $mainwp_wpvivid_extension_activator->get_websites_ex();
            $new_website_list = array();
            if ( is_array( $websites_with_plugin ) && count( $websites_with_plugin ) > 0 ) {
                foreach ($websites_with_plugin as $website) {
                    if(!$website['check-status']){
                        continue;
                    }
                    if ($website['individual']) {
                        continue;
                    }
                    $domain = rtrim(trailingslashit($website['url']), '/');
                    $parse = parse_url($domain);
                    $path = '';
                    if(isset($parse['path'])) {
                        $parse['path'] = str_replace('/', '_', $parse['path']);
                        $parse['path'] = str_replace('.', '_', $parse['path']);
                        $path = $parse['path'];
                    }
                    $parse['host'] = str_replace('/', '_', $parse['host']);
                    $parse['host'] = str_replace('.', '_', $parse['host']);
                    $website['custom_path'] = $parse['host'].$path;
                    $new_website_list[] = $website;
                }
            }
            $ret['result'] = 'success';
            $ret['websites'] = $new_website_list;
            echo json_encode($ret);
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_schedule_mould_list(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['page'])){
                $page = sanitize_text_field($_POST['page']);

                $schedule_mould_list = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_mould_addon', array());
                $table = new Mainwp_WPvivid_Schedule_Mould_List();
                $table->set_schedule_mould_list($schedule_mould_list, $page);
                $table->prepare_items();
                ob_start();
                $table->display();
                $html = ob_get_clean();
                $ret['schedule_mould_list'] = $html;
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_remote_storage_list(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['page'])){
                $page = sanitize_text_field($_POST['page']);

                $remote_storages = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('remote_addon', array());
                $remote_list = '';
                if (isset($remote_storages['upload']) && !empty($remote_storages['upload'])) {
                    $remote_list = $remote_storages['upload'];
                }
                $table = new MainWP_WPvivid_Remote_Storage_Global_List();
                $table->set_storage_list($remote_list, $page);
                $table->prepare_items();
                ob_start();
                $table->display();
                $ret['remote_list'] = ob_get_clean();
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function set_general_setting_addon()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['setting']) && !empty($_POST['setting']) && is_string($_POST['setting'])) {
                $setting = array();
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_set_general_setting_addon_mainwp';
                $json = stripslashes(sanitize_text_field($_POST['setting']));
                $setting = json_decode($json, true);

                $options=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option(sanitize_text_field($_GET['id']), 'settings_addon', array());

                $mwp_use_temp_file = isset($setting['mwp_use_temp_file_addon']) ? $setting['mwp_use_temp_file_addon'] : 1;
                $mwp_use_temp_size = isset($setting['mwp_use_temp_size_addon']) ? $setting['mwp_use_temp_size_addon'] : 16;
                $mwp_compress_type = isset($setting['mwp_compress_type_addon']) ? $setting['mwp_compress_type_addon'] : 'zip';

                $setting['mwp_use_temp_file_addon'] = intval($mwp_use_temp_file);
                $setting['mwp_use_temp_size_addon'] = intval($mwp_use_temp_size);
                $setting['mwp_exclude_file_size_addon'] = intval($setting['mwp_exclude_file_size_addon']);
                $setting['mwp_max_execution_time_addon'] = intval($setting['mwp_max_execution_time_addon']);
                $setting['mwp_max_backup_count_addon'] = intval($setting['mwp_max_local_backup_count_addon']);
                if(isset($setting['mwp_max_remote_backup_count_addon'])){
                    $setting['mwp_max_remote_backup_count_addon'] = intval($setting['mwp_max_remote_backup_count_addon']);
                }
                else{
                    $setting['mwp_max_remote_backup_count_addon'] = 30;
                }
                $setting['mwp_max_resume_count_addon'] = intval($setting['mwp_max_resume_count_addon']);

                $setting_data['wpvivid_compress_setting']['compress_type'] = $mwp_compress_type;
                $setting_data['wpvivid_compress_setting']['max_file_size'] = $setting['mwp_max_file_size_addon'] . 'M';
                $setting_data['wpvivid_compress_setting']['no_compress'] = $setting['mwp_no_compress_addon'];
                $setting_data['wpvivid_compress_setting']['use_temp_file'] = $setting['mwp_use_temp_file_addon'];
                $setting_data['wpvivid_compress_setting']['use_temp_size'] = $setting['mwp_use_temp_size_addon'];
                $setting_data['wpvivid_compress_setting']['exclude_file_size'] = $setting['mwp_exclude_file_size_addon'];
                $setting_data['wpvivid_compress_setting']['subpackage_plugin_upload'] = $setting['mwp_subpackage_plugin_upload_addon'];

                $setting_data['wpvivid_local_setting']['path'] = $setting['mwp_path_addon'];
                $setting_data['wpvivid_local_setting']['save_local'] = isset($options['wpvivid_local_setting']['save_local']) ? $options['wpvivid_local_setting']['save_local'] : 1;

                $setting_data['wpvivid_common_setting']['max_execution_time'] = $setting['mwp_max_execution_time_addon'];
                $setting_data['wpvivid_common_setting']['log_save_location'] = $setting['mwp_path_addon'] . '/wpvivid_log';
                $setting_data['wpvivid_common_setting']['max_backup_count'] = $setting['mwp_max_backup_count_addon'];
                $setting_data['wpvivid_common_setting']['max_remote_backup_count'] = $setting['mwp_max_remote_backup_count_addon'];
                $setting_data['wpvivid_common_setting']['show_admin_bar'] = isset($options['wpvivid_common_setting']['show_admin_bar']) ? $options['wpvivid_common_setting']['show_admin_bar'] : 1;
                $setting_data['wpvivid_common_setting']['clean_old_remote_before_backup'] = $setting['mwp_clean_old_remote_before_backup_addon'];
                $setting_data['wpvivid_common_setting']['estimate_backup'] = $setting['mwp_estimate_backup_addon'];
                $setting_data['wpvivid_common_setting']['ismerge'] = $setting['mwp_ismerge_addon'];
                $setting_data['wpvivid_common_setting']['domain_include'] = isset($options['wpvivid_common_setting']['domain_include']) ? $options['wpvivid_common_setting']['domain_include'] : 1;
                $setting_data['wpvivid_common_setting']['memory_limit'] = $setting['mwp_memory_limit_addon'] . 'M';
                $setting_data['wpvivid_common_setting']['max_resume_count'] = $setting['mwp_max_resume_count_addon'];
                $setting_data['wpvivid_common_setting']['db_connect_method'] = $setting['mwp_db_connect_method_addon'];

                $setting_data['wpvivid_staging_options']['staging_db_insert_count'] = intval($setting['mwp_staging_db_insert_count_addon']);
                $setting_data['wpvivid_staging_options']['staging_db_replace_count'] = intval($setting['mwp_staging_db_replace_count_addon']);
                $setting_data['wpvivid_staging_options']['staging_file_copy_count'] = intval($setting['mwp_staging_file_copy_count_addon']);
                $setting_data['wpvivid_staging_options']['staging_exclude_file_size'] = intval($setting['mwp_staging_exclude_file_size_addon']);
                $setting_data['wpvivid_staging_options']['staging_memory_limit'] = $setting['mwp_staging_memory_limit_addon'].'M';
                $setting_data['wpvivid_staging_options']['staging_max_execution_time'] = intval($setting['mwp_staging_max_execution_time_addon']);
                $setting_data['wpvivid_staging_options']['staging_resume_count'] = intval($setting['mwp_staging_resume_count_addon']);

                foreach ($setting_data as $option_name => $option) {
                    $options[$option_name] = $setting_data[$option_name];
                }

                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'settings_addon', $options);

                $post_data['setting'] = json_encode($options);

                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);

                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function save_menu_capability_addon()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['caps']) && !empty($_POST['caps']) && is_string($_POST['caps'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_set_menu_capability_addon_mainwp';

                $json = stripslashes(sanitize_text_field($_POST['caps']));
                $caps = json_decode($json, true);
                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'menu_capability', $caps);

                $post_data['menu_cap'] = json_encode($caps);
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);

                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function save_global_menu_capability_addon()
    {
        $this->mwp_ajax_check_security();
        try {
            if(isset($_POST['caps']) && !empty($_POST['caps']) && is_string($_POST['caps'])) {
                $json = stripslashes(sanitize_text_field($_POST['caps']));
                $caps = json_decode($json, true);
                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('menu_capability', $caps);

                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function set_global_general_setting_addon()
    {
        $this->mwp_ajax_check_security();
        try {
            $setting = array();
            $schedule = array();
            if (isset($_POST['setting']) && !empty($_POST['setting']) && is_string($_POST['setting'])) {
                $json = stripslashes(sanitize_text_field($_POST['setting']));
                $setting = json_decode($json, true);
                $options = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('settings_addon', array());
                $mwp_use_temp_file = isset($setting['mwp_use_temp_file_addon']) ? $setting['mwp_use_temp_file_addon'] : 1;
                $mwp_use_temp_size = isset($setting['mwp_use_temp_size_addon']) ? $setting['mwp_use_temp_size_addon'] : 16;
                $mwp_compress_type = isset($setting['mwp_compress_type_addon']) ? $setting['mwp_compress_type_addon'] : 'zip';

                $setting['mwp_use_temp_file_addon'] = intval($mwp_use_temp_file);
                $setting['mwp_use_temp_size_addon'] = intval($mwp_use_temp_size);
                $setting['mwp_exclude_file_size_addon'] = intval($setting['mwp_exclude_file_size_addon']);
                $setting['mwp_max_execution_time_addon'] = intval($setting['mwp_max_execution_time_addon']);
                $setting['mwp_max_backup_count_addon'] = intval($setting['mwp_max_local_backup_count_addon']);
                if(isset($setting['mwp_max_remote_backup_count_addon'])){
                    $setting['mwp_max_remote_backup_count_addon'] = intval($setting['mwp_max_remote_backup_count_addon']);
                }
                else{
                    $setting['mwp_max_remote_backup_count_addon'] = 30;
                }
                $setting['mwp_max_resume_count_addon'] = intval($setting['mwp_max_resume_count_addon']);

                $setting_data['wpvivid_compress_setting']['compress_type'] = $mwp_compress_type;
                $setting_data['wpvivid_compress_setting']['max_file_size'] = $setting['mwp_max_file_size_addon'] . 'M';
                $setting_data['wpvivid_compress_setting']['no_compress'] = $setting['mwp_no_compress_addon'];
                $setting_data['wpvivid_compress_setting']['use_temp_file'] = $setting['mwp_use_temp_file_addon'];
                $setting_data['wpvivid_compress_setting']['use_temp_size'] = $setting['mwp_use_temp_size_addon'];
                $setting_data['wpvivid_compress_setting']['exclude_file_size'] = $setting['mwp_exclude_file_size_addon'];
                $setting_data['wpvivid_compress_setting']['subpackage_plugin_upload'] = $setting['mwp_subpackage_plugin_upload_addon'];

                $setting_data['wpvivid_local_setting']['path'] = $setting['mwp_path_addon'];
                $setting_data['wpvivid_local_setting']['save_local'] = isset($options['wpvivid_local_setting']['save_local']) ? $options['wpvivid_local_setting']['save_local'] : 1;

                $setting_data['wpvivid_common_setting']['max_execution_time'] = $setting['mwp_max_execution_time_addon'];
                $setting_data['wpvivid_common_setting']['log_save_location'] = $setting['mwp_path_addon'] . '/wpvivid_log';
                $setting_data['wpvivid_common_setting']['max_backup_count'] = $setting['mwp_max_backup_count_addon'];
                $setting_data['wpvivid_common_setting']['max_remote_backup_count'] = $setting['mwp_max_remote_backup_count_addon'];
                $setting_data['wpvivid_common_setting']['show_admin_bar'] = isset($options['wpvivid_common_setting']['show_admin_bar']) ? $options['wpvivid_common_setting']['show_admin_bar'] : 1;
                $setting_data['wpvivid_common_setting']['clean_old_remote_before_backup'] = $setting['mwp_clean_old_remote_before_backup_addon'];
                $setting_data['wpvivid_common_setting']['estimate_backup'] = $setting['mwp_estimate_backup_addon'];
                $setting_data['wpvivid_common_setting']['ismerge'] = $setting['mwp_ismerge_addon'];
                $setting_data['wpvivid_common_setting']['domain_include'] = isset($options['wpvivid_common_setting']['domain_include']) ? $options['wpvivid_common_setting']['domain_include'] : 1;
                $setting_data['wpvivid_common_setting']['memory_limit'] = $setting['mwp_memory_limit_addon'] . 'M';
                $setting_data['wpvivid_common_setting']['max_resume_count'] = $setting['mwp_max_resume_count_addon'];
                $setting_data['wpvivid_common_setting']['db_connect_method'] = $setting['mwp_db_connect_method_addon'];

                $setting_data['wpvivid_staging_options']['staging_db_insert_count'] = intval($setting['mwp_staging_db_insert_count_addon']);
                $setting_data['wpvivid_staging_options']['staging_db_replace_count'] = intval($setting['mwp_staging_db_replace_count_addon']);
                $setting_data['wpvivid_staging_options']['staging_file_copy_count'] = intval($setting['mwp_staging_file_copy_count_addon']);
                $setting_data['wpvivid_staging_options']['staging_exclude_file_size'] = intval($setting['mwp_staging_exclude_file_size_addon']);
                $setting_data['wpvivid_staging_options']['staging_memory_limit'] = $setting['mwp_staging_memory_limit_addon'].'M';
                $setting_data['wpvivid_staging_options']['staging_max_execution_time'] = intval($setting['mwp_staging_max_execution_time_addon']);
                $setting_data['wpvivid_staging_options']['staging_resume_count'] = intval($setting['mwp_staging_resume_count_addon']);

                if(empty($options)){
                    $options = array();
                }
                foreach ($setting_data as $option_name => $option) {
                    $options[$option_name] = $setting_data[$option_name];
                }

                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('settings_addon', $options);

                $ret['result'] = 'success';

                echo json_encode($ret);
            }
            die();
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
    }

    public function refresh_incremental_tables(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_refresh_incremental_table_addon_mainwp';
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['database_tables'] = Mainwp_WPvivid_Extension_Subpage::output_database_table($information['database_tables']['base_tables'], $information['database_tables']['other_tables']);
                    $ret['themes_plugins_table'] = Mainwp_WPvivid_Extension_Subpage::output_themes_plugins_table($information['theme_plugin_tables']['themes'], $information['theme_plugin_tables']['plugins']);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function enable_incremental_backup(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) && isset($_POST['enable'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $post_data['mwp_action'] = 'wpvivid_enable_incremental_backup_mainwp';
                $post_data['enable'] = $_POST['enable'];
                if ($_POST['enable']) {
                    $this->set_incremental_enable($site_id, true);
                }
                else{
                    $this->set_incremental_enable($site_id, false);
                }
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $table=new Mainwp_WPvivid_Schedule_List();
                    $table->set_schedule_list($information['schedule_info']);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                    $ret['html'] = $html;
                    $this->set_incremental_schedules($site_id, $information['incremental_schedules']);
                    $this->set_incremental_backup_data($site_id, $information['incremental_backup_data']);
                    $this->set_incremental_output_msg($site_id, $information['incremental_output_msg']);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function set_incremental_file_settings($site_id, $options)
    {
        $incremental_backup_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'incremental_backup_setting', array());
        if(empty($incremental_backup_setting)){
            $incremental_backup_setting = array();
            $history = array();
        }
        else{
            $history = isset($incremental_backup_setting['incremental_history']) ? $incremental_backup_setting['incremental_history'] : array();
            if(empty($history)){
                $history = array();
            }
        }

        $custom_option['database_option']['database_check'] = isset($options['database_check']) ? $options['database_check'] : 0;
        $custom_option['database_option']['exclude_table_list'] = isset($options['database_list']) ? $options['database_list'] : array();

        $custom_option['themes_option']['themes_check'] = isset($options['themes_check']) ? $options['themes_check'] : 0;
        $custom_option['themes_option']['exclude_themes_list'] = isset($options['themes_list']) ? $options['themes_list'] : array();

        $custom_option['plugins_option']['plugins_check'] = isset($options['plugins_check']) ? $options['plugins_check'] : 0;
        $custom_option['plugins_option']['exclude_plugins_list'] = isset($options['plugins_list']) ? $options['plugins_list'] : array();

        $custom_option['uploads_option']['uploads_check'] = isset($options['uploads_check']) ? $options['uploads_check'] : 0;
        $custom_option['uploads_option']['exclude_uploads_list'] = isset($options['uploads_list']) ? $options['uploads_list'] : array();
        $custom_option['uploads_option']['uploads_extension_list'] = isset($options['upload_extension']) ? $options['upload_extension'] : array();

        $custom_option['content_option']['content_check'] = isset($options['content_check']) ? $options['content_check'] : 0;
        $custom_option['content_option']['exclude_content_list'] = isset($options['content_list']) ? $options['content_list'] : array();
        $custom_option['content_option']['content_extension_list'] = isset($options['content_extension']) ? $options['content_extension'] : array();

        $custom_option['core_option']['core_check'] = isset($options['core_check']) ? $options['core_check'] : 0;

        $custom_option['other_option']['other_check'] = isset($options['other_check']) ? $options['other_check'] : 0;
        $custom_option['other_option']['include_other_list'] = isset($options['other_list']) ? $options['other_list'] : array();
        $custom_option['other_option']['other_extension_list'] = isset($options['other_extension']) ? $options['other_extension'] : array();

        $custom_option['additional_database_option']['additional_database_check'] = isset($options['additional_database_check']) ? $options['additional_database_check'] : 0;
        if(isset($history['incremental_file']['additional_database_option'])) {
            $custom_option['additional_database_option'] = $history['incremental_file']['additional_database_option'];
        }

        $incremental_backup_setting['incremental_history']['incremental_file'] = $custom_option;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'incremental_backup_setting', $incremental_backup_setting);
    }

    public function set_incremental_db_setting($site_id, $options){
        $incremental_backup_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'incremental_backup_setting', array());
        if(empty($incremental_backup_setting)){
            $incremental_backup_setting = array();
            $history = array();
        }
        else{
            $history = isset($incremental_backup_setting['incremental_history']) ? $incremental_backup_setting['incremental_history'] : array();
            if(empty($history)){
                $history = array();
            }
        }

        $custom_option['database_option']['database_check'] = isset($options['database_check']) ? $options['database_check'] : 0;
        $custom_option['database_option']['exclude_table_list'] = isset($options['database_list']) ? $options['database_list'] : array();

        $custom_option['themes_option']['themes_check'] = isset($options['themes_check']) ? $options['themes_check'] : 0;
        $custom_option['themes_option']['exclude_themes_list'] = isset($options['themes_list']) ? $options['themes_list'] : array();

        $custom_option['plugins_option']['plugins_check'] = isset($options['plugins_check']) ? $options['plugins_check'] : 0;
        $custom_option['plugins_option']['exclude_plugins_list'] = isset($options['plugins_list']) ? $options['plugins_list'] : array();

        $custom_option['uploads_option']['uploads_check'] = isset($options['uploads_check']) ? $options['uploads_check'] : 0;
        $custom_option['uploads_option']['exclude_uploads_list'] = isset($options['uploads_list']) ? $options['uploads_list'] : array();
        $custom_option['uploads_option']['uploads_extension_list'] = isset($options['upload_extension']) ? $options['upload_extension'] : array();

        $custom_option['content_option']['content_check'] = isset($options['content_check']) ? $options['content_check'] : 0;
        $custom_option['content_option']['exclude_content_list'] = isset($options['content_list']) ? $options['content_list'] : array();
        $custom_option['content_option']['content_extension_list'] = isset($options['content_extension']) ? $options['content_extension'] : array();

        $custom_option['core_option']['core_check'] = isset($options['core_check']) ? $options['core_check'] : 0;

        $custom_option['other_option']['other_check'] = isset($options['other_check']) ? $options['other_check'] : 0;
        $custom_option['other_option']['include_other_list'] = isset($options['other_list']) ? $options['other_list'] : array();
        $custom_option['other_option']['other_extension_list'] = isset($options['other_extension']) ? $options['other_extension'] : array();

        if(isset($history['incremental_db']['additional_database_option'])) {
            $custom_option['additional_database_option'] = $history['incremental_db']['additional_database_option'];
        }
        $custom_option['additional_database_option']['additional_database_check'] = isset($options['additional_database_check']) ? $options['additional_database_check'] : 0;

        $incremental_backup_setting['incremental_history']['incremental_db'] = $custom_option;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'incremental_backup_setting', $incremental_backup_setting);
    }

    public function set_incremental_remote_retain_count($site_id, $count){
        $incremental_backup_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'incremental_backup_setting', array());
        if(empty($incremental_backup_setting)){
            $incremental_backup_setting = array();
        }

        $incremental_backup_setting['incremental_remote_backup_count'] = $count;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'incremental_backup_setting', $incremental_backup_setting);
    }

    public function set_incremental_enable($site_id, $status){
        $incremental_backup_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'incremental_backup_setting', array());
        if(empty($incremental_backup_setting)){
            $incremental_backup_setting = array();
        }

        $incremental_backup_setting['enable_incremental_schedules'] = $status;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'incremental_backup_setting', $incremental_backup_setting);
    }

    public function set_incremental_schedules($site_id, $schedules){
        $incremental_backup_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'incremental_backup_setting', array());
        if(empty($incremental_backup_setting)){
            $incremental_backup_setting = array();
        }

        $incremental_backup_setting['incremental_schedules'] = $schedules;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'incremental_backup_setting', $incremental_backup_setting);
    }

    public function set_incremental_backup_data($site_id, $data){
        $incremental_backup_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'incremental_backup_setting', array());
        if(empty($incremental_backup_setting)){
            $incremental_backup_setting = array();
        }

        $incremental_backup_setting['incremental_backup_data'] = $data;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'incremental_backup_setting', $incremental_backup_setting);
    }

    public function set_incremental_output_msg($site_id, $msg){
        $incremental_backup_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($site_id, 'incremental_backup_setting', array());
        if(empty($incremental_backup_setting)){
            $incremental_backup_setting = array();
        }

        $incremental_backup_setting['incremental_output_msg'] = $msg;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'incremental_backup_setting', $incremental_backup_setting);
    }

    public function mwp_wpvivid_custom_backup_data_transfer($options, $data, $type)
    {
        if(!isset($data['database_check'])){
            $data['database_check'] = 0;
        }
        $options['backup_select']['db'] = intval($data['database_check']);
        if(!isset($data['database_list'])){
            $data['database_list'] = array();
        }
        $options['exclude_tables'] = $data['database_list'];

        if(!isset($data['themes_check'])){
            $data['themes_check'] = 0;
        }
        $options['backup_select']['themes'] = intval($data['themes_check']);
        if(!isset($data['themes_list'])){
            $data['themes_list'] = array();
        }
        $options['exclude_themes'] = $data['themes_list'];

        if(!isset($data['plugins_check'])){
            $data['plugins_check'] = 0;
        }
        $options['backup_select']['plugin'] = intval($data['plugins_check']);
        if(!isset($data['plugins_list'])){
            $data['plugins_list'] = array();
        }
        $options['exclude_plugins'] = $data['plugins_list'];

        if(!isset($data['uploads_check'])){
            $data['uploads_check'] = 0;
        }
        $options['backup_select']['uploads'] = intval($data['uploads_check']);
        $upload_exclude_list = array();
        if(isset($data['uploads_list'])) {
            foreach ($data['uploads_list'] as $key => $value){
                $upload_exclude_list[] = $key;
            }
        }
        else{
            $data['uploads_list'] = array();
        }
        $options['exclude_uploads'] = $upload_exclude_list;
        $upload_exclude_file_list=array();
        $upload_extension_tmp = array();
        if(isset($data['upload_extension']) && !empty($data['upload_extension'])) {
            $str_tmp = explode(',', $data['upload_extension']);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $upload_exclude_file_list[] = '.*\.'.$str_tmp[$index].'$';
                    $upload_extension_tmp[] = $str_tmp[$index];
                }
            }
            $data['upload_extension'] = $upload_extension_tmp;
        }
        else{
            $data['upload_extension'] = array();
        }
        $options['exclude_uploads_files'] = $upload_exclude_file_list;

        if(!isset($data['content_check'])){
            $data['content_check'] = 0;
        }
        $options['backup_select']['content'] = intval($data['content_check']);
        $content_exclude_list=array();
        if(isset($data['content_list'])) {
            foreach ($data['content_list'] as $key => $value){
                $content_exclude_list[] = $key;
            }
        }
        else{
            $data['content_list'] = array();
        }
        $options['exclude_content'] = $content_exclude_list;
        $content_exclude_file_list=array();
        $content_extension_tmp = array();
        if(isset($data['content_extension']) && !empty($data['content_extension'])) {
            $str_tmp = explode(',', $data['content_extension']);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $content_exclude_file_list[] = '.*\.'.$str_tmp[$index].'$';
                    $content_extension_tmp[] = $str_tmp[$index];
                }
            }
            $data['content_extension'] = $content_extension_tmp;
        }
        else{
            $data['content_extension'] = array();
        }
        $options['exclude_content_files'] = $content_exclude_file_list;

        if(!isset($data['core_check'])){
            $data['core_check'] = 0;
        }
        $options['backup_select']['core'] = intval($data['core_check']);

        if(!isset($data['other_check'])){
            $data['other_check'] = 0;
        }
        $options['backup_select']['other'] = intval($data['other_check']);
        $other_include_list=array();
        if(isset($data['other_list'])) {
            foreach ($data['other_list'] as $key => $value){
                $other_include_list[] = $key;
            }
        }
        else{
            $data['other_list'] = array();
        }
        $options['custom_other_root'] = $other_include_list;
        $other_exclude_file_list=array();
        $other_extension_tmp = array();
        if(isset($data['other_extension']) && !empty($data['other_extension'])) {
            $str_tmp = explode(',', $data['other_extension']);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $other_exclude_file_list[] = '.*\.'.$str_tmp[$index].'$';
                    $other_extension_tmp[] = $str_tmp[$index];
                }
            }
            $data['other_extension'] = $other_extension_tmp;
        }
        else{
            $data['other_extension'] = array();
        }
        $options['exclude_custom_other_files'] = $other_exclude_file_list;
        $options['exclude_custom_other']=array();

        if(!isset($data['additional_database_check'])){
            $data['additional_database_check'] = 0;
        }
        $options['backup_select']['additional_db'] = intval($data['additional_database_check']);
        if($options['backup_select']['additional_db'] === 1){

            if(isset($history['additional_database_option']['additional_database_list']) && !empty($history['additional_database_option']['additional_database_list'])) {
                $options['additional_database_list'] = $history['additional_database_option']['additional_database_list'];
            }
            else{
                $options['additional_database_list'] = array();
            }
        }

        return $options;
    }

    public function check_incremental_schedule_option($data){
        $ret['schedule']['file_start_time_zone'] = $data['file_start_time_zone'];
        $ret['schedule']['db_start_time_zone'] = $data['db_start_time_zone'];
        $ret['schedule']['incremental_recurrence'] =$data['recurrence'];
        $ret['schedule']['incremental_recurrence_week'] =$data['recurrence_week'];
        $ret['schedule']['incremental_recurrence_day'] =$data['recurrence_day'];
        $ret['schedule']['incremental_files_recurrence'] =$data['incremental_files_recurrence'];
        $ret['schedule']['incremental_db_recurrence'] =$data['incremental_db_recurrence'];
        $ret['schedule']['incremental_db_recurrence_week'] = $data['incremental_db_recurrence_week'];
        $ret['schedule']['incremental_db_recurrence_day'] = $data['incremental_db_recurrence_day'];
        $ret['schedule']['incremental_files_start_backup'] = $data['incremental_files_start_backup'];

        if(isset($data['custom']['files'])){
            $ret['schedule']['backup_files']=array();
            $ret['schedule']['backup_files'] = apply_filters('mwp_wpvivid_custom_backup_data_transfer', $ret['schedule']['backup_files'], $data['custom']['files'], 'incremental_backup_file');
        }
        if(isset($data['custom']['db'])){
            $ret['schedule']['backup_db']=array();
            $ret['schedule']['backup_db'] = apply_filters('mwp_wpvivid_custom_backup_data_transfer', $ret['schedule']['backup_db'], $data['custom']['db'], 'incremental_backup_db');
        }
        $data['save_local_remote']=sanitize_text_field($data['save_local_remote']);

        if(!empty($data['save_local_remote']))
        {
            if($data['save_local_remote'] == 'remote')
            {
                $ret['schedule']['backup']['remote']=1;
                $ret['schedule']['backup']['local']=0;
            }
            else
            {
                $ret['schedule']['backup']['remote']=0;
                $ret['schedule']['backup']['local']=1;
            }
        }

        if(isset($data['db_current_day']))
        {
            $ret['schedule']['db_current_day'] = $data['db_current_day'];
        }

        if(isset($data['files_current_day']))
        {
            $ret['schedule']['files_current_day'] = $data['files_current_day'];
        }

        $ret['schedule']['files_current_day_hour'] = $data['files_current_day_hour'];
        $ret['schedule']['files_current_day_minute'] = $data['files_current_day_minute'];
        $ret['schedule']['db_current_day_hour'] = $data['db_current_day_hour'];
        $ret['schedule']['db_current_day_minute'] = $data['db_current_day_minute'];
        return $ret;
    }

    public function mwp_add_incremental_schedule($schedule){
        $schedule_data=array();
        $schedule_data['id']=uniqid('wpvivid_incremental_schedule');
        $schedule_data['files_schedule_id']=uniqid('wpvivid_incremental_files_schedule_event');
        $schedule_data['db_schedule_id']=uniqid('wpvivid_incremental_db_schedule_event');

        $schedule['backup']['ismerge']=1;
        $schedule['backup']['lock']=0;
        $schedule_data= $this->mwp_set_incremental_schedule_data($schedule_data,$schedule);

        $schedules=array();
        $schedules[$schedule_data['id']]=$schedule_data;
        return $schedules;
    }

    public function mwp_set_incremental_schedule_data($schedule_data,$schedule){
        $schedule_data['file_start_time_zone'] = $schedule['file_start_time_zone'];
        $schedule_data['db_start_time_zone'] = $schedule['db_start_time_zone'];
        $schedule_data['incremental_recurrence']=$schedule['incremental_recurrence'];
        $schedule_data['incremental_recurrence_week']=$schedule['incremental_recurrence_week'];
        $schedule_data['incremental_recurrence_day']=$schedule['incremental_recurrence_day'] ;
        $schedule_data['incremental_files_recurrence']=$schedule['incremental_files_recurrence'];
        $schedule_data['incremental_db_recurrence']=$schedule['incremental_db_recurrence'];
        $schedule_data['incremental_db_recurrence_week']=$schedule['incremental_db_recurrence_week'];
        $schedule_data['incremental_db_recurrence_day']=$schedule['incremental_db_recurrence_day'];
        $schedule_data['db_current_day']=$schedule['db_current_day'];
        $schedule_data['files_current_day']=$schedule['files_current_day'];
        $schedule_data['incremental_files_start_backup']=$schedule['incremental_files_start_backup'];
        $schedule_data['files_current_day_hour'] = $schedule['files_current_day_hour'];
        $schedule_data['files_current_day_minute'] = $schedule['files_current_day_minute'];
        $schedule_data['db_current_day_hour'] = $schedule['db_current_day_hour'];
        $schedule_data['db_current_day_minute'] = $schedule['db_current_day_minute'];

        $schedule_data['backup_files'] = $schedule['backup_files'];
        $schedule_data['backup_db'] = $schedule['backup_db'];

        $schedule_data['backup']=$schedule['backup'];
        return $schedule_data;
    }

    public function set_global_incremental_file_settings($incremental_schedule_mould_name, $options){
        $incremental_backup_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('incremental_backup_setting', array());
        if(empty($incremental_backup_setting)){
            $incremental_backup_setting = array();
            $history = array();
        }
        else{
            $history = isset($incremental_backup_setting['incremental_history']) ? $incremental_backup_setting['incremental_history'] : array();
            if(empty($history)){
                $history = array();
            }
        }

        $custom_option['database_option']['database_check'] = isset($options['database_check']) ? $options['database_check'] : 0;

        $custom_option['themes_option']['themes_check'] = isset($options['themes_check']) ? $options['themes_check'] : 0;

        $custom_option['plugins_option']['plugins_check'] = isset($options['plugins_check']) ? $options['plugins_check'] : 0;

        $custom_option['uploads_option']['uploads_check'] = isset($options['uploads_check']) ? $options['uploads_check'] : 0;

        $upload_extension_tmp = array();
        if(isset($options['upload_extension']) && !empty($options['upload_extension'])) {
            $str_tmp = explode(',', $options['upload_extension']);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $upload_extension_tmp[] = $str_tmp[$index];
                }
            }
            $custom_option['uploads_option']['uploads_extension_list'] = $upload_extension_tmp;
        }
        else{
            $custom_option['uploads_option']['uploads_extension_list'] = array();
        }
        //$custom_option['uploads_option']['uploads_extension_list'] = isset($options['upload_extension']) ? $options['upload_extension'] : array();

        $custom_option['content_option']['content_check'] = isset($options['content_check']) ? $options['content_check'] : 0;

        $content_extension_tmp = array();
        if(isset($options['content_extension']) && !empty($options['content_extension'])) {
            $str_tmp = explode(',', $options['content_extension']);
            for($index=0; $index<count($str_tmp); $index++){
                if(!empty($str_tmp[$index])) {
                    $content_extension_tmp[] = $str_tmp[$index];
                }
            }
            $custom_option['content_option']['content_extension_list'] = $content_extension_tmp;
        }
        else{
            $custom_option['content_option']['content_extension_list'] = array();
        }
        //$custom_option['content_option']['content_extension_list'] = isset($options['content_extension']) ? $options['content_extension'] : array();

        $custom_option['core_option']['core_check'] = isset($options['core_check']) ? $options['core_check'] : 0;

        $incremental_backup_setting[$incremental_schedule_mould_name]['incremental_history']['incremental_file'] = $custom_option;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('incremental_backup_setting', $incremental_backup_setting);
    }

    public function set_global_incremental_db_settings($incremental_schedule_mould_name, $options){
        $incremental_backup_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('incremental_backup_setting', array());
        if(empty($incremental_backup_setting)){
            $incremental_backup_setting = array();
            $history = array();
        }
        else{
            $history = isset($incremental_backup_setting['incremental_history']) ? $incremental_backup_setting['incremental_history'] : array();
            if(empty($history)){
                $history = array();
            }
        }

        $custom_option['database_option']['database_check'] = isset($options['database_check']) ? $options['database_check'] : 0;

        $custom_option['themes_option']['themes_check'] = isset($options['themes_check']) ? $options['themes_check'] : 0;

        $custom_option['plugins_option']['plugins_check'] = isset($options['plugins_check']) ? $options['plugins_check'] : 0;

        $custom_option['uploads_option']['uploads_check'] = isset($options['uploads_check']) ? $options['uploads_check'] : 0;
        $custom_option['uploads_option']['uploads_extension_list'] = isset($options['upload_extension']) ? $options['upload_extension'] : array();

        $custom_option['content_option']['content_check'] = isset($options['content_check']) ? $options['content_check'] : 0;
        $custom_option['content_option']['content_extension_list'] = isset($options['content_extension']) ? $options['content_extension'] : array();

        $custom_option['core_option']['core_check'] = isset($options['core_check']) ? $options['core_check'] : 0;

        $incremental_backup_setting[$incremental_schedule_mould_name]['incremental_history']['incremental_db'] = $custom_option;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('incremental_backup_setting', $incremental_backup_setting);
    }

    public function set_global_incremental_remote_retain_count($incremental_schedule_mould_name, $count){
        $incremental_backup_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('incremental_backup_setting', array());
        if(empty($incremental_backup_setting)){
            $incremental_backup_setting = array();
        }

        $incremental_backup_setting[$incremental_schedule_mould_name]['incremental_remote_backup_count'] = $count;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('incremental_backup_setting', $incremental_backup_setting);
    }

    public function set_global_incremental_schedules($incremental_schedule_mould_name, $schedule){
        $incremental_backup_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('incremental_backup_setting', array());
        if(empty($incremental_backup_setting)){
            $incremental_backup_setting = array();
        }

        $ret = $this->check_incremental_schedule_option($schedule);
        $schedules = $this->mwp_add_incremental_schedule($ret['schedule']);

        $incremental_backup_setting[$incremental_schedule_mould_name]['incremental_schedules'] = $schedules;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('incremental_backup_setting', $incremental_backup_setting);
    }

    public function set_global_incremental_backup_schedule(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['schedule']) && !empty($_POST['schedule']) && is_string($_POST['schedule']) &&
                isset($_POST['incremental_schedule_mould_name'])  && !empty($_POST['incremental_schedule_mould_name']) && is_string($_POST['incremental_schedule_mould_name'])){
                $json = stripslashes(sanitize_text_field($_POST['schedule']));
                $schedule = json_decode($json, true);
                $incremental_schedule_mould_name = sanitize_text_field($_POST['incremental_schedule_mould_name']);

                $incremental_schedule_mould_name_array = array();
                $incremental_schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('incremental_backup_setting', array());
                if(empty($incremental_schedule_mould)){
                    $incremental_schedule_mould = array();
                }
                else{
                    foreach ($incremental_schedule_mould as $incremental_schedule_name => $value){
                        $incremental_schedule_mould_name_array[] = $incremental_schedule_name;
                    }
                }

                if(!in_array($incremental_schedule_mould_name, $incremental_schedule_mould_name_array)){
                    if(isset($_POST['incremental_remote_retain']) && !empty($_POST['incremental_remote_retain'])){
                        $incremental_remote_retain = intval($_POST['incremental_remote_retain']);
                        $this->set_global_incremental_remote_retain_count($incremental_schedule_mould_name, $incremental_remote_retain);
                    }
                    if(isset($schedule['custom']['files'])) {
                        $this->set_global_incremental_file_settings($incremental_schedule_mould_name, $schedule['custom']['files']);
                    }
                    if(isset($schedule['custom']['db'])){
                        $this->set_global_incremental_db_settings($incremental_schedule_mould_name, $schedule['custom']['db']);
                    }
                    $this->set_global_incremental_schedules($incremental_schedule_mould_name, $schedule);
                    $incremental_schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('incremental_backup_setting', array());
                    if(empty($incremental_schedule_mould)){
                        $incremental_schedule_mould = array();
                    }
                    $table = new Mainwp_WPvivid_Incremental_Schedule_Mould_List();
                    $table->set_schedule_mould_list($incremental_schedule_mould);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                    $ret['html'] = $html;
                    $success_msg = 'You have successfully added a schedule.';
                    $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', true, $success_msg);
                    $ret['result'] = 'success';
                }
                else{
                    $ret['result'] = 'failed';
                    $error_msg = 'The schedule mould name already existed.';
                    $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', false, $error_msg);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function update_global_incremental_backup_schedule(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['schedule']) && !empty($_POST['schedule']) && is_string($_POST['schedule']) &&
                isset($_POST['incremental_schedule_mould_name'])  && !empty($_POST['incremental_schedule_mould_name']) && is_string($_POST['incremental_schedule_mould_name'])){

                $json = stripslashes(sanitize_text_field($_POST['schedule']));
                $schedule = json_decode($json, true);
                $incremental_schedule_mould_name = sanitize_text_field($_POST['incremental_schedule_mould_name']);

                if(isset($_POST['incremental_remote_retain']) && !empty($_POST['incremental_remote_retain'])){
                    $incremental_remote_retain = intval($_POST['incremental_remote_retain']);
                    $this->set_global_incremental_remote_retain_count($incremental_schedule_mould_name, $incremental_remote_retain);
                }
                if(isset($schedule['custom']['files'])) {
                    $this->set_global_incremental_file_settings($incremental_schedule_mould_name, $schedule['custom']['files']);
                }
                if(isset($schedule['custom']['db'])){
                    $this->set_global_incremental_db_settings($incremental_schedule_mould_name, $schedule['custom']['db']);
                }
                $this->set_global_incremental_schedules($incremental_schedule_mould_name, $schedule);
                $incremental_schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('incremental_backup_setting', array());
                if(empty($incremental_schedule_mould)){
                    $incremental_schedule_mould = array();
                }
                $table = new Mainwp_WPvivid_Incremental_Schedule_Mould_List();
                $table->set_schedule_mould_list($incremental_schedule_mould);
                $table->prepare_items();
                ob_start();
                $table->display();
                $html = ob_get_clean();
                $ret['html'] = $html;
                $success_msg = 'You have successfully update the schedule.';
                $ret['notice'] = apply_filters('mwp_wpvivid_set_schedule_notice', true, $success_msg);
                $ret['result'] = 'success';

                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function edit_global_incremental_schedule_mould_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['mould_name']) && !empty($_POST['mould_name']) && is_string($_POST['mould_name'])){
                $mould_name = sanitize_text_field($_POST['mould_name']);
                $schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('incremental_backup_setting', array());
                $incremental_schedule = $schedule_mould[$mould_name];
                $incremental_schedule_id = '';
                foreach ($incremental_schedule['incremental_schedules'] as $key => $value){
                    $incremental_schedule_id = $key;
                }
                $ret['incremental_remote_retain'] = $incremental_schedule['incremental_remote_backup_count'];


                if(isset($incremental_schedule['incremental_schedules'][$incremental_schedule_id]['backup_files']['exclude_uploads_files']) && !empty($incremental_schedule['incremental_schedules'][$incremental_schedule_id]['backup_files']['exclude_uploads_files'])){
                    $tmp_upload = $incremental_schedule['incremental_schedules'][$incremental_schedule_id]['backup_files']['exclude_uploads_files'];
                    $tmp_upload = str_replace('.*\.', '', $tmp_upload);
                    $tmp_upload = str_replace('$', '', $tmp_upload);
                    $tmp_upload = implode(",", $tmp_upload);
                    $incremental_schedule['incremental_schedules'][$incremental_schedule_id]['backup_files']['exclude_uploads_files'] = $tmp_upload;
                }
                if(isset($incremental_schedule['incremental_schedules'][$incremental_schedule_id]['backup_files']['exclude_content_files']) && !empty($incremental_schedule['incremental_schedules'][$incremental_schedule_id]['backup_files']['exclude_content_files'])){
                    $tmp_content = $incremental_schedule['incremental_schedules'][$incremental_schedule_id]['backup_files']['exclude_content_files'];
                    $tmp_content = str_replace('.*\.', '', $tmp_content);
                    $tmp_content = str_replace('$', '', $tmp_content);
                    $tmp_content = implode(",", $tmp_content);
                    $incremental_schedule['incremental_schedules'][$incremental_schedule_id]['backup_files']['exclude_content_files'] = $tmp_content;
                }
                
                $ret['incremental_schedule'] = $incremental_schedule['incremental_schedules'][$incremental_schedule_id];
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_global_incremental_schedule_mould_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['mould_name']) && !empty($_POST['mould_name']) && is_string($_POST['mould_name'])){
                $mould_name = sanitize_text_field($_POST['mould_name']);
                $schedule_mould = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('incremental_backup_setting', array());
                if(isset($schedule_mould[$mould_name])){
                    unset($schedule_mould[$mould_name]);
                }
                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('incremental_backup_setting', $schedule_mould);

                $table = new Mainwp_WPvivid_Incremental_Schedule_Mould_List();
                $table->set_schedule_mould_list($schedule_mould);
                $table->prepare_items();
                ob_start();
                $table->display();
                $html = ob_get_clean();
                $ret['html'] = $html;
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function set_incremental_backup_schedule(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['schedule']) && !empty($_POST['schedule']) && is_string($_POST['schedule']) &&
                isset($_POST['start'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $json = stripslashes(sanitize_text_field($_POST['schedule']));
                if(isset($_POST['incremental_remote_retain']) && !empty($_POST['incremental_remote_retain'])){
                    $incremental_remote_retain = intval($_POST['incremental_remote_retain']);
                    $post_data['incremental_remote_retain'] = $incremental_remote_retain;
                    $this->set_incremental_remote_retain_count($site_id, $incremental_remote_retain);
                }
                $post_data['mwp_action'] = 'wpvivid_set_incremental_backup_schedule_mainwp';
                $post_data['schedule'] = $json;
                $post_data['start'] = sanitize_text_field($_POST['start']);
                if(isset($post_data['start'])&&$post_data['start']){
                    $this->set_incremental_enable($site_id, true);
                }
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $table=new Mainwp_WPvivid_Schedule_List();
                    $table->set_schedule_list($information['schedule_info']);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                    $ret['html'] = $html;
                    $ret['data'] = $information['data'];
                    $ret['notice'] = $information['notice'];
                    $schedule = json_decode($json, true);
                    if(isset($schedule['custom']['files'])){
                        $this->set_incremental_file_settings($site_id, $schedule['custom']['files']);
                    }
                    if(isset($schedule['custom']['db'])){
                        $this->set_incremental_db_setting($site_id, $schedule['custom']['db']);
                    }
                    $this->set_incremental_schedules($site_id, $information['incremental_schedules']);
                    $this->set_incremental_backup_data($site_id, $information['incremental_backup_data']);
                    $this->set_incremental_output_msg($site_id, $information['incremental_output_msg']);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function update_incremental_backup_exclude_extension_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['type']) && !empty($_POST['type']) && is_string($_POST['type']) &&
                isset($_POST['exclude_content']) && !empty($_POST['exclude_content']) && is_string($_POST['exclude_content'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $type = sanitize_text_field($_POST['type']);
                $exclude_content = sanitize_text_field($_POST['exclude_content']);
                //$this->mwp_wpvivid_update_backup_exclude_extension_rule($site_id, $type, $exclude_content);
                $post_data['mwp_action'] = 'wpvivid_update_incremental_backup_exclude_extension_addon_mainwp';
                $post_data['type'] = $type;
                $post_data['exclude_content'] = $exclude_content;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function incremental_connect_additional_database_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['database_info']) && !empty($_POST['database_info']) && is_string($_POST['database_info'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $data = sanitize_text_field($_POST['database_info']);
                $data = stripslashes($data);
                $json = json_decode($data, true);
                $post_data['mwp_action'] = 'wpvivid_incremental_connect_additional_database_addon_mainwp';
                $post_data['db_user'] = sanitize_text_field($json['db_user']);
                $post_data['db_pass'] = sanitize_text_field($json['db_pass']);
                $post_data['db_host'] = sanitize_text_field($json['db_host']);
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['html'] = Mainwp_WPvivid_Extension_Subpage::output_additional_database_table($information['database_array']);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function incremental_add_additional_database_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['database_info']) && !empty($_POST['database_info']) && is_string($_POST['database_info'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $data = sanitize_text_field($_POST['database_info']);
                $data = stripslashes($data);
                $json = json_decode($data, true);
                $post_data['mwp_action'] = 'wpvivid_incremental_add_additional_database_addon_mainwp';
                $post_data['db_user'] = $json['db_user'];
                $post_data['db_pass'] = $json['db_pass'];
                $post_data['db_host'] = $json['db_host'];
                $post_data['additional_database_list'] = $json['additional_database_list'];
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['html'] = Mainwp_WPvivid_Extension_Subpage::output_additional_database_list($information['data']);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function incremental_remove_additional_database_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['database_name']) && !empty($_POST['database_name']) && is_string($_POST['database_name'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $database_name = sanitize_text_field($_POST['database_name']);
                $post_data['mwp_action'] = 'wpvivid_incremental_remove_additional_database_addon_mainwp';
                $post_data['database_name'] = $database_name;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $ret['html'] = Mainwp_WPvivid_Extension_Subpage::output_additional_database_list($information['data']);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function achieve_incremental_child_path_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['remote_id']) && !empty($_POST['remote_id']) && is_string($_POST['remote_id']) &&
                isset($_POST['incremental_path']) && !empty($_POST['incremental_path']) && is_string($_POST['incremental_path'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $remote_id = sanitize_text_field($_POST['remote_id']);
                $incremental_path = sanitize_text_field($_POST['incremental_path']);

                $post_data['mwp_action'] = 'wpvivid_achieve_incremental_child_path_addon_mainwp';
                $post_data['remote_id'] = $remote_id;
                $post_data['incremental_path'] = $incremental_path;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                    $table = new Mainwp_WPvivid_Backup_List();
                    $table->set_backup_list($information['list_data']);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['html'] = ob_get_clean();
                    //$ret['html'] = Mainwp_WPvivid_Extension_Subpage::output_additional_database_list($information['data']);
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function archieve_incremental_remote_folder_list_addon(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['remote_id']) && !empty($_POST['remote_id']) && is_string($_POST['remote_id']) &&
                isset($_POST['folder']) && !empty($_POST['folder']) && is_string($_POST['folder']) &&
                isset($_POST['page'])){
                $site_id = sanitize_text_field($_POST['site_id']);
                $remote_id = sanitize_text_field($_POST['remote_id']);
                $folder  = sanitize_text_field($_POST['folder']);
                $page    = sanitize_text_field($_POST['page']);

                $post_data['mwp_action'] = 'wpvivid_archieve_incremental_remote_folder_list_addon_mainwp';
                $post_data['remote_id'] = $remote_id;
                $post_data['folder'] = $folder;
                $post_data['page'] = $page;
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);
                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $table = new Mainwp_WPvivid_Incremental_List();
                    $table->set_incremental_list($information['incremental_list'], $page);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['incremental_list'] = ob_get_clean();
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function mwp_check_white_label_option($data)
    {
        $ret['result']='failed';
        if(!isset($data['white_label_display']))
        {
            $ret['error']=__('The white label is required.', 'wpvivid');
            return $ret;
        }
        $data['white_label_display']=sanitize_text_field($data['white_label_display']);
        if(empty($data['white_label_display']))
        {
            $ret['error']=__('The white label is required.', 'wpvivid');
            return $ret;
        }

        if(!isset($data['white_label_slug']))
        {
            $ret['error']=__('The slug is required.', 'wpvivid');
            return $ret;
        }
        $data['white_label_slug']=sanitize_text_field($data['white_label_slug']);
        if(empty($data['white_label_slug']))
        {
            $ret['error']=__('The slug is required.', 'wpvivid');
            return $ret;
        }

        if(!isset($data['white_label_support_email']))
        {
            $ret['error']=__('The support email is required.', 'wpvivid');
            return $ret;
        }
        $data['white_label_support_email']=sanitize_text_field($data['white_label_support_email']);
        if(empty($data['white_label_support_email']))
        {
            $ret['error']=__('The support email is required.', 'wpvivid');
            return $ret;
        }

        if(!isset($data['white_label_website']))
        {
            $ret['error']=__('The website is required.', 'wpvivid');
            return $ret;
        }
        $data['white_label_website']=sanitize_text_field($data['white_label_website']);
        if(empty($data['white_label_website']))
        {
            $ret['error']=__('The website is required.', 'wpvivid');
            return $ret;
        }

        $ret['result']='success';
        return $ret;
    }

    public function set_white_label_setting()
    {
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['site_id']) && !empty($_POST['site_id']) && is_string($_POST['site_id']) &&
                isset($_POST['setting']) && !empty($_POST['setting']) && is_string($_POST['setting'])) {
                $site_id = sanitize_text_field($_POST['site_id']);
                $json_setting = $_POST['setting'];
                $json_setting = stripslashes($json_setting);
                $setting = json_decode($json_setting, true);
                if (is_null($setting))
                {
                    echo 'json decode failed';
                    die();
                }
                $ret = $this->mwp_check_white_label_option($setting);
                if($ret['result']!='success')
                {
                    echo json_encode($ret);
                    die();
                }

                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'white_label_setting', $setting);

                $post_data['mwp_action'] = 'wpvivid_set_white_label_setting_addon_mainwp';
                $post_data['setting'] = json_encode($setting);
                $information = apply_filters('mainwp_fetchurlauthed', $this->childFile, $this->childKey, $site_id, 'wpvivid_backuprestore', $post_data);

                if (isset($information['error'])) {
                    $ret['result'] = 'failed';
                    $ret['error'] = $information['error'];
                } else {
                    $ret['result'] = 'success';
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function global_set_white_label_setting(){
        $this->mwp_ajax_check_security();
        try{
            if(isset($_POST['setting']) && !empty($_POST['setting']) && is_string($_POST['setting'])) {
                $json_setting = $_POST['setting'];
                $json_setting = stripslashes($json_setting);
                $setting = json_decode($json_setting, true);
                if (is_null($setting))
                {
                    echo 'json decode failed';
                    die();
                }
                $ret = $this->mwp_check_white_label_option($setting);
                if($ret['result']!='success')
                {
                    echo json_encode($ret);
                    die();
                }

                Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_global_option('white_label_setting', $setting);
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function mwp_ajax_check_security($role='administrator')
    {
        if(!is_admin()||!current_user_can($role))
            die();
    }

    public function mwp_check_wpvivid_pro($plugins, $website_id){
        $check_pro = false;
        $is_pro=Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_get_single_option($website_id, 'is_pro', false);
        if($is_pro){
            $check_pro = true;
        }
        return $check_pro;
    }

    public function get_websites()
    {
        $websites = apply_filters( 'mainwp_getsites', $this->childFile, $this->childKey, null );
        $sites_ids = array();
        if ( is_array( $websites ) )
        {
            foreach ( $websites as $site ) {
                $sites_ids[] = $site['id'];
            }
            unset( $websites );
        }
        $option = array( 'plugin_upgrades' => true, 'plugins' => true );
        $selected_group=array();
        if ( isset( $_POST['mwp_wpvivid_plugin_groups_select'] ) && $_POST['mwp_wpvivid_plugin_groups_select']!=0)
        {
            $selected_group[] = intval(sanitize_text_field($_POST['mwp_wpvivid_plugin_groups_select']));
        }
        if(!empty($selected_group))
        {
            $sites_ids=array();
        }

        $dbwebsites = apply_filters( 'mainwp_getdbsites', $this->childFile, $this->childKey, $sites_ids, $selected_group, $option );
        $websites_with_plugin=array();
        foreach ( $dbwebsites as $website )
        {
            if ( $website )
            {
                $plugins = json_decode( $website->plugins, 1 );
                if ( is_array( $plugins ) && count( $plugins ) != 0 )
                {
                    $site = array('id' => $website->id, 'name' => $website->name, 'url' => $website->url);
                    $check_pro = $this->mwp_check_wpvivid_pro($plugins, $website->id);
                    if(!$check_pro) {
                        $site['pro'] = 0;
                        $site['install'] = 0;
                        $site['active'] = 0;
                        $site['login'] = 0;
                        $site['version'] = 'N/A';
                        $site['slug'] = 'wpvivid-backuprestore'; //wpvivid-backup-pro
                        $site['individual'] = 0;
                        $site['status'] = 'Not Install';
                        $site['class'] = 'need-install';

                        foreach ($plugins as $plugin) {
                            $reg_string = 'wpvivid-backuprestore/wpvivid-backuprestore.php';
                            if ((strcmp($plugin['slug'], $reg_string) === 0)) {
                                $site['pro'] = 0;
                                $site['install'] = 1;
                                $site['slug'] = $plugin['slug'];
                                $site['version'] = esc_html($plugin['version']).' (WPvivid Backup)';


                                $individual = Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_get_single_option($site['id'], 'individual', false);
                                if ($individual) {
                                    $site['individual'] = 1;
                                } else {
                                    $site['individual'] = 0;
                                }

                                if ($plugin['active']) {
                                    $site['active'] = 1;
                                    $plugin_upgrades = json_decode($website->plugin_upgrades, 1);
                                    if (is_array($plugin_upgrades) && count($plugin_upgrades) > 0) {
                                        if (isset($plugin_upgrades['wpvivid-backuprestore/wpvivid-backuprestore.php'])) {
                                            $upgrade = $plugin_upgrades['wpvivid-backuprestore/wpvivid-backuprestore.php'];
                                            if (isset($upgrade['update'])) {
                                                $site['upgrade'] = $upgrade['update'];
                                                $site['status'] = 'New version available';
                                                $site['class'] = 'need-update';
                                            }
                                            else{
                                                $site['status'] = 'Latest version';
                                                $site['class'] = '';
                                            }
                                        }
                                        else{
                                            $site['status'] = 'Latest version';
                                            $site['class'] = '';
                                        }
                                    }
                                    else{
                                        $site['status'] = 'Latest version';
                                        $site['class'] = '';
                                    }
                                } else {
                                    $site['active'] = 0;
                                    $site['status'] = 'Not Actived';
                                    $site['class'] = 'need-active';
                                }
                                //$site['report'] = Mainwp_WPvivid_Extension_Option::get_instance()->get_report_addon($site['id']);
                                //$site['sync_remote_setting'] = Mainwp_WPvivid_Extension_Option::get_instance()->get_sync_remote_setting($site['id']);
                                break;
                            }
                        }
                        if (isset($_GET['search']) && !empty($_GET['search'])) {
                            $find = trim(sanitize_text_field($_GET['search']));
                            if (stripos($site['name'], $find) !== false || stripos($site['url'], $find) !== false) {
                                $websites_with_plugin[$site['id']] = $site;
                            }
                        } else {
                            $websites_with_plugin[$site['id']] = $site;
                        }
                    }
                }
            }
        }

        return $websites_with_plugin;
    }

    public function mwp_get_child_websites(){
        $websites = apply_filters( 'mainwp_getsites', $this->childFile, $this->childKey, null );
        $sites_ids = array();
        if ( is_array( $websites ) ) {
            foreach ( $websites as $site ) {
                $sites_ids[] = $site['id'];
            }
            unset( $websites );
        }
        $option = array( 'plugin_upgrades' => true, 'plugins' => true );
        $selected_group=array();
        if ( isset( $_POST['mwp_wpvivid_plugin_groups_select'] ) && $_POST['mwp_wpvivid_plugin_groups_select']!=0) {
            $selected_group[] = intval(sanitize_text_field($_POST['mwp_wpvivid_plugin_groups_select']));
        }
        if(!empty($selected_group)) {
            $sites_ids=array();
        }

        $dbwebsites = apply_filters( 'mainwp_getdbsites', $this->childFile, $this->childKey, $sites_ids, $selected_group, $option );
        return $dbwebsites;
    }

    public function get_websites_ex()
    {
        $websites = apply_filters( 'mainwp_getsites', $this->childFile, $this->childKey, null );
        $sites_ids = array();
        if ( is_array( $websites ) ) {
            foreach ( $websites as $site ) {
                $sites_ids[] = $site['id'];
            }
            unset( $websites );
        }
        $option = array( 'plugin_upgrades' => true, 'plugins' => true );
        $selected_group=array();
        if ( isset( $_POST['mwp_wpvivid_plugin_groups_select'] ) && $_POST['mwp_wpvivid_plugin_groups_select']!=0) {
            $selected_group[] = intval(sanitize_text_field($_POST['mwp_wpvivid_plugin_groups_select']));
        }
        if(!empty($selected_group)) {
            $sites_ids=array();
        }

        $login_options = $this->get_global_login_addon();
        if($login_options !== false && isset($login_options['wpvivid_pro_login_cache'])){
            $addons_cache = $login_options['wpvivid_pro_login_cache'];
            if(isset($addons_cache['pro']['version'])){
                $latest_version = $addons_cache['pro']['version'];
            }
            else{
                $latest_version = false;
            }
        }
        else{
            $latest_version = false;
        }

        $dbwebsites = apply_filters( 'mainwp_getdbsites', $this->childFile, $this->childKey, $sites_ids, $selected_group, $option );
        $websites_with_plugin=array();
        foreach ( $dbwebsites as $website ){
            if ( $website )
            {
                $plugins = json_decode( $website->plugins, 1 );
                if ( is_array( $plugins ) && count( $plugins ) != 0 )
                {
                    $site = array('id' => $website->id, 'name' => $website->name, 'url' => $website->url);
                    $check_pro = $this->mwp_check_wpvivid_pro($plugins, $website->id);

                    $site['pro'] = 1;
                    $site['slug'] = 'wpvivid-backup-pro';
                    $site['version'] = 'N/A';
                    $site['individual'] = 0;
                    $site['install-wpvivid'] = 0;
                    $site['active-wpvivid'] = 0;
                    $site['install-wpvivid-pro'] = 0;
                    $site['active-wpvivid-pro'] = 0;
                    $site['login'] = 0;
                    $site['check-status'] = 0;
                    $site['status'] = 'WPvivid Backup Pro not claimed';
                    $site['class'] = 'need-install-wpvivid';
                    $site['class-update'] = '';
                    $wpvivid_need_update = false;

                    $wpvivid_status = false;
                    foreach ($plugins as $plugin){
                        $reg_string = 'wpvivid-backuprestore/wpvivid-backuprestore.php';
                        if ((strcmp($plugin['slug'], $reg_string) === 0)) {
                            $site['install-wpvivid'] = 1;
                            if ($plugin['active']) {
                                $site['active-wpvivid'] = 1;

                                $plugin_upgrades = json_decode($website->plugin_upgrades, 1);
                                if (is_array($plugin_upgrades) && count($plugin_upgrades) > 0) {
                                    if (isset($plugin_upgrades['wpvivid-backuprestore/wpvivid-backuprestore.php'])) {
                                        $upgrade = $plugin_upgrades['wpvivid-backuprestore/wpvivid-backuprestore.php'];
                                        if (isset($upgrade['update'])) {
                                            $site['status'] = 'New version available';
                                            $site['class-update'] = 'need-update-wpvivid';
                                            $wpvivid_need_update = true;
                                        }
                                    }
                                }

                                $wpvivid_status = true;
                            } else {
                                $site['active-wpvivid'] = 0;
                                $site['status'] = 'WPvivid Backup Pro not claimed';
                                $site['class'] = 'need-active-wpvivid';
                            }
                            break;
                        }
                    }

                    if($wpvivid_status){
                        $site['status'] = 'WPvivid Backup Pro not claimed';
                        $site['class'] = 'need-install-wpvivid-pro';
                        foreach ($plugins as $plugin) {
                            $reg_string = 'wpvivid-backup-pro/wpvivid-backup-pro.php';
                            if ((strcmp($plugin['slug'], $reg_string) === 0)) {
                                $site['install-wpvivid-pro'] = 1;
                                $site['slug'] = $plugin['slug'];

                                $individual = Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_get_single_option($site['id'], 'individual', false);
                                if ($individual) {
                                    $site['individual'] = 1;
                                } else {
                                    $site['individual'] = 0;
                                }

                                if ($plugin['active']) {
                                    $site['active-wpvivid-pro'] = 1;

                                    $wpvivid_pro_need_update_pro = false;
                                    if($latest_version !== false){
                                        if(version_compare($latest_version, $plugin['version'],'>')){
                                            $is_login_pro = $this->get_is_login($site['id']);
                                            if($is_login_pro !== false){
                                                if(intval($is_login_pro) !== 1){
                                                    $wpvivid_pro_need_update_pro = true;
                                                    $site['status'] = 'WPvivid Backup Pro not claimed';
                                                    $site['class'] = 'need-install-wpvivid-pro';
                                                }
                                            }
                                            else{
                                                $wpvivid_pro_need_update_pro = true;
                                                $site['status'] = 'WPvivid Backup Pro not claimed';
                                                $site['class'] = 'need-install-wpvivid-pro';
                                            }
                                        }
                                    }

                                    if(!$wpvivid_pro_need_update_pro){
                                        $is_login_pro = $this->get_is_login($site['id']);
                                        if($is_login_pro !== false){
                                            if(intval($is_login_pro) === 1){
                                                $site['login'] = 1;
                                                $need_update = $this->get_need_update($site['id']);
                                                if($need_update == '1'){
                                                    $site['status'] = 'New version available';
                                                    $site['class-update'] = 'need-update-wpvivid-pro';
                                                    $site['class'] = '';
                                                }
                                                else{
                                                    if(!$wpvivid_need_update) {
                                                        $site['status'] = 'Latest version';
                                                        $site['class'] = '';
                                                        $site['check-status'] = 1;
                                                    }
                                                    else{
                                                        $site['status'] = 'New version available';
                                                        $site['class'] = '';
                                                        $site['class-update'] = 'need-update-wpvivid';
                                                    }
                                                }
                                                $site['version'] = $this->get_current_version($site['id']);
                                                $site['version'] = $site['version'].' (WPvivid Backup Pro)';
                                            }
                                            else{
                                                $site['login'] = 0;
                                                $site['status'] = 'WPvivid Backup Pro not claimed';
                                                $site['class'] = 'need-login';
                                            }
                                        }
                                        else{
                                            $site['status'] = 'WPvivid Backup Pro not claimed';
                                            $site['class'] = 'need-login';
                                        }
                                    }
                                } else {
                                    $site['active-wpvivid-pro'] = 0;
                                    $site['status'] = 'WPvivid Backup Pro not claimed';
                                    $site['class'] = 'need-active-wpvivid-pro';
                                }

                                break;
                            }
                        }
                    }
                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                        $find = trim(sanitize_text_field($_GET['search']));
                        if (stripos($site['name'], $find) !== false || stripos($site['url'], $find) !== false) {
                            $websites_with_plugin[$site['id']] = $site;
                        }
                    } else {
                        $websites_with_plugin[$site['id']] = $site;
                    }
                }
            }
        }
        return $websites_with_plugin;
    }

    public function render_sync_websites_page($submit_id, $check_addon = false, $schedule_mould_name = '')
    {
        global $mainwp_wpvivid_extension_activator;

        if(intval($check_addon) === 1){
            $websites_with_plugin=$mainwp_wpvivid_extension_activator->get_websites_ex();
        }
        else{
            $websites_with_plugin=$mainwp_wpvivid_extension_activator->get_websites();
        }

        ?>
        <div style="padding: 10px;">
            <h2 style="margin-top: 10px;">Saving settings to child sites ...</h2><br>
            <?php
            if($submit_id === 'mwp_wpvivid_sync_schedule' && intval($check_addon) === 1){
                ?>
                <div class="mwp-wpvivid-block-bottom-space">
                    <span>Schedule Name:</span><span class="mwp_wpvivid_schedule_mould_name"><?php echo $schedule_mould_name; ?></span>
                </div>
                <div class="mwp-wpvivid-block-bottom-space">
                    <div>
                        <label>
                            <input type="radio" name="mwp_wpvivid_default_schedule" value="default_only" checked />
                            <span>Set as the only active schedule (This will disable and replace existing schedules on the child sites)</span>
                        </label>
                    </div>
                    <div>
                        <label>
                            <input type="radio" name="mwp_wpvivid_default_schedule" value="default_append" />
                            <span>Set as an additional active schedule (This will add the new schedule to the child sites and will not disable existing schedules)</span>
                        </label>
                    </div>
                </div>
                <?php
            }
            else if($submit_id === 'mwp_wpvivid_sync_incremental_schedule' && intval($check_addon) === 1){
                ?>
                <div class="mwp-wpvivid-block-bottom-space">
                    <span>Schedule Name:</span><span class="mwp_wpvivid_schedule_mould_name"><?php echo $schedule_mould_name; ?></span>
                </div>
                <?php
            }
            ?>
            <table class="ui single line table">
                <thead>
                <tr>
                    <th class="no-sort collapsing check-column"><span class="ui checkbox"><input type="checkbox" checked /></span></th>
                    <th><?php _e( 'Site' ); ?></th>
                    <th><?php _e( 'URL' ); ?></th>
                    <th><?php _e( 'Status' ); ?></th>
                </tr>
                </thead>
                <tbody class="list:sites">
                <?php
                if ( is_array( $websites_with_plugin ) && count( $websites_with_plugin ) > 0 )
                {
                    foreach ( $websites_with_plugin as $website )
                    {
                        $website_id = $website['id'];


                        if(intval($check_addon) !== intval($website['pro']))
                        {
                            continue;
                        }

                        if(intval($check_addon) === 1)
                        {
                            if(!$website['check-status'])
                            {
                                continue;
                            }
                        }
                        else {
                            if(!$website['install'])
                            {
                                continue;
                            }

                            if(!$website['active'])
                            {
                                continue;
                            }
                        }

                        if($website['individual'])
                        {
                            continue;
                        }

                        ?>
                        <tr class="mwp-wpvivid-sync-row">
                            <td class="check-column" website-id="<?php esc_attr_e($website_id); ?>"><span class="ui checkbox"><input type="checkbox" name="checked[]" checked /></span></td>
                            <td>
                                <a href="admin.php?page=managesites&dashboard=<?php esc_attr_e($website_id); ?>"><?php _e(stripslashes($website['name'])); ?></a><br/>
                            </td>
                            <td>
                                <a href="<?php esc_attr_e($website['url']); ?>" target="_blank"><?php _e($website['url']); ?></a><br/>
                            </td>
                            <td class="mwp-wpvivid-progress" website-id="<?php esc_attr_e($website_id); ?>">
                                <span>Ready to update</span>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    _e( '<tr><td colspan="9">No websites were found with the WPvivid Backup plugin installed.</td></tr>' );
                }
                ?>
                </tbody>
                <tfoot>
                <tr>
                    <th class="row-title" colspan="4"><input class="ui green mini button"
                                                             id="<?php esc_attr_e($submit_id) ?>" type="button"
                                                             value="<?php esc_attr_e('Start Syncing Changes', 'mainwp-wpvivid-extension'); ?>"/></th>
                </tr>
                </tfoot>
            </table>
        </div>
        <?php
    }

    public function render_sync_websites_remote_page($submit_id, $check_addon = false){
        global $mainwp_wpvivid_extension_activator;

        $websites_with_plugin=$mainwp_wpvivid_extension_activator->get_websites_ex();

        ?>
        <div style="padding: 10px;">
            <h2 style="margin-top: 10px;">Saving settings to child sites ...</h2><br>
            <table class="ui single line table">
                <thead>
                <tr>
                    <th class="no-sort collapsing check-column"><span class="ui checkbox"><input type="checkbox" checked /></span></th>
                    <th><?php _e( 'Site' ); ?></th>
                    <th><?php _e( 'URL' ); ?></th>
                    <th><?php _e(' Custom Path' ); ?></th>
                    <th><?php _e( 'Status' ); ?></th>
                </tr>
                </thead>
                <tbody class="list:sites" id="mwp_wpvivid_sync_remote_list">
                <?php
                if ( is_array( $websites_with_plugin ) && count( $websites_with_plugin ) > 0 )
                {
                    foreach ( $websites_with_plugin as $website )
                    {
                        $website_id = $website['id'];
                        if(!$website['install'])
                        {
                            continue;
                        }

                        if(!$website['active'])
                        {
                            continue;
                        }

                        if(intval($check_addon) !== intval($website['pro']))
                        {
                            continue;
                        }

                        if(intval($check_addon) === 1)
                        {
                            if(!$website['login'])
                            {
                                continue;
                            }
                        }

                        if($website['individual'])
                        {
                            continue;
                        }

                        ?>
                        <tr class="mwp-wpvivid-sync-row">
                            <td class="check-column" website-id="<?php esc_attr_e($website_id); ?>"><span class="ui checkbox"><input type="checkbox" name="checked[]" checked /></span></td>
                            <td>
                                <a href="admin.php?page=managesites&dashboard=<?php esc_attr_e($website_id); ?>"><?php _e(stripslashes($website['name'])); ?></a><br/>
                            </td>
                            <td>
                                <a href="<?php esc_attr_e($website['url']); ?>" target="_blank"><?php _e($website['url']); ?></a><br/>
                            </td>
                            <td>
                                <span>Domain</span>
                                <input class="ui green mini button remote-path-edit" type="button" value="<?php esc_attr_e('Edit', 'mainwp-wpvivid-extension'); ?>" />
                            </td>
                            <td class="mwp-wpvivid-progress" website-id="<?php esc_attr_e($website_id); ?>">
                                <span>Ready to update</span>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    _e( '<tr><td colspan="9">No websites were found with the WPvivid Backup plugin installed.</td></tr>' );
                }
                ?>
                </tbody>
                <tfoot>
                <tr>
                    <th class="row-title" colspan="5"><input class="ui green mini button"
                                                             id="<?php esc_attr_e($submit_id) ?>" type="button"
                                                             value="<?php esc_attr_e('Start Syncing Changes', 'mainwp-wpvivid-extension'); ?>"/></th>
                </tr>
                </tfoot>
            </table>
        </div>
        <?php
    }

    public function render_check_report_page($website_id, $pro, $website_name){
        $report = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($website_id, 'report_addon', array());
        ?>
        <div style="padding: 10px;">
            <div class="mwp-wpvivid-block-bottom-space">Note: The list below includes the last 10 backup information.</div>
            <div class="mwp-wpvivid-block-bottom-space"><span>Site Title: </span><span><?php _e($website_name); ?></span></div>
            <table class="widefat mwp-wpvivid-block-bottom-space">
                <thead>
                    <th>Backup Time</th>
                    <th>Status</th>
                </thead>
                <tbody>
                <?php
                if(isset($report) && !empty($report)) {
                    usort($report, function($a, $b){
                        if($a['backup_time'] === $b['backup_time']){
                            return 0;
                        }
                        else if($a['backup_time'] > $b['backup_time']){
                            return -1;
                        }
                        else{
                            return 1;
                        }
                    });
                    foreach ($report as $task_id => $report_option) {
                        ?>
                        <tr>
                            <td><?php _e(date("H:i:s - m/d/Y", $report_option['backup_time'])); ?></td>
                            <td><?php _e($report_option['status']); ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <div>
                <a href="admin.php?page=Extensions-Wpvivid-Backup-Mainwp&tab=dashboard" class="ui green mini button">Return to WPvivid Backup Dashboard</a>
            </div>
        </div>
        <?php
    }
}

global $mainwp_wpvivid_extension_activator;
$mainwp_wpvivid_extension_activator = new Mainwp_WPvivid_Extension_Activator();