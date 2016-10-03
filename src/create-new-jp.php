<?php
/* Template for viewing all available events and for creating a new event
 * Project: j-p
 * Date: August 2014
 * Author: James Nolan */
?>
<?php if (isset($_POST['name_jp'])) : ?>


	<?php
		// Get activities
		if (isset($_POST['activities']) && is_array($_POST['activities'])) {
			foreach ($_POST['activities'] as $act) {
				if (trim($act) != '') {
					$activities[] = $act;
				}
			}
		}
		if (trim($_POST['name_jp']) != '') {
			$name_jp = $_POST['name_jp'];
		}

		// If everything in order
		if (isset($activities) && count($activities) >= 4 && isset($name_jp)) {
			// Add jp
			global $wpdb;
			if ($wpdb->insert('svmm_pedagogical_days', array('name' => $name_jp))) {
				// Get inserted jp id
				$jp_id = $wpdb->insert_id;

				// Insert activities
				foreach ($activities as $act) {
					$wpdb->insert('svmm_pedagogical_activities', array('pedagogicalDay' => $jp_id, 'name' => $act));
				}
				$success = true;
			} else {
				die('Mysql error insertion to db');
			}
		} else {
			$error = true;
		}
	?>

<?php endif; ?>

<div class="wrap">
	<?php if ($success) : ?>
		<p><strong style="color:green">Journée créée avec succès !</strong></p>
	<?php endif; ?>
	<?php if ($error) : ?>
		<p><strong style="color:red">Une erreur est survenue. Une journée pédagogique doit consister en au moins 4 ateliers.</strong></p>
	<?php endif; ?>
	<h2>Liste des journées pédagogiques</h2>
    <p style="color:red">
    	<strong>Attention :</strong><br />
    	Les nouvelles inscriptions sont automatiquement ajoutées à la <strong>dernière journée pédagogique créée</strong>.<br />
    	Ne transmettez le formulaire d'inscription aux membres <strong>qu'après</strong> avoir créé une nouvelle journée pédagogique.
    </p>
	<ul>
		<?php
			global $wpdb;
			$jps = $wpdb->get_results('SELECT * FROM svmm_pedagogical_days ORDER BY id DESC');
			foreach ($jps as $jp) {
				echo '<li>';
			    if ($jp === reset($jps)) {
			    	echo '<strong style="background-color:darkgreen;color:white"> Active </strong>&nbsp;';
			    } else {
			    	echo '<strong style="background-color:darkred;color:white"> Terminée </strong>&nbsp;';
			    }
				echo $jp->name;
				echo '</li>';
			}
		?>
	</ul>
    <h2>Nouvelle journée pédagogique</h2>
    <form action="" method="post">
		<h3>Journée pédagogique</h3>
    	<label>Titre</label>
    	<input type="text" placeholder="Journée pédagogique <?php echo date('Y'); ?>" name="name_jp" value="<?php echo isset($_POST['name_jp']) ? htmlspecialchars($name_jp) : ''; ?>">
    	<h3>Activités</h3>
    	<p>Au minimum 4</p>
    	<?php foreach (range(0, 9) as $i) : ?>
    	<label>Nom</label>
    	<input type="text" name="activities[]" placeholder="Activité <?php echo $i + 1; ?>" value="<?php echo isset($_POST['activities'][$i]) ? htmlspecialchars($_POST['activities'][$i]) : ''; ?>"><br />
	    <?php endforeach; ?>
	    <input type="submit">
    </form>
</div>