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
	$type = 'Creature';

	global $conn;
	$stmt = $conn->prepare("(SELECT latin_name, 'Bee' AS type FROM Bee) UNION (SELECT latin_name, 'Lepidopteran' AS type FROM Lepidopteran)");
	$stmt->execute();
	$bee_lep_list = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
	$stmt = $conn->prepare("SELECT COUNT(latin_name) FROM Plant WHERE latin_name = ?");
	$stmt->bindValue(1, $latin_name);
	$stmt->execute();
	if ($stmt->fetch() == 1) $type = 'Plant';

	if (array_key_exists($latin_name, $bee_lep_list)) {
		$type = $bee_lep_list[$latin_name][0];
	}
	return $type;
}

/* Takes a query and uses it to build a basic table */
function display($query_string) {
	global $conn;
	$stmt = $conn->prepare($query_string);
	$stmt->execute();

	$num_col = $stmt->columnCount();

	# Determine table class for styling
	$meta = $stmt->getColumnMeta(0);
	$table = $meta['table'];

	echo "<table>";

	# Print header row
	echo "<tr>";
	for ($i = 0; $i < $num_col; $i++) {
		$meta = $stmt->getColumnMeta($i);
		$hname = ucfirst($meta['name']);
		if ($hname != 'Img_url') {
			$hname = str_replace("_", " ", $hname);
			echo "<th>" . $hname . "</th>";
		}
		else echo '<th></th>';
	}
	echo "</tr>";

	# Print data rows
	while ($row = $stmt->fetch()) {
		# Row formatting options for different tables
		if ($table == "Plant" && array_key_exists('have', $row) && $row['have'] == "0") echo "<tr id=\"havenot\">";

		else if ($table == "Lepidopteran" && $row['seen'] == "1") {
			if (explode(' ', $row['common_name'])[0] == 'Unknown') echo "<tr class=\"seen-l-fam\">";
			else echo "<tr class=\"seen-l\">";
		}

		else if  ( $table == "Bee" && $row['seen'] == "1") {
			if (explode(' ', $row['latin_name'])[1] == 'spp') echo "<tr class=\"seen-b-genus\">";
			else echo "<tr class=\"seen-b\">";
		}
		else echo "<tr>";

		for ($i = 0; $i < $num_col; $i++) {
			$meta = $stmt->getColumnMeta($i);
			$dname = $meta['name'];
			$dtype = $meta['native_type'];

			# Quick patch to add thumbnails until this function gets properly overhauled
			if ($i == 0 && isset($row['latin_name']) && isset($row['img_url'])) {
				thumbnail($row['img_url'], $row['latin_name'], "2rem");
				continue;
			}
			# Cell formatting options for different tables
			if ($dname == "want" && $row[$i] == 1) echo "<td class=\"want\">";
			else echo "<td>";

			# If first cell is for a species table, make it a link
			if ($i == 0 && isset($row['latin_name']))
			{
				$type = get_type($row['latin_name']);
				if ($table == "Plant" || $type == "Plant") echo "<a href='view_plant.php?spp=".$row['latin_name']."'>";
				else echo "<a href='view.php?spp=".$row['latin_name']."'>";
			}

			# If bool, change output to check mark or dash mark
			if ($dtype == "TINY") {
				if ($row[$i] != 0) echo '<div style="text-align: center">&#x2713;</div>';
				else echo '<div style="text-align: center">&mdash;</div>';
			}

			# If species name, use italics
			else if ($dname == "latin_name") echo "<em>" . $row[$i] . "</em>";

			else echo $row[$i];
			if ($i == 0 && isset($row['common_name'])) echo "</a>";
			echo "</td>";
		}
		echo "</tr>";
	}

	echo "</table>";
	$stmt = null;
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
