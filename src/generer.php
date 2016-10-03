<?php
/* Perform the assignement of workshops to participants. The process is divided
 * in two forms:
 * - The first form asks for the dataset of participants (default: the last one
 *   that was created) and the number of time  slots for each activity (default: 2).
 * - The second form asks for the number of available places for each activity and
 *   time slot, and what information should be printed in the result table
 *   (first name, last name, email)
 * When the second form is submitted, the assignement is performed and the result
 * is displayed in a table for each activity and time slot.
 * Project: j-p
 * Date: August 2014
 * Author: James Nolan */
?>

<div class="wrap">
<h2>Répartition automatique</h2>

<?php require_once('HungarianAlgorithm.class.php'); ?>
<?php require_once('Preference.class.php'); ?>
<?php require_once('functions.php'); ?>
<?php
    // Constants
    define('FIRST_CHOICE_COST', 0);
    define('SECOND_CHOICE_COST', 1);
    define('THIRD_CHOICE_COST', 2);
    define('FOURTH_CHOICE_COST', 3);
    define('OTHER_CHOICE_COST', 4);
    define('NUMBER_SCHEDULES', 4);
    
    // Algorithm
    $algo = new HungarianAlgorithm();
    
    // Get workers and tasks
    $workers = get_preferences_list();
    $tasks = get_available_activities();
?>
<?php if ( !isset( $_POST['nbSchedules'] ) || intval( isset( $_POST['nbSchedules'] ) ) < 1 || intval ( isset( $_POST['nbSchedules'] ) ) > 4 ) : ?>
<!-- Step 1 -->
<form action="" method="post" id="nbSchedules">
    <?php echo generate_select_jp_form(/* include_form = */false); ?><br />
    <label>Nombre de plages horaires : </label>
    <input type="number" min="1" max="4" name="nbSchedules" value="2" /><br />
    <input type="submit" value="suivant">
</form>
<?php else : ?>
<!-- Step 2 -->
<form action="" method="post" id="places">
    <input type="hidden" name="jp" value="<?php echo intval($_POST['jp']); ?>">
    <h3>Nombre de places pour chaque activité</h3>
    <p>Par défaut, le tableau est rempli avec le nombre de places optimal (calculé à partir des
    <a href="<?php echo admin_url('admin.php?page=journee-pedagogique-statistiques'); ?>" target="_blank">statistiques</a>)
    pour chaque activité. Vous pouvez garder ces valeurs, ou les adapter en fonction de contraintes logistiques.</p>
    <p>Dans l'idéal, le nombre de places total pour une plage horaire doit être de <strong><?php echo count( $workers ); ?></strong>.</p>
<?php
    // Number of schedules
    $nbSchedules = intval($_POST['nbSchedules']);
    
    $taskCount = array();
    $scheduleCount = array();
    foreach ( $tasks as $task ) {
        // Count number of people that has each activity as choice 1, 2, 3 or 4
        $taskPopularity = 0;
        foreach ( $workers as $worker ) {
            if ($worker->choice1 == $task->id && $nbSchedules >= 1)
                $taskPopularity++;
            if ($worker->choice2 == $task->id && $nbSchedules >= 2)
                $taskPopularity++;
            if ($worker->choice3 == $task->id && $nbSchedules >= 3)
                $taskPopularity++;
            if ($worker->choice4 == $task->id && $nbSchedules >= 4)
                $taskPopularity++;
        }
        // Generate group sizes
        $taskCount[$task->id] = array();
        for ($i = 1; $i <= $nbSchedules; ++$i) {
            $taskCount[$task->id][$i] = floor($taskPopularity/$nbSchedules);
            $scheduleCount[$i] += $taskCount[$task->id][$i];
        }
        for ($i = 1; array_sum($taskCount[$task->id]) != $taskPopularity; $i++) {
            $taskCount[$task->id][$i]++;
            $scheduleCount[$i]++;
        }
    }
        
    
    foreach ( $taskCount as $k1 => $task1 ) {
        // If perfect total match, stop
        if (count( array_unique($scheduleCount) ) == 1) {
            break;
        }
        
        for ($i = 1; $i <= $nbSchedules && count( array_unique($scheduleCount) ) != 1; $i++) {
            foreach ( $taskCount as $k2 => $task2 ) {
                // If perfect total match, stop
                if (count( array_unique($scheduleCount) ) == 1) {
                    break;
                }
                
                if ($k1 != $k2) {
                    for ($j = 1; $j <= $nbSchedules && count( array_unique($scheduleCount) ) != 1; $j++) {
                        // If perfect total match, stop
                        if (count( array_unique($scheduleCount) ) == 1) {
                            break;
                        }
                        
                        // If perfect distribution, don't bother shifting
                        if ( count( array_unique($task2) ) != 1 ) {
                            foreach ( $taskCount[$k2] as $key => $t ) {
                                $scheduleCount[$key] -= $t;
                            }
                            $taskCount[$k2] = array_shift_circular($taskCount[$k2]);
                            
                            foreach ( $taskCount[$k2] as $key => $t ) {
                                $scheduleCount[$key] += $t;
                            }
                        }
                    }
                }
            }
            
            // If perfect distribution, don't bother shifting
            if ( count( array_unique($task1) ) != 1 ) {
                foreach ( $taskCount[$k1] as $key => $t ) {
                    $scheduleCount[$key] -= $t;
                }
                $taskCount[$k1] = array_shift_circular($taskCount[$k1]);
                
                foreach ( $taskCount[$k1] as $key => $t ) {
                    $scheduleCount[$key] += $t;
                }
            }
        }
    }
    
    // List activities
    foreach ( $tasks as $task ) {
        
        echo '<table class="schedules">';
        echo '<tr><th colspan="' . $nbSchedules . '">' . $task->name . '</th></label>';
        
        // Table headers
        echo '<tr>';
        for ($i = 1; $i <= $nbSchedules; ++$i) {
            echo "<td>Plage horaire $i</td>";
        }
        echo '</tr>';
        
        // Number of places
        echo '<tr>';
        for ($i = 1; $i <= $nbSchedules; ++$i) {
            echo '<td><input name="numberPlaces[' . $task->id . '][' . $i . ']" type="number" min="0" value="' . $taskCount[$task->id][$i] . '" /></td>';
        }
        echo '</tr>';
        
        echo '</table>';
    }
