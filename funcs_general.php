<?php
/* Determines what type of species is referred to by $latin_name, and returns
   the name of its most specific table. */

// PAGES THAT STILL USE: view, edit, delete, update_logs
function get_type($latin_name) {
	global $conn;
	// Get list of all species in Bee, Lepidopteran, and Plant, and mark them by table of origin
	$stmt = $conn->prepare("(SELECT latin_name, 'Bee' AS type FROM Bee) UNION (SELECT latin_name, 'Lepidopteran' AS type FROM Lepidopteran) UNION (SELECT latin_name, 'Plant' AS type FROM Plant)");
	$stmt->execute();
	$species_list = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);

	// If it's not one of those, it's just in the Creature list;
	// if it is one of those, adjust the type accordingly
	$type = 'Creature';
	if (array_key_exists($latin_name, $species_list)) {
		$type = $species_list[$latin_name][0];
	}
	return $type;
}

/* Returns an array containing some values used to customize view.php and
   edit.php with styling and content. */

// PAGES THAT STILL USE: edit, view
function template_vals($type) {
	$template['type'] = 'other'; // Tells the header which part of the menu should be marked as active
	$template['table'] = 'Creature_full'; // Table name for the query
	$template['class'] = 'o'; // For styling

	if ($type == 'Lepidopteran') {
		$template['type'] = 'lepidop';
		$template['table'] = 'Lep_full';
		$template['class'] = 'l';
	}
	else if ($type == 'Bee') {
		$template['type'] = 'bee';
		$template['table'] = 'Bee_full';
		$template['class'] = 'b';
	}
	return $template;
}

/* Builds tiny thumbnail for species tables. If there's no image, it substitutes
   a gray box of the same size. In either case, the thumbnail links to the species page. */

// PAGES THAT STILL USE: home
function thumbnail($img_url, $latin, $size, $page='view.php', $tooltip='') {
	// Imgur uses multiple urls per image, making it convenient to reduce sizes for faster loading times
	if (strpos($img_url, 'https://i.imgur.com/') !== false ) $img_url = str_replace('l.', 't.', $img_url);

	echo '<a href="'.$page.'?spp='.$latin.'"';
	if ($tooltip) echo ' data-toggle="tooltip" data-placement="right" title="'.$tooltip.'"';
	echo '><div style="width:'.$size.'; height:'.$size.'; background-color:#e9ecef; display:inline-block; vertical-align:middle">';
	if ($img_url) echo '<img src="'.$img_url.'" style="max-width:100%; max-height:100%">';
	echo '</div></a>';
}
?>
