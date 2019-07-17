<script>
	$(document).ready(function() { $('[data-toggle="tooltip"]').tooltip(); });
	$(document).ready(function(){ $('[data-toggle="popover"]').popover(); });
</script>
<?php


# Determines what type of species is referred to by $latin_name, and returns the name of its most specific table.
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

# Builds a table for a family within Bee or Lepidopteran
# Acceptable inputs for $type = 'Butterfly', 'Moth', 'Bee'
function build_family_table($family, $type) {
	if ($type == 'Butterfly' || $type == 'Moth') {
		$table = 'Lep_full';
		$style = 'l';
		if ($family == 'All') {
			$query = "SELECT latin_name, common_name, family_name FROM $table WHERE subtype='$type' ORDER BY family_name, latin_name";
		}
		else {
			$query = "SELECT latin_name, common_name, family_name FROM $table WHERE family_name='$family' AND subtype='$type' ORDER BY latin_name";
		}
	}
	else if ($type == 'Bee') {
		$table = 'Bee_full';
		$style = 'b';
		if ($family == 'All') {
			$query = "SELECT latin_name, common_name, family_name FROM $table ORDER BY family_name, latin_name";
		}
		else {
			$query = "SELECT latin_name, common_name, family_name FROM $table WHERE family_name='$family' ORDER BY latin_name";
		}
	}
	# Add error handling for invalid input
	global $conn;
	$stmt = $conn->prepare($query);
	$stmt->execute();

	$num_col = $stmt->columnCount();

	# Start of table
	echo "<table style='width: 80%'>";
	echo "<tr><th>Latin name</th><th>Common name</th><th>Sightings</th></tr>";
	while ($row = $stmt->fetch()) {
		$name = $row['latin_name'];
		$substmt = $conn->prepare("SELECT COUNT(DISTINCT date) FROM Log WHERE latin_name='$name'");
		$substmt->execute();
		$seen = $substmt->fetch()[0];

		if  ($seen != "0") {
			if (explode(' ', $row['latin_name'])[1] == 'spp') echo "<tr class=\"seen-$style-fam\">";
			else echo "<tr class=\"seen-$style\">";
		}
		else echo "<tr>";

		$lat = $row['latin_name'];
		$com = $row['common_name'];
		echo "<td><a href='view.php?spp=$lat'>$lat</a></td>";
		echo "<td>$com</td>";
		echo "<td>$seen</td>";
		echo "</tr>";
	}
	echo "</table>";
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
		$hname = str_replace("_", " ", $hname);
		echo "<th>" . $hname . "</th>";
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

			# Cell formatting options for different tables
			if ($dname == "want" && $row[$i] == 1) echo "<td class=\"want\">";
			else echo "<td>";

			# If first cell is for a species table, make it a link
			if ($i == 0 && isset($row['latin_name']))
			{
				$type = get_type($row['latin_name']);
				if ($table == "Plant" || $type == "Plant") echo "<a href='view_plant.php?name=".$row['latin_name']."'>";
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
				# If first cell is for a species table, make it a link
				if ($i == 0 && (isset($row['latin_name'])))
				{
					if ($dtable == "Plant" || $dtable == "Plant_deriv") echo "<a href='view_plant.php?name=".$row['latin_name']."'>";
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
