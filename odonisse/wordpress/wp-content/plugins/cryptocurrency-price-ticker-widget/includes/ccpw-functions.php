<?php

/*
|--------------------------------------------------------------------------
| getting all coins details from database
|--------------------------------------------------------------------------
*/
function ccpw_get_coins_data($coin_id_arr)
{
    $DB = new ccpw_database;
    $coin_data = $DB->get_coins(array('coin_id' => $coin_id_arr, 'number' => '1000','orderby'=>'market_cap','order'=>'DESC'));

    if (is_array($coin_data) && isset($coin_data)) {
        $coin_rs_data = ccpw_objectToArray($coin_data);
        return $coin_rs_data;
    } else {
        return false;
    }

}

/*
|--------------------------------------------------------------------------
| getting all coins details from database
|--------------------------------------------------------------------------
*/

function ccpw_get_top_coins_data($limit){
    $order_col_name = 'market_cap';
    $order_type ='DESC';
    $DB = new ccpw_database;
    $coin_data= $DB->get_coins( array("number"=>$limit,'offset'=> 0,'orderby' => $order_col_name,
    'order' => $order_type
    ));
    if (is_array($coin_data) && isset($coin_data)) {
        $coins_rs_arr = ccpw_objectToArray($coin_data);
        return $coins_rs_arr;
    } else {
        return false;
    }
}

/*
|--------------------------------------------------------------------------
| getting all coin ids from database
|--------------------------------------------------------------------------
*/
function ccpw_get_all_coin_ids()
{
    $DB = new ccpw_database;
    $coin_data = $DB->get_coins(array('number' => '1000'));
    if (is_array($coin_data) && isset($coin_data)) {
        $coin_data = ccpw_objectToArray($coin_data);
        $coins = array();
        foreach ($coin_data as $coin) {
            $coins[$coin['coin_id']] = $coin['name'];
        }
        return $coins;
    } else {
        return false;
    }

}

/*
|-----------------------------------------------------------
| Fetching data through CoinGecko API and save in database
|-----------------------------------------------------------
| MUST NOT CALL THIS FUNCTION DIRECTLY
|-----------------------------------------------------------
*/
function ccpw_get_coin_gecko_data()
{
    $update_api_name = 'ccpw-active-api';
    $data_cache_name = 'ccpw-saved-coindata';
    $activate_api = get_transient($update_api_name);
    $cache = get_transient($data_cache_name);

    // Avoid updating database if cache exist and same API is requested
    if ($activate_api == 'CoinGecko' && false != $cache ) {
        return;
    }

    $coins = array();
    $api_url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=250&page=1&sparkline=false';

    $request = wp_remote_get($api_url, array('timeout' => 120, 'sslverify' => false));
    if (is_wp_error($request)) {
        return false; // Bail early
    }
    $body = wp_remote_retrieve_body($request);
    $coins = json_decode($body);
    $response = array();
    $coin_data = array();

    if (isset($coins) && $coins != "" && is_array($coins)) {
        foreach ($coins as $coin) {
            $response['coin_id'] = $coin->id;
            $response['rank'] = $coin->market_cap_rank;
            $response['name'] = $coin->name;
            $response['symbol'] = strtoupper($coin->symbol);
            $response['price'] = ccpw_set_default_if_empty($coin->current_price, 0.00);
            $response['percent_change_24h'] = ccpw_set_default_if_empty($coin->price_change_percentage_24h, 0);
            $response['market_cap'] = ccpw_set_default_if_empty($coin->market_cap, 0);
            $response['total_volume'] = ccpw_set_default_if_empty($coin->total_volume);
            $response['circulating_supply'] = ccpw_set_default_if_empty($coin->circulating_supply);
            $response['logo'] =$coin->image;
            $coin_data[] = $response;
        }
        $DB = new ccpw_database();
        $DB->create_table();
        $DB->ccpw_insert($coin_data);
        set_transient($data_cache_name, date('H:s:i'), 5 * MINUTE_IN_SECONDS);
        set_transient($update_api_name, 'CoinGecko', 0);

    }

}

/**
 * Check if provided $value is empty or not.
 * Return $default if $value is empty
 */
function ccpw_set_default_if_empty($value, $default = 'N/A')
{
    return $value ? $value : $default;
}

