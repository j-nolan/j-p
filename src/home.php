<?php
/* Homescreen for the plugin
 * Date: August 2014
 * Author: James Nolan */
?>
<div class="wrap">
    <h2>Journées pédagogiques</h2>
    <p>Bienvenue dans l'administration des journées pédagogiques !</p>
    <p>Cet outil a été conçu pour la Société Vaudoise des Maîtres de Musique (SVMM) dans le but de faciliter l'organisation de ses journées pédagogiques.</p>
    <p>Pour toute réclamation ou demande d'assistance, contactez James Nolan (<a href="mailto:J.Nolan@otherwise.ch">J.Nolan@otherwise.ch</a>).</p>
    <ul>
        <li><a href="<?php echo admin_url( 'admin.php?page=journee-pedagogique-create' ); ?>">Créer une nouvelle journée pédagogique</a></li>
        <li><a href="<?php echo admin_url( 'admin.php?page=journee-pedagogique-participants' ); ?>">Consulter la liste des participants</a></li>
        <li><a href="<?php echo admin_url( 'admin.php?page=journee-pedagogique-statistiques' ); ?>">Consulter les statistiques sur les inscriptions</a></li>
        <li><a href="<?php echo admin_url( 'admin.php?page=journee-pedagogique-repartition' ); ?>">Répartir automatiquement les participants</a></li>
    </ul>
</div>