<?php

/**
 * @link              Pierre VPCrazy
 * @since             1.0.0
 * @package           Scrapping
 */

namespace Inc\Base;

use Inc\Base\VPCrazy_Alert;

class CustomPostTypeController
{

    public function register()
    {
        add_action('init', array($this, 'create_cpt_resultats_monitoring'));
        add_action('init', array($this, 'create_cpt_monitoring'));

        add_action('add_meta_boxes', array($this, 'metabox_cpt_resultats_monitoring'), 5, 2);
        add_action('add_meta_boxes', array($this, 'metabox_cpt_monitoring'), 5, 2);

        add_action('save_post', array($this, 'enregistrement_metaboxe_monitoring'));
        add_action('save_post', array($this, 'enregistrement_metaboxe_resultats_monitoring'));
    }

    function create_cpt_monitoring()
    {

        // On rentre les différentes dénominations de notre custom post type qui seront affichées dans l'administration//

        $labels = array(
            // Le nom au pluriel
            'name'                => _x('Monitoring', 'Post Type General Name'),
            // Le nom au singulier
            'singular_name'       => _x('Monitoring', 'Post Type Singular Name'),
            // Le libellé affiché dans le menu
            'menu_name'           => __('Monitoring'),


            // Les différents libellés de l'administration
            'all_items'           => __('Toutes les pages'),
            'view_item'           => __('Voir les pages'),
            'add_new_item'        => __('Ajouter une nouvelle pages'),
            'add_new'             => __('Ajouter'),
            'edit_item'           => __('Editer la page'),
            'update_item'         => __('Modifier la page'),
            'search_items'        => __('Rechercher une page'),
            'not_found'           => __('Non trouvée'),
            'not_found_in_trash'  => __('Non trouvée dans la corbeille'),
        );

        // On peut définir ici d'autres options pour notre custom post type

        $args = array(
            'label'               => __('Monitoring'),
            'description'         => __('Toutes les pages'),
            'labels'              => $labels,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-admin-site',
            // On définit les options disponibles dans l'éditeur de notre custom post          type ( un titre, un auteur...)//

            'supports'            => array('title', 'editor', 'excerpt', 'author',         'thumbnail', 'comments', 'revisions', 'custom-fields',),

            /* Différentes options supplémentaires*/

            'show_in_rest'        => true,
            'hierarchical'        => false,
            'public'              => true,
            'has_archive'         => true,
            'rewrite'              => array('slug' => 'monitoring'),


        );

        // On enregistre notre custom post type qu'on nomme ici "réalisations" et ses arguments
        register_post_type('monitoring', $args);
    }

    function create_cpt_resultats_monitoring()
    {

        // On rentre les différentes dénominations de notre custom post type qui seront affichées dans l'administration//

        $labels = array(
            // Le nom au pluriel
            'name'                => _x('Résultat Monitoring', 'Post Type General Name'),
            // Le nom au singulier
            'singular_name'       => _x('Résultat Monitoring', 'Post Type Singular Name'),
            // Le libellé affiché dans le menu
            'menu_name'           => __('Résultat Monitoring'),


            // Les différents libellés de l'administration
            'all_items'           => __('Tout les résultat'),
            'view_item'           => __('Voir les résultat'),
            'add_new_item'        => __('Ajouter une nouvelle pages'),
            'add_new'             => __('Ajouter'),
            'edit_item'           => __('Editer la page'),
            'update_item'         => __('Modifier la page'),
            'search_items'        => __('Rechercher une page'),
            'not_found'           => __('Non trouvée'),
            'not_found_in_trash'  => __('Non trouvée dans la corbeille'),
        );

        // On peut définir ici d'autres options pour notre custom post type

        $args = array(
            'label'               => __('Résultat Monitoring'),
            'description'         => __('Tous les résultats'),
            'labels'              => $labels,
            'menu_position' => 10,
            'menu_icon' => 'dashicons-admin-multisite',
            // On définit les options disponibles dans l'éditeur de notre custom post          type ( un titre, un auteur...)//

            'supports'            => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', 'trackbacks'),

            /* Différentes options supplémentaires*/

            'show_in_rest'        => true,
            'hierarchical'        => false,
            'public'              => true,
            'has_archive'         => true,
            'rewrite'              => array('slug' => 'resultats_monitoring'),


        );

        // On enregistre notre custom post type qu'on nomme ici "réalisations" et ses arguments
        register_post_type('resultats', $args);
    }

    function metabox_cpt_monitoring($post_type, $post)
    {
        add_meta_box(
            'post_custom_monitoring',
            'Form',
            array($this, 'metabox_cpt_monitoring_callback'),
            'monitoring', //Lien entre la metabox et le cpt
            'normal',
            'core'
        );
    }

