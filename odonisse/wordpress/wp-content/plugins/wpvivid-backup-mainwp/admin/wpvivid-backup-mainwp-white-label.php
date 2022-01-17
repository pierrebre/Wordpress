<?php

class Mainwp_WPvivid_Extension_White_Label
{
    private $white_label_addon;
    private $site_id;

    public function __construct($white_label_addon = array())
    {
        $this->white_label_addon=$white_label_addon;
    }

    public function set_site_id($site_id)
    {
        $this->site_id=$site_id;
    }

    public function render($check_pro, $global=false)
    {
        if(isset($_GET['synchronize']) && isset($_GET['addon']))
        {
            $check_addon = sanitize_text_field($_GET['addon']);
            $this->mwp_wpvivid_synchronize_white_label($check_addon);
        }
        else{
            $white_label_setting = $this->white_label_addon;
            $white_label_display = empty($white_label_setting['white_label_display']) ? 'WPvivid Backup' : $white_label_setting['white_label_display'];
            $white_label_slug = empty($white_label_setting['white_label_slug']) ? 'WPvivid' : $white_label_setting['white_label_slug'];
            $white_label_support_email = empty($white_label_setting['white_label_support_email']) ? 'pro.support@wpvivid.com' : $white_label_setting['white_label_support_email'];
            $white_label_website_protocol = empty($white_label_setting['white_label_website_protocol']) ? 'https' : $white_label_setting['white_label_website_protocol'];
            $white_label_website = empty($white_label_setting['white_label_website']) ? 'wpvivid.com' : $white_label_setting['white_label_website'];
            if(isset($white_label_setting['white_label_hide_page'])){
                if($white_label_setting['white_label_hide_page']){
                    $is_hide_page = 'checked';
                }
                else{
                    $is_hide_page = '';
                }
            }
            else{
                $is_hide_page = '';
            }
            ?>
            <div style="margin: 10px;">
                <div>
                    <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                        <img src="<?php echo esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL.'/admin/images/settings.png'); ?>" style="width:50px;height:50px;">
                    </div>
                    <div class="mwp-wpvivid-block-bottom-space">
                        <div>This tab allows you to configure and sync WPvivid Pro white label settings to child sites.</div>
                    </div>
                    <div style="clear: both;"></div>
                </div>

                <div class="postbox mwp-wpvivid-setting-block mwp-wpvivid-block-bottom-space">
                    <div>
                        <div class="mwp-wpvivid-block-bottom-space"><strong><?php _e('Plugin Name', 'wpvivid'); ?></strong></div>
                        <div class="mwp-wpvivid-block-bottom-space">
                            <input type="text" placeholder="WPvivid" option="mwp_white_label_setting" name="white_label_display" class="all-options" value="<?php esc_attr_e($white_label_display); ?>" />
                        </div>
                        <div class="mwp-wpvivid-block-bottom-space"><?php echo sprintf(__('Enter your preferred plugin name to replace %s on the plugin UI and WP dashboard.', 'wpvivid'), $white_label_display); ?></div>

                        <div class="mwp-wpvivid-block-bottom-space"><strong><?php _e('Slug', 'wpvivid'); ?></strong></div>
                        <div class="mwp-wpvivid-block-bottom-space">
                            <input type="text" placeholder="WPvivid" option="mwp_white_label_setting" name="white_label_slug" class="all-options" value="<?php esc_attr_e($white_label_slug); ?>" onkeyup="value=value.replace(/[^a-zA-Z0-9]/g,'')" onpaste="value=value.replace(/[^\a-\z\A-\Z0-9]/g,'')" />
                        </div>
                        <div class="mwp-wpvivid-block-bottom-space"><?php echo sprintf(__('Enter your preferred slug to replace %s in all slugs, default storage directory paths, backup file names, default staging database names and table prefixes.', 'wpvivid'), $white_label_slug); ?></div>

                        <div class="mwp-wpvivid-block-bottom-space"><strong><?php _e('Support Email', 'wpvivid'); ?></strong></div>
                        <div class="mwp-wpvivid-block-bottom-space">
                            <input type="text" placeholder="pro.support@wpvivid.com" option="mwp_white_label_setting" name="white_label_support_email" class="all-options" value="<?php esc_attr_e($white_label_support_email); ?>" />
                        </div>
                        <div class="mwp-wpvivid-block-bottom-space"><?php echo sprintf(__('Enter your support email to replace %s in the plugin\'s Debug tab.', 'wpvivid'), $white_label_support_email); ?></div>

                        <div class="mwp-wpvivid-block-bottom-space"><strong><?php _e('Author URL', 'wpvivid'); ?></strong></div>
                        <div class="mwp-wpvivid-block-bottom-space">
                            <select option="mwp_white_label_setting" name="white_label_website_protocol" style="margin-bottom: 3px;">
                                <?php
                                if($white_label_website_protocol === 'http'){
                                    $http_protocol  = 'selected';
                                    $https_protocol = '';
                                }
                                else{
                                    $http_protocol  = '';
                                    $https_protocol = 'selected';
                                }
                                ?>
                                <option value="https" <?php esc_attr_e($https_protocol); ?>>https://</option>
                                <option value="http" <?php esc_attr_e($http_protocol); ?>>http://</option>
                            </select>
                            <input type="text" placeholder="pro.wpvivid.com" option="mwp_white_label_setting" name="white_label_website" class="all-options" value="<?php esc_attr_e($white_label_website); ?>" />
                        </div>
                        <div class="mwp-wpvivid-block-bottom-space"><?php echo sprintf(__('Enter your service URL to replace %s://%s in the plugin UI.', 'wpvivid'), $white_label_website_protocol, $white_label_website); ?></div>
                    </div>
                </div>

                <div>
                    <?php
                    if($global){
                        ?>
                        <input class="ui green mini button" id="mwp_wpvivid_global_white_label_save" type="button" value="<?php esc_attr_e( 'Save Changes and Sync', 'wpvivid' ); ?>" />
                        <?php
                    }
                    else{
                        ?>
                        <input class="ui green mini button" id="mwp_wpvivid_white_label_save" type="button" value="<?php esc_attr_e( 'Save Changes', 'wpvivid' ); ?>" />
                        <?php
                    }
                    ?>
                </div>
            </div>
            <script>
                jQuery('#mwp_wpvivid_global_white_label_save').on('click', function(){
                    var setting_data = mwp_wpvivid_ajax_data_transfer('mwp_white_label_setting');
                    var ajax_data = {
                        'action': 'mwp_wpvivid_global_set_white_label_setting',
                        'setting': setting_data
                    };
                    jQuery('#mwp_wpvivid_global_white_label_save').css({'pointer-events': 'none', 'opacity': '0.4'});
                    mwp_wpvivid_post_request(ajax_data, function (data) {
                        try {
                            var jsonarray = jQuery.parseJSON(data);

                            jQuery('#mwp_wpvivid_global_white_label_save').css({'pointer-events': 'auto', 'opacity': '1'});
                            if (jsonarray.result === 'success') {
                                window.location.href = window.location.href + "&synchronize=1&addon=1";
                            }
                            else {
                                alert(jsonarray.error);
                            }
                        }
                        catch (err) {
                            alert(err);
                            jQuery('#mwp_wpvivid_global_white_label_save').css({'pointer-events': 'auto', 'opacity': '1'});
                        }
                    }, function (XMLHttpRequest, textStatus, errorThrown) {
                        jQuery('#mwp_wpvivid_global_white_label_save').css({'pointer-events': 'auto', 'opacity': '1'});
                        var error_message = mwp_wpvivid_output_ajaxerror('changing base settings', textStatus, errorThrown);
                        alert(error_message);
                    });
                });

                jQuery('#mwp_wpvivid_white_label_save').on('click', function(){
                    var setting_data = mwp_wpvivid_ajax_data_transfer('mwp_white_label_setting');
                    var ajax_data = {
                        'action': 'mwp_wpvivid_set_white_label_setting',
                        'setting': setting_data,
                        'site_id': '<?php echo esc_html($this->site_id); ?>'
                    };
                    jQuery('#mwp_wpvivid_white_label_save').css({'pointer-events': 'none', 'opacity': '0.4'});
                    mwp_wpvivid_post_request(ajax_data, function (data) {
                        try {
                            var jsonarray = jQuery.parseJSON(data);

                            jQuery('#mwp_wpvivid_white_label_save').css({'pointer-events': 'auto', 'opacity': '1'});
                            if (jsonarray.result === 'success') {
                                location.reload();
                            }
                            else {
                                alert(jsonarray.error);
                            }
                        }
                        catch (err) {
                            alert(err);
                            jQuery('#mwp_wpvivid_white_label_save').css({'pointer-events': 'auto', 'opacity': '1'});
                        }
                    }, function (XMLHttpRequest, textStatus, errorThrown) {
                        jQuery('#mwp_wpvivid_white_label_save').css({'pointer-events': 'auto', 'opacity': '1'});
                        var error_message = mwp_wpvivid_output_ajaxerror('changing base settings', textStatus, errorThrown);
                        alert(error_message);
                    });
                });
            </script>
            <?php
        }
    }

