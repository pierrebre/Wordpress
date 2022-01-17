<?php
/**
 * Create response for datatable AJAX request
 */


function ccpw_get_ajax_data(){

  if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'ccpwf-tbl-widget' ) ){
        die ('Please refresh window and check it again');
    }

    $rtype    = $_REQUEST['rtype']? filter_var($_REQUEST['rtype'], FILTER_SANITIZE_STRING):0;
		$start_point    = $_REQUEST['start']? filter_var($_REQUEST['start'], FILTER_SANITIZE_STRING):0;
        $data_length    = $_REQUEST['length']?filter_var($_REQUEST['length'], FILTER_SANITIZE_STRING):10;
        $current_page   = (int)$_REQUEST['draw']?filter_var($_REQUEST['draw'], FILTER_SANITIZE_STRING):1;
        $requiredCurrencies = ccpw_set_default_if_empty(filter_var($_REQUEST['requiredCurrencies'], FILTER_SANITIZE_STRING),10);
        $fiat_currency = $_REQUEST['currency'] ? filter_var($_REQUEST['currency'], FILTER_SANITIZE_STRING) :'USD';
        $fiat_currency_rate = $_REQUEST['currencyRate'] ? filter_var($_REQUEST['currencyRate'], FILTER_SANITIZE_STRING) : 1;
        $coin_no=$start_point+1;
        $coins_list=array();
        $order_col_name = 'market_cap';
        $order_type ='DESC';
        $DB = new ccpw_database;
        $Total_DBRecords = '1000';
        $coins_request_count=$data_length+$start_point;
   
       if($rtype=="top"){
            $coindata= $DB->get_coins( array("number"=>$data_length,'offset'=> $start_point,'orderby' => $order_col_name,
            'order' => $order_type
          ));
        }else{
            $coinslist    =$_REQUEST['coinslist']? $_REQUEST['coinslist']:[] ;
            $coindata= $DB->get_coins( 
                array('coin_id' =>$coinslist,
                'number' => $data_length,
                'orderby'=> $order_col_name,
                'order' => $order_type));
        }

          $coin_ids=array();
          if($coindata){
            foreach($coindata as $coin){
                 $coin_ids[]= $coin->coin_id;
            }
        }
   
		$response = array();
        $coins = array();
        $bitcoin_price = get_transient('ccpw_btc_price');
        $coins_list=array();
       
        if($coindata){

            foreach($coindata as $coin){
                $coin = (array)$coin;
                $coins['rank'] = $coin_no;
                $coins['id']    =   $coin['coin_id'];
                if( ccpw_get_coin_logo($coin['coin_id'], $size = 32)==false){
                    $coins['logo'] ='<img  alt="'.esc_attr($coin['name']).'" src="'.$coin['logo'].'">';
                  }else{
                    $coins['logo'] = ccpw_get_coin_logo( $coin['coin_id'] );
                  }
                $coins['symbol']= strtoupper($coin['symbol']);
                $coins['name'] = strtoupper($coin['name']);
                $coins['price'] = $coin['price'];
                if($fiat_currency=="USD"){
                    $coins['price'] = $coin['price'];
                    $coins['market_cap'] = $coin['market_cap'];
                    $coins['total_volume'] = $coin['total_volume'];
                    $c_price=$coin['price'];
                }else{
                    $coins['price'] = $coin['price']* $fiat_currency_rate;
                    $coins['market_cap'] = $coin['market_cap'] * $fiat_currency_rate;
                    $coins['total_volume'] = $coin['total_volume'] * $fiat_currency_rate;
                }
                $coins['change_percentage_24h'] = number_format($coin['percent_change_24h'],2,'.','');
                $coins['market_cap'] = $coin['market_cap'];
                $coins['total_volume'] = $coin['total_volume'];
                $coins['supply'] = $coin['circulating_supply'];

                $coin_no++;
                $coins_list[]= $coins;

            }   //end of foreach-block
        }   //end of if-block
       
        $response = array(
        "draw"=>$current_page,
        "recordsTotal"=>$Total_DBRecords,
        "recordsFiltered"=> $requiredCurrencies,
        "data"=>$coins_list
       );
		echo json_encode( $response );
}