    public function metabox_cpt_monitoring_callback($post)
    {
        $url = get_post_meta($post->ID, '_url', true);
        $selecteur = get_post_meta($post->ID, '_selecteur', true);
        $intervalle = get_post_meta($post->ID, '_intervalle', true);
        $daystart = get_post_meta($post->ID, '_day', true);
        $hourstart = get_post_meta($post->ID, '_hour', true);
        $etat = get_post_meta($post->ID, '_etat', true);
        $resultat = get_post_meta($post->ID, '_resultat', true);
        $selecteurhtml = get_post_meta($post->ID, '_selecteurhtml', true);
        $selecteurcss = get_post_meta($post->ID, '_selecteurcss', true);

?>
        <label for="url_monitoring">Url monitoring</label>
        <input id="url_monitoring" name="url_monitoring" type="url" value="<?php echo $url ?>">
        <br><br>
        <label for="selecteur_html_monitoring">Element html a monitorer</label>
        <input id="selecteur_html_monitoring" name="selecteur_html_monitoring" placeholder="ex : div" type="text" value="<?php echo $selecteurhtml ?>">
        <br><br>
        <label for="selecteur"> Type selecteur CSS : </label>
        <label for="selecteur1">Class</label>
        <input id="selecteur1" name="selecteur" type="radio" value="class" <?php checked($selecteurcss, 'class');  ?> />
        <label for="selecteur2">Id</label>
        <input id="selecteur2" name="selecteur" type="radio" value="id" <?php checked($selecteurcss, 'id'); ?> />
        <br><br>
        <label for="selecteur_nom_monitoring">Nom selecteur css</label>
        <input id="selecteur_nom_monitoring" name="selecteur_nom_monitoring" type="text" value="<?php echo $selecteur ?>">

        <br><br>
        <label for="monitoring_start">Début du monitoring</label>
        <input id="monitoring_start" name="day_start" type="date" value="<?php echo $daystart ?>">
        <input id="monitoring_start" name="hour_start" type="time" value="<?php echo $hourstart ?>">
        <br><br>
        <label for="intervalle_monitoring">Intervalle entre chaque monitoring ( heures )</label>
        <input id="intervalle_monitoring" name="intervalle_monitoring" type="number" value="<?php echo $intervalle ?>">
        <br><br>
        <label for="etat">Etat monitoring</label>
        <input id="etat1" name="etat" type="radio" value="actif" <?php checked($etat, 'actif');  ?>>
        <label for="etat1">Actif</label>
        <input id="etat2" name="etat" type="radio" value="inactif" <?php checked($etat, 'inactif'); ?> />
        <label for="etat2">Inactif</label>
        <br><br>
        <label for="resultat_monitoring">Choix de la reception des résultats</label>
        <input id="tel" name="resultat_monitoring" type="radio" value="telephone" <?php checked($resultat, 'telephone'); ?> />
        <label for="tel">Téléphone</label>
        <input id="email" name="resultat_monitoring" type="radio" value="email" <?php checked($resultat, 'email') ?> />
        <label for="email">E-mail</label>
        <br><br>

    <?php
    }

    function enregistrement_metaboxe_monitoring($post_id)
    {
        if (isset($_POST['url_monitoring'])) {
            update_post_meta($post_id, '_url', sanitize_text_field($_POST['url_monitoring']));
        }
        if (isset($_POST['selecteur_nom_monitoring'])) {
            update_post_meta($post_id, '_selecteur', sanitize_text_field($_POST['selecteur_nom_monitoring']));
        }
        if (isset($_POST['intervalle_monitoring'])) {
            update_post_meta($post_id, '_intervalle', sanitize_text_field($_POST['intervalle_monitoring']));
        }
        if (isset($_POST['day_start'])) {
            update_post_meta($post_id, '_day', sanitize_text_field($_POST['day_start']));
        }
        if (isset($_POST['hour_start'])) {
            update_post_meta($post_id, '_hour', sanitize_text_field($_POST['hour_start']));
        }
        if (isset($_POST['etat'])) {
            update_post_meta($post_id, '_etat',  sanitize_html_class($_POST['etat']));
        }
        if (isset($_POST['resultat_monitoring'])) {
            update_post_meta($post_id, '_resultat',  sanitize_html_class($_POST['resultat_monitoring']));
        }
        if (isset($_POST['selecteur_html_monitoring'])) {
            update_post_meta($post_id, '_selecteurhtml', sanitize_text_field($_POST['selecteur_html_monitoring']));
        }
        if (isset($_POST['selecteur'])) {
            update_post_meta($post_id, '_selecteurcss',  sanitize_html_class($_POST['selecteur']));
        }
    }

    function metabox_cpt_resultats_monitoring($post_type, $post)
    {
        add_meta_box(
            'post_custom_resultats_monitoring',
            'Form',
            array($this, 'metabox_cpt_resultats_monitoring_callback'),
            'resultats', //Lien entre la metabox et le cpt
            'normal',
            'core'
        );
    }

    function metabox_cpt_resultats_monitoring_callback($post)
    {
        $hash_resultat = get_post_meta($post->ID, '_hash_resultat', true);
    ?>
        <label for="hash_resultat">hash :</label>
        <input id="hash_resultat" name="hash_resultat" value="<?php echo $hash_resultat ?>">
<?php
    }

    function enregistrement_metaboxe_resultats_monitoring($post_id)
    {
        if (isset($_POST['hash_resultat'])) {
            update_post_meta($post_id, '_hash_resultat', sanitize_text_field($_POST['hash_resultat']));
        }
    }
}
