<?php

/**
 * @link              Pierre VPCrazy
 * @since             1.0.0
 * @package           Scrapping
 */

namespace Inc\Base;

use simple_html_dom;

include('simple_html_dom.php');


class ScriptMonitoring
{
    private $html;
    private $post_resultat;

    function register()
    {
        //add_filter('cron_schedules', array($this, 'custom_cron_intervalle'), 10, 1);
        add_action('save_post', array($this, 'gestEvent'), 10, 3);
        add_action('delete_post', array($this, 'deleteEvent'), 10, 2);
        add_action('testcron', array($this, 'cronmonitoring'), 10, 3);
    }


    public function custom_cron_intervalle($post_id)
    {
        $intervalle = get_post_meta($post_id, '_intervalle', true);
        $intervalleH = 2 * HOUR_IN_SECONDS;
        $schedules[$intervalle] = array(
            'interval' => $intervalleH,
            'display'  => esc_html__('Every ' . $intervalle  . 'hours'),
        );
        return $schedules;
    }

    public function gestEvent($post_id, $post, $update)
    {
        if (!$update) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if ($post->post_type != 'monitoring') {
            return;
        }
        /*if (wp_is_post_revision($post_id)) {
            return;
        }*/
        $intervalle = get_post_meta($post_id, '_intervalle', true);
        $jourstart = get_post_meta($post_id, '_day', true);
        $hourtart = get_post_meta($post_id, '_hour', true);
        $time = strtotime($jourstart . $hourtart);
        $timestamp_start = $time - 3600;
        $args = array($post_id);
        if (!wp_next_scheduled('testcron', $args)) {
            wp_schedule_event($timestamp_start, 'hourly', 'testcron', $args);
        }
    }

    public function cronmonitoring($post_id)
    {
        // Variables 
        $nom = get_the_title($post_id);
        $url  = get_post_meta($post_id, '_url', true);
        $selecteur = get_post_meta($post_id, '_selecteur', true);
        $selecteurhtml = get_post_meta($post_id, '_selecteurhtml', true);
        $selecteurcss = get_post_meta($post_id, '_selecteurcss', true);

        // Scrapping de la page
        $this->html = new simple_html_dom();
        /* $context = stream_context_create();
        stream_context_set_params($context, array('user_agent' => 'UserAgent/1.0')); */
        $this->html = file_get_html($url/* , 0, $context*/);
        $selection =  $this->html->find($selecteurhtml . '[' . $selecteurcss . '=' . $selecteur . ']', 0)->innertext;
        $empreinte = hash('sha256', $selection);
        $test = $post_id + 2;
        $post_args = array(
            'post_id'       => $test,
            'post_title'    => $post_id . 'url : ' . $url,
            'post_content'  =>  'Résultat requete : ' . $selection,
            'post_status'   => 'publish',
            'post_type'     => 'resultats',
            'meta_input'   => array(
                '_hash_resultat' => $empreinte
            )
        );
        wp_insert_post($post_args);

        $to = 'pierrebarbe0@gmail.com';
        $subject = ' Monitoring de :'/* . $url*/;
        $body = 'Le monitoring a été modifié';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $sent = wp_mail($to, $subject, strip_tags($body), $headers);
        if ($sent) {
            var_dump($sent);
        } //message sent!
        else {
            echo "error";
        } //message wasn't sent
        //$hash = get_post_meta($test,'_hash_resultat', true);

        /* if () {

            $args_update = array(
                'ID'  => $test,
                'post_title'    => $post_id . 'url : ' . $url,
                'post_content'  =>  'Résultat requete : ' . $selection,
                'post_status'   => 'publish',
                'post_type'     => 'resultats',
                'meta_input'   => array(
                    '_hash_resultat' => $empreinte
                )
            );

            if ($empreinte != ) {
                wp_update_post($args_update);
                //Update des résultats
                //wp_mail( $to:string|array, $subject:string, $message:string, $headers:string|array, $attachments:string|array )
            }
        } else {
            // Insertion des résultats 
            wp_insert_post($post_args);
            //wp_mail( $to:string|array, $subject:string, $message:string, $headers:string|array, $attachments:string|array )
        }*/
    }

    public function deleteEvent($post_id, $post)
    {
        if ($post->post_type != 'monitoring') {
            return;
        }
        $args = array($post_id);
        wp_clear_scheduled_hook('testcron', $args);
        $post_delete = $post_id + 2;
        wp_delete_post($post_delete);
    }
}
