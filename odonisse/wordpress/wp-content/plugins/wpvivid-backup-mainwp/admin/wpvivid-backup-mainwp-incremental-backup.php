<?php

if ( ! class_exists( 'WP_List_Table' ) )
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Mainwp_WPvivid_Incremental_Schedule_Mould_List extends WP_List_Table
{
    public $page_num;
    public $incremental_schedule_mould_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'incremental_schedule_mould',
                'screen' => 'incremental_schedule_mould',
            )
        );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb']))
        {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox"/>';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) )
            {
                $class[] = 'hidden';
            }

            if ( $column_key === $primary )
            {
                $class[] = 'column-primary';
            }

            if ( $column_key === 'cb' )
            {
                $class[] = 'check-column';
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) )
            {
                $class = "class='" . join( ' ', $class ) . "'";
            }

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    public function get_columns()
    {
        $columns = array();
        $columns['wpvivid_mould_name'] = __( 'Mould Name', 'wpvivid' );
        $columns['wpvivid_sync_mould'] = __( 'Sync Mould', 'wpvivid' );
        $columns['wpvivid_actions'] = __( 'Actions', 'wpvivid' );
        return $columns;
    }

    public function set_schedule_mould_list($incremental_schedule_mould_list,$page_num=1)
    {
        $this->incremental_schedule_mould_list=$incremental_schedule_mould_list;
        $this->page_num=$page_num;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        if(!empty($this->incremental_schedule_mould_list)) {
            $total_items = sizeof($this->incremental_schedule_mould_list);
        }
        else{
            $total_items = 0;
        }

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->incremental_schedule_mould_list);
    }

    public function _column_wpvivid_mould_name( $incremental_schedule_mould )
    {
        echo '<td><div>'.$incremental_schedule_mould['mould_name'].'</div></td>';
    }

    public function _column_wpvivid_sync_mould( $incremental_schedule_mould )
    {
        echo '<td><input class="ui green mini button mwp-wpvivid-sync-incremental-schedule-mould" type="button" value="Sync" /></td>';
    }

    public function _column_wpvivid_actions( $incremental_schedule_mould )
    {
        echo '<td>
                    <div>
                         <img class="mwp-wpvivid-incremental-schedule-mould-edit" src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . '/admin/images/Edit.png') . '"
                              style="vertical-align:middle; cursor:pointer;" title="Edit the schedule" />                    
                         <img class="mwp-wpvivid-incremental-schedule-mould-delete" src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . '/admin/images/Delete.png') . '"
                              style="vertical-align:middle; cursor:pointer;" title="Delete the schedule" />                    
                     </div>
                </td>';
    }

    public function display_rows()
    {
        $this->_display_rows( $this->incremental_schedule_mould_list );
    }

    private function _display_rows($incremental_schedule_mould)
    {
        $page=$this->get_pagenum();

        $page_schedule_mould_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_schedule_mould_list = array_splice( $incremental_schedule_mould, 0, 10);
            $count++;
        }
        foreach ( $page_schedule_mould_list as $mould_name => $schedule_mould)
        {
            $schedule_mould['mould_name'] = $mould_name;
            $this->single_row($schedule_mould);
        }
    }

    public function single_row($incremental_schedule_mould)
    {
        ?>
        <tr slug="<?php echo $incremental_schedule_mould['mould_name'];?>">
            <?php $this->single_row_columns( $incremental_schedule_mould ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector-schedule' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-schedule" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1) {
            ?>
            <div class="tablenav <?php echo esc_attr($which); ?>" style="<?php esc_attr_e($css_type); ?>">
                <?php
                $this->extra_tablenav($which);
                $this->pagination($which);
                ?>

                <br class="clear"/>
            </div>
            <?php
        }
    }

    protected function get_table_classes()
    {
        return array( 'widefat plugin-install' );
    }
}

class Mainwp_WPvivid_Extension_Custom_Backup_Selector
{
    public $id;
    public $option;
    public static $incremental_backup_data;

    public function __construct( $id,$option,$incremental_backup_data )
    {
        $this->id=$id;
        $this->option=$option;
        self::$incremental_backup_data=$incremental_backup_data;
    }

    public static function get_incremental_setting(){
        $history = isset(self::$incremental_backup_data['incremental_history']) ? self::$incremental_backup_data['incremental_history'] : array();
        if(empty($history)){
            $history = array();
        }
        return $history;
    }

    public static function set_incremental_file_settings($site_id, $options){
        $history = isset(self::$incremental_backup_data['incremental_history']) ? self::$incremental_backup_data['incremental_history'] : array();
        if(empty($history)){
            $history = array();
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

        self::$incremental_backup_data['incremental_history']['incremental_file'] = $custom_option;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'incremental_backup_setting', self::$incremental_backup_data);
    }

    public static function get_incremental_file_settings()
    {
        $history = isset(self::$incremental_backup_data['incremental_history']) ? self::$incremental_backup_data['incremental_history'] : array();
        if(isset($history['incremental_file'])){
            $options = $history['incremental_file'];
        }
        else{
            $options = array();
        }
        return $options;
    }