    public function mwp_wpvivid_synchronize_white_label($check_addon){
        global $mainwp_wpvivid_extension_activator;
        $mainwp_wpvivid_extension_activator->render_sync_websites_page('mwp_wpvivid_sync_white_label', $check_addon);
        ?>
        <script>
            jQuery('#mwp_wpvivid_sync_white_label').click(function(){
                mwp_wpvivid_sync_white_label();
            });

            function mwp_wpvivid_sync_white_label(){
                var website_ids= [];
                mwp_wpvivid_sync_index=0;
                jQuery('.mwp-wpvivid-sync-row').each(function()
                {
                    jQuery(this).children('td:first').each(function(){
                        if (jQuery(this).children().children().prop('checked')) {
                            var id = jQuery(this).attr('website-id');
                            website_ids.push(id);
                        }
                    });
                });
                if(website_ids.length>0)
                {
                    jQuery('#mwp_wpvivid_sync_menu_capability').css({'pointer-events': 'none', 'opacity': '0.4'});
                    var check_addon = '<?php echo $check_addon; ?>';
                    mwp_wpvivid_sync_site(website_ids,check_addon,'mwp_wpvivid_sync_white_label','Extensions-Wpvivid-Backup-Mainwp&tab=white_label','mwp_wpvivid_white_label_tab');
                }
            }
        </script>
        <?php
    }
}