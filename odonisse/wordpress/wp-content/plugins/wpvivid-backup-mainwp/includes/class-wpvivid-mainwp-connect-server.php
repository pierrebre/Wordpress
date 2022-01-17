<?php

class Mainwp_WPvivid_Connect_server
{
    private $url='https://pro.wpvivid.com/wc-api/wpvivid_api';
    private $update_url='http://download.wpvivid.com';
    private $public_key;

    public function __construct()
    {

    }

    public function get_mainwp_status($email=false,$user_info,$encrypt_user_info,$use_token=false,$get_key=false)
    {
        global $mainwp_wpvivid_extension_activator;
        $login_options = $mainwp_wpvivid_extension_activator->get_global_login_addon();
        if($get_key)
            $public_key='';
        else{
            $public_key='';
            if($login_options !== false){
                if(isset($login_options['wpvivid_connect_key']) && !empty($login_options['wpvivid_connect_key'])){
                    $public_key = $login_options['wpvivid_connect_key'];
                }
            }
        }

        if(empty($public_key)) {
            $public_key=$this->get_key();
            if($public_key===false)
            {
                $ret['result']='failed';
                $ret['error']='An error occurred when connecting to WPvivid Backup Pro server. Please try again later or contact us.';
                return $ret;
            }
            $login_options['wpvivid_connect_key'] = $public_key;
            $mainwp_wpvivid_extension_activator->set_global_login_addon($login_options);
        }

        $crypt=new Mainwp_WPvivid_crypt($public_key);

        if($encrypt_user_info) {
            if($use_token)
            {
                $encrypt_user_info=$crypt->encrypt_user_token($user_info);
                $encrypt_user_info=base64_encode($encrypt_user_info);
            }
            else
            {
                $encrypt_user_info=$crypt->encrypt_user_info($user_info);
                $encrypt_user_info=base64_encode($encrypt_user_info);
            }
        }
        else {
            $encrypt_user_info=$user_info;
        }

        $crypt->generate_key();

        $json['user_info'] = $encrypt_user_info;
        $json['domain'] = strtolower(home_url());
        $json=json_encode($json);
        $data=$crypt->encrypt_message($json);

        $action='get_mainwp_status_v2';
        $url=$this->url;
        $url.='?request='.$action;
        $url.='&data='.rawurlencode(base64_encode($data));
        $options=array();
        $options['timeout']=30;
        $request=wp_remote_request($url,$options);

        if(!is_wp_error($request) && ($request['response']['code'] == 200)) {
            $json= wp_remote_retrieve_body($request);
            $body=json_decode($json,true);
            if(is_null($body)) {
                $ret['result']='failed';
                $ret['error']='Decoding json failed. Please try again later.';
                return $ret;
            }
            if(isset($body['token'])) {
                $encrypt_user_info=$crypt->encrypt_user_token($body['token']);
                $encrypt_user_info=base64_encode($encrypt_user_info);
                $info['token']=$encrypt_user_info;
            }
            return $body;
        }
        else {
            $ret['result']='failed';
            if ( is_wp_error( $request ) ) {
                $error_message = $request->get_error_message();
                $ret['error']="Sorry, something went wrong: $error_message. Please try again later or contact us.";
            }
            else {
                $ret['error']=$request;
            }
            return $ret;
        }
    }

    public function mwp_wpvivid_get_site_url($site_id)
    {
        global $mainwp_wpvivid_extension_activator;
        $site_url = false;
        $websites=$mainwp_wpvivid_extension_activator->get_websites_ex();
        foreach ( $websites as $website ){
            if($site_id === $website['id']){
                $site_url = $website['url'];
                $site_url = rtrim($site_url, '/');
                break;
            }
        }
        return $site_url;
    }

