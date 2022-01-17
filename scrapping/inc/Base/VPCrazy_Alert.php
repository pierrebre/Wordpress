<?php

/**
 * @link              Pierre VPCrazy
 * @since             1.0.0
 * @package           Scrapping
 */

namespace Inc\Base;



class VPCrazy_Alert
{
	/**
	 * getUserHash
	 * Genère un hash en fonction de l'heure + temps + agent
	 * et le pose en cookie pour identifier l'user non connecté
	 * et le retourne pour être associé à al variable transient
	 * @return void
	 */
	public static function getUserHash()
	{
		$hashConnu = $_COOKIE["wp_vpcrazy_msg"];
		if (!$hashConnu || mb_strlen($hashConnu) != 32) {
			$time = time();
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			$agent = $_SERVER['HTTP_USER_AGENT'];
			$str = $time . '-' . $ip . '-' . $agent;
			$hashConnu = md5($str);
			setcookie("wp_vpcrazy_msg", $hashConnu, time() + (3600 * 24 * 365));  /* expire dans 1 an */
		}
		return $hashConnu;
	}
	/**
	 * Définit le message d'alerte Bootstrap pour la requete en cours
	 *
	 * @param  mixed $msg
	 * @param  mixed $typealert
	 * @return void
	 */
	public static function set($msg, $typealert)
	{
		$current_wp_vpcrazy_msg = get_transient("wp_vpcrazy_msg" . self::getUserHash());
		$current_wp_vpcrazy_msg = $current_wp_vpcrazy_msg ? $current_wp_vpcrazy_msg : '';
		set_transient("wp_vpcrazy_msg" . self::getUserHash(), $current_wp_vpcrazy_msg . '<div class="alert alert-dismissible alert-' . $typealert . ' alert-dismissible fade show" role="alert">' . $msg . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>', 45);
	}
	/**
	 * Affiche le message Bootstrap de l'alerte
	 *
	 * @return void
	 */
	public static function get()
	{
		$wp_vpcrazy_msg = get_transient("wp_vpcrazy_msg" . self::getUserHash());
		if ($wp_vpcrazy_msg) {
			echo $wp_vpcrazy_msg;
		}
		delete_transient('wp_vpcrazy_msg' . self::getUserHash());
	}
	/**
	 * Indique s'il reste un message dans le tampon pour la requete
	 * @return void
	 */
	public static function isEmpty()
	{
		$current_wp_vpcrazy_msg = get_transient("wp_vpcrazy_msg" . self::getUserHash());
		return $current_wp_vpcrazy_msg ? true : false;
	}
}