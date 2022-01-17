<?php

if ( ! class_exists( 'WP_List_Table' ) )
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Mainwp_WPvivid_Schedule_List extends WP_List_Table
{
    public $page_num;
    public $schedule_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'schedule',
                'screen' => 'schedule',
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
        $columns['cb'] = __( 'cb', 'wpvivid' );
        $columns['wpvivid_status'] = __( 'Status', 'wpvivid' );
        $columns['wpvivid_backup_cycles'] =__( 'Backup Cycles', 'wpvivid'  );
        $columns['wpvivid_last_backup'] = __( 'Last Backup', 'wpvivid'  );
        $columns['wpvivid_next_backup'] = __( 'Next Backup', 'wpvivid'  );
        $columns['wpvivid_backup_type'] = __( 'Backup Type', 'wpvivid'  );
        $columns['wpvivid_storage'] = __( 'Storage', 'wpvivid'  );
        $columns['wpvivid_actions'] = __( 'Actions', 'wpvivid'  );
        return $columns;
    }

    public function set_schedule_list($schedule_list,$page_num=1)
    {
        $this->schedule_list=$schedule_list;
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

        $total_items =sizeof($this->schedule_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->schedule_list);
    }

    public function  column_cb( $schedule )
    {
        if ($schedule['status'] == 'Active')
        {
            echo '<input type="checkbox" checked/>';
        } else {
            echo '<input type="checkbox"/>';
        }
    }

    public function _column_wpvivid_status( $schedule )
    {
        echo '<td class="mwp-wpvivid-schedule-status">'.$schedule['status'].'</td>';
    }

    public function _column_wpvivid_backup_cycles( $schedule )
    {
        if (!isset($schedule['week']))
        {
            $schedule['week'] = 'N/A';
        }
        $schedule_type = $schedule['schedule_cycles'];
        /*$recurrence = wp_get_schedules();
        if (isset($recurrence[$schedule['type']]))
        {
            $schedule_type = $recurrence[$schedule['type']]['display'];
            if ($schedule_type === 'Weekly')
            {
                if (isset($schedule['week']))
                {
                    if ($schedule['week'] === 'sun')
                    {
                        $schedule_type = $schedule_type . '-Sunday';
                    } else if ($schedule['week'] === 'mon')
                    {
                        $schedule_type = $schedule_type . '-Monday';
                    } else if ($schedule['week'] === 'tue')
                    {
                        $schedule_type = $schedule_type . '-Tuesday';
                    } else if ($schedule['week'] === 'wed') {

                        $schedule_type = $schedule_type . '-Wednesday';
                    } else if ($schedule['week'] === 'thu')
                    {
                        $schedule_type = $schedule_type . '-Thursday';
                    } else if ($schedule['week'] === 'fri')
                    {
                        $schedule_type = $schedule_type . '-Friday';
                    } else if ($schedule['week'] === 'sat')
                    {
                        $schedule_type = $schedule_type . '-Saturday';
                    }
                }
            }
        } else {
            $schedule_type = 'not found';
        }*/

        echo '<td class="'.$schedule['type'].'">'.$schedule_type.'</td>';
    }

    public function _column_wpvivid_last_backup( $schedule )
    {
        /*if (isset($schedule['last_backup_time']))
        {
            //$offset=get_option('gmt_offset');
            //$localtime = $schedule['last_backup_time'] + $offset * 60 * 60;
            $last_backup_time = date("H:i:s - m/d/Y ", $schedule['last_backup_time']);
        } else {
            $last_backup_time = 'N/A';
        }*/
        $last_backup_time = $schedule['last_backup_time'];
        echo '<td>'.$last_backup_time.'</td>';
    }

    public function _column_wpvivid_next_backup( $schedule )
    {
        /*if ($schedule['status'] == 'Active')
        {
            $timestamp = wp_next_scheduled($schedule['id'], array($schedule['id']));
            $schedule['next_start'] = $timestamp;
            //$offset=get_option('gmt_offset');
            //$localtime = $schedule['next_start'] + $offset * 60 * 60;
            $next_start = date("H:i:s - m/d/Y ", $schedule['next_start']);
        } else {
            $next_start = 'N/A';
        }*/
        $next_start = $schedule['next_start_time'];
        echo '<td>'.$next_start.'</td>';
    }

    public function _column_wpvivid_backup_type( $schedule )
    {
        if (isset($schedule['backup']['backup_files']))
        {
            $backup_type = $schedule['backup']['backup_files'];
            if ($backup_type === 'files+db')
            {
                $backup_type = 'Database + Files (WordPress Files)';
            } else if ($backup_type === 'files')
            {
                $backup_type = 'WordPress Files (Exclude Database)';
            } else if ($backup_type === 'db')
            {
                $backup_type = 'Only Database';
            }
        } else {
            $backup_type = 'Custom';
        }

        echo '<td>'.$backup_type.'</td>';
    }

    public function _column_wpvivid_storage( $schedule )
    {
        if (isset($schedule['backup']['local']))
        {
            if ($schedule['backup']['local'] == '1')
            {
                $backup_to = 'Localhost';
            } else {
                $backup_to = 'Remote';
            }
        } else {
            $backup_to = 'Localhost';
        }
        echo '<td>'.$backup_to.'</td>';
    }

    public function _column_wpvivid_actions( $schedule )
    {
        echo '<td>
                    <div>
                         <img class="mwp-wpvivid-schedule-edit" src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . '/admin/images/Edit.png') . '"
                              style="vertical-align:middle; cursor:pointer;" title="Edit the schedule" name="'.esc_attr(json_encode($schedule)).'" />                    
                         <img class="mwp-wpvivid-schedule-delete" src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . '/admin/images/Delete.png') . '"
                              style="vertical-align:middle; cursor:pointer;" title="Delete the schedule" />                    
                     </div>
                </td>';
    }

    public function display_rows()
    {
        $this->_display_rows( $this->schedule_list );
    }

    private function _display_rows($schedule_list)
    {
        $page=$this->get_pagenum();

        $page_schedule_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_schedule_list = array_splice( $schedule_list, 0, 10);
            $count++;
        }
        foreach ( $page_schedule_list as $schedule)
        {
            $this->single_row($schedule);
        }
    }

    public function single_row($schedule)
    {
        if ($schedule['status'] == 'Active')
        {
            $class='schedule-item mwp-wpvivid-schedule-active';
        } else {
            $class='schedule-item';
        }
        ?>
        <tr class="<?php echo $class;?>" slug="<?php echo $schedule['id'];?>">
            <?php $this->single_row_columns( $schedule ); ?>
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

class Mainwp_WPvivid_Schedule_Global_List extends WP_List_Table
{
    public $page_num;
    public $schedule_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'schedule',
                'screen' => 'schedule',
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
        $timezone = '<div class="mwp-wpvivid-font-right-space" style="float: left;">Timezone</div>
                        <small>
                            <div class="mwp-wpvivid-tooltip" style="float: left; margin-top: 4px; line-height: 100%;">?
                                <div class="mwp-wpvivid-tooltiptext">The time zone which the backup will start.</div>
                            </div>
                        </small>
                        <div style="clear: both;"></div>';
        $columns = array();
        $columns['cb'] = __( 'cb', 'wpvivid' );
        $columns['wpvivid_status'] = __( 'Status', 'wpvivid' );
        $columns['wpvivid_backup_cycles'] =__( 'Backup Cycles', 'wpvivid'  );
        $columns['wpvivid_start_time'] = __( 'Start Time', 'wpvivid'  );
        $columns['wpvivid_start_local_utc'] = $timezone;//__( 'Timezone', 'wpvivid' );
        $columns['wpvivid_backup_type'] = __( 'Backup Type', 'wpvivid'  );
        $columns['wpvivid_storage'] = __( 'Storage', 'wpvivid'  );
        $columns['wpvivid_actions'] = __( 'Actions', 'wpvivid'  );
        return $columns;
    }

    public function set_schedule_list($schedule_list,$page_num=1)
    {
        $this->schedule_list=$schedule_list;
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

        $total_items =sizeof($this->schedule_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->schedule_list);
    }

    public function  column_cb( $schedule )
    {
        if ($schedule['status'] == 'Active')
        {
            echo '<input type="checkbox" checked/>';
        } else {
            echo '<input type="checkbox"/>';
        }
    }

    public function _column_wpvivid_status( $schedule )
    {
        echo '<td class="mwp-wpvivid-schedule-status">'.$schedule['status'].'</td>';
    }

    public function _column_wpvivid_backup_cycles( $schedule )
    {
        if (!isset($schedule['week']))
        {
            $schedule['week'] = 'N/A';
        }
        //$schedule_type = $schedule['schedule_cycles'];
        $schedule_type = $schedule['type'];
        switch ($schedule_type){
            case 'wpvivid_hourly':
                $schedule_type = 'Every hour';
                break;
            case 'wpvivid_2hours':
                $schedule_type = 'Every 2 hours';
                break;
            case 'wpvivid_4hours':
                $schedule_type = 'Every 4 hours';
                break;
            case 'wpvivid_8hours':
                $schedule_type = 'Every 8 hours';
                break;
            case 'wpvivid_12hours':
                $schedule_type = 'Every 12 hours';
                break;
            case 'wpvivid_daily':
                $schedule_type = 'Daily';
                break;
            case 'wpvivid_weekly':
                $schedule_type = 'Weekly';
                break;
            case 'wpvivid_fortnightly':
                $schedule_type = 'Fortnightly';
                break;
            case 'wpvivid_monthly':
                $schedule_type = 'Monthly';
                break;
            default:
                $schedule_type = 'not found';
                break;
        }
        if ($schedule_type === 'Weekly') {
            if (isset($schedule['week'])) {
                if ($schedule['week'] === 'sun') {
                    $schedule_type = $schedule_type . '-Sunday';
                } else if ($schedule['week'] === 'mon') {
                    $schedule_type = $schedule_type . '-Monday';
                } else if ($schedule['week'] === 'tue') {
                    $schedule_type = $schedule_type . '-Tuesday';
                } else if ($schedule['week'] === 'wed') {
                    $schedule_type = $schedule_type . '-Wednesday';
                } else if ($schedule['week'] === 'thu') {
                    $schedule_type = $schedule_type . '-Thursday';
                } else if ($schedule['week'] === 'fri') {
                    $schedule_type = $schedule_type . '-Friday';
                } else if ($schedule['week'] === 'sat') {
                    $schedule_type = $schedule_type . '-Saturday';
                }
            }
        }

        echo '<td class="'.$schedule['type'].'">'.$schedule_type.'</td>';
    }

    public function _column_wpvivid_start_time( $schedule ){
        echo '<td>'.$schedule['current_day'].'</td>';
    }

    public function _column_wpvivid_start_local_utc( $schedule ){
        if(isset($schedule['start_time_local_utc'])){
            $start_time_local_utc = $schedule['start_time_local_utc'];
            if($start_time_local_utc === 'local'){
                $start_time_local_utc = 'Local Time';
            }
            else{
                $start_time_local_utc = 'UTC Time';
            }
        }
        else{
            $start_time_local_utc = 'UTC Time';
        }
        echo '<td>'.$start_time_local_utc.'</td>';
    }

    public function _column_wpvivid_last_backup( $schedule )
    {
        if (isset($schedule['last_backup_time']))
        {
            $offset=get_option('gmt_offset');
            $localtime = $schedule['last_backup_time'] + $offset * 60 * 60;
            $last_backup_time = date("H:i:s - m/d/Y ", $schedule['last_backup_time']);
        } else {
            $last_backup_time = 'N/A';
        }
        //$last_backup_time = $schedule['last_backup_time'];
        echo '<td>'.$last_backup_time.'</td>';
    }

    public function _column_wpvivid_backup_type( $schedule )
    {
        if (isset($schedule['backup']['backup_files']))
        {
            $backup_type = $schedule['backup']['backup_files'];
            if ($backup_type === 'files+db')
            {
                $backup_type = 'Database + Files (WordPress Files)';
            } else if ($backup_type === 'files')
            {
                $backup_type = 'WordPress Files (Exclude Database)';
            } else if ($backup_type === 'db')
            {
                $backup_type = 'Only Database';
            }
        } else {
            $backup_type = 'Custom';
        }

        echo '<td>'.$backup_type.'</td>';
    }

    public function _column_wpvivid_storage( $schedule )
    {
        if (isset($schedule['backup']['local']))
        {
            if ($schedule['backup']['local'] == '1')
            {
                $backup_to = 'Localhost';
            } else {
                $backup_to = 'Remote';
            }
        } else {
            $backup_to = 'Localhost';
        }
        echo '<td>'.$backup_to.'</td>';
    }

    public function _column_wpvivid_actions( $schedule )
    {
        echo '<td>
                    <div>
                         <img class="mwp-wpvivid-schedule-edit" src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . '/admin/images/Edit.png') . '"
                              style="vertical-align:middle; cursor:pointer;" title="Edit the schedule" />                    
                         <img class="mwp-wpvivid-schedule-delete" src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . '/admin/images/Delete.png') . '"
                              style="vertical-align:middle; cursor:pointer;" title="Delete the schedule" />                    
                     </div>
                </td>';
    }

    public function display_rows()
    {
        $this->_display_rows( $this->schedule_list );
    }

    private function _display_rows($schedule_list)
    {
        $page=$this->get_pagenum();

        $page_schedule_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_schedule_list = array_splice( $schedule_list, 0, 10);
            $count++;
        }
        foreach ( $page_schedule_list as $schedule)
        {
            $this->single_row($schedule);
        }
    }

    public function single_row($schedule)
    {
        if ($schedule['status'] == 'Active')
        {
            $class='schedule-item mwp-wpvivid-schedule-active';
        } else {
            $class='schedule-item';
        }
        ?>
        <tr class="<?php echo $class;?>" slug="<?php echo $schedule['id'];?>">
            <?php $this->single_row_columns( $schedule ); ?>
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

class Mainwp_WPvivid_Schedule_Mould_List extends WP_List_Table
{
    public $page_num;
    public $schedule_mould_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'schedule_mould',
                'screen' => 'schedule_mould',
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

    public function set_schedule_mould_list($schedule_mould_list,$page_num=1)
    {
        $this->schedule_mould_list=$schedule_mould_list;
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

        $total_items =sizeof($this->schedule_mould_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->schedule_mould_list);
    }

    public function _column_wpvivid_mould_name( $schedule_mould )
    {
        echo '<td><div>'.$schedule_mould['mould_name'].'</div></td>';
    }

    public function _column_wpvivid_sync_mould( $schedule_mould )
    {
        echo '<td><input class="ui green mini button mwp-wpvivid-sync-schedule-mould" type="button" value="Sync" /></td>';
    }

    public function _column_wpvivid_actions( $schedule_mould )
    {
        echo '<td>
                    <div>
                         <img class="mwp-wpvivid-schedule-mould-edit" src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . '/admin/images/Edit.png') . '"
                              style="vertical-align:middle; cursor:pointer;" title="Edit the schedule" />                    
                         <img class="mwp-wpvivid-schedule-mould-delete" src="' . esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL . '/admin/images/Delete.png') . '"
                              style="vertical-align:middle; cursor:pointer;" title="Delete the schedule" />                    
                     </div>
                </td>';
    }

    public function display_rows()
    {
        $this->_display_rows( $this->schedule_mould_list );
    }

    private function _display_rows($schedule_mould_list)
    {
        $page=$this->get_pagenum();

        $page_schedule_mould_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_schedule_mould_list = array_splice( $schedule_mould_list, 0, 10);
            $count++;
        }
        foreach ( $page_schedule_mould_list as $mould_name => $schedule_mould)
        {
            $schedule_mould['mould_name'] = $mould_name;
            $this->single_row($schedule_mould);
        }
    }

    public function single_row($schedule_mould)
    {
        ?>
        <tr slug="<?php echo $schedule_mould['mould_name'];?>">
            <?php $this->single_row_columns( $schedule_mould ); ?>
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

class Mainwp_WPvivid_Extension_SchedulePage
{
    private $setting;
    private $setting_addon;
    private $global_custom_setting;
    private $time_zone;
    private $select_pro;
    private $site_id;
    public $main_tab;

    public function __construct($setting, $setting_addon=array(), $global_custom_setting=array(), $time_zone=0, $select_pro=0)
    {
        $this->setting=$setting;
        $this->setting_addon=$setting_addon;
        $this->global_custom_setting=$global_custom_setting;
        $this->select_pro=$select_pro;
        $this->time_zone=$time_zone;
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
            if(isset($_GET['mould_name'])){
                $mould_name = sanitize_text_field($_GET['mould_name']);
            }
            else{
                $mould_name = '';
            }
            if(isset($_GET['is_incremental']) && $_GET['is_incremental'] == 1){
                $is_incremental = 1;
            }
            else{
                $is_incremental = 0;
            }
            $this->mwp_wpvivid_synchronize_setting($check_addon, $mould_name, $is_incremental);
        }
        else
        {
            ?>
            <div style="padding: 10px;">
                <?php
                if($global){
                    if($this->select_pro){
                        $select_pro_check = 'checked';
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
                            <div style="float: left;"><input class="ui green mini button" type="button" value="Save" onclick="mwp_wpvivid_switch_pro_setting();" /></div>
                            <div style="clear: both;"></div>
                        </div>
                    </div>
                    <div style="clear: both;"></div>
                    <?php
                    if($this->select_pro){
                        Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_first_init_schedule_to_module();
                        $this->mwp_wpvivid_schedule_page_addon($global);
                    }
                    else{
                        $this->mwp_wpvivid_schedule_page($global);
                    }
                    ?>
                    <?php
                }
                else {
                    if ($check_pro) {
                        $this->mwp_wpvivid_schedule_page_addon($global);
                    } else {
                        $this->mwp_wpvivid_schedule_page($global);
                    }
                }
                ?>
            </div>
            <script>
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

                function mwp_wpvivid_swtich_global_schedule_tab(evt, contentName){
                    var i, tabcontent, tablinks;
                    tabcontent = document.getElementsByClassName("mwp-global-schedule-tab-content");
                    for (i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";
                    }
                    tablinks = document.getElementsByClassName("mwp-global-schedule-nav-tab");
                    for (i = 0; i < tablinks.length; i++) {
                        tablinks[i].className = tablinks[i].className.replace(" nav-tab-active", "");
                    }
                    document.getElementById(contentName).style.display = "block";
                    evt.currentTarget.className += " nav-tab-active";
                }
            </script>
            <?php
        }
    }

    public function mwp_wpvivid_schedule_page_addon($global){
        $incremental_backup = new Mainwp_WPvivid_Extension_Incremental_Backup();
        if(!$global){
            $incremental_backup->set_site_id($this->site_id);
            $incremental_backup_data=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_option($this->site_id, 'incremental_backup_setting', array());
            $incremental_backup->set_incremental_backup_data($incremental_backup_data);
        }
        else{
            $incremental_backup_data=Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('incremental_backup_setting', array());
            $incremental_backup->set_incremental_backup_data($incremental_backup_data);
        }
        $schedules = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_addon', array());

        add_action('mwp_wpvivid_schedule_add_edit', array($this, 'mwp_wpvivid_schedule_add_edit'), 10, 2);
        add_action('mwp_wpvivid_global_schedule_custom_backup_setting', array($this, 'mwp_wpvivid_global_schedule_custom_backup_setting'), 10);
        add_filter('mwp_wpvivid_schedule_backup_type_addon', array($this, 'mwp_wpvivid_schedule_backup_type_addon'), 10, 3);
        add_filter('mwp_wpvivid_schedule_local_remote_addon', array($this, 'mwp_wpvivid_schedule_local_remote_addon'), 10, 2);

        ?>
        <div>
            <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                <img src="<?php echo esc_url(MAINWP_WPVIVID_EXTENSION_PLUGIN_URL.'/admin/images/schedule.png'); ?>" style="width: 50px; height: 50px; " />
            </div>
            <div class="mwp-wpvivid-block-bottom-space" style="float: left;">
                <div>This tab allows you to create backup schedules, customize schedules for child sites with the following functions:</div>
                <div>1. Customize the start time and schedule cycles</div>
                <div>2. Customize what to backup</div>
                <div>3. Save the backups to localhost or remote storage</div>
                <div>Click 'Sync' to sync the schedules to child sites</div>
            </div>
            <div style="clear: both;"></div>
        </div>
        <?php

        if(!class_exists('Mainwp_WPvivid_Tab_Page_Container'))
            include_once MAINWP_WPVIVID_EXTENSION_PLUGIN_DIR . '/includes/wpvivid-backup-mainwp-tab-page-container.php';
        $this->main_tab=new Mainwp_WPvivid_Tab_Page_Container();

        $args['is_parent_tab']=0;
        $args['transparency']=1;

        $tabs['schedules']['title'] = 'Schedules';
        $tabs['schedules']['slug'] = 'schedules';
        $tabs['schedules']['callback'] = array($this, 'output_schedules_page');
        $tabs['schedules']['args'] = $args;

        $args['can_delete']=1;
        $args['hide']=1;
        $args['global']=$global;
        $tabs['schedules_edit']['title'] = 'Schedule Edit';
        $tabs['schedules_edit']['slug'] = 'schedules_edit';
        $tabs['schedules_edit']['callback'] = array($this, 'output_schedules_edit_page');
        $tabs['schedules_edit']['args'] = $args;
        $tabs=apply_filters('mwp_wpvivid_schedule_tabs',$tabs);
        foreach ($tabs as $key=>$tab)
        {
            $this->main_tab->add_tab($tab['title'],$tab['slug'],$tab['callback'], $tab['args']);
        }
        $this->main_tab->display();
        ?>
        <script>
            var is_global = '<?php echo $global; ?>';
            if(!is_global){
                mwp_wpvivid_get_schedules_addon();
            }
            function mwp_wpvivid_get_schedules_addon(){
                var ajax_data={
                    'action': 'mwp_wpvivid_get_schedules_addon',
                    'site_id':'<?php echo esc_html($this->site_id); ?>'
                };
                mwp_wpvivid_post_request(ajax_data, function (data)
                {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#mwp_wpvivid_schedule_list_addon').html(jsonarray.html);
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    setTimeout(function ()
                    {
                        mwp_wpvivid_get_schedules_addon();
                    }, 3000);
                });
            }

            var mwp_wpvivid_edit_schedule_id = '';
            function mwp_wpvivid_edit_schedule_ex(data){
                var jsonarray = jQuery.parseJSON(data);

                mwp_wpvivid_edit_schedule_id = jsonarray.id;
                jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'schedules_edit', 'schedules' ]);

                var cycles = jsonarray.type;
                jQuery("#mwp_wpvivid_schedule_update_cycles_select").val(cycles);
                jQuery('#mwp_wpvivid_schedule_update_week').hide();
                jQuery('#mwp_wpvivid_schedule_update_day').hide();
                if(cycles === 'wpvivid_weekly' || cycles === 'wpvivid_fortnightly')
                {
                    jQuery('#mwp_wpvivid_schedule_update_week').show();
                    jQuery('#mwp_wpvivid_schedule_update_week_select').val(jsonarray.week);
                }
                else if(cycles === 'wpvivid_monthly'){
                    jQuery('#mwp_wpvivid_schedule_update_day').show();
                    jQuery('#mwp_wpvivid_schedule_update_day_select').val(jsonarray.day);
                }

                jQuery('select[option=mwp_schedule_update][name=current_day_hour]').each(function() {
                    jQuery(this).val(jsonarray.hours);
                });
                jQuery('select[option=mwp_schedule_update][name=current_day_minute]').each(function(){
                    jQuery(this).val(jsonarray.minute);
                });

                jQuery('#mwp_wpvivid_schedule_update_utc_time').html(jsonarray.current_day);

                jQuery('#mwp_wpvivid_schedule_update_start_local_time').html(jsonarray.hours+':'+jsonarray.minute);
                jQuery('#mwp_wpvivid_schedule_update_start_utc_time').html(jsonarray.current_day);
                jQuery('#mwp_wpvivid_schedule_update_start_cycles').html(jsonarray.schedule_cycles);

                if(typeof jsonarray.backup.backup_files !== 'undefined') {
                    if (jsonarray.backup.backup_files === 'files+db') {
                        jQuery('input[option=mwp_schedule_update][name=mwp_schedule_update_backup_type][value=\'files+db\']').prop('checked', true);
                    }
                    else {
                        jQuery('input[option=mwp_schedule_update][name=mwp_schedule_update_backup_type][value=' + jsonarray.backup.backup_files + ']').prop('checked', true);
                    }
                    jQuery('#mwp_wpvivid_schedule_update_custom_module_part').hide();
                    mwp_wpvivid_popup_schedule_tour_addon('hide', 'schedule_update');
                }
                else{
                    jQuery('input[option=mwp_schedule_update][name=mwp_schedule_update_backup_type][value=custom]').prop('checked', true);
                    jQuery('#mwp_wpvivid_schedule_update_custom_module_part').show();
                    mwp_wpvivid_popup_schedule_tour_addon('show', 'schedule_update');
                    mwp_wpvivid_display_schedule_setting(jsonarray.backup);
                }

                var backup_to = jsonarray.backup.local === 1 ? 'local' : 'remote';
                jQuery('input:radio[option=mwp_schedule_update][name=mwp_schedule_update_save_local_remote][value='+backup_to+']').prop('checked', true);
            }

            function mwp_wpvivid_display_schedule_setting(backupinfo){
                var core_check = true;
                var database_check = true;
                var themes_plugins_check = true;
                var uploads_check = true;
                var content_check = true;
                var other_check = true;
                var additional_database = true;
                var custom_all_check = true;
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-database-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-themes-plugins-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-uploads-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-content-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-additional-folder-detail').css({'pointer-events': 'auto', 'opacity': '1'});
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-additional-database-detail').css({'pointer-events': 'auto', 'opacity': '1'});

                if(backupinfo.backup_select.core !== 1){
                    core_check = false;
                    custom_all_check = false;
                }
                if(backupinfo.backup_select.db !== 1){
                    database_check = false;
                    custom_all_check = false;
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-database-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                }
                if(backupinfo.backup_select.themes !== 1 && backupinfo.backup_select.plugin !== 1){
                    themes_plugins_check = false;
                    custom_all_check = false;
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-themes-plugins-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                }
                if(backupinfo.backup_select.uploads !== 1){
                    uploads_check = false;
                    custom_all_check = false;
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-uploads-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                }
                if(backupinfo.backup_select.content !== 1){
                    content_check = false;
                    custom_all_check = false;
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-content-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                }
                if(backupinfo.backup_select.other !== 1){
                    other_check = false;
                    custom_all_check = false;
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-additional-folder-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                }
                if(backupinfo.backup_select.additional_db !== 1){
                    additional_database = false;
                    custom_all_check = false;
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-additional-database-detail').css({'pointer-events': 'none', 'opacity': '0.4'});
                }
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-core-check').prop('checked', core_check);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-database-check').prop('checked', database_check);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-themes-plugins-check').prop('checked', themes_plugins_check);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-uploads-check').prop('checked', uploads_check);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-content-check').prop('checked', content_check);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-additional-folder-check').prop('checked', other_check);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-additional-database-check').prop('checked', additional_database);

                var exclude_uploads = '';
                var exclude_content = '';
                var include_other = '';
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-base-table-all-check').prop('checked', true);
                //jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-other-table-all-check').prop('checked', true);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-themes-all-check').prop('checked', true);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-plugins-all-check').prop('checked', true);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('input:checkbox[name=Database]').prop('checked', true);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('input:checkbox[name=Themes]').prop('checked', true);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('input:checkbox[name=Plugins]').prop('checked', true);
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-exclude-uploads-list').html('');
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-exclude-content-list').html('');
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-include-additional-folder-list').html('');
                jQuery.each(backupinfo.exclude_tables, function(index, value){
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('input:checkbox[name=Database][value='+value+']').prop('checked', false);
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-base-table-all-check').prop('checked', false);
                });
                jQuery.each(backupinfo.exclude_themes, function(index, value){
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('input:checkbox[name=Themes][value='+value+']').prop('checked', false);
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-themes-all-check').prop('checked', false);
                });
                jQuery.each(backupinfo.exclude_plugins, function(index, value){
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('input:checkbox[name=Plugins][value='+value+']').prop('checked', false);
                    jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-plugins-all-check').prop('checked', false);
                });
                jQuery.each(backupinfo.exclude_uploads, function(index, value){
                    exclude_uploads += "<ul>" +
                        "<li>" +
                        "<div class='mwp-"+value.type+"'></div>" +
                        "<div class='mwp-wpvivid-custom-li-font'>"+value.name+"</div>" +
                        "<div class='mwp-wpvivid-custom-li-close' onclick='mwp_wpvivid_remove_custom_tree(this);' title='Remove' style='cursor: pointer;'>X</div>" +
                        "</li>" +
                        "</ul>";
                });
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-exclude-uploads-list').append(exclude_uploads);
                jQuery.each(backupinfo.exclude_content, function(index, value){
                    exclude_content += "<ul>" +
                        "<li>" +
                        "<div class='mwp-"+value.type+"'></div>" +
                        "<div class='mwp-wpvivid-custom-li-font'>"+value.name+"</div>" +
                        "<div class='mwp-wpvivid-custom-li-close' onclick='mwp_wpvivid_remove_custom_tree(this);' title='Remove' style='cursor: pointer;'>X</div>" +
                        "</li>" +
                        "</ul>";
                });
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-exclude-content-list').append(exclude_content);
                jQuery.each(backupinfo.custom_other_root, function(index ,value){
                    include_other += "<ul>" +
                        "<li>" +
                        "<div class='mwp-"+value.type+"'></div>" +
                        "<div class='mwp-wpvivid-custom-li-font'>"+value.name+"</div>" +
                        "<div class='mwp-wpvivid-custom-li-close' onclick='mwp_wpvivid_remove_custom_tree(this);' title='Remove' style='cursor: pointer;'>X</div>" +
                        "</li>" +
                        "</ul>";
                });
                jQuery('#mwp_wpvivid_schedule_update_custom_module').find('.mwp-wpvivid-custom-include-additional-folder-list').append(include_other);
            }

            function mwp_wpvivid_delete_schedule(schedule_id){
                var ajax_data = {
                    'action': 'mwp_wpvivid_delete_schedule_addon',
                    'site_id': '<?php echo esc_html($this->site_id); ?>',
                    'schedule_id': schedule_id
                };
                jQuery('#mwp_wpvivid_schedule_update_notice').html('');
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            jQuery('#mwp_wpvivid_schedule_update_notice').html(jsonarray.notice);
                            jQuery('#mwp_wpvivid_schedule_list_addon').html(jsonarray.html);
                        }
                        else {
                            jQuery('#mwp_wpvivid_schedule_update_notice').html(jsonarray.notice);
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

            jQuery('#mwp_wpvivid_schedule_list_addon').on('click', '.mwp-wpvivid-schedule-edit', function(){
                var Obj = jQuery(this);
                var id = Obj.closest('tr').attr('slug');
                var name = jQuery(this).attr('name');
                mwp_wpvivid_edit_schedule_ex(name);
            });

            jQuery('#mwp_wpvivid_schedule_list_addon').on('click', '.mwp-wpvivid-schedule-delete', function(){
                var descript = 'Are you sure to remove this schedule?';
                var ret = confirm(descript);
                if(ret === true) {
                    var Obj = jQuery(this);
                    var id = Obj.closest('tr').attr('slug');
                    mwp_wpvivid_delete_schedule(id);
                }
            });

            jQuery('#mwp_wpvivid_schedule_list_addon').on('change', '.schedule-item > .check-column > input', function(){
                if( jQuery(this).is(':checked') )
                {
                    var Obj=jQuery(this).closest('tr');
                    Obj.addClass('mwp-wpvivid-schedule-active');
                    Obj.find('.mwp-wpvivid-schedule-status').html('Active');
                }
                else
                {
                    var Obj=jQuery(this).closest('tr');
                    Obj.removeClass('mwp-wpvivid-schedule-active');
                    Obj.find('.mwp-wpvivid-schedule-status').html('InActive');
                }
            });

            jQuery('#mwp_wpvivid_schedule_list_addon').on('change' ,'thead .check-column input',function() {
                if( jQuery(this).is(':checked') )
                {
                    jQuery('#mwp_wpvivid_schedule_list_addon').find('.schedule-item > .check-column > input').each(function()
                    {
                        var Obj=jQuery(this).closest('tr');
                        Obj.addClass('mwp-wpvivid-schedule-active');
                        Obj.find('.mwp-wpvivid-schedule-status').html('Active');
                    });
                }
                else
                {
                    jQuery('#mwp_wpvivid_schedule_list_addon').find('.schedule-item > .check-column > input').each(function()
                    {
                        var Obj=jQuery(this).closest('tr');
                        Obj.removeClass('mwp-wpvivid-schedule-active');
                        Obj.find('.mwp-wpvivid-schedule-status').html('InActive');
                    });
                }
            });

            jQuery('#mwp_wpvivid_schedule_list_addon').on('change' ,'tfoot .check-column input',function() {
                if( jQuery(this).is(':checked') )
                {
                    jQuery('#mwp_wpvivid_schedule_list_addon').find('.schedule-item > .check-column > input').each(function()
                    {
                        var Obj=jQuery(this).closest('tr');
                        Obj.addClass('mwp-wpvivid-schedule-active');
                        Obj.find('.mwp-wpvivid-schedule-status').html('Active');
                    });
                }
                else
                {
                    jQuery('#mwp_wpvivid_schedule_list_addon').find('.schedule-item > .check-column > input').each(function()
                    {
                        var Obj=jQuery(this).closest('tr');
                        Obj.removeClass('mwp-wpvivid-schedule-active');
                        Obj.find('.mwp-wpvivid-schedule-status').html('InActive');
                    });
                }
            });

            var mwp_wpvivid_global_edit_schedule_id = '';
            var mwp_wpvivid_global_edit_schedule_mould_name = '';
            function mwp_wpvivid_global_edit_schedule(schedule_id){
                var mould_name = jQuery('#mwp_wpvivid_schedule_mould_name').val();
                mwp_wpvivid_global_edit_schedule_id = schedule_id;
                mwp_wpvivid_global_edit_schedule_mould_name = mould_name;
                var ajax_data = {
                    'action': 'mwp_wpvivid_edit_global_schedule_addon',
                    'schedule_id': schedule_id,
                    'mould_name': mould_name
                };
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            jQuery('#mwp_wpvivid_tab_schedule_edit').show();
                            jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'schedules_edit', 'schedules' ]);

                            var arr = new Array();
                            arr = jsonarray.schedule_info.current_day.split(':');

                            jQuery('select[option=mwp_schedule_update][name=current_day_hour]').each(function()
                            {
                                jQuery(this).val(arr[0]);
                            });
                            jQuery('select[option=mwp_schedule_update][name=current_day_minute]').each(function(){
                                jQuery(this).val(arr[1]);
                            });

                            if(jsonarray.schedule_info.start_time_local_utc === 'local') {
                                jQuery('#mwp_wpvivid_schedule_update_start_timezone').val('local');
                            }
                            else{
                                jQuery('#mwp_wpvivid_schedule_update_start_timezone').val('utc');
                            }

                            if(jsonarray.schedule_info.type === 'wpvivid_daily')
                            {
                                jQuery('#mwp_wpvivid_schedule_update_cycles_select').val('wpvivid_daily');
                            }
                            else if(jsonarray.schedule_info.type === 'wpvivid_weekly')
                            {
                                jQuery('#mwp_wpvivid_schedule_update_week').show();
                                jQuery('#mwp_wpvivid_schedule_update_cycles_select').val('wpvivid_weekly');
                                jQuery('#mwp_wpvivid_schedule_update_week_select').val(jsonarray.schedule_info.week);
                            }
                            else if(jsonarray.schedule_info.type === 'wpvivid_fortnightly')
                            {
                                jQuery('#mwp_wpvivid_schedule_update_week').show();
                                jQuery('#mwp_wpvivid_schedule_update_cycles_select').val('wpvivid_fortnightly');
                                jQuery('#mwp_wpvivid_schedule_update_week_select').val(jsonarray.schedule_info.week);
                            }
                            else if(jsonarray.schedule_info.type === 'wpvivid_monthly')
                            {
                                jQuery('#mwp_wpvivid_schedule_update_day').show();
                                jQuery('#mwp_wpvivid_schedule_update_cycles_select').val('wpvivid_monthly');
                                jQuery('#mwp_wpvivid_schedule_update_day_select').val(jsonarray.schedule_info.day);
                            }
                            else{
                                jQuery('#mwp_wpvivid_schedule_update_cycles_select').val(jsonarray.schedule_info.type);
                            }

                            jQuery('#mwp_wpvivid_schedule_update_week').hide();
                            jQuery('#mwp_wpvivid_schedule_update_day').hide();
                            var select_value = jQuery('#mwp_wpvivid_schedule_update_cycles_select').val();
                            if(select_value === 'wpvivid_weekly' || select_value === 'wpvivid_fortnightly')
                            {
                                jQuery('#mwp_wpvivid_schedule_update_week').show();
                            }
                            else if(select_value === 'wpvivid_monthly'){
                                jQuery('#mwp_wpvivid_schedule_update_day').show();
                            }

                            jQuery('#mwp_wpvivid_schedule_update_start_local_time').html(jsonarray.schedule_info.current_day);
                            jQuery('#mwp_wpvivid_schedule_update_start_utc_time').html(jsonarray.schedule_info.current_day);
                            var backup_cycles = jQuery("#mwp_wpvivid_schedule_update_cycles_select option:selected").text();
                            jQuery('#mwp_wpvivid_schedule_update_start_cycles').html(backup_cycles);

                            if(typeof jsonarray.schedule_info.backup.backup_files !== 'undefined') {
                                if (jsonarray.schedule_info.backup.backup_files == 'files+db') {
                                    jQuery('input[option=mwp_schedule_update][name=mwp_schedule_update_backup_type][value=\'files+db\']').prop('checked', true);
                                }
                                else {
                                    jQuery('input[option=mwp_schedule_update][name=mwp_schedule_update_backup_type][value=' + jsonarray.schedule_info.backup.backup_files + ']').prop('checked', true);
                                }
                                jQuery('#mwp_wpvivid_schedule_update_custom_module_part').hide();
                                mwp_wpvivid_popup_schedule_tour_addon('hide', 'schedule_update');
                            }
                            else{
                                jQuery('input[option=mwp_schedule_update][name=mwp_schedule_update_backup_type][value=custom]').prop('checked', true);
                                jQuery('#mwp_wpvivid_schedule_update_custom_module_part').show();
                                mwp_wpvivid_popup_schedule_tour_addon('show', 'schedule_update');
                                mwp_wpvivid_display_schedule_setting(jsonarray.schedule_info.backup);
                            }

                            jQuery('#mwp_wpvivid_schedule_update_custom_module_part').find('.mwp-wpvivid-uploads-extension').val(jsonarray.schedule_info.backup.upload_extension);
                            jQuery('#mwp_wpvivid_schedule_update_custom_module_part').find('.mwp-wpvivid-content-extension').val(jsonarray.schedule_info.backup.content_extension);

                            var backup_to = jsonarray.schedule_info.backup.local === 1 ? 'local' : 'remote';
                            jQuery('input:radio[option=mwp_schedule_update][name=mwp_schedule_update_save_local_remote][value='+backup_to+']').prop('checked', true);
                            jQuery('#mwp_wpvivid_schedule_update_utc_time').html(jsonarray.schedule_info.current_day);
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

            function mwp_wpvivid_global_delete_schedule(schedule_id){
                var mould_name = jQuery('#mwp_wpvivid_schedule_mould_name').val();
                var ajax_data = {
                    'action': 'mwp_wpvivid_global_delete_schedule_addon',
                    'schedule_id': schedule_id,
                    'mould_name': mould_name
                };
                jQuery('#mwp_wpvivid_schedule_update_notice').html('');
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            jQuery('#mwp_wpvivid_schedule_update_notice').html(jsonarray.notice);
                            jQuery('#mwp_wpvivid_global_schedule_list_addon').html(jsonarray.html);
                        }
                        else {
                            jQuery('#mwp_wpvivid_schedule_update_notice').html(jsonarray.notice);
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

            jQuery('#mwp_wpvivid_global_schedule_list_addon').on('click', '.mwp-wpvivid-schedule-edit', function(){
                var Obj=jQuery(this);
                var id=Obj.closest('tr').attr('slug');
                mwp_wpvivid_global_edit_schedule(id);
            });

            jQuery('#mwp_wpvivid_global_schedule_list_addon').on('click', '.mwp-wpvivid-schedule-delete', function(){
                var descript = 'Are you sure to remove this schedule?';
                var ret = confirm(descript);
                if(ret === true) {
                    var Obj = jQuery(this);
                    var id = Obj.closest('tr').attr('slug');
                    mwp_wpvivid_global_delete_schedule(id);
                }
            });

            jQuery('#mwp_wpvivid_global_schedule_list_addon').on('change', '.schedule-item > .check-column > input', function(){
                if( jQuery(this).is(':checked') )
                {
                    var Obj=jQuery(this).closest('tr');
                    Obj.addClass('mwp-wpvivid-schedule-active');
                    Obj.find('.mwp-wpvivid-schedule-status').html('Active');
                }
                else
                {
                    var Obj=jQuery(this).closest('tr');
                    Obj.removeClass('mwp-wpvivid-schedule-active');
                    Obj.find('.mwp-wpvivid-schedule-status').html('InActive');
                }
            });

            jQuery('#mwp_wpvivid_global_schedule_list_addon').on('change', 'thead .check-column input', function(){
                if( jQuery(this).is(':checked') )
                {
                    jQuery('#mwp_wpvivid_global_schedule_list_addon').find('.schedule-item > .check-column > input').each(function()
                    {
                        var Obj=jQuery(this).closest('tr');
                        Obj.addClass('mwp-wpvivid-schedule-active');
                        Obj.find('.mwp-wpvivid-schedule-status').html('Active');
                    });
                }
                else
                {
                    jQuery('#mwp_wpvivid_global_schedule_list_addon').find('.schedule-item > .check-column > input').each(function()
                    {
                        var Obj=jQuery(this).closest('tr');
                        Obj.removeClass('mwp-wpvivid-schedule-active');
                        Obj.find('.mwp-wpvivid-schedule-status').html('InActive');
                    });
                }
            });

            jQuery('#mwp_wpvivid_global_schedule_list_addon').on('change' ,'tfoot .check-column input',function() {
                if( jQuery(this).is(':checked') )
                {
                    jQuery('#mwp_wpvivid_global_schedule_list_addon').find('.schedule-item > .check-column > input').each(function()
                    {
                        var Obj=jQuery(this).closest('tr');
                        Obj.addClass('mwp-wpvivid-schedule-active');
                        Obj.find('.mwp-wpvivid-schedule-status').html('Active');
                    });
                }
                else
                {
                    jQuery('#mwp_wpvivid_global_schedule_list_addon').find('.schedule-item > .check-column > input').each(function()
                    {
                        var Obj=jQuery(this).closest('tr');
                        Obj.removeClass('mwp-wpvivid-schedule-active');
                        Obj.find('.mwp-wpvivid-schedule-status').html('InActive');
                    });
                }
            });

            jQuery('#mwp_wpvivid_global_schedule_save_addon').click(function(){
                mwp_wpvivid_global_schedule_save_addon();
            });

            jQuery('#mwp_wpvivid_schedule_save_addon').click(function(){
                mwp_wpvivid_schedule_save_addon();
            });

            function mwp_wpvivid_global_schedule_save_addon() {
                var json={};
                var schedule_id = '';
                var schedule_status = '';
                var need_update = false;

                jQuery('#mwp_wpvivid_global_schedule_list_addon tbody').find('tr').each(function(){
                    if(!jQuery(this).hasClass('no-items')) {
                        need_update = true;
                        schedule_id = jQuery(this).attr('slug');
                        if (jQuery(this).children().children().prop('checked')) {
                            schedule_status = 'Active';
                        }
                        else {
                            schedule_status = 'InActive';
                        }
                        json[schedule_id] = schedule_status;
                    }
                });
                schedule_status = JSON.stringify(json);

                var ajax_data= {
                    'action': 'mwp_wpvivid_global_save_schedule_status_addon',
                    'schedule_data': schedule_status
                };
                jQuery('#mwp_wpvivid_schedule_update_notice').html('');
                mwp_wpvivid_post_request(ajax_data, function(data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            window.location.href = window.location.href + "&synchronize=1&addon=1";
                        }
                        else {
                            jQuery('#mwp_wpvivid_schedule_update_notice').html(jsonarray.notice);
                        }
                    }
                    catch (err) {
                        alert(err);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown) {
                    var error_message = mwp_wpvivid_output_ajaxerror('setting up a lock for the backup', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function mwp_wpvivid_schedule_save_addon() {
                var json={};
                var schedule_id = '';
                var schedule_status = '';
                var need_update = false;

                jQuery('#mwp_wpvivid_schedule_list_addon tbody').find('tr').each(function(){
                    if(!jQuery(this).hasClass('no-items')) {
                        need_update = true;
                        schedule_id = jQuery(this).attr('slug');
                        if (jQuery(this).children().children().prop('checked')) {
                            schedule_status = 'Active';
                        }
                        else {
                            schedule_status = 'InActive';
                        }
                        json[schedule_id] = schedule_status;
                    }
                });
                schedule_status = JSON.stringify(json);

                if(need_update === true){
                    var ajax_data= {
                        'action': 'mwp_wpvivid_save_schedule_status_addon',
                        'schedule_data': schedule_status,
                        'site_id': '<?php echo esc_html($this->site_id); ?>'
                    };
                    jQuery('#mwp_wpvivid_schedule_update_notice').html('');
                    mwp_wpvivid_post_request(ajax_data, function(data) {
                        try {
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success') {
                                jQuery('#mwp_wpvivid_schedule_update_notice').html(jsonarray.notice);
                                jQuery('#mwp_wpvivid_schedule_list_addon').html(jsonarray.html);
                            }
                            else {
                                jQuery('#mwp_wpvivid_schedule_update_notice').html(jsonarray.notice);
                            }
                        }
                        catch (err) {
                            alert(err);
                        }
                    }, function(XMLHttpRequest, textStatus, errorThrown) {
                        var error_message = mwp_wpvivid_output_ajaxerror('setting up a lock for the backup', textStatus, errorThrown);
                        alert(error_message);
                    });
                }
            }

            function mwp_wpvivid_start_sync_schedule(){
                mwp_wpvivid_global_schedule_save_addon();
            }
        </script>
        <?php
    }

    public function output_schedules_page($global){
        ?>
        <div style="margin-top: 10px;">
            <?php
            if($global){
                ?>
                <div class="mwp-wpvivid-block-bottom-space" id="mwp_wpvivid_schedule_mould_part_1">
                    <div class="mwp-wpvivid-block-bottom-space" id="mwp_wpvivid_schedule_mould_list_addon">
                        <?php
                        $schedule_mould_list = Mainwp_WPvivid_Extension_DB_Option::get_instance()->wpvivid_get_global_option('schedule_mould_addon', array());
                        if(empty($schedule_mould_list)){
                            $schedule_mould_list = array();
                        }
                        $table = new Mainwp_WPvivid_Schedule_Mould_List();
                        $table->set_schedule_mould_list($schedule_mould_list);
                        $table->prepare_items();
                        $table->display();
                        ?>
                    </div>
                    <div>
                        <input class="ui green mini button" type="button" value="<?php esc_attr_e('Create New Schedule Mould'); ?>" onclick="mwp_wpvivid_create_new_schedule_mould();" />
                    </div>
                </div>
                <div id="mwp_wpvivid_schedule_mould_part_2" style="display: none;">
                    <div class="mwp-wpvivid-block-bottom-space">
                        <span>Input a name:</span>
                        <input id="mwp_wpvivid_schedule_mould_name" />
                    </div>
                    <?php
                    $type='mwp_schedule_add';
                    do_action('mwp_wpvivid_schedule_add_edit', $type, $global);
                    ?>
                    <div id="mwp_wpvivid_schedule_update_notice"></div>
                    <div style="width: 100%; border: 1px solid #e5e5e5; float: left; box-sizing: border-box; margin-bottom: 10px; padding: 10px;">
                        <div class="mwp-wpvivid-block-bottom-space"><strong>Tips: </strong>Selected schedules will be executed sequentially. When there is a conflict of starting times for scheduled tasks, only one will be executed properly.</div>
                        <?php
                        $schedules = $this->setting_addon;
                        $schedules_list = array();
                        ?>
                        <div id="mwp_wpvivid_global_schedule_list_addon">
                            <?php
                            $table=new Mainwp_WPvivid_Schedule_Global_List();
                            $table->set_schedule_list($schedules_list);
                            $table->prepare_items();
                            $table->display();
                            ?>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                    <div>
                        <input class="ui green mini button" onclick="mwp_wpvivid_back_schedule_mould();" type="button" value="<?php esc_attr_e('Back to Mould List'); ?>" />
                    </div>
                </div>
                <?php
            }
            else{
                $type='mwp_schedule_add';
                do_action('mwp_wpvivid_schedule_add_edit', $type, $global);
                ?>
                <div id="mwp_wpvivid_schedule_update_notice"></div>
                <div style="width: 100%; border: 1px solid #e5e5e5; float: left; box-sizing: border-box; margin-bottom: 10px; padding: 10px;">
                    <div class="mwp-wpvivid-block-bottom-space"><strong>Tips: </strong>Selected schedules will be executed sequentially. When there is a conflict of starting times for scheduled tasks, only one will be executed properly.</div>
                    <div id="mwp_wpvivid_schedule_list_addon"></div>
                    <?php
                    if($global===false){
                        ?>
                        <div style="margin-top: 10px; float: left;">
                            <?php if($global===false)
                            {
                                $save_change_id= 'mwp_wpvivid_schedule_save_addon';
                                ?>
                                <input class="ui green mini button" id="<?php esc_attr_e($save_change_id); ?>" type="button" value="Save Changes" />
                                <?php
                            }
                            else
                            {
                                $save_change_id= 'mwp_wpvivid_global_schedule_save_addon';
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                    <div style="clear: both;"></div>
                </div>
                <?php
            }
            ?>
        </div>
        <script>
            function mwp_wpvivid_create_new_schedule_mould()
            {
                jQuery('#mwp_wpvivid_schedule_mould_part_1').hide();
                jQuery('#mwp_wpvivid_schedule_mould_part_2').show();
            }

            function mwp_wpvivid_back_schedule_mould()
            {
                window.location.href = window.location.href;
            }

            jQuery('#mwp_wpvivid_schedule_mould_list_addon').on('click', '.mwp-wpvivid-sync-schedule-mould', function(){
                var Obj=jQuery(this);
                var mould_name=Obj.closest('tr').attr('slug');
                window.location.href = window.location.href + "&synchronize=1&addon=1&mould_name=" + mould_name;
            });

            jQuery('#mwp_wpvivid_schedule_mould_list_addon').on('click', '.mwp-wpvivid-schedule-mould-edit', function(){
                jQuery('#mwp_wpvivid_schedule_mould_part_1').hide();
                jQuery('#mwp_wpvivid_schedule_mould_part_2').show();
                var Obj=jQuery(this);
                var mould_name=Obj.closest('tr').attr('slug');
                mwp_wpvivid_edit_schedule_mould(mould_name);
            });

            jQuery('#mwp_wpvivid_schedule_mould_list_addon').on('click', '.mwp-wpvivid-schedule-mould-delete', function(){
                var descript = 'Are you sure to remove this schedule mould?';
                var ret = confirm(descript);
                if(ret === true) {
                    var Obj = jQuery(this);
                    var mould_name = Obj.closest('tr').attr('slug');
                    mwp_wpvivid_delete_schedule_mould(mould_name);
                }
            });

            function mwp_wpvivid_edit_schedule_mould(mould_name)
            {
                jQuery('#mwp_wpvivid_schedule_mould_name').val(mould_name);
                jQuery('#mwp_wpvivid_schedule_mould_name').attr('disabled', 'disabled');
                first_create = '0';
                var ajax_data = {
                    'action': 'mwp_wpvivid_edit_global_schedule_mould_addon',
                    'mould_name': mould_name
                };
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            jQuery('#mwp_wpvivid_global_schedule_list_addon').html(jsonarray.html);
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

            function mwp_wpvivid_delete_schedule_mould(mould_name)
            {
                var ajax_data = {
                    'action': 'mwp_wpvivid_delete_global_schedule_mould_addon',
                    'mould_name': mould_name
                };
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            jQuery('#mwp_wpvivid_schedule_mould_list_addon').html(jsonarray.html);
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

            jQuery('#mwp_wpvivid_schedule_mould_list_addon').on("click",'.first-page',function() {
                mwp_wpvivid_get_schedule_mould_list('first');
            });

            jQuery('#mwp_wpvivid_schedule_mould_list_addon').on("click",'.prev-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                mwp_wpvivid_get_schedule_mould_list(page-1);
            });

            jQuery('#mwp_wpvivid_schedule_mould_list_addon').on("click",'.next-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                mwp_wpvivid_get_schedule_mould_list(page+1);
            });

            jQuery('#mwp_wpvivid_schedule_mould_list_addon').on("click",'.last-page',function() {
                mwp_wpvivid_get_schedule_mould_list('last');
            });

            jQuery('#mwp_wpvivid_schedule_mould_list_addon').on("keypress", '.current-page', function(){
                if(event.keyCode === 13){
                    var page = jQuery(this).val();
                    mwp_wpvivid_get_schedule_mould_list(page);
                }
            });

            function mwp_wpvivid_get_schedule_mould_list(page=0) {
                if(page === 0){
                    var current_page = jQuery('#mwp_wpvivid_schedule_mould_list_addon').find('.current-page').val();
                    if(typeof current_page !== 'undefined') {
                        page = jQuery('#mwp_wpvivid_schedule_mould_list_addon').find('.current-page').val();
                    }
                }
                var ajax_data = {
                    'action': 'mwp_wpvivid_get_schedule_mould_list',
                    'page':page
                };
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    jQuery('#mwp_wpvivid_schedule_mould_list_addon').html('');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#mwp_wpvivid_schedule_mould_list_addon').html(jsonarray.schedule_mould_list);
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
                        mwp_wpvivid_get_schedule_mould_list();
                    }, 3000);
                });
            }
        </script>
        <?php
    }

    public function output_schedules_edit_page($global){
        ?>
        <div style="margin-top: 10px;">
            <?php
            $type='mwp_schedule_update';
            do_action('mwp_wpvivid_schedule_add_edit', $type, $global);
            ?>
        </div>
        <?php
    }

    public function mwp_wpvivid_global_schedule_custom_backup_setting($type){
        $pop_class = 'mwp-wpvivid-custom-popup';
        $pop_text_class = 'mwp-wpvivid-custom-popuptext';
        $pop_style = 'display: none;';

        ?>
        <div class="<?php esc_attr_e($pop_class); ?>" id="mwp_wpvivid_<?php esc_attr_e($type); ?>_custom_module_part" style="<?php esc_attr_e($pop_style); ?>">
            <div class="<?php esc_attr_e($pop_text_class); ?>" id="mwp_wpvivid_<?php esc_attr_e($type); ?>_custom_module" style="padding-top: 0;">
                <?php
                $custom_staging_list = new Mainwp_WPvivid_Global_Schedule_Custom_Backup_List($this->global_custom_setting);
                $custom_staging_list ->set_parent_id('mwp_wpvivid_'.$type.'_custom_module');
                $custom_staging_list ->display_rows();
                $custom_staging_list ->load_js();
                ?>
            </div>
        </div>
        <?php
    }

    public function mwp_wpvivid_schedule_page($global){
        ?>
        <table class="widefat">
            <tbody>
            <?php
            add_filter('mwp_wpvivid_schedule_backup_type',array($this,'mwp_wpvivid_schedule_backup_type'));
            add_filter('mwp_wpvivid_schedule_notice',array($this,'mwp_wpvivid_schedule_notice'),10);
            add_filter('mwp_wpvivid_schedule_local_remote', array( $this, 'mwp_wpvivid_schedule_local_remote' ), 10);
            add_action('mwp_wpvivid_schedule_do_js',array( $this, 'mwp_wpvivid_schedule_do_js' ),10);

            $this->mwp_wpvivid_schedule_settings();
            ?>
            <tfoot>
            <tr>
                <?php if($global===false)
                {
                    $save_change_id= 'mwp_wpvivid_schedule_save';
                }
                else
                {
                    $save_change_id= 'mwp_wpvivid_global_schedule_save';
                }
                ?>
                <th class="row-title"><input class="ui green mini button" id="<?php esc_attr_e($save_change_id); ?>" type="button" value="Save Changes" /></th>
                <th></th>
            </tr>
            </tfoot>
            </tbody>
        </table>
        <script>
            function mwp_wpvivid_global_schedule_save()
            {
                var setting_data = mwp_wpvivid_ajax_data_transfer('mwp-schedule');
                var ajax_data = {
                    'action': 'mwp_wpvivid_set_global_schedule',
                    'schedule': setting_data,
                };
                jQuery('#mwp_wpvivid_global_schedule_save').css({'pointer-events': 'none', 'opacity': '0.4'});
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);

                        jQuery('#mwp_wpvivid_global_schedule_save').css({'pointer-events': 'auto', 'opacity': '1'});
                        if (jsonarray.result === 'success') {
                            window.location.href = window.location.href + "&synchronize=1&addon=0";
                        }
                        else {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err) {
                        alert(err);
                        jQuery('#mwp_wpvivid_global_schedule_save').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    jQuery('#mwp_wpvivid_global_schedule_save').css({'pointer-events': 'auto', 'opacity': '1'});
                    var error_message = mwp_wpvivid_output_ajaxerror('changing base settings', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function mwp_wpvivid_schedule_save()
            {
                var setting_data = mwp_wpvivid_ajax_data_transfer('mwp-schedule');
                var ajax_data = {
                    'action': 'mwp_wpvivid_set_schedule',
                    'schedule': setting_data,
                    'site_id': '<?php echo esc_html($this->site_id); ?>'
                };
                jQuery('#mwp_wpvivid_schedule_save').css({'pointer-events': 'none', 'opacity': '0.4'});
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);

                        jQuery('#mwp_wpvivid_schedule_save').css({'pointer-events': 'auto', 'opacity': '1'});
                        if (jsonarray.result === 'success') {
                            location.reload();
                        }
                        else {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err) {
                        alert(err);
                        jQuery('#mwp_wpvivid_schedule_save').css({'pointer-events': 'auto', 'opacity': '1'});
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    jQuery('#mwp_wpvivid_schedule_save').css({'pointer-events': 'auto', 'opacity': '1'});
                    var error_message = mwp_wpvivid_output_ajaxerror('changing base settings', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            jQuery('#mwp_wpvivid_global_schedule_save').click(function(){
                mwp_wpvivid_global_schedule_save();
            });
            jQuery('#mwp_wpvivid_schedule_save').click(function(){
                mwp_wpvivid_schedule_save();
            });
        </script>
        <?php
    }

    public function mwp_wpvivid_schedule_backup_type_addon($html, $type, $global){
        if(!$global){
            $custom = '<div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                            <label>
                                <input type="radio" option="'.$type.'" name="'.$type.'_backup_type" value="custom" onclick="mwp_wpvivid_click_schedule_type(\''.$type.'\', this);" />
                                <span>Custom</span>
                            </label>
                        </div>';
            $html .= '
        <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
            <label>
                <input type="radio" option="'.$type.'" name="'.$type.'_backup_type" value="files+db" onclick="mwp_wpvivid_click_schedule_type(\''.$type.'\', this);" checked />
                <span>Database + Files (WordPress Files)</span>
            </label>
        </div>
        <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
            <label>
                <input type="radio" option="'.$type.'" name="'.$type.'_backup_type" value="files" onclick="mwp_wpvivid_click_schedule_type(\''.$type.'\', this);" />
                <span>WordPress Files (Exclude Database)</span>
            </label>
        </div>
        <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
            <label>
                <input type="radio" option="'.$type.'" name="'.$type.'_backup_type" value="db" onclick="mwp_wpvivid_click_schedule_type(\''.$type.'\', this);" />
                <span>Only Database</span>
            </label>
        </div>
        '.$custom;
        }
        else{
            $custom = '<div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
                            <label>
                                <input type="radio" option="'.$type.'" name="'.$type.'_backup_type" value="custom" onclick="mwp_wpvivid_click_schedule_type(\''.$type.'\', this);" />
                                <span>Custom</span>
                            </label>
                        </div>';
            $html .= '
        <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
            <label>
                <input type="radio" option="'.$type.'" name="'.$type.'_backup_type" value="files+db" onclick="mwp_wpvivid_click_schedule_type(\''.$type.'\', this);" checked />
                <span>Database + Files (WordPress Files)</span>
            </label>
        </div>
        <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
            <label>
                <input type="radio" option="'.$type.'" name="'.$type.'_backup_type" value="files" onclick="mwp_wpvivid_click_schedule_type(\''.$type.'\', this);" />
                <span>WordPress Files (Exclude Database)</span>
            </label>
        </div>
        <div class="mwp-wpvivid-block-bottom-space mwp-wpvivid-block-right-space" style="float: left;">
            <label>
                <input type="radio" option="'.$type.'" name="'.$type.'_backup_type" value="db" onclick="mwp_wpvivid_click_schedule_type(\''.$type.'\', this);" />
                <span>Only Database</span>
            </label>
        </div>
        '.$custom;
        }
        return $html;
    }

    public function mwp_wpvivid_schedule_local_remote_addon($html, $type){
        $html .= '
        <div class="mwp-wpvivid-block-bottom-space">
            <label>
                <input type="radio" option="'.$type.'" name="'.$type.'_save_local_remote" value="local" checked />
                <span>Save backups on localhost (web server)</span>
            </label>
        </div>
        <div>
            <label>
                <input type="radio" option="'.$type.'" name="'.$type.'_save_local_remote" value="remote" />
                <span>Send backups to remote storage (Backups will be deleted from localhost after they are completely uploaded to remote storage)</span>
            </label>
        </div>
        <input type="checkbox" option="'.$type.'" name="lock" value="0" style="display: none;" />';
        return $html;
    }

    public function mwp_wpvivid_schedule_add_edit($type, $global){
        $utc_time=date( 'H:i:s - m/d/Y ', time() );
        if($global) {
            $offset = get_option('gmt_offset');
            $local_time=date( 'H:i:s - m/d/Y ', current_time( 'timestamp', 0 ) );
        }
        else{
            $offset = $this->time_zone;
            $local_time = time() + $offset * 60 * 60;
            $local_time = date("H:i:s - m/d/Y ", $local_time);
        }
        $mwp_wpvivid_cycles = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_cycles' : 'mwp_wpvivid_schedule_update_cycles';
        $mwp_wpvivid_cycles_select = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_cycles_select' : 'mwp_wpvivid_schedule_update_cycles_select';
        $mwp_wpvivid_week = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_week' : 'mwp_wpvivid_schedule_update_week';
        $mwp_wpvivid_week_select = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_week_select' : 'mwp_wpvivid_schedule_update_week_select';
        $mwp_wpvivid_day = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_day' : 'mwp_wpvivid_schedule_update_day';
        $mwp_wpvivid_day_select = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_day_select' : 'mwp_wpvivid_schedule_update_day_select';
        $mwp_wpvivid_hour_select = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_hour_select' : 'mwp_wpvivid_schedule_update_hour_select';
        $mwp_wpvivid_minute_select = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_minute_select' : 'mwp_wpvivid_schedule_update_minute_select';
        $mwp_wpvivid_utc_time = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_utc_time' : 'mwp_wpvivid_schedule_update_utc_time';
        $mwp_wpvivid_start_local_time = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_start_local_time' : 'mwp_wpvivid_schedule_update_start_local_time';
        $mwp_wpvivid_start_utc_time = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_start_utc_time' : 'mwp_wpvivid_schedule_update_start_utc_time';
        $mwp_wpvivid_start_cycles = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_start_cycles' : 'mwp_wpvivid_schedule_update_start_cycles';
        $mwp_wpvivid_start_timezone = $type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_start_timezone' : 'mwp_wpvivid_schedule_update_start_timezone';
        $location = 'options-general.php';
        $mwp_wpvivid_timezone = $global === true ? admin_url().'options-general.php' : 'admin.php?page=SiteOpen&newWindow=yes&websiteid='.$this->site_id.'&location='.base64_encode($location);
        ?>
        <?php
        if(!$global) {
            ?>
            <div class="mwp-wpvivid-block-bottom-space">
                <table class="wp-list-table widefat plugin">
                    <thead>
                    <tr>
                        <th></th>
                        <th class="manage-column column-name column-primary"><strong>Local Time </strong><a
                                    href="<?php esc_attr_e($mwp_wpvivid_timezone); ?>">(Timezone Setting)</a></th>
                        <th class="manage-column column-name column-primary"><strong>Universal Time (UTC)</strong></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th><strong>Current Time</strong></th>
                        <td>
                            <div>
                                <div style="float: left; margin-right: 10px;"><?php _e($local_time); ?></div>
                                <small>
                                    <div class="mwp-wpvivid-tooltip"
                                         style="float: left; margin-top:3px; line-height: 100%;">?
                                        <div class="mwp-wpvivid-tooltiptext">Current time in the city or the UTC
                                            timezone offset you have chosen in WordPress Timezone Settings.
                                        </div>
                                    </div>
                                </small>
                                <div style="clear: both;"></div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div style="float: left; margin-right: 10px;"><?php _e($utc_time); ?></div>
                                <small>
                                    <div class="mwp-wpvivid-tooltip"
                                         style="float: left; margin-top:3px; line-height: 100%;">?
                                        <div class="mwp-wpvivid-tooltiptext">Current local time in UTC.</div>
                                    </div>
                                </small>
                                <div style="clear: both;"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><strong>Schedule Start Time</strong></th>
                        <td>
                        <span>
                            <div id="<?php esc_attr_e($mwp_wpvivid_cycles); ?>"
                                 style="padding: 0 10px 0 0; float: left;">
                                <select id="<?php esc_attr_e($mwp_wpvivid_cycles_select); ?>"
                                        option="<?php esc_attr_e($type); ?>" name="recurrence"
                                        onchange="mwp_wpvivid_set_schedule('<?php esc_attr_e($type); ?>');">
                                    <option value="wpvivid_hourly">Every hour</option>
                                    <option value="wpvivid_2hours">Every 2 hours</option>
                                    <option value="wpvivid_4hours">Every 4 hours</option>
                                    <option value="wpvivid_8hours">Every 8 hours</option>
                                    <option value="wpvivid_12hours">Every 12 hours</option>
                                    <option value="wpvivid_daily" selected>Daily</option>
                                    <option value="wpvivid_weekly">Weekly</option>
                                    <option value="wpvivid_fortnightly">Fortnightly</option>
                                    <option value="wpvivid_monthly">30 Days</option>
                                </select>
                            </div>
                        </span>
                            <span>
                            <div id="<?php esc_attr_e($mwp_wpvivid_week); ?>"
                                 style="padding: 0 10px 0 0; float: left; display: none;">
                                <select id="<?php esc_attr_e($mwp_wpvivid_week_select); ?>"
                                        option="<?php esc_attr_e($type); ?>" name="week">
                                    <option value="sun" selected>Sunday</option>
                                    <option value="mon">Monday</option>
                                    <option value="tue">Tuesday</option>
                                    <option value="wed">Wednesday</option>
                                    <option value="thu">Thursday</option>
                                    <option value="fri">Friday</option>
                                    <option value="sat">Saturday</option>
                                </select>
                            </div>
                        </span>
                            <span>
                            <div id="<?php esc_attr_e($mwp_wpvivid_day); ?>"
                                 style="padding: 0 10px 0 0; float: left; display: none;">
                                <div class="mwp-wpvivid-schedule-font-fix mwp-wpvivid-font-right-space"
                                     style="float: left;">Start at:</div>
                                <select id="<?php esc_attr_e($mwp_wpvivid_day_select); ?>"
                                        option="<?php esc_attr_e($type); ?>" name="day">
                                    <?php
                                    $html = '';
                                    for ($i = 1; $i < 31; $i++) {
                                        $html .= '<option value="' . $i . '">' . $i . '</option>';
                                    }
                                    echo $html;
                                    ?>
                                </select>
                            </div>
                        </span>
                            <span>
                            <div style="padding: 0 10px 0 0;">
                                <select id="<?php esc_attr_e($mwp_wpvivid_hour_select); ?>"
                                        option="<?php esc_attr_e($type); ?>" name="current_day_hour"
                                        style="margin-bottom: 4px;"
                                        onchange="mwp_wpvivid_set_schedule('<?php esc_attr_e($type); ?>');">
                                    <?php
                                    $html = '';
                                    for ($hour = 0; $hour < 24; $hour++) {
                                        $format_hour = sprintf("%02d", $hour);
                                        $html .= '<option value="' . $format_hour . '">' . $format_hour . '</option>';
                                    }
                                    echo $html;
                                    ?>
                                </select>
                                <span>:</span>
                                <select id="<?php esc_attr_e($mwp_wpvivid_minute_select); ?>"
                                        option="<?php esc_attr_e($type); ?>" name="current_day_minute"
                                        style="margin-bottom: 4px;"
                                        onchange="mwp_wpvivid_set_schedule('<?php esc_attr_e($type); ?>');">
                                    <?php
                                    $html = '';
                                    for ($minute = 0; $minute < 60; $minute++) {
                                        $format_minute = sprintf("%02d", $minute);
                                        $html .= '<option value="' . $format_minute . '">' . $format_minute . '</option>';
                                    }
                                    echo $html;
                                    ?>
                                </select>
                            </div>
                        </span>
                        </td>
                        <td style="vertical-align: middle;">
                            <div>
                                <div id="<?php esc_attr_e($mwp_wpvivid_utc_time); ?>"
                                     style="float: left; margin-right: 10px;">00:00
                                </div>
                                <small>
                                    <div class="mwp-wpvivid-tooltip"
                                         style="float: left; margin-top:3px; line-height: 100%;">?
                                        <div class="mwp-wpvivid-tooltiptext">The schedule start time in UTC.</div>
                                    </div>
                                </small>
                                <div style="clear: both;"></div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="3">
                            <i>
                                <span>The schedule will be performed at [(local time)</span>
                                <span id="<?php esc_attr_e($mwp_wpvivid_start_local_time); ?>" style="margin-right: 0;">00:00</span>
                                <span>] [UTC</span>
                                <span id="<?php esc_attr_e($mwp_wpvivid_start_utc_time); ?>" style="margin-right: 0;">00:00</span>
                                <span>] [Schedule Cycles:</span>
                                <span id="<?php esc_attr_e($mwp_wpvivid_start_cycles); ?>" style="margin-right: 0;">Daily</span>]
                            </i>
                        </th>
                    <tr>
                    </tfoot>
                </table>
            </div>
            <?php
        }
        else{
            ?>
            <div style="width:100%; border:1px solid #e5e5e5; float:left; box-sizing: border-box;margin-bottom:10px;">
                <div class="mwp-wpvivid-block-bottom-space" style="margin: 1px 1px 10px 1px; background-color: #f7f7f7; box-sizing: border-box; padding: 10px;">Set backup cycle and start time:</div>
                <div class="mwp-wpvivid-block-bottom-space" style="margin-left: 10px; margin-right: 10px;">
                    <div style="padding: 4px 10px 0 0; float: left;">The backup will run</div>
                    <div id="<?php esc_attr_e($mwp_wpvivid_cycles); ?>" style="padding: 0 10px 0 0; float: left;">
                        <select id="<?php esc_attr_e($mwp_wpvivid_cycles_select); ?>" option="<?php esc_attr_e($type); ?>" name="recurrence" onchange="mwp_wpvivid_set_schedule('<?php esc_attr_e($type); ?>');">
                            <option value="wpvivid_hourly">Every hour</option>
                            <option value="wpvivid_2hours">Every 2 hours</option>
                            <option value="wpvivid_4hours">Every 4 hours</option>
                            <option value="wpvivid_8hours">Every 8 hours</option>
                            <option value="wpvivid_12hours">Every 12 hours</option>
                            <option value="wpvivid_daily" selected>Daily</option>
                            <option value="wpvivid_weekly">Weekly</option>
                            <option value="wpvivid_fortnightly">Fortnightly</option>
                            <option value="wpvivid_monthly">30 Days</option>
                        </select>
                    </div>
                    <div style="padding: 4px 10px 0 0; float: left;">at</div>
                    <div id="<?php esc_attr_e($mwp_wpvivid_week); ?>" style="padding: 0 10px 0 0; float: left; display: none;">
                        <select id="<?php esc_attr_e($mwp_wpvivid_week_select); ?>" option="<?php esc_attr_e($type); ?>" name="week">
                            <option value="sun" selected>Sunday</option>
                            <option value="mon">Monday</option>
                            <option value="tue">Tuesday</option>
                            <option value="wed">Wednesday</option>
                            <option value="thu">Thursday</option>
                            <option value="fri">Friday</option>
                            <option value="sat">Saturday</option>
                        </select>
                    </div>
                    <div id="<?php esc_attr_e($mwp_wpvivid_day); ?>" style="padding: 0 10px 0 0; float: left; display: none;">
                        <select id="<?php esc_attr_e($mwp_wpvivid_day_select); ?>" option="<?php esc_attr_e($type); ?>" name="day">
                            <?php
                            $html = '';
                            for ($i = 1; $i < 31; $i++) {
                                $html .= '<option value="' . $i . '">' . $i . '</option>';
                            }
                            echo $html;
                            ?>
                        </select>
                    </div>
                    <div style="padding: 0 10px 0 0; float: left;">
                        <select id="<?php esc_attr_e($mwp_wpvivid_hour_select); ?>" option="<?php esc_attr_e($type); ?>" name="current_day_hour" style="margin-bottom: 4px;" onchange="mwp_wpvivid_set_schedule('<?php esc_attr_e($type); ?>');">
                            <?php
                            $html = '';
                            for ($hour = 0; $hour < 24; $hour++) {
                                $format_hour = sprintf("%02d", $hour);
                                $html .= '<option value="' . $format_hour . '">' . $format_hour . '</option>';
                            }
                            echo $html;
                            ?>
                        </select>
                        <span>:</span>
                        <select id="<?php esc_attr_e($mwp_wpvivid_minute_select); ?>" option="<?php esc_attr_e($type); ?>" name="current_day_minute" style="margin-bottom: 4px;" onchange="mwp_wpvivid_set_schedule('<?php esc_attr_e($type); ?>');">
                            <?php
                            $html = '';
                            for ($minute = 0; $minute < 60; $minute++) {
                                $format_minute = sprintf("%02d", $minute);
                                $html .= '<option value="' . $format_minute . '">' . $format_minute . '</option>';
                            }
                            echo $html;
                            ?>
                        </select>
                    </div>
                    <div style="padding: 4px 10px 0 0; float: left;">in</div>
                    <div style="padding: 0 10px 0 0; float: left;">
                        <select id="<?php esc_attr_e($mwp_wpvivid_start_timezone); ?>" option="<?php esc_attr_e($type); ?>" name="start_time_zone" style="margin-bottom: 4px;">
                            <option value="utc" selected>UTC Time</option>
                            <option value="local">Local Time</option>
                        </select>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>
            <?php
        }
        ?>
        <div style="width:100%; border:1px solid #e5e5e5; float:left; box-sizing: border-box;margin-bottom:10px;">
            <div class="mwp-wpvivid-block-bottom-space" style="margin: 1px 1px 10px 1px; background-color: #f7f7f7; box-sizing: border-box; padding: 10px;">Select backup type:</div>
            <div style="margin-left: 10px; margin-right: 10px;">
                <?php
                $html = '';
                $html = apply_filters('mwp_wpvivid_schedule_backup_type_addon', $html, $type, $global);
                echo $html;
                ?>
            </div>
            <div style="clear: both;"></div>
            <?php
            if(!$global){
                ?>
                <div style="margin-left: 10px; margin-right: 10px;">
                    <?php
                    $schedule_type = $type === 'mwp_schedule_add' ? 'schedule_add' : 'schedule_update';
                    do_action('mwp_wpvivid_custom_backup_setting', $schedule_type);
                    ?>
                </div>
                <?php
            }
            else{
                ?>
                <div style="margin-left: 10px; margin-right: 10px;">
                    <?php
                    $schedule_type = $type === 'mwp_schedule_add' ? 'schedule_add' : 'schedule_update';
                    do_action('mwp_wpvivid_global_schedule_custom_backup_setting', $schedule_type);
                    ?>
                </div>
                <?php
            }
            ?>
        </div>

        <div style="width:100%; border:1px solid #e5e5e5; float:left; box-sizing: border-box;margin-bottom:10px;">
            <div class="mwp-wpvivid-block-bottom-space" style="margin: 1px 1px 10px 1px; background-color: #f7f7f7; box-sizing: border-box; padding: 10px;">Select where to send backups:</div>
            <div class="mwp-wpvivid-block-bottom-space" style="margin-left: 10px; margin-right: 10px;">
                <?php
                $html = '';
                $html = apply_filters('mwp_wpvivid_schedule_local_remote_addon', $html, $type);
                echo $html;
                ?>
            </div>
            <div class="mwp-wpvivid-block-bottom-space" id="mwp_schedule_upload_storage" style="cursor:pointer; margin-left: 10px; margin-right: 10px;" title="Highlighted icon illuminates that you have choosed a remote storage to store backups">
            </div>
        </div>
        <div style="clear: both;"></div>

        <div class="mwp-wpvivid-block-bottom-space">
            <div id="mwp_wpvivid_schedule_create_notice"></div>
            <?php
            if($type === 'mwp_schedule_add'){
                ?>
                <input class="ui green mini button" type="button" value="Create new schedule" onclick="mwp_wpvivid_create_schedule_addon('<?php esc_attr_e($type); ?>', '<?php esc_attr_e($global); ?>');" />
                <?php
            }
            else{
                ?>
                <input class="ui green mini button" type="button" value="Update Schedule" onclick="mwp_wpvivid_edit_schedule_addon('<?php esc_attr_e($type); ?>', '<?php esc_attr_e($global); ?>');" />
                <?php
            }
            ?>
        </div>

        <script>
            var first_create = '1';
            function mwp_wpvivid_create_global_custom_backup_json(parent_id){
                var json = {};
                jQuery('#'+parent_id).find('.mwp-wpvivid-custom-check').each(function(){
                    if(jQuery(this).hasClass('mwp-wpvivid-custom-core-check')){
                        if(jQuery(this).prop('checked')){
                            json['core_check'] = '1';
                        }
                        else{
                            json['core_check'] = '0';
                        }
                    }
                    else if(jQuery(this).hasClass('mwp-wpvivid-custom-database-check')){
                        if(jQuery(this).prop('checked')){
                            json['database_check'] = '1';
                        }
                        else{
                            json['database_check'] = '0';
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
                        json['content_list'] = {};
                        if(jQuery(this).prop('checked')){
                            json['content_check'] = '1';
                            json['content_extension'] = jQuery('#'+parent_id).find('.mwp-wpvivid-content-extension').val();
                        }
                        else{
                            json['content_check'] = '0';
                        }
                    }
                });
                return json;
            }

            function mwp_wpvivid_create_schedule_addon(type, global){
                var mwp_wpvivid_utc_time = type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_utc_time' : 'mwp_wpvivid_schedule_update_utc_time';
                var schedule_data = '';
                schedule_data = mwp_wpvivid_ajax_data_transfer('mwp_schedule_add');
                var backup_type = jQuery('input:radio[option=mwp_schedule_add][name=mwp_schedule_add_backup_type]:checked').val();
                if(backup_type === 'custom'){
                    schedule_data = JSON.parse(schedule_data);
                    if(global){
                        var custom_dirs = mwp_wpvivid_create_global_custom_backup_json('mwp_wpvivid_schedule_add_custom_module_part');
                    }
                    else {
                        var custom_dirs = mwp_wpvivid_create_custom_backup_json('mwp_wpvivid_schedule_add_custom_module_part');
                    }
                    var custom_option = {
                        'custom_dirs': custom_dirs
                    };
                    jQuery.extend(schedule_data, custom_option);
                    schedule_data = JSON.stringify(schedule_data);
                }

                if(global){
                    schedule_data = JSON.parse(schedule_data);
                    schedule_data['save_local_remote'] = schedule_data['mwp_schedule_add_save_local_remote'];
                    schedule_data['schedule_backup_backup_type'] = schedule_data['mwp_schedule_add_backup_type'];
                    schedule_data['status'] = 'Active';
                    schedule_data = JSON.stringify(schedule_data);
                    var schedule_mould_name = jQuery('#mwp_wpvivid_schedule_mould_name').val();
                    if(schedule_mould_name == ''){
                        alert('A schedule mould name is required.');
                        return;
                    }
                    var ajax_data = {
                        'action': 'mwp_wpvivid_global_create_schedule_addon',
                        'schedule': schedule_data,
                        'schedule_mould_name': schedule_mould_name,
                        'first_create': first_create
                    };
                }
                else {
                    var utc_time = jQuery('#'+mwp_wpvivid_utc_time).html();
                    var arr = new Array();
                    arr = utc_time.split(':');
                    schedule_data = JSON.parse(schedule_data);
                    schedule_data['save_local_remote'] = schedule_data['mwp_schedule_add_save_local_remote'];
                    schedule_data['schedule_backup_backup_type'] = schedule_data['mwp_schedule_add_backup_type'];
                    schedule_data['current_day_hour'] = arr[0];
                    schedule_data['current_day_minute'] = arr[1];
                    schedule_data['status'] = 'Active';
                    schedule_data = JSON.stringify(schedule_data);
                    var ajax_data = {
                        'action': 'mwp_wpvivid_create_schedule_addon',
                        'schedule': schedule_data,
                        'site_id': '<?php echo esc_html($this->site_id); ?>'
                    };
                }
                jQuery('#mwp_wpvivid_schedule_create_notice').html('');
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            if(global) {
                                first_create = '0';
                                jQuery('#mwp_wpvivid_schedule_create_notice').html(jsonarray.notice);
                                jQuery('#mwp_wpvivid_global_schedule_list_addon').html(jsonarray.html);
                            }
                            else{
                                jQuery('#mwp_wpvivid_schedule_create_notice').html(jsonarray.notice);
                                jQuery('#mwp_wpvivid_schedule_list_addon').html(jsonarray.html);
                            }
                        }
                        else {
                            jQuery('#mwp_wpvivid_schedule_create_notice').html(jsonarray.notice);
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

            function mwp_wpvivid_edit_schedule_addon(type, global){
                var mwp_wpvivid_utc_time = type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_utc_time' : 'mwp_wpvivid_schedule_update_utc_time';
                var schedule_data = '';
                schedule_data = mwp_wpvivid_ajax_data_transfer('mwp_schedule_update');
                var backup_type = jQuery('input:radio[option=mwp_schedule_update][name=mwp_schedule_update_backup_type]:checked').val();
                if(backup_type === 'custom'){
                    schedule_data = JSON.parse(schedule_data);
                    if(global){
                        var custom_dirs = mwp_wpvivid_create_global_custom_backup_json('mwp_wpvivid_schedule_update_custom_module_part');
                    }
                    else {
                        var custom_dirs = mwp_wpvivid_create_custom_backup_json('mwp_wpvivid_schedule_update_custom_module_part');
                    }
                    var custom_option = {
                        'custom_dirs': custom_dirs
                    };
                    jQuery.extend(schedule_data, custom_option);
                    schedule_data = JSON.stringify(schedule_data);
                }

                if(global){
                    var schedule_mould_name = mwp_wpvivid_global_edit_schedule_mould_name;
                    schedule_data = JSON.parse(schedule_data);
                    schedule_data['update_schedule_backup_save_local_remote'] = schedule_data['mwp_schedule_update_save_local_remote'];
                    schedule_data['update_schedule_backup_backup_type'] = schedule_data['mwp_schedule_update_backup_type'];
                    schedule_data['status'] = 'Active';
                    schedule_data['schedule_id'] = mwp_wpvivid_global_edit_schedule_id;
                    schedule_data = JSON.stringify(schedule_data);
                    var ajax_data = {
                        'action': 'mwp_wpvivid_global_update_schedule_addon',
                        'schedule': schedule_data,
                        'mould_name': schedule_mould_name
                    };
                }
                else {
                    var utc_time = jQuery('#'+mwp_wpvivid_utc_time).html();
                    var arr = new Array();
                    arr = utc_time.split(':');
                    schedule_data = JSON.parse(schedule_data);
                    schedule_data['update_schedule_backup_save_local_remote'] = schedule_data['mwp_schedule_update_save_local_remote'];
                    schedule_data['update_schedule_backup_backup_type'] = schedule_data['mwp_schedule_update_backup_type'];
                    schedule_data['current_day_hour'] = arr[0];
                    schedule_data['current_day_minute'] = arr[1];
                    schedule_data['status'] = 'Active';
                    schedule_data['schedule_id'] = mwp_wpvivid_edit_schedule_id;
                    schedule_data = JSON.stringify(schedule_data);
                    var ajax_data = {
                        'action': 'mwp_wpvivid_update_schedule_addon',
                        'schedule': schedule_data,
                        'site_id': '<?php echo esc_html($this->site_id); ?>'
                    };
                }
                jQuery('#mwp_wpvivid_schedule_update_notice').html('');
                mwp_wpvivid_post_request(ajax_data, function (data) {
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            if(global) {
                                jQuery('#mwp_wpvivid_schedule_update_notice').html(jsonarray.notice);
                                jQuery('#mwp_wpvivid_global_schedule_list_addon').html(jsonarray.html);
                                jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-delete',[ 'schedules_edit', 'schedules' ]);
                            }
                            else{
                                jQuery('#mwp_wpvivid_schedule_update_notice').html(jsonarray.notice);
                                jQuery('#mwp_wpvivid_schedule_list_addon').html(jsonarray.html);
                                jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-delete',[ 'schedules_edit', 'schedules' ]);
                            }
                        }
                        else {
                            jQuery('#mwp_wpvivid_schedule_update_notice').html(jsonarray.notice);
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

            var time_offset=<?php echo $offset ?>;
            function mwp_wpvivid_set_schedule(type){
                var mwp_wpvivid_week_id = type === 'mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_week' : 'mwp_wpvivid_schedule_update_week';
                var mwp_wpvivid_day_id = type === 'mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_day' : 'mwp_wpvivid_schedule_update_day';
                var mwp_wpvivid_cycles_select = type === 'mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_cycles_select' : 'mwp_wpvivid_schedule_update_cycles_select';
                var mwp_wpvivid_utc_time = type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_utc_time' : 'mwp_wpvivid_schedule_update_utc_time';
                var mwp_wpvivid_start_local_time = type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_start_local_time' : 'mwp_wpvivid_schedule_update_start_local_time';
                var mwp_wpvivid_start_utc_time = type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_start_utc_time' : 'mwp_wpvivid_schedule_update_start_utc_time';
                var mwp_wpvivid_start_cycles = type==='mwp_schedule_add' ? 'mwp_wpvivid_schedule_add_start_cycles' : 'mwp_wpvivid_schedule_update_start_cycles';

                jQuery('#'+mwp_wpvivid_week_id).hide();
                jQuery('#'+mwp_wpvivid_day_id).hide();
                var cycles_value = jQuery('#'+mwp_wpvivid_cycles_select).val();
                if(cycles_value === 'wpvivid_weekly' || cycles_value === 'wpvivid_fortnightly') {
                    jQuery('#'+mwp_wpvivid_week_id).show();
                }
                else if(cycles_value === 'wpvivid_monthly'){
                    jQuery('#'+mwp_wpvivid_day_id).show();
                }
                var cycles_display = jQuery('#'+mwp_wpvivid_cycles_select+' option:checked').text();
                jQuery('#'+mwp_wpvivid_start_cycles).html(cycles_display);

                var hour='00';
                var minute='00';
                jQuery('select[option='+type+'][name=current_day_hour]').each(function() {
                    hour=jQuery(this).val();
                });
                jQuery('select[option='+type+'][name=current_day_minute]').each(function(){
                    minute=jQuery(this).val();
                });
                var time=hour+":"+minute;
                jQuery('#'+mwp_wpvivid_start_local_time).html(time);
                hour=Number(hour)-Number(time_offset);
                var Hours=Math.floor(hour);
                var Minutes=Math.floor(60*(hour-Hours));
                Minutes=Number(minute)+Minutes;
                if(Minutes>=60) {
                    Hours=Hours+1;
                    Minutes=Minutes-60;
                }
                if(Hours>=24) {
                    Hours=Hours-24;
                }
                else if(Hours<0) {
                    Hours=24-Math.abs(Hours);
                }
                if(Hours<10) {
                    Hours='0'+Hours;
                }
                if(Minutes<10) {
                    Minutes='0'+Minutes;
                }
                time=Hours+":"+Minutes;
                jQuery('#'+mwp_wpvivid_utc_time).html(time);
                jQuery('#'+mwp_wpvivid_start_utc_time).html(time);
            }

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

            function mwp_wpvivid_click_schedule_type(type, obj){
                type = type === 'mwp_schedule_add' ? 'schedule_add' : 'schedule_update';
                if(obj.value === 'custom') {
                    jQuery('#mwp_wpvivid_' + type + '_custom_module_part').show();
                    mwp_wpvivid_popup_schedule_tour_addon('show', type);
                }
                else{
                    jQuery('#mwp_wpvivid_' + type + '_custom_module_part').hide();
                    mwp_wpvivid_popup_schedule_tour_addon('hide', type);
                }
            }

            function mwp_wpvivid_add_global_schedule_extension_rule(obj, type, value){
                var ajax_data = {
                    'action': 'mwp_wpvivid_update_global_schedule_backup_exclude_extension_addon',
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

            jQuery(document).ready(function ()
            {
                mwp_wpvivid_set_schedule('mwp_schedule_add');
            });
        </script>
        <?php
    }

    public function mwp_wpvivid_schedule_settings()
    {
        ?>
        <tr>
            <td class="row-title tablelistcolumn"><label for="tablecell">Schedule Settings</label></td>
            <td class="tablelistcolumn">
                <div>
                    <div class="postbox mwp-wpvivid-schedule-block" style="margin-bottom: 10px;">
                        <div class="mwp-wpvivid-block-bottom-space">
                            <label for="mwp_wpvivid_schedule_enable">
                                <input option="mwp-schedule" name="mwp_enable" type="checkbox" id="mwp_wpvivid_schedule_enable" />
                                <span>Enable backup schedule</span>
                            </label><br>
                        </div>
                        <div class="mwp-wpvivid-block-bottom-space">
                            <?php
                            $notice='';
                            $notice= apply_filters('mwp_wpvivid_schedule_notice',$notice);
                            echo $notice;
                            ?>
                        </div>
                    </div>

                    <div class="postbox mwp-wpvivid-schedule-block" style="margin-bottom: 10px;">
                        <div class="mwp-wpvivid-block-bottom-space">
                            <label>
                                <input type="radio" option="mwp-schedule" name="mwp_recurrence" value="wpvivid_12hours" />
                                <span>12Hours</span>
                            </label>
                        </div>
                        <div class="mwp-wpvivid-block-bottom-space">
                            <label>
                                <input type="radio" option="mwp-schedule" name="mwp_recurrence" value="wpvivid_daily" />
                                <span>Daily</span>
                            </label>
                        </div>
                        <div class="mwp-wpvivid-block-bottom-space">
                            <label>
                                <input type="radio" option="mwp-schedule" name="mwp_recurrence" value="wpvivid_weekly" />
                                <span>Weekly</span>
                            </label>
                        </div>
                        <div class="mwp-wpvivid-block-bottom-space">
                            <label>
                                <input type="radio" option="mwp-schedule" name="mwp_recurrence" value="wpvivid_fortnightly" />
                                <span>Fortnightly</span>
                            </label>
                        </div>
                        <div class="mwp-wpvivid-block-bottom-space">
                            <label>
                                <input type="radio" option="mwp-schedule" name="mwp_recurrence" value="wpvivid_monthly" />
                                <span>Monthly</span>
                            </label>
                        </div>
                    </div>

                    <div class="postbox mwp-wpvivid-schedule-block" id="mwp_wpvivid_schedule_backup_type" style="margin-bottom: 10px;">
                        <?php
                        $backup_type='';
                        $backup_type= apply_filters('mwp_wpvivid_schedule_backup_type',$backup_type);
                        echo $backup_type;
                        ?>
                    </div>

                    <div class="postbox mwp-wpvivid-schedule-block" id="mwp_wpvivid_schedule_remote_storage" style="margin-bottom: 10px;">
                        <?php
                        $html='';
                        $html= apply_filters('mwp_wpvivid_schedule_local_remote',$html);
                        echo $html;
                        ?>
                    </div>
                </div>
            </td>
        </tr>
        <script>
            <?php
            do_action('mwp_wpvivid_schedule_do_js');
            ?>
        </script>
        <?php
    }

    public function mwp_wpvivid_schedule_backup_type($html)
    {
        $html ='<div class="mwp-wpvivid-block-bottom-space">';
        $html.='<label>';
        $html.='<input type="radio" option="mwp-schedule" name="mwp_backup_type" value="files+db"/>';
        $html.='<span>Database + Files (Entire website)</span>';
        $html.='</label>';
        $html.='</div>';

        $html.='<div class="mwp-wpvivid-block-bottom-space">';
        $html.='<label>';
        $html.='<input type="radio" option="mwp-schedule" name="mwp_backup_type" value="files"/>';
        $html.='<span>All Files (Exclude Database)</span>';
        $html.='</label>';
        $html.='</div>';

        $html.='<div class="mwp-wpvivid-block-bottom-space">';
        $html.='<label>';
        $html.='<input type="radio" option="mwp-schedule" name="mwp_backup_type" value="db"/>';
        $html.='<span>Only Database</span>';
        $html.='</label>';
        $html.='</div>';

        return $html;
    }

    public function mwp_wpvivid_schedule_notice($html)
    {
        $html='<div class="mwp-wpvivid-block-bottom-space">1) Scheduled job will start at web server time: </div>';
        $html.='<div class="mwp-wpvivid-block-bottom-space">2) Being subjected to mechanisms of PHP, a scheduled backup task for your site will be triggered only when the site receives at least a visit at any page.</div>';
        return $html;
    }

    public function mwp_wpvivid_schedule_local_remote($html)
    {
        $html = '';
        $schedule=$this->setting;
        $backup_local = 'checked';
        $backup_remote = '';
        if(isset($schedule['enable'])) {
            if ($schedule['enable'] == true) {
                if ($schedule['backup']['remote'] === 1) {
                    $backup_local = '';
                    $backup_remote = 'checked';
                } else {
                    $backup_local = 'checked';
                    $backup_remote = '';
                }
            }
        }
        $html .= '<div class="mwp-wpvivid-block-bottom-space">
                       <label>
                            <input type="radio" option="mwp-schedule" name="mwp_save_local_remote" value="local" '.esc_attr($backup_local).' />
                            <span>'.__( 'Save backups on localhost of child-site (web server)', 'mainwp-wpvivid-extension' ).'</span>
                       </label>
                   </div>
                   <div class="mwp-wpvivid-block-bottom-space">
                       <label>
                            <input type="radio" option="mwp-schedule" name="mwp_save_local_remote" value="remote" '.esc_attr($backup_remote).' />
                            <span>'.__( 'Send backups to remote storage (choose this option, the local backup will be deleted after uploading to remote storage completely)', 'mainwp-wpvivid-extension' ).'</span>
                       </label>
                   </div>
                   <div class="mwp-wpvivid-block-bottom-space" id="mwp_wpvivid_schedule_upload_storage" style="cursor:pointer;" title="Highlighted icon illuminates that you have choosed a remote storage to store backups"></div>
                   <label style="display: none;">
                        <input type="checkbox" option="mwp-schedule" name="mwp_lock" value="0" />
                   </label>
                   ';
        return $html;
    }

    public function mwp_wpvivid_schedule_do_js()
    {
        $schedule=$this->setting;
        if(isset($schedule['enable'])) {
            if ($schedule['enable'] == true) {
                ?>
                jQuery("#mwp_wpvivid_schedule_enable").prop('checked', true);
                <?php
                if ($schedule['backup']['remote'] === 1) {
                    $schedule_remote = 'remote';
                } else {
                    $schedule_remote = 'local';
                }
            } else {
                $schedule['type'] = 'wpvivid_daily';
                $schedule['backup']['backup_files'] = 'files+db';
                $schedule_remote = 'local';
            }
        }
        else{
            $schedule['type'] = 'wpvivid_daily';
            $schedule['backup']['backup_files'] = 'files+db';
            $schedule_remote = 'local';
        }
        ?>
        jQuery("input:radio[value='<?php echo esc_attr($schedule['type']); ?>']").prop('checked', true);
        jQuery("input:radio[value='<?php echo esc_attr($schedule['backup']['backup_files']); ?>']").prop('checked', true);
        jQuery("input:radio[name='mwp_save_local_remote'][value='remote']").click(function(){
            if(!mwp_wpvivid_has_remote){
                alert('There is no default remote storage configured. Please set it up first.');
                jQuery('input:radio[name=mwp_save_local_remote][value=local]').prop('checked', true);
            }
        });
        <?php
    }

    public function mwp_wpvivid_synchronize_setting($check_addon, $mould_name = '', $is_incremental = 0)
    {
        global $mainwp_wpvivid_extension_activator;
        if(intval($check_addon) === 1) {
            if (intval($is_incremental) === 1) {
                $submit_id = 'mwp_wpvivid_sync_incremental_schedule';
            } else {
                $submit_id = 'mwp_wpvivid_sync_schedule';
            }
        }
        else{
            $submit_id = 'mwp_wpvivid_sync_schedule';
        }
        $mainwp_wpvivid_extension_activator->render_sync_websites_page($submit_id, $check_addon, $mould_name);
        ?>
        <script>
            var sync_btn_id = '<?php echo $submit_id; ?>';
            jQuery('#'+sync_btn_id).click(function(){
                mwp_wpvivid_sync_schedule();
            });
            function mwp_wpvivid_sync_schedule()
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
                    jQuery('#mwp_wpvivid_sync_schedule').css({'pointer-events': 'none', 'opacity': '0.4'});
                    var check_addon = '<?php echo $check_addon; ?>';
                    if(check_addon){
                        var schedule_mould_name = jQuery('.mwp_wpvivid_schedule_mould_name').html();
                        mwp_wpvivid_sync_schedule_mould(website_ids, schedule_mould_name, check_addon, sync_btn_id, 'Extensions-Wpvivid-Backup-Mainwp&tab=schedules', 'mwp_wpvivid_scheduled_tab');
                    }
                    else {
                        mwp_wpvivid_sync_site(website_ids, check_addon, sync_btn_id, 'Extensions-Wpvivid-Backup-Mainwp&tab=schedules', 'mwp_wpvivid_scheduled_tab');
                    }
                }
            }
        </script>
        <?php
    }

    public function get_websites_row($websites)
    {
        foreach ( $websites as $website )
        {
            $website_id = $website['id'];
            if(!$website['active'])
            {
                continue;
            }

            ?>
            <tr class="mwp-wpvivid-sync-row"">
                <th class="check-column" website-id="<?php esc_attr_e($website_id); ?>">
                    <input type="checkbox"  name="checked[]" >
                </th>
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
    }
}

class Mainwp_WPvivid_Global_Schedule_Custom_Backup_List
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
            <!-- database -->
            <tr style="cursor: pointer;">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-database-check" <?php esc_attr_e($database_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font">Database</td>
                <td class="column-description desc"><?php _e($database_descript); ?></td>
            </tr>
            <!-- themes -->
            <tr style="cursor: pointer;">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-themes-check" <?php esc_attr_e($themes_plugins_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font">Themes</td>
                <td class="column-description desc"><?php _e($themes_plugins_descript); ?></td>
            </tr>
            <!-- plugins -->
            <tr style="cursor: pointer;">
                <th class="check-column" scope="row" style="padding-left: 6px;">
                    <label class="screen-reader-text" for=""></label>
                    <input type="checkbox" name="checked[]" class="mwp-wpvivid-custom-check mwp-wpvivid-custom-plugins-check" <?php esc_attr_e($themes_plugins_check); ?> />
                </th>
                <td class="plugin-title column-primary mwp-wpvivid-backup-to-font">Plugins</td>
                <td class="column-description desc"><?php _e($themes_plugins_descript); ?></td>
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