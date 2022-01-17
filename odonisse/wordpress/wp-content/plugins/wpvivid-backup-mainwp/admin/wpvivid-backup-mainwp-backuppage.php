<?php

class Mainwp_WPvivid_Extension_BackupPage
{
    private $setting;
    private $setting_addon;
    private $backup_custom_setting;
    private $site_id;

    public function __construct($setting, $setting_addon=array(), $backup_custom_setting=array())
    {
        $this->setting=$setting;
        $this->setting_addon=$setting_addon;
        $this->backup_custom_setting=$backup_custom_setting;
    }

    public function set_site_id($site_id)
    {
        $this->site_id=$site_id;
    }

    public function render($check_pro, $global=false)
    {
        ?>
        <div style="margin: 10px;">
        <?php
        if($check_pro){
            $this->mwp_wpvivid_backup_progress_addon();
            $this->mwp_wpvivid_backup_manual_addon();
            $this->mwp_wpvivid_backup_js_addon();
        }
        else{
            $this->mwp_wpvivid_backup_progress();
            $this->mwp_wpvivid_backup_manual();
            $this->mwp_wpvivid_backup_schedule();
            $this->mwp_wpvivid_backup_list();
            $this->mwp_wpvivid_backup_js();
        }
        ?>
        </div>
        <?php
    }

