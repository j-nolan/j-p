<?php
/* List all conference participants. Generates a table with all available
 * information about each participant, including prefered workshops.
 * Project: j-p
 * Date: August 2014
 * Author: James Nolan */
?>

<?php
    global $wpdb;

    // Get pedagogical day
    $jp_id = get_selected_jp();
    
    // Get inscriptions
    $query = 'SELECT t1.*, t2.*
              FROM svmm_inscriptions_jp as t1
                LEFT JOIN svmm_teachers as t2
                    ON t1.teacherID = t2.ID
                WHERE dateID = ' . $jp_id . '
                ORDER BY t1.id';
                
    $inscriptions = $wpdb->get_results( $query );
    
    // Get choices name

    $query = 'SELECT id, name
              FROM svmm_pedagogical_activities
              WHERE pedagogicalDay = ' . $jp_id;
              
    $res = $wpdb->get_results( $query );
    $activities = array();
    foreach ($res as $l) {
        // Crop activity name to 15chr
        if (strlen($l->name) > 15) {
            $l->name = substr($l->name, 0, 15) . '...';
        }
        $activities[$l->id] = $l->name;
    }
?>
<div class="wrap">
    <h2>Participants</h2>
    <?php generate_select_jp_form(); ?>
    <p><?php echo count( $inscriptions ); ?> inscrits (<?php echo date_i18n( 'd F Y à H:i', time() ); ?>)</p>
    <table class="wp-list-table widefat fixed bookmarks" cellspacing="0">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name desc">
                    <strong><span>Prénom, nom</span></strong>
                </th>
                <th scope="col" class="manage-column column-name desc">
                    <strong><span>E-mail</span></strong>
                </th>
                <th scope="col" class="manage-column column-name desc">
                    <strong><span>Adresse 1</span></strong><br />
                    <span>Adresse 2</span>
                </th>
                <th scope="col" class="manage-column column-name desc">
                    <strong><span>Ville</span></strong><br />
                    <span>Code postal</span>
                </th>
                <th scope="col" class="manage-column column-name desc">
                    <strong><span>Téléphone</span></strong><br />
                    <span>Téléphone 2</span>
                </th>
                <th scope="col" class="manage-column column-name desc">
                    <strong><span>Canton</span></strong><br />
                    <span>Etablissement 1</span><br />
                    <em><span>Etablissement 2</span></em>
                </th>
                <th scope="col" class="manage-column column-name desc">
                    <strong><span>Membre ?</span></strong><br />
                    <em>Y = Oui, N = Non, W = Le souhaite</em>
                </th>
                <th scope="col" class="manage-column column-name desc">
                    <strong><span>Date</span></strong><br />
                    <span>Heure</span>
                </th>
                <th scope="col" class="manage-column column-name desc">
                    <strong><span>Choix 1</span></strong><br />
                    <span>Choix 2</span><br />
                    <span>Choix 3</span><br />
                    <span>Choix 4</span><br />
                </th>
                <th scope="col" class="manage-column column-name desc">
                    <strong><span>Repas de midi</span></strong><br />
                </th>
                <th scope="col" class="manage-column column-name desc">
                    <strong><span>Transport</span></strong><br />
                </th>
            </tr>
        </thead>
        <tbody id="the-list">
            <?php foreach( $inscriptions as $inscription ) : ?>
            <tr valign="middle" class="alternate">
                <td class="column-name">
                    <strong><?php echo stripslashes( esc_html( $inscription->lastname . ' ' . $inscription->firstname ) );?></strong>
                </td>
                <td class="column-name">
                    <strong><?php echo stripslashes( esc_html( $inscription->email ) );?></strong>
                </td>
                <td class="column-name">
                    <strong><?php echo stripslashes( esc_html( $inscription->address1 ) );?></strong><br />
                    <?php echo stripslashes( esc_html( $inscription->address2 ) );?>
                </td>
                <td class="column-name">
                    <strong><?php echo stripslashes( esc_html( $inscription->city ) );?></strong><br />
                    <?php echo stripslashes( esc_html( $inscription->cp ) );?>
                </td>
                <td class="column-name">
                    <strong><?php echo stripslashes( esc_html( $inscription->phone1 ) );?></strong><br />
                    <?php echo stripslashes( esc_html( $inscription->phone2 ) );?>
                </td>
                <td class="column-name">
                    <strong><?php echo stripslashes( esc_html( $inscription->canton ) );?></strong><br />
                    <?php echo stripslashes( esc_html( $inscription->workplace1 ) );?><br />
                    <em><?php echo stripslashes( esc_html( $inscription->workplace2 ) );?></em>
                </td>
                <td class="column-name">
                    <strong><?php echo stripslashes( esc_html( $inscription->type ) );?></strong><br />
                </td>
                <td class="column-name">
                    <strong><?php echo date_i18n('d F Y', strtotime( $inscription->date ) );?></strong><br />
                    <?php echo date_i18n('H:i', strtotime( $inscription->date ) );?>
                </td>
                <td class="column-name">
                    <strong><?php echo $activities[$inscription->choice1];?></strong>, <br />
                    <?php echo $activities[$inscription -> choice2]; ?>,<br />
                    <?php echo $activities[$inscription -> choice3]; ?>,<br />
                    <?php echo $activities[$inscription -> choice4]; ?>
                </td>
                <td class="column-name">
                    <strong><?php echo $inscription->repas ;?></strong>
                </td>
                <td class="column-name">
                    <strong><?php echo $inscription->transport ;?></strong>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>