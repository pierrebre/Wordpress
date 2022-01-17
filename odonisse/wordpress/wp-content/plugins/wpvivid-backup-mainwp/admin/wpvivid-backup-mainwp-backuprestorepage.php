<?php

if ( ! class_exists( 'WP_List_Table' ) )
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Mainwp_WPvivid_Files_List extends WP_List_Table
{
    public $page_num;
    public $file_list;
    public $backup_id;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'files',
                'screen' => 'files'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat striped' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
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
        $columns['wpvivid_file'] = __( 'File', 'wpvivid' );
        return $columns;
    }

    public function _column_wpvivid_file( $file )
    {
        $html='<td class="tablelistcolumn">
                    <div style="padding:0 0 10px 0;">
                        <span>'. $file['key'].'</span>
                    </div>
                    <div class="mwp-wpvivid-download-status" style="padding:0;">';
        if($file['status']=='completed')
        {
            $html.='<span>'.__('File Size: ', 'wpvivid').'</span><span class="mwp-wpvivid-block-right-space mwp-wpvivid-download-file-size">'.$file['size'].'</span><span class="mwp-wpvivid-block-right-space">|</span><span class="mwp-wpvivid-block-right-space mwp-wpvivid-ready-download"><a style="cursor: pointer;">Download</a></span>';
        }
        else if($file['status']=='file_not_found')
        {
            $html.='<span>' . __('File not found', 'wpvivid') . '</span>';
        }
        else if($file['status']=='need_download')
        {
            $html.='<span>'.__('File Size: ', 'wpvivid').'</span><span class="mwp-wpvivid-block-right-space mwp-wpvivid-download-file-size">'.$file['size'].'</span><span class="mwp-wpvivid-block-right-space">|</span><span class="mwp-wpvivid-block-right-space"><a class="mwp-wpvivid-prepare-download" style="cursor: pointer;">Prepare to Download</a></span>';
        }
        else if($file['status']=='running')
        {
            $html.='<div class="mwp-wpvivid-block-bottom-space">
                        <span class="mwp-wpvivid-block-right-space">Retriving (remote storage to web server)</span><span class="mwp-wpvivid-block-right-space">|</span><span>File Size: </span><span class="mwp-wpvivid-block-right-space mwp-wpvivid-download-file-size">'.$file['size'].'</span><span class="mwp-wpvivid-block-right-space">|</span><span>Downloaded Size: </span><span>'.$file['downloaded_size'].'</span>
                    </div>
                    <div style="width:100%;height:10px; background-color:#dcdcdc;">
                        <div style="background-color:#0085ba; float:left;width:'.$file['progress_text'].'%;height:10px;"></div>
                    </div>';
        }
        else if($file['status']=='timeout')
        {
            $html.='<div class="mwp-wpvivid-block-bottom-space">
                        <span>Download timeout, please retry.</span>
                    </div>
                    <div>
                        <span>'.__('File Size: ', 'wpvivid').'</span><span class="mwp-wpvivid-block-right-space mwp-wpvivid-download-file-size">'.$file['size'].'</span><span class="mwp-wpvivid-block-right-space">|</span><span class="mwp-wpvivid-block-right-space"><a class="mwp-wpvivid-prepare-download" style="cursor: pointer;">Prepare to Download</a></span>
                    </div>';
        }
        else if($file['status']=='error')
        {
            $html.='<div class="mwp-wpvivid-block-bottom-space">
                        <span>'.$file['error'].'</span>
                    </div>
                    <div>
                        <span>'.__('File Size: ', 'wpvivid').'</span><span class="mwp-wpvivid-block-right-space mwp-wpvivid-download-file-size">'.$file['size'].'</span><span class="mwp-wpvivid-block-right-space">|</span><span class="mwp-wpvivid-block-right-space"><a class="mwp-wpvivid-prepare-download" style="cursor: pointer;">Prepare to Download</a></span>
                    </div>';
        }

        $html.='</div></td>';
        echo $html;
        //size
    }

    public function set_files_list($file_list,$backup_id,$page_num=1)
    {
        $this->file_list=$file_list;
        $this->backup_id=$backup_id;
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

        $total_items =sizeof($this->file_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->file_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->file_list);
    }

    private function _display_rows($file_list)
    {
        $page=$this->get_pagenum();

        $page_file_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_file_list = array_splice( $file_list, 0, 10);
            $count++;
        }
        foreach ( $page_file_list as $key=>$file)
        {
            $file['key']=$key;
            $this->single_row($file);
        }
    }

    public function single_row($file)
    {
        ?>
        <tr slug="<?php echo $file['key']?>">
            <?php $this->single_row_columns( $file ); ?>
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
                "%s<input class='current-page' id='current-page-selector-filelist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-filelist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
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
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
    }

    public function display()
    {
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"
                <?php
                if ( $singular ) {
                    echo " data-wp-lists='list:$singular'";
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }
}

class Mainwp_WPvivid_Backup_List extends WP_List_Table
{
    public $page_num;
    public $backup_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'backup',
                'screen' => 'backup'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat striped mwp-wpvivid-backup-list' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
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
        $columns['cb'] = __( 'cb', 'wpvivid' );
        $columns['wpvivid_backup'] = __( 'Backup', 'wpvivid' );
        $columns['wpvivid_storage'] = __( 'Storage', 'wpvivid' );
        $columns['wpvivid_comment'] =__( 'Comment', 'wpvivid'  );
        $columns['wpvivid_download'] = __( 'Download', 'wpvivid'  );
        if(current_user_can('wpvivid-can-restore')||current_user_can('administrator'))
            $columns['wpvivid_restore'] = __( 'Restore', 'wpvivid'  );

        $columns['wpvivid_delete'] = __( 'Delete', 'wpvivid'  );
        return $columns;
    }

    public function column_cb( $backup )
    {
        $html='<input type="checkbox"/>';
        echo $html;
    }

    public function _column_wpvivid_backup( $backup )
    {
        $upload_title = '';

        if ($backup['type'] == 'Migration' || $backup['type'] == 'Upload')
        {
            if ($backup['type'] == 'Migration')
            {
                $upload_title = 'Received Backup: ';
            } else if ($backup['type'] == 'Upload')
            {
                $upload_title = 'Uploaded Backup: ';
            }
        }

        if (empty($backup['lock']))
        {
            $backup_lock = '/admin/images/unlocked.png';
            $lock_status = '';
        }
        else {
            if ($backup['lock'] == 0)
            {
                $backup_lock = '/admin/images/unlocked.png';
                $lock_status = '';
            } else {
                $backup_lock = '/admin/images/locked.png';
                $lock_status = 'lock';
            }
        }

        $offset=get_option('gmt_offset');
        $localtime = $backup['create_time'] + $offset * 60 * 60;

        $html='<td class="tablelistcolumn">
                    <div style="float:left;padding:0 10px 10px 0;">
                        <div style="float: left; margin-right: 2px;"><strong>' . $upload_title . '</strong></div>
                        <div class="backuptime" style="float: left;">' . __(date('M d, Y H:i', $localtime)) . '</div>
                        <div style="clear: both;"></div>
                        <div class="common-table">
                            <span class="mwp-wpvivid-lock '.$lock_status.'"  title="To lock the backup, the backup can only be deleted manually">
                            <img src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . $backup_lock) . '" style="vertical-align:middle; cursor:pointer;"/>
                            </span>
                            <span style="margin:0 5px 0 0; opacity: 0.5;">|</span> <span>' . __('Type: ', 'wpvivid') . '</span><span>' . __($backup['type'], 'wpvivid') . '</span>
                            <span style="margin:0 0 0 5px; opacity: 0.5;">|</span> <span title="Backup log"><a href="#" name="'.basename($backup['log']).'" class="mwp-wpvivid-log"><img src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . '/admin/images/Log.png') . '" style="vertical-align:middle;cursor:pointer;"/><span style="margin:0;">' . __('Log', 'wpvivid') . '</span></a></span>
                        </div>
                    </div>
                </td>';
        echo $html;
    }

    public function _column_wpvivid_storage( $backup )
    {
        $remote=array();
        $remote=apply_filters('mwp_wpvivid_remote_pic', $remote);

        $save_local_pic_y = '/admin/images/storage-local.png';
        $save_local_pic_n = '/admin/images/storage-local(gray).png';
        $local_title = 'Localhost';
        if ($backup['save_local'] == 1 || $backup['type'] == 'Migration') {
            $remote_pic_html = '<img  src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . $save_local_pic_y) . '" style="vertical-align:middle; " title="' . $local_title . '"/>';
        } else {
            $remote_pic_html = '<img  src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . $save_local_pic_n) . '" style="vertical-align:middle; " title="' . $local_title . '"/>';
        }

        if (is_array($remote))
        {
            foreach ($remote as $key1 => $value1)
            {
                $title = $value1['title'];
                foreach ($backup['remote'] as $storage_type)
                {
                    if ($key1 === $storage_type['type'])
                    {
                        $pic = $value1['selected_pic'];
                        $remote_pic_html = '<img src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . $pic) . '" style="vertical-align:middle; " title="' . $title . '"/>';
                    }
                }
            }
        }

        $html='<td class="tablelistcolumn">
                    <div style="float:left;padding:10px 10px 10px 0;">' . $remote_pic_html . '</div>
                </td>';
        echo $html;
    }

    public function _column_wpvivid_comment( $backup )
    {
        if(isset($backup['backup_prefix']) && !empty($backup['backup_prefix']))
        {
            $backup_prefix = $backup['backup_prefix'];
        }
        else{
            $backup_prefix = 'N/A';
        }
        $html='<td class="tablelistcolumn wpvivid-list-td-center">
                    <div style="padding:14px 10px 10px 0;">'.$backup_prefix.'</div>
                </td>';
        echo $html;
    }

    public function _column_wpvivid_download( $backup )
    {
        $html='<td class="tablelistcolumn" style="min-width:100px;">
                    <div class="mwp-wpvivid-download" style="float:left;padding:10px 10px 10px 0;">
                        <div style="cursor:pointer;" title="Prepare to download the backup">
                            <img src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . '/admin/images/download.png') . '" style="vertical-align:middle;" />
                            <span>' . __('Download', 'wpvivid') . '</span>
                        </div>
                    </div>
                </td>';
        echo $html;
    }

    public function _column_wpvivid_restore( $backup )
    {
        $html='<td class="tablelistcolumn" style="min-width:100px;">
                    <div>
                      <div class="mwp-wpvivid-restore" style="cursor:pointer;float:left;padding:10px 0 10px 0;">
                            <img src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . '/admin/images/Restore.png') . '" style="vertical-align:middle;" /><span>' . __('Restore', 'wpvivid') . '</span>
                       </div>
                    </div>
                </td>';
        echo $html;
    }

    public function _column_wpvivid_delete( $backup )
    {
        $html='<td class="tablelistcolumn">
                    <div class="mwp-backuplist-delete-backup" style="padding:10px 0 10px 0;">
                        <img src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . '/admin/images/Delete.png') . '" style="vertical-align:middle; cursor:pointer;" title="Delete the backup"/>
                    </div>
                </td>';
        echo $html;
    }

    public function set_backup_list($backup_list,$page_num=1)
    {
        $this->backup_list=$backup_list;
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

        $total_items =sizeof($this->backup_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->backup_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->backup_list);
    }

    private function _display_rows($backup_list)
    {
        $page=$this->get_pagenum();

        $page_backup_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_backup_list = array_splice( $backup_list, 0, 10);
            $count++;
        }
        foreach ( $page_backup_list as $key=>$backup)
        {
            $backup['key']=$key;
            $this->single_row($backup);
        }
    }

    public function single_row($backup)
    {
        $row_style = 'display: table-row;';
        $class='';
        if ($backup['type'] == 'Migration' || $backup['type'] == 'Upload')
        {
            $class .= 'wpvivid-upload-tr';
        }
        ?>
        <tr style="<?php echo $row_style?>" class='mwp-wpvivid-backup-row <?php echo $class?>' id="<?php echo $backup['key'];?>">
            <?php $this->single_row_columns( $backup ); ?>
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
                "%s<input class='current-page' id='current-page-selector-backuplist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-backuplist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
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
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
    }

    public function display()
    {
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"
                <?php
                if ( $singular ) {
                    echo " data-wp-lists='list:$singular'";
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

            <tfoot>
            <tr>
                <td class="manage-column column-cb check-column" style="padding-left: 6px;">
                    <label class="screen-reader-text" for="cb-select-all-2">Select All</label>
                    <input type="checkbox" id="cb-select-all-2" />
                </td>
                <th class="row-title" colspan="6"><a class="mwp-wpvivid-delete-array" style="cursor: pointer;"><?php _e('Delete the selected backups', 'wpvivid'); ?></a></th>
            </tr>
            </tfoot>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }
}