    function mwp_wpvivid_backup_progress_addon(){
        if(isset($this->setting['wpvivid_common_setting']['estimate_backup'])) {
            if ($this->setting['wpvivid_common_setting']['estimate_backup']) {
                $mwp_wpvivid_setting_estimate_backup = '';
            } else {
                $mwp_wpvivid_setting_estimate_backup = 'display: none;';
            }
        }
        else{
            $mwp_wpvivid_setting_estimate_backup = '';
        }
        ?>

        <div class="postbox" id="mwp_wpvivid_backup_progress_addon" style="display: none;">
            <div class="mwp-action-progress-bar">
                <div class="mwp-action-progress-bar-percent" style="height:24px;width:0"></div>
            </div>
            <div style="float: left; <?php esc_attr_e($mwp_wpvivid_setting_estimate_backup); ?>">
                <div class="mwp-backup-basic-info"><span class="mwp-wpvivid-span"><?php _e('Database Size:', 'mainwp-wpvivid-extension'); ?></span><span class="mwp-wpvivid-span">N/A</span></div>
                <div class="mwp-backup-basic-info"><span class="mwp-wpvivid-span"><?php _e('File Size:', 'mainwp-wpvivid-extension'); ?></span><span class="mwp-wpvivid-span">N/A</span></div>
            </div>
            <div style="float: left;">
                <div class="mwp-backup-basic-info"><span class="mwp-wpvivid-span"><?php _e('Total Size:', 'mainwp-wpvivid-extension'); ?></span><span class="mwp-wpvivid-span">N/A</span></div>
                <div class="mwp-backup-basic-info"><span class="mwp-wpvivid-span"><?php _e('Uploaded:', 'mainwp-wpvivid-extension'); ?></span><span class="mwp-wpvivid-span">N/A</span></div>
                <div class="mwp-backup-basic-info"><span class="mwp-wpvivid-span"><?php _e('Speed:', 'mainwp-wpvivid-extension'); ?></span><span class="mwp-wpvivid-span">N/A</span></div>
            </div>
            <div style="float: left;">
                <div class="mwp-backup-basic-info"><span class="mwp-wpvivid-span"><?php _e('Network Connection:', 'mainwp-wpvivid-extension'); ?></span><span class="mwp-wpvivid-span">N/A</span></div>
            </div>
            <div style="clear:both;"></div>
            <div style="margin-left:10px; float: left; width:100%;"><p id="mwp_wpvivid_current_doing"></p></div>
            <div style="clear: both;"></div>
            <div>
                <div class="mwp-backup-log-btn" style="float: left;"><input class="ui green mini button" id="mwp_wpvivid_backup_cancel_btn_addon" type="button" value="<?php esc_attr_e( 'Cancel', 'mainwp-wpvivid-extension' ); ?>" /></div>
                <div style="clear: both;"></div>
            </div>
            <div style="clear: both;"></div>
        </div>
        <script>
            jQuery('#mwp_wpvivid_backup_progress_addon').on('click', 'input', function(){
                if(jQuery(this).attr('id') === 'mwp_wpvivid_backup_cancel_btn_addon')
                {
                    mwp_wpvivid_cancel_backup_addon();
                }
            });

            function mwp_wpvivid_cancel_backup_addon(){
                var ajax_data= {
                    'action': 'mwp_wpvivid_backup_cancel_addon',
                    'site_id':'<?php echo esc_html($this->site_id); ?>'
                };
                jQuery('#mwp_wpvivid_backup_cancel_btn_addon').css({'pointer-events': 'none', 'opacity': '0.4'});
                mwp_wpvivid_post_request(ajax_data, function(data){
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        jQuery('#mwp_wpvivid_current_doing').html(jsonarray.msg);
                    }
                    catch(err){
                        alert(err);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown) {
                    jQuery('#mwp_wpvivid_backup_cancel_btn_addon').css({'pointer-events': 'auto', 'opacity': '1'});
                    var error_message = mwp_wpvivid_output_ajaxerror('cancelling the backup', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        </script>
        <?php
    }

    function mwp_wpvivid_backup_manual_addon(){
        if(isset($this->setting['wpvivid_local_setting']['path']) && !empty($this->setting['wpvivid_local_setting']['path'])){
            $local_path = $this->setting['wpvivid_local_setting']['path'];
        }
        else{
            $local_path = 'wpvividbackups';
        }
        add_action('mwp_wpvivid_custom_backup_setting', array($this, 'mwp_wpvivid_custom_backup_setting'), 10);
        ?>
        <div class="mwp-wpvivid-block-bottom-space" id="mwp_wpvivid_backup_notice"></div>
        <div class="postbox mwp-quickbackup-addon">
            <div>
                <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                    <img src="<?php echo esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL.'/admin/images/backup-migration.png'); ?>" style="width:50px;height:50px;">
                </div>
                <div style="box-sizing: border-box;">
                    <div class="mwp-wpvivid-block-bottom-space">The block allows you to: </div>
                    <div class="mwp-wpvivid-block-right-space" style="float: left;">1. Create a manual backup </div>
                    <div class="mwp-wpvivid-block-right-space" style="float: left;">2. Create a staging or dev environment </div>
                    <div class="mwp-wpvivid-block-right-space" style="float: left;">3. Migrate a WordPress site </div>
                    <div style="clear: both;"></div>
                </div>
                <div style="clear: both;"></div>
            </div>
            <div style="clear: both;"></div>
            <!-- Backup to -->
            <div class="mwp-wpvivid-block-bottom-space" style="background: #fff; border: 1px solid #f1f1f1; border-radius: 6px;padding: 10px 10px 0 10px;">
                <div class="mwp-wpvivid-font-right-space" style="float: left; margin-bottom: 10px;"><?php _e('Child-Site Storage Directory: '); ?></div>
                <div class="mwp-wpvivid-font-right-space" style="float: left; margin-bottom: 10px;"><?php _e('http(s)://child-site/wp-content/'); ?><?php _e($local_path); ?></div>
                <div class="mwp-wpvivid-font-right-space" style="float: left; margin-bottom: 10px;"><a href="#" onclick="mwp_switch_wpvivid_tab('setting');"><?php _e(' rename directory', 'wpvivid'); ?></a></div>
                <div style="clear: both;"></div>
            </div>
            <!-- Step One -->
            <div style="width:100%; border:1px solid #f1f1f1; float:left; box-sizing: border-box;margin-bottom:10px;">
                <div style="box-sizing: border-box; margin: 1px; background-color: #f1f1f1;">
                    <div style="box-sizing: border-box;">
                        <div style="float: left; margin-right: 10px;"><h2 class="mwp-wpvivid-font-h2-addon" style="padding-right: 0;">Step One: I want to</h2></div>
                        <small>
                            <div class="mwp-wpvivid-tooltip" style="margin-top: 12px; float: left;">?
                                <div class="mwp-wpvivid-tooltiptext">The backup will be saved to different folders for different purposes.</div>
                            </div>
                        </small>
                        <div style="clear: both;"></div>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
            <div style="width:100%; border:1px solid #f1f1f1; float:left; padding:10px 10px 0 10px;box-sizing: border-box;margin-bottom:10px;">
                <div style="float:left; border:1px solid #f1f1f1; margin:0 10px 10px 0; width: calc(33% - 10px);box-sizing: border-box;">
                    <div style="margin:1px 1px 10px 1px; padding:10px; box-sizing: border-box; background-color: #f7f7f7;"><strong>Option 1:</strong> Create a <strong>Backup</strong> Manually</div>
                    <fieldset style="box-sizing: border-box; padding: 0; margin:10px 10px 0 10px;">
                        <div>
                            <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                                <label>
                                    <input type="radio" option="mwp_backup" name="mwp_backup_to" value="local" checked>
                                    <span>save it to localhost</span>
                                </label>
                            </div>
                            <small>
                                <div class="mwp-wpvivid-tooltip mwp-wpvivid-block-bottom-space" style="float: left; margin-top: 2px;">?
                                    <div class="mwp-wpvivid-tooltiptext">The backup will be saved to the server where your website is hosted.</div>
                                </div>
                            </small>
                            <div style="clear: both;"></div>
                        </div>
                        <div>
                            <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                                <label>
                                    <input type="radio" option="mwp_backup" name="mwp_backup_to" value="remote">
                                    <span>send it to remote storage</span>
                                </label>
                            </div>
                            <small>
                                <div class="mwp-wpvivid-tooltip mwp-wpvivid-block-bottom-space" style="float: left; margin-top: 3px;">?
                                    <div class="mwp-wpvivid-tooltiptext">Save the backup to remote storage, for example, Google Drive, Dropbox, Microsoft OneDrive and more. A valid remote storage account is reuqired.</div>
                                </div>
                            </small>
                            <div style="clear: both;"></div>
                        </div>
                    </fieldset>
                </div>

                <div style="float:left;border:1px solid #f1f1f1; margin:0 10px 10px 0; width: calc(33% - 10px);box-sizing: border-box;">
                    <div style="margin:1px 1px 10px 1px; padding:10px; box-sizing: border-box; background-color: #f7f7f7;"><strong>Option 2:</strong> Create a <strong>Staging</strong> or Dev Environment</div>
                    <fieldset style="box-sizing: border-box; padding: 0; margin:10px 10px 0 10px;">
                        <div class="mwp-wpvivid-block-bottom-space">
                            <a onclick="mwp_wpvivid_switch_staging();" style="cursor: pointer;">Click here to Create a Staging Site</a>
                        </div>
                    </fieldset>
                </div>

                <div style="float:left;border:1px solid #f1f1f1; margin:0 0 10px 0; width: calc(33% - 0px);box-sizing: border-box;">
                    <div style="margin:1px 1px 10px 1px; padding:10px; box-sizing: border-box; background-color: #f7f7f7;"><strong>Option 3: Migrate</strong> the Site By</div>
                    <fieldset style="box-sizing: border-box; padding: 0; margin:10px 10px 0 10px;">
                        <div>
                            <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                                <label>
                                    <input type="radio" option="mwp_backup" name="mwp_backup_to" value="migrate_remote">
                                    <span>sending it to remote storage</span>
                                </label>
                            </div>
                            <div class="mwp-wpvivid-block-bottom-space" style="float: left; margin-top: 2px;">
                                <small>
                                    <div class="mwp-wpvivid-tooltip" style="float: left;">?
                                        <div class="mwp-wpvivid-tooltiptext">Send the backup to remote storage for migration.</div>
                                    </div>
                                </small>
                                <div style="float: left; margin-left: 5px;">
                                    <a href="https://wpvivid.com/wpvivid-backup-pro-migrate-wordpress-site-via-remote-storage" target="_blank">Learn more</a>
                                </div>
                                <div style="clear: both;"></div>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                    </fieldset>
                    <div style="padding:0 10px 10px 10px;">
                        This is the most reliable method when you migrate a wordpress site, especially a medium-sized or large site.
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
            <!-- Step Two -->
            <div style="width:100%; border:1px solid #f1f1f1; float:left; box-sizing: border-box;margin-bottom:10px;">
                <div style="box-sizing: border-box; margin: 1px; background-color: #f1f1f1;"><h2 class="mwp-wpvivid-font-h2-addon">Step Two: Choose what to back up</h2></div>
            </div>
            <div style="clear: both;"></div>
            <div style="width:100%; border:1px solid #f1f1f1; float:left; padding:10px 10px 0 10px;margin-bottom:10px; box-sizing: border-box;">
                <fieldset style="padding: 0;">
                    <legend class="screen-reader-text"><span>input type="radio"</span></legend>
                    <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                        <label>
                            <input type="radio" option="mwp_backup" name="mwp_backup_files" value="files+db" checked />
                            <span><?php _e('Database + Files (WordPress Files)', 'mainwp-wpvivid-extension'); ?></span>
                        </label>
                    </div>
                    <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                        <label>
                            <input type="radio" option="mwp_backup" name="mwp_backup_files" value="files" />
                            <span><?php _e('WordPress Files (Exclude Database)', 'mainwp-wpvivid-extension'); ?></span>
                        </label>
                    </div>
                    <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                        <label>
                            <input type="radio" option="mwp_backup" name="mwp_backup_files" value="db" />
                            <span><?php _e('Only Database', 'mainwp-wpvivid-extension'); ?></span>
                        </label>
                    </div>
                    <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                        <label>
                            <input type="radio" option="mwp_backup" name="mwp_backup_files" value="custom" />
                            <span><?php _e('Custom Backup', 'mainwp-wpvivid-extension'); ?></span>
                        </label>
                    </div>
                    <div style="clear: both;"></div>
                </fieldset>
            </div>
            <div style="clear: both;"></div>
            <?php
            $type = 'manual_backup';
            do_action('mwp_wpvivid_custom_backup_setting', $type);
            ?>
            <!-- Step Three -->
            <div style="width:100%; border:1px solid #f1f1f1; float:left; box-sizing: border-box;margin-bottom:10px;">
                <div style="box-sizing: border-box; margin: 1px; background-color: #f1f1f1;"><h2 class="mwp-wpvivid-font-h2-addon">Step Three: Comment the backup (optional)</h2></div>
            </div>
            <div style="clear: both;"></div>
            <div style="width:100%; border:1px solid #f1f1f1; float:left; padding:10px 10px 0 10px;margin-bottom:10px; box-sizing: border-box;">
                <div>
                    <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-font-right-space" style="float: left; height: 30px; line-height: 30px;">Comment the backup: </div>
                    <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-font-right-space" style="float: left;">
                        <input type="text" option="mwp_backup" name="mwp_backup_prefix" id="mwp_wpvivid_set_manual_prefix" onkeyup="value=value.replace(/[^a-zA-Z0-9]/g,'')" onpaste="value=value.replace(/[^\a-\z\A-\Z0-9]/g,'')" />
                    </div>
                    <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-font-right-space" style="float: left; height: 30px; line-height: 30px;">
                        Only letters (except for wpvivid) and numbers are allowed.
                    </div>
                    <div style="clear: both;"></div>
                </div>
                <div>
                    <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-font-right-space" style="float: left;">Sample:</div>
                    <div class="mwp-wpvivid-block-bottom-space" style="float: left;">
                        <div class="mwp-wpvivid-block-bottom-space" style="display: inline;" id="mwp_wpvivid_manual_prefix">*</div><div class="mwp-wpvivid-block-bottom-space" style="display: inline;">_wpvivid-5ceb938b6dca9_2019-05-27-07-36_backup_all.zip</div>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>
            <!-- Step Four -->
            <div style="width:100%; border:1px solid #f1f1f1; float:left; box-sizing: border-box;margin-bottom:10px;">
                <div style="box-sizing: border-box; margin: 1px; background-color: #f1f1f1;"><h2 class="mwp-wpvivid-font-h2-addon">Step Four:</h2></div>
            </div>
            <div style="clear: both;"></div>
            <div style="width:100%; border:1px solid #f1f1f1; float:left; padding:10px 10px 0 10px; box-sizing: border-box;margin-bottom:10px;">
                <div>
                    <div class="mwp-wpvivid-block-bottom-space">
                        <input class="ui green mini button mwp-quickbackup-btn" id="mwp_wpvivid_backup_btn_addon" type="button" value="Backup Now" style="width: 200px;" />
                    </div>
                    <div class="mwp-wpvivid-block-bottom-space" style="text-align: left;">
                        <fieldset style="padding: 0;">
                            <label>
                                <input type="checkbox" id="mwp_wpvivid_backup_lock" option="mwp_backup" name="mwp_lock">
                                <span>Marking this backup can only be deleted manually</span>
                            </label>
                        </fieldset>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
            <!-- Tips -->
            <div style="background: #fff; border: 1px solid #f1f1f1; border-radius: 6px; padding: 10px;">
                <span><strong>Tips: </strong>The settings are only for manual backup, it won't affect schedule settings.</span>
            </div>
        </div>
        <?php
    }

    function mwp_wpvivid_custom_backup_setting($type){
        $pop_class = 'mwp-wpvivid-custom-popup';
        $pop_text_class = 'mwp-wpvivid-custom-popuptext';
        $pop_style = 'display: none;';

        ?>
        <div class="<?php esc_attr_e($pop_class); ?>" id="mwp_wpvivid_<?php esc_attr_e($type); ?>_custom_module_part" style="<?php esc_attr_e($pop_style); ?>">
            <div class="<?php esc_attr_e($pop_text_class); ?>" id="mwp_wpvivid_<?php esc_attr_e($type); ?>_custom_module" style="padding-top: 0;">
                <?php
                $custom_staging_list = new Mainwp_WPvivid_Custom_Backup_List($this->backup_custom_setting);
                $custom_staging_list ->set_parent_id('mwp_wpvivid_'.$type.'_custom_module');
                $custom_staging_list ->display_rows();
                $custom_staging_list ->load_js();
                ?>
            </div>
        </div>

        <script>
            function mwp_wpvivid_refresh_custom_backup_info(){
                mwp_wpvivid_get_database_tables();
                mwp_wpvivid_get_themes_plugins();
            }

            var mwp_wpvivid_get_database_retry_times = 0;
            var mwp_wpvivid_get_themes_retry_times = 0;

            function mwp_wpvivid_get_database_retry(){
                var need_retry_custom_database = false;
                mwp_wpvivid_get_database_retry_times++;
                if(mwp_wpvivid_get_database_retry_times < 10){
                    need_retry_custom_database = true;
                }
                if(need_retry_custom_database){
                    setTimeout(function(){
                        mwp_wpvivid_get_database_tables();
                    }, 3000);
                }
                else{
                    var refresh_btn = '<input class="ui green mini button" type="button" value="Refresh" onclick="mwp_wpvivid_refresh_database_tables();">';
                    jQuery('#mwp_wpvivid_manual_backup_custom_module').find('.mwp-wpvivid-custom-database-info').html(refresh_btn);
                    jQuery('#mwp_wpvivid_schedule_add_custom_module').find('.mwp-wpvivid-custom-database-info').html(refresh_btn);
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-database-info').html(refresh_btn);
                }
            }

            function mwp_wpvivid_refresh_database_tables(){
                mwp_wpvivid_get_database_retry_times = 0;
                var custom_database_loading = '<div class="spinner is-active" style="margin: 0 5px 10px 0; float: left;"></div>' +
                    '<div style="float: left;">Archieving themes and plugins</div>' +
                    '<div style="clear: both;"></div>';
                jQuery('#mwp_wpvivid_manual_backup_custom_module').find('.mwp-wpvivid-custom-database-info').html(custom_database_loading);
                jQuery('#mwp_wpvivid_schedule_add_custom_module').find('.mwp-wpvivid-custom-database-info').html(custom_database_loading);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-database-info').html(custom_database_loading);
                mwp_wpvivid_get_database_tables();
            }

            function mwp_wpvivid_get_database_tables(){
                var ajax_data = {
                    'action': 'mwp_wpvivid_get_database_tables',
                    'site_id': '<?php echo esc_html($this->site_id); ?>'
                };
                mwp_wpvivid_post_request(ajax_data, function (data){
                    try {
                        var json = jQuery.parseJSON(data);
                        if (json.result === 'success') {
                            mwp_wpvivid_get_database_retry_times = 0;
                            jQuery('#mwp_wpvivid_manual_backup_custom_module').find('.mwp-wpvivid-custom-database-info').html(json.database_tables);
                            jQuery('#mwp_wpvivid_schedule_add_custom_module').find('.mwp-wpvivid-custom-database-info').html(json.database_tables);
                            jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-database-info').html(json.database_tables);
                        }
                        else{
                            mwp_wpvivid_get_database_retry();
                        }
                    }
                    catch(err) {
                        mwp_wpvivid_get_database_retry();
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    mwp_wpvivid_get_database_retry();
                });
            }

            function mwp_wpvivid_get_themes_retry(){
                var need_retry_custom_themes = false;
                mwp_wpvivid_get_themes_retry_times++;
                if(mwp_wpvivid_get_themes_retry_times < 10){
                    need_retry_custom_themes = true;
                }
                if(need_retry_custom_themes){
                    setTimeout(function(){
                        mwp_wpvivid_get_themes_plugins();
                    }, 3000);
                }
                else{
                    var refresh_btn = '<input class="ui green mini button" type="button" value="Refresh" onclick="mwp_wpvivid_refresh_themes_plugins();">';
                    jQuery('#mwp_wpvivid_manual_backup_custom_module').find('.mwp-wpvivid-custom-themes-plugins-info').html(refresh_btn);
                    jQuery('#mwp_wpvivid_schedule_add_custom_module').find('.mwp-wpvivid-custom-themes-plugins-info').html(refresh_btn);
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-themes-plugins-info').html(refresh_btn);
                }
            }

            function mwp_wpvivid_refresh_themes_plugins(){
                mwp_wpvivid_get_themes_retry_times = 0;
                var custom_themes_loading = '<div class="spinner is-active" style="margin: 0 5px 10px 0; float: left;"></div>' +
                    '<div style="float: left;">Archieving database tables</div>' +
                    '<div style="clear: both;"></div>';
                jQuery('#mwp_wpvivid_manual_backup_custom_module').find('.mwp-wpvivid-custom-themes-plugins-info').html(custom_themes_loading);
                jQuery('#mwp_wpvivid_schedule_add_custom_module').find('.mwp-wpvivid-custom-themes-plugins-info').html(custom_themes_loading);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-themes-plugins-info').html(custom_themes_loading);
                mwp_wpvivid_get_themes_plugins();
            }

            function mwp_wpvivid_get_themes_plugins(){
                var ajax_data = {
                    'action': 'mwp_wpvivid_get_themes_plugins',
                    'site_id': '<?php echo esc_html($this->site_id); ?>'
                };
                mwp_wpvivid_post_request(ajax_data, function (data){
                    try {
                        var json = jQuery.parseJSON(data);
                        if (json.result === 'success') {
                            mwp_wpvivid_get_themes_retry_times = 0;
                            jQuery('#mwp_wpvivid_manual_backup_custom_module').find('.mwp-wpvivid-custom-themes-plugins-info').html(json.themes_plugins_table);
                            jQuery('#mwp_wpvivid_schedule_add_custom_module').find('.mwp-wpvivid-custom-themes-plugins-info').html(json.themes_plugins_table);
                            jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-themes-plugins-info').html(json.themes_plugins_table);
                        }
                        else{
                            mwp_wpvivid_get_themes_retry();
                        }
                    }
                    catch(err) {
                        mwp_wpvivid_get_themes_retry();
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    mwp_wpvivid_get_themes_retry();
                });
            }

            function mwp_wpvivid_get_uploads_tree(parent_id, refresh){
                if(refresh){
                    jQuery('#'+parent_id).find('.mwp-wpvivid-custom-uploads-tree-info').jstree("refresh");
                }
                else {
                    jQuery('#'+parent_id).find('.mwp-wpvivid-custom-uploads-tree-info').jstree({
                        "core": {
                            "check_callback": true,
                            "multiple": true,
                            "data": function (node_id, callback) {
                                var tree_node = {
                                    'node': node_id
                                };
                                tree_node = JSON.stringify(tree_node);
                                var ajax_data = {
                                    'action': 'mwp_wpvivid_get_uploads_tree_data',
                                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                                    'tree_node': tree_node
                                };
                                jQuery.ajax({
                                    type: "post",
                                    url: ajax_object.ajax_url,
                                    data: ajax_data,
                                    success: function (data) {
                                        var jsonarray = jQuery.parseJSON(data);
                                        if(jsonarray.result === 'success') {
                                            callback.call(this, jsonarray.uploads_tree_data);
                                        }
                                        else{
                                            alert(jsonarray.error);
                                        }
                                    },
                                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                                        alert("error");
                                    },
                                    timeout: 30000
                                });
                            },
                            'themes': {
                                'stripes': true
                            }
                        }
                    });
                }
            }

            function mwp_wpvivid_get_content_tree(parent_id, refresh){
                if(refresh){
                    jQuery('#'+parent_id).find('.mwp-wpvivid-custom-content-tree-info').jstree("refresh");
                }
                else {
                    jQuery('#'+parent_id).find('.mwp-wpvivid-custom-content-tree-info').jstree({
                        "core": {
                            "check_callback": true,
                            "multiple": true,
                            "data": function (node_id, callback) {
                                var tree_node = {
                                    'node': node_id
                                };
                                tree_node = JSON.stringify(tree_node);
                                var ajax_data = {
                                    'action': 'mwp_wpvivid_get_content_tree_data',
                                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                                    'tree_node': tree_node
                                };
                                jQuery.ajax({
                                    type: "post",
                                    url: ajax_object.ajax_url,
                                    data: ajax_data,
                                    success: function (data) {
                                        var jsonarray = jQuery.parseJSON(data);
                                        if(jsonarray.result === 'success') {
                                            callback.call(this, jsonarray.content_tree_data);
                                        }
                                        else{
                                            alert(jsonarray.error);
                                        }
                                    },
                                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                                        alert("error");
                                    },
                                    timeout: 30000
                                });
                            },
                            'themes': {
                                'stripes': true
                            }
                        }
                    });
                }
            }

            function mwp_wpvivid_get_additional_folder_tree(parent_id, refresh){
                if(refresh){
                    jQuery('#'+parent_id).find('.mwp-wpvivid-custom-additional-folder-tree-info').jstree('refresh');
                }
                else {
                    jQuery('#'+parent_id).find('.mwp-wpvivid-custom-additional-folder-tree-info').jstree({
                        "core": {
                            "check_callback": true,
                            "multiple": true,
                            "data": function (node_id, callback) {
                                var tree_node = {
                                    'node': node_id
                                };
                                tree_node = JSON.stringify(tree_node);
                                var ajax_data = {
                                    'action': 'mwp_wpvivid_get_additional_folder_tree_data',
                                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                                    'tree_node': tree_node
                                };
                                jQuery.ajax({
                                    type: "post",
                                    url: ajax_object.ajax_url,
                                    data: ajax_data,
                                    success: function (data) {
                                        var jsonarray = jQuery.parseJSON(data);
                                        if(jsonarray.result === 'success') {
                                            callback.call(this, jsonarray.additional_folder_tree_data);
                                        }
                                        else{
                                            alert(jsonarray.error);
                                        }
                                    },
                                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                                        alert("error");
                                    },
                                    timeout: 30000
                                });
                            },
                            'themes': {
                                'stripes': true
                            }
                        }
                    });
                }
            }

            function mwp_wpvivid_additional_database_connect(parent_id){
                var db_user = jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-user').val();
                var db_pass = jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-pass').val();
                var db_host = jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-host').val();
                if(db_user !== ''){
                    if(db_host !== ''){
                        var db_json = {};
                        db_json['db_user'] = db_user;
                        db_json['db_pass'] = db_pass;
                        db_json['db_host'] = db_host;
                        var db_connect_info = JSON.stringify(db_json);
                        var ajax_data = {
                            'action': 'mwp_wpvivid_connect_additional_database_addon',
                            'site_id': '<?php echo esc_html($this->site_id); ?>',
                            'database_info': db_connect_info
                        };
                        jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-connect').css({'pointer-events': 'none', 'opacity': '0.4'});
                        jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-close').css({'pointer-events': 'none', 'opacity': '0.4'});
                        mwp_wpvivid_post_request(ajax_data, function (data){
                            jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-connect').css({'pointer-events': 'auto', 'opacity': '1'});
                            jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-close').css({'pointer-events': 'auto', 'opacity': '1'});
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray !== null) {
                                if (jsonarray.result === 'success') {
                                    var div = '<div class="mwp-wpvivid-additional-db-account" style="border: 1px solid #e5e5e5; border-bottom: 0; margin-top: 0; margin-bottom: 0; padding: 10px; box-sizing:border-box;">' + jsonarray.html + '</div>';
                                    jQuery('#'+parent_id).find('.mwp-wpvivid-additional-database-list').append(div);
                                    jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-connect').hide();
                                    jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-close').hide();
                                }
                                else {
                                    alert(jsonarray.error);
                                }
                            }
                            else {
                                alert('Login Failed. Please check the credentials you entered and try again.');
                            }
                        }, function (XMLHttpRequest, textStatus, errorThrown) {
                            jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-connect').css({'pointer-events': 'auto', 'opacity': '1'});
                            jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-close').css({'pointer-events': 'auto', 'opacity': '1'});
                            var error_message = mwp_wpvivid_output_ajaxerror('retrieving the last backup log', textStatus, errorThrown);
                            alert(error_message);
                        });
                    }
                    else{
                        alert('Host is required.');
                    }
                }
                else{
                    alert('User Name is required.');
                }
            }

            function mwp_wpvivid_additional_database_add(parent_id){
                var db_user = jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-user').val();
                var db_pass = jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-pass').val();
                var db_host = jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-host').val();
                if(db_user !== ''){
                    if(db_host !== ''){
                        var json = {};
                        json['db_user'] = db_user;
                        json['db_pass'] = db_pass;
                        json['db_host'] = db_host;
                        json['additional_database_list'] = Array();
                        jQuery('#'+parent_id).find('input:checkbox[option=additional_db]').each(function () {
                            if (jQuery(this).prop('checked')) {
                                json['additional_database_list'].push(this.value);
                            }
                        });
                        var database_info = JSON.stringify(json);
                        var ajax_data = {
                            'action': 'mwp_wpvivid_add_additional_database_addon',
                            'site_id': '<?php echo esc_html($this->site_id); ?>',
                            'database_info': database_info
                        };
                        jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-add').css({'pointer-events': 'none', 'opacity': '0.4'});
                        jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-table-close').css({'pointer-events': 'none', 'opacity': '0.4'});
                        mwp_wpvivid_post_request(ajax_data, function (data){
                            jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-add').css({'pointer-events': 'auto', 'opacity': '1'});
                            jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-table-close').css({'pointer-events': 'auto', 'opacity': '1'});
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success') {
                                jQuery('#'+parent_id).find('.mwp-wpvivid-additional-database-list').html(jsonarray.html);
                            }
                            else {
                                alert(jsonarray.error);
                            }
                        }, function (XMLHttpRequest, textStatus, errorThrown) {
                            jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-add').css({'pointer-events': 'auto', 'opacity': '1'});
                            jQuery('#'+parent_id).find('.mwp-wpvivid-additional-db-table-close').css({'pointer-events': 'auto', 'opacity': '1'});
                            var error_message = mwp_wpvivid_output_ajaxerror('retrieving the last backup log', textStatus, errorThrown);
                            alert(error_message);
                        });
                    }
                    else{
                        alert('Host is required.');
                    }
                }
                else{
                    alert('User Name is required.');
                }
            }

            function mwp_wpvivid_additional_database_remove(parent_id, database_name){
                var ajax_data = {
                    'action': 'mwp_wpvivid_remove_additional_database_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'database_name': database_name
                }
                jQuery('#'+parent_id).find('.mwp-wpvivid-additional-database-remove').css({'pointer-events': 'none', 'opacity': '0.4'});
                mwp_wpvivid_post_request(ajax_data, function (data){
                    jQuery('#'+parent_id).find('.mwp-wpvivid-additional-database-remove').css({'pointer-events': 'auto', 'opacity': '1'});
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success') {
                        jQuery('#'+parent_id).find('.mwp-wpvivid-additional-database-list').html(jsonarray.html);
                    }
                    else {
                        alert(jsonarray.error);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    jQuery('#'+parent_id).find('.mwp-wpvivid-additional-database-remove').css({'pointer-events': 'auto', 'opacity': '1'});
                    var error_message = mwp_wpvivid_output_ajaxerror('retrieving the last backup log', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function mwp_wpvivid_add_extension_rule(obj, type, value){
                var ajax_data = {
                    'action': 'mwp_wpvivid_update_backup_exclude_extension_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'type': type,
                    'exclude_content': value
                };
                jQuery(obj).css({'pointer-events': 'none', 'opacity': '0.4'});
                mwp_wpvivid_post_request(ajax_data, function (data){
                    jQuery(obj).css({'pointer-events': 'auto', 'opacity': '1'});
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                        }
                    }
                    catch (err) {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    jQuery(obj).css({'pointer-events': 'auto', 'opacity': '1'});
                    var error_message = mwp_wpvivid_output_ajaxerror('retrieving the last backup log', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        </script>
        <?php
    }

    function mwp_wpvivid_backup_js_addon(){
        ?>
        <script>
            function mwp_wpvivid_get_default_remote_addon(){
                var ajax_data={
                    'action': 'mwp_wpvivid_get_default_remote_addon',
                    'site_id':'<?php echo esc_html($this->site_id); ?>'
                };

                mwp_wpvivid_post_request(ajax_data, function (data){
                    try {
                        var json = jQuery.parseJSON(data);
                        if (json.result === 'success')
                        {
                            if(json.default_remote_storage === ''){
                                mwp_wpvivid_has_remote = false;
                            }
                            jQuery('#mwp_wpvivid_upload_storage').html(json.default_remote_pic);
                            jQuery('#mwp_schedule_upload_storage').html(json.default_remote_pic);
                            jQuery('#mwp_wpvivid_schedule_upload_storage').html(json.default_remote_pic);
                        }
                    }
                    catch(err)
                    {
                        mwp_wpvivid_get_default_remote_addon();
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    mwp_wpvivid_get_default_remote_addon();
                });
            }
            mwp_wpvivid_get_default_remote_addon();

            jQuery('input:radio[option=mwp_backup][name=mwp_backup_to]').click(function(){
                mwp_wpvivid_switch_backup_btn('backup');
                var value = jQuery(this).prop('value');
                if(value === 'remote' || value === 'staging_remote' || value === 'migrate_remote'){
                    if(!mwp_wpvivid_has_remote){
                        alert('There is no default remote storage configured. Please set it up first.');
                        jQuery('input:radio[option=mwp_backup][name=mwp_backup_to][value=local]').prop('checked', true);
                        mwp_wpvivid_switch_backup_btn('backup');
                    }
                    else{
                        if(value === 'remote'){
                            mwp_wpvivid_switch_backup_btn('backup');
                        }
                        else if(value === 'staging_remote'){
                            mwp_wpvivid_switch_backup_btn('staging');
                        }
                        else if(value === 'migrate_remote'){
                            mwp_wpvivid_switch_backup_btn('migrate');
                        }
                    }
                }
            });

            function mwp_wpvivid_switch_backup_btn(type){
                jQuery('#mwp_wpvivid_backup_btn_addon').val('Backup Now');
                jQuery('#mwp_wpvivid_backup_btn_addon').css({'width': '200'});
                if(type === 'backup'){
                    jQuery('#mwp_wpvivid_backup_btn_addon').val('Backup Now');
                    jQuery('#mwp_wpvivid_backup_btn_addon').css({'width': '200'});
                }
                else if(type === 'staging'){
                    jQuery('#mwp_wpvivid_backup_btn_addon').val('Clone the Site Now');
                    jQuery('#mwp_wpvivid_backup_btn_addon').css({'width': '250'});
                }
                else if(type === 'migrate'){
                    jQuery('#mwp_wpvivid_backup_btn_addon').val('Migrate');
                    jQuery('#mwp_wpvivid_backup_btn_addon').css({'width': '160'});
                }
            }

            jQuery('input:radio[option=mwp_backup][name=mwp_backup_files]').click(function(){
                if(this.value === 'custom'){
                    jQuery('#mwp_wpvivid_manual_backup_custom_module_part').show();
                    mwp_wpvivid_popup_schedule_tour_addon('show', 'manual_backup');
                }
                else{
                    jQuery('#mwp_wpvivid_manual_backup_custom_module_part').hide();
                    mwp_wpvivid_popup_schedule_tour_addon('hide', 'manual_backup');
                }
            });

            function mwp_wpvivid_popup_schedule_tour_addon(style, type) {
                var popup = document.getElementById("mwp_wpvivid_"+type+"_custom_module");
                if (popup != null) {
                    if(style === 'show') {
                        if(popup.classList.contains('hide')){
                            popup.classList.remove('hide');
                        }
                        popup.classList.add(style);
                    }
                    else if(style === 'hide'){
                        if(popup.classList.contains('show')){
                            popup.classList.remove('hide');
                            popup.classList.add(style);
                        }
                    }
                }
            }

            jQuery('#mwp_wpvivid_backup_btn_addon').on('click', function(){
                mwp_wpvivid_clear_notice('mwp_wpvivid_backup_notice');
                mwp_wpvivid_prepare_backup_addon();
            });

            var mwp_wpvivid_prepare_backup=false;
            var mwp_wpvivid_running_backup_taskid='';
            var mwp_task_retry_times = 0;

            function mwp_wpvivid_create_custom_backup_json(parent_id){
                var json = {};
                jQuery('#'+parent_id).find('.mwp-wpvivid-custom-check').each(function(){
                    if(jQuery(this).hasClass('mwp-wpvivid-custom-core-check')){
                        json['core_list'] = Array();
                        if(jQuery(this).prop('checked')){
                            json['core_check'] = '1';
                        }
                        else{
                            json['core_check'] = '0';
                        }
                    }
                    else if(jQuery(this).hasClass('mwp-wpvivid-custom-database-check')){
                        json['database_list'] = Array();
                        if(jQuery(this).prop('checked')){
                            json['database_check'] = '1';
                            jQuery('#'+parent_id).find('input:checkbox[name=Database]').each(function(){
                                if(!jQuery(this).prop('checked')){
                                    json['database_list'].push(jQuery(this).val());
                                }
                            });
                        }
                        else{
                            json['database_check'] = '0';
                        }
                    }
                    else if(jQuery(this).hasClass('mwp-wpvivid-custom-themes-plugins-check')){
                        json['themes_list'] = Array();
                        json['plugins_list'] = Array();
                        if(jQuery(this).prop('checked')){
                            json['themes_check'] = '0';
                            json['plugins_check'] = '0';
                            jQuery('#'+parent_id).find('input:checkbox[option=mwp_themes][name=Themes]').each(function(){
                                if(jQuery(this).prop('checked')){
                                    json['themes_check'] = '1';
                                }
                                else{
                                    json['themes_list'].push(jQuery(this).val());
                                }
                            });
                            jQuery('#'+parent_id).find('input:checkbox[option=mwp_plugins][name=Plugins]').each(function(){
                                if(jQuery(this).prop('checked')) {
                                    json['plugins_check'] = '1';
                                }
                                else{
                                    json['plugins_list'].push(jQuery(this).val());
                                }
                            });
                        }
                        else{
                            json['themes_check'] = '0';
                            json['plugins_check'] = '0';
                        }
                    }
                    else if(jQuery(this).hasClass('mwp-wpvivid-custom-uploads-check')){
                        json['uploads_list'] = {};
                        if(jQuery(this).prop('checked')){
                            json['uploads_check'] = '1';
                            jQuery('#'+parent_id).find('.mwp-wpvivid-custom-exclude-uploads-list ul').find('li div:eq(1)').each(function(){
                                var folder_name = this.innerHTML;
                                json['uploads_list'][folder_name] = {};
                                json['uploads_list'][folder_name]['name'] = folder_name;
                                json['uploads_list'][folder_name]['type'] = jQuery(this).prev().get(0).classList.item(0);
                            });
                            json['upload_extension'] = jQuery('#'+parent_id).find('.mwp-wpvivid-uploads-extension').val();
                        }
                        else{
                            json['uploads_check'] = '0';
                        }
                    }
                    else if(jQuery(this).hasClass('mwp-wpvivid-custom-content-check')){
                        json['content_list'] = {};
                        if(jQuery(this).prop('checked')){
                            json['content_check'] = '1';
                            jQuery('#'+parent_id).find('.mwp-wpvivid-custom-exclude-content-list ul').find('li div:eq(1)').each(function(){
                                var folder_name = this.innerHTML;
                                json['content_list'][folder_name] = {};
                                json['content_list'][folder_name]['name'] = folder_name;
                                json['content_list'][folder_name]['type'] = jQuery(this).prev().get(0).classList.item(0);
                            });
                            json['content_extension'] = jQuery('#'+parent_id).find('.mwp-wpvivid-content-extension').val();
                        }
                        else{
                            json['content_check'] = '0';
                        }
                    }
                    else if(jQuery(this).hasClass('mwp-wpvivid-custom-additional-folder-check')){
                        json['other_list'] = {};
                        if(jQuery(this).prop('checked')){
                            json['other_check'] = '1';
                            jQuery('#'+parent_id).find('.mwp-wpvivid-custom-include-additional-folder-list ul').find('li div:eq(1)').each(function(){
                                var folder_name = this.innerHTML;
                                json['other_list'][folder_name] = {};
                                json['other_list'][folder_name]['name'] = folder_name;
                                json['other_list'][folder_name]['type'] = jQuery(this).prev().get(0).classList.item(0);
                            });
                            json['other_extension'] = jQuery('#'+parent_id).find('.mwp-wpvivid-additional-folder-extension').val();
                        }
                        else{
                            json['other_check'] = '0';
                        }
                    }
                    else if(jQuery(this).hasClass('mwp-wpvivid-custom-additional-database-check')){
                        if(jQuery(this).prop('checked')) {
                            json['additional_database_check'] = '1';
                        }
                        else{
                            json['additional_database_check'] = '0';
                        }
                    }
                });
                return json;
            }

            function mwp_wpvivid_delete_ready_task(error){
                var ajax_data={
                    'action': 'mwp_wpvivid_delete_ready_task_addon',
                    'site_id':'<?php echo esc_html($this->site_id); ?>'
                };
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            mwp_wpvivid_add_notice('Backup', 'Error', error);
                            jQuery('#mwp_wpvivid_backup_btn_addon').css({'pointer-events': 'auto', 'opacity': '1'});
                            jQuery('#mwp_wpvivid_backup_progress_addon').hide();
                        }
                    }
                    catch(err){
                        mwp_wpvivid_add_notice('Backup', 'Error', err);
                        jQuery('#mwp_wpvivid_backup_btn_addon').css({'pointer-events': 'auto', 'opacity': '1'});
                        jQuery('#mwp_wpvivid_backup_progress_addon').hide();
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    setTimeout(function () {
                        mwp_wpvivid_delete_ready_task(error);
                    }, 3000);
                });
            }

            function mwp_wpvivid_prepare_backup_addon() {
                var backup_data = mwp_wpvivid_ajax_data_transfer('mwp_backup');
                var backup_type = jQuery('input:radio[option=mwp_backup][name=mwp_backup_files]:checked').val();
                if(backup_type === 'custom'){
                    backup_data = JSON.parse(backup_data);
                    var custom_dirs = mwp_wpvivid_create_custom_backup_json('mwp_wpvivid_manual_backup_custom_module_part');
                    var custom_option = {
                        'custom_dirs': custom_dirs
                    };
                    jQuery.extend(backup_data, custom_option);
                    backup_data = JSON.stringify(backup_data);
                }

                var ajax_data={
                    'action': 'mwp_wpvivid_prepare_backup_addon',
                    'site_id':'<?php echo esc_html($this->site_id); ?>',
                    'backup': backup_data
                };
                jQuery('#mwp_wpvivid_backup_btn_addon').css({'pointer-events': 'none', 'opacity': '0.4'});
                mwp_wpvivid_prepare_backup=true;
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    mwp_wpvivid_prepare_backup=false;
                    try {
                        var json = jQuery.parseJSON(data);
                        if (json.result === 'success')
                        {
                            mwp_wpvivid_backup_now_addon(json.data);
                        }
                        else
                        {
                            mwp_wpvivid_delete_ready_task(json.error);
                        }
                    }
                    catch(err) {
                        mwp_wpvivid_delete_ready_task(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    mwp_wpvivid_prepare_backup=false;
                    var error_message = mwp_wpvivid_output_ajaxerror('preparing the backup', textStatus, errorThrown);
                    mwp_wpvivid_delete_ready_task(error_message);
                });
            }

            function mwp_wpvivid_backup_now_addon(task_id) {
                var ajax_data = {
                    'action': 'mwp_wpvivid_backup_now_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'task_id': task_id
                };
                mwp_wpvivid_need_update = true;
                mwp_wpvivid_post_request(ajax_data, function (data) {
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                });
            }

            function mwp_wpvivid_list_task_addon(){
                var ajax_data={
                    'action': 'mwp_wpvivid_list_task_addon',
                    'site_id':'<?php echo esc_html($this->site_id); ?>'
                };
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    setTimeout(function () {
                        mwp_wpvivid_manage_task_addon();
                    }, 3000);
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        mwp_wpvivid_list_task_data(jsonarray);
                    }
                    catch(err) {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    setTimeout(function () {
                        mwp_wpvivid_need_update = true;
                        mwp_wpvivid_manage_task_addon();
                    }, 3000);
                });
            }

            function mwp_wpvivid_list_task_data(data){
                var b_has_data = false;
                var update_backup=false;

                if(data.progress_html!==false) {
                    jQuery('#mwp_wpvivid_backup_progress_addon').show();
                    jQuery('#mwp_wpvivid_backup_progress_addon').html(data.progress_html);
                }
                else {
                    if(!mwp_wpvivid_prepare_backup) {
                        jQuery('#mwp_wpvivid_backup_progress_addon').hide();
                    }
                }
                if (data.success_notice_html !== false) {
                    jQuery('#mwp_wpvivid_backup_notice').show();
                    jQuery('#mwp_wpvivid_backup_notice').append(data.success_notice_html);
                    update_backup=true;
                }
                if(data.error_notice_html !== false) {
                    jQuery('#mwp_wpvivid_backup_notice').show();
                    jQuery('#mwp_wpvivid_backup_notice').append(data.error_notice_html);
                    update_backup=true;
                }
                if(update_backup) {
                    jQuery( document ).trigger( 'mwp_wpvivid_update_local_backup');
                }
                if(data.need_refresh_remote !== false){
                    jQuery( document ).trigger( 'mwp_wpvivid_update_remote_backup');
                }
                if(data.need_update) {
                    mwp_wpvivid_need_update = true;
                }
                if(data.running_backup_taskid!== '') {
                    b_has_data = true;
                    mwp_task_retry_times = 0;
                    mwp_wpvivid_running_backup_taskid = data.running_backup_taskid;
                    jQuery('#mwp_wpvivid_backup_btn_addon').css({'pointer-events': 'none', 'opacity': '0.4'});
                    if(data.wait_resume) {
                        if (data.next_resume_time !== 'get next resume time failed.') {
                            mwp_wpvivid_resume_backup_addon(mwp_wpvivid_running_backup_taskid, data.next_resume_time);
                        }
                        else {
                            wpvivid_delete_backup_task(mwp_wpvivid_running_backup_taskid);
                        }
                    }
                }
                else {
                    if(!mwp_wpvivid_prepare_backup) {
                        jQuery('#mwp_wpvivid_backup_cancel_btn_addon').css({'pointer-events': 'auto', 'opacity': '1'});
                        jQuery('#mwp_wpvivid_backup_btn_addon').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    mwp_wpvivid_running_backup_taskid='';
                }
                if (!b_has_data) {
                    mwp_task_retry_times++;
                    if (mwp_task_retry_times < 5) {
                        mwp_wpvivid_need_update = true;
                    }
                }
            }

            function mwp_wpvivid_switch_staging(){
                <?php
                $white_label_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option(sanitize_text_field($_GET['id']), 'white_label_setting', array());
                if(!$white_label_setting){
                    $location = 'admin.php?page=wpvivid-staging&from-mainwp';
                }
                else{
                    $slug_page = strtolower($white_label_setting['white_label_slug']);
                    $location = 'admin.php?page='.$slug_page.'-staging&from-mainwp';
                }
                ?>
                location.href="admin.php?page=SiteOpen&newWindow=yes&websiteid=<?php echo esc_html($this->site_id); ?>&location=<?php echo esc_html(base64_encode($location)); ?>";
            }

            function mwp_wpvivid_manage_task_addon() {
                if(mwp_wpvivid_need_update === true){
                    mwp_wpvivid_need_update = false;
                    mwp_wpvivid_list_task_addon();
                }
                else{
                    setTimeout(function(){
                        mwp_wpvivid_manage_task_addon();
                    }, 3000);
                }
            }

            function mwp_wpvivid_active_cron_addon(){
                var next_get_time = 3 * 60 * 1000;
                mwp_wpvivid_cron_task_addon();
                setTimeout("mwp_wpvivid_active_cron_addon()", next_get_time);
                setTimeout(function(){
                    mwp_wpvivid_need_update=true;
                }, 10000);
            }

            function mwp_wpvivid_cron_task_addon(){
                var site_url = '<?php echo esc_url(home_url()); ?>';
                jQuery.get(site_url+'/wp-cron.php');
            }

            function mwp_wpvivid_resume_backup_addon(backup_id, next_resume_time){
                if(next_resume_time < 0){
                    next_resume_time = 0;
                }
                next_resume_time = next_resume_time * 1000;
                setTimeout("mwp_wpvivid_cron_task_addon()", next_resume_time);
                setTimeout(function(){
                    mwp_task_retry_times = 0;
                    mwp_wpvivid_need_update=true;
                }, next_resume_time);
            }

            jQuery(document).ready(function(){
                mwp_wpvivid_refresh_custom_backup_info();
                mwp_wpvivid_active_cron_addon();
                mwp_wpvivid_manage_task_addon();
            });
        </script>
        <?php
    }

    function mwp_wpvivid_backup_progress(){
        if($this->setting['wpvivid_common_setting']['estimate_backup'])
        {
            $mwp_wpvivid_setting_estimate_backup='';
        }
        else{
            $mwp_wpvivid_setting_estimate_backup='display: none;';
        }
        ?>

        <div class="postbox" id="mwp_wpvivid_backup_progress" style="display: none;">
            <div class="mwp-action-progress-bar">
                <div class="mwp-action-progress-bar-percent" style="height:24px;width:0"></div>
            </div>
            <div style="float: left; <?php esc_attr_e($mwp_wpvivid_setting_estimate_backup); ?>">
                <div class="mwp-backup-basic-info"><span class="mwp-wpvivid-span"><?php _e('Database Size:', 'mainwp-wpvivid-extension'); ?></span><span class="mwp-wpvivid-span">N/A</span></div>
                <div class="mwp-backup-basic-info"><span class="mwp-wpvivid-span"><?php _e('File Size:', 'mainwp-wpvivid-extension'); ?></span><span class="mwp-wpvivid-span">N/A</span></div>
            </div>
            <div style="float: left;">
                <div class="mwp-backup-basic-info"><span class="mwp-wpvivid-span"><?php _e('Total Size:', 'mainwp-wpvivid-extension'); ?></span><span class="mwp-wpvivid-span">N/A</span></div>
                <div class="mwp-backup-basic-info"><span class="mwp-wpvivid-span"><?php _e('Uploaded:', 'mainwp-wpvivid-extension'); ?></span><span class="mwp-wpvivid-span">N/A</span></div>
                <div class="mwp-backup-basic-info"><span class="mwp-wpvivid-span"><?php _e('Speed:', 'mainwp-wpvivid-extension'); ?></span><span class="mwp-wpvivid-span">N/A</span></div>
            </div>
            <div style="float: left;">
                <div class="mwp-backup-basic-info"><span class="mwp-wpvivid-span"><?php _e('Network Connection:', 'mainwp-wpvivid-extension'); ?></span><span class="mwp-wpvivid-span">N/A</span></div>
            </div>
            <div style="clear:both;"></div>
            <div style="margin-left:10px; float: left; width:100%;"><p id="mwp_wpvivid_current_doing"></p></div>
            <div style="clear: both;"></div>
            <div>
                <div class="mwp-backup-log-btn" style="float: left;"><input class="ui green mini button" id="mwp_wpvivid_backup_cancel_btn" type="button" value="<?php esc_attr_e( 'Cancel', 'mainwp-wpvivid-extension' ); ?>" onclick="mwp_wpvivid_cancel_backup();" /></div>
                <div class="mwp-backup-log-btn" style="float: left;"><input class="ui green mini button" type="button" value="<?php esc_attr_e( 'Log', 'mainwp-wpvivid-extension' ); ?>" onclick="mwp_wpvivid_read_log('mwp_wpvivid_view_backup_task_log');" /></div>
                <div style="clear: both;"></div>
            </div>
            <div style="clear: both;"></div>
        </div>
        <script>
            function mwp_wpvivid_cancel_backup(){
                var ajax_data= {
                    'action': 'mwp_wpvivid_backup_cancel',
                    'site_id':'<?php echo esc_html($this->site_id); ?>'
                };
                jQuery('#mwp_wpvivid_backup_cancel_btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                mwp_wpvivid_post_request(ajax_data, function(data){
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        jQuery('#mwp_wpvivid_current_doing').html(jsonarray.msg);
                    }
                    catch(err){
                        alert(err);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown) {
                    jQuery('#mwp_wpvivid_backup_cancel_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                    var error_message = mwp_wpvivid_output_ajaxerror('cancelling the backup', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        </script>
        <?php
    }

    function mwp_wpvivid_backup_manual(){
        ?>
        <div class="mwp-wpvivid-block-bottom-space" id="mwp_wpvivid_backup_notice"></div>
        <div class="postbox mwp-quickbackup" style="margin-bottom: 10px;">
            <h2><span><?php _e( 'Back Up Manually','mainwp-wpvivid-extension'); ?></span></h2>
            <div class="mwp-quickstart-storage-setting">
                <span class="mwp-list-top-chip backup" name="ismerge" value="1"><?php _e('Child-Site Storage Directory: ', 'mainwp-wpvivid-extension'); ?></span>
                <span class="mwp-list-top-chip"><?php _e('http(s)://child-site/wp-content/'); ?><?php _e($this->setting['wpvivid_local_setting']['path']); ?></span>
                <span class="mwp-list-top-chip"><a href="#" onclick="mwp_switch_wpvivid_tab('setting');"><?php _e(' rename directory', 'mainwp-wpvivid-extension'); ?></a></span>
            </div>

            <div class="mwp-quickstart-archive-block">
                <legend class="screen-reader-text"><span>input type="radio"</span></legend>
                <label>
                    <input type="radio" option="mwp_backup" name="mwp_backup_files" value="files+db" checked />
                    <span><?php _e( 'Database + Files (WordPress Files)', 'mainwp-wpvivid-extension' ); ?></span>
                </label><br>
                <label>
                    <input type="radio" option="mwp_backup" name="mwp_backup_files" value="files" />
                    <span><?php _e( 'WordPress Files (Exclude Database)', 'mainwp-wpvivid-extension' ); ?></span>
                </label><br>
                <label>
                    <input type="radio" option="mwp_backup" name="mwp_backup_files" value="db" />
                    <span><?php _e( 'Only Database', 'mainwp-wpvivid-extension' ); ?></span>
                </label><br>
                <label style="display: none;">
                    <input type="checkbox" option="mwp_backup" name="mwp_ismerge" value="1" checked />
                </label><br>
            </div>

            <div class="mwp-quickstart-storage-block">
                <legend class="screen-reader-text"><span>input type="checkbox"</span></legend>
                <label>
                    <input type="radio" option="mwp_backup_ex" name="mwp_local_remote" value="local" checked />
                    <span><?php _e( 'Save Backups to Child-Site Local', 'mainwp-wpvivid-extension' ); ?></span>
                </label>

                <div style="clear:both;"></div>
                <label>
                    <input type="radio" option="mwp_backup_ex" name="mwp_local_remote" value="remote" />
                    <span><?php _e( 'Send Backup to Remote Storage', 'mainwp-wpvivid-extension' ); ?></span>
                </label><br>
                <div id="mwp_wpvivid_upload_storage" style="cursor:pointer;" title="Highlighted icon illuminates that you have choosed a remote storage to store backups">
                </div>
            </div>

            <div class="mwp-quickstart-btn" style="padding-top:20px;">
                <div class="mwp-wpvivid-block-bottom-space">
                    <input class="ui green mini button mwp-quickbackup-btn" id="mwp_wpvivid_backup_btn"  style="margin: 0 auto !important;" type="button" value="<?php esc_attr_e( 'Backup Now', 'mainwp-wpvivid-extension'); ?>" onclick="mwp_wpvivid_prepare_backup();" />
                </div>
                <div class="mwp-schedule-tab-block" style="text-align:center;">
                    <fieldset>
                        <label>
                            <input type="checkbox" option="mwp_backup" name="mwp_lock" />
                            <span><?php _e( 'This backup can only be deleted manually', 'mainwp-wpvivid-extension' ); ?></span>
                        </label>
                    </fieldset>
                </div>
            </div>

            <div class="mwp-custom-info" style="float:left; width:100%;">
                <strong><?php _e('Tips', 'mainwp-wpvivid-extension'); ?></strong><?php _e(': The settings is only for manual backup, which won\'t affect schedule settings.', 'mainwp-wpvivid-extension'); ?>
            </div>
        </div>
        <script>
            function mwp_wpvivid_get_default_remote(){
                var ajax_data={
                    'action': 'mwp_wpvivid_get_default_remote',
                    'site_id':'<?php echo esc_html($this->site_id); ?>'
                };

                mwp_wpvivid_post_request(ajax_data, function (data){
                    try {
                        var json = jQuery.parseJSON(data);
                        if (json.result === 'success')
                        {
                            if(json.default_remote_storage === ''){
                                mwp_wpvivid_has_remote = false;
                            }
                            jQuery('#mwp_wpvivid_upload_storage').html(json.default_remote_pic);
                            jQuery('#mwp_schedule_upload_storage').html(json.default_remote_pic);
                            jQuery('#mwp_wpvivid_schedule_upload_storage').html(json.default_remote_pic);
                        }
                    }
                    catch(err)
                    {
                        mwp_wpvivid_get_default_remote();
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    mwp_wpvivid_get_default_remote();
                });
            }
            mwp_wpvivid_get_default_remote();

            jQuery('input:radio[option=mwp_backup_ex][name=mwp_local_remote]').click(function(){
                var value = jQuery(this).prop('value');
                if(value === 'remote'){
                    if(!mwp_wpvivid_has_remote){
                        alert('There is no default remote storage configured. Please set it up first.');
                        jQuery('input:radio[option=mwp_backup_ex][name=mwp_local_remote][value=local]').prop('checked', true);
                    }
                }
            });

            function mwp_wpvivid_prepare_backup()
            {
                mwp_wpvivid_clear_notice('mwp_wpvivid_backup_notice');
                var backup_data = mwp_wpvivid_ajax_data_transfer('mwp_backup');
                backup_data = JSON.parse(backup_data);
                jQuery('input:radio[option=mwp_backup_ex]').each(function() {
                    if(jQuery(this).prop('checked'))
                    {
                        var key = jQuery(this).prop('name');
                        var value = jQuery(this).prop('value');
                        var json = new Array();
                        if(value == 'local'){
                            json['mwp_local']='1';
                            json['mwp_remote']='0';
                        }
                        else if(value == 'remote'){
                            json['mwp_local']='0';
                            json['mwp_remote']='1';
                        }
                    }
                    jQuery.extend(backup_data, json);
                });

                var ajax_data={
                    'action': 'mwp_wpvivid_prepare_backup',
                    'site_id':'<?php echo esc_html($this->site_id); ?>',
                    'backup': backup_data
                };
                jQuery('#mwp_wpvivid_backup_btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var json = jQuery.parseJSON(data);
                        if (json.result === 'success')
                        {
                            mwp_wpvivid_backup_now(json.data);
                        }
                        else
                        {
                            mwp_wpvivid_add_notice('Backup', 'Error', json.error);
                            jQuery('#mwp_wpvivid_backup_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                        }
                    }
                    catch(err)
                    {
                        mwp_wpvivid_add_notice('Backup', 'Error', err);
                        jQuery('#mwp_wpvivid_backup_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    mwp_wpvivid_add_notice('Backup', 'Error', errorThrown);
                    jQuery('#mwp_wpvivid_backup_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                });
            }

            function mwp_wpvivid_backup_now(task_id) {
                var ajax_data={
                    'action': 'mwp_wpvivid_backup_now',
                    'site_id':'<?php echo esc_html($this->site_id); ?>',
                    'task_id':task_id
                };
                mwp_wpvivid_need_update = true;
                mwp_wpvivid_post_request(ajax_data, function (data) {
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                });
            }
        </script>
        <?php
    }

    function mwp_wpvivid_backup_schedule(){
        ?>
        <div class="postbox mwp-qucikbackup-schedule" style="margin-bottom: 10px;">
            <h2><span><?php _e( 'Backup Schedule','mainwp-wpvivid-extension'); ?></span></h2>
            <div class="mwp-schedule-block" id="mwp_wpvivid_backup_schedule">
            </div>
        </div>
        <div style="clear:both;"></div>
        <script>
            function mwp_wpvivid_get_backup_schedule(){
                var ajax_data={
                    'action': 'mwp_wpvivid_get_backup_schedule',
                    'site_id':'<?php echo esc_html($this->site_id); ?>'
                };
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var json = jQuery.parseJSON(data);
                        if (json.result === 'success') {
                            jQuery('#mwp_wpvivid_backup_schedule').html(json.schedule_html);
                        }
                        else {
                            alert(json.error);
                        }
                    }
                    catch(err) {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    setTimeout(function () {
                        mwp_wpvivid_get_backup_schedule();
                    }, 3000);
                });
            }
            mwp_wpvivid_get_backup_schedule();
        </script>
        <?php
    }

    function mwp_wpvivid_backup_list(){
        ?>
        <h2 class="nav-tab-wrapper mwp-wpvivid-intab" id="wpvivid_backup_tab" style="padding-bottom:0!important;">
            <?php
            $this->mwp_wpvivid_tab_backup_list();
            $this->mwp_wpvivid_tab_log();
            ?>
        </h2>
        <script>
            function mwp_wpvivid_switchrestoreTabs(evt,contentName) {
                // Declare all variables
                var i, tabcontent, tablinks;

                // Get all elements with class="table-list-content" and hide them
                tabcontent = document.getElementsByClassName("mwp-wpvivid-backup-tab-content");
                for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                }

                // Get all elements with class="table-nav-tab" and remove the class "nav-tab-active"
                tablinks = document.getElementsByClassName("mwp-wpvivid-backup-nav-tab");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" nav-tab-active", "");
                }

                // Show the current tab, and add an "storage-menu-active" class to the button that opened the tab
                document.getElementById(contentName).style.display = "block";
                evt.currentTarget.className += " nav-tab-active";
            }
        </script>
        <?php
        $this->mwp_wpvivid_page_backup_list();
        $this->mwp_wpvivid_page_log();
        ?>
        <?php
    }

    function mwp_wpvivid_tab_backup_list(){
        ?>
        <a href="#" id="mwp_wpvivid_tab_backup_list" class="nav-tab mwp-wpvivid-backup-nav-tab nav-tab-active" onclick="mwp_wpvivid_switchrestoreTabs(event,'mwp-page-backuplist')" style="background: #ffffff;"><?php _e('Backups', 'mainwp-wpvivid-extension'); ?></a>
        <?php
    }

    function mwp_wpvivid_tab_log(){
        ?>
        <a href="#" id="mwp_wpvivid_tab_backup_log" class="nav-tab mwp-wpvivid-backup-nav-tab delete" onclick="mwp_wpvivid_switchrestoreTabs(event,'mwp-page-log')" style="display: none; background: #ffffff;">
            <div style="margin-right: 15px;"><?php _e('Log', 'mainwp-wpvivid-extension'); ?></div>
            <div class="mwp-nav-tab-delete-img">
                <img src="<?php echo esc_url(plugins_url( 'images/delete-tab.png', __FILE__ )); ?>" style="vertical-align:middle; cursor:pointer;" onclick="mwp_wpvivid_close_tab(event, 'mwp_wpvivid_tab_backup_log', 'mwp-wpvivid-backup', 'mwp_wpvivid_tab_backup_list');" />
            </div>
        </a>
        <?php
    }

    function mwp_wpvivid_page_backup_list(){
        ?>
        <div class="mwp-wpvivid-backup-tab-content mwp_wpvivid_tab_backup_list" id="mwp-page-backuplist" style="border-top: none;">
            <table class="wp-list-table widefat plugins" style="border-collapse: collapse; border-top: none;">
                <thead>
                <tr style="border-bottom: 0;">
                    <td></td>
                    <th><?php _e( 'Backup','mainwp-wpvivid-extension'); ?></th>
                    <th><?php _e( 'Storage','mainwp-wpvivid-extension'); ?></th>
                    <th><?php _e( 'Download','mainwp-wpvivid-extension'); ?></th>
                    <th><?php _e( 'Restore','mainwp-wpvivid-extension'); ?></th>
                    <th><?php _e( 'Delete','mainwp-wpvivid-extension'); ?></th>
                </tr>
                </thead>
                <tbody class="mwp-wpvivid-backuplist" id="mwp_wpvivid_backuplist">

                </tbody>
                <tfoot>
                <tr>
                    <th><input type="checkbox" id="mwp_wpvivid_backuplist_all_check" value="1" onclick="mwp_wpvivid_select_inbatches();" /></th>
                    <th class="row-title" colspan="5"><a onclick="mwp_wpvivid_delete_backups_inbatches();" style="cursor: pointer;"><?php _e('Delete the selected backups', 'mainwp-wpvivid-extension'); ?></a></th>
                </tr>
                </tfoot>
            </table>
        </div>
        <script>
            function mwp_wpvivid_initialize_restore(backup_id, backup_time, backup_type, restore_type='backup'){
                <?php
                $location = 'admin.php?page=WPvivid';
                ?>
                location.href="admin.php?page=SiteOpen&newWindow=yes&websiteid=<?php echo esc_html($this->site_id); ?>&location=<?php echo esc_html(base64_encode($location)); ?>";
            }

            function mwp_wpvivid_reset_backup_list(){
                jQuery('#mwp_wpvivid_backuplist tr').each(function(i){
                    jQuery(this).children('td').each(function (j) {
                        if (j == 2) {
                            var backup_id = jQuery(this).parent().children('th').find("input[type=checkbox]").attr("id");
                            var download_btn = '<div id="wpvivid_file_part_' + backup_id + '" style="float:left;padding:10px 10px 10px 0px;">' +
                                '<div style="cursor:pointer;" onclick="mwp_wpvivid_initialize_download(\'' + backup_id + '\');" title="Prepare to download the backup">' +
                                '<img id="wpvivid_download_btn_' + backup_id + '" src="<?php echo esc_url(plugins_url( 'images/download.png', __FILE__ )); ?>" style="vertical-align:middle;" />Download' +
                                '<div class="spinner" id="wpvivid_download_loading_' + backup_id + '" style="float:right;width:auto;height:auto;padding:10px 180px 10px 0;background-position:0 0;"></div>' +
                                '</div>' +
                                '</div>';
                            jQuery(this).html(download_btn);
                        }
                    });
                });
            }

            function mwp_wpvivid_delete_backup(backup_id){
                var name = '';
                jQuery('#mwp_wpvivid_backuplist tr').each(function(i){
                    jQuery(this).children('td').each(function (j) {
                        if (j == 0) {
                            var id = jQuery(this).parent().children('th').find("input[type=checkbox]").attr("id");
                            if(id === backup_id){
                                name = jQuery(this).parent().children('td').eq(0).find('img').attr('name');
                            }
                        }
                    });
                });
                var descript = '';
                var force_del = 0;
                if(name === 'lock') {
                    descript = '<?php _e('This backup is locked, are you sure to remove it? This backup will be deleted permanently from your hosting (localhost) and remote storages.', 'mainwp-wpvivid-extension'); ?>';
                    force_del = 1;
                }
                else{
                    descript = '<?php _e('Are you sure to remove this backup? This backup will be deleted permanently from your hosting (localhost) and remote storages.', 'mainwp-wpvivid-extension'); ?>';
                    force_del = 0;
                }
                var ret = confirm(descript);
                if(ret === true){
                    var ajax_data={
                        'action': 'mwp_wpvivid_delete_backup',
                        'site_id':'<?php echo esc_html($this->site_id); ?>',
                        'backup_id': backup_id,
                        'force': force_del
                    };
                    mwp_wpvivid_post_request(ajax_data, function(data){
                        try {
                            var json = jQuery.parseJSON(data);
                            if (json.result === 'success') {
                                jQuery('#mwp_wpvivid_backuplist').html('');
                                jQuery('#mwp_wpvivid_backuplist').append(json.backup_list);
                            }
                            else {
                                alert(json.error);
                            }
                        }
                        catch(err) {
                            alert(err);
                        }
                    }, function(XMLHttpRequest, textStatus, errorThrown) {
                        var error_message = mwp_wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                        alert(error_message);
                    });
                }
            }

            function mwp_wpvivid_click_check_backup(backup_id){
                var name = "";
                var all_check = true;
                jQuery('#mwp_wpvivid_backuplist tr').each(function (i) {
                    jQuery(this).children('th').each(function (j) {
                        if(j === 0) {
                            var id = jQuery(this).find("input[type=checkbox]").attr("id");
                            if (id === backup_id) {
                                name = jQuery(this).parent().children('td').eq(0).find("img").attr("name");
                                if (name === "unlock") {
                                    if (jQuery(this).find("input[type=checkbox]").prop('checked') === false) {
                                        all_check = false;
                                    }
                                }
                                else {
                                    jQuery(this).find("input[type=checkbox]").prop('checked', false);
                                    all_check = false;
                                }
                            }
                            else {
                                if (jQuery(this).find("input[type=checkbox]").prop('checked') === false) {
                                    all_check = false;
                                }
                            }
                        }
                    });
                });
                if(all_check === true){
                    jQuery('#mwp_wpvivid_backuplist_all_check').prop('checked', true);
                }
                else{
                    jQuery('#mwp_wpvivid_backuplist_all_check').prop('checked', false);
                }
            }

            function mwp_wpvivid_select_inbatches(){
                var name = '';
                if(jQuery('#mwp_wpvivid_backuplist_all_check').prop('checked')) {
                    jQuery('#mwp_wpvivid_backuplist tr').each(function (i) {
                        jQuery(this).children('th').each(function (j) {
                            if (j == 0) {
                                name = jQuery(this).parent().children('td').eq(0).find("img").attr("name");
                                if(name === 'unlock') {
                                    jQuery(this).find("input[type=checkbox]").prop('checked', true);
                                }
                                else{
                                    jQuery(this).find("input[type=checkbox]").prop('checked', false);
                                }
                            }
                        });
                    });
                }
                else{
                    jQuery('#mwp_wpvivid_backuplist tr').each(function (i) {
                        jQuery(this).children('th').each(function (j) {
                            if (j == 0) {
                                jQuery(this).find("input[type=checkbox]").prop('checked', false);
                            }
                        });
                    });
                }
            }

            function mwp_wpvivid_delete_backups_inbatches(){
                var delete_backup_array = new Array();
                var count = 0;
                jQuery('#mwp_wpvivid_backuplist tr').each(function (i) {
                    jQuery(this).children('th').each(function (j) {
                        if (j == 0) {
                            if(jQuery(this).find('input[type=checkbox]').prop('checked')){
                                delete_backup_array[count] = jQuery(this).find('input[type=checkbox]').attr('id');
                                count++;
                            }
                        }
                    });
                });
                if( count === 0 ){
                    alert('<?php _e('Please select at least one item.','mainwp-wpvivid-extension'); ?>');
                }
                else {
                    var descript = '<?php _e('Are you sure to remove the selected backups? These backups will be deleted permanently from your hosting (localhost).', 'mainwp-wpvivid-extension'); ?>';
                    var ret = confirm(descript);
                    if (ret === true) {
                        var ajax_data = {
                            'action': 'mwp_wpvivid_delete_backup_array',
                            'site_id':'<?php echo esc_html($this->site_id); ?>',
                            'backup_id': delete_backup_array
                        };
                        mwp_wpvivid_post_request(ajax_data, function (data) {
                            try {
                                var json = jQuery.parseJSON(data);
                                if (json.result === 'success') {
                                    jQuery('#mwp_wpvivid_backuplist_all_check').prop('checked', false);
                                    jQuery('#mwp_wpvivid_backuplist').html('');
                                    jQuery('#mwp_wpvivid_backuplist').append(json.backup_list);
                                }
                                else {
                                    alert(json.error);
                                }
                            }
                            catch(err) {
                                alert(err);
                            }
                        }, function (XMLHttpRequest, textStatus, errorThrown) {
                            var error_message = mwp_wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                            alert(error_message);
                        });
                    }
                }
            }

            function mwp_wpvivid_set_backup_lock(backup_id, lock_status){
                if(lock_status === "lock"){
                    var lock=0;
                }
                else{
                    var lock=1;
                }
                var ajax_data={
                    'action': 'mwp_wpvivid_set_security_lock',
                    'site_id':'<?php echo esc_html($this->site_id); ?>',
                    'backup_id': backup_id,
                    'lock': lock
                };
                mwp_wpvivid_post_request(ajax_data, function(data) {
                    try {
                        var json = jQuery.parseJSON(data);
                        if (json.result === 'success') {
                            jQuery('#mwp_wpvivid_backuplist').html('');
                            jQuery('#mwp_wpvivid_backuplist').append(json.backup_list);
                        }
                    }
                    catch(err){
                        alert(err);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = mwp_wpvivid_output_ajaxerror('setting up a lock for the backup', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function mwp_wpvivid_get_backup_list(){
                var ajax_data={
                    'action': 'mwp_wpvivid_get_backup_list',
                    'site_id': '<?php echo esc_html($this->site_id); ?>'
                };
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var json = jQuery.parseJSON(data);
                        if (json.result === 'success') {
                            jQuery('#mwp_wpvivid_backuplist').html('');
                            jQuery('#mwp_wpvivid_backuplist').append(json.backup_list);
                        }
                        else {
                            alert(json.error);
                        }
                    }
                    catch(err) {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    setTimeout(function () {
                        mwp_wpvivid_get_backup_list();
                    }, 3000);
                });
            }
            mwp_wpvivid_get_backup_list();
        </script>
        <?php
    }

    function mwp_wpvivid_page_log(){
        ?>
        <div class="mwp-wpvivid-backup-tab-content mwp_wpvivid_tab_backup_log" id="mwp-page-log" style="display:none; border-top: none;">
            <div class="postbox mwp-restore_log" id="wpvivid_display_log_content" style="border-top: none;">
                <div></div>
            </div>
        </div>
        <?php
    }

    function mwp_wpvivid_backup_js(){
        ?>
        <script>
            mwp_wpvivid_activate_cron();

            function mwp_wpvivid_cron_task(){
                var site_url = '<?php echo esc_url(home_url()); ?>';
                jQuery.get(site_url+'/wp-cron.php');
            }

            function mwp_wpvivid_resume_backup(backup_id, next_resume_time){
                if(next_resume_time < 0){
                    next_resume_time = 0;
                }
                next_resume_time = next_resume_time * 1000;
                setTimeout("mwp_wpvivid_cron_task()", next_resume_time);
                setTimeout(function(){
                    task_retry_times = 0;
                    mwp_wpvivid_need_update=true;
                }, next_resume_time);
            }

            function mwp_wpvivid_get_status()
            {
                var ajax_data={
                    'action': 'mwp_wpvivid_get_status',
                    'site_id':'<?php echo esc_html($this->site_id); ?>'
                };
                mwp_wpvivid_post_request(ajax_data, function (data)
                {
                    try {
                        var json = jQuery.parseJSON(data);
                        if (json.result === 'success')
                        {
                            if(json.data.length !== 0) {
                                jQuery.each(json.data, function (index, value) {
                                    if (value.status.str == 'ready'){
                                        jQuery('#mwp_wpvivid_backup_progress').show();
                                        jQuery('#mwp_wpvivid_backup_progress').html(json.html);
                                        mwp_wpvivid_need_update = true;
                                    }
                                    else if (value.status.str == 'running') {
                                        mwp_running_backup_taskid = index;
                                        jQuery('#mwp_wpvivid_backup_progress').show();
                                        jQuery('#mwp_wpvivid_backup_progress').html(json.html);
                                        mwp_wpvivid_need_update = true;
                                        jQuery('#mwp_wpvivid_backup_btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                                    }
                                    else if (value.status.str == 'wait_resume'){
                                        mwp_running_backup_taskid = index;
                                        jQuery('#mwp_wpvivid_backup_progress').show();
                                        jQuery('#mwp_wpvivid_backup_progress').html(json.html);
                                        mwp_wpvivid_resume_backup(index, value.data.next_resume_time);
                                        jQuery('#mwp_wpvivid_backup_btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                                    }
                                    else if (value.status.str === 'no_responds') {
                                        mwp_running_backup_taskid = index;
                                        jQuery('#mwp_wpvivid_backup_progress').show();
                                        jQuery('#mwp_wpvivid_backup_progress').html(value.progress_html);
                                        mwp_wpvivid_need_update = true;
                                        jQuery('#mwp_wpvivid_backup_btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                                    }
                                    else if (value.status.str === 'completed') {
                                        mwp_running_backup_taskid = '';
                                        jQuery('#mwp_wpvivid_backup_progress').html(value.progress_html);
                                        jQuery('#mwp_wpvivid_backup_progress').hide();
                                        mwp_wpvivid_get_backup_list();
                                        mwp_wpvivid_get_backup_schedule();
                                        mwp_wpvivid_need_update = true;
                                        jQuery('#mwp_wpvivid_backup_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                                        mwp_wpvivid_add_notice('Backup', 'Success', '');
                                    }
                                    else if (value.status.str === 'error') {
                                        mwp_running_backup_taskid = '';
                                        jQuery('#mwp_wpvivid_backup_progress').html(value.progress_html);
                                        jQuery('#mwp_wpvivid_backup_progress').hide();
                                        mwp_wpvivid_get_backup_list();
                                        mwp_wpvivid_get_backup_schedule();
                                        mwp_wpvivid_need_update = true;
                                        jQuery('#mwp_wpvivid_backup_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                                        var error_info = "Backup error: " + value.status.error + ", task id: " + index;
                                        mwp_wpvivid_add_notice('Backup', 'Error', error_info);
                                    }
                                    else {
                                        jQuery('#mwp_wpvivid_backup_progress').hide();
                                        jQuery('#mwp_wpvivid_backup_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                                    }
                                });
                            }
                            else{
                                jQuery('#mwp_wpvivid_backup_progress').hide();
                                jQuery('#mwp_wpvivid_backup_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                            }
                        }
                        else
                        {
                            alert(json.error);
                        }
                    }
                    catch(err)
                    {
                        jQuery('#wpvivid_ajax_result').html(err);
                    }
                    setTimeout(function ()
                    {
                        mwp_wpvivid_manage_task();
                    }, 3000);
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#wpvivid_ajax_result').html(errorThrown);
                    setTimeout(function ()
                    {
                        mwp_wpvivid_get_status();
                    }, 3000);
                });

            }

            function mwp_wpvivid_manage_task() {
                if(mwp_wpvivid_need_update === true){
                    mwp_wpvivid_need_update = false;
                    mwp_wpvivid_get_status();
                }
                else{
                    setTimeout(function(){
                        mwp_wpvivid_manage_task();
                    }, 3000);
                }
            }
            mwp_wpvivid_manage_task();
        </script>
        <?php
    }
}

class Mainwp_WPvivid_Custom_Backup_List
{
    public $parent_id;
    private $backup_custom_setting;

    public function __construct($backup_custom_setting = array()){
        $this->backup_custom_setting = $backup_custom_setting;
    }

    public function set_parent_id($parent_id){
        $this->parent_id = $parent_id;
    }

    public function display_rows(){
        $core_descript = 'Choose if to back up the WordPress core files. <a href="https://wpvivid.com/wpvivid-backup-pro-wordpress-core-backup" target="_blank">Learn more</a>';
        $database_descript = 'Select which tables of database you want to back up. <a href="https://wpvivid.com/wpvivid-backup-pro-database-backup" target="_blank">Learn more</a>';
        $themes_plugins_descript = 'Specify which themes or plugins you want to back up. In order to save server resources, only the activated themes and all plugins are checked by default. <a href="https://wpvivid.com/wpvivid-backup-pro-wordpress-themes-and-plugins-backup" target="_blank">Learn more</a>';
        $uploads_descript = '<strong style="text-decoration:underline;"><i>Exclude</i></strong> folders you do not want to back up under wp-content/uploads folder.';
        $content_descript = '<strong style="text-decoration:underline;"><i>Exclude</i></strong> folders you do not want to back up under wp-content folder, except for the wp-content/uploads folder. <a href="https://wpvivid.com/wpvivid-backup-pro-wp-content-backup" target="_blank">Learn more</a>';
        $additional_folder_descript = '<strong style="text-decoration:underline;"><i>Include</i></strong> additional files or folders you want to back up. <a href="https://wpvivid.com/wpvivid-backup-pro-additional-files-folders-backup" target="_blank">Learn more</a>';
        $additional_database_descript = '<strong style="text-decoration:underline;"><i>Include</i></strong> additional databases you want to back up.';

        $core_check = 'checked';
        $database_check = 'checked';
        $database_text_style = 'pointer-events: auto; opacity: 1;';
        $themes_plugins_check = 'checked';
        $themes_plugins_text_style = 'pointer-events: auto; opacity: 1;';
        $themes_check = 'checked';
        $plugins_check = 'checked';
        $uploads_check = 'checked';
        $uploads_text_style = 'pointer-events: auto; opacity: 1;';
        $content_check = 'checked';
        $content_text_style = 'pointer-events: auto; opacity: 1;';
        $additional_folder_check = '';
        $additional_folder_text_style = 'pointer-events: none; opacity: 0.4;';
        $additional_database_check = '';
        $additional_database_text_style = 'pointer-events: none; opacity: 0.4;';
        $upload_extension = '';
        $content_extension = '';
        $other_extension = '';

        if(!empty($this->backup_custom_setting)){
            if(isset($this->backup_custom_setting['core_option']['core_check'])){
                $core_check = $this->backup_custom_setting['core_option']['core_check'] === '1' ? 'checked' : '';
            }
            if(isset($this->backup_custom_setting['database_option']['database_check'])){
                $database_check = $this->backup_custom_setting['database_option']['database_check'] === '1' ? 'checked' : '';
                $database_text_style = $this->backup_custom_setting['database_option']['database_check'] === '1' ? 'pointer-events: auto; opacity: 1;' : 'pointer-events: none; opacity: 0.4;';
            }
            if(isset($this->backup_custom_setting['themes_option']['themes_check'])){
                $themes_check = $this->backup_custom_setting['themes_option']['themes_check'] === '1' ? 'checked' : '';
            }
            if(isset($this->backup_custom_setting['plugins_option']['plugins_check'])){
                $plugins_check = $this->backup_custom_setting['plugins_option']['plugins_check'] === '1' ? 'checked' : '';
            }
            if($themes_check === '' && $plugins_check === ''){
                $themes_plugins_check = '';
                $themes_plugins_text_style = 'pointer-events: none; opacity: 0.4;';
            }
            if(isset($this->backup_custom_setting['uploads_option']['uploads_check'])){
                $uploads_check = $this->backup_custom_setting['uploads_option']['uploads_check'] === '1' ? 'checked' : '';
                $uploads_text_style = $this->backup_custom_setting['uploads_option']['uploads_check'] === '1' ? 'pointer-events: auto; opacity: 1;' : 'pointer-events: none; opacity: 0.4;';
            }
            if(isset($this->backup_custom_setting['content_option']['content_check'])){
                $content_check = $this->backup_custom_setting['content_option']['content_check'] === '1' ? 'checked' : '';
                $content_text_style = $this->backup_custom_setting['content_option']['content_check'] === '1' ? 'pointer-events: auto; opacity: 1;' : 'pointer-events: none; opacity: 0.4;';
            }
            if(isset($this->backup_custom_setting['other_option']['other_check'])){
                $additional_folder_check = $this->backup_custom_setting['other_option']['other_check'] === '1' ? 'checked' : '';
                $additional_folder_text_style = $this->backup_custom_setting['other_option']['other_check'] === '1' ? 'pointer-events: auto; opacity: 1;' : 'pointer-events: none; opacity: 0.4;';
            }
            if(isset($this->backup_custom_setting['additional_database_option']['additional_database_check'])){
                $additional_database_check = $this->backup_custom_setting['additional_database_option']['additional_database_check'] === '1' ? 'checked' : '';
                $additional_database_text_style = $this->backup_custom_setting['additional_database_option']['additional_database_check'] === '1' ? 'pointer-events: auto; opacity: 1;' : 'pointer-events: none; opacity: 0.4;';
            }
            if(isset($this->backup_custom_setting['uploads_option']['uploads_extension_list']) && !empty($this->backup_custom_setting['uploads_option']['uploads_extension_list'])){
                $upload_extension = implode(",", $this->backup_custom_setting['uploads_option']['uploads_extension_list']);
            }
            if(isset($this->backup_custom_setting['content_option']['content_extension_list']) && !empty($this->backup_custom_setting['content_option']['content_extension_list'])){
                $content_extension = implode(",", $this->backup_custom_setting['content_option']['content_extension_list']);
            }
            if(isset($this->backup_custom_setting['other_option']['other_extension_list']) && !empty($this->backup_custom_setting['other_option']['other_extension_list'])){
                $other_extension = implode(",", $this->backup_custom_setting['other_option']['other_extension_list']);
            }
        }
        ?>
        <table class="wp-list-table widefat plugins" style="width: 100%; border: 1px solid #f1f1f1;">
            <tbody>
            <!-- core -->
            <tr>
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-core-check" <?php esc_attr_e($core_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font">Wordpress Core</td>
                <td class="column-description desc"><?php _e($core_descript); ?></td>
            </tr>
            <!-- database -->
            <tr style="cursor: pointer;">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-database-check" <?php esc_attr_e($database_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font mwp-wpvivid-handle-database-detail">Database</td>
                <td class="column-description desc mwp-wpvivid-handle-database-detail"><?php _e($database_descript); ?></td>
                <td class="mwp-wpvivid-handle-database-detail">
                    <details class="primer" onclick="return false;" style="display: inline-block; width: 100%;">
                        <summary title="Show detail" style="float: right; color: #a0a5aa;"></summary>
                    </details>
                </td>
            </tr>
            <tr class="mwp-wpvivid-custom-detail mwp-wpvivid-database-detail mwp-wpvivid-close" style="<?php esc_attr_e($database_text_style); ?> display: none;">
                <th class="check-column"></th>
                <td colspan="3" class="plugin-title column-primary mwp-wpvivid-custom-database-info">
                    <div class="spinner is-active" style="margin: 0 5px 10px 0; float: left;"></div>
                    <div style="float: left;">Archieving database tables</div>
                    <div style="clear: both;"></div>
                </td>
            </tr>
            <!-- themes and plugins -->
            <tr style="cursor: pointer;">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-themes-plugins-check" <?php esc_attr_e($themes_plugins_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font mwp-wpvivid-handle-themes-plugins-detail">Themes and Plugins</td>
                <td class="column-description desc mwp-wpvivid-handle-themes-plugins-detail"><?php _e($themes_plugins_descript); ?></td>
                <th class="mwp-wpvivid-handle-themes-plugins-detail">
                    <details class="primer" onclick="return false;" style="display: inline-block; width: 100%;">
                        <summary title="Show detail" style="float: right; color: #a0a5aa;"></summary>
                    </details>
                </th>
            </tr>
            <tr class="mwp-wpvivid-custom-detail mwp-wpvivid-themes-plugins-detail mwp-wpvivid-close" style="<?php esc_attr_e($themes_plugins_text_style); ?> display: none;">
                <th class="check-column"></th>
                <td colspan="3" class="plugin-title column-primary mwp-wpvivid-custom-themes-plugins-info">
                    <div class="spinner is-active" style="margin: 0 5px 10px 0; float: left;"></div>
                    <div style="float: left;">Archieving themes and plugins</div>
                    <div style="clear: both;"></div>
                </td>
            </tr>
            <!-- uploads -->
            <tr style="cursor: pointer;">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-uploads-check" <?php esc_attr_e($uploads_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font mwp-wpvivid-handle-uploads-detail">wp-content/uploads</td>
                <td class="column-description desc mwp-wpvivid-handle-uploads-detail">
                    <div style="float: left; margin-right: 10px;"><?php _e($uploads_descript); ?></div>
                    <small>
                        <div class="mwp-wpvivid-tooltip" style="float: left; margin-top: 3px; line-height: 100%;">?
                            <div class="mwp-wpvivid-tooltiptext">By default all files and folders will be backed up if there's no item being excluded. <a href="https://wpvivid.com/wpvivid-backup-pro-wordpress-uploads-folder-backup" target="_blank" style="color: #9e5c07;">Learn more</a></div>
                        </div>
                    </small>
                </td>
                <th class="mwp-wpvivid-handle-uploads-detail">
                    <details class="primer" onclick="return false;" style="display: inline-block; width: 100%;">
                        <summary title="Show detail" style="float: right; color: #a0a5aa;"></summary>
                    </details>
                </th>
            </tr>
            <tr class="mwp-wpvivid-custom-detail mwp-wpvivid-uploads-detail mwp-wpvivid-close" style="<?php esc_attr_e($uploads_text_style); ?> display: none;">
                <th class="check-column"></th>
                <td colspan="3" class="plugin-title column-primary">
                    <table class="wp-list-table widefat plugins" style="width:100%;">
                        <thead>
                        <tr>
                            <th class="manage-column column-name column-primary">
                                <label class="mwp-wpvivid-refresh-tree mwp-wpvivid-refresh-uploads-tree" style="margin-bottom: 0; font-size: 13px;">Click Here to Refresh Folder Tree</label>
                            </th>
                            <th class="manage-column column-description" style="font-size: 13px;">Checked Folders or Files to Backup</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="mwp-wpvivid-custom-uploads-left" style="padding-right: 0;">
                                <div class="mwp-wpvivid-custom-uploads-tree">
                                    <div class="mwp-wpvivid-custom-tree mwp-wpvivid-custom-uploads-tree-info"></div>
                                </div>
                            </td>
                            <td class="mwp-wpvivid-custom-uploads-right">
                                <div class="mwp-wpvivid-custom-uploads-table mwp-wpvivid-custom-exclude-uploads-list"</div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2">
                                <div>
                                    <div style="float: left; margin-right: 10px;">
                                        <input class="ui green mini button mwp-wpvivid-exclude-uploads-folder-btn" type="button" value="Exclude Folders" />
                                    </div>
                                    <small>
                                        <div class="mwp-wpvivid-tooltip" style="margin-top: 8px; float: left; line-height: 100%; white-space: normal;">?
                                            <div class="mwp-wpvivid-tooltiptext">Double click to open the folder tree, press Ctrl + left-click to select multiple items.</div>
                                        </div>
                                    </small>
                                    <div style="clear: both;"></div>
                                </div>
                            </td>
                        </tr>
                        </tfoot>
                        <div style="clear:both;"></div>
                    </table>
                    <div style="margin-top: 10px;">
                        <div style="float: left; margin-right: 10px;">
                            <input type="text" class="regular-text mwp-wpvivid-uploads-extension" value="<?php esc_attr_e($upload_extension); ?>" placeholder="Exclude file types, for example: gif,jpg,webp" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_,]/g,'')" />
                            <input type="button" class="mwp-wpvivid-uploads-extension-rule-btn" value="Save" />
                        </div>
                        <small>
                            <div class="mwp-wpvivid-tooltip" style="margin-top: 8px; float: left; line-height: 100%; white-space: normal;">?
                                <div class="mwp-wpvivid-tooltiptext">Exclude file types from the copy. All file types are separated by commas, for example: jpg, gif, tmp etc (without a dot before the file type).</div>
                            </div>
                        </small>
                        <div style="clear: both;"></div>
                    </div>
                </td>
            </tr>
            <!-- content -->
            <tr style="cursor:pointer">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-content-check" <?php esc_attr_e($content_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font mwp-wpvivid-handle-content-detail">wp-content</td>
                <td class="column-description desc mwp-wpvivid-handle-content-detail"><?php _e($content_descript); ?></td>
                <th class="mwp-wpvivid-handle-content-detail">
                    <details class="primer" onclick="return false;" style="display: inline-block; width: 100%;">
                        <summary title="Show detail" style="float: right; color: #a0a5aa;"></summary>
                    </details>
                </th>
            </tr>
            <tr class="mwp-wpvivid-custom-detail mwp-wpvivid-content-detail mwp-wpvivid-close" style="<?php esc_attr_e($content_text_style); ?> display: none;">
                <th class="check-column"></th>
                <td colspan="3" class="plugin-title column-primary">
                    <table class="wp-list-table widefat plugins" style="width:100%;">
                        <thead>
                        <tr>
                            <th class="manage-column column-name column-primary">
                                <label class="mwp-wpvivid-refresh-tree mwp-wpvivid-refresh-content-tree" style="margin-bottom: 0; font-size: 13px;">Click Here to Refresh Folder Tree</label>
                            </th>
                            <th class="manage-column column-description" style="font-size: 13px;">Checked Folders or Files to Backup</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="mwp-wpvivid-custom-uploads-left" style="padding-right: 0;">
                                <div class="mwp-wpvivid-custom-uploads-tree">
                                    <div class="mwp-wpvivid-custom-tree mwp-wpvivid-custom-content-tree-info"></div>
                                </div>
                            </td>
                            <td class="mwp-wpvivid-custom-uploads-right">
                                <div class="mwp-wpvivid-custom-uploads-table mwp-wpvivid-custom-exclude-content-list">
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2">
                                <div style="float: left; margin-right: 10px;">
                                    <input class="ui green mini button mwp-wpvivid-exclude-content-folder-btn" type="button" value="Exclude Folders" />
                                </div>
                                <small>
                                    <div class="mwp-wpvivid-tooltip" style="margin-top: 8px; float: left; line-height: 100%; white-space: normal;">?
                                        <div class="mwp-wpvivid-tooltiptext">Double click to open the folder tree, press Ctrl + left-click to select multiple items.</div>
                                    </div>
                                </small>
                                <div style="clear: both;"></div>
                            </td>
                        </tr>
                        </tfoot>
                        <div style="clear:both;"></div>
                    </table>
                    <div style="margin-top: 10px;">
                        <div style="float: left; margin-right: 10px;">
                            <input type="text" class="regular-text mwp-wpvivid-content-extension" value="<?php esc_attr_e($content_extension); ?>" placeholder="Exclude file types, for example: gif,jpg,webp" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_,]/g,'')" />
                            <input type="button" class="mwp-wpvivid-content-extension-rule-btn" value="Save" />
                        </div>
                        <small>
                            <div class="mwp-wpvivid-tooltip" style="margin-top: 8px; float: left; line-height: 100%; white-space: normal;">?
                                <div class="mwp-wpvivid-tooltiptext">Exclude file types from the copy. All file types are separated by commas, for example: jpg, gif, tmp etc (without a dot before the file type).</div>
                            </div>
                        </small>
                        <div style="clear: both;"></div>
                    </div>
                </td>
            </tr>
            <!-- additional folder -->
            <tr style="cursor:pointer">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-additional-folder-check" <?php esc_attr_e($additional_folder_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font mwp-wpvivid-handle-additional-folder-detail">Additional Files/Folder</td>
                <td class="column-description desc mwp-wpvivid-handle-additional-folder-detail"><?php _e($additional_folder_descript); ?></td>
                <th class="mwp-wpvivid-handle-additional-folder-detail">
                    <details class="primer" onclick="return false;" style="display: inline-block; width: 100%;">
                        <summary title="Show detail" style="float: right; color: #a0a5aa;"></summary>
                    </details>
                </th>
            </tr>
            <tr class="mwp-wpvivid-custom-detail mwp-wpvivid-additional-folder-detail mwp-wpvivid-close" style="<?php esc_attr_e($additional_folder_text_style); ?> display: none;">
                <th class="check-column"></th>
                <td colspan="3" class="plugin-title column-primary">
                    <table class="wp-list-table widefat plugins" style="width:100%;">
                        <thead>
                        <tr>
                            <th class="manage-column column-name column-primary">
                                <label class="mwp-wpvivid-refresh-tree mwp-wpvivid-refresh-additional-folder-tree" style="margin-bottom: 0; font-size: 13px;">Click Here to Refresh Folder/File Tree</label>
                            </th>
                            <th class="manage-column column-description" style="font-size: 13px;">Checked Folders or Files to Backup</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="mwp-wpvivid-custom-uploads-left" style="padding-right: 0;">
                                <div class="mwp-wpvivid-custom-uploads-tree">
                                    <div class="mwp-wpvivid-custom-tree mwp-wpvivid-custom-additional-folder-tree-info"></div>
                                </div>
                            </td>
                            <td class="mwp-wpvivid-custom-uploads-right">
                                <div class="mwp-wpvivid-custom-uploads-table mwp-wpvivid-custom-include-additional-folder-list">
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2">
                                <div style="float: left; margin-right: 10px;">
                                    <input class="ui green mini button mwp-wpvivid-include-additional-folder-btn" type="button" value="Include folders/files" />
                                </div>
                                <small>
                                    <div class="mwp-wpvivid-tooltip" style="margin-top: 8px; float: left; line-height: 100%; white-space: normal;">?
                                        <div class="mwp-wpvivid-tooltiptext">Double click to open the folder tree, press Ctrl + left-click to select multiple items.</div>
                                    </div>
                                </small>
                                <div style="clear: both;"></div>
                            </td>
                        </tr>
                        </tfoot>
                        <div style="clear:both;"></div>
                    </table>
                    <div style="margin-top: 10px;">
                        <div style="float: left; margin-right: 10px;">
                            <input type="text" class="regular-text mwp-wpvivid-additional-folder-extension" value="<?php esc_attr_e($other_extension); ?>" placeholder="Exclude file types, for example: gif,jpg,webp" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_,]/g,'')" />
                            <input type="button" class="mwp-wpvivid-additional-folder-extension-rule-btn" value="Save" />
                        </div>
                        <small>
                            <div class="mwp-wpvivid-tooltip" style="margin-top: 8px; float: left; line-height: 100%; white-space: normal;">?
                                <div class="mwp-wpvivid-tooltiptext">Exclude file types from the copy. All file types are separated by commas, for example: jpg, gif, tmp etc (without a dot before the file type).</div>
                            </div>
                        </small>
                        <div style="clear: both;"></div>
                    </div>
                </td>
            </tr>
            <!-- additional database -->
            <tr style="cursor:pointer">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-additional-database-check" <?php esc_attr_e($additional_database_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font mwp-wpvivid-handle-additional-database-detail">Additional Database</td>
                <td class="column-description desc mwp-wpvivid-handle-additional-database-detail"><?php _e($additional_database_descript); ?></td>
                <th class="mwp-wpvivid-handle-additional-database-detail">
                    <details class="primer" onclick="return false;" style="display: inline-block; width: 100%;">
                        <summary title="Show detail" style="float: right; color: #a0a5aa;"></summary>
                    </details>
                </th>
            </tr>
            <tr class="mwp-wpvivid-custom-detail mwp-wpvivid-additional-database-detail mwp-wpvivid-close" style="<?php esc_attr_e($additional_database_text_style); ?> display: none;">
                <th class="check-column"></th>
                <td colspan="3" class="plugin-title column-primary">
                    <div>
                        <div class="mwp-wpvivid-additional-database-list"></div>
                        <div style="border: 1px solid #e5e5e5; margin-top: 0; margin-bottom: 0; height: 30px; line-height: 30px;">
                            <div class="mwp-wpvivid-additional-database mwp-wpvivid-additional-database-add" style="cursor: pointer; text-align: center;">+ Add an Additional Database</div>
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    public function load_js(){
        ?>
        <script>
            jQuery('#<?php echo $this->parent_id; ?>').on('click', '.mwp-wpvivid-custom-check', function(){
                var check_status = false;
                jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-custom-check').each(function(){
                    if(jQuery(this).prop('checked')){
                        check_status = true;
                    }
                });
                if(!check_status) {
                    jQuery(this).prop('checked', true);
                    alert('Please select at least one item under Custom option.');
                }
            });

            function mwp_wpvivid_handle_custom_open_close(obj, sub_obj){
                if(obj.hasClass('mwp-wpvivid-close')) {
                    sub_obj.hide();
                    sub_obj.prev().find('details').prop('open', false);
                    sub_obj.removeClass('mwp-wpvivid-open');
                    sub_obj.addClass('mwp-wpvivid-close');
                    sub_obj.prev().css('background-color', '#fff');
                    obj.prev().css('background-color', '#f1f1f1');
                    obj.prev().find('details').prop('open', true);
                    obj.show();
                    obj.removeClass('mwp-wpvivid-close');
                    obj.addClass('mwp-wpvivid-open');
                }
                else{
                    obj.hide();
                    obj.prev().css('background-color', '#fff');
                    obj.prev().find('details').prop('open', false);
                    obj.removeClass('mwp-wpvivid-open');
                    obj.addClass('mwp-wpvivid-close');
                }
            }

            function mwp_wpvivid_init_tree(type, parent_id, refresh=0){
                if(type === 'uploads'){
                    mwp_wpvivid_get_uploads_tree(parent_id, refresh);
                }
                else if(type === 'content'){
                    mwp_wpvivid_get_content_tree(parent_id, refresh);
                }
                else if(type === 'additional_folder'){
                    mwp_wpvivid_get_additional_folder_tree(parent_id, refresh);
                }
            }

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-handle-database-detail', function(){
                var obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-database-detail');
                var sub_obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-custom-detail');
                mwp_wpvivid_handle_custom_open_close(obj, sub_obj);
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-handle-themes-plugins-detail', function(){
                var obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-themes-plugins-detail');
                var sub_obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-custom-detail');
                mwp_wpvivid_handle_custom_open_close(obj, sub_obj);
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-handle-uploads-detail', function(){
                var obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-uploads-detail');
                var sub_obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-custom-detail');
                mwp_wpvivid_handle_custom_open_close(obj, sub_obj);
                mwp_wpvivid_init_tree('uploads', '<?php echo $this->parent_id; ?>');
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-handle-content-detail', function(){
                var obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-content-detail');
                var sub_obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-custom-detail');
                mwp_wpvivid_handle_custom_open_close(obj, sub_obj);
                mwp_wpvivid_init_tree('content', '<?php echo $this->parent_id; ?>');
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-handle-additional-folder-detail', function(){
                var obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-folder-detail');
                var sub_obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-custom-detail');
                mwp_wpvivid_handle_custom_open_close(obj, sub_obj);
                mwp_wpvivid_init_tree('additional_folder', '<?php echo $this->parent_id; ?>');
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-handle-additional-database-detail', function(){
                var obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-database-detail');
                var sub_obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-custom-detail');
                mwp_wpvivid_handle_custom_open_close(obj, sub_obj);
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-refresh-uploads-tree', function(){
                mwp_wpvivid_init_tree('uploads', '<?php echo $this->parent_id; ?>', 1);
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-refresh-content-tree', function(){
                mwp_wpvivid_init_tree('content', '<?php echo $this->parent_id; ?>', 1);
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-refresh-additional-folder-tree', function(){
                mwp_wpvivid_init_tree('additional_folder', '<?php echo $this->parent_id; ?>', 1);
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', '.mwp-wpvivid-custom-check', function(){
                if(jQuery(this).hasClass('mwp-wpvivid-custom-database-check')){
                    if(jQuery(this).prop('checked')) {
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-database-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    else{
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-database-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                }
                if(jQuery(this).hasClass('mwp-wpvivid-custom-themes-plugins-check')){
                    if(jQuery(this).prop('checked')) {
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-themes-plugins-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    else{
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-themes-plugins-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                }
                if(jQuery(this).hasClass('mwp-wpvivid-custom-uploads-check')){
                    if(jQuery(this).prop('checked')) {
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-uploads-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    else {
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-uploads-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                }
                if(jQuery(this).hasClass('mwp-wpvivid-custom-content-check')){
                    if(jQuery(this).prop('checked')) {
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-content-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    else {
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-content-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                }
                if(jQuery(this).hasClass('mwp-wpvivid-custom-additional-folder-check')){
                    if(jQuery(this).prop('checked')) {
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-folder-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    else {
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-folder-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                }
                if(jQuery(this).hasClass('mwp-wpvivid-custom-additional-database-check')){
                    if(jQuery(this).prop('checked')) {
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-database-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    else {
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-database-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                }
            });

            function mwp_wpvivid_check_custom_tree_repeat(parent_id, type, value){
                var brepeat = false;
                var list_class = 'mwp-wpvivid-custom-exclude-uploads-list';
                if (type === 'uploads') {
                    list_class = 'mwp-wpvivid-custom-exclude-uploads-list';
                }
                if (type === 'content') {
                    list_class = 'mwp-wpvivid-custom-exclude-content-list';
                }
                if (type === 'additional_folder') {
                    list_class = 'mwp-wpvivid-custom-include-additional-folder-list';
                }
                jQuery('#' + parent_id).find('.' + list_class + ' ul').find('li div:eq(1)').each(function () {
                    if (value === this.innerHTML) {
                        brepeat = true;
                    }
                });
                return brepeat;
            }

            function mwp_wpvivid_remove_custom_tree(obj) {
                jQuery(obj).parent().parent().remove();
            }

            jQuery('#<?php echo $this->parent_id; ?>').on('click', '.mwp-wpvivid-uploads-extension-rule-btn', function(){
                var value = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-uploads-extension').val();
                if(value !== ''){
                    mwp_wpvivid_add_extension_rule(this, 'uploads', value);
                }
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', '.mwp-wpvivid-content-extension-rule-btn', function(){
                var value = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-content-extension').val();
                if(value !== ''){
                    mwp_wpvivid_add_extension_rule(this, 'content', value);
                }
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', '.mwp-wpvivid-additional-folder-extension-rule-btn', function(){
                var value = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-folder-extension').val();
                if(value !== ''){
                    mwp_wpvivid_add_extension_rule(this, 'additional_folder', value);
                }
            });

            function mwp_wpvivid_include_exculde_folder(parent_id, type){
                var select_folders = '';
                var tree_path = '';
                if (type === 'uploads') {
                    select_folders = jQuery('#' + parent_id).find('.mwp-wpvivid-custom-uploads-tree-info').jstree(true).get_selected(true);
                    tree_path = jQuery('#' + parent_id).find('.mwp-wpvivid-custom-uploads-tree-info').find('.jstree-anchor:first').attr('id');
                    tree_path = tree_path.replace('_anchor', '');
                    var list_obj = jQuery('#' + parent_id).find('.mwp-wpvivid-custom-exclude-uploads-list');
                }
                if (type === 'content') {
                    select_folders = jQuery('#' + parent_id).find('.mwp-wpvivid-custom-content-tree-info').jstree(true).get_selected(true);
                    tree_path = jQuery('#' + parent_id).find('.mwp-wpvivid-custom-content-tree-info').find('.jstree-anchor:first').attr('id');
                    tree_path = tree_path.replace('_anchor', '');
                    var list_obj = jQuery('#' + parent_id).find('.mwp-wpvivid-custom-exclude-content-list');
                }
                if (type === 'additional_folder') {
                    select_folders = jQuery('#' + parent_id).find('.mwp-wpvivid-custom-additional-folder-tree-info').jstree(true).get_selected(true);
                    tree_path = jQuery('#' + parent_id).find('.mwp-wpvivid-custom-additional-folder-tree-info').find('.jstree-anchor:first').attr('id');
                    tree_path = tree_path.replace('_anchor', '');
                    var list_obj = jQuery('#' + parent_id).find('.mwp-wpvivid-custom-include-additional-folder-list');
                }
                jQuery.each(select_folders, function (index, select_item) {
                    if (select_item.id !== tree_path) {
                        var value = select_item.id;
                        value = value.replace(tree_path, '');
                        if (!mwp_wpvivid_check_custom_tree_repeat(parent_id, type, value)) {
                            var class_name = select_item.icon === 'jstree-folder' ? 'mwp-wpvivid-custom-li-folder-icon' : 'mwp-wpvivid-custom-li-file-icon';
                            var tr = "<ul style='margin: 0;'>" +
                                "<li>" +
                                "<div class='" + class_name + "'></div>" +
                                "<div class='mwp-wpvivid-custom-li-font'>" + value + "</div>" +
                                "<div class='mwp-wpvivid-custom-li-close' onclick='mwp_wpvivid_remove_custom_tree(this);' title='Remove' style='cursor: pointer;'>X</div>" +
                                "</li>" +
                                "</ul>";
                            list_obj.append(tr);
                        }
                    }
                });
            }

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-exclude-uploads-folder-btn', function(){
                mwp_wpvivid_include_exculde_folder('<?php echo $this->parent_id; ?>', 'uploads');
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-exclude-content-folder-btn', function(){
                mwp_wpvivid_include_exculde_folder('<?php echo $this->parent_id; ?>', 'content');
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-include-additional-folder-btn', function(){
                mwp_wpvivid_include_exculde_folder('<?php echo $this->parent_id; ?>', 'additional_folder');
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-additional-database-add', function(){
                var html = '<div class="mwp-wpvivid-additional-db-account" style="border: 1px solid #e5e5e5; border-bottom: 0; margin-top: 0; margin-bottom: 0; padding-left: 10px;">'+
                                '<div class="mwp-wpvivid-block-right-space" style="float: left;">' +
                                    'User Name: <input type="text" class="mwp-wpvivid-additional-db-user" style="width: 120px;" />' +
                                '</div>'+
                                '<div class="mwp-wpvivid-block-right-space" style="float: left;">' +
                                    'Password: <input type="text" class="mwp-wpvivid-additional-db-pass" style="width: 120px;" />' +
                                '</div>' +
                                '<div class="mwp-wpvivid-block-right-space" style="float: left;">' +
                                    'Host: <input type="text" class="mwp-wpvivid-additional-db-host" style="width: 120px;" />' +
                                '</div>' +
                                '<div class="mwp-wpvivid-block-right-space" style="float: left;">' +
                                    '<input class="ui green mini button mwp-wpvivid-additional-db-connect" type="button" value="Show Databases" />' +
                                '</div>' +
                                '<div class="mwp-wpvivid-block-right-space" style="float: left;">' +
                                    '<input class="ui green mini button mwp-wpvivid-additional-db-close" type="button" value="Close" />' +
                                '</div>' +
                                '<div style="clear: both;"></div>' +
                            '</div>';
                jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-database-list').append(html);
                jQuery(this).css({'pointer-events': 'none', 'opacity': '0.4'});
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-additional-db-connect', function(){
                mwp_wpvivid_additional_database_connect('<?php echo $this->parent_id; ?>');
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-additional-db-close', function(){
                jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-db-account').remove();
                jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-database-add').css({'pointer-events': 'auto', 'opacity': '1'});
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-additional-db-add', function(){
                mwp_wpvivid_additional_database_add('<?php echo $this->parent_id; ?>');
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-additional-db-table-close', function(){
                jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-db-account').remove();
                jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-database-add').css({'pointer-events': 'auto', 'opacity': '1'});
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-additional-database-remove', function(){
                var database_name = jQuery(this).attr('name');
                mwp_wpvivid_additional_database_remove('<?php echo $this->parent_id; ?>', database_name);
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', '.mwp-wpvivid-database-table-check', function(){
                if(jQuery(this).prop('checked')) {
                    if(jQuery(this).hasClass('mwp-wpvivid-base-table-all-check')){
                        jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_base_db][name=Database]').prop('checked', true);
                    }
                    else if(jQuery(this).hasClass('mwp-wpvivid-other-table-all-check')){
                        jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_other_db][name=Database]').prop('checked', true);
                    }
                }
                else{
                    var check_status = false;
                    if (jQuery(this).hasClass('mwp-wpvivid-base-table-all-check')) {
                        jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_other_db][name=Database]').each(function(){
                            if(jQuery(this).prop('checked')){
                                check_status = true;
                            }
                        });
                        if(check_status) {
                            jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_base_db][name=Database]').prop('checked', false);
                        }
                        else{
                            jQuery(this).prop('checked', true);
                            alert('Please select at least one table type under the Database option, or deselect the option.');
                        }
                    }
                    else if (jQuery(this).hasClass('mwp-wpvivid-other-table-all-check')) {
                        jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_base_db][name=Database]').each(function(){
                            if(jQuery(this).prop('checked')){
                                check_status = true;
                            }
                        });
                        if(check_status) {
                            jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_other_db][name=Database]').prop('checked', false);
                        }
                        else{
                            jQuery(this).prop('checked', true);
                            alert('Please select at least one table type under the Database option, or deselect the option.');
                        }
                    }
                }
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', 'input:checkbox[option=mwp_base_db][name=Database]', function(){
                if(jQuery(this).prop('checked')){
                    var all_check = true;
                    jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_base_db][name=Database]').each(function(){
                        if(!jQuery(this).prop('checked')){
                            all_check = false;
                        }
                    });
                    if(all_check){
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-base-table-all-check').prop('checked', true);
                    }
                }
                else{
                    var check_status = false;
                    jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[name=Database]').each(function(){
                        if(jQuery(this).prop('checked')){
                            check_status = true;
                        }
                    });
                    if(check_status){
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-base-table-all-check').prop('checked', false);
                    }
                    else{
                        jQuery(this).prop('checked', true);
                        alert('Please select at least one table type under the Database option, or deselect the option.');
                    }
                }
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', 'input:checkbox[option=mwp_other_db][name=Database]', function(){
                if(jQuery(this).prop('checked')){
                    var all_check = true;
                    jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_other_db][name=Database]').each(function(){
                        if(!jQuery(this).prop('checked')){
                            all_check = false;
                        }
                    });
                    if(all_check){
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-other-table-all-check').prop('checked', true);
                    }
                }
                else{
                    var check_status = false;
                    jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[name=Database]').each(function(){
                        if(jQuery(this).prop('checked')){
                            check_status = true;
                        }
                    });
                    if(check_status){
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-other-table-all-check').prop('checked', false);
                    }
                    else{
                        jQuery(this).prop('checked', true);
                        alert('Please select at least one table type under the Database option, or deselect the option.');
                    }
                }
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', '.mwp-wpvivid-themes-plugins-table-check', function(){
                if(jQuery(this).prop('checked')){
                    if(jQuery(this).hasClass('mwp-wpvivid-themes-all-check')){
                        jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_themes][name=Themes]').prop('checked', true);
                    }
                    else if(jQuery(this).hasClass('mwp-wpvivid-plugins-all-check')){
                        jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_plugins][name=Plugins]').prop('checked', true);
                    }
                }
                else{
                    var check_status = false;
                    if (jQuery(this).hasClass('mwp-wpvivid-themes-all-check')) {
                        jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_plugins][name=Plugins]').each(function(){
                            if(jQuery(this).prop('checked')){
                                check_status = true;
                            }
                        });
                        if(check_status) {
                            jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_themes][name=Themes]').prop('checked', false);
                        }
                        else{
                            jQuery(this).prop('checked', true);
                            alert('Please select at least one item under the Themes and Plugins option, or deselect the option.');
                        }
                    }
                    else if (jQuery(this).hasClass('mwp-wpvivid-plugins-all-check')) {
                        jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_themes][name=Themes]').each(function(){
                            if(jQuery(this).prop('checked')){
                                check_status = true;
                            }
                        });
                        if(check_status) {
                            jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_plugins][name=Plugins]').each(function(){
                                if(jQuery(this).val() !== 'wpvivid-backuprestore' && jQuery(this).val() !== 'wpvivid-backup-pro'){
                                    jQuery(this).prop('checked', false);
                                }
                            });
                        }
                        else{
                            jQuery(this).prop('checked', true);
                            alert('Please select at least one item under the Themes and Plugins option, or deselect the option.');
                        }
                    }
                }
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', 'input:checkbox[option=mwp_themes][name=Themes]', function(){
                if(jQuery(this).prop('checked')){
                    var all_check = true;
                    jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_themes][name=Themes]').each(function(){
                        if(!jQuery(this).prop('checked')){
                            all_check = false;
                        }
                    });
                    if(all_check){
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-themes-all-check').prop('checked', true);
                    }
                }
                else{
                    var check_status = false;
                    jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_themes][name=Themes]').each(function(){
                        if(jQuery(this).prop('checked')){
                            check_status = true;
                        }
                    });
                    if(!check_status){
                        jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_plugins][name=Plugins]').each(function(){
                            if(jQuery(this).prop('checked')){
                                check_status = true;
                            }
                        });
                    }
                    if(check_status){
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-themes-all-check').prop('checked', false);
                    }
                    else{
                        jQuery(this).prop('checked', true);
                        alert('Please select at least one item under the Themes and Plugins option, or deselect the option.');
                    }
                }
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', 'input:checkbox[option=mwp_plugins][name=Plugins]', function(){
                if(jQuery(this).prop('checked')){
                    var all_check = true;
                    jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_plugins][name=Plugins]').each(function(){
                        if(!jQuery(this).prop('checked')){
                            all_check = false;
                        }
                    });
                    if(all_check){
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-plugins-all-check').prop('checked', true);
                    }
                }
                else{
                    var check_status = false;
                    jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_plugins][name=Plugins]').each(function(){
                        if(jQuery(this).prop('checked')){
                            check_status = true;
                        }
                    });
                    if(!check_status){
                        jQuery('#<?php echo $this->parent_id; ?>').find('input:checkbox[option=mwp_themes][name=Themes]').each(function(){
                            if(jQuery(this).prop('checked')){
                                check_status = true;
                            }
                        });
                    }
                    if(check_status){
                        jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-plugins-all-check').prop('checked', false);
                    }
                    else{
                        jQuery(this).prop('checked', true);
                        alert('Please select at least one item under the Themes and Plugins option, or deselect the option.');
                    }
                }
            });
        </script>
        <?php
    }
}