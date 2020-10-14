<?php
/* Determines what type of species is referred to by $latin_name, and returns
   the name of its most specific table. */

// PAGES THAT STILL USE: update_species, edit, delete, update_logs
function get_type($latin_name) {
	// Get list of all species in Bee, Lepidopteran, and Plant, and mark them by table of origin
	global $conn;
	$stmt = $conn->prepare("(SELECT latin_name, 'Bee' AS type FROM Bee) UNION (SELECT latin_name, 'Lepidopteran' AS type FROM Lepidopteran) UNION (SELECT latin_name, 'Plant' AS type FROM Plant)");
	$stmt->execute();
	$species_list = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

	// If it's not one of those, it's just in the Creature list;
	// if it is one of those, adjust the type accordingly
	if (array_key_exists($latin_name, $species_list)) {
		return $species_list[$latin_name][0];
	}
	return 'Creature';
}

/* Returns an array containing some values used to customize view.php and
   edit.php with styling and content. */

// PAGES THAT STILL USE: update_species, view
function template_vals($type) {
	$template['type'] = 'misc'; // Tells the header which part of the menu should be marked as active
	$template['table'] = 'Creature_full'; // Table name for the query

	if ($type == 'Lepidopteran') {
		$template['type'] = 'lep';
		$template['table'] = 'Lep_full';
	}
	else if ($type == 'Bee') {
		$template['type'] = 'bee';
		$template['table'] = 'Bee_full';
	}
	return $template;
}
?>
