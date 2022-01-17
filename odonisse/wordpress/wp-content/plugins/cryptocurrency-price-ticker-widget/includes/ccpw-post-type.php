<?php

class CPTW_Posttype
{
    public function __construct()
    {
      //creating posttype for plugin settings panel
        add_action( 'init',array($this,'ccpw_post_type'));
     
        if(is_admin()){
        add_action( 'add_meta_boxes',array($this,'register_ccpw_meta_box'));
    	add_action( 'add_meta_boxes_ccpw',array($this,'ccpw_add_meta_boxes'));
        add_filter( 'manage_ccpw_posts_columns',array($this,'set_custom_edit_ccpw_columns'));
        add_action( 'manage_ccpw_posts_custom_column' ,array($this,'custom_ccpw_column'), 10, 2 );
        add_action( 'save_post', array( $this,'save_ccpw_shortcode'),10, 3 );
        }
        require_once CCPWF_DIR . 'admin/ccpw-settings.php';
        // integrating cmb2 metaboxes in post type
		add_action( 'cmb2_admin_init','cmb2_ccpw_metaboxes');
    }


/*
|--------------------------------------------------------------------------
| Register Custom Post Type of Crypto Widget
|--------------------------------------------------------------------------
*/   
function ccpw_post_type()
{

    $labels = array(
        'name' => _x('CryptoCurrency Price Widget', 'Post Type General Name', 'ccpwx'),
        'singular_name' => _x('CryptoCurrency Price Widget', 'Post Type Singular Name', 'ccpwx'),
        'menu_name' => __('Crypto Widgets', 'ccpwx'),
        'name_admin_bar' => __('Post Type', 'ccpwx'),
        'archives' => __('Item Archives', 'ccpwx'),
        'attributes' => __('Item Attributes', 'ccpwx'),
        'parent_item_colon' => __('Parent Item:', 'ccpwx'),
        'all_items' => __('All Shortcodes', 'ccpwx'),
        'add_new_item' => __('Add New Shortcode', 'ccpwx'),
        'add_new' => __('Add New', 'ccpwx'),
        'new_item' => __('New Item', 'ccpwx'),
        'edit_item' => __('Edit Item', 'ccpwx'),
        'update_item' => __('Update Item', 'ccpwx'),
        'view_item' => __('View Item', 'ccpwx'),
        'view_items' => __('View Items', 'ccpwx'),
        'search_items' => __('Search Item', 'ccpwx'),
        'not_found' => __('Not found', 'ccpwx'),
        'not_found_in_trash' => __('Not found in Trash', 'ccpwx'),
        'featured_image' => __('Featured Image', 'ccpwx'),
        'set_featured_image' => __('Set featured image', 'ccpwx'),
        'remove_featured_image' => __('Remove featured image', 'ccpwx'),
        'use_featured_image' => __('Use as featured image', 'ccpwx'),
        'insert_into_item' => __('Insert into item', 'ccpwx'),
        'uploaded_to_this_item' => __('Uploaded to this item', 'ccpwx'),
        'items_list' => __('Items list', 'ccpwx'),
        'items_list_navigation' => __('Items list navigation', 'ccpwx'),
        'filter_items_list' => __('Filter items list', 'ccpwx'),
    );
    $args = array(
        'label' => __('CryptoCurrency Price Widget', 'ccpwx'),
        'description' => __('Post Type Description', 'ccpwx'),
        'labels' => $labels,
        'supports' => array('title'),
        'taxonomies' => array(''),
        'hierarchical' => false,
        'public' => false, // it's not public, it shouldn't have it's own permalink, and so on
        'show_ui' => true,
        'show_in_nav_menus' => true, // you shouldn't be able to add it to menus
        'menu_position' => 9,
        'show_in_admin_bar' => false,
        'show_in_menu' => false,
        'can_export' => true,
        'has_archive' => false, // it shouldn't have archive page
        'rewrite' => false, // it shouldn't have rewrite rules
        'exclude_from_search' => true,
        'publicly_queryable' => true,
        'menu_icon' => CCPWF_URL . '/assets/ccpw-icon.png',
        'capability_type' => 'post',
    );
    register_post_type('ccpw', $args);

}

/*
|--------------------------------------------------------------------------
| Register  meta boxes for shortcode
|--------------------------------------------------------------------------
*/ 
    function register_ccpw_meta_box()
    {
        add_meta_box('ccpw-shortcode', 'Crypto Widget Shortcode', array($this,'ccpw_p_shortcode_meta'), 'ccpw', 'side', 'high');
    }