    public function login($email=false,$user_info,$site_id,$encrypt_user_info,$use_token=false,$get_key=false)
    {
        global $mainwp_wpvivid_extension_activator;
        $site_url = $this->mwp_wpvivid_get_site_url($site_id);
        if($site_url === false){
            $ret['result']='failed';
            $ret['error']='Failed to get child site url.';
            return $ret;
        }

        $login_options = $mainwp_wpvivid_extension_activator->get_global_login_addon();
        if($get_key)
            $public_key='';
        else{
            $public_key='';
            if($login_options !== false){
                if(isset($login_options['wpvivid_connect_key']) && !empty($login_options['wpvivid_connect_key'])){
                    $public_key = $login_options['wpvivid_connect_key'];
                }
            }
        }

        if(empty($public_key)) {
            $public_key=$this->get_key();
            if($public_key===false)
            {
                $ret['result']='failed';
                $ret['error']='An error occurred when connecting to WPvivid Backup Pro server. Please try again later or contact us.';
                return $ret;
            }
            $login_options['wpvivid_connect_key'] = $public_key;
            $mainwp_wpvivid_extension_activator->set_global_login_addon($login_options);
        }

        $crypt=new Mainwp_WPvivid_crypt($public_key);

        if($encrypt_user_info)
        {
            if($use_token)
            {
                $encrypt_user_info=$crypt->encrypt_user_token($user_info);
                $encrypt_user_info=base64_encode($encrypt_user_info);
            }
            else
            {
                $encrypt_user_info=$crypt->encrypt_user_info($user_info);
                $encrypt_user_info=base64_encode($encrypt_user_info);
            }

        }
        else
        {
            $encrypt_user_info=$user_info;
        }

        $crypt->generate_key();

        $json['user_info'] = $encrypt_user_info;
        $json['domain'] = strtolower($site_url);
        $json=json_encode($json);
        $data=$crypt->encrypt_message($json);

        $action='get_status_v2';
        $url=$this->url;
        $url.='?request='.$action;
        $url.='&data='.rawurlencode(base64_encode($data));
        $options=array();
        $options['timeout']=30;
        $request=wp_remote_request($url,$options);
        if(!is_wp_error($request) && ($request['response']['code'] == 200)) {
            $json= wp_remote_retrieve_body($request);
            $body=json_decode($json,true);
            if(is_null($body)) {
                $ret['result']='failed';
                $ret['error']='Decoding json failed. Please try again later.';
                return $ret;
            }
            if(isset($body['token'])) {
                $encrypt_user_info=$crypt->encrypt_user_token($body['token']);
                $encrypt_user_info=base64_encode($encrypt_user_info);
                $info['token']=$encrypt_user_info;
                $login_options['wpvivid_pro_user'] = $info;
                $mainwp_wpvivid_extension_activator->set_global_login_addon($login_options);
            }
            else if($use_token)
            {
                if($email !== false) {
                    $info['email'] = $email;
                }
                $info['token']=$encrypt_user_info;
                $login_options['wpvivid_pro_user'] = $info;
                $mainwp_wpvivid_extension_activator->set_global_login_addon($login_options);
            }
            return $body;
        }
        else {
            $ret['result']='failed';
            if ( is_wp_error( $request ) ) {
                $error_message = $request->get_error_message();
                $ret['error']="Sorry, something went wrong: $error_message. Please try again later or contact us.";
            }
            else {
                $ret['error']=$request;
            }
            return $ret;
        }
    }

    public function active_site($email=false,$user_info,$site_id)
    {
        global $mainwp_wpvivid_extension_activator;
        $site_url = $this->mwp_wpvivid_get_site_url($site_id);
        if($site_url === false){
            $ret['result']='failed';
            $ret['error']='Failed to get child site url.';
            return $ret;
        }

        $login_options = $mainwp_wpvivid_extension_activator->get_global_login_addon();
        $public_key='';
        if($login_options !== false){
            if(isset($login_options['wpvivid_connect_key']) && !empty($login_options['wpvivid_connect_key'])){
                $public_key = $login_options['wpvivid_connect_key'];
            }
        }
        if(empty($public_key)) {
            $public_key=$this->get_key();
            if($public_key===false) {
                $ret['result']='failed';
                $ret['error']='An error occurred when connecting to WPvivid Backup Pro server. Please try again later or contact us.';
                return $ret;
            }
            $login_options['wpvivid_connect_key'] = $public_key;
            $mainwp_wpvivid_extension_activator->set_global_login_addon($login_options);
        }

        $crypt=new Mainwp_WPvivid_crypt($public_key);

        $encrypt_user_info=$user_info;

        $crypt->generate_key();

        $json['user_info'] = $encrypt_user_info;
        $json['domain'] = strtolower($site_url);
        $json=json_encode($json);
        $data=$crypt->encrypt_message($json);

        $action='active_site';
        $url=$this->url;
        $url.='?request='.$action;
        $url.='&data='.rawurlencode(base64_encode($data));
        $options=array();
        $options['timeout']=30;
        $request=wp_remote_request($url,$options);

        if(!is_wp_error($request) && ($request['response']['code'] == 200)) {
            $json= wp_remote_retrieve_body($request);
            $body=json_decode($json,true);
            if(is_null($body)) {
                $ret['result']='failed';
                $ret['error']=$json;
                return $ret;
            }

            if(isset($body['token'])) {
                $encrypt_user_info=$crypt->encrypt_user_token($body['token']);
                $encrypt_user_info=base64_encode($encrypt_user_info);
                $info['token']=$encrypt_user_info;
                $login_options['wpvivid_pro_user'] = $info;
                $mainwp_wpvivid_extension_activator->set_global_login_addon($login_options);
            }

            return $body;
        }
        else {
            $ret['result']='failed';
            if ( is_wp_error( $request ) ) {
                $error_message = $request->get_error_message();
                $ret['error']="Sorry, something went wrong: $error_message. Please try again later or contact us.";
            }
            else {
                $ret['error']=$request;
            }
            return $ret;
        }
    }

    public function get_key()
    {
        $options=array();
        $options['timeout']=30;
        $request=wp_remote_request($this->url.'?request=get_key',$options);

        if(!is_wp_error($request) && ($request['response']['code'] == 200))
        {
            $json= wp_remote_retrieve_body($request);
            $body=json_decode($json,true);
            if(is_null($body))
            {
                return false;
            }

            if($body['result']=='success')
            {
                $public_key=base64_decode($body['public_key']);
                if($public_key==null)
                {
                    return false;
                }
                else
                {
                    return $public_key;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
}