class Mainwp_WPvivid_Incremental_List extends WP_List_Table
{
    public $page_num;
    public $incremental_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'incremental',
                'screen' => 'incremental'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat striped' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
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
        $columns['wpvivid_incremental_backup_date'] = __( 'Date', 'wpvivid' );
        $columns['wpvivid_incremental_backup_action'] = __( 'Action', 'wpvivid' );
        return $columns;
    }

    public function _column_wpvivid_incremental_backup_date($incremental)
    {
        $time = date('F d, Y', $incremental['path']);

        $html='<td class="tablelistcolumn" style="width: 95%;">
                    <div>
                        '.$time.'
                    </div>
               </td>';
        echo $html;
    }

    public function _column_wpvivid_incremental_backup_action($incremental)
    {
        $html='<td class="tablelistcolumn"><div class="mwp-wpvivid-incremental-child" style="padding:0; width: 5%;">';
        $html.='<input type="button" value="scan" />';
        $html.='</div></td>';
        echo $html;
    }

    public function set_incremental_list($incremental_list,$page_num=1)
    {
        $this->incremental_list=$incremental_list;
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

        if(!empty($this->incremental_list)){
            $total_items = sizeof($this->incremental_list);
        }
        else{
            $total_items = 0;
        }

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 5,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->incremental_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->incremental_list);
    }

    private function _display_rows($incremental_list)
    {
        $page=$this->get_pagenum();

        $page_incremental_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_incremental_list = array_splice( $incremental_list, 0, 5);
            $count++;
        }
        foreach ( $page_incremental_list as $key=>$incremental)
        {
            $this->single_row($incremental);
        }
    }

    public function single_row($incremental)
    {
        ?>
        <tr id="<?php echo $incremental['og_path']; ?>">
            <?php $this->single_row_columns( $incremental ); ?>
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
                "%s<input class='current-page' id='current-page-selector-filelist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-filelist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
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
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
    }

    public function display()
    {
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"
                <?php
                if ( $singular ) {
                    echo " data-wp-lists='list:$singular'";
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }
}

class Mainwp_WPvivid_Extension_BackupRestorePage
{
    private $setting;
    private $setting_addon;
    private $remote_addon;
    private $site_id;
    public $main_tab;

    public function __construct($setting, $setting_addon=array(), $remote_addon = array())
    {
        $this->setting=$setting;
        $this->setting_addon=$setting_addon;
        $this->remote_addon=$remote_addon;
        //$this->remote_addon=array();
    }

    public function set_site_id($site_id)
    {
        $this->site_id=$site_id;
    }

    public function render($check_pro, $global=false)
    {
        ?>
        <div style="margin: 10px;">
            <div>
                <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                    <img src="<?php echo esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL.'/admin/images/backups-restore.png'); ?>" style="width:50px;height:50px;">
                </div>
                <div class="mwp-wpvivid-block-bottom-space">
                    <div>Display all your backups:</div>
                    <div>Localhost: The local storage directory where all your manual backups, schedule backups and rollback backups are stored in localhost.</div>
                    <div>Remote Storage: The remote storage accounts you have added.</div>
                </div>
                <div style="clear: both;"></div>
            </div>
            <?php
            if(!class_exists('Mainwp_WPvivid_Tab_Page_Container'))
                include_once MAINWP_WPVIVID_EXTENSION_PLUGIN_DIR . '/includes/wpvivid-backup-mainwp-tab-page-container.php';
            $this->main_tab=new Mainwp_WPvivid_Tab_Page_Container();

            $args['is_parent_tab']=0;
            $args['transparency']=1;
            $this->main_tab->add_tab('Localhost','localhost_list',array($this, 'output_localhost'), $args);
            $this->main_tab->add_tab('Remote Storage','remote_list',array($this, 'output_remote'), $args);
            $args['can_delete']=1;
            $args['hide']=1;
            $this->main_tab->add_tab('Download','download',array($this, 'output_download'), $args);
            $this->main_tab->add_tab('Restore','log',array($this, 'output_log'), $args);
            $this->main_tab->display();
            ?>
        </div>
        <?php
    }