?>
    <div id="display-infos">
        <p>Afficher les informations suivantes pour chaque participant :</p>
        <div class="display-element"><input type="checkbox" checked="checked" id="display-firstname"><label for="display-firstname">Prénom</label></div>
        <div class="display-element"><input type="checkbox" checked="checked" id="display-name"><label for="display-name">Nom</label></div>
        <div class="display-element"><input type="checkbox" checked="checked" id="display-email"><label for="display-email">E-mail</label></div>
        <div class="spacer"></div>
    </div>
    <input type="submit" value="Lancer la répartition" name="generateGroups" />
</form>
<?php endif; ?>

<?php
    if (isset($_POST['generateGroups'])) {
        // Start timer
        $time_start = microtime(true);
        $assignements = array();
        for ($i = 1; $i <= count(array_values($_POST['numberPlaces'])[0]); ++$i) {
            
            // Get matrix dimension
            $places = 0;
            foreach ($_POST['numberPlaces'] as $nbPlaces) {
                $places += $nbPlaces[$i];
            }
            $dimension = max($places, count($workers));
            
            // Create square matrix
            $matrix = new SplFixedArray($dimension);
            $rowCount = 0;
        
            foreach ( $workers as $k => $worker ) {
                // Each worker has a row
                $row = new SplFixedArray($dimension);
                
                $colCount = 0;
                // Fill the row with tasks and preferences for that worker
                foreach ( $tasks as $task ) {
                    for ( $j = 0; $j < $_POST['numberPlaces'][$task->id][$i]; ++$j ) {
                        $cost;
                        switch ( $task->id ) {
                            case $worker->choice1 :
                                $cost = FIRST_CHOICE_COST;
                                break;
                            case $worker->choice2 :
                                $cost = SECOND_CHOICE_COST;
                                break;
                            case $worker->choice3 :
                                $cost = THIRD_CHOICE_COST;
                                break;
                            case $worker->choice4 :
                                $cost = FOURTH_CHOICE_COST;
                                break;
                            default :
                                $cost = OTHER_CHOICE_COST;
                                break;
                        }
                        $cell = new Preference($cost, $worker, $task);
                        
                        // Check if that (worker, task) assignement has already been matched before
                        foreach ( $assignements as $assignement ) {
                            if ( $assignement->getWorker() == $cell->getWorker() && $assignement->getTask() == $cell->getTask() ) {
                                // If so, disable cell
                                $cell->disable();
                                break;
                            }
                        }
                        $row[$colCount] = $cell;
                        $colCount++;
                    }
                }
                while ($colCount < $dimension) {
                    $dummyTask = new stdClass;
                    $dummyTask->id = -1;
                    $dummyTask->name = 'Sans groupe';
                    $dummyTask->nbPlaces = 20;
                    $cell = new Preference(0, $worker, $dummyTask); 
                    $cell->disable();
                    $row[$colCount] = $cell;
                    $colCount++;
                }
                
                $matrix[$rowCount] = $row;
                $rowCount++;
            }
            
            while ($rowCount < $dimension) {
                $dummyWorker = new stdClass;
                $dummyWorker->fullname = '(Place libre)';
                $row = new SplFixedArray($dimension);
                $colCount = 0;
                foreach ( $tasks as $task ) {
                    for ( $j = 0; $j < $_POST['numberPlaces'][$task->id][$i]; ++$j ) {
                        $cell = new Preference(0, $dummyWorker, $task); 
                        $cell->disable();
                        $row[$colCount] = $cell;
                        $colCount++;
                    }
                }
                $matrix[$rowCount] = $row;
                $rowCount++;
            }
            
            // Compute assignements
            $indexesList = $algo->computeAssignments(copyMatrix($matrix));
            
            // Display result
            display('<h3>Plage horaire ' . $i . '</h3>', $matrix, $indexesList);
            
            // Disable matched (worker, task) associations
            //disableCells($matrix, $indexesList);
            foreach ( $indexesList as $indexes ) {
                $assignements[] = $matrix[$indexes[0]][$indexes[1]];
            }
        }
        
        echo '<div class="spacer"></div>';
        echo 'Répartition automatique en ' . (microtime(true) - $time_start) . ' secondes.';
    }
?>
<br />
</div>