/*
Adding coins SVG logos
*/
function ccpw_get_coin_logo($coin_id, $size = 32, $HTML = true)
{
    $logo_html = '';
    $coin_svg = CCPWF_DIR . '/assets/coin-logos/' . strtolower($coin_id) . '.svg';
    $coin_png = CCPWF_DIR . '/assets/coin-logos/' . strtolower($coin_id) . '.png';

    if (file_exists($coin_svg)) {
        $coin_svg = CCPWF_URL . 'assets/coin-logos/' . strtolower($coin_id) . '.svg';
        if ($HTML == true) {
            $logo_html = '<img id="' . $coin_id . '" alt="' . $coin_id . '" src="' . $coin_svg . '">';
        } else {
            $logo_html = $coin_svg;
        }
    return $logo_html;

    } else if(file_exists($coin_png)) {
        $coin_png = CCPWF_URL . 'assets/coin-logos/' . strtolower($coin_id) . '.png';
        if ($HTML == true) {
            $logo_html = '<img id="' . $coin_id . '" alt="' . $coin_id . '" src="' . $coin_png . '">';
        } else {
            $logo_html = $coin_png;
        }
    return $logo_html;

    }else{
        return false;
    }
}

/* USD conversions */
function ccpw_usd_conversions($currency)
{
    // use common transient between cmc and ccpw
    $conversions = get_transient('cmc_usd_conversions');
    if (empty($conversions) || $conversions === "") {
        $request = wp_remote_get('https://api-beta.coinexchangeprice.com/v1/exchange-rates', array('sslverify' => false));

        if (is_wp_error($request)) {
            return false;
        }

        $currency_ids = array("USD", "AUD", "BRL", "CAD", "CZK", "DKK", "EUR", "HKD", "HUF", "ILS", "INR", "JPY", "MYR", "MXN", "NOK", "NZD", "PHP", "PLN", "GBP", "SEK", "CHF", "TWD", "THB", "TRY", "CNY", "KRW", "RUB", "SGD", "CLP", "IDR", "PKR", "ZAR");
        $body = wp_remote_retrieve_body($request);
        $conversion_data = json_decode($body);

        if (isset($conversion_data->rates)) {
            $conversion_data = (array) $conversion_data->rates;
        } else {
            $conversion_data = array();
        }

        if (is_array($conversion_data) && count($conversion_data) > 0) {
            foreach ($conversion_data as $key => $currency_price) {
                if (in_array($key, $currency_ids)) {
                    $conversions[$key] = $currency_price;
                }

            }

            uksort($conversions, function ($key1, $key2) use ($currency_ids) {
                return (array_search($key1, $currency_ids) > array_search($key2, $currency_ids)) ? 1 : -1;
            });

            set_transient('cmc_usd_conversions', $conversions, 12 * HOUR_IN_SECONDS);
        }
    }

    if ($currency == "all") {

        return $conversions;

    } else {
        if (isset($conversions[$currency])) {
            return $conversions[$currency];
        }
    }
}

function ccpw_format_number($n)
{
    $formatted = $n;
    if ($n <= -1) {
        $formatted = number_format($n, 2, '.', ',');
    } else if ($n < 0.50) {
        $formatted = number_format($n, 6, '.', ',');
    } else {
        $formatted = number_format($n, 2, '.', ',');
    }
    return $formatted;
}

// object to array conversion
function ccpw_objectToArray($d)
{
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
         * Return array converted to object
         * Using __FUNCTION__ (Magic constant)
         * for recursive call
         */
        return array_map(__FUNCTION__, $d);
    } else {
        // Return array
        return $d;
    }
}

