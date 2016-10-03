<?php
/* Display statistics to give an overview of the number of people that are
 * have interest as 1-4 choices for each activity.
 * Project: j-p
 * Date: August 2014
 * Author: James Nolan */
?>
<?php require_once( 'functions.php' ); ?>
<div class="wrap">
    <h2>Statistiques</h2>
    <p>Ces graphiques permettent d'évaluer le nombre de participants ayant
        manifesté leur intérêt comme premier, deuxième, troisième et quatrième
        choix pour chaque activité.</p>

    <?php generate_select_jp_form(); ?>
    
    <?php
        // Get all preferences lists and activities
        $prefLists = get_preferences_list();
        $activities = get_available_activities();

        $activityById = array();
        foreach ( $activities as $activity ) {
            $activityById[$activity->id] = $activity->name;
        }
        
        // For each activity, count subscriptions that have subscription 1, 2,
        // 3, and 4
        $stats = array();
        
        foreach ( $prefLists as $prefList )  {
            $stats[1][$prefList->choice1]++;
            $stats[2][$prefList->choice2]++;
            $stats[3][$prefList->choice3]++;
            $stats[4][$prefList->choice4]++;
        }
        

        // Display
    ?>

    <style type="text/css">
    <?php foreach ( $activities as $k => $activity ) : ?>
    #activity-<?php echo $activity->id; ?> {
        background-color:#<?php echo ($k%2 == 0 ? '6ba4de' : '578fc9'); ?>;
    }
    <?php endforeach; ?>
    </style>

    <div id="stats">
    <?php foreach ( $stats as $choice => $activities ) : ?>
        <div id="choice<?php echo $choice; ?>" class="choice">
        <p class="choice-title">Choix <?php echo $choice; ?></p>
        <?php ksort( $activities ); ?>
        <?php foreach ( $activities as $activity => $count ) : ?>
        <div id="activity-<?php echo $activity; ?>"
            class="activity"
            style="width:<?php echo 100 * $count / max( $activities ); ?>%"
        >
            <strong>&nbsp;<?php echo $activityById[$activity]
                . '</strong><br />&nbsp;' . $count . ' personnes'; ?></div>
        <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    <div style="clear:both;"></div>
    </div>
</div>