<script>
	$(document).ready(function() { $('[data-toggle="tooltip"]').tooltip(); });
	$(document).ready(function(){ $('[data-toggle="popover"]').popover(); });
</script>

<?php
/* Generates tiny thumbnail for species tables */
function thumbnail($img_url, $latin, $size, $page='view.php') {
	if (strpos($img_url, 'https://i.imgur.com/') !== false ) $thumb = str_replace('l.', 't.', $img_url);
	echo '<td><a href="'.$page.'?spp='.$latin.'"><div style="width:'.$size.'; height:'.$size.'; background-color:#e9ecef">';
	if ($thumb != '') echo '<img src="'.$thumb.'" style="max-width:100%; max-height:100%">';
	echo '</div></a></td>';
}

/* Determines what type of species is referred to by $latin_name, and returns the name of its most specific table. */
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

/* Builds logbook */
function logbook($query, $name, $num_logs, $class) {
		echo '<div class="card">';
		// Logbook header: Downward caret + badge indicating number of logs + "Logbook"
		echo '<div class= "card-header prim-' . $class . '" id="logbookHeader">'
		 		 . '<div class="mb-0" data-toggle="collapse" data-target="#logs">'
				 . '<i class="fas fa-caret-down"></i> '
			 	 . '<span class="badge badge-' . ($num_logs == 0 ? 'light' : 'dark') . '">'
				 . $num_logs . '</span> <strong>Logbook</strong>'
				 . '</div></div>';
		// Logbook body: Simple table of logs from most to least recent
		echo '<div id="logs" class="collapse" aria-labelledby="logbookHeader">';
		echo '<div class="card-body">';
		if ($num_logs != 0) {
			echo '<table class="spp spp-' . $class . '" style="width: auto">';
			if ($class == 'p') {
				echo '<tr><th>Latin name</th><th>Date</th><th>Stage</th><th>Notes</th></tr>';
				build_rows($query, $name);
			}
			else {
				echo '<tr><th>Date</th><th>Stage</th><th>Notes</th></tr>';
				build_rows($query, $name);
			}
			echo '</table>';
		}
		echo '</div></div></div>';
}

/* Builds delete button and dialog */
function delete_button($name, $class) {
	echo '<a href="#" class="btn btn-' . $class . ' btn-del" data-toggle="modal" data-target="#delModal"><i class="fas fa-trash-alt"></i></a>';
	echo '<div class="modal fade" id="delModal" tabindex="-1" role="dialog">'
			 . '<div class="modal-dialog" role="document">'
			 . '<div class="modal-content">'
			 . '<div class="modal-header"><h3 class="modal-title" id="delLabel">Delete species</h3></div>'
			 . '<div class="modal-body">Are you sure you want to delete all data for this species?</div>'
			 . '<div class="modal-footer">'
			 . '<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>'
			 . '<a href="delete.php?spp=' . $name . '" target="_blank" class="btn btn-primary">Delete</a>'
			 . '</div>'
			 . '</div>'
			 . '</div>'
			 . '</div>';
}

/* Populates some values used to customize view.php and edit.php with styling and content */
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

/* Some other helper functions for displaying the results of queries in interesting ways */

/* Builds generic rows inside an existing table */
function build_rows($query, $name='') {
	global $conn;
	$stmt = $conn->prepare($query);
	if ($name != '') $stmt->bindValue(1, $name);
	$stmt->execute();
	$num_col = $stmt->columnCount();

	# Print data rows
	while ($row = $stmt->fetch()) {
		for ($i = 0; $i < $num_col; $i++) {
			$meta = $stmt->getColumnMeta($i);
			$dtable = $meta['table'];
			$link = false;

			# Quick patch to add thumbnails until this function gets properly overhauled
			if ($i == 0 && isset($row['latin_name']) && isset($row['img_url'])) {
				thumbnail($row['img_url'], $row['latin_name'], "2rem", "view_plant.php");
				continue;
			}
			echo '<td>';
			# If binary value, display as check mark or dash
			if ($meta['native_type'] == 'TINY') {
				if ($row[$i] != 0) echo '<div style="text-align: center">&#x2713;</div>';
				else echo '<div style="text-align: center">&mdash;</div>';
			}
			# If species name, use italics
			else if ($meta['name'] == "latin_name") echo "<em>" . $row[$i] . "</em>";
			else
			{
				# If row is for a species table, make it a link
				if ($i == 1 && (isset($row['latin_name'])))
				{
					if ($dtable == "Plant" || $dtable == "Plant_deriv") {
						echo "<a href='view_plant.php?spp=".$row['latin_name']."'>";
					}
					else echo "<a href='view.php?spp=".$row['latin_name']."'>";
					$link = true;
				}
				echo ucfirst($row[$i]);
			}

			if ($link) echo '</a>';
			echo '</td>';
		}
		echo "</tr>";
	}
}

/* Builds a list of months (e.g. months a plant is in bloom) with tooltips */
function display_months($table, $name) {
	global $conn;
	$query = "Select * from $table natural join Month where latin_name = ? order by month_num";
	$stmt = $conn->prepare($query);
	$stmt->bindValue(1, $name);
	$stmt->execute();

	$all_months = '';
	while ($month = $stmt->fetch())
	{
		if ($month['verified'] == 0)
		{
			$all_months = $all_months.'<a href="#" data-toggle="tooltip" title="Unverified. '.$month['notes'].'" data-placement="top"><em>'.substr($month['month'], 0, 3).'</em></a>*';
		}
		else
		{
			$all_months = $all_months.'<a href="#" data-toggle="tooltip" title="'.$month['notes'].'" data-placement="top">'.substr($month['month'], 0, 3).'</a>*';
		}
	}
	$all_months = rtrim(str_replace('*', ' - ', $all_months), ' -');
	if ($all_months != '') echo $all_months;
	else echo 'n/a';
}

/* Breaks a notes field apart into a bulleted list.
Uses periods as the delimiter, so each sentence ends up on a different line; will
probably edit data later to use a different delimiter for more flexibility.*/
function display_list($notes) {
	if ($notes == '') echo '&nbsp;';
	else
	{
		echo '<ul>';
		$items = explode(". ", $notes);
		foreach($items as $li)
		{
			$li = rtrim($li, '.');
			echo '<li>'.$li.'</li>';
		}
		echo '</ul>';
	}
}

?>