    public function output_localhost(){
        if(isset($this->setting['wpvivid_local_setting']['path']) && !empty($this->setting['wpvivid_local_setting']['path'])){
            $local_path = $this->setting['wpvivid_local_setting']['path'];
        }
        else{
            $local_path = 'wpvividbackups';
        }
        ?>
        <div style="margin-top: 10px;">
            <div class="mwp-quickstart-storage-setting">
                <div style="padding: 10px 0 0 0;">
                    <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-font-right-space" style="float: left;"><?php _e('Child-Site Storage Directory: '); ?></div>
                    <div style="float: left;">
                        <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-font-right-space" style="float: left;"><?php _e('http(s)://child-site/wp-content/'); ?><?php _e($local_path); ?></div>
                        <small>
                            <div class="mwp-wpvivid-tooltip" style="float: left; margin-top: 4px; line-height: 100%;">?
                                <div class="mwp-wpvivid-tooltiptext">The backups will be uploaded to <?php _e('http(s)://child-site/wp-content/'.$local_path); ?></div>
                            </div>
                        </small>
                        <div style="clear: both;"></div>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>
            <div style="clear: both;"></div>

            <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-font-right-space" style="float: left; height: 30px; line-height: 30px;">Displays all backups stored under</div>
            <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-font-right-space" style="float: left;">
                <select id="mwp_wpvivid_select_local_backup_folder" onchange="mwp_wpvivid_get_local_backup_folder();">
                    <option value="wpvivid" selected="selected">wpvividbackups</option>
                    <option value="rollback">Rollback</option>
                    <option value="incremental">Incremental</option>
                </select>
            </div>
            <div class="mwp-wpvivid-block-bottom-space" style="float: left; height: 30px; line-height: 30px;">
                <a onclick="mwp_wpvivid_rollback_folder_descript();" style="cursor: pointer;">what is Rollback folder?</a>
            </div>
            <div style="clear: both;"></div>

            <div class="mwp-wpvivid-click-popup mwp-wpvivid-block-bottom-space" id="mwp_wpvivid_rollbackup_folder_desc" style="display: none;">
                <div>Rollback folder stores all backups before updating themes, plugins or WordPress core files.</div>
            </div>
            <div style="clear: both;"></div>

            <div class="mwp-wpvivid-block-bottom-space" id="mwp_wpvivid_scan_local_backup">
                <div class="mwp-wpvivid-block-bottom-space">
                    <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                        <input type="button" class="ui green mini button" id="mwp_wpvivid_rescan_local_folder_btn" value="Scan uploaded backup or received backup" onclick="mwp_wpvivid_rescan_local_folder();" style="float: left;" />
                    </div>
                    <small>
                        <div class="mwp-wpvivid-tooltip" style="float: left; margin-top: 10px; line-height: 100%;">?
                            <div class="mwp-wpvivid-tooltiptext">Scan all uploaded or received backups in directory <?php _e('http(s)://child-site/wp-content/'.$local_path); ?></div>
                        </div>
                    </small>
                    <div class="spinner" id="mwp_wpvivid_scanning_local_folder" style="float: left;"></div>
                    <div style="clear: both;"></div>
                </div>
            </div>
            <div class="mwp-wpvivid-local-remote-backup-list" id="mwp_wpvivid_backup_list"></div>
        </div>
        <script>
            function mwp_wpvivid_rollback_folder_descript(){
                if(jQuery('#mwp_wpvivid_rollbackup_folder_desc').is(":hidden"))
                {
                    jQuery('#mwp_wpvivid_rollbackup_folder_desc').show();
                }
                else{
                    jQuery('#mwp_wpvivid_rollbackup_folder_desc').hide();
                }
            }

            function mwp_wpvivid_rescan_local_folder(){
                var ajax_data = {
                    'action': 'mwp_wpvivid_rescan_local_folder_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>'
                };
                jQuery('#mwp_wpvivid_rescan_local_folder_btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#mwp_wpvivid_scanning_local_folder').addClass('is-active');
                mwp_wpvivid_post_request(ajax_data, function (data)
                {
                    jQuery('#mwp_wpvivid_rescan_local_folder_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_scanning_local_folder').removeClass('is-active');
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            mwp_wpvivid_get_local_backup_folder();
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch(err) {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#mwp_wpvivid_rescan_local_folder_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_scanning_local_folder').removeClass('is-active');
                    var error_message = mwp_wpvivid_output_ajaxerror('scanning backup list', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function mwp_wpvivid_get_local_backup_folder(page=0){
                if(page === 0){
                    var current_page = jQuery('#mwp_wpvivid_backup_list').find('.current-page').val();
                    if(typeof current_page !== 'undefined') {
                        page = jQuery('#mwp_wpvivid_backup_list').find('.current-page').val();
                    }
                }
                var value = jQuery('#mwp_wpvivid_select_local_backup_folder').val();
                if(value === 'rollback' || value == 'incremental')
                {
                    jQuery('#mwp_wpvivid_scan_local_backup').hide();
                }
                else
                {
                    jQuery('#mwp_wpvivid_scan_local_backup').show();
                }
                var ajax_data = {
                    'action': 'mwp_wpvivid_achieve_local_backup_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'folder': value,
                    'page':page
                };
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    jQuery('#mwp_wpvivid_backup_list').html('');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#mwp_wpvivid_backup_list').html(jsonarray.html);
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err)
                    {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    setTimeout(function () {
                        mwp_wpvivid_get_local_backup_folder();
                    }, 3000);
                });
            }

            jQuery('#mwp_wpvivid_backup_list').on("click",'.first-page',function() {
                mwp_wpvivid_get_local_backup_folder('first');
            });

            jQuery('#mwp_wpvivid_backup_list').on("click",'.prev-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                mwp_wpvivid_get_local_backup_folder(page-1);
            });

            jQuery('#mwp_wpvivid_backup_list').on("click",'.next-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                mwp_wpvivid_get_local_backup_folder(page+1);
            });

            jQuery('#mwp_wpvivid_backup_list').on("click",'.last-page',function() {
                mwp_wpvivid_get_local_backup_folder('last');
            });

            jQuery('#mwp_wpvivid_backup_list').on("keypress", '.current-page', function(){
                if(event.keyCode === 13){
                    var page = jQuery(this).val();
                    mwp_wpvivid_get_local_backup_folder(page);
                }
            });

            jQuery('#mwp_wpvivid_backup_list').on('click', '.mwp-wpvivid-lock', function(){
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                if(Obj.hasClass('lock')) {
                    var lock=0;
                }
                else {
                    var lock=1;
                }
                var ajax_data= {
                    'action': 'mwp_wpvivid_set_security_lock_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'backup_id': backup_id,
                    'lock': lock
                };
                mwp_wpvivid_post_request(ajax_data, function(data) {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            if(lock)
                            {
                                Obj.addClass('lock');
                            }
                            else
                            {
                                Obj.removeClass('lock');
                            }
                            Obj.html(jsonarray.html);
                        }
                    }
                    catch(err){
                        alert(err);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = mwp_wpvivid_output_ajaxerror('setting up a lock for the backup', textStatus, errorThrown);
                    alert(error_message);
                });
            });

            jQuery('#mwp_wpvivid_backup_list').on('click', '.mwp-wpvivid-restore', function() {
                <?php
                $white_label_setting = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option(sanitize_text_field($_GET['id']), 'white_label_setting', array());
                if(!$white_label_setting){
                    $location = 'admin.php?page=WPvivid&wpvivid-restore-page-mainwp';
                }
                else{
                    $slug = $white_label_setting['white_label_slug'];
                    $slug_page = strtolower($white_label_setting['white_label_slug']);
                    $location = 'admin.php?page='.$slug.'&'.$slug_page.'-restore-page-mainwp';
                }
                ?>
                location.href="admin.php?page=SiteOpen&newWindow=yes&websiteid=<?php echo esc_html($this->site_id); ?>&location=<?php echo esc_html(base64_encode($location)); ?>";
            });

            jQuery('#mwp_wpvivid_backup_list').on('click', '.mwp-backuplist-delete-backup', function(){
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                var current_page = jQuery('#mwp_wpvivid_backup_list').find('.current-page').val();
                if(typeof current_page !== 'undefined') {
                    var page = jQuery('#mwp_wpvivid_backup_list').find('.current-page').val();
                }
                else{
                    var page = 0;
                }
                var value = jQuery('#mwp_wpvivid_select_local_backup_folder').val();
                var descript = '<?php _e('Are you sure to remove this backup? This backup will be deleted permanently from your hosting (localhost) and remote storages.', 'wpvivid'); ?>';

                var ret = confirm(descript);
                if(ret === true) {
                    var ajax_data = {
                        'action': 'mwp_wpvivid_delete_local_backup_addon',
                        'site_id': '<?php echo esc_html($this->site_id); ?>',
                        'backup_id': backup_id,
                        'folder': value,
                        'page':page
                    };
                    mwp_wpvivid_post_request(ajax_data, function(data) {
                        try {
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success') {
                                jQuery('#mwp_wpvivid_backup_list').html(jsonarray.html);
                            }
                            else if(jsonarray.result === 'failed') {
                                alert(jsonarray.error);
                            }
                        }
                        catch(err){
                            alert(err);
                        }

                    }, function(XMLHttpRequest, textStatus, errorThrown) {
                        var error_message = mwp_wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                        alert(error_message);
                    });
                }
            });

            jQuery('#mwp_wpvivid_backup_list').on('click', '.mwp-wpvivid-delete-array', function(){
                var delete_backup_array = new Array();
                var count = 0;
                var current_page = jQuery('#mwp_wpvivid_backup_list').find('.current-page').val();
                if(typeof current_page !== 'undefined') {
                    var page = jQuery('#mwp_wpvivid_backup_list').find('.current-page').val();
                }
                else{
                    var page = 0;
                }
                var folder = jQuery('#mwp_wpvivid_select_local_backup_folder').val();
                jQuery('#mwp_wpvivid_backup_list .mwp-wpvivid-backup-row input').each(function (i)
                {
                    if(jQuery(this).prop('checked'))
                    {
                        delete_backup_array[count] =jQuery(this).closest('tr').attr('id');
                        count++;
                    }
                });
                if( count === 0 )
                {
                    alert('<?php _e('Please select at least one item.','wpvivid'); ?>');
                }
                else
                {
                    var descript = '<?php _e('Are you sure to remove the selected backups? These backups will be deleted permanently from your hosting (localhost).', 'wpvivid'); ?>';

                    var ret = confirm(descript);
                    if (ret === true)
                    {
                        var ajax_data = {
                            'action': 'mwp_wpvivid_delete_local_backup_array_addon',
                            'site_id': '<?php echo esc_html($this->site_id); ?>',
                            'backup_id': delete_backup_array,
                            'folder': folder,
                            'page':page
                        };

                        mwp_wpvivid_post_request(ajax_data, function (data) {
                            try {
                                var jsonarray = jQuery.parseJSON(data);
                                if (jsonarray.result === 'success') {
                                    jQuery('#mwp_wpvivid_backup_list').html(jsonarray.html);
                                }
                                else if(jsonarray.result === 'failed') {
                                    alert(jsonarray.error);
                                }
                            }
                            catch(err){
                                alert(err);
                            }
                        }, function (XMLHttpRequest, textStatus, errorThrown) {
                            var error_message = mwp_wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                            alert(error_message);
                        });
                    }
                }
            });

