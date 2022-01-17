<?php

class Mainwp_WPvivid_Extension_DashboardPage
{
    private $select_pro;

    public function __construct($select_pro=0)
    {
        $this->select_pro=$select_pro;
    }

    public function render()
    {
        global $mainwp_wpvivid_extension_activator;
        ?>
        <div style="padding: 10px;">
            <?php
            $check_login_status = true;
            if($this->select_pro){
                $select_pro_check = 'checked';
                $login_options = $mainwp_wpvivid_extension_activator->get_global_login_addon();
                if($login_options === false || !isset($login_options['wpvivid_pro_account'])){
                    $check_login_status = false;
                }
            }
            else{
                $select_pro_check = '';
            }
            ?>
            <div class="mwp-wpvivid-block-bottom-space" style="background: #fff;">
                <div class="postbox" style="padding: 10px; margin-bottom: 0;">
                    <div style="float: left; margin-top: 7px; margin-right: 25px;"><?php _e('Switch to WPvivid Backup Pro'); ?></div>
                    <div class="ui toggle checkbox mwp-wpvivid-pro-swtich" style="float: left; margin-top:4px; margin-right: 10px;">
                        <input type="checkbox" <?php esc_attr_e($select_pro_check); ?> />
                        <label for=""></label>
                    </div>
                    <div style="float: left;"><input class="ui green mini button" type="button" value="Save" onclick="mwp_wpvivid_switch_pro_setting();"/></div>
                    <div style="clear: both;"></div>
                </div>
            </div>
            <div style="clear: both;"></div>
            <?php
            if($check_login_status) {
                if(isset($login_options['wpvivid_pro_account']['email']) && isset($login_options['wpvivid_pro_account']['password'])){
                    $login_options = array();
                    $mainwp_wpvivid_extension_activator->set_global_login_addon($login_options);
                    ?>
                    <div class="notice notice-warning inline" style="margin: 0; padding-top: 10px; margin-bottom: 10px;"><p>Note: WPvivid Backup Pro 2.0 requires login with the father license. Please <a onclick="mwp_wpvivid_switch_login_page();" style="cursor: pointer;">log in with your father license</a>.</p>
                        <button type="button" class="notice-dismiss" onclick="mwp_click_dismiss_notice(this);">
                            <span class="screen-reader-text">Dismiss this notice.</span>
                        </button>
                    </div>
                    <?php
                }
                else{
                    if(isset($_REQUEST['sync']) && intval($_REQUEST['sync']) === 1){
                        ?>
                        <div class="notice notice-success inline dismiss" style="margin: 0; padding-top: 10px; margin-bottom: 10px;"><p>You have successfully logged in. Please click the Sync Dashboard with Child Sites button on the dashboard to sync the data to child sites.</a></p>
                            <button type="button" class="notice-dismiss" onclick="mwp_click_dismiss_notice(this);">
                                <span class="screen-reader-text">Dismiss this notice.</span>
                            </button>
                        </div>
                        <?php
                    }
                    ?>
                    <?php $this->gen_select_sites(); ?>
                    <div class="mwp-wpvivid-block-bottom-space"></div>
                    <?php $this->get_dashboard_tab(); ?>
                    <?php
                }
            }
            else{
                ?>
                <div class="notice notice-warning inline" style="margin: 0; padding-top: 10px; margin-bottom: 10px;"><p>Notice: Please <a onclick="mwp_wpvivid_switch_login_page();" style="cursor: pointer;">login to your WPvivid Backup Pro account</a> first.</p>
                    <button type="button" class="notice-dismiss" onclick="mwp_click_dismiss_notice(this);">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
                <?php
            }
            ?>
        </div>
        <script>
            function mwp_wpvivid_switch_login_page(){
                location.href='<?php echo apply_filters('wpvivid_get_admin_url', '') . 'admin.php?page=Extensions-Wpvivid-Backup-Mainwp&tab=login'; ?>';
            }

            function mwp_wpvivid_explanation_action() {
                if(jQuery('#mwp_wpvivid_explanation_action').is(":hidden")) {
                    jQuery('#mwp_wpvivid_explanation_action').show();
                }
                else{
                    jQuery('#mwp_wpvivid_explanation_action').hide();
                }
            }

            function mwp_wpvivid_switch_pro_setting(){
                if(jQuery('.mwp-wpvivid-pro-swtich').find('input:checkbox').prop('checked')){
                    var pro_setting = 1;
                }
                else{
                    var pro_setting = 0;
                }
                var ajax_data = {
                    'action': 'mwp_wpvivid_switch_pro_setting',
                    'pro_setting': pro_setting
                };
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            location.reload();
                        }
                        else {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err) {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = mwp_wpvivid_output_ajaxerror('changing base settings', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function mwp_wpvivid_refresh_dashboard_page()
            {
                location.href='<?php echo apply_filters('wpvivid_get_admin_url', '') . 'admin.php?page=Extensions-Wpvivid-Backup-Mainwp&tab=dashboard'; ?>';
            }
        </script>
        <?php
    }

    public function gen_select_sites()
    {
        ?>
        <div class="mainwp-actions-bar" style="border: 1px solid #dadada;">
            <div class="ui grid">
                <div class="ui two column row">
                    <div style="padding-left: 0;">
                        <div style="float: left;">
                            <select class="ui dropdown" id="mwp_wpvivid_plugin_action">
                                <?php
                                if($this->select_pro){
                                    ?>
                                    <option value="update-selected-ex"><?php _e( 'Update WPvivid Backup Free/Pro 2.0' ); ?></option>
                                    <option value="login-selected"><?php _e( 'Install & Claim WPvivid Backup Pro 2.0' ); ?></option>
                                    <?php
                                }
                                else{
                                    ?>
                                    <option value="update-selected"><?php _e( 'Update the selected plugins' ); ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                            <input type="button" value="<?php _e( 'Apply' ); ?>" class="ui basic button action" id="mwp_wpvivid_plugin_doaction_btn">
                        </div>
                        <?php
                        if($this->select_pro){
                            ?>
                            <div style="margin: 12px 0 0 10px; float: left;">
                                <a onclick="mwp_wpvivid_explanation_action();" style="cursor: pointer;">What are these options?</a>
                            </div>
                            <?php
                        }
                        ?>
                        <div style="clear: both;"></div>
                    </div>
                </div>
                <div id="mwp_wpvivid_explanation_action" style="display: none; margin-bottom: 10px; padding: 0 0 0 15px;">
                    <ul style="margin: 0;">
                        <li>Update WPvivid Backup Free/Pro 2.0 : This option allows you to update WPvivid Backup Plugin Free and Pro 2.0 to the latest versions on the selected child sites.</li>
                        <li>Install and Claim WPvivid Backup Pro 2.0 : This option allows you to install and claim WPvivid Backup Pro 2.0 on the selected child sites.</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    public function get_dashboard_tab(){
        global $mainwp_wpvivid_extension_activator;
        $selected_group=0;
        if ( isset( $_POST['mwp_wpvivid_plugin_groups_select'] ) ) {
            $selected_group = intval(sanitize_text_field($_POST['mwp_wpvivid_plugin_groups_select']));
        }

        $select_pro=$mainwp_wpvivid_extension_activator->get_global_select_pro();
        if($select_pro){
            $websites_with_plugin=$mainwp_wpvivid_extension_activator->get_websites_ex();
            ?>
            <table class="ui single line selectable stackable table" id="mwp_wpvivid_sites_table" style="width: 100%;">
                <thead>
                <tr>
                    <th id="cb" class="no-sort collapsing check-column"><div class="ui checkbox"><input id="cb-select-all-top" type="checkbox"></div></th>
                    <th><?php _e('Site'); ?></th>
                    <th class="no-sort collapsing"><i class="sign in icon"></i></th>
                    <th><?php _e('URL'); ?></th>
                    <th><?php _e('Last Backup'); ?></th>
                    <th><?php _e('Report'); ?></th>
                    <th><?php _e('Current Version'); ?></th>
                    <th><?php _e('Status'); ?></th>
                    <th><?php _e('Schedule & Cloud Storage'); ?></th>
                    <th><?php _e('Settings'); ?></th>
                    <th><?php _e('Backup Now'); ?></th>
                </tr>
                </thead>
                <tbody id="the-mwp-wpvivid-list">
                    <?php self::get_websites_row_ex($websites_with_plugin,$selected_group); ?>
                </tbody>
                <tfoot>
                <tr>
                    <th id="cb" class="no-sort collapsing check-column"><div class="ui checkbox"><input id="cb-select-all-bottom" type="checkbox"></div></th>
                    <th><?php _e('Site'); ?></th>
                    <th class="no-sort collapsing"><i class="sign in icon"></i></th>
                    <th><?php _e('URL'); ?></th>
                    <th><?php _e('Last Backup'); ?></th>
                    <th><?php _e('Report'); ?></th>
                    <th><?php _e('Current Version'); ?></th>
                    <th><?php _e('Status'); ?></th>
                    <th><?php _e('Schedule & Cloud Storage'); ?></th>
                    <th><?php _e('Settings'); ?></th>
                    <th><?php _e('Backup Now'); ?></th>
                </tr>
                </tfoot>
            </table>
            <?php
        }
        else{
            $websites_with_plugin=$mainwp_wpvivid_extension_activator->get_websites();
            $has_update = false;
            foreach ( $websites_with_plugin as $website ) {
                $website_id = $website['id'];
                $class_active = (isset($website['active']) && !empty($website['active'])) ? '' : 'negative';
                if ($website['pro']) {
                    $need_update = $mainwp_wpvivid_extension_activator->get_need_update($website_id);
                    $class_update = $need_update == '1' ? 'warning' : '';
                } else {
                    $class_update = (isset($website['upgrade'])) ? 'warning' : '';
                }
                $class_update = ( 'negative' == $class_active ) ? 'negative' : $class_update;
                if($class_update === 'warning'){
                    $has_update = true;
                }
            }
            if($has_update){
                ?>
                <div class="notice notice-warning is-dismissible inline" style="margin: 0; padding-top: 10px; margin-bottom: 10px;"><p>There are plugins available to update. Select the checkboxes of websites in list and click on Apply button to start updating.</p>
                    <button type="button" class="notice-dismiss" onclick="mwp_click_dismiss_notice(this);">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
                <?php
            }
            ?>

            <table class="ui single line table" id="mwp_wpvivid_sites_table">
                <thead>
                <tr>
                    <th class="no-sort collapsing check-column"><span class="ui checkbox"><input type="checkbox"></span></th>
                    <th><?php _e('Site'); ?></th>
                    <th class="no-sort collapsing"><i class="sign in icon"></i></th>
                    <th><?php _e('URL'); ?></th>
                    <th><?php _e('Report'); ?></th>
                    <th><?php _e('Current Version'); ?></th>
                    <th><?php _e('Status'); ?></th>
                    <th><?php _e('Settings'); ?></th>
                    <th><?php _e('Backup Now'); ?></th>
                </tr>
                </thead>
                <tbody id="the-mwp-wpvivid-list">
                <?php
                if ( is_array( $websites_with_plugin ) && count( $websites_with_plugin ) > 0 )
                {
                    self::get_websites_row($websites_with_plugin,$selected_group);
                }
                else {
                    _e( '<tr><td colspan="9">No websites were found with the WPvivid Backup plugin installed.</td></tr>' );
                }
                ?>
                </tbody>
                <tfoot>
                <tr>
                    <th class="no-sort collapsing check-column"><span class="ui checkbox"><input type="checkbox"></span></th>
                    <th><?php _e('Site'); ?></th>
                    <th class="no-sort collapsing"><i class="sign in icon"></i></th>
                    <th><?php _e('URL'); ?></th>
                    <th><?php _e('Report'); ?></th>
                    <th><?php _e('Current Version'); ?></th>
                    <th><?php _e('Status'); ?></th>
                    <th><?php _e('Settings'); ?></th>
                    <th><?php _e('Backup Now'); ?></th>
                </tr>
                </tfoot>
            </table>
            <?php
        }
        ?>

        <script>
            jQuery( '#mwp_wpvivid_sites_table' ).DataTable( {
                //"columnDefs": [ { "orderable": false, "targets": "no-sort" } ],
                //"order": [ [ 1, "asc" ] ],
                //
                //"stateSave":  true,
                "stateDuration": 0, // forever
                "scrollX": true,
                "pagingType": "full_numbers",
                "order": [],
                "columnDefs": [ { "targets": 'no-sort', "orderable": false } ],
                //
                "pageLength": 50,
                "language": { "emptyTable": "No websites were found with the WPvivid Backup plugin installed." },
                "drawCallback": function( settings ) {
                    jQuery( '#mwp_wpvivid_sites_table .ui.dropdown').dropdown();
                    jQuery('#mwp_wpvivid_sites_table .ui.checkbox').checkbox();
                },
            } );
        </script>
        <?php
    }

    static public function get_websites_row($websites,$selected_group=0)
    {
        $plugin_name = 'WPvivid Backup';
        foreach ( $websites as $website )
        {
            $website_id = $website['id'];
            if($website['individual']) {
                $individual='Individual';
            }
            else {
                $individual='General';
            }
            $latest_version = (isset($website['upgrade']['new_version'])) ? $website['upgrade']['new_version'] : $website['version'];
            $plugin_slug = ( isset( $website['slug'] ) ) ? $website['slug'] : '';

            $class_install = '';
            $class_active = '';
            $class_update = '';
            if($website['class'] === 'need-install'){
                $class_install = 'negative need-install';
            }
            else if($website['class'] === 'need-active'){
                $class_active = 'negative need-active';
            }
            else if($website['class'] === 'need-update'){
                $class_update = 'warning need-update';
            }
            ?>
            <tr class="<?php esc_attr_e($class_install.' '.$class_active.' '.$class_update); ?>" website-id="<?php esc_attr_e($website_id); ?>" plugin-name="<?php esc_attr_e($plugin_name); ?>" plugin-slug="<?php esc_attr_e($plugin_slug); ?>" is-pro="<?php esc_attr_e($website['pro']); ?>" version="<?php esc_attr_e(isset($website['version']) ? $website['version'] : ''); ?>" latest-version="<?php esc_attr_e($latest_version); ?>">
                <td class="check-column"><span class="ui checkbox"><input type="checkbox" name="checked[]"></span></td>
                <td class="website-name"><a href="admin.php?page=managesites&dashboard=<?php esc_attr_e($website_id); ?>"><?php _e(stripslashes( $website['name'] )); ?></a></td>
                <td><a href="admin.php?page=SiteOpen&newWindow=yes&websiteid=<?php esc_attr_e($website_id); ?>" target="_blank"><i class="sign in icon"></i></a></td>
                <td><a href="<?php esc_attr_e($website['url']); ?>" target="_blank"><?php _e($website['url']); ?></a></td>
                <td><a onclick="mwp_wpvivid_check_report('<?php esc_attr_e($website['id']); ?>', '<?php esc_attr_e($website['pro']); ?>', '<?php esc_attr_e($website['name']) ?>');" style="cursor: pointer;">Report</a></td>
                <td><span class="updating"></span><span class="mwp-wpvivid-current-version"><?php _e($website['version']); ?></span></td>
                <td><span class="install-login-status"></span><span class="mwp-wpvivid-status"><?php _e($website['status']); ?></span></td>
                <td><span><?php _e($individual); ?></span></td>
                <td><span><a href="admin.php?page=ManageSitesWPvivid&id=<?php esc_attr_e($website_id); ?>"><i class="fa fa-hdd-o"></i> <?php _e( 'Backup Now', 'mainwp-wpvivid-extension' ); ?></a></span></td>
            </tr>
            <?php
        }
        ?>
        <script>
            function mwp_wpvivid_check_report(website_id, is_pro, website_name){
                window.location.href = window.location.href + "&check_report=1&website_id="+website_id+"&pro="+is_pro+"&website_name="+website_name;
            }
        </script>
        <?php
    }

    static public function get_websites_row_ex($websites,$selected_group=0)
    {
        global $mainwp_wpvivid_extension_activator;
        $plugin_name = 'WPvivid Backup Pro';
        foreach ( $websites as $website )
        {
            $website_id = $website['id'];

            $last_backup = 'Never';
            $report = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($website_id, 'report_addon', array());
            if(isset($report) && !empty($report)){
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
                    if($report_option['status'] === 'Succeeded') {
                        //$last_backup = date("H:i:s - m/d/Y", $report_option['backup_time']);
                        $last_backup = date("F d, Y H:i", $report_option['backup_time']);
                        break;
                    }
                }
            }
            else{
                $last_backup = 'Never';
            }

            if($website['individual']) {
                $individual='Individual';
            }
            else {
                $individual='General';
            }

            $schedule_addon = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($website_id, 'schedule_addon', array());
            if(isset($schedule_addon) && !empty($schedule_addon)){
                $schedule_css = 'dashicons dashicons-calendar-alt mwp-wpvivid-dashicons-green';
            }
            else{
                $schedule_css = 'dashicons dashicons-calendar-alt mwp-wpvivid-dashicons-grey';
            }
            $remote = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($website_id, 'remote', array());
            if(isset($remote) && !empty($remote)){
                $remote_css = 'dashicons dashicons-admin-site-alt3 mwp-wpvivid-dashicons-grey';
                foreach($remote['upload'] as $key => $value){
                    if($key === 'remote_selected') {
                        continue;
                    }
                    else{
                        $remote_css = 'dashicons dashicons-admin-site-alt3 mwp-wpvivid-dashicons-green';
                    }
                }
            }
            else{
                $remote_css = 'dashicons dashicons-admin-site-alt3 mwp-wpvivid-dashicons-grey';
            }

            $plugin_slug = ( isset( $website['slug'] ) ) ? $website['slug'] : '';
            $latest_version = $mainwp_wpvivid_extension_activator->get_latest_version($website_id);
            if($latest_version == ''){
                $latest_version = $mainwp_wpvivid_extension_activator->get_current_version($website_id);
            }
            $class_install = '';
            $class_login = '';
            $class_active = '';
            $class_update = '';
            $check_login_status = true;
            if($website['class'] === 'need-install-wpvivid'){
                $class_install = 'negative need-claim need-install-wpvivid';
                $check_login_status = false;
            }
            else if($website['class'] === 'need-active-wpvivid'){
                $class_active = 'negative need-claim need-active-wpvivid';
                $check_login_status = false;
            }
            else if($website['class'] === 'need-install-wpvivid-pro'){
                $class_install = 'negative need-claim need-install-wpvivid-pro';
                $check_login_status = false;
            }
            else if($website['class'] === 'need-active-wpvivid-pro'){
                $class_active = 'negative need-claim need-active-wpvivid-pro';
                $check_login_status = false;
            }
            else if($website['class'] === 'need-login'){
                $class_login = 'negative need-claim need-login';
                $check_login_status = false;
            }
            if($check_login_status) {
                if ($website['class-update'] === 'need-update-wpvivid') {
                    $class_update = 'warning need-update';
                } else if ($website['class-update'] === 'need-update-wpvivid-pro') {
                    $class_update = 'warning need-update';
                }
            }

            ?>
            <tr class="<?php esc_attr_e($class_install.' '.$class_login.' '.$class_active.' '.$class_update); ?>" website-id="<?php esc_attr_e($website_id); ?>" plugin-name="<?php esc_attr_e($plugin_name); ?>" plugin-slug="<?php esc_attr_e($plugin_slug); ?>" is-pro="<?php esc_attr_e($website['pro']); ?>" version="<?php esc_attr_e(isset($website['version']) ? $website['version'] : ''); ?>" latest-version="<?php esc_attr_e($latest_version); ?>">
                <td class="check-column"><span class="ui checkbox"><input type="checkbox" name="checked[]"></span></td>
                <td class="website-name"><a href="admin.php?page=managesites&dashboard=<?php esc_attr_e($website_id); ?>"><?php _e(stripslashes( $website['name'] )); ?></a></td>
                <td><a href="admin.php?page=SiteOpen&newWindow=yes&websiteid=<?php esc_attr_e($website_id); ?>" target="_blank"><i class="sign in icon"></i></a></td>
                <td><a href="<?php esc_attr_e($website['url']); ?>" target="_blank"><?php _e($website['url']); ?></a></td>
                <td><span><?php _e($last_backup); ?></span></td>
                <td><a onclick="mwp_wpvivid_check_report('<?php esc_attr_e($website['id']); ?>', '<?php esc_attr_e($website['pro']); ?>', '<?php esc_attr_e($website['name']) ?>');" style="cursor: pointer;">Report</a></td>
                <td><span class="updating"></span><span class="mwp-wpvivid-current-version"><?php _e($website['version']); ?></span></td>
                <td><span class="install-login-status"></span><span class="mwp-wpvivid-status"><?php _e($website['status']); ?></span></td>
                <td><span class="<?php esc_attr_e($schedule_css); ?>" style="margin-right: 10px;"></span><span class="<?php esc_attr_e($remote_css); ?>" style="margin-top: 2px;"></span></td>
                <td><span><?php _e($individual); ?></span></td>
                <td><span><a href="admin.php?page=ManageSitesWPvivid&id=<?php esc_attr_e($website_id); ?>"><i class="fa fa-hdd-o"></i> <?php _e( 'Backup Now', 'mainwp-wpvivid-extension' ); ?></a></span></td>
            </tr>
            <?php
        }
        ?>
        <script>
            function mwp_wpvivid_check_report(website_id, is_pro, website_name){
                window.location.href = window.location.href + "&check_report=1&website_id="+website_id+"&pro="+is_pro+"&website_name="+website_name;
            }
        </script>
        <?php
    }
}