// currencies symbol
function ccpw_currency_symbol($name)
{
    $cc = strtoupper($name);
    $currency = array(
        "USD" => "&#36;", //U.S. Dollar
        "CLP" => "&#36;", //CLP Dollar
        "SGD" => "S&#36;", //Singapur dollar
        "AUD" => "&#36;", //Australian Dollar
        "BRL" => "R&#36;", //Brazilian Real
        "CAD" => "C&#36;", //Canadian Dollar
        "CZK" => "K&#269;", //Czech Koruna
        "DKK" => "kr", //Danish Krone
        "EUR" => "&euro;", //Euro
        "HKD" => "&#36", //Hong Kong Dollar
        "HUF" => "Ft", //Hungarian Forint
        "ILS" => "&#x20aa;", //Israeli New Sheqel
        "INR" => "&#8377;", //Indian Rupee
        "IDR" => "Rp", //Indian Rupee
        "KRW" => "&#8361;", //WON
        "CNY" => "&#165;", //CNY
        "JPY" => "&yen;", //Japanese Yen
        "MYR" => "RM", //Malaysian Ringgit
        "MXN" => "&#36;", //Mexican Peso
        "NOK" => "kr", //Norwegian Krone
        "NZD" => "&#36;", //New Zealand Dollar
        "PHP" => "&#x20b1;", //Philippine Peso
        "PLN" => "&#122;&#322;", //Polish Zloty
        "GBP" => "&pound;", //Pound Sterling
        "SEK" => "kr", //Swedish Krona
        "CHF" => "Fr", //Swiss Franc
        "TWD" => "NT&#36;", //Taiwan New Dollar
        "PKR" => "Rs", //Rs
        "THB" => "&#3647;", //Thai Baht
        "TRY" => "&#8378;", //Turkish Lira
        "ZAR" => "R", //zar
        "RUB" => "&#8381;", //rub
    );

    if (array_key_exists($cc, $currency)) {
        return $currency[$cc];
    }
}

 	
/*
|--------------------------------------------------------------------------
|  check admin side post type page
|--------------------------------------------------------------------------
*/
function ccpw_get_post_type_page() {
	global $post, $typenow, $current_screen;

		if ( $post && $post->post_type ){
				return $post->post_type;
		}elseif( $typenow ){
				return $typenow;
		}elseif( $current_screen && $current_screen->post_type ){
				return $current_screen->post_type;
		}
		elseif( isset( $_REQUEST['post_type'] ) ){
				return sanitize_key( $_REQUEST['post_type'] );
		}
		elseif ( isset( $_REQUEST['post'] ) ) {
		return get_post_type( filter_var($_REQUEST['post'], FILTER_SANITIZE_STRING) );
		}
		return null;
}



function display_live_preview(){
    $output='';
    if( isset($_REQUEST['post']) && !is_array($_REQUEST['post'])){
     
      $id = filter_var($_REQUEST['post'], FILTER_SANITIZE_STRING);
     
      $type = get_post_meta($id, 'type', true);
         $output='<p><strong class="micon-info-circled"></strong>'.__('Backend preview may be a little bit different from frontend / actual view. Add this shortcode on any page for frontend view - ','ccpwx').'<code>[ccpw id='.$id.']</code></p>'.do_shortcode("[ccpw id='".$id."']");
         $output.='<script type="text/javascript">
         jQuery(document).ready(function($){
           $(".ccpw-ticker-cont").fadeIn();     
         });
         </script>
         <style type="text/css">
         .ccpw-footer-ticker-fixedbar, .ccpw-header-ticker-fixedbar{
           position:relative!important;
         }
         .tickercontainer li{
             float:left!important;
             width:auto!important;
         }
         .ccpw-container-rss-view ul li.ccpw-news {
          margin-bottom: 30px;
          float: none;
          width: auto;
      }
      .ccpw-news-ticker .tickercontainer li{
        width: auto!important;
      }
         </style>';
         return $output;
     
       }else{
      return  $output='<h4><strong class="micon-info-circled"></strong> '.__('Publish to preview the widget.','ccpwx').'</h4>';
  
       }
  }


function update_tbl_settings($post_id){
    $old_settings= get_post_meta($post_id,'display_currencies_for_table', true);
   
    if($old_settings){
        switch($old_settings){
            case 'top-10':
            $newVal = 10;
            break;
            case 'top-50':
            $newVal=50;
            break;
            case 'top-100':
            $newVal =100;
            break;
            case 'top-200':
            $newVal =200;
            break;
            case 'all':
            $newVal = 250;
            break;
            default:
            $newVal=10;
        } 
        update_post_meta($post_id,'show-coins',$newVal);
        delete_post_meta( $post_id,'display_currencies_for_table');
    }
}

function ccpw_set_checkbox_default_for_new_post($default)
{
    return isset($_GET['post']) ? '' : ($default ? (string) $default : '');
}