    public static function set_incremental_db_setting($site_id, $options){
        $history = isset(self::$incremental_backup_data['incremental_history']) ? self::$incremental_backup_data['incremental_history'] : array();
        if(empty($history)){
            $history = array();
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

        self::$incremental_backup_data['incremental_history']['incremental_db'] = $custom_option;
        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_update_option($site_id, 'incremental_backup_setting', self::$incremental_backup_data);
    }

    public static function get_incremental_db_setting(){
        $history = isset(self::$incremental_backup_data['incremental_history']) ? self::$incremental_backup_data['incremental_history'] : array();
        if(isset($history['incremental_db'])){
            $options = $history['incremental_db'];
        }
        else{
            $options = array();
        }
        return $options;
    }

    public function output_backup_files(){
        $core_check = 'checked';
        $themes_check = 'checked';
        $plugins_check = 'checked';
        $themes_plugins_check = 'checked';
        $uploads_check = 'checked';
        $content_check = 'checked';
        $additional_folder_check = '';
        $themes_plugins_text_style = 'pointer-events: auto; opacity: 1;';
        $uploads_text_style = 'pointer-events: auto; opacity: 1;';
        $content_text_style = 'pointer-events: auto; opacity: 1;';
        $additional_folder_text_style = 'pointer-events: none; opacity: 0.4;';
        $upload_extension = '';
        $content_extension = '';
        $additional_folder_extension = '';

        $custom_incremental_history = self::get_incremental_file_settings();
        if(isset($custom_incremental_history) && !empty($custom_incremental_history)) {
            if(isset($custom_incremental_history['core_option']['core_check']))
            {
                if ($custom_incremental_history['core_option']['core_check'] != '1')
                {
                    $core_check = '';
                }
            }

            if(isset($custom_incremental_history['themes_option']['themes_check']))
            {
                if ($custom_incremental_history['themes_option']['themes_check'] != '1')
                {
                    $themes_check = '';
                }
            }
            if(isset($custom_incremental_history['plugins_option']['plugins_check']))
            {
                if ($custom_incremental_history['plugins_option']['plugins_check'] != '1')
                {
                    $plugins_check = '';
                }
            }
            if($themes_check == '' && $plugins_check == '')
            {
                $themes_plugins_check = '';
                $themes_plugins_text_style = 'pointer-events: none; opacity: 0.4;';
            }

            if(isset($custom_incremental_history['uploads_option']['uploads_check']))
            {
                if ($custom_incremental_history['uploads_option']['uploads_check'] != '1')
                {
                    $uploads_check = '';
                    $uploads_text_style = 'pointer-events: none; opacity: 0.4;';
                }
            }

            if(isset($custom_incremental_history['content_option']['content_check']))
            {
                if ($custom_incremental_history['content_option']['content_check'] != '1')
                {
                    $content_check = '';
                    $content_text_style = 'pointer-events: none; opacity: 0.4;';
                }
            }

            if(isset($custom_incremental_history['other_option']['other_check']))
            {
                if ($custom_incremental_history['other_option']['other_check'] == '1')
                {
                    $additional_folder_check = 'checked';
                    $additional_folder_text_style = 'pointer-events: auto; opacity: 1;';
                }
            }

            if(isset($custom_incremental_history['uploads_option']['uploads_extension_list']) && !empty($custom_incremental_history['uploads_option']['uploads_extension_list'])){
                $upload_extension = implode(",", $custom_incremental_history['uploads_option']['uploads_extension_list']);
            }
            if(isset($custom_incremental_history['content_option']['content_extension_list']) && !empty($custom_incremental_history['content_option']['content_extension_list'])){
                $content_extension = implode(",", $custom_incremental_history['content_option']['content_extension_list']);
            }
            if(isset($custom_incremental_history['other_option']['other_extension_list']) && !empty($custom_incremental_history['other_option']['other_extension_list'])){
                $additional_folder_extension = implode(",", $custom_incremental_history['other_option']['other_extension_list']);
            }
        }

        $core_descript = 'Choose if to back up the WordPress core files. <a href="https://wpvivid.com/wpvivid-backup-pro-wordpress-core-backup" target="_blank">Learn more</a>';
        $themes_plugins_descript = 'Specify which themes or plugins you want to back up. In order to save server resources, only the activated themes and all plugins are checked by default. <a href="https://wpvivid.com/wpvivid-backup-pro-wordpress-themes-and-plugins-backup" target="_blank">Learn more</a>';
        $uploads_descript = '<strong style="text-decoration:underline;"><i>Exclude</i></strong> folders you do not want to back up under wp-content/uploads folder.';
        $content_descript = '<strong style="text-decoration:underline;"><i>Exclude</i></strong> folders you do not want to back up under wp-content folder, except for the wp-content/uploads folder. <a href="https://wpvivid.com/wpvivid-backup-pro-wp-content-backup" target="_blank">Learn more</a>';
        $additional_folder_descript = '<strong style="text-decoration:underline;"><i>Include</i></strong> additional files or folders you want to back up. <a href="https://wpvivid.com/wpvivid-backup-pro-additional-files-folders-backup" target="_blank">Learn more</a>';
        ?>
        <table class="wp-list-table widefat plugins mwp-wpvivid-custom-table">
            <tbody>
            <!-------- core -------->
            <tr>
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-core-check mwp-wpvivid-custom-file-check" <?php esc_attr_e($core_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font">Wordpress Core</td>
                <td class="column-description desc"><?php _e($core_descript); ?></td>
            </tr>
            <!-------- themes and plugins -------->
            <tr style="cursor:pointer">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-themes-plugins-check mwp-wpvivid-custom-file-check" <?php esc_attr_e($themes_plugins_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font mwp-wpvivid-handle-themes-plugins-detail">Themes and Plugins</td>
                <td class="column-description desc mwp-wpvivid-handle-themes-plugins-detail">
                    <?php _e($themes_plugins_descript); ?>
                </td>
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
            <!-------- uploads -------->
            <tr style="cursor:pointer">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-uploads-check mwp-wpvivid-custom-file-check" <?php esc_attr_e($uploads_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font mwp-wpvivid-handle-uploads-detail">wp-content/uploads</td>
                <td class="column-description desc mwp-wpvivid-handle-uploads-detail"><?php _e($uploads_descript); ?></td>
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
                            <th class="manage-column column-name column-primary" style="border-bottom: 1px solid #e1e1e1 !important;">
                                <label class="mwp-wpvivid-refresh-tree mwp-wpvivid-refresh-uploads-tree" style="margin-bottom: 0; font-size: 13px;">Click Here to Refresh Folder Tree</label>
                            </th>
                            <th class="manage-column column-description" style="font-size: 13px; border-bottom: 1px solid #e1e1e1 !important;">Checked Folders or Files to Backup</th>
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
                                <div class="mwp-wpvivid-custom-uploads-table mwp-wpvivid-custom-exclude-uploads-list">
                                    <?php
                                    $html = '';
                                    if(!empty($custom_incremental_history['uploads_option']['exclude_uploads_list'])) {
                                        foreach ($custom_incremental_history['uploads_option']['exclude_uploads_list'] as $index => $value) {
                                            $html .= '<ul>
                                                        <li>
                                                            <div class="'.$value['type'].'"></div>
                                                            <div class="mwp-wpvivid-custom-li-font">'.$value['name'].'</div>
                                                            <div class="mwp-wpvivid-custom-li-close" onclick="mwp_wpvivid_remove_exclude_tree(this);" title="Remove" style="cursor: pointer;">X</div>
                                                        </li>
                                                     </ul>';
                                        }
                                    }
                                    echo $html;
                                    ?>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2">
                                <div>
                                    <div style="float: left; margin-right: 10px;">
                                        <input class="ui green mini button mwp-wpvivid-exclude-uploads-folder-btn" type="submit" value="Exclude Folders" />
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
                            <input type="text" class="regular-text mwp-wpvivid-uploads-extension" placeholder="Exclude file types, for example: gif,jpg,webp" value="<?php esc_attr_e($upload_extension); ?>" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_,]/g,'')"/>
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
            <!-------- content -------->
            <tr style="cursor:pointer">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-content-check mwp-wpvivid-custom-file-check" <?php esc_attr_e($content_check); ?> />
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
                            <th class="manage-column column-name column-primary" style="border-bottom: 1px solid #e1e1e1 !important;">
                                <label class="mwp-wpvivid-refresh-tree mwp-wpvivid-refresh-content-tree" style="margin-bottom: 0; font-size: 13px;">Click Here to Refresh Folder Tree</label>
                            </th>
                            <th class="manage-column column-description" style="font-size: 13px; border-bottom: 1px solid #e1e1e1 !important;">Checked Folders or Files to Backup</th>
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
                                    <?php
                                    $html = '';
                                    if(!empty($custom_incremental_history['content_option']['exclude_content_list'])) {
                                        foreach ($custom_incremental_history['content_option']['exclude_content_list'] as $index => $value) {
                                            $html .= '<ul>
                                                        <li>
                                                            <div class="'.$value['type'].'"></div>
                                                            <div class="mwp-wpvivid-custom-li-font">'.$value['name'].'</div>
                                                            <div class="mwp-wpvivid-custom-li-close" onclick="mwp_wpvivid_remove_exclude_tree(this);" title="Remove" style="cursor: pointer;">X</div>
                                                        </li>
                                                     </ul>';
                                        }
                                    }
                                    echo $html;
                                    ?>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2">
                                <div style="float: left; margin-right: 10px;">
                                    <input class="ui green mini button mwp-wpvivid-exclude-content-folder-btn" type="submit" value="Exclude Folders" />
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
                            <input type="text" class="regular-text mwp-wpvivid-content-extension" placeholder="Exclude file types, for example: gif,jpg,webp" value="<?php esc_attr_e($content_extension); ?>" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_,]/g,'')"/>
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
            <!-------- additional files -------->
            <tr style="cursor:pointer">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-additional-folder-check mwp-wpvivid-custom-file-check" <?php esc_attr_e($additional_folder_check); ?> />
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
                            <th class="manage-column column-name column-primary" style="border-bottom: 1px solid #e1e1e1 !important;">
                                <label class="mwp-wpvivid-refresh-tree mwp-wpvivid-refresh-additional-folder-tree" style="margin-bottom: 0; font-size: 13px;">Click Here to Refresh Folder/File Tree</label>
                            </th>
                            <th class="manage-column column-description" style="font-size: 13px; border-bottom: 1px solid #e1e1e1 !important;">Checked Folders or Files to Backup</th>
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
                                    <?php
                                    $html = '';
                                    if(!empty($custom_incremental_history['other_option']['include_other_list'])) {
                                        foreach ($custom_incremental_history['other_option']['include_other_list'] as $index => $value) {
                                            $html .= '<ul>
                                                        <li>
                                                            <div class="'.$value['type'].'"></div>
                                                            <div class="mwp-wpvivid-custom-li-font">'.$value['name'].'</div>
                                                            <div class="mwp-wpvivid-custom-li-close" onclick="wpvivid_remove_exclude_tree(this);" title="Remove" style="cursor: pointer;">X</div>
                                                        </li>
                                                     </ul>';
                                        }
                                    }
                                    echo $html;
                                    ?>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2">
                                <div style="float: left; margin-right: 10px;">
                                    <input class="ui green mini button mwp-wpvivid-include-additional-folder-btn" type="submit" value="Include folders/files" />
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
                            <input type="text" class="regular-text mwp-wpvivid-additional-folder-extension" placeholder="Exclude file types, for example: gif,jpg,webp" value="<?php esc_attr_e($additional_folder_extension); ?>" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_,]/g,'')"/>
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
            </tbody>
        </table>
        <?php
    }

    public function output_backup_db()
    {
        $database_check = 'checked';
        $additional_database_check = '';
        $database_text_style = 'pointer-events: auto; opacity: 1;';
        $additional_database_text_style = 'pointer-events: none; opacity: 0.4;';

        $custom_incremental_history = self::get_incremental_db_setting();
        if(isset($custom_incremental_history) && !empty($custom_incremental_history)){
            if(isset($custom_incremental_history['database_option']['database_check']))
            {
                if ($custom_incremental_history['database_option']['database_check'] != '1')
                {
                    $database_check = '';
                    $database_text_style = 'pointer-events: none; opacity: 0.4;';
                }
            }

            if(!empty($custom_incremental_history['additional_database_option']))
            {
                if(isset($custom_incremental_history['additional_database_option']['additional_database_check']))
                {
                    if ($custom_incremental_history['additional_database_option']['additional_database_check'] == '1')
                    {
                        $additional_database_check = 'checked';
                        $additional_database_text_style = 'pointer-events: auto; opacity: 1;';
                    }
                }
            }
        }

        $database_descript = 'Select which tables of database you want to back up. <a href="https://wpvivid.com/wpvivid-backup-pro-database-backup" target="_blank">Learn more</a>';
        $additional_database_descript = '<strong style="text-decoration:underline;"><i>Include</i></strong> additional databases you want to back up.';
        ?>
        <table class="wp-list-table widefat plugins mwp-wpvivid-custom-table">
            <tbody>
            <!-------- database -------->
            <tr style="cursor:pointer;">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-database-check mwp-wpvivid-custom-db-check" <?php esc_attr_e($database_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font mwp-wpvivid-handle-database-detail">Database</td>
                <td class="column-description desc mwp-wpvivid-handle-database-detail">
                    <?php _e($database_descript); ?>
                </td>
                <th class="mwp-wpvivid-handle-database-detail">
                    <details class="primer" onclick="return false;" style="display: inline-block; width: 100%;">
                        <summary title="Show detail" style="float: right; color: #a0a5aa;"></summary>
                    </details>
                </th>
            </tr>
            <tr class="mwp-wpvivid-custom-detail mwp-wpvivid-database-detail mwp-wpvivid-close" style="<?php esc_attr_e($database_text_style); ?> display: none;">
                <th class="check-column"></th>
                <td colspan="3" class="plugin-title column-primary mwp-wpvivid-custom-database-info">
                    <div class="spinner is-active" style="margin: 0 5px 10px 0; float: left;"></div>
                    <div style="float: left;">Archieving database tables</div>
                    <div style="clear: both;"></div>
                </td>
            </tr>
            <!-- additional database -->
            <tr style="cursor:pointer">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-additional-database-check mwp-wpvivid-custom-db-check" <?php esc_attr_e($additional_database_check); ?> />
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
                        <div class="mwp-wpvivid-additional-database-list">
                            <?php
                            $html = '';
                            if(isset($custom_incremental_history['additional_database_option']['additional_database_list'])) {
                                foreach ($custom_incremental_history['additional_database_option']['additional_database_list'] as $database => $db_info) {
                                    $html .= '<div style="border: 1px solid #e5e5e5; border-bottom: 0; height: 30px; line-height: 30px;">
                                                <div class="mwp-wpvivid-additional-database" option="additional_db_custom" name="' . $database . '" style="margin-left: 10px; float: left;">' . $database . '</div>
                                                <div class="mwp-wpvivid-additional-database mwp-wpvivid-additional-database-remove" name="' . $database . '" style="margin-right: 10px; float: right; cursor: pointer;">X</div>
                                                <div style="clear: both;"></div>
                                              </div>';
                                }
                            }
                            echo $html;
                            ?>
                        </div>
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

    public function output_local_remote_selector()
    {
        ?>
        <fieldset>
            <div class="mwp-wpvivid-block-bottom-space">
                <label title="">
                    <input type="radio" option="<?php echo $this->option;?>" name="save_local_remote" value="local" checked />
                    <span><?php _e( 'Save backups on localhost (web server)', 'wpvivid' );?></span>
                </label>
            </div>
            <div>
                <label title="">
                    <input type="radio" option="<?php echo $this->option;?>" name="save_local_remote" value="remote" />
                    <span><?php _e( 'Send backups to remote storage (Backups will be deleted from localhost after they are completely uploaded to remote storage.)', 'wpvivid' );?></span>
                </label>
            </div>
            <label style="display: none;">
                <input type="checkbox" option="<?php echo $this->option;?>" name="lock" value="0" />
            </label>
        </fieldset>
        <?php
    }

    public function output_remote_pic()
    {
        $pic='';
        $pic= apply_filters('wpvivid_schedule_add_remote_pic',$pic);
        echo $pic;
    }

    public function load_js(){
        ?>
        <script>
            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-handle-database-detail', function(){
                var obj = jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-database-detail');
                mwp_wpvivid_handle_incremental_custom_open_close(obj);
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-handle-themes-plugins-detail', function(){
                var obj = jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-themes-plugins-detail');
                mwp_wpvivid_handle_incremental_custom_open_close(obj);
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-handle-uploads-detail', function(){
                var obj = jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-uploads-detail');
                mwp_wpvivid_handle_incremental_custom_open_close(obj);
                mwp_wpvivid_init_incremental_tree('uploads', '<?php echo $this->id; ?>');
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-handle-content-detail', function(){
                var obj = jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-content-detail');
                mwp_wpvivid_handle_incremental_custom_open_close(obj);
                mwp_wpvivid_init_incremental_tree('content', '<?php echo $this->id; ?>');
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-handle-additional-folder-detail', function(){
                var obj = jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-additional-folder-detail');
                mwp_wpvivid_handle_incremental_custom_open_close(obj);
                mwp_wpvivid_init_incremental_tree('additional_folder', '<?php echo $this->id; ?>');
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-handle-additional-database-detail', function(){
                var obj = jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-additional-database-detail');
                mwp_wpvivid_handle_incremental_custom_open_close(obj);
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-refresh-tree', function () {
                if (jQuery(this).hasClass('mwp-wpvivid-refresh-uploads-tree')) {
                    mwp_wpvivid_init_incremental_tree('uploads', '<?php echo $this->id; ?>', 1);
                }
                else if (jQuery(this).hasClass('mwp-wpvivid-refresh-content-tree')) {
                    mwp_wpvivid_init_incremental_tree('content', '<?php echo $this->id; ?>', 1);
                }
                else if (jQuery(this).hasClass('mwp-wpvivid-refresh-additional-folder-tree')) {
                    mwp_wpvivid_init_incremental_tree('additional-folder', '<?php echo $this->id; ?>', 1);
                }
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-exclude-uploads-folder-btn', function(){
                mwp_wpvivid_include_exculde_folder('<?php echo $this->id; ?>', 'uploads');
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-exclude-content-folder-btn', function () {
                mwp_wpvivid_include_exculde_folder('<?php echo $this->id; ?>', 'content');
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-include-additional-folder-btn', function () {
                mwp_wpvivid_include_exculde_folder('<?php echo $this->id; ?>', 'additional-folder');
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-custom-check', function(){
                if(jQuery(this).hasClass('mwp-wpvivid-custom-database-check')){
                    if(jQuery(this).prop('checked')) {
                        jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-database-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    else{
                        jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-database-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                }
                if(jQuery(this).hasClass('mwp-wpvivid-custom-themes-plugins-check')){
                    if(jQuery(this).prop('checked')) {
                        jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-themes-plugins-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    else{
                        jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-themes-plugins-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                }
                if(jQuery(this).hasClass('mwp-wpvivid-custom-uploads-check')){
                    if(jQuery(this).prop('checked')) {
                        jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-uploads-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    else {
                        jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-uploads-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                }
                if(jQuery(this).hasClass('mwp-wpvivid-custom-content-check')){
                    if(jQuery(this).prop('checked')) {
                        jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-content-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    else {
                        jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-content-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                }
                if(jQuery(this).hasClass('mwp-wpvivid-custom-additional-folder-check')){
                    if(jQuery(this).prop('checked')) {
                        jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-additional-folder-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    else {
                        jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-additional-folder-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                }
                if(jQuery(this).hasClass('mwp-wpvivid-custom-additional-database-check')){
                    if(jQuery(this).prop('checked')) {
                        jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-additional-database-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                    else {
                        jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-additional-database-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                    }
                }
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-uploads-extension-rule-btn', function(){
                var value = jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-uploads-extension').val();
                if(value !== '') {
                    mwp_wpvivid_update_incremental_exclude_extension_rule(this, 'upload', value);
                }
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-content-extension-rule-btn', function(){
                var value = jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-content-extension').val();
                if(value !== ''){
                    mwp_wpvivid_update_incremental_exclude_extension_rule(this, 'content', value);
                }
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-additional-folder-extension-rule-btn', function(){
                var value = jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-additional-folder-extension').val();
                if(value !== ''){
                    mwp_wpvivid_update_incremental_exclude_extension_rule(this, 'additional_folder', value);
                }
            });

            jQuery('#<?php echo $this->id; ?>').on('click', '.mwp-wpvivid-additional-database-add', function(){
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
                jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-additional-database-list').append(html);
                jQuery(this).css({'pointer-events': 'none', 'opacity': '0.4'});
            });

            jQuery('#<?php echo $this->id; ?>').on("click", '.mwp-wpvivid-additional-db-connect', function(){
                mwp_wpvivid_incremental_additional_database_connect('<?php echo $this->id; ?>');
            });

            jQuery('#<?php echo $this->id; ?>').on("click", '.mwp-wpvivid-additional-db-close', function(){
                jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-additional-db-account').remove();
                jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-additional-database-add').css({'pointer-events': 'auto', 'opacity': '1'});
            });

            jQuery('#<?php echo $this->id; ?>').on("click", '.mwp-wpvivid-additional-db-add', function(){
                mwp_wpvivid_incremental_additional_database_add('<?php echo $this->id; ?>');
            });

            jQuery('#<?php echo $this->id; ?>').on("click", '.mwp-wpvivid-additional-db-table-close', function(){
                jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-additional-db-account').remove();
                jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-additional-database-add').css({'pointer-events': 'auto', 'opacity': '1'});
            });

            jQuery('#<?php echo $this->id; ?>').on("click", '.mwp-wpvivid-additional-database-remove', function(){
                var database_name = jQuery(this).attr('name');
                mwp_wpvivid_incremental_additional_database_remove('<?php echo $this->id; ?>', database_name);
            });

            function mwp_wpvivid_handle_incremental_custom_open_close(obj){
                if(obj.hasClass('mwp-wpvivid-close')) {
                    var sub_obj = jQuery('#<?php echo $this->id; ?>').find('.mwp-wpvivid-custom-detail');
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

            function mwp_wpvivid_init_incremental_tree(type, parent_id, refresh=0){
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
        </script>
        <?php
    }
}

class Mainwp_WPvivid_Extension_Global_Custom_Backup_Selector
{
    public $parent_id;
    public $option;
    private $backup_custom_setting;

    public function __construct($parent_id, $option, $backup_custom_setting = array()){
        $this->parent_id = $parent_id;
        $this->option=$option;
        $this->backup_custom_setting = $backup_custom_setting;
    }

    public function display_file_rows(){
        $core_descript = 'Choose if to back up the WordPress core files. <a href="https://wpvivid.com/wpvivid-backup-pro-wordpress-core-backup" target="_blank">Learn more</a>';
        $themes_descript = 'Specify which themes you want to back up. In order to save server resources, only the activated themes are checked by default. <a href="https://wpvivid.com/wpvivid-backup-pro-wordpress-themes-and-plugins-backup" target="_blank">Learn more</a>';
        $plugins_descript = 'Specify which plugins you want to back up. In order to save server resources, only the activated plugins are checked by default. <a href="https://wpvivid.com/wpvivid-backup-pro-wordpress-themes-and-plugins-backup" target="_blank">Learn more</a>';
        $uploads_descript = '<strong style="text-decoration:underline;"><i>Exclude</i></strong> folders you do not want to back up under wp-content/uploads folder.';
        $content_descript = '<strong style="text-decoration:underline;"><i>Exclude</i></strong> folders you do not want to back up under wp-content folder, except for the wp-content/uploads folder. <a href="https://wpvivid.com/wpvivid-backup-pro-wp-content-backup" target="_blank">Learn more</a>';

        $core_check = 'checked';
        $themes_plugins_check = 'checked';
        $themes_check = 'checked';
        $plugins_check = 'checked';
        $uploads_check = 'checked';
        $uploads_text_style = 'pointer-events: auto; opacity: 1;';
        $content_check = 'checked';
        $content_text_style = 'pointer-events: auto; opacity: 1;';
        $upload_extension = '';
        $content_extension = '';

        if(!empty($this->backup_custom_setting)){
            if(isset($this->backup_custom_setting['core_option']['core_check'])){
                $core_check = $this->backup_custom_setting['core_option']['core_check'] === '1' ? 'checked' : '';
            }
            if(isset($this->backup_custom_setting['themes_option']['themes_check'])){
                $themes_check = $this->backup_custom_setting['themes_option']['themes_check'] === '1' ? 'checked' : '';
            }
            if(isset($this->backup_custom_setting['plugins_option']['plugins_check'])){
                $plugins_check = $this->backup_custom_setting['plugins_option']['plugins_check'] === '1' ? 'checked' : '';
            }
            if($themes_check === '' && $plugins_check === ''){
                $themes_plugins_check = '';
            }
            if(isset($this->backup_custom_setting['uploads_option']['uploads_check'])){
                $uploads_check = $this->backup_custom_setting['uploads_option']['uploads_check'] === '1' ? 'checked' : '';
                $uploads_text_style = $this->backup_custom_setting['uploads_option']['uploads_check'] === '1' ? 'pointer-events: auto; opacity: 1;' : 'pointer-events: none; opacity: 0.4;';
            }
            if(isset($this->backup_custom_setting['content_option']['content_check'])){
                $content_check = $this->backup_custom_setting['content_option']['content_check'] === '1' ? 'checked' : '';
                $content_text_style = $this->backup_custom_setting['content_option']['content_check'] === '1' ? 'pointer-events: auto; opacity: 1;' : 'pointer-events: none; opacity: 0.4;';
            }
            if(isset($this->backup_custom_setting['uploads_option']['uploads_extension_list']) && !empty($this->backup_custom_setting['uploads_option']['uploads_extension_list'])){
                $upload_extension = implode(",", $this->backup_custom_setting['uploads_option']['uploads_extension_list']);
            }
            if(isset($this->backup_custom_setting['content_option']['content_extension_list']) && !empty($this->backup_custom_setting['content_option']['content_extension_list'])){
                $content_extension = implode(",", $this->backup_custom_setting['content_option']['content_extension_list']);
            }
        }
        ?>
        <table class="wp-list-table widefat plugins" style="width: 100%; border: 1px solid #f1f1f1;">
            <!-- core -->
            <tr>
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-core-check" <?php esc_attr_e($core_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font">Wordpress Core</td>
                <td class="column-description desc"><?php _e($core_descript); ?></td>
            </tr>
            <!-- themes -->
            <tr style="cursor: pointer;">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-themes-check" <?php esc_attr_e($themes_plugins_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font">Themes</td>
                <td class="column-description desc"><?php _e($themes_descript); ?></td>
            </tr>
            <!-- plugins -->
            <tr style="cursor: pointer;">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-plugins-check" <?php esc_attr_e($themes_plugins_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font">Plugins</td>
                <td class="column-description desc"><?php _e($plugins_descript); ?></td>
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
        </table>
        <?php
    }

    public function display_db_rows(){
        $database_descript = 'Select which tables of database you want to back up. <a href="https://wpvivid.com/wpvivid-backup-pro-database-backup" target="_blank">Learn more</a>';
        $database_check = 'checked';

        if(!empty($this->backup_custom_setting)){
            if(isset($this->backup_custom_setting['database_option']['database_check'])){
                $database_check = $this->backup_custom_setting['database_option']['database_check'] === '1' ? 'checked' : '';
            }
        }
        ?>
        <table class="wp-list-table widefat plugins" style="width: 100%; border: 1px solid #f1f1f1;">
            <!-- database -->
            <tr style="cursor: pointer;">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-database-check" <?php esc_attr_e($database_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font">Database</td>
                <td class="column-description desc"><?php _e($database_descript); ?></td>
            </tr>
        </table>
        <?php
    }

    public function output_local_remote_selector()
    {
        ?>
        <fieldset>
            <div class="mwp-wpvivid-block-bottom-space">
                <label title="">
                    <input type="radio" option="<?php echo $this->option;?>" name="save_local_remote" value="local" checked />
                    <span><?php _e( 'Save backups on localhost (web server)', 'wpvivid' );?></span>
                </label>
            </div>
            <div>
                <label title="">
                    <input type="radio" option="<?php echo $this->option;?>" name="save_local_remote" value="remote" />
                    <span><?php _e( 'Send backups to remote storage (Backups will be deleted from localhost after they are completely uploaded to remote storage.)', 'wpvivid' );?></span>
                </label>
            </div>
            <label style="display: none;">
                <input type="checkbox" option="<?php echo $this->option;?>" name="lock" value="0" />
            </label>
        </fieldset>
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

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-handle-uploads-detail', function(){
                var obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-uploads-detail');
                var sub_obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-custom-detail');
                mwp_wpvivid_handle_custom_open_close(obj, sub_obj);
            });

            jQuery('#<?php echo $this->parent_id; ?>').on("click", '.mwp-wpvivid-handle-content-detail', function(){
                var obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-content-detail');
                var sub_obj = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-custom-detail');
                mwp_wpvivid_handle_custom_open_close(obj, sub_obj);
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', '.mwp-wpvivid-custom-check', function(){
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
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', '.mwp-wpvivid-uploads-extension-rule-btn', function(){
                var value = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-uploads-extension').val();
                if(value !== ''){
                    mwp_wpvivid_add_global_schedule_extension_rule(this, 'uploads', value);
                }
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', '.mwp-wpvivid-content-extension-rule-btn', function(){
                var value = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-content-extension').val();
                if(value !== ''){
                    mwp_wpvivid_add_global_schedule_extension_rule(this, 'content', value);
                }
            });

            jQuery('#<?php echo $this->parent_id; ?>').on('click', '.mwp-wpvivid-additional-folder-extension-rule-btn', function(){
                var value = jQuery('#<?php echo $this->parent_id; ?>').find('.mwp-wpvivid-additional-folder-extension').val();
                if(value !== ''){
                    mwp_wpvivid_add_global_schedule_extension_rule(this, 'additional_folder', value);
                }
            });
        </script>
        <?php
    }
}

class Mainwp_WPvivid_Extension_Incremental_Backup
{
    private $site_id;
    private $incremental_backup_data;

    public function __construct()
    {
        add_filter('mwp_wpvivid_schedule_tabs', array($this, 'add_schedule_tabs'));
    }

    public function set_site_id($site_id)
    {
        $this->site_id=$site_id;
    }

    public function set_incremental_backup_data($incremental_backup_data)
    {
        $this->incremental_backup_data=$incremental_backup_data;
    }

    public function add_schedule_tabs($tabs)
    {
        $args['is_parent_tab']=0;
        $args['transparency']=1;
        $tabs['incremental_backup_schedules']['title']='Incremental Backup Schedule';
        $tabs['incremental_backup_schedules']['slug']='incremental_backup_schedule';
        $tabs['incremental_backup_schedules']['callback']=array($this, 'output_incremental_page');
        $tabs['incremental_backup_schedules']['args']=$args;
        return $tabs;
    }

    public function output_incremental_page($global)
    {
        if($global) {
            ?>
            <div style="margin-top: 10px;">
                <div class="mwp-wpvivid-block-bottom-space" id="mwp_wpvivid_incremental_backup_part_1">
                    <div class="mwp-wpvivid-block-bottom-space" id="mwp_wpvivid_incremental_schedule_mould_list_addon">
                        <?php
                        $incremental_schedule_mould_list = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('incremental_backup_setting', array());
                        if(empty($incremental_schedule_mould_list)){
                            $incremental_schedule_mould_list = array();
                        }
                        $table = new Mainwp_WPvivid_Incremental_Schedule_Mould_List();
                        $table->set_schedule_mould_list($incremental_schedule_mould_list);
                        $table->prepare_items();
                        $table->display();
                        ?>
                    </div>
                    <div>
                        <input class="ui green mini button" type="button" value="<?php esc_attr_e('Create New Incremental Schedule Mould'); ?>" onclick="mwp_wpvivid_create_new_incremental_schedule_mould();" />
                    </div>
                </div>
                <div id="mwp_wpvivid_incremental_backup_part_2" style="display: none;">
                    <?php
                    $this->output_edit_schedule($global);
                    ?>
                </div>
            </div>
            <script>
                function mwp_wpvivid_create_new_incremental_schedule_mould(){
                    jQuery('#mwp_wpvivid_incremental_backup_part_1').hide();
                    jQuery('#mwp_wpvivid_incremental_backup_part_2').show();
                    jQuery('#mwp_wpvivid_incremental_backup_schedule_save').show();
                    jQuery('#mwp_wpvivid_incremental_backup_schedule_update').hide();
                }

                function mwp_wpvivid_edit_incremental_schedule_mould(mould_name){
                    jQuery('#mwp_wpvivid_incremental_backup_schedule_save').hide();
                    jQuery('#mwp_wpvivid_incremental_backup_schedule_update').show();
                    jQuery('#mwp_wpvivid_incremental_schedule_mould_name').val(mould_name);
                    jQuery('#mwp_wpvivid_incremental_schedule_mould_name').attr('disabled', 'disabled');
                    var ajax_data = {
                        'action': 'mwp_wpvivid_edit_global_incremental_schedule_mould_addon',
                        'mould_name': mould_name
                    };
                    mwp_wpvivid_post_request(ajax_data, function (data) {
                        try {
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success') {
                                jQuery('#mwp_wpvivid_incrementa_schedule_recurrence').val(jsonarray.incremental_schedule.incremental_recurrence);
                                jQuery('[option=mwp_incremental_backup][name=recurrence_week]').val(jsonarray.incremental_schedule.incremental_recurrence_week);
                                jQuery('[option=mwp_incremental_backup][name=recurrence_day]').val(jsonarray.incremental_schedule.incremental_recurrence_day);
                                var arr_file = new Array();
                                arr_file = jsonarray.incremental_schedule.files_current_day.split(':');
                                jQuery('select[option=mwp_incremental_backup][name=files_current_day_hour]').each(function() {
                                    jQuery(this).val(arr_file[0]);
                                });
                                jQuery('select[option=mwp_incremental_backup][name=files_current_day_minute]').each(function(){
                                    jQuery(this).val(arr_file[1]);
                                });
                                jQuery('[option=mwp_incremental_backup][name=file_start_time_zone]').val(jsonarray.incremental_schedule.file_start_time_zone);
                                jQuery('[option=mwp_incremental_backup][name=incremental_files_recurrence]').val(jsonarray.incremental_schedule.incremental_files_recurrence);
                                if(jsonarray.incremental_schedule.incremental_files_start_backup == '1'){
                                    jQuery('[option=mwp_incremental_backup][name=incremental_files_start_backup]').prop('checked', true);
                                }
                                else{
                                    jQuery('[option=mwp_incremental_backup][name=incremental_files_start_backup]').prop('checked', false);
                                }
                                //
                                var core_check = true;
                                var themes_check = true;
                                var plugins_check = true;
                                var uploads_check = true;
                                var content_check = true;
                                jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-uploads-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                                jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-content-detail').css({'pointer-events': 'auto', 'opacity': '1'});

                                if(jsonarray.incremental_schedule.backup_files.backup_select.core !== 1){
                                    core_check = false;
                                }
                                if(jsonarray.incremental_schedule.backup_files.backup_select.themes !== 1){
                                    themes_check = false;
                                }
                                if(jsonarray.incremental_schedule.backup_files.backup_select.plugin !== 1){
                                    plugins_check = false;
                                }
                                if(jsonarray.incremental_schedule.backup_files.backup_select.uploads !== 1){
                                    uploads_check = false;
                                    jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-uploads-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                                }
                                if(jsonarray.incremental_schedule.backup_files.backup_select.content !== 1){
                                    content_check = false;
                                    jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-content-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                                }
                                jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-custom-core-check').prop('checked', core_check);
                                jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-custom-themes-check').prop('checked', themes_check);
                                jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-custom-plugins-check').prop('checked', plugins_check);
                                jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-custom-uploads-check').prop('checked', uploads_check);
                                jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-custom-content-check').prop('checked', content_check);
                                jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-uploads-extension').val(jsonarray.incremental_schedule.backup_files.exclude_uploads_files);
                                jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-content-extension').val(jsonarray.incremental_schedule.backup_files.exclude_content_files);
                                //
                                jQuery('#mwp_wpvivid_incrementa_schedule_db_recurrence').val(jsonarray.incremental_schedule.incremental_db_recurrence);
                                jQuery('[option=mwp_incremental_backup][name=incremental_db_recurrence_week]').val(jsonarray.incremental_schedule.incremental_db_recurrence_week);
                                jQuery('[option=mwp_incremental_backup][name=incremental_db_recurrence_day]').val(jsonarray.incremental_schedule.incremental_db_recurrence_day);
                                var arr_db = new Array();
                                arr_db = jsonarray.incremental_schedule.db_current_day.split(':');
                                jQuery('select[option=mwp_incremental_backup][name=db_current_day_hour]').each(function() {
                                    jQuery(this).val(arr_db[0]);
                                });
                                jQuery('select[option=mwp_incremental_backup][name=db_current_day_minute]').each(function(){
                                    jQuery(this).val(arr_db[1]);
                                });
                                jQuery('[option=mwp_incremental_backup][name=db_start_time_zone]').val(jsonarray.incremental_schedule.db_start_time_zone);
                                //
                                var database_check = true;
                                if(jsonarray.incremental_schedule.backup_db.backup_select.db !== 1){
                                    database_check = false;
                                }
                                jQuery('#mwp_wpvivid_incremental_backup_db').find('.mwp-wpvivid-custom-database-check').prop('checked', database_check);
                                //
                                var backup_to = jsonarray.incremental_schedule.backup.local === 1 ? 'local' : 'remote';
                                jQuery('input:radio[option=mwp_incremental_backup][name=save_local_remote][value='+backup_to+']').prop('checked', true);
                                jQuery('#mwp_wpvivid_incremental_remote_max_backup_count').val(jsonarray.incremental_remote_retain);
                                jQuery('#mwp_wpvivid_incremental_backup_schedule_save').text('Update');
                            }
                            else {
                                alert(jsonarray.error);
                            }
                        }
                        catch (err) {
                            alert(err);
                        }
                    }, function (XMLHttpRequest, textStatus, errorThrown) {
                        var error_message = mwp_wpvivid_output_ajaxerror('editing schedule', textStatus, errorThrown);
                        alert(error_message);
                    });
                }

                function mwp_wpvivid_delete_incremental_schedule_mould(mould_name){
                    var ajax_data = {
                        'action': 'mwp_wpvivid_delete_global_incremental_schedule_mould_addon',
                        'mould_name': mould_name
                    };
                    mwp_wpvivid_post_request(ajax_data, function (data) {
                        try {
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success') {
                                jQuery('#mwp_wpvivid_incremental_schedule_mould_list_addon').html(jsonarray.html);
                            }
                            else {
                                alert(jsonarray.error);
                            }
                        }
                        catch (err) {
                            alert(err);
                        }
                    }, function (XMLHttpRequest, textStatus, errorThrown) {
                        var error_message = mwp_wpvivid_output_ajaxerror('editing schedule', textStatus, errorThrown);
                        alert(error_message);
                    });
                }

                jQuery('#mwp_wpvivid_incremental_schedule_mould_list_addon').on('click', '.mwp-wpvivid-sync-incremental-schedule-mould', function(){
                    var Obj=jQuery(this);
                    var mould_name=Obj.closest('tr').attr('slug');
                    window.location.href = window.location.href + "&synchronize=1&addon=1&is_incremental=1&mould_name=" + mould_name;
                });

                jQuery('#mwp_wpvivid_incremental_schedule_mould_list_addon').on('click', '.mwp-wpvivid-incremental-schedule-mould-edit', function(){
                    jQuery('#mwp_wpvivid_incremental_backup_part_1').hide();
                    jQuery('#mwp_wpvivid_incremental_backup_part_2').show();
                    var Obj=jQuery(this);
                    var mould_name=Obj.closest('tr').attr('slug');
                    mwp_wpvivid_edit_incremental_schedule_mould(mould_name);
                });

                jQuery('#mwp_wpvivid_incremental_schedule_mould_list_addon').on('click', '.mwp-wpvivid-incremental-schedule-mould-delete', function(){
                    var descript = 'Are you sure to remove this schedule mould?';
                    var ret = confirm(descript);
                    if(ret === true) {
                        var Obj = jQuery(this);
                        var mould_name = Obj.closest('tr').attr('slug');
                        mwp_wpvivid_delete_incremental_schedule_mould(mould_name);
                    }
                });
            </script>
            <?php
        }
        else{
            ?>
            <div id="mwp_wpvivid_enable_incremental_backup" class="postbox" style="margin-top: 10px; padding: 10px;">
                <p>Incremental backups can save you time and storage space.</p>
                <p><?php echo sprintf(__('When it is enabled, %s Pro will create a full backup of all files on your site, then it will only back up files that are newly created or changed since the last full backup.', 'wpvivid'), apply_filters('wpvivid_white_label_display', 'WPvivid Backup')); ?></p>
                <p>Database cannot be incrementally backed up, you'll need to set a backup schedule for database separately.</p>
                <p><a onclick="mwp_wpvivid_restore_incremental_descript_add();" style="cursor: pointer;">How to restore a incremental backup?</a></p>
                <div class="mwp-wpvivid-click-popup" id="mwp_wpvivid_restore_incremental_desc_add" style="display: none; margin-bottom: 10px;">
                    <div>1. Go to Backups & Restore tab, then Localhost or Remote Storage sub-tab, depending on where the incremental backups are stored.</div>
                    <div>2. Select Incremental in combox to scan and display the incremental backups.</div>
                    <div>3. Click Restore button of a incremental backup.</div>
                </div>
                <div style="clear: both;"></div>
                <div class="card" style="margin-left: auto;margin-right: auto;margin-top: 10px">
                    <div style="margin-top: 10px">
                        <strong>Enabling incremental backup schedules</strong>
                        <span>requires disabling all other scheduled backup tasks first. Running multiple schedules at the same time will comsume more server resources, which is not recommended.</span>
                    </div>
                    <br>
                    <div>
                        <input class="button-primary" type="submit" value="Disable Other Schedules and Run the Incremental Backup" onclick="mwp_wpvivid_start_incremental_backup(1);"/>
                    </div>
                </div>
            </div>
            <?php
            $enable_incremental_schedules = isset($this->incremental_backup_data['enable_incremental_schedules']) && !empty($this->incremental_backup_data['enable_incremental_schedules']) ? $this->incremental_backup_data['enable_incremental_schedules'] : '0';
            $schedules = isset($this->incremental_backup_data['incremental_schedules']) ? $this->incremental_backup_data['incremental_schedules'] : array();
            $this->output_edit_schedule($global);
            $this->output_incremental_schedule($global);
            ?>
            <script>
                var mwp_wpvivid_refresh_incremental_table_retry_times = 0;

                function mwp_wpvivid_restore_incremental_descript_add()
                {
                    if(jQuery('#mwp_wpvivid_restore_incremental_desc_add').is(":hidden"))
                    {
                        jQuery('#mwp_wpvivid_restore_incremental_desc_add').show();
                    }
                    else{
                        jQuery('#mwp_wpvivid_restore_incremental_desc_add').hide();
                    }
                }

                function mwp_wpvivid_refresh_incremental_backup_table()
                {
                    var ajax_data = {
                        'action': 'mwp_wpvivid_refresh_incremental_tables',
                        'site_id': '<?php echo esc_html($this->site_id); ?>'
                    };
                    mwp_wpvivid_post_request(ajax_data, function (data){
                        try {
                            var json = jQuery.parseJSON(data);
                            if (json.result === 'success') {
                                mwp_wpvivid_refresh_incremental_table_retry_times = 0;
                                jQuery('#mwp_wpvivid_incremental_backup_db').find('.mwp-wpvivid-custom-database-info').html(json.database_tables);
                                jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-custom-themes-plugins-info').html(json.themes_plugins_table);
                            }
                            else{
                                mwp_wpvivid_refresh_incremental_table_retry();
                            }
                        }
                        catch(err) {
                            mwp_wpvivid_refresh_incremental_table_retry();
                        }
                    }, function (XMLHttpRequest, textStatus, errorThrown) {
                        mwp_wpvivid_refresh_incremental_table_retry();
                    });
                }

                function mwp_wpvivid_refresh_incremental_table_retry()
                {
                    var need_retry_incremental_table = false;
                    mwp_wpvivid_refresh_incremental_table_retry_times++;
                    if(mwp_wpvivid_refresh_incremental_table_retry_times < 10){
                        need_retry_incremental_table = true;
                    }
                    if(need_retry_incremental_table){
                        setTimeout(function(){
                            mwp_wpvivid_refresh_incremental_backup_table();
                        }, 3000);
                    }
                    else{
                        var refresh_btn = '<input class="ui green mini button" type="button" value="Refresh" onclick="mwp_wpvivid_refresh_incremental_backup();">';
                        jQuery('#mwp_wpvivid_incremental_backup_db').find('.mwp-wpvivid-custom-database-info').html(refresh_btn);
                        jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-custom-themes-plugins-info').html(refresh_btn);
                    }
                }

                function mwp_wpvivid_refresh_incremental_backup()
                {
                    mwp_wpvivid_refresh_incremental_table_retry_times = 0;
                    var custom_database_loading = '<div class="spinner is-active" style="margin: 0 5px 10px 0; float: left;"></div>' +
                        '<div style="float: left;">Archieving ...</div>' +
                        '<div style="clear: both;"></div>';
                    jQuery('#mwp_wpvivid_incremental_backup_db').find('.mwp-wpvivid-custom-database-info').html(custom_database_loading);
                    jQuery('#mwp_wpvivid_incremental_backup_files').find('.mwp-wpvivid-custom-themes-plugins-info').html(custom_database_loading);
                    mwp_wpvivid_refresh_incremental_backup_table();
                }

                jQuery(document).ready(function ()
                {
                    <?php
                    if($enable_incremental_schedules == '0' || empty($schedules))
                    {
                    ?>
                    jQuery('#mwp_wpvivid_output_incremental_schedule').hide();
                    <?php
                    }
                    else
                    {
                    ?>
                    jQuery('#mwp_wpvivid_create_new_incremental_schedule').hide();
                    <?php
                    }
                    ?>
                    var incremental_backup_refresh = false;

                    jQuery(document).on('mwp_wpvivid_refresh_incremental_custom_backup_tables', function(event){
                        event.stopPropagation();
                        if(!incremental_backup_refresh){
                            incremental_backup_refresh = true;
                            mwp_wpvivid_refresh_incremental_backup_table();
                        }
                    });
                });
            </script>
            <?php
        }
    }

    public function output_edit_schedule($global)
    {
        if($global){
            ?>
            <div id="mwp_wpvivid_create_new_incremental_schedule">
                <div class="mwp-wpvivid-block-bottom-space">
                    <span>Input a name:</span>
                    <input id="mwp_wpvivid_incremental_schedule_mould_name" />
                </div>
                <div id="mwp_wpvivid_edit_incremental_backup">
                    <div style="width:100%; border:1px solid #e5e5e5; float:left; box-sizing: border-box;margin-bottom:10px;">
                        <div class="mwp-wpvivid-block-bottom-space" style="margin: 1px 1px 10px 1px; background-color: #f7f7f7; box-sizing: border-box; padding: 10px;">Set file backup cycle and start time:</div>
                        <div class="mwp-wpvivid-block-bottom-space" style="margin-left: 10px; margin-right: 10px;">
                            <div style="padding: 4px 10px 0 0; float: left;">Files full backup cycle</div>
                            <div style="padding: 0 10px 0 0; float: left;">
                                <select id="mwp_wpvivid_incrementa_schedule_recurrence" option="mwp_incremental_backup" name="recurrence" onchange="mwp_change_incremental_backup_recurrence();">
                                    <option value="wpvivid_weekly" selected="selected">Weekly</option>
                                    <option value="wpvivid_fortnightly">Fortnightly</option>
                                    <option value="wpvivid_monthly">Every 30 days</option>
                                </select>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                        <div style="clear: both;"></div>
                        <div class="mwp-wpvivid-block-bottom-space" style="margin-left: 10px; margin-right: 10px;">
                            <div style="padding: 4px 10px 0 0; float: left;">Files incremental backup will run at</div>
                            <div id="mwp_wpvivid_incrementa_schedule_backup_start_week" style="float: left; padding: 0 10px 0 0;">
                                <select option="mwp_incremental_backup" name="recurrence_week">
                                    <option value="sun">Sunday</option>
                                    <option value="mon" selected="selected">Monday</option>
                                    <option value="tue">Tuesday</option>
                                    <option value="wed">Wednesday</option>
                                    <option value="thu">Thursday</option>
                                    <option value="fri">Friday</option>
                                    <option value="sat">Saturday</option>
                                </select>
                            </div>
                            <div id="mwp_wpvivid_incrementa_schedule_backup_start_day" style="float: left; display: none; padding: 0 10px 0 0;">
                                <select option="mwp_incremental_backup" name="recurrence_day">
                                    <?php
                                    $html='';
                                    for($i=1;$i<31;$i++)
                                    {
                                        $html.='<option value="'.$i.'">'.$i.'</option>';
                                    }
                                    echo $html;
                                    ?>
                                </select>
                            </div>
                            <div style="float: left; padding: 0 10px 0 0;">
                                <select option="mwp_incremental_backup" name="files_current_day_hour" style="margin-bottom: 4px;">
                                    <?php
                                    $html='';
                                    for($hour=0; $hour<24; $hour++){
                                        $format_hour = sprintf("%02d", $hour);
                                        $html .= '<option value="'.$format_hour.'">'.$format_hour.'</option>';
                                    }
                                    echo $html;
                                    ?>
                                </select>
                                <span>:</span>
                                <select option="mwp_incremental_backup" name="files_current_day_minute" style="margin-bottom: 4px;">
                                    <?php
                                    $html='';
                                    for($minute=0; $minute<60; $minute++){
                                        $format_minute = sprintf("%02d", $minute);
                                        $html .= '<option value="'.$format_minute.'">'.$format_minute.'</option>';
                                    }
                                    echo $html;
                                    ?>
                                </select>
                            </div>
                            <div style="padding: 4px 10px 0 0; float: left;">in</div>
                            <div style="padding: 0 10px 0 0; float: left;">
                                <select option="mwp_incremental_backup" name="file_start_time_zone" style="margin-bottom: 4px;">
                                    <option value="utc" selected>UTC Time</option>
                                    <option value="local">Local Time</option>
                                </select>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                        <div style="clear: both;"></div>
                        <div class="mwp-wpvivid-block-bottom-space" style="margin-left: 10px; margin-right: 10px;">
                            <div style="padding: 4px 10px 0 0; float: left;">Files incremental backup cycle</div>
                            <div style="padding: 0 10px 0 0; float: left;">
                                <select option="mwp_incremental_backup" name="incremental_files_recurrence">
                                    <option value="wpvivid_hourly">Every hour</option>
                                    <option value="wpvivid_2hours">Every 2 hours</option>
                                    <option value="wpvivid_4hours">Every 4 hours</option>
                                    <option value="wpvivid_8hours">Every 8 hours</option>
                                    <option value="wpvivid_12hours">Every 12 hours</option>
                                    <option value="wpvivid_daily" >Daily</option>
                                </select>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                        <div style="clear: both;"></div>
                        <div class="mwp-wpvivid-block-bottom-space" style="margin-left: 10px; margin-right: 10px;">
                            <label>
                                <input type="checkbox" option="mwp_incremental_backup" name="incremental_files_start_backup" style="margin-top:2px; margin-left: 0;" />
                                <span>Run a full backup immediately after the new incremental backup schedule is created.</span>
                            </label>
                        </div>
                        <div style="clear: both;"></div>
                        <div id="mwp_wpvivid_incremental_backup_files_select" style="margin: 1px; background-color: #f7f7f7; box-sizing: border-box; padding: 10px; cursor: pointer;">
                            <div style="float: left;">
                                Choose which folders to run incremental backups:
                            </div>
                            <div style="float: right;">
                                <details class="primer" onclick="return false;" style="display: inline-block; width: 100%;">
                                    <summary title="Show detail" style="float: right; color: #a0a5aa;"></summary>
                                </details>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                        <div id="mwp_wpvivid_incremental_backup_files" style="padding: 10px; display: none;">
                            <?php
                            $custom_backup_selector=new Mainwp_WPvivid_Extension_Global_Custom_Backup_Selector('mwp_wpvivid_edit_incremental_backup','mwp_incremental_backup', $this->incremental_backup_data);
                            $custom_backup_selector->display_file_rows();
                            ?>
                        </div>
                    </div>
                    <div style="width:100%; border:1px solid #e5e5e5; float:left; box-sizing: border-box;margin-bottom:10px;">
                        <div class="mwp-wpvivid-block-bottom-space" style="margin: 1px 1px 10px 1px; background-color: #f7f7f7; box-sizing: border-box; padding: 10px;">Set database backup cycle and start time:</div>
                        <div class="mwp-wpvivid-block-bottom-space" style="margin-left: 10px; margin-right: 10px;">
                            <div style="padding: 4px 10px 0 0; float: left;">Database backup will run</div>
                            <div style="float: left; padding: 0 10px 0 0;">
                                <select id="mwp_wpvivid_incrementa_schedule_db_recurrence" option="mwp_incremental_backup" name="incremental_db_recurrence" onchange="mwp_change_incremental_backup_db_recurrence();">
                                    <option value="wpvivid_hourly">Every hour</option>
                                    <option value="wpvivid_2hours">Every 2 hours</option>
                                    <option value="wpvivid_4hours">Every 4 hours</option>
                                    <option value="wpvivid_8hours">Every 8 hours</option>
                                    <option value="wpvivid_12hours">Every 12 hours</option>
                                    <option value="wpvivid_daily">Daily</option>
                                    <option value="wpvivid_weekly" selected="selected">Weekly</option>
                                    <option value="wpvivid_fortnightly">Fortnightly</option>
                                    <option value="wpvivid_monthly">Every 30 days</option>
                                </select>
                            </div>
                            <div style="padding: 4px 10px 0 0; float: left;">at</div>
                            <div id="mwp_wpvivid_incrementa_schedule_backup_db_start_week" style="float: left; padding: 0 10px 0 0;">
                                <select option="mwp_incremental_backup" name="incremental_db_recurrence_week">
                                    <option value="sun">Sunday</option>
                                    <option value="mon" selected="selected">Monday</option>
                                    <option value="tue">Tuesday</option>
                                    <option value="wed">Wednesday</option>
                                    <option value="thu">Thursday</option>
                                    <option value="fri">Friday</option>
                                    <option value="sat">Saturday</option>
                                </select>
                            </div>
                            <div id="mwp_wpvivid_incrementa_schedule_backup_db_start_day" style="display: none; padding: 0 10px 0 0; float: left;">
                                <select option="mwp_incremental_backup" name="incremental_db_recurrence_day">
                                    <?php
                                    $html='';
                                    for($i=1;$i<31;$i++)
                                    {
                                        $html.='<option value="'.$i.'">'.$i.'</option>';
                                    }
                                    echo $html;
                                    ?>
                                </select>
                            </div>
                            <div class="wpvivid-schedule-time-select-addon" style="padding: 0 10px 0 0; float: left;">
                                <select option="mwp_incremental_backup" name="db_current_day_hour" style="margin-bottom: 4px;">
                                    <?php
                                    $html='';
                                    for($hour=0; $hour<24; $hour++)
                                    {
                                        $format_hour = sprintf("%02d", $hour);
                                        $html .= '<option value="'.$format_hour.'">'.$format_hour.'</option>';
                                    }
                                    echo $html;
                                    ?>
                                </select>
                                <span>:</span>
                                <select option="mwp_incremental_backup" name="db_current_day_minute" style="margin-bottom: 4px;">
                                    <?php
                                    $html='';
                                    for($minute=0; $minute<60; $minute++)
                                    {
                                        $format_minute = sprintf("%02d", $minute);
                                        $html .= '<option value="'.$format_minute.'">'.$format_minute.'</option>';
                                    }
                                    echo $html;
                                    ?>
                                </select>
                            </div>
                            <div style="padding: 4px 10px 0 0; float: left;">in</div>
                            <div style="padding: 0 10px 0 0; float: left;">
                                <select option="mwp_incremental_backup" name="db_start_time_zone" style="margin-bottom: 4px;">
                                    <option value="utc" selected>UTC Time</option>
                                    <option value="local">Local Time</option>
                                </select>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                        <div style="clear: both;"></div>
                        <div id="mwp_wpvivid_incremental_backup_db_select" style="margin: 1px; background-color: #f7f7f7; box-sizing: border-box; padding: 10px; cursor: pointer;">
                            <div style="float: left;">
                                Choose databases to backup:
                            </div>
                            <div style="float: right;">
                                <details class="primer" onclick="return false;" style="display: inline-block; width: 100%;">
                                    <summary title="Show detail" style="float: right; color: #a0a5aa;"></summary>
                                </details>
                            </div>
                            <div style="clear: both;"></div>
                        </div>
                        <div id="mwp_wpvivid_incremental_backup_db" style="padding: 10px; display: none;">
                            <?php
                            $custom_backup_selector->display_db_rows();
                            $custom_backup_selector->load_js();
                            ?>
                        </div>
                    </div>
                    <div style="width:100%; border:1px solid #e5e5e5; float:left; box-sizing: border-box;margin-bottom:10px;">
                        <div class="mwp-wpvivid-block-bottom-space" style="margin: 1px 1px 10px 1px; background-color: #f7f7f7; box-sizing: border-box; padding: 10px;">Choose where to send backups:</div>
                        <div class="mwp-wpvivid-block-bottom-space" style="margin-left: 10px; margin-right: 10px;">
                            <?php
                            $custom_backup_selector->output_local_remote_selector();
                            ?>
                        </div>
                        <div id="mwp_wpvivid_incremental_remote_backup_count_setting" style="margin-left: 10px; margin-right: 10px; display: none;">
                            <div>
                                <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-font-right-space" style="float: left;">
                                    <input type="text" id="mwp_wpvivid_incremental_remote_max_backup_count" value="30" style="width: 50px;" />
                                </div>
                                <div class="mwp-wpvivid-block-bottom-space wpvivid-setting-text-fix" style="float: left; height: 28px; line-height: 28px;"><strong><?php _e('Incremental backups retained for each remote storage', 'wpvivid'); ?></strong></div>
                                <div style="clear: both;"></div>
                            </div>
                        </div>
                    </div>
                    <div style="clear: both;"></div>

                    <div>
                        <div id="mwp_wpvivid_global_incremental_backup_schedule_notice" style="padding-left: 12px; padding-right: 12px;"></div>
                        <input id="mwp_wpvivid_incremental_backup_schedule_save" class="button-primary" type="submit" value="Create the new incremental schedule" onclick="mwp_wpvivid_click_create_incremental_schedule();" />
                        <input id="mwp_wpvivid_incremental_backup_schedule_update" class="button-primary" type="submit" value="Update incremental schedule" onclick="mwp_wpvivid_click_update_incremental_schedule();" style="display: none;" />
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>

            <script>
                jQuery('input:radio[option=mwp_incremental_backup][name=save_local_remote]').click(function(){
                    var value = jQuery(this).val();
                    if(value === 'local'){
                        jQuery('#mwp_wpvivid_incremental_remote_backup_count_setting').hide();
                    }
                    else if(value === 'remote'){
                        jQuery('#mwp_wpvivid_incremental_remote_backup_count_setting').show();
                    }
                });

                jQuery('#mwp_wpvivid_back_prev_page').on('click', function(){
                    jQuery('#mwp_wpvivid_output_incremental_schedule').show();
                    jQuery('#mwp_wpvivid_create_new_incremental_schedule').hide();
                });

                jQuery('#mwp_wpvivid_incremental_backup_files_select').click(function(){
                    if(jQuery('#mwp_wpvivid_incremental_backup_files').is(":hidden")) {
                        jQuery(this).find('details').prop('open', true);
                        jQuery('#mwp_wpvivid_incremental_backup_files').show();
                        jQuery( document ).trigger( 'mwp_wpvivid_refresh_incremental_custom_backup_tables' );
                    }
                    else{
                        jQuery(this).find('details').prop('open', false);
                        jQuery('#mwp_wpvivid_incremental_backup_files').hide();
                    }
                });

                jQuery('#mwp_wpvivid_incremental_backup_db_select').click(function () {
                    if(jQuery('#mwp_wpvivid_incremental_backup_db').is(":hidden")) {
                        jQuery(this).find('details').prop('open', true);
                        jQuery('#mwp_wpvivid_incremental_backup_db').show();
                        jQuery( document ).trigger( 'mwp_wpvivid_refresh_incremental_custom_backup_tables' );
                    }
                    else{
                        jQuery(this).find('details').prop('open', false);
                        jQuery('#mwp_wpvivid_incremental_backup_db').hide();
                    }
                });

                function mwp_change_incremental_backup_recurrence() {
                    jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_day').hide();
                    jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_week').hide();
                    var select_value = jQuery('#mwp_wpvivid_incrementa_schedule_recurrence').val();
                    if(select_value === 'wpvivid_weekly' || select_value === 'wpvivid_fortnightly')
                    {
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_week').show();
                    }
                    else if(select_value === 'wpvivid_monthly')
                    {
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_day').show();
                    }
                }

                function mwp_change_incremental_backup_db_recurrence() {
                    jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_week').hide();
                    jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_day').hide();
                    var select_value = jQuery('#mwp_wpvivid_incrementa_schedule_db_recurrence').val();
                    if(select_value === 'wpvivid_weekly' || select_value === 'wpvivid_fortnightly') {
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_week').show();
                    }
                    else if(select_value === 'wpvivid_monthly'){
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_day').show();
                    }
                }

                function mwp_wpvivid_create_incremental_json(parent_id, incremental_type){
                    var json = {};
                    jQuery('#'+parent_id).find('.mwp-wpvivid-custom-check').each(function(){
                        if(incremental_type === 'files'){
                            if(jQuery(this).hasClass('mwp-wpvivid-custom-core-check')){
                                if(jQuery(this).prop('checked')){
                                    json['core_check'] = '1';
                                }
                                else{
                                    json['core_check'] = '0';
                                }
                            }
                            else if(jQuery(this).hasClass('mwp-wpvivid-custom-themes-check')){
                                if(jQuery(this).prop('checked')){
                                    json['themes_check'] = '1';
                                }
                                else{
                                    json['themes_check'] = '0';
                                }
                            }
                            else if(jQuery(this).hasClass('mwp-wpvivid-custom-plugins-check')){
                                if(jQuery(this).prop('checked')){
                                    json['plugins_check'] = '1';
                                }
                                else{
                                    json['plugins_check'] = '0';
                                }
                            }
                            else if(jQuery(this).hasClass('mwp-wpvivid-custom-uploads-check')){
                                if(jQuery(this).prop('checked')){
                                    json['uploads_check'] = '1';
                                    json['upload_extension'] = jQuery('#'+parent_id).find('.mwp-wpvivid-uploads-extension').val();
                                }
                                else{
                                    json['uploads_check'] = '0';
                                }
                            }
                            else if(jQuery(this).hasClass('mwp-wpvivid-custom-content-check')){
                                if(jQuery(this).prop('checked')){
                                    json['content_check'] = '1';
                                    json['content_extension'] = jQuery('#'+parent_id).find('.mwp-wpvivid-content-extension').val();
                                }
                                else{
                                    json['content_check'] = '0';
                                }
                            }
                        }
                        else if(incremental_type === 'database'){
                            if(jQuery(this).hasClass('mwp-wpvivid-custom-database-check')){
                                if(jQuery(this).prop('checked')){
                                    json['database_check'] = '1';
                                }
                                else{
                                    json['database_check'] = '0';
                                }
                            }
                        }
                    });
                    return json;
                }

                function mwp_get_wpvivid_sync_time(option_name,current_day_hour,current_day_minute) {
                    var hour='00';
                    var minute='00';
                    jQuery('select[option='+option_name+'][name='+current_day_hour+']').each(function()
                    {
                        hour=jQuery(this).val();
                    });
                    jQuery('select[option='+option_name+'][name='+current_day_minute+']').each(function(){
                        minute=jQuery(this).val();
                    });

                    hour=Number(hour)-Number(time_offset);

                    var Hours=Math.floor(hour);
                    var Minutes=Math.floor(60*(hour-Hours));

                    Minutes=Number(minute)+Minutes;
                    if(Minutes>=60)
                    {
                        Hours=Hours+1;
                        Minutes=Minutes-60;
                    }

                    if(Hours>=24)
                    {
                        Hours=Hours-24;
                    }
                    else if(Hours<0)
                    {
                        Hours=24-Math.abs(Hours);
                    }
                    if(Hours<10)
                    {
                        Hours='0'+Hours;
                    }

                    if(Minutes<10)
                    {
                        Minutes='0'+Minutes;
                    }

                    return Hours+":"+Minutes;
                }

                function mwp_wpvivid_click_create_incremental_schedule(){
                    /*if(mwp_wpvivid_start_incremental === 0){
                        var descript = 'Update will reset schedule, continue?';
                        var ret = confirm(descript);
                        if (ret !== true) {
                            return;
                        }
                    }*/
                    var file_json = mwp_wpvivid_create_incremental_json('mwp_wpvivid_edit_incremental_backup', 'files');
                    var db_json = mwp_wpvivid_create_incremental_json('mwp_wpvivid_edit_incremental_backup', 'database');

                    var schedule_data = mwp_wpvivid_ajax_data_transfer('mwp_incremental_backup');
                    schedule_data = JSON.parse(schedule_data);
                    var db_current_day=mwp_get_wpvivid_sync_time('mwp_incremental_backup','db_current_day_hour','db_current_day_minute');
                    var files_current_day=mwp_get_wpvivid_sync_time('mwp_incremental_backup','files_current_day_hour','files_current_day_minute');
                    var current_day = {
                        'db_current_day': db_current_day,
                        'files_current_day': files_current_day,
                    };
                    var custom = {};
                    custom['custom'] = {
                        'files': file_json,
                        'db': db_json,
                    };
                    //custom = JSON.stringify(custom);
                    jQuery.extend(schedule_data, current_day);
                    jQuery.extend(schedule_data, custom);
                    schedule_data = JSON.stringify(schedule_data);

                    var incremental_remote_backup_retain = jQuery('#mwp_wpvivid_incremental_remote_max_backup_count').val();

                    var incremental_schedule_mould_name = jQuery('#mwp_wpvivid_incremental_schedule_mould_name').val();
                    if(incremental_schedule_mould_name == ''){
                        alert('A schedule mould name is required.');
                        return;
                    }

                    var ajax_data = {
                        'action': 'mwp_wpvivid_set_global_incremental_backup_schedule',
                        'schedule': schedule_data,
                        'incremental_schedule_mould_name': incremental_schedule_mould_name,
                        //'start':mwp_wpvivid_start_incremental,
                        'incremental_remote_retain': incremental_remote_backup_retain
                        //'custom':custom
                    };
                    jQuery('#mwp_wpvivid_incremental_backup_schedule_create_notice').html('');
                    mwp_wpvivid_post_request(ajax_data, function (data)
                    {
                        try
                        {
                            jQuery('#mwp_wpvivid_incremental_backup_schedule_save').css({'pointer-events': 'auto', 'opacity': '1'});
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success')
                            {
                                /*if(mwp_wpvivid_start_incremental)
                                {
                                    jQuery('#mwp_wpvivid_schedule_list_addon').html(jsonarray.html);
                                    mwp_wpvivid_start_incremental=0;
                                }

                                jQuery('#mwp_wpvivid_output_incremental_schedule').show();
                                jQuery('#mwp_wpvivid_create_new_incremental_schedule').hide();

                                var all_schedule=jsonarray.data.all_schedule;
                                var db_schedule=jsonarray.data.db_schedule;
                                var db_next_start=jsonarray.data.db_next_start;
                                var files_schedule=jsonarray.data.files_schedule;
                                var files_next_start=jsonarray.data.files_next_start;
                                var next_start_of_all_files=jsonarray.data.next_start_of_all_files;
                                init_incremental_page(all_schedule,next_start_of_all_files,files_schedule,files_next_start,db_schedule,db_next_start);*/
                                jQuery('#mwp_wpvivid_incremental_backup_part_1').show();
                                jQuery('#mwp_wpvivid_incremental_backup_part_2').hide();
                                jQuery('#mwp_wpvivid_global_incremental_backup_schedule_notice').html(jsonarray.notice);
                                jQuery('#mwp_wpvivid_incremental_schedule_mould_list_addon').html(jsonarray.html);
                            }
                            else {
                                jQuery('#mwp_wpvivid_global_incremental_backup_schedule_notice').html(jsonarray.notice);
                            }
                        }
                        catch (err)
                        {
                            alert(err);
                        }
                    }, function (XMLHttpRequest, textStatus, errorThrown)
                    {
                        var error_message = mwp_wpvivid_output_ajaxerror('changing schedule', textStatus, errorThrown);
                        alert(error_message);
                    });
                }

                function mwp_wpvivid_click_update_incremental_schedule(){
                    var file_json = mwp_wpvivid_create_incremental_json('mwp_wpvivid_edit_incremental_backup', 'files');
                    var db_json = mwp_wpvivid_create_incremental_json('mwp_wpvivid_edit_incremental_backup', 'database');

                    var schedule_data = mwp_wpvivid_ajax_data_transfer('mwp_incremental_backup');
                    schedule_data = JSON.parse(schedule_data);
                    var db_current_day=mwp_get_wpvivid_sync_time('mwp_incremental_backup','db_current_day_hour','db_current_day_minute');
                    var files_current_day=mwp_get_wpvivid_sync_time('mwp_incremental_backup','files_current_day_hour','files_current_day_minute');
                    var current_day = {
                        'db_current_day': db_current_day,
                        'files_current_day': files_current_day,
                    };
                    var custom = {};
                    custom['custom'] = {
                        'files': file_json,
                        'db': db_json,
                    };
                    jQuery.extend(schedule_data, current_day);
                    jQuery.extend(schedule_data, custom);
                    schedule_data = JSON.stringify(schedule_data);

                    var incremental_remote_backup_retain = jQuery('#mwp_wpvivid_incremental_remote_max_backup_count').val();

                    var incremental_schedule_mould_name = jQuery('#mwp_wpvivid_incremental_schedule_mould_name').val();
                    if(incremental_schedule_mould_name == ''){
                        alert('A schedule mould name is required.');
                        return;
                    }

                    var ajax_data = {
                        'action': 'mwp_wpvivid_update_global_incremental_backup_schedule',
                        'schedule': schedule_data,
                        'incremental_schedule_mould_name': incremental_schedule_mould_name,
                        'incremental_remote_retain': incremental_remote_backup_retain
                    };
                    jQuery('#mwp_wpvivid_incremental_backup_schedule_create_notice').html('');
                    mwp_wpvivid_post_request(ajax_data, function (data)
                    {
                        try
                        {
                            jQuery('#mwp_wpvivid_incremental_backup_schedule_save').css({'pointer-events': 'auto', 'opacity': '1'});
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success')
                            {
                                jQuery('#mwp_wpvivid_incremental_backup_part_1').show();
                                jQuery('#mwp_wpvivid_incremental_backup_part_2').hide();
                                jQuery('#mwp_wpvivid_global_incremental_backup_schedule_notice').html(jsonarray.notice);
                                jQuery('#mwp_wpvivid_incremental_schedule_mould_list_addon').html(jsonarray.html);
                            }
                            else {
                                jQuery('#mwp_wpvivid_global_incremental_backup_schedule_notice').html(jsonarray.notice);
                            }
                        }
                        catch (err)
                        {
                            alert(err);
                        }
                    }, function (XMLHttpRequest, textStatus, errorThrown)
                    {
                        var error_message = mwp_wpvivid_output_ajaxerror('changing schedule', textStatus, errorThrown);
                        alert(error_message);
                    });
                }

                jQuery(document).ready(function (){
                    var files_current_day_hour='01';
                    jQuery('[option=mwp_incremental_backup][name=files_current_day_hour]').val(files_current_day_hour);
                });
            </script>
            <?php
        }
        else{
            $time_zone=Mainwp_WPvivid_Extension_Option::get_instance()->wpvivid_get_single_option($this->site_id, 'time_zone', '');
            if(empty($time_zone)){
                $time_zone = 0;
            }
            $location = 'options-general.php';
            $mwp_wpvivid_timezone = 'admin.php?page=SiteOpen&newWindow=yes&websiteid='.$this->site_id.'&location='.base64_encode($location);

            $enable_incremental_schedules = isset($this->incremental_backup_data['enable_incremental_schedules']) ? $this->incremental_backup_data['enable_incremental_schedules'] : '0';
            $schedules = isset($this->incremental_backup_data['incremental_schedules']) ? $this->incremental_backup_data['incremental_schedules'] : array();
            $incremental_remote_backup_count = isset($this->incremental_backup_data['incremental_remote_backup_count']) ? $this->incremental_backup_data['incremental_remote_backup_count'] : 30;
            if(empty($schedules))
            {
                $recurrence='wpvivid_weekly';
                $incremental_files_recurrence='wpvivid_hourly';
                $incremental_db_recurrence='wpvivid_weekly';
                $incremental_files_recurrence_week='mon';
                $incremental_files_recurrence_day='1';
                $incremental_db_recurrence_week='mon';
                $incremental_db_recurrence_day='1';
                $db_current_day_hour='00';
                $db_current_day_minute='00';
                $files_current_day_hour='01';
                $files_current_day_minute='00';
                $backup_to='local';

                $btn='Create the new incremental schedule';
            }
            else
            {
                $schedule=array_shift($schedules);
                $recurrence=$schedule['incremental_recurrence'];
                $incremental_files_recurrence=$schedule['incremental_files_recurrence'];
                $incremental_db_recurrence=$schedule['incremental_db_recurrence'];

                $incremental_files_recurrence_week=isset($schedule['incremental_recurrence_week']) ? $schedule['incremental_recurrence_week'] : 'mon';
                $incremental_files_recurrence_day=isset($schedule['incremental_recurrence_day']) ? $schedule['incremental_recurrence_day'] : '1';

                $incremental_db_recurrence_week=isset($schedule['incremental_db_recurrence_week']) ? $schedule['incremental_db_recurrence_week'] : 'mon';
                $incremental_db_recurrence_day=isset($schedule['incremental_db_recurrence_day']) ? $schedule['incremental_db_recurrence_day'] : '1';

                $db_current_day_hour=$schedule['db_current_day_hour'];
                $db_current_day_minute=$schedule['db_current_day_minute'];

                $files_current_day_hour=$schedule['files_current_day_hour'];
                $files_current_day_minute=$schedule['files_current_day_minute'];

                if($schedule['backup']['remote'])
                {
                    $backup_to='remote';
                }
                else
                {
                    $backup_to='local';
                }

                $btn='Update schedule';
            }
            ?>
            <div id="mwp_wpvivid_create_new_incremental_schedule">
                <div style="margin-right: 10px; margin-top: 10px;">
                    <div class="mwp-wpvivid-block-right-space" id="mwp_wpvivid_disable_incremental_backup" style="float: left;">
                        <input class="button-primary" type="submit" value="Disable incremental backups" onclick="mwp_wpvivid_enable_incremental_backup(0);" />
                    </div>
                    <div class="mwp-wpvivid-block-right-space" id="mwp_wpvivid_back_prev_page" style="float: left;">
                        <input class="button-primary" type="submit" value="Back to prev page" />
                    </div>
                    <div style="clear: both;"></div>
                </div>
                <div id="mwp_wpvivid_edit_incremental_backup">
                    <div style="margin-top: 10px;">
                        <div class="mwp-wpvivid-block-bottom-space">
                            <div style="margin-bottom:10px;padding-right:10px;float:left;">
                                <i><strong>UTC Time: </strong><?php echo date("l, F d, Y H:i",time()); ?></i>
                            </div>
                            <div style="margin-bottom:10px;padding-right:10px;float:left;">
                                <i><strong>Local Time: </strong><?php echo date("l, F d, Y H:i",time()+$time_zone*60*60); ?></i><span> | <a href="<?php esc_attr_e($mwp_wpvivid_timezone); ?>">(Timezone Setting)</a></span>
                            </div>
                        </div>

                        <div class="mwp-wpvivid-block-bottom-space">
                            <table class="wp-list-table widefat plugin">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th class="manage-column column-primary"><strong>Local Time </strong><a href="<?php esc_attr_e($mwp_wpvivid_timezone); ?>">(Timezone Setting)</a></th>
                                    <th class="manage-column column-primary"><strong>Universal Time (UTC)</strong></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th><strong>Files full backup cycle</strong></th>
                                    <td>
                                    <span>
                                        <div style="padding: 0 10px 0 0;">
                                            <select id="mwp_wpvivid_incrementa_schedule_recurrence" option="mwp_incremental_backup" name="recurrence" onchange="mwp_change_incremental_backup_recurrence();">
                                                <option value="wpvivid_weekly" selected="selected">Weekly</option>
                                                <option value="wpvivid_fortnightly">Fortnightly</option>
                                                <option value="wpvivid_monthly">Every 30 days</option>
                                            </select>
                                        </div>
                                    </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><strong>Files incremental backup start time</strong></th>
                                    <td>
                                    <span>
                                        <div id="mwp_wpvivid_incrementa_schedule_backup_start_week" style="float: left; padding: 0 10px 0 0;">
                                            <select option="mwp_incremental_backup" name="recurrence_week">
                                                <option value="sun">Sunday</option>
                                                <option value="mon" selected="selected">Monday</option>
                                                <option value="tue">Tuesday</option>
                                                <option value="wed">Wednesday</option>
                                                <option value="thu">Thursday</option>
                                                <option value="fri">Friday</option>
                                                <option value="sat">Saturday</option>
                                            </select>
                                        </div>
                                    </span>
                                        <span>
                                        <div id="mwp_wpvivid_incrementa_schedule_backup_start_day" style="float: left; display: none; padding: 0 10px 0 0;">
                                            <select option="mwp_incremental_backup" name="recurrence_day">
                                                <?php
                                                $html='';
                                                for($i=1;$i<31;$i++)
                                                {
                                                    $html.='<option value="'.$i.'">'.$i.'</option>';
                                                }
                                                echo $html;
                                                ?>
                                            </select>
                                        </div>
                                    </span>
                                        <span>
                                        <div style="float: left; padding: 0 10px 0 0;">
                                            <select option="mwp_incremental_backup" name="files_current_day_hour" onchange="mwp_wpvivid_check_incremental_time('files');" style="margin-bottom: 4px;">
                                                <?php
                                                $html='';
                                                for($hour=0; $hour<24; $hour++){
                                                    $format_hour = sprintf("%02d", $hour);
                                                    $html .= '<option value="'.$format_hour.'">'.$format_hour.'</option>';
                                                }
                                                echo $html;
                                                ?>
                                            </select>
                                            <span>:</span>
                                            <select option="mwp_incremental_backup" name="files_current_day_minute" onchange="mwp_wpvivid_check_incremental_time('files');" style="margin-bottom: 4px;">
                                            <?php
                                            $html='';
                                            for($minute=0; $minute<60; $minute++){
                                                $format_minute = sprintf("%02d", $minute);
                                                $html .= '<option value="'.$format_minute.'">'.$format_minute.'</option>';
                                            }
                                            echo $html;
                                            ?>
                                            </select>
                                        </div>
                                    </span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div>
                                            <div id="mwp_wpvivid_incremental_files_utc_time" style="float: left; margin-right: 10px;">00:00</div>
                                            <small>
                                                <div class="mwp-wpvivid-tooltip" style="float: left; margin-top:3px; line-height: 100%;">?
                                                    <div class="mwp-wpvivid-tooltiptext">The schedule start time in UTC.</div>
                                                </div>
                                            </small>
                                            <div style="clear: both;"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th><strong>Files incremental backup cycle</strong></th>
                                    <td>
                                    <span>
                                        <div style="padding: 0 10px 0 0;">
                                            <select option="mwp_incremental_backup" name="incremental_files_recurrence">
                                                <option value="wpvivid_hourly">Every hour</option>
                                                <option value="wpvivid_2hours">Every 2 hours</option>
                                                <option value="wpvivid_4hours">Every 4 hours</option>
                                                <option value="wpvivid_8hours">Every 8 hours</option>
                                                <option value="wpvivid_12hours">Every 12 hours</option>
                                                <option value="wpvivid_daily" >Daily</option>
                                            </select>
                                        </div>
                                     </span>
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        <label>
                                            <input type="checkbox" option="mwp_incremental_backup" name="incremental_files_start_backup" style="margin-top:2px; margin-left: 0;" />
                                            <span>Run a full backup immediately after the new incremental backup schedule is created.</span>
                                        </label>
                                    </th>
                                </tr>
                                <tr>
                                    <td colspan="3" style="line-height: 1.0em; padding: 0;">
                                        <div style="width: 100%;border:1px solid #e5e5e5; float:left; box-sizing: border-box;">
                                            <div id="mwp_wpvivid_incremental_backup_files_select" style="margin: 1px; background-color: #f7f7f7; box-sizing: border-box; padding: 10px; cursor: pointer;">
                                                <div style="float: left;">
                                                    Choose which folders to run incremental backups:
                                                </div>
                                                <div style="float: right;">
                                                    <details class="primer" onclick="return false;" style="display: inline-block; width: 100%;">
                                                        <summary title="Show detail" style="float: right; color: #a0a5aa;"></summary>
                                                    </details>
                                                </div>
                                                <div style="clear: both;"></div>
                                            </div>
                                            <div id="mwp_wpvivid_incremental_backup_files" style="padding: 10px; display: none;">
                                                <?php
                                                $custom_backup_selector=new Mainwp_WPvivid_Extension_Custom_Backup_Selector('mwp_wpvivid_edit_incremental_backup','mwp_incremental_backup', $this->incremental_backup_data);
                                                $custom_backup_selector->output_backup_files();
                                                ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mwp-wpvivid-block-bottom-space">
                            <table class="wp-list-table widefat plugin">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th class="manage-column column-primary"><strong>Local Time </strong><a href="<?php esc_attr_e($mwp_wpvivid_timezone); ?>">(Timezone Setting)</a></th>
                                    <th class="manage-column column-primary"><strong>Universal Time (UTC)</strong></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <th><strong>Database backup cycle</strong></th>
                                    <td>
                                    <span>
                                        <div style="float: left; padding: 0 10px 0 0;">
                                            <select id="mwp_wpvivid_incrementa_schedule_db_recurrence" option="mwp_incremental_backup" name="incremental_db_recurrence" onchange="mwp_change_incremental_backup_db_recurrence();">
                                                <option value="wpvivid_hourly">Every hour</option>
                                                <option value="wpvivid_2hours">Every 2 hours</option>
                                                <option value="wpvivid_4hours">Every 4 hours</option>
                                                <option value="wpvivid_8hours">Every 8 hours</option>
                                                <option value="wpvivid_12hours">Every 12 hours</option>
                                                <option value="wpvivid_daily">Daily</option>
                                                <option value="wpvivid_weekly" selected="selected">Weekly</option>
                                                <option value="wpvivid_fortnightly">Fortnightly</option>
                                                <option value="wpvivid_monthly">Every 30 days</option>
                                            </select>
                                        </div>
                                    </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th><strong>Database backup start time</strong></th>
                                    <td>
                                    <span>
                                        <div id="mwp_wpvivid_incrementa_schedule_backup_db_start_week" style="float: left; padding: 0 10px 0 0;">
                                            <select option="mwp_incremental_backup" name="incremental_db_recurrence_week">
                                                <option value="sun">Sunday</option>
                                                <option value="mon" selected="selected">Monday</option>
                                                <option value="tue">Tuesday</option>
                                                <option value="wed">Wednesday</option>
                                                <option value="thu">Thursday</option>
                                                <option value="fri">Friday</option>
                                                <option value="sat">Saturday</option>
                                            </select>
                                        </div>
                                    </span>
                                        <span>
                                        <div id="mwp_wpvivid_incrementa_schedule_backup_db_start_day" style="display: none; padding: 0 10px 0 0; float: left;">
                                            <select option="mwp_incremental_backup" name="incremental_db_recurrence_day">
                                                <?php
                                                $html='';
                                                for($i=1;$i<31;$i++)
                                                {
                                                    $html.='<option value="'.$i.'">'.$i.'</option>';
                                                }
                                                echo $html;
                                                ?>
                                            </select>
                                        </div>
                                    </span>
                                        <span>
                                        <div class="wpvivid-schedule-time-select-addon" style="padding: 0 10px 0 0; float: left;">
                                            <select option="mwp_incremental_backup" name="db_current_day_hour" onchange="mwp_wpvivid_check_incremental_time('db');" style="margin-bottom: 4px;">
                                            <?php
                                            $html='';
                                            for($hour=0; $hour<24; $hour++)
                                            {
                                                $format_hour = sprintf("%02d", $hour);
                                                $html .= '<option value="'.$format_hour.'">'.$format_hour.'</option>';
                                            }
                                            echo $html;
                                            ?>
                                            </select>
                                            <span>:</span>
                                            <select option="mwp_incremental_backup" name="db_current_day_minute" onchange="mwp_wpvivid_check_incremental_time('db');" style="margin-bottom: 4px;">
                                            <?php
                                            $html='';
                                            for($minute=0; $minute<60; $minute++)
                                            {
                                                $format_minute = sprintf("%02d", $minute);
                                                $html .= '<option value="'.$format_minute.'">'.$format_minute.'</option>';
                                            }
                                            echo $html;
                                            ?>
                                            </select>
                                        </div>
                                    </span>
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div>
                                            <div id="mwp_wpvivid_incremental_db_utc_time" style="float: left; margin-right: 10px;">00:00</div>
                                            <small>
                                                <div class="mwp-wpvivid-tooltip" style="float: left; margin-top:3px; line-height: 100%;">?
                                                    <div class="mwp-wpvivid-tooltiptext">The schedule start time in UTC.</div>
                                                </div>
                                            </small>
                                            <div style="clear: both;"></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" style="line-height: 1.0em; padding: 0;">
                                        <div style="width: 100%;border:1px solid #e5e5e5; float:left; box-sizing: border-box;">
                                            <div id="mwp_wpvivid_incremental_backup_db_select" style="margin: 1px; background-color: #f7f7f7; box-sizing: border-box; padding: 10px; cursor: pointer;">
                                                <div style="float: left;">
                                                    Choose databases to backup:
                                                </div>
                                                <div style="float: right;">
                                                    <details class="primer" onclick="return false;" style="display: inline-block; width: 100%;">
                                                        <summary title="Show detail" style="float: right; color: #a0a5aa;"></summary>
                                                    </details>
                                                </div>
                                                <div style="clear: both;"></div>
                                            </div>
                                            <div id="mwp_wpvivid_incremental_backup_db" style="padding: 10px; display: none;">
                                                <?php
                                                $custom_backup_selector->output_backup_db();
                                                $custom_backup_selector->load_js();
                                                ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div style="width:100%; border:1px solid #e5e5e5; float:left; box-sizing: border-box;margin-bottom:10px;">
                        <div class="mwp-wpvivid-block-bottom-space" style="margin: 1px 1px 10px 1px; background-color: #f7f7f7; box-sizing: border-box; padding: 10px;">Choose where to send backups:</div>
                        <div class="mwp-wpvivid-block-bottom-space" style="margin-left: 10px; margin-right: 10px;">
                            <?php
                            $custom_backup_selector->output_local_remote_selector();
                            ?>
                        </div>
                        <div id="mwp_wpvivid_incremental_remote_backup_count_setting" style="margin-left: 10px; margin-right: 10px; display: none;">
                            <div>
                                <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-font-right-space" style="float: left;">
                                    <input type="text" id="mwp_wpvivid_incremental_remote_max_backup_count" value="<?php esc_attr_e($incremental_remote_backup_count); ?>" style="width: 50px;" />
                                </div>
                                <div class="mwp-wpvivid-block-bottom-space wpvivid-setting-text-fix" style="float: left; height: 28px; line-height: 28px;"><strong><?php _e('Incremental backups retained for each remote storage', 'wpvivid'); ?></strong></div>
                                <div style="clear: both;"></div>
                            </div>
                        </div>
                        <div class="mwp-wpvivid-block-bottom-space" id="schedule_upload_storage" style="cursor:pointer; margin-left: 10px; margin-right: 10px;" title="Highlighted icon illuminates that you have choosed a remote storage to store backups">
                            <?php
                            $custom_backup_selector->output_remote_pic();
                            ?>
                        </div>
                    </div>
                    <div style="clear: both;"></div>

                    <div>
                        <div id="mwp_wpvivid_incremental_backup_schedule_create_notice"></div>
                        <input id="mwp_wpvivid_incremental_backup_schedule_save" class="button-primary" type="submit" value="<?php echo $btn?>" onclick="mwp_wpvivid_click_create_incremental_schedule();"/>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>

            <script>
                var mwp_wpvivid_start_incremental=0;

                jQuery('input:radio[option=mwp_incremental_backup][name=save_local_remote]').click(function(){
                    var value = jQuery(this).val();
                    if(value === 'local'){
                        jQuery('#mwp_wpvivid_incremental_remote_backup_count_setting').hide();
                    }
                    else if(value === 'remote'){
                        jQuery('#mwp_wpvivid_incremental_remote_backup_count_setting').show();
                    }
                });

                jQuery('#mwp_wpvivid_back_prev_page').on('click', function(){
                    jQuery('#mwp_wpvivid_output_incremental_schedule').show();
                    jQuery('#mwp_wpvivid_create_new_incremental_schedule').hide();
                });

                jQuery('#mwp_wpvivid_incremental_backup_files_select').click(function(){
                    if(jQuery('#mwp_wpvivid_incremental_backup_files').is(":hidden")) {
                        jQuery(this).find('details').prop('open', true);
                        jQuery('#mwp_wpvivid_incremental_backup_files').show();
                        jQuery( document ).trigger( 'mwp_wpvivid_refresh_incremental_custom_backup_tables' );
                    }
                    else{
                        jQuery(this).find('details').prop('open', false);
                        jQuery('#mwp_wpvivid_incremental_backup_files').hide();
                    }
                });

                jQuery('#mwp_wpvivid_incremental_backup_db_select').click(function () {
                    if(jQuery('#mwp_wpvivid_incremental_backup_db').is(":hidden")) {
                        jQuery(this).find('details').prop('open', true);
                        jQuery('#mwp_wpvivid_incremental_backup_db').show();
                        jQuery( document ).trigger( 'mwp_wpvivid_refresh_incremental_custom_backup_tables' );
                    }
                    else{
                        jQuery(this).find('details').prop('open', false);
                        jQuery('#mwp_wpvivid_incremental_backup_db').hide();
                    }
                });

                function mwp_change_incremental_backup_recurrence() {
                    jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_day').hide();
                    jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_week').hide();
                    var select_value = jQuery('#mwp_wpvivid_incrementa_schedule_recurrence').val();
                    if(select_value === 'wpvivid_weekly' || select_value === 'wpvivid_fortnightly')
                    {
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_week').show();
                    }
                    else if(select_value === 'wpvivid_monthly')
                    {
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_day').show();
                    }
                }

                function mwp_change_incremental_backup_db_recurrence() {
                    jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_week').hide();
                    jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_day').hide();
                    var select_value = jQuery('#mwp_wpvivid_incrementa_schedule_db_recurrence').val();
                    if(select_value === 'wpvivid_weekly' || select_value === 'wpvivid_fortnightly') {
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_week').show();
                    }
                    else if(select_value === 'wpvivid_monthly'){
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_day').show();
                    }
                }

                function mwp_get_wpvivid_sync_time(option_name,current_day_hour,current_day_minute) {
                    var hour='00';
                    var minute='00';
                    jQuery('select[option='+option_name+'][name='+current_day_hour+']').each(function()
                    {
                        hour=jQuery(this).val();
                    });
                    jQuery('select[option='+option_name+'][name='+current_day_minute+']').each(function(){
                        minute=jQuery(this).val();
                    });

                    hour=Number(hour)-Number(time_offset);

                    var Hours=Math.floor(hour);
                    var Minutes=Math.floor(60*(hour-Hours));

                    Minutes=Number(minute)+Minutes;
                    if(Minutes>=60)
                    {
                        Hours=Hours+1;
                        Minutes=Minutes-60;
                    }

                    if(Hours>=24)
                    {
                        Hours=Hours-24;
                    }
                    else if(Hours<0)
                    {
                        Hours=24-Math.abs(Hours);
                    }
                    if(Hours<10)
                    {
                        Hours='0'+Hours;
                    }

                    if(Minutes<10)
                    {
                        Minutes='0'+Minutes;
                    }

                    return Hours+":"+Minutes;
                }

                function mwp_wpvivid_check_incremental_time(type){
                    var db_current_day=mwp_get_wpvivid_sync_time('mwp_incremental_backup','db_current_day_hour','db_current_day_minute');
                    var files_current_day=mwp_get_wpvivid_sync_time('mwp_incremental_backup','files_current_day_hour','files_current_day_minute');
                    if(db_current_day === files_current_day){
                        alert('You have set the same start time for the files incremental backup schedule and the database backup schedule. When there is a conflict of starting times for schedule tasks, only one task will be executed properly. Please make sure that the times are different.')
                    }
                    jQuery('#mwp_wpvivid_incremental_db_utc_time').html(db_current_day);
                    jQuery('#mwp_wpvivid_incremental_files_utc_time').html(files_current_day);
                }

                function mwp_wpvivid_enable_incremental_backup(enable){
                    var ajax_data = {
                        'action': 'mwp_wpvivid_enable_incremental_backup',
                        'site_id': '<?php echo esc_html($this->site_id); ?>',
                        'enable': enable
                    };
                    mwp_wpvivid_post_request(ajax_data, function (data)
                    {
                        try
                        {
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success')
                            {
                                if(enable)
                                {
                                    jQuery('#mwp_wpvivid_schedule_list_addon').html(jsonarray.html);

                                    jQuery('#mwp_wpvivid_create_new_incremental_schedule').show();
                                    jQuery('#mwp_wpvivid_output_incremental_schedule').hide();

                                    jQuery('#mwp_wpvivid_edit_incremental_backup').show();
                                    jQuery('#mwp_wpvivid_enable_incremental_backup').hide();
                                    jQuery('#mwp_wpvivid_disable_incremental_backup').show();
                                    jQuery('#mwp_wpvivid_back_prev_page').show();
                                }
                                else
                                {
                                    mwp_wpvivid_start_incremental=0;
                                    jQuery('#mwp_wpvivid_create_new_incremental_schedule').show();
                                    jQuery('#mwp_wpvivid_output_incremental_schedule').hide();

                                    jQuery('#mwp_wpvivid_edit_incremental_backup').hide();
                                    jQuery('#mwp_wpvivid_enable_incremental_backup').show();
                                    jQuery('#mwp_wpvivid_disable_incremental_backup').hide();
                                    jQuery('#mwp_wpvivid_back_prev_page').hide();
                                }
                            }
                        }
                        catch (err)
                        {
                            alert(err);
                        }
                    }, function (XMLHttpRequest, textStatus, errorThrown)
                    {
                        var error_message = mwp_wpvivid_output_ajaxerror('changing schedule', textStatus, errorThrown);
                        alert(error_message);
                    });
                }

                function mwp_wpvivid_start_incremental_backup(){
                    jQuery('#mwp_wpvivid_create_new_incremental_schedule').show();
                    jQuery('#mwp_wpvivid_output_incremental_schedule').hide();

                    jQuery('#mwp_wpvivid_edit_incremental_backup').show();
                    jQuery('#mwp_wpvivid_enable_incremental_backup').hide();
                    jQuery('#mwp_wpvivid_disable_incremental_backup').hide();
                    jQuery('#mwp_wpvivid_back_prev_page').hide();
                    jQuery('#mwp_wpvivid_incremental_backup_schedule_save').val('Create the new incremental schedule');
                    mwp_wpvivid_start_incremental=1;
                }

                function mwp_wpvivid_create_incremental_json(parent_id, incremental_type){
                    var json = {};
                    jQuery('#'+parent_id).find('.mwp-wpvivid-custom-check').each(function(){
                        if(incremental_type === 'files'){
                            if(jQuery(this).hasClass('mwp-wpvivid-custom-core-check')){
                                json['core_list'] = Array();
                                if(jQuery(this).prop('checked')){
                                    json['core_check'] = '1';
                                }
                                else{
                                    json['core_check'] = '0';
                                }
                            }
                            else if(jQuery(this).hasClass('mwp-wpvivid-custom-themes-plugins-check')){
                                json['themes_list'] = Array();
                                json['plugins_list'] = Array();
                                var has_themes = false;
                                var has_plugins = false;
                                if(jQuery(this).prop('checked')){
                                    json['themes_check'] = '0';
                                    json['plugins_check'] = '0';
                                    jQuery('#'+parent_id).find('input:checkbox[option=mwp_themes][name=Themes]').each(function(){
                                        has_themes = true;
                                        if(jQuery(this).prop('checked')){
                                            json['themes_check'] = '1';
                                        }
                                        else{
                                            json['themes_list'].push(jQuery(this).val());
                                        }
                                    });
                                    if(!has_themes){
                                        json['themes_check'] = '1';
                                    }
                                    jQuery('#'+parent_id).find('input:checkbox[option=mwp_plugins][name=Plugins]').each(function(){
                                        has_plugins = true;
                                        if(jQuery(this).prop('checked')) {
                                            json['plugins_check'] = '1';
                                        }
                                        else{
                                            json['plugins_list'].push(jQuery(this).val());
                                        }
                                    });
                                    if(!has_plugins){
                                        json['plugins_check'] = '1';
                                    }
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
                        }
                        else if(incremental_type === 'database'){
                            if(jQuery(this).hasClass('mwp-wpvivid-custom-database-check')){
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
                            else if(jQuery(this).hasClass('mwp-wpvivid-custom-additional-database-check')){
                                if(jQuery(this).prop('checked')){
                                    json['additional_database_check'] = '1';
                                }
                                else{
                                    json['additional_database_check'] = '0';
                                }
                            }
                        }
                    });
                    return json;
                }

                function mwp_wpvivid_click_create_incremental_schedule(){
                    if(mwp_wpvivid_start_incremental === 0){
                        var descript = 'Update will reset schedule, continue?';
                        var ret = confirm(descript);
                        if (ret !== true) {
                            return;
                        }
                    }
                    var file_json = mwp_wpvivid_create_incremental_json('mwp_wpvivid_edit_incremental_backup', 'files');
                    var db_json = mwp_wpvivid_create_incremental_json('mwp_wpvivid_edit_incremental_backup', 'database');

                    var schedule_data = mwp_wpvivid_ajax_data_transfer('mwp_incremental_backup');
                    schedule_data = JSON.parse(schedule_data);
                    var db_current_day=mwp_get_wpvivid_sync_time('mwp_incremental_backup','db_current_day_hour','db_current_day_minute');
                    var files_current_day=mwp_get_wpvivid_sync_time('mwp_incremental_backup','files_current_day_hour','files_current_day_minute');
                    var current_day = {
                        'db_current_day': db_current_day,
                        'files_current_day': files_current_day,
                    };
                    var custom = {};
                    custom['custom'] = {
                        'files': file_json,
                        'db': db_json,
                    };
                    //custom = JSON.stringify(custom);
                    jQuery.extend(schedule_data, current_day);
                    jQuery.extend(schedule_data, custom);
                    schedule_data = JSON.stringify(schedule_data);

                    var incremental_remote_backup_retain = jQuery('#mwp_wpvivid_incremental_remote_max_backup_count').val();

                    var ajax_data = {
                        'action': 'mwp_wpvivid_set_incremental_backup_schedule',
                        'site_id': '<?php echo esc_html($this->site_id); ?>',
                        'schedule': schedule_data,
                        'start':mwp_wpvivid_start_incremental,
                        'incremental_remote_retain': incremental_remote_backup_retain
                        //'custom':custom
                    };
                    jQuery('#mwp_wpvivid_incremental_backup_schedule_create_notice').html('');
                    mwp_wpvivid_post_request(ajax_data, function (data)
                    {
                        try
                        {
                            jQuery('#mwp_wpvivid_incremental_backup_schedule_save').css({'pointer-events': 'auto', 'opacity': '1'});
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success')
                            {
                                if(mwp_wpvivid_start_incremental)
                                {
                                    jQuery('#mwp_wpvivid_schedule_list_addon').html(jsonarray.html);
                                    mwp_wpvivid_start_incremental=0;
                                }

                                jQuery('#mwp_wpvivid_output_incremental_schedule').show();
                                jQuery('#mwp_wpvivid_create_new_incremental_schedule').hide();
                                jQuery('#mwp_wpvivid_incremental_backup_schedule_notice').html(jsonarray.notice);
                                var all_schedule=jsonarray.data.all_schedule;
                                var db_schedule=jsonarray.data.db_schedule;
                                var db_next_start=jsonarray.data.db_next_start;
                                var files_schedule=jsonarray.data.files_schedule;
                                var files_next_start=jsonarray.data.files_next_start;
                                var next_start_of_all_files=jsonarray.data.next_start_of_all_files;
                                init_incremental_page(all_schedule,next_start_of_all_files,files_schedule,files_next_start,db_schedule,db_next_start);
                            }
                            else {
                                jQuery('#wpvivid_incremental_backup_schedule_create_notice').html(jsonarray.notice);
                            }
                        }
                        catch (err)
                        {
                            alert(err);
                        }
                    }, function (XMLHttpRequest, textStatus, errorThrown)
                    {
                        var error_message = mwp_wpvivid_output_ajaxerror('changing schedule', textStatus, errorThrown);
                        alert(error_message);
                    });
                }

                function mwp_wpvivid_update_incremental_exclude_extension_rule(obj, type, value){
                    var ajax_data = {
                        'action': 'mwp_wpvivid_update_incremental_backup_exclude_extension_addon',
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

                function mwp_wpvivid_incremental_additional_database_connect(parent_id){
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
                                'action': 'mwp_wpvivid_incremental_connect_additional_database_addon',
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

                function mwp_wpvivid_incremental_additional_database_add(parent_id){
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
                                'action': 'mwp_wpvivid_incremental_add_additional_database_addon',
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

                function mwp_wpvivid_incremental_additional_database_remove(parent_id, database_name){
                    var ajax_data = {
                        'action': 'mwp_wpvivid_incremental_remove_additional_database_addon',
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

                jQuery(document).ready(function (){
                    <?php
                    if($enable_incremental_schedules)
                    {
                    ?>
                    jQuery('#mwp_wpvivid_edit_incremental_backup').show();
                    jQuery('#mwp_wpvivid_enable_incremental_backup').hide();
                    jQuery('#mwp_wpvivid_disable_incremental_backup').show();
                    jQuery('#mwp_wpvivid_back_prev_page').show();
                    <?php
                    }
                    else
                    {
                    ?>
                    jQuery('#mwp_wpvivid_edit_incremental_backup').hide();
                    jQuery('#mwp_wpvivid_enable_incremental_backup').show();
                    jQuery('#mwp_wpvivid_disable_incremental_backup').hide();
                    jQuery('#mwp_wpvivid_back_prev_page').hide();
                    <?php
                    }
                    ?>

                    var recurrence='<?php echo $recurrence ?>';
                    var incremental_files_recurrence='<?php echo $incremental_files_recurrence ?>';
                    var incremental_db_recurrence='<?php echo $incremental_db_recurrence ?>';
                    var incremental_files_recurrence_week='<?php echo $incremental_files_recurrence_week; ?>';
                    var incremental_files_recurrence_day='<?php echo $incremental_files_recurrence_day; ?>';
                    var incremental_db_recurrence_week='<?php echo $incremental_db_recurrence_week; ?>';
                    var incremental_db_recurrence_day='<?php echo $incremental_db_recurrence_day; ?>';
                    var db_current_day_hour='<?php echo $db_current_day_hour ?>';
                    var db_current_day_minute='<?php echo $db_current_day_minute ?>';
                    var files_current_day_hour='<?php echo $files_current_day_hour ?>';
                    var files_current_day_minute='<?php echo $files_current_day_minute ?>';
                    var backup_to='<?php echo $backup_to ?>';

                    jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_week').hide();
                    jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_day').hide();
                    jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_week').hide();
                    jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_day').hide();

                    jQuery('[option=mwp_incremental_backup][name=recurrence]').val(recurrence);
                    jQuery('[option=mwp_incremental_backup][name=incremental_files_recurrence]').val(incremental_files_recurrence);
                    jQuery('[option=mwp_incremental_backup][name=incremental_db_recurrence]').val(incremental_db_recurrence);

                    if(recurrence === 'wpvivid_weekly' || recurrence === 'wpvivid_fortnightly')
                    {
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_week').show();
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_day').hide();
                    }
                    else if(recurrence === 'wpvivid_monthly')
                    {
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_week').hide();
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_start_day').show();
                    }
                    if(incremental_db_recurrence === 'wpvivid_weekly' || incremental_db_recurrence === 'wpvivid_fortnightly')
                    {
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_week').show();
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_day').hide();
                    }
                    else if(incremental_db_recurrence === 'wpvivid_monthly')
                    {
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_week').hide();
                        jQuery('#mwp_wpvivid_incrementa_schedule_backup_db_start_day').show();
                    }

                    jQuery('[option=mwp_incremental_backup][name=recurrence_week]').val(incremental_files_recurrence_week);
                    jQuery('[option=mwp_incremental_backup][name=recurrence_day]').val(incremental_files_recurrence_day);
                    jQuery('[option=mwp_incremental_backup][name=incremental_db_recurrence_week]').val(incremental_db_recurrence_week);
                    jQuery('[option=mwp_incremental_backup][name=incremental_db_recurrence_day]').val(incremental_db_recurrence_day);

                    jQuery('[option=mwp_incremental_backup][name=db_current_day_hour]').val(db_current_day_hour);
                    jQuery('[option=mwp_incremental_backup][name=db_current_day_minute]').val(db_current_day_minute);

                    jQuery('[option=mwp_incremental_backup][name=files_current_day_hour]').val(files_current_day_hour);
                    jQuery('[option=mwp_incremental_backup][name=files_current_day_minute]').val(files_current_day_minute);

                    var db_current_day=mwp_get_wpvivid_sync_time('mwp_incremental_backup','db_current_day_hour','db_current_day_minute');
                    var files_current_day=mwp_get_wpvivid_sync_time('mwp_incremental_backup','files_current_day_hour','files_current_day_minute');
                    jQuery('#mwp_wpvivid_incremental_files_utc_time').html(files_current_day);
                    jQuery('#mwp_wpvivid_incremental_db_utc_time').html(db_current_day);

                    jQuery('[option=mwp_incremental_backup][name=save_local_remote]').each(function()
                    {
                        if(jQuery(this).val()===backup_to)
                        {
                            jQuery(this).prop('checked',true);
                        }
                        else
                        {
                            jQuery(this).prop('checked',false);
                        }
                    });
                    if(backup_to === 'remote'){
                        jQuery('#mwp_wpvivid_incremental_remote_backup_count_setting').show();
                    }
                });
            </script>
            <?php
        }
    }

    public function output_incremental_schedule($global)
    {
        $incremental_output_msg = isset($this->incremental_backup_data['incremental_output_msg']) ? $this->incremental_backup_data['incremental_output_msg'] : array();
        $all_schedule = isset($incremental_output_msg['all_schedule']) ? $incremental_output_msg['all_schedule'] : 'N/A';
        $next_start_of_all_files = isset($incremental_output_msg['next_start_of_all_files']) ? $incremental_output_msg['next_start_of_all_files'] : 'N/A';
        $db_schedule = isset($incremental_output_msg['db_schedule']) ? $incremental_output_msg['db_schedule'] : 'N/A';
        $db_next_start = isset($incremental_output_msg['db_next_start']) ? $incremental_output_msg['db_next_start'] : 'N/A';
        $files_schedule = isset($incremental_output_msg['files_schedule']) ? $incremental_output_msg['files_schedule'] : 'N/A';
        $files_next_start = isset($incremental_output_msg['files_next_start']) ? $incremental_output_msg['files_next_start'] : 'N/A';
        $last_message = isset($incremental_output_msg['last_message']) ? $incremental_output_msg['last_message'] : 'N/A';
        ?>
        <div id="mwp_wpvivid_output_incremental_schedule" class="postbox" style="margin: 10px 0 0 0;">
            <div class="inside">
                <span><strong>Last Backup:</strong></span>
                <span style="color:#dd9933;"><?php echo $last_message; ?></span>
            </div>
            <div class="inside">
                <span id="mwp_wpvivid_incremental_next_start">Next files full backup start at:</span>
            </div>
            <div class="inside">
                <span id="mwp_wpvivid_incremental_schedule">Schedule:</span>
            </div>
            <div class="inside">
                <span id="mwp_wpvivid_incremental_files_next_start">Next files incremental backup will start at:</span>
            </div>
            <div class="inside">
                <span id="mwp_wpvivid_incremental_files_schedule">Schedule:</span>
            </div>
            <div class="inside">
                <span id="mwp_wpvivid_incremental_db_next_start">Next database backup will start at:</span>
            </div>
            <div class="inside">
                <span id="mwp_wpvivid_incremental_db_schedule">Schedule:</span>
            </div>
            <div id="mwp_wpvivid_incremental_backup_schedule_notice" style="padding-left: 12px; padding-right: 12px;"></div>
            <div class="inside">
                <input class="button-primary" id="mwp_wpvivid_change_incremental_schedule" type="button" value="Change incremental schedule setting">
            </div>
        </div>
        <script>
            jQuery('#mwp_wpvivid_change_incremental_schedule').on("click",function()
            {
                jQuery('#mwp_wpvivid_output_incremental_schedule').hide();
                jQuery('#mwp_wpvivid_create_new_incremental_schedule').show();
                jQuery('#mwp_wpvivid_incremental_backup_schedule_save').val('Update schedule');
                jQuery('#mwp_wpvivid_disable_incremental_backup').show();
                jQuery('#mwp_wpvivid_back_prev_page').show();
            });

            jQuery(document).ready(function ()
            {
                var all_schedule='<?php echo $all_schedule; ?>';
                var next_start_of_all_files='<?php echo $next_start_of_all_files; ?>';
                var db_schedule='<?php echo $db_schedule; ?>';
                var db_next_start='<?php echo $db_next_start; ?>';
                var files_schedule='<?php echo $files_schedule; ?>';
                var files_next_start='<?php echo $files_next_start; ?>';
                init_incremental_page(all_schedule,next_start_of_all_files,files_schedule,files_next_start,db_schedule,db_next_start);

            });

            function init_incremental_page(all_schedule,next_start_of_all_files,files_schedule,files_next_start,db_schedule,db_next_start)
            {
                jQuery('#mwp_wpvivid_incremental_next_start').html('Next files full backup start at: '+next_start_of_all_files);
                jQuery('#mwp_wpvivid_incremental_schedule').html('Schedule:'+all_schedule);
                jQuery('#mwp_wpvivid_incremental_files_schedule').html('Schedule:'+files_schedule);
                jQuery('#mwp_wpvivid_incremental_files_next_start').html('Next files incremental backup will start at:'+files_next_start);
                jQuery('#mwp_wpvivid_incremental_db_schedule').html('Schedule:'+db_schedule);
                jQuery('#mwp_wpvivid_incremental_db_next_start').html('Next database backup will start at:'+db_next_start);
            }
        </script>
        <?php
    }
}