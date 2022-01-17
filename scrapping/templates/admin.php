<div class="wrap">

    <h1>Paramètres</h1>
    <?php settings_errors(); ?>

    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-1">Gérer les paramètres</a></li>
        <li><a href="#tab-2">Gérer les taches crons</a></li>
    </ul>

    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
            <?php

            ?>
            <h2>Modifier les paramètres</h2>

            <form method="post" action="">
                <?php wp_nonce_field('parametre', 'parametre-nonce'); ?>

                <label for="nb_curl">Nombre de thread ( curl )</label>
                <input id="nb_curl" name="nb_curl" type="number" value="<?php echo get_option('nb_threads') ?>">

                <label for="email_notif">E-mail de reception des notifications</label>
                <input id="email_notif" name="email_notif" type="email" value="<?php echo get_option("email_notif") ?>">

                <label for="tel_notif">Numéro de reception des notifications</label>
                <input id="tel_notif" name="tel_notif" type="tel" value="<?php echo get_option("tel_notif") ?>">

                <label for="nb_archive">Nombres d'archives a garder pour chaque requete</label>
                <input id="nb_archive" name="nb_archive" type="number" value="<?php echo get_option("nb_archives") ?>">

                <input type="submit" name="modifier" value="modifier">
            </form>

        </div>

        <div id="tab-2" class="tab-pane">
            <h2>Gérer les taches crons</h2>
            <form method="POST" action="#">
                <?php wp_nonce_field('cron_monitoring', 'cron-nonce'); ?>
                <label>Mettre en pause les taches crons</label>
                <input id="radio1" name="radio" type="radio" value="oui" <?php if(get_option('choix_globale') == "oui"){?> checked="checked" <?php } ?>/>
                <label for="radio1">Yes</label>
                <input id="radio2" name="radio" type="radio"  value="non" <?php if(get_option('choix_globale') == "non"){?> checked="checked" <?php } ?>/>
                <label for="radio2">Non</label>
                <input type="submit" name="btn_cron" value="modifier">
            </form>
        </div>
    </div>
</div>