    /*
    Plugin Shortcode meta section
    */
    function ccpw_p_shortcode_meta()
    {
        $id = get_the_ID();
        $dynamic_attr = '';
        _e(' <p>Paste this shortcode anywhere in Page/Post.</p>', 'ccpwx');

        $element_type = get_post_meta($id, 'pp_type', true);
        $dynamic_attr .= "[ccpw id=\"{$id}\"";
        $dynamic_attr .= ']';
        ?>
            <input style="width:100%" onClick="this.select();" type="text" class="regular-small" name="my_meta_box_text" id="my_meta_box_text" value="<?php echo htmlentities($dynamic_attr); ?>" readonly/>
        <div>
            <br/>
            <a class="button button-secondary red" target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcoin-market-cap-prices-wordpress-cryptocurrency-plugin%2F21429844">How to create CoinMarketCap.com clone?</a>
        </div>
            <?php
    }

/*
|--------------------------------------------------------------------------
| Register  meta boxes for Feedback
|--------------------------------------------------------------------------
*/ 
    
function ccpw_add_meta_boxes($post)
{
    add_meta_box(
        'ccpw-feedback-section',
        __('Hopefully you are Happy with our Cool Crypto Widgets Plugin', 'ccpwx'),
        array($this,'ccpw_right_section'),
        'ccpw',
        'side',
        'low'
    );
}

/*
Admin notice for plugin feedback
*/
function ccpw_right_section($post, $callback)
{
    global $post;
    $pro_add = '';
    $pro_add .=

    '<p>'.__('You have been using','ccpwx').'<b>'.__(' Cryptocurrency Widgets','ccpwx').'</b>'.__(' for a while. We hope you liked it ! Please give us a quick rating, it works as a boost for us to keep working on the plugin !', 'ccpwx') .
    '<br/><br/><a href="https://wordpress.org/support/plugin/cryptocurrency-price-ticker-widget/reviews/#new-post" class="button button-primary" target="_blank">'.__('Submit Review','ccpwx').' ★★★★★</a></p>
        <hr>
         <div>
         <a class="button button-primary" target="_blank" href="https://1.envato.market/c/1258464/275988/4415?u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fcryptocurrency-price-ticker-widget-pro-wordpress-plugin%2F21269050s">' . __('Buy Now', 'ccpwx') . ' ($24)</a>
         <a  class="button button-secondary" target="_blank" href="http://cryptowidgetpro.coolplugins.net/">' . __('VIEW ALL DEMOS', 'ccpwx') . '</a>

         <h3>'.__('Crypto Widgets Pro Features:-','ccpwx').'</h3>
      <ol style="list-style:disc;"><li> '.__('You can display real time live price changes. -','ccpwx').' <a href="http://cryptowidgetpro.coolplugins.net/list-widget/#live-changes-demo" target="_blank">'.__('DEMO','ccpwx').'</a></li>
		<li> '.__('Create widgets for 3700+ crypto coins in pro version.','ccpwx').'</li>
		<li>  '.__('Create historical price charts & tradingview candlestick charts. - ','ccpwx').'<a href="http://cryptowidgetpro.coolplugins.net/coin-price-chart/" target="_blank">'.__('DEMO','ccpwx').'</a></li>
		<li>  '.__('You can create beautiful price label and crypto price card designs.','ccpwx').'</li>
         <li>  '.__('Display latest crypto news feed from popular websites. - ','ccpwx').'<a href="http://cryptowidgetpro.coolplugins.net/news-feed/" target="_blank">'.__('DEMO','ccpwx').'</a></li>
		<li>  '.__('Display market cap and volume of virtual crypto coins.','ccpwx').'</li>
		<li>  '.__('32+ fiat currencies support - USD, GBP, EUR, INR, JPY, CNY, ILS, KRW, RUB, DKK, PLN, AUD, BRL, MXN, SEK, CAD, HKD, MYR, SGD, CHF, HUF, NOK, THB, CLP, IDR, NZD, TRY, PHP, TWD, CZK, PKR, ZAR.','ccpwx').'</li>
        <li> '.__('Create an advance table with charts and extra values','ccpwx').'</li>
        <li> '.__('Create Elegent Price widget with accordion layout','ccpwx').'</li>
        <li>'.__('Display Coin Price in 3 type of Price Block layouts','ccpwx').'</li>
        <li>'.__('Create a beautiful coin price slider.','ccpwx').'</li>
		</ol>
     
        </div>';
        

    echo $pro_add;

}

/*
|--------------------------------------------------------------------------
| Set Custom Column for Post Type
|--------------------------------------------------------------------------
*/ 
   
function set_custom_edit_ccpw_columns($columns) {
     $columns['type'] = __( 'Widget Type', 'ccpwx' );
     $columns['shortcode'] = __( 'Shortcode', 'ccpwx' );
     return $columns;
 }

 function custom_ccpw_column( $column, $post_id ) {
     switch ( $column ) {
         case 'type' :
               $type=get_post_meta( $post_id , 'type' , true ); 
             switch ($type){				
                 case "ticker":
                     _e('Ticker','ccpwx');
                 break;
                 case "price-label":
                     _e('Price Label', 'ccpwx');
                 break;
                 case "multi-currency-tab":
                     _e('Multi Currency Tabs', 'ccpwx');
                 break;
                 case "table-widget":
                     _e('Table Widget','ccpwx');
                 break;
                 default:
                     _e('List Widget','ccpwx');
             }
           break;
         case 'shortcode' :
             echo '<code>[ccpw id="'.$post_id.'"]</code>'; 
         break;
         default:
             _e('Not Matched','ccpwx');
     }
 }


/**
 * Save shortcode when a post is saved.
 *
 * @param int $post_id The post ID.
 * @param post $post The post object.
 * @param bool $update Whether this is an existing post being updated or not.
 */
function save_ccpw_shortcode( $post_id, $post, $update ) {
    // Autosave, do nothing
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
            return;
    // AJAX? Not used here
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
            return;
    // Check user permissions
    if ( ! current_user_can( 'edit_post', $post_id ) )
            return;
    // Return if it's a post revision
    if ( false !== wp_is_post_revision( $post_id ) )
            return;
    /*
    * In production code, $slug should be set only once in the plugin,
    * preferably as a class property, rather than in each function that needs it.
    */
    $post_type = get_post_type($post_id);

    // If this isn't a 'ccpw' post, don't update it.
    if ( "ccpw" != $post_type ) return;
        // - Update the post's metadata.
        if(isset($_POST['ticker_position'])&& in_array($_POST['ticker_position'],array('header','footer'))){
            update_option('ccpw-p-id',$post_id);
            update_option('ccpw-shortcode',"[ccpw id=".$post_id."]");
            }

        delete_transient( 'ccpw-coins' ); // Site Transient
}



}