            jQuery(document).ready(function($) {
                jQuery(document).on('mwp_wpvivid_update_local_backup', function(event)
                {
                    jQuery('#mwp_wpvivid_select_local_backup_folder').val('wpvivid');
                    mwp_wpvivid_get_local_backup_folder();
                });
                mwp_wpvivid_get_local_backup_folder();
            });
        </script>
        <?php
    }

    public function output_remote_ex(){
        $remoteslist=$this->remote_addon['upload'];
        $has_remote = false;
        $path = 'Common';
        $remote_storage_option = '';
        $remote_array = array();
        $first_remote_path = '';
        foreach ($remoteslist as $key => $value)
        {
            if($key === 'remote_selected') {
                continue;
            }
            else{
                $has_remote = true;
                $value['type']=apply_filters('mwp_wpvivid_storage_provider_tran', $value['type']);
                $remote_storage_option.='<option value="'.$key.'">'.$value['type'].' -> '.$value['name'].'</option>';
                if(isset($value['custom_path']))
                {
                    $path = $value['path'].'wpvividbackuppro/'. $value['custom_path'];
                }
                else
                {
                    $path = $value['path'];
                }
                $remote_array[$key]['path'] = $path;
                if($first_remote_path === ''){
                    $first_remote_path = $path;
                }
            }
        }
        $path = $first_remote_path;
        ?>
        <div style="margin-top: 10px;">
            <?php
            if($has_remote) {
                ?>
                <div class="mwp-quickstart-storage-setting">
                    <div style="padding: 10px 0;">
                        <div class="mwp-wpvivid-font-right-space" style="float: left;">Current Folder Path:</div>
                        <div id="mwp_wpvivid_remote_folder" style="float: left;"><?php _e($path); ?></div>
                        <div style="clear: both;"></div>
                    </div>
                    <div style="clear: both;"></div>
                </div>
                <div style="clear: both;"></div>

                <div class="mwp-wpvivid-block-bottom-space">
                    <div style="float: left;">
                        <div>Display all backups stored in account
                            <select id="mwp_wpvivid_select_remote_storage" onchange="mwp_wpvivid_select_remote_storage_folder();"><?php _e($remote_storage_option); ?></select> under
                            <select id="mwp_wpvivid_select_remote_folder" onchange="mwp_wpvivid_select_remote_storage_folder();">
                                <option value="Common"><?php _e($path); ?></option>
                                <option value="Migrate">Migration</option>
                                <option value="Rollback">Rollback</option>
                                <option value="Incremental">Incremental</option>
                            </select> folder.
                        </div>
                    </div>
                    <div style="float: left; margin-left: 5px; height: 30px; line-height: 30px;">
                        <a onclick="mwp_wpvivid_explanation_folders();" style="cursor: pointer;">Explanation about these
                            folders.</a>
                    </div>
                    <div style="clear: both;"></div>
                </div>

                <div class="mwp-wpvivid-click-popup mwp-wpvivid-block-bottom-space" id="mwp_wpvivid_explanation_folders" style="display: none; padding: 0 0 0 10px;">
                    <ul>
                        <li><i id="mwp_wpvivid_explanation_backup_folder"><?php _e($path); ?></i> Folder is where the manual backups and scheduled backups are stored on your cloud storage. <a href="https://wpvivid.com/wpvivid-backup-pro-custom-backup-folder" target="_blank">Learn more</a></li>
                        <li><i>Migrate</i> Folder is where the backups for migration are stored on your cloud storage.<a href="https://wpvivid.com/wpvivid-backup-pro-migration-folder" target="_blank">Learn more</a></li>
                        <li><i id="mwp_wpvivid_explanation_rollback_folder"><?php _e($path); ?>/rollback</i> Folder is where the backups created before updating are stored. You can disable this feature in Settings. <a href="https://wpvivid.com/wpvivid-backup-pro-rollback-folder" target="_blank">Learn more</a></li>
                        <li><i id="mwp_wpvivid_explanation_incremental_folder"><?php _e($path); ?>/incremental</i> Folder is where the incremental backups are stored on your cloud storage.</li>
                    </ul>
                </div>
                <div style="clear: both;"></div>

                <div style="margin-bottom: 10px;">
                    <input class="ui green mini button" id="mwp_wpvivid_sync_remote_folder" type="button" value="Scan The Folder" onclick="mwp_wpvivid_select_remote_folder();" style="float: left;"/>
                    <div class="spinner" id="mwp_wpvivid_scanning_remote_folder" style="float: left;"></div>
                    <div style="clear: both;"></div>
                </div>
                <div class="mwp-wpvivid-remote-sync-error" style="display: none;"></div>
                <div style="clear: both;"></div>
                <?php
            }
            else{
                ?>
                <div class="mwp-quickstart-storage-setting mwp-wpvivid-block-bottom-space">
                    <div style="padding: 10px 0;">
                        <span style="margin-right: 0;">There is no remote storage available, please set it up first.</span>
                    </div>
                </div>
                <?php
            }
            ?>

            <div class="mwp-wpvivid-local-remote-backup-list" id="mwp_wpvivid_remote_backups_list"></div>
        </div>
        <script>
            var mwp_remote_folder = '';
            var mwp_incremental_remote_folder = '';
            var mwp_remote_list_array = {};
            <?php
            foreach ($remote_array as $key => $value) {
            ?>
            var key = '<?php echo $key; ?>';
            mwp_remote_list_array[key] = Array();
            mwp_remote_list_array[key]['path'] = '<?php echo $value['path']; ?>';
            <?php
            }
            ?>

            function mwp_wpvivid_select_remote_storage_folder(){
                var value = jQuery('#mwp_wpvivid_select_remote_folder').val();
                var remote_id = jQuery('#mwp_wpvivid_select_remote_storage').val();
                var common_folder = '';
                var rollback_folder = '';
                var incremental_folder = '';
                jQuery.each(mwp_remote_list_array, function(index, value){
                    if(remote_id === index){
                        common_folder = value.path;
                        rollback_folder = common_folder + "/rollback";
                        incremental_folder = common_folder + "/incremental";
                    }
                });
                jQuery('option[value=Common]').text(common_folder);
                jQuery('#mwp_wpvivid_explanation_backup_folder').html(common_folder);
                jQuery('#mwp_wpvivid_explanation_rollback_folder').html(rollback_folder);
                jQuery('#mwp_wpvivid_explanation_incremental_folder').html(incremental_folder);
                if(value === 'Common'){
                    jQuery('#mwp_wpvivid_remote_folder').html(common_folder);
                }
                else if(value === 'Staging'){
                    jQuery('#mwp_wpvivid_remote_folder').html('staging');
                }
                else if(value === 'Migrate'){
                    jQuery('#mwp_wpvivid_remote_folder').html('migrate');
                }
                else if(value === 'Rollback'){
                    jQuery('#mwp_wpvivid_remote_folder').html(rollback_folder);
                }
                else if(value === 'Incremental'){
                    jQuery('#mwp_wpvivid_remote_folder').html(incremental_folder);
                }
            }

            function mwp_wpvivid_explanation_folders(){
                if(jQuery('#mwp_wpvivid_explanation_folders').is(":hidden")) {
                    jQuery('#mwp_wpvivid_explanation_folders').show();
                }
                else{
                    jQuery('#mwp_wpvivid_explanation_folders').hide();
                }
            }

            function mwp_wpvivid_select_remote_folder(){
                mwp_wpvivid_get_remote_backup_folder();
            }

            function mwp_wpvivid_get_remote_backup_folder(page=0){
                if(page === 0){
                    var current_page = jQuery('#mwp_wpvivid_remote_backups_list').find('.current-page').val();
                    if(typeof current_page !== 'undefined') {
                        page = jQuery('#mwp_wpvivid_remote_backups_list').find('.current-page').val();
                    }
                }
                var remote_id = jQuery('#mwp_wpvivid_select_remote_storage').val();
                var remote_folder = jQuery('#mwp_wpvivid_select_remote_folder').val();
                var is_incremental = false;
                mwp_remote_folder=remote_folder;
                if(mwp_remote_folder === 'Incremental'){
                    is_incremental = true;
                    var ajax_data = {
                        'action': 'mwp_wpvivid_achieve_remote_backup_addon',
                        'remote_id': remote_id,
                        'folder': remote_folder,
                        'incremental_path': mwp_incremental_remote_folder,
                        'page':page
                    };
                }
                else{
                    var ajax_data = {
                        'action': 'mwp_wpvivid_achieve_remote_backup_addon',
                        'site_id': '<?php echo esc_html($this->site_id); ?>',
                        'remote_id': remote_id,
                        'folder': remote_folder,
                        'page':page
                    };
                }
                jQuery('#mwp_wpvivid_sync_remote_folder').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#mwp_wpvivid_remote_backups_list').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#mwp_wpvivid_scanning_remote_folder').addClass('is-active');
                jQuery('.mwp-wpvivid-remote-sync-error').hide();
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    jQuery('#mwp_wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_remote_backups_list').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_scanning_remote_folder').removeClass('is-active');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if(jsonarray !== null) {
                            if (jsonarray.result === 'success') {
                                jQuery('#mwp_wpvivid_remote_backups_list').html(jsonarray.html);
                            }
                            else {
                                jQuery('.mwp-wpvivid-remote-sync-error').show();
                                jQuery('.mwp-wpvivid-remote-sync-error').html(jsonarray.error);
                                jQuery('#mwp_wpvivid_remote_backups_list').html('');
                            }
                        }
                        else{
                            jQuery('#mwp_wpvivid_remote_backups_list').html('');
                        }
                    }
                    catch (err)
                    {
                        jQuery('.mwp-wpvivid-remote-sync-error').show();
                        jQuery('.mwp-wpvivid-remote-sync-error').html(err);
                        jQuery('#mwp_wpvivid_remote_backups_list').html('');
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    jQuery('#mwp_wpvivid_remote_backups_list').html('');
                    jQuery('#mwp_wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_scanning_remote_folder').removeClass('is-active');
                    var error_message = mwp_wpvivid_output_ajaxerror('achieving backup', textStatus, errorThrown);
                    jQuery('.mwp-wpvivid-remote-sync-error').show();
                    jQuery('.mwp-wpvivid-remote-sync-error').html(error_message);
                });
            }

            jQuery('#mwp_wpvivid_remote_backups_list').on("click",'.first-page',function() {
                mwp_wpvivid_get_remote_backup_folder('first');
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on("click",'.prev-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                mwp_wpvivid_get_remote_backup_folder(page-1);
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on("click",'.next-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                mwp_wpvivid_get_remote_backup_folder(page+1);
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on("click",'.last-page',function() {
                mwp_wpvivid_get_remote_backup_folder('last');
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on("keypress", '.current-page', function(){
                if(event.keyCode === 13){
                    var page = jQuery(this).val();
                    mwp_wpvivid_get_remote_backup_folder(page);
                }
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on('click', '.mwp-wpvivid-lock', function(){
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                if(Obj.hasClass('lock')) {
                    var lock=0;
                }
                else {
                    var lock=1;
                }
                var ajax_data= {
                    'action': 'mwp_wpvivid_set_remote_security_lock_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'backup_id': backup_id,
                    'lock': lock
                };
                mwp_wpvivid_post_request(ajax_data, function(data) {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            if(lock)
                            {
                                Obj.addClass('lock');
                            }
                            else
                            {
                                Obj.removeClass('lock');
                            }
                            Obj.html(jsonarray.html);
                        }
                    }
                    catch(err){
                        alert(err);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = mwp_wpvivid_output_ajaxerror('setting up a lock for the backup', textStatus, errorThrown);
                    alert(error_message);
                });
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on('click', '.mwp-wpvivid-restore', function() {
                <?php
                $location = 'admin.php?page=WPvivid';
                ?>
                location.href="admin.php?page=SiteOpen&newWindow=yes&websiteid=<?php echo esc_html($this->site_id); ?>&location=<?php echo esc_html(base64_encode($location)); ?>";
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on('click', '.mwp-backuplist-delete-backup', function(){
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                var current_page = jQuery('#mwp_wpvivid_remote_backups_list').find('.current-page').val();
                if(typeof current_page !== 'undefined') {
                    var page = jQuery('#mwp_wpvivid_remote_backups_list').find('.current-page').val();
                }
                else{
                    var page = 0;
                }
                var descript = '<?php _e('Are you sure to remove this backup? This backup will be deleted permanently from your hosting (localhost) and remote storages.', 'wpvivid'); ?>';
                var ret = confirm(descript);
                if(ret === true) {
                    var ajax_data = {
                        'action': 'mwp_wpvivid_delete_remote_backup_addon',
                        'site_id': '<?php echo esc_html($this->site_id); ?>',
                        'backup_id': backup_id,
                        'folder': mwp_remote_folder,
                        'page':page
                    };
                    mwp_wpvivid_post_request(ajax_data, function(data) {
                        try {
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success') {
                                jQuery('#mwp_wpvivid_remote_backups_list').html(jsonarray.html);
                            }
                            else if(jsonarray.result === 'failed') {
                                alert(jsonarray.error);
                            }
                        }
                        catch(err){
                            alert(err);
                        }

                    }, function(XMLHttpRequest, textStatus, errorThrown) {
                        var error_message = mwp_wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                        alert(error_message);
                    });
                }
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on('click', '.mwp-wpvivid-delete-array', function(){
                var delete_backup_array = new Array();
                var count = 0;
                var current_page = jQuery('#mwp_wpvivid_remote_backups_list').find('.current-page').val();
                if(typeof current_page !== 'undefined') {
                    var page = jQuery('#mwp_wpvivid_remote_backups_list').find('.current-page').val();
                }
                else{
                    var page = 0;
                }
                jQuery('#mwp_wpvivid_remote_backups_list .mwp-wpvivid-backup-row input').each(function (i)
                {
                    if(jQuery(this).prop('checked'))
                    {
                        delete_backup_array[count] =jQuery(this).closest('tr').attr('id');
                        count++;
                    }
                });
                if( count === 0 )
                {
                    alert('<?php _e('Please select at least one item.','wpvivid'); ?>');
                }
                else
                {
                    var descript = '<?php _e('Are you sure to remove the selected backups? These backups will be deleted permanently from your hosting (localhost).', 'wpvivid'); ?>';
                    var ret = confirm(descript);
                    if (ret === true)
                    {
                        var ajax_data = {
                            'action': 'mwp_wpvivid_delete_remote_backup_array_addon',
                            'site_id': '<?php echo esc_html($this->site_id); ?>',
                            'backup_id': delete_backup_array,
                            'folder': mwp_remote_folder,
                            'page':page
                        };

                        mwp_wpvivid_post_request(ajax_data, function (data) {
                            try {
                                var jsonarray = jQuery.parseJSON(data);
                                if (jsonarray.result === 'success') {
                                    jQuery('#mwp_wpvivid_remote_backups_list').html(jsonarray.html);
                                }
                                else if(jsonarray.result === 'failed') {
                                    alert(jsonarray.error);
                                }
                            }
                            catch(err){
                                alert(err);
                            }
                        }, function (XMLHttpRequest, textStatus, errorThrown) {
                            var error_message = mwp_wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                            alert(error_message);
                        });
                    }
                }
            });

            jQuery(document).ready(function($) {
                jQuery(document).on('mwp_wpvivid_update_remote_backup', function(event) {
                    mwp_wpvivid_get_remote_backup_folder();
                });
                mwp_wpvivid_get_remote_backup_folder();
            });
        </script>
        <?php
    }

    public function output_remote(){
        ?>
        <div style="margin-top: 10px;">
            <div id="mwp_wpvivid_achieve_remote_backup_step_1">
                <div class="spinner is-active" id="mwp_wpvivid_achieving_remote_backup_info" style="float: left;"></div>
                <div style="margin-top: 4px; float: left;">Archieving Remote Storage Info</div>
                <div style="clear: both;"></div>
            </div>
            <div id="mwp_wpvivid_achieve_remote_backup_error"></div>
            <div id="mwp_wpvivid_achieve_remote_backup_step_2">
                <div id="mwp_wpvivid_remote_list_part"></div>
                <div class="mwp-wpvivid-local-remote-backup-list mwp-wpvivid-block-bottom-space" id="mwp_wpvivid_incremental_path_list"></div>
                <div class="mwp-wpvivid-local-remote-backup-list" id="mwp_wpvivid_remote_backups_list"></div>
            </div>
        </div>
        <script>
            var mwp_remote_folder = '';
            var mwp_incremental_remote_folder = '';
            var mwp_remote_list_array = {};

            function mwp_wpvivid_select_remote_storage_folder(){
                var value = jQuery('#mwp_wpvivid_select_remote_folder').val();
                var remote_id = jQuery('#mwp_wpvivid_select_remote_storage').val();
                var common_folder = '';
                var rollback_folder = '';
                var incremental_folder = '';
                jQuery.each(mwp_remote_list_array, function(index, value){
                    if(remote_id === index){
                        common_folder = value.path;
                        rollback_folder = common_folder + "/rollback";
                        incremental_folder = common_folder + "/incremental";
                    }
                });
                jQuery('option[value=Common]').text(common_folder);
                jQuery('#mwp_wpvivid_explanation_backup_folder').html(common_folder);
                jQuery('#mwp_wpvivid_explanation_rollback_folder').html(rollback_folder);
                jQuery('#mwp_wpvivid_explanation_incremental_folder').html(incremental_folder);
                jQuery('#mwp_wpvivid_incremental_path_list').hide();
                if(value === 'Common'){
                    jQuery('#mwp_wpvivid_remote_folder').html(common_folder);
                }
                else if(value === 'Staging'){
                    jQuery('#mwp_wpvivid_remote_folder').html('staging');
                }
                else if(value === 'Migrate'){
                    jQuery('#mwp_wpvivid_remote_folder').html('migrate');
                }
                else if(value === 'Rollback'){
                    jQuery('#mwp_wpvivid_remote_folder').html(rollback_folder);
                }
                else if(value === 'Incremental'){
                    jQuery('#mwp_wpvivid_remote_folder').html(incremental_folder);
                }
            }

            function mwp_wpvivid_explanation_folders(){
                if(jQuery('#mwp_wpvivid_explanation_folders').is(":hidden")) {
                    jQuery('#mwp_wpvivid_explanation_folders').show();
                }
                else{
                    jQuery('#mwp_wpvivid_explanation_folders').hide();
                }
            }

            function mwp_wpvivid_select_remote_folder(){
                mwp_wpvivid_get_remote_backup_folder();
            }

            function mwp_wpvivid_get_remote_backup_folder(page=0){
                var is_page_turn = true;
                if(page === 0){
                    is_page_turn = false;
                    var current_page = jQuery('#mwp_wpvivid_remote_backups_list').find('.current-page').val();
                    if(typeof current_page !== 'undefined') {
                        page = jQuery('#mwp_wpvivid_remote_backups_list').find('.current-page').val();
                    }
                }
                var remote_id = jQuery('#mwp_wpvivid_select_remote_storage').val();
                var remote_folder = jQuery('#mwp_wpvivid_select_remote_folder').val();
                var is_incremental = false;
                mwp_remote_folder=remote_folder;

                if(mwp_remote_folder === 'Incremental'){
                    is_incremental = true;
                    var ajax_data = {
                        'action': 'mwp_wpvivid_achieve_remote_backup_addon',
                        'site_id': '<?php echo esc_html($this->site_id); ?>',
                        'remote_id': remote_id,
                        'folder': remote_folder,
                        'incremental_path': mwp_incremental_remote_folder,
                        'page':page
                    };
                }
                else{
                    var ajax_data = {
                        'action': 'mwp_wpvivid_achieve_remote_backup_addon',
                        'site_id': '<?php echo esc_html($this->site_id); ?>',
                        'remote_id': remote_id,
                        'folder': remote_folder,
                        'page':page
                    };
                }

                jQuery('#mwp_wpvivid_sync_remote_folder').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#mwp_wpvivid_remote_backups_list').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#mwp_wpvivid_scanning_remote_folder').addClass('is-active');
                jQuery('.mwp-wpvivid-remote-sync-error').hide();
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    jQuery('#mwp_wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_remote_backups_list').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_scanning_remote_folder').removeClass('is-active');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if(jsonarray !== null) {
                            if (jsonarray.result === 'success') {
                                if(is_page_turn){
                                    jQuery('#mwp_wpvivid_remote_backups_list').html(jsonarray.html);
                                }
                                else if(!is_incremental) {
                                    jQuery('#mwp_wpvivid_incremental_path_list').hide();
                                    jQuery('#mwp_wpvivid_incremental_path_list').html('');
                                    jQuery('#mwp_wpvivid_remote_backups_list').html(jsonarray.html);
                                }
                                else{
                                    jQuery('#mwp_wpvivid_incremental_path_list').show();
                                    jQuery('#mwp_wpvivid_incremental_path_list').html(jsonarray.incremental_list);
                                    jQuery('#mwp_wpvivid_remote_backups_list').html('');
                                }
                            }
                            else {
                                jQuery('.mwp-wpvivid-remote-sync-error').show();
                                jQuery('.mwp-wpvivid-remote-sync-error').html(jsonarray.error);
                                jQuery('#mwp_wpvivid_remote_backups_list').html('');
                            }
                        }
                        else{
                            jQuery('#mwp_wpvivid_remote_backups_list').html('');
                        }
                    }
                    catch (err)
                    {
                        jQuery('.mwp-wpvivid-remote-sync-error').show();
                        jQuery('.mwp-wpvivid-remote-sync-error').html(err);
                        jQuery('#mwp_wpvivid_remote_backups_list').html('');
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    jQuery('#mwp_wpvivid_remote_backups_list').html('');
                    jQuery('#mwp_wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_scanning_remote_folder').removeClass('is-active');
                    var error_message = mwp_wpvivid_output_ajaxerror('achieving backup', textStatus, errorThrown);
                    jQuery('.mwp-wpvivid-remote-sync-error').show();
                    jQuery('.mwp-wpvivid-remote-sync-error').html(error_message);
                });
            }

            jQuery('#mwp_wpvivid_remote_backups_list').on("click",'.first-page',function() {
                mwp_wpvivid_get_remote_backup_folder('first');
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on("click",'.prev-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                mwp_wpvivid_get_remote_backup_folder(page-1);
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on("click",'.next-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                mwp_wpvivid_get_remote_backup_folder(page+1);
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on("click",'.last-page',function() {
                mwp_wpvivid_get_remote_backup_folder('last');
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on("keypress", '.current-page', function(){
                if(event.keyCode === 13){
                    var page = jQuery(this).val();
                    mwp_wpvivid_get_remote_backup_folder(page);
                }
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on('click', '.mwp-wpvivid-lock', function(){
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                if(Obj.hasClass('lock')) {
                    var lock=0;
                }
                else {
                    var lock=1;
                }
                var ajax_data= {
                    'action': 'mwp_wpvivid_set_remote_security_lock_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'backup_id': backup_id,
                    'lock': lock
                };
                mwp_wpvivid_post_request(ajax_data, function(data) {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            if(lock)
                            {
                                Obj.addClass('lock');
                            }
                            else
                            {
                                Obj.removeClass('lock');
                            }
                            Obj.html(jsonarray.html);
                        }
                    }
                    catch(err){
                        alert(err);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = mwp_wpvivid_output_ajaxerror('setting up a lock for the backup', textStatus, errorThrown);
                    alert(error_message);
                });
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on('click', '.mwp-wpvivid-restore', function() {
                <?php
                $location = 'admin.php?page=WPvivid';
                ?>
                location.href="admin.php?page=SiteOpen&newWindow=yes&websiteid=<?php echo esc_html($this->site_id); ?>&location=<?php echo esc_html(base64_encode($location)); ?>";
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on('click', '.mwp-backuplist-delete-backup', function(){
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                var current_page = jQuery('#mwp_wpvivid_remote_backups_list').find('.current-page').val();
                if(typeof current_page !== 'undefined') {
                    var page = jQuery('#mwp_wpvivid_remote_backups_list').find('.current-page').val();
                }
                else{
                    var page = 0;
                }
                var descript = '<?php _e('Are you sure to remove this backup? This backup will be deleted permanently from your hosting (localhost) and remote storages.', 'wpvivid'); ?>';
                var ret = confirm(descript);
                if(ret === true) {
                    var ajax_data = {
                        'action': 'mwp_wpvivid_delete_remote_backup_addon',
                        'site_id': '<?php echo esc_html($this->site_id); ?>',
                        'backup_id': backup_id,
                        'folder': mwp_remote_folder,
                        'page':page
                    };
                    mwp_wpvivid_post_request(ajax_data, function(data) {
                        try {
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success') {
                                jQuery('#mwp_wpvivid_remote_backups_list').html(jsonarray.html);
                            }
                            else if(jsonarray.result === 'failed') {
                                alert(jsonarray.error);
                            }
                        }
                        catch(err){
                            alert(err);
                        }

                    }, function(XMLHttpRequest, textStatus, errorThrown) {
                        var error_message = mwp_wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                        alert(error_message);
                    });
                }
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on('click', '.mwp-wpvivid-delete-array', function(){
                var delete_backup_array = new Array();
                var count = 0;
                var current_page = jQuery('#mwp_wpvivid_remote_backups_list').find('.current-page').val();
                if(typeof current_page !== 'undefined') {
                    var page = jQuery('#mwp_wpvivid_remote_backups_list').find('.current-page').val();
                }
                else{
                    var page = 0;
                }
                jQuery('#mwp_wpvivid_remote_backups_list .mwp-wpvivid-backup-row input').each(function (i)
                {
                    if(jQuery(this).prop('checked'))
                    {
                        delete_backup_array[count] =jQuery(this).closest('tr').attr('id');
                        count++;
                    }
                });
                if( count === 0 )
                {
                    alert('<?php _e('Please select at least one item.','wpvivid'); ?>');
                }
                else
                {
                    var descript = '<?php _e('Are you sure to remove the selected backups? These backups will be deleted permanently from your hosting (localhost).', 'wpvivid'); ?>';
                    var ret = confirm(descript);
                    if (ret === true)
                    {
                        var ajax_data = {
                            'action': 'mwp_wpvivid_delete_remote_backup_array_addon',
                            'site_id': '<?php echo esc_html($this->site_id); ?>',
                            'backup_id': delete_backup_array,
                            'folder': mwp_remote_folder,
                            'page':page
                        };

                        mwp_wpvivid_post_request(ajax_data, function (data) {
                            try {
                                var jsonarray = jQuery.parseJSON(data);
                                if (jsonarray.result === 'success') {
                                    jQuery('#mwp_wpvivid_remote_backups_list').html(jsonarray.html);
                                }
                                else if(jsonarray.result === 'failed') {
                                    alert(jsonarray.error);
                                }
                            }
                            catch(err){
                                alert(err);
                            }
                        }, function (XMLHttpRequest, textStatus, errorThrown) {
                            var error_message = mwp_wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                            alert(error_message);
                        });
                    }
                }
            });

            var mwp_wpvivid_get_remote_backup_info_retry_times = 0;
            function mwp_wpvivid_get_remote_backup_info_retry(error_msg){
                var need_retry_get_remote_backup_info = false;
                mwp_wpvivid_get_remote_backup_info_retry_times++;
                if(mwp_wpvivid_get_remote_backup_info_retry_times < 3){
                    need_retry_get_remote_backup_info = true;
                }
                if(need_retry_get_remote_backup_info){
                    setTimeout(function(){
                        mwp_wpvivid_get_remote_backup_info();
                    }, 3000);
                }
                else{
                    var refresh_btn = '<div>'+error_msg+'</div>' +
                        '<input class="ui green mini button" type="button" value="Retry" onclick="mwp_wpvivid_refresh_get_remote_backup_info();">';
                    jQuery('#mwp_wpvivid_achieve_remote_backup_step_1').hide();
                    jQuery('#mwp_wpvivid_achieve_remote_backup_step_2').hide();
                    jQuery('#mwp_wpvivid_achieve_remote_backup_error').show();
                    jQuery('#mwp_wpvivid_achieving_remote_backup_info').removeClass('is-active');
                    jQuery('#mwp_wpvivid_achieve_remote_backup_error').html(refresh_btn);
                }
            }

            function mwp_wpvivid_refresh_get_remote_backup_info(){
                mwp_wpvivid_get_remote_backup_info_retry_times = 0;
                mwp_wpvivid_get_remote_backup_info();
            }

            function mwp_wpvivid_get_remote_backup_info(){
                var ajax_data = {
                    'action': 'mwp_wpvivid_achieve_remote_backup_info_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>'
                };
                jQuery('#mwp_wpvivid_achieve_remote_backup_step_1').show();
                jQuery('#mwp_wpvivid_achieve_remote_backup_step_2').hide();
                jQuery('#mwp_wpvivid_achieve_remote_backup_error').hide();
                jQuery('#mwp_wpvivid_achieving_remote_backup_info').addClass('is-active');
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            jQuery('#mwp_wpvivid_achieve_remote_backup_step_1').hide();
                            jQuery('#mwp_wpvivid_achieve_remote_backup_step_2').show();
                            jQuery('#mwp_wpvivid_achieve_remote_backup_error').hide();
                            jQuery('#mwp_wpvivid_achieving_remote_backup_info').removeClass('is-active');
                            jQuery('#mwp_wpvivid_remote_list_part').html(jsonarray.remote_part_html);
                            jQuery('#mwp_wpvivid_remote_backups_list').html(jsonarray.select_list_html);
                            jQuery.each(jsonarray.remote_list, function(key, value){
                                if(key !== 'remote_selected') {
                                    mwp_remote_list_array[key] = Array();
                                    if(typeof value.custom_path !== 'undefined'){
                                        var path = value.path + 'wpvividbackuppro/' + value.custom_path;
                                    }
                                    else{
                                        var path = value.path;
                                    }
                                    mwp_remote_list_array[key]['path'] = path;
                                }
                            });
                        }
                        else {
                            mwp_wpvivid_get_remote_backup_info_retry(jsonarray.error);
                        }
                    }
                    catch (err) {
                        mwp_wpvivid_get_remote_backup_info_retry(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = mwp_wpvivid_output_ajaxerror('achieving backup', textStatus, errorThrown);
                    mwp_wpvivid_get_remote_backup_info_retry(error_message);
                });
            }

            jQuery('#mwp_wpvivid_incremental_path_list').on('click', '.first-page', function(){
                mwp_wpvivid_archieve_incremental_remote_folder_list('first')
            });

            jQuery('#mwp_wpvivid_incremental_path_list').on('click', '.prev-page', function(){
                var page=parseInt(jQuery(this).attr('value'));
                mwp_wpvivid_archieve_incremental_remote_folder_list(page-1);
            });

            jQuery('#mwp_wpvivid_incremental_path_list').on('click', '.next-page', function(){
                var page=parseInt(jQuery(this).attr('value'));
                mwp_wpvivid_archieve_incremental_remote_folder_list(page+1);
            });

            jQuery('#mwp_wpvivid_incremental_path_list').on('click', '.last-page', function(){
                mwp_wpvivid_archieve_incremental_remote_folder_list('last');
            });

            jQuery('#mwp_wpvivid_incremental_path_list').on('keypress', '.current-page', function(){
                if(event.keyCode === 13){
                    var page = jQuery(this).val();
                    mwp_wpvivid_archieve_incremental_remote_folder_list(page);
                }
            });

            function mwp_wpvivid_archieve_incremental_remote_folder_list(page=0){
                if(page === 0){
                    var current_page = jQuery('#mwp_wpvivid_incremental_path_list').find('.current-page').val();
                    if(typeof current_page !== 'undefined') {
                        page = jQuery('#mwp_wpvivid_incremental_path_list').find('.current-page').val();
                    }
                }
                var remote_id = jQuery('#mwp_wpvivid_select_remote_storage').val();
                var remote_folder = 'Common';
                var ajax_data = {
                    'action': 'mwp_wpvivid_archieve_incremental_remote_folder_list_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'remote_id': remote_id,
                    'folder': remote_folder,
                    'page':page
                };
                jQuery('#mwp_wpvivid_sync_remote_folder').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#mwp_wpvivid_remote_backups_list').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#mwp_wpvivid_scanning_remote_folder').addClass('is-active');
                jQuery('.mwp-wpvivid-remote-sync-error').hide();
                mwp_wpvivid_post_request(ajax_data, function (data)
                {
                    jQuery('#mwp_wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_remote_backups_list').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_scanning_remote_folder').removeClass('is-active');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if(jsonarray !== null) {
                            if (jsonarray.result === 'success') {
                                jQuery('#mwp_wpvivid_incremental_path_list').show();
                                jQuery('#mwp_wpvivid_incremental_path_list').html(jsonarray.incremental_list);
                                jQuery('#mwp_wpvivid_remote_backups_list').html('');
                            }
                            else {
                                jQuery('.mwp-wpvivid-remote-sync-error').show();
                                jQuery('.mwp-wpvivid-remote-sync-error').html(jsonarray.error);
                                jQuery('#mwp_wpvivid_remote_backups_list').html('');
                            }
                        }
                        else{
                            jQuery('#mwp_wpvivid_remote_backups_list').html('');
                        }
                    }
                    catch (err)
                    {
                        jQuery('.mwp-wpvivid-remote-sync-error').show();
                        jQuery('.mwp-wpvivid-remote-sync-error').html(err);
                        jQuery('#mwp_wpvivid_remote_backups_list').html('');
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#mwp_wpvivid_remote_backups_list').html('');
                    jQuery('#mwp_wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_scanning_remote_folder').removeClass('is-active');
                    var error_message = mwp_wpvivid_output_ajaxerror('achieving backup', textStatus, errorThrown);
                    jQuery('.mwp-wpvivid-remote-sync-error').show();
                    jQuery('.mwp-wpvivid-remote-sync-error').html(error_message);
                });
            }

            jQuery('#mwp_wpvivid_incremental_path_list').on('click', '.mwp-wpvivid-incremental-child', function(){
                var incremental_path = jQuery(this).closest('tr').attr('id');
                var remote_id = jQuery('#mwp_wpvivid_select_remote_storage').val();
                mwp_incremental_remote_folder = incremental_path;

                var ajax_data = {
                    'action': 'mwp_wpvivid_achieve_incremental_child_path_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'remote_id': remote_id,
                    'incremental_path': incremental_path
                };
                jQuery('#mwp_wpvivid_sync_remote_folder').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#mwp_wpvivid_remote_backups_list').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('.mwp-wpvivid-incremental-child').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#mwp_wpvivid_scanning_remote_folder').addClass('is-active');
                jQuery('.mwp-wpvivid-remote-sync-error').hide();

                mwp_wpvivid_post_request(ajax_data, function (data){
                    jQuery('#mwp_wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_remote_backups_list').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('.mwp-wpvivid-incremental-child').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_scanning_remote_folder').removeClass('is-active');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if(jsonarray !== null) {
                            if (jsonarray.result === 'success') {
                                jQuery('#mwp_wpvivid_remote_backups_list').html(jsonarray.html);
                            }
                            else {
                                jQuery('.mwp-wpvivid-remote-sync-error').show();
                                jQuery('.mwp-wpvivid-remote-sync-error').html(jsonarray.error);
                                jQuery('#mwp_wpvivid_remote_backups_list').html('');
                            }
                        }
                        else{
                            jQuery('#mwp_wpvivid_remote_backups_list').html('');
                        }
                    }
                    catch (err)
                    {
                        jQuery('.mwp-wpvivid-remote-sync-error').show();
                        jQuery('.mwp-wpvivid-remote-sync-error').html(err);
                        jQuery('#mwp_wpvivid_remote_backups_list').html('');
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    jQuery('#mwp_wpvivid_remote_backups_list').html('');
                    jQuery('#mwp_wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('.mwp-wpvivid-incremental-child').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#mwp_wpvivid_scanning_remote_folder').removeClass('is-active');
                    var error_message = mwp_wpvivid_output_ajaxerror('achieving backup', textStatus, errorThrown);
                    jQuery('.mwp-wpvivid-remote-sync-error').show();
                    jQuery('.mwp-wpvivid-remote-sync-error').html(error_message);
                });
            });

            jQuery(document).ready(function($) {
                jQuery(document).on('mwp_wpvivid_update_remote_backup', function(event) {
                    mwp_wpvivid_get_remote_backup_folder();
                });
                mwp_wpvivid_get_remote_backup_info();
            });
        </script>
        <?php
    }

    public function output_download(){
        ?>
        <div style="margin-top: 10px;">
            <div id="mwp_wpvivid_init_download_info">
                <div style="float: left; height: 20px; line-height: 20px; margin-top: 4px;">Initializing the download info</div>
                <div class="spinner" style="float: left;"></div>
                <div style="clear: both;"></div>
            </div>
            <div id="mwp_wpvivid_files_list">
            </div>
        </div>
        <script>
            var mwp_wpvivid_download_files_list = {};
            mwp_wpvivid_download_files_list.backup_id='';
            mwp_wpvivid_download_files_list.wpvivid_download_file_array = Array();
            mwp_wpvivid_download_files_list.wpvivid_download_lock_array = Array();

            mwp_wpvivid_download_files_list.init=function(backup_id)
            {
                mwp_wpvivid_download_files_list.backup_id=backup_id;
                mwp_wpvivid_download_files_list.wpvivid_download_file_array.splice(0, mwp_wpvivid_download_files_list.wpvivid_download_file_array.length);
            };

            mwp_wpvivid_download_files_list.add_download_queue=function(filename)
            {
                var download_file_size = jQuery("[slug='"+filename+"']").find('.mwp-wpvivid-download-status').find('.mwp-wpvivid-download-file-size').html();
                var tmp_html = '<div class="mwp-wpvivid-block-bottom-space">' +
                    '<span class="mwp-wpvivid-block-right-space">Retriving (remote storage to web server)</span><span class="mwp-wpvivid-block-right-space">|</span><span>File Size: </span><span class="mwp-wpvivid-block-right-space">'+download_file_size+'</span><span class="mwp-wpvivid-block-right-space">|</span><span>Downloaded Size: </span><span>0</span>' +
                    '</div>' +
                    '<div style="width:100%;height:10px; background-color:#dcdcdc;">' +
                    '<div style="background-color:#0085ba; float:left;width:0%;height:10px;"></div>' +
                    '</div>';
                jQuery("[slug='"+filename+"']").find('.mwp-wpvivid-download-status').html(tmp_html);
                if(jQuery.inArray(filename, mwp_wpvivid_download_files_list.wpvivid_download_file_array) === -1) {
                    mwp_wpvivid_download_files_list.wpvivid_download_file_array.push(filename);
                }
                var ajax_data = {
                    'action': 'mwp_wpvivid_prepare_download_backup_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'backup_id':mwp_wpvivid_download_files_list.backup_id,
                    'file_name':filename
                };
                mwp_wpvivid_post_request(ajax_data, function(data)
                {
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                }, 0);

                mwp_wpvivid_download_files_list.check_queue();
            };

            mwp_wpvivid_download_files_list.check_queue=function()
            {
                if(jQuery.inArray(mwp_wpvivid_download_files_list.backup_id, mwp_wpvivid_download_files_list.wpvivid_download_lock_array) !== -1){
                    return;
                }
                var ajax_data = {
                    'action': 'mwp_wpvivid_get_download_progress_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'backup_id': mwp_wpvivid_download_files_list.backup_id
                };
                mwp_wpvivid_download_files_list.wpvivid_download_lock_array.push(mwp_wpvivid_download_files_list.backup_id);
                mwp_wpvivid_post_request(ajax_data, function(data){
                    mwp_wpvivid_download_files_list.wpvivid_download_lock_array.splice(jQuery.inArray(mwp_wpvivid_download_files_list.backup_id, mwp_wpvivid_download_files_list.wpvivid_download_file_array),1);
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success') {
                        jQuery.each(jsonarray.files,function (index, value) {
                            if(jQuery.inArray(index, mwp_wpvivid_download_files_list.wpvivid_download_file_array) !== -1) {
                                if(value.status === 'timeout' || value.status === 'completed' || value.status === 'error'){
                                    mwp_wpvivid_download_files_list.wpvivid_download_file_array.splice(jQuery.inArray(index, mwp_wpvivid_download_files_list.wpvivid_download_file_array),1);
                                }
                                mwp_wpvivid_download_files_list.update_item(index, value);
                            }
                        });
                        if(mwp_wpvivid_download_files_list.wpvivid_download_file_array.length > 0) {
                            setTimeout(function() {
                                mwp_wpvivid_download_files_list.check_queue();
                            }, 3000);
                        }
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown){
                    mwp_wpvivid_download_files_list.wpvivid_download_lock_array.splice(jQuery.inArray(mwp_wpvivid_download_files_list.backup_id, mwp_wpvivid_download_files_list.wpvivid_download_file_array), 1);
                    setTimeout(function() {
                        mwp_wpvivid_download_files_list.check_queue();
                    }, 3000);
                });
            };

            mwp_wpvivid_download_files_list.update_item=function(index,file)
            {
                jQuery("[slug='"+index+"']").find('.mwp-wpvivid-download-status').html(file.html);
            };

            function mwp_wpvivid_init_download_page(backup_id,list_from){
                jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'download', list_from ]);
                var ajax_data = {
                    'action': 'mwp_wpvivid_init_download_page_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'backup_id': backup_id
                };
                jQuery('#mwp_wpvivid_files_list').html('');
                jQuery('#mwp_wpvivid_init_download_info').show();
                jQuery('#mwp_wpvivid_init_download_info').find('.spinner').addClass('is-active');
                var retry = '<input type="button" class="ui green mini button" value="Retry the initialization" onclick="mwp_wpvivid_init_download_page(\''+backup_id+'\', \''+list_from+'\');" />';
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    jQuery('#mwp_wpvivid_init_download_info').hide();
                    jQuery('#mwp_wpvivid_init_download_info').find('.spinner').removeClass('is-active');
                    try{
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            mwp_wpvivid_download_files_list.init(backup_id);
                            var need_check_queue = false;
                            jQuery.each(jsonarray.files, function (index, value)
                            {
                                if(value.status === 'running'){
                                    if(jQuery.inArray(index, mwp_wpvivid_download_files_list.wpvivid_download_file_array) === -1) {
                                        mwp_wpvivid_download_files_list.wpvivid_download_file_array.push(index);
                                        need_check_queue = true;
                                    }
                                }
                            });
                            if(need_check_queue) {
                                mwp_wpvivid_download_files_list.check_queue();
                            }
                            jQuery('#mwp_wpvivid_files_list').html(jsonarray.html);
                        }
                        else{
                            alert(jsonarray.error);
                            jQuery('#mwp_wpvivid_files_list').html(retry);
                        }
                    }
                    catch(err)
                    {
                        alert(err);
                        jQuery('#mwp_wpvivid_files_list').html(retry);
                    }

                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#mwp_wpvivid_init_download_info').hide();
                    jQuery('#mwp_wpvivid_init_download_info').find('.spinner').removeClass('is-active');
                    var error_message = mwp_wpvivid_output_ajaxerror('initializing download information', textStatus, errorThrown);
                    alert(error_message);
                    jQuery('#mwp_wpvivid_files_list').html(retry);
                });
            }

            jQuery('#mwp_wpvivid_backup_list').on('click', '.mwp-wpvivid-download', function() {
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                mwp_wpvivid_init_download_page(backup_id,'localhost_list');
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on('click', '.mwp-wpvivid-download', function() {
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                mwp_wpvivid_init_download_page(backup_id,'remote_list');
            });

            jQuery('#mwp_wpvivid_files_list').on('click', '.mwp-wpvivid-prepare-download', function(){
                var Obj=jQuery(this);
                var file_name=Obj.closest('tr').attr('slug');
                mwp_wpvivid_download_files_list.add_download_queue(file_name);
            });

            jQuery('#mwp_wpvivid_files_list').on('click', '.mwp-wpvivid-ready-download', function(){
                var Obj=jQuery(this);
                var file_name=Obj.closest('tr').attr('slug');
                var loc = 'admin-ajax.php?backup_id='+mwp_wpvivid_download_files_list.backup_id+'&file_name='+file_name+'&action=wpvivid_download_backup_mainwp';
                var url =  mwp_wpvivid_get_donwnloadlink(site_id, loc);
                window.open(url, '_blank');
            });

            function wpvivid_get_backup_addon_list(page=0){
                var backup_id = mwp_wpvivid_download_files_list.backup_id;
                if(page==0) {
                    page =jQuery('#mwp_wpvivid_files_list').find('.current-page').val();
                }
                var ajax_data = {
                    'action': 'mwp_wpvivid_get_backup_addon_list',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'backup_id': backup_id,
                    'page':page
                };
                mwp_wpvivid_post_request(ajax_data, function (data){
                    try{
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            jQuery('#mwp_wpvivid_files_list').html(jsonarray.html);
                        }
                        else if (jsonarray.result === 'failed') {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err) {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = mwp_wpvivid_output_ajaxerror('adding the remote storage', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            jQuery('#mwp_wpvivid_files_list').on("click",'.first-page',function() {
                wpvivid_get_backup_addon_list('first');
            });

            jQuery('#mwp_wpvivid_files_list').on("click",'.prev-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_get_backup_addon_list(page-1);
            });

            jQuery('#mwp_wpvivid_files_list').on("click",'.next-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_get_backup_addon_list(page+1);
            });

            jQuery('#mwp_wpvivid_files_list').on("click",'.last-page',function() {
                wpvivid_get_backup_addon_list('last');
            });

            jQuery('#mwp_wpvivid_files_list').on("keypress", '.current-page', function(){
                if(event.keyCode === 13){
                    var page = jQuery(this).val();
                    wpvivid_get_backup_addon_list(page);
                }
            });
        </script>
        <?php
    }

    public function output_log(){
        ?>
        <div class="postbox mwp-restore_log" id="mwp_wpvivid_read_log_content"></div>
        <script>
            function mwp_wpvivid_backup_open_log(log, list_from){
                var ajax_data = {
                    'action':'mwp_wpvivid_view_log_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'log': log
                };
                jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'log', list_from ]);
                mwp_wpvivid_post_request(ajax_data, function(data) {
                    jQuery('#mwp_wpvivid_read_log_content').html("");
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === "success")
                        {
                            var log_data = jsonarray.data;
                            while (log_data.indexOf('\n') >= 0)
                            {
                                var iLength = log_data.indexOf('\n');
                                var log = log_data.substring(0, iLength);
                                log_data = log_data.substring(iLength + 1);
                                var insert_log = "<div style=\"clear:both;\">" + log + "</div>";
                                jQuery('#mwp_wpvivid_read_log_content').append(insert_log);
                            }
                        }
                        else
                        {
                            jQuery('#mwp_wpvivid_read_log_content').html(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        alert(err);
                        var div = "Reading the log failed. Please try again.";
                        jQuery('#mwp_wpvivid_read_log_content').html(div);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = mwp_wpvivid_output_ajaxerror('export the previously-exported settings', textStatus, errorThrown);
                    jQuery('#mwp_wpvivid_read_log_content').html(error_message);
                });
            }

            jQuery('#mwp_wpvivid_backup_list').on('click', '.mwp-wpvivid-log', function() {
                var Obj=jQuery(this);
                var log=Obj.attr('name');
                mwp_wpvivid_backup_open_log(log,'localhost_list');
            });

            jQuery('#mwp_wpvivid_remote_backups_list').on('click', '.mwp-wpvivid-log', function() {
                var Obj=jQuery(this);
                var log=Obj.attr('name');
                mwp_wpvivid_backup_open_log(log,'remote_list');
            });
        </script>
        <?php
    }
}