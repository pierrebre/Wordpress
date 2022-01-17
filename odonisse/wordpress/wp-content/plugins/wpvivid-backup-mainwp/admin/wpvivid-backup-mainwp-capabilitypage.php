<?php

class Mainwp_WPvivid_Extension_Capability
{
    private $capability_addon;
    private $site_id;

    public function __construct($capability_addon = array())
    {
        $this->capability_addon=$capability_addon;
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
            $this->mwp_wpvivid_synchronize_menu_capability($check_addon);
        }
        else{
            $cap_list = $this->capability_addon;
            ?>
            <div style="margin: 10px;">
                <div>
                    <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                        <img src="<?php echo esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL.'/admin/images/role-cap.png'); ?>" style="width:50px;height:50px;">
                    </div>
                    <div class="mwp-wpvivid-block-bottom-space">
                        <div>In this tab, you have the option to choose to hide WPvivid Backup Pro plugin modules on child sites.</div>
                    </div>
                    <div style="clear: both;"></div>
                </div>
                <div style="margin-top:10px; margin-bottom: 10px;"><p><strong>Select Modules and Hide</strong></p></div>
                <table class="wp-list-table widefat plugins">
                    <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Display</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($cap_list as $key=>$value){
                        ?>
                        <tr>
                            <td>
                                <?php echo $this->menu_transfer($key); ?>
                            </td>
                            <td>
                                <?php
                                if($value == '1'){
                                    ?>
                                    <input type="checkbox" name="mwp_wpvivid_caps" value="<?php esc_attr_e($key); ?>" checked />
                                    <?php
                                }
                                else{
                                    ?>
                                    <input type="checkbox" name="mwp_wpvivid_caps" value="<?php esc_attr_e($key); ?>" />
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
                <div style="margin-top: 10px;">
                    <?php
                    if ($global === false) {
                        $save_change_id = 'mwp_wpvivid_capability_save_addon';
                    } else {
                        $save_change_id = 'mwp_wpvivid_global_capability_save_addon';
                    }
                    ?>
                    <input class="ui green mini button" id="<?php esc_attr_e($save_change_id); ?>" type="button" value="<?php esc_attr_e('Save Changes and Sync'); ?>" />
                </div>
            </div>
            <script>
                jQuery('#mwp_wpvivid_capability_save_addon').click(function(){
                    var cap_option = {};
                    jQuery('input:checkbox[name=mwp_wpvivid_caps]').each(function()
                    {
                        var value = jQuery(this).val();
                        if(jQuery(this).prop('checked')) {
                            cap_option[value]=1;
                        }
                        else {
                            cap_option[value]=0;
                        }
                    });
                    var caps=JSON.stringify(cap_option);
                    var ajax_data= {
                        'action': 'mwp_wpvivid_save_menu_capability_addon',
                        'caps':caps,
                        'site_id': '<?php echo esc_html($this->site_id); ?>'
                    };
                    jQuery('#mwp_wpvivid_capability_save_addon').css({'pointer-events': 'none', 'opacity': '0.4'});
                    mwp_wpvivid_post_request(ajax_data, function (data) {
                        jQuery('#mwp_wpvivid_capability_save_addon').css({'pointer-events': 'auto', 'opacity': '1'});
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
                        jQuery('#mwp_wpvivid_capability_save_addon').css({'pointer-events': 'auto', 'opacity': '1'});
                        var error_message = mwp_wpvivid_output_ajaxerror('changing base settings', textStatus, errorThrown);
                        alert(error_message);
                    });
                });

                jQuery('#mwp_wpvivid_global_capability_save_addon').click(function(){
                    var cap_option = {};
                    jQuery('input:checkbox[name=mwp_wpvivid_caps]').each(function()
                    {
                        var value = jQuery(this).val();
                        if(jQuery(this).prop('checked')) {
                            cap_option[value]=1;
                        }
                        else {
                            cap_option[value]=0;
                        }
                    });
                    var caps=JSON.stringify(cap_option);
                    var ajax_data= {
                        'action': 'mwp_wpvivid_save_global_menu_capability_addon',
                        'caps':caps
                    };
                    jQuery('#mwp_wpvivid_global_capability_save_addon').css({'pointer-events': 'none', 'opacity': '0.4'});
                    mwp_wpvivid_post_request(ajax_data, function (data) {
                        jQuery('#mwp_wpvivid_global_capability_save_addon').css({'pointer-events': 'auto', 'opacity': '1'});
                        try {
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success') {
                                window.location.href = window.location.href + "&synchronize=1&addon=1";
                            }
                            else {
                                alert(jsonarray.error);
                            }
                        }
                        catch (err) {
                            alert(err);
                        }
                    }, function (XMLHttpRequest, textStatus, errorThrown) {
                        jQuery('#mwp_wpvivid_global_capability_save_addon').css({'pointer-events': 'auto', 'opacity': '1'});
                        var error_message = mwp_wpvivid_output_ajaxerror('changing base settings', textStatus, errorThrown);
                        alert(error_message);
                    });
                });
            </script>
            <?php
        }
    }

    public function menu_transfer($menu)
    {
        switch ($menu){
            case 'menu_staging':
                $menu = 'Staging Sites';
                break;
            case 'menu_export_import':
                $menu = 'Export & Import';
                break;
            case 'menu_setting':
                $menu = 'Setting';
                break;
            case 'menu_debug':
                $menu = 'Debug';
                break;
            case 'menu_tools':
                $menu = 'Tools';
                break;
            case 'menu_log':
                $menu = 'Log';
                break;
            case 'menu_pro_page':
                $menu = 'License';
                break;
            default:
                break;
        }
        return $menu;
    }

    public function mwp_wpvivid_synchronize_menu_capability($check_addon){
        global $mainwp_wpvivid_extension_activator;
        $mainwp_wpvivid_extension_activator->render_sync_websites_page('mwp_wpvivid_sync_menu_capability', $check_addon);
        ?>
        <script>
            function mwp_wpvivid_sync_menu_capability()
            {
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
                    mwp_wpvivid_sync_site(website_ids,check_addon,'mwp_wpvivid_sync_menu_capability','Extensions-Wpvivid-Backup-Mainwp&tab=menu','mwp_wpvivid_menu_tab');
                }
            }
            jQuery('#mwp_wpvivid_sync_menu_capability').click(function(){
                mwp_wpvivid_sync_menu_capability();
            });
        </script>
        <?php
    }
}