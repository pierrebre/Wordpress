<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en « wp-config.php » et remplir les
 * valeurs.
 *
 * Ce fichier contient les réglages de configuration suivants :
 *
 * Réglages MySQL
 * Préfixe de table
 * Clés secrètes
 * Langue utilisée
 * ABSPATH
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'odonisse' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/**
 * Type de collation de la base de données.
 * N’y touchez que si vous savez ce que vous faites.
 */
define( 'DB_COLLATE', '' );

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '5/h%B{3zRB%0;&+-#WIr><@AW;|BE]:DKlRHW sNz&m8SC$*o^/slA@cxzEk5,:P' );
define( 'SECURE_AUTH_KEY',  ':3REJ<T}0vE7aOE~lq2uoMVH#&0v6ZvXSB!Y>iZu~|]Fu)3S2Od}?FHKV_Gc?@F9' );
define( 'LOGGED_IN_KEY',    '/7^H_n,E.Wy7u*8ySo~0%cb]vvMed/[|8sWD[M)[6` pm8;~!Qx}jFCl]o)R}DRQ' );
define( 'NONCE_KEY',        'Qhj(Cdb!wa{gA^7Eh0$A~U6,9(QoJp}N`EX&0J$b@CP,Cg2%NznF:pI4~NAFj*gJ' );
define( 'AUTH_SALT',        '(U.S8VozFNX62^PwiDOS[sG7;i$8E!l#@NgS05*kPnh{Oq6pA fJWaeNX3Q2H|Gr' );
define( 'SECURE_AUTH_SALT', 'V NLlygwnsVvo4-zb4.~}C7Ca<Ud@ N_`K(MNN^ y~aSVLg~6Q/Sx.C 3u.^f&%R' );
define( 'LOGGED_IN_SALT',   'B>524gGPek|o5lZRz]<^Hy]E(9t:fEBZJMq7(pzYA(#ruK~%vw05`;q?QM@5^~66' );
define( 'NONCE_SALT',       't)GrbP~wLtDH}w=[{Z Uu>J=!-JR.-|PGB66Y_gXXr-u|{lmJ&tH?1 <8{@$.SPy' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( ! defined( 'ABSPATH' ) )
  define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once( ABSPATH . 'wp-settings.php' );
