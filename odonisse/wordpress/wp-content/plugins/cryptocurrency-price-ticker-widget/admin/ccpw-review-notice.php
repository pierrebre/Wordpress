<?php

if (!class_exists('ccpw_review_notice')) {
    class ccpw_review_notice {
        /**
         * The Constructor
         */
        public function __construct() {
            // register actions
         
            if(is_admin()){
                add_action( 'admin_notices',array($this,'ccpw_admin_notice_for_reviews'));
                add_action( 'admin_print_scripts', array($this, 'ccpw_load_script' ) );
                add_action( 'wp_ajax_ccpw_dismiss_notice',array($this,'ccpw_dismiss_review_notice' ) );
            }
        }

    /**
	 * Load script to dismiss notices.
	 *
	 * @return void
	 */
	public function ccpw_load_script() {
		wp_register_script( 'ccpw-feedback-notice-script', CCPWF_URL.'/assets/js/ccpw-admin-feedback-notice.js', array( 'jquery' ),null, true );
        wp_enqueue_script( 'ccpw-feedback-notice-script' );
        wp_register_style( 'ccpw-feedback-notice-styles', CCPWF_URL.'/assets/css/ccpw-admin-feedback-notice.css' );
        wp_enqueue_style( 'ccpw-feedback-notice-styles' );
    }
    // ajax callback for review notice
    public function ccpw_dismiss_review_notice(){
        $rs=update_option( 'ccpw-alreadyRated','yes' );
        echo  json_encode( array("success"=>"true") );
        exit;
    }
   // admin notice  
    public function ccpw_admin_notice_for_reviews(){

        if( !current_user_can( 'update_plugins' ) ){
            return;
         }
         // get installation dates and rated settings
         $installation_date = get_option( 'ccpw_activation_time' );
         if(is_numeric($installation_date)){
            $installation_date = date("Y-m-d h:i:s", $installation_date);
         }
       
         $alreadyRated =get_option( 'ccpw-alreadyRated' )!=false?get_option( 'ccpw-alreadyRated'):"no";
         
         if(null != get_option('ccpw_spare_me')){
            $spare_me = get_option('ccpw_spare_me');
            if($spare_me==true){
               $alreadyRated ="yes";
            }
         }
        
         // check user already rated 
         if( $alreadyRated=="yes") {
                return;
            }

            // grab plugin installation date and compare it with current date
            $display_date = date( 'Y-m-d h:i:s' );
            $install_date= new DateTime( $installation_date );
            $current_date = new DateTime( $display_date );
            $difference = $install_date->diff($current_date);
            $diff_days= $difference->days;
          
            // check if installation days is greator then week
          if (isset($diff_days) && $diff_days>=3) {
                echo $this->ccpw_create_notice_content();
                }
       }  

       // generated review notice HTML
       function ccpw_create_notice_content(){
        
        $ajax_url=admin_url( 'admin-ajax.php' );
        $ajax_callback='ccpw_dismiss_notice';
        $wrap_cls="notice notice-info is-dismissible";
        $img_path= CCPWF_URL.'assets/crypto-widget.png';
        $p_name="Cryptocurrency Widgets";
        $like_it_text='Rate Now! ★★★★★';
        $already_rated_text=esc_html__( 'I already rated it', 'cool-timeline' );
        $not_like_it_text=esc_html__( 'No, not good enough, i do not like to rate it!', 'cool-timeline' );
        $not_interested=esc_html__( 'Not Interested', 'cool-timeline' );
        $p_link=esc_url('https://wordpress.org/support/plugin/cryptocurrency-price-ticker-widget/reviews/#new-post');
       
        $message="Thanks for using <b>$p_name</b> WordPress plugin. We hope it meets your expectations! <br/>Please give us a quick rating, it works as a boost for us to keep working on more <a href='https://coolplugins.net' target='_blank'><strong>Cool Plugins</strong></a>!<br/>";
      
        $html='<div data-ajax-url="%8$s"  data-ajax-callback="%9$s" class="cool-feedback-notice-wrapper %1$s">
        <div class="logo_container"><a href="%5$s"><img src="%2$s" alt="%3$s"></a></div>
        <div class="message_container">%4$s
        <div class="callto_action">
        <ul>
            <li class="love_it"><a href="%5$s" class="like_it_btn button button-primary" target="_new" title="%6$s">%6$s</a></li>
            <li class="already_rated"><a href="javascript:void(0);" class="already_rated_btn button ccpw_dismiss_notice" title="%7$s">%7$s</a></li>
            <li class="already_rated"><a href="javascript:void(0);" class="already_rated_btn button ccpw_dismiss_notice" title="%10$s">%10$s</a></li>
        </ul>
        <div class="clrfix"></div>
        </div>
        </div>
        </div>';

        return sprintf($html,
        $wrap_cls,
        $img_path,
        $p_name,
        $message,
        $p_link,
        $like_it_text,
        $already_rated_text,
        $ajax_url,// 8
        $ajax_callback,//9
        $not_interested//10
        );
        
       }

    } //class end

} 



