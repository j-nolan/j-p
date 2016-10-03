<?php
/* All utility functions for the j-p plugin project.
 * Project: j-p
 * Date: August 2014
 * Author: James Nolan */
?>
<?php
    // Display functions. Each screen has its own template that is called by
    // its corresponding function.
    function display_jp_accueil() {
        include( 'accueil.php' );
    }
    function display_jp_participants() {
        include( 'liste-inscrits.php' );
    }
    function create_new_jp() {
        include( 'create-new-jp.php' );
    }
    function download_jp_participants() {
        include( 'download-inscrits.php' );
    }
    function display_jp_statistiques() {
        include( 'statistiques.php' );
    }
    function display_jp_repartition() {
        include( 'generer.php' );
    }

    // Get the event that is currently being used as the dataset. This function
    // returns the last event that was created.
    function get_selected_jp() {
        if (isset($_POST['jp'])) {
            $jp_id = intval($_POST['jp']);
        } else {
            global $wpdb;
            // If no post sent, use latest jp from database
            $query = 'SELECT id FROM svmm_pedagogical_days ORDER BY id DESC LIMIT 1';
            $res = $wpdb->get_results($query);
            foreach ( $res as $r ) {
                $jp_id = $r->id;
            }
        }
        return $jp_id;
    }


    // Get the lists of preferences of  each participant to each workshop for the
    // current event
    function get_preferences_list() {
        // Get preferences from DB
        global $wpdb;
        $query = 'SELECT
                    CONCAT(t2.lastname, " ", t2.firstname) as fullname,
                    t2.email,
                    t1.teacherID,
                    t1.choice1,
                    t1.choice2,
                    t1.choice3,
                    t1.choice4
                FROM `svmm_inscriptions_jp` as t1
                INNER JOIN `svmm_teachers` as t2
                    ON t1.teacherID = t2.id
                WHERE t1.dateID = ' . get_selected_jp();
                
        $preferences = $wpdb -> get_results( $query );
        return $preferences;
        
    }

    // Generate the event selection form
    function generate_select_jp_form($include_form = true) {
        global $wpdb;
        ?>
            <?php if ($include_form) : ?>
            <form action="" method="POST">
            <?php endif; ?>
                <label>Journée pédagogique :</label>
                <select name="jp">
                    <?php
                        // List available journées pédagogiques (one for each year, usually)
                        $query = '
                            SELECT id, name
                            FROM svmm_pedagogical_days
                            ORDER BY id DESC';
                        $res = $wpdb->get_results( $query );
                        if ( is_array( $res ) ) {
                            foreach ( $res as $available_jp ) {
                                echo '<option value="' . $available_jp->id . '" ' . ($_POST['jp'] == $available_jp->id ? 'selected="selected"' : '') . '>' . $available_jp->name . '</option>';
                            }
                        } else {
                            die ('Pas de journées pédagogiques disponibles.');
                        }
                    ?>
                </select>

                <?php if ($include_form) : ?>
                <input type="submit" value="Envoyer" />
            </form>
        <?php endif; ?>

        <?php
    }
    
    /**
     * Circularly shifts an array
     *
     * Shifts to right for $steps > 0. Shifts to left for $steps < 0. Keys are
     * preserved.
     * Source: http://www.ermshaus.org/2011/04/php-circular-shift-array
     */
    function array_shift_circular(array $array, $steps = 1)
    {
        $keys = array_keys( $array );
        
        if (!is_int($steps)) {
            throw new InvalidArgumentException(
                    'steps has to be an (int)');
        }
     
        if ($steps === 0) {
            return $array;
        }
     
        $l = count($array);
     
        if ($l === 0) {
            return $array;
        }
     
        $steps = $steps % $l;
        $steps *= -1;
     
        return array_combine( $keys, array_merge(array_slice($array, $steps),
                           array_slice($array, 0, $steps) ) );
    }


    // Get the list of available workshops
    function get_available_activities() {
        // Get available choices from DB
        global $wpdb;
        $query = 'SELECT
                    id,
                    name,
                    nbPlaces FROM `svmm_pedagogical_activities`
                WHERE pedagogicalDay = ' . get_selected_jp();
        $activities = $wpdb -> get_results( $query );
        return $activities;
    }


    // Display the result table for a given result matrix after the assignement
    // has been performed
    function display($title, &$matrix, $indexesList) {
        echo '<div class="result-table">';
        echo $title;
        echo '<table class="sorted-tasks">';
        // Loop through assignements
        $previousTask = null;
        foreach ($indexesList as $indexes) {
            // Locate assignement
            $assignement = $matrix[$indexes[0]][$indexes[1]];
            
            // Display task name if new
            if ($assignement->getTask()->name != $previousTask) {
                echo '</table>';
                echo '<h4> ' . $assignement->getTask()->name . '</h4>';
                echo '<table class="sorted-tasks">';
                $previousTask = $assignement->getTask()->name;
            }
            
            // Is it a good match?
            $quality = '';
            switch ($assignement->getValue()) {
                case FIRST_CHOICE_COST :
                    $quality = 1;
                    break;
                case SECOND_CHOICE_COST :
                    $quality = 2;
                    break;
                case THIRD_CHOICE_COST :
                    $quality = 3;
                    break;
                case FOURTH_CHOICE_COST :
                    $quality = 4;
                    break;
                default :
                    $quality = 'x';
                    break;
            }
            
            // Display worker
            echo '<tr>';
            echo '<td class="quality choice-' . $quality . '">Choix ' . $quality . '</td>';
            echo '<td class="worker">' . $assignement->getWorker()->fullname .'</td>';
            echo '<td class="worker">' . $assignement->getWorker()->email .'</td>';
            
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }

    // To avoid a worker being assigned to the same workshop at different
    // time slots, a cell can be disabled after the first run of the hungarian
    // algorithm. This prevents  the worker to be assigned twice for the same
    // workshop
    function disableCells(&$matrix, &$indexesList) {
        foreach ($indexesList as $indexes) {
            $assignement = $matrix[$indexes[0]][$indexes[1]];
            for ( $i = 0; $i < count($matrix[0]); ++$i ) {
                $cell = $matrix[$indexes[0]][$i];
                if ( $cell->getWorker() == $assignement->getWorker() && $cell->getTask() == $assignement->getTask() ) {
                    $matrix[$indexes[0]][$i]->disable();
                }
            }
            for ($i = 0; $i < count($matrix); ++$i) {
                $cell = $matrix[$i][$indexes[1]];
                if ( $cell->getWorker() == $assignement->getWorker() && $cell->getTask() == $assignement->getTask() ) {
                    $matrix[$i][$indexes[1]]->disable();
                }
            }
        }
    }
    
    // Perform a deep copy of a matrix
    function copyMatrix(&$matrix) {
        $size = $matrix->count();
        $matrix2 = new SplFixedArray($size);
        for ($i = 0; $i < $size; ++$i) {
            $row = new SplFixedArray($size);
            for ($j = 0; $j < $size; ++$j) {
                $row[$j] = clone $matrix[$i][$j];
            }
            $matrix2[$i] = $row;
        }
        return $matrix2;
    }
?>