<script>
	$(document).ready(function() { $('[data-toggle="tooltip"]').tooltip(); });
	$(document).ready(function(){ $('[data-toggle="popover"]').popover(); });
</script>

<?php
/*******************************************************/
/* FUNCTIONS THAT RETURN METADATA */
/*******************************************************/

/* Determines what type of species is referred to by $latin_name, and returns
   the name of its most specific table. */
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

/*******************************************************/
/* FUNCTIONS THAT PRINT FULL ELEMENTS */
/*******************************************************/

/* Builds tiny thumbnail for species tables. If there's no image, it substitutes
   a gray box of the same size. In either case, the thumbnail links to the species page. */
function thumbnail($img_url, $latin, $size, $page='view.php', $tooltip='') {
	// Imgur uses multiple urls per image, making it convenient to reduce sizes for faster loading times
	if (strpos($img_url, 'https://i.imgur.com/') !== false ) $img_url = str_replace('l.', 't.', $img_url);

	echo '<a href="'.$page.'?spp='.$latin.'"';
	if ($tooltip) echo ' data-toggle="tooltip" data-placement="right" title="'.$tooltip.'"';
	echo '><div style="width:'.$size.'; height:'.$size.'; background-color:#e9ecef; display:inline-block; vertical-align:middle">';
	if ($img_url) echo '<img src="'.$img_url.'" style="max-width:100%; max-height:100%">';
	echo '</div></a>';
}

/* Builds logbook.
		 $query = query string that will generate the logbook data; contains up to one "?" (see $bound_var)
		 $bound_var = value that will be bound to the query string, or '' if not applicable
		 $num_logs = number of entries in logbook
		 $class = class suffix for table styling ('p' for plants, 'b' for bees, 'l' for lepidopterans, 'o' for other creatures) */
function logbook($query, $bound_var, $num_logs, $class) {
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
				table($query, $bound_var, array('class' => 'spp spp-'.$class));
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

/* Builds a table.
		 $query = query string that will generate the logbook data; contains up to one "?" (see $bound_var)
	 	 $bound_var = value that will be bound to the query string, or '' if not applicable
		 $table_settings = associative array allowing for some style customization; current supported keys are 'class', 'width', and 'tbody_id' */
/* Note: The $table_settings array for customization is a bit clunky, but seemed
   like the simplest way to accommodate a bit of style fiddling, for now. Once I
	 start experimenting with formatting properly, there might end up being a lot
	 of such tweaks, temporarily, so I wanted maximum flexibility. */
function table($query, $bound_var, $table_settings) {
	if (isset($table_settings['width'])) $width = $table_settings['width'];
	else $width = 'auto';
	if (isset($table_settings['class'])) $class = 'class="' . $table_settings['class'] . '" ';
	else $class = '';

	global $conn;
	$stmt = $conn->prepare($query);
	if ($bound_var) $stmt->bindValue(1, $bound_var);
	$stmt->execute();
	$num_col = $stmt->columnCount();

	echo '<table '.$class.'style="width: '.$width.'">';

	// Print header row by iterating through results to get each column's name
	echo '<thead>';
	for ($i = 0; $i < $num_col; $i++) {
		$meta = $stmt->getColumnMeta($i);
		// Make uppercase and remove underscores
		$hname = ucfirst($meta['name']);
		if ($hname != 'Img_url') {
			$hname = str_replace('_', ' ', $hname);
			echo '<th>' . $hname . '</th>';
		}
		else echo '<th></th>'; // If it's a thumbnail column, leave the header cell empty
	}
	echo '</thead>';

	echo '<tbody' . (isset($table_settings['tbody_id']) ? ' id="'.$table_settings['tbody_id'].'"' : '') . '>';
	// Print data rows
	while ($row = $stmt->fetch()) {
		echo '<tr>';
		for ($i = 0; $i < $num_col; $i++) {
			$meta = $stmt->getColumnMeta($i);
			if ($meta['table'] == 'Plant') $url = 'view_plant.php';
			else $url = 'view.php';

			echo '<td>';
			// A few types of cells get special formatting, as follows:
			// Thumbnails
			if ($meta['name'] == 'img_url') {
				thumbnail($row['img_url'], $row['latin_name'], '2rem', $url);
			}
			// Boolean values (have, want): convert to check mark or dash
			else if ($meta['native_type'] == 'TINY') {
				echo '<div style="text-align: center">' . ($row[$i] != 0 ? '&#x2713' : '&mdash;') . '</div>';
			}
			// Latin names: italicize
			else if ($meta['name'] == "latin_name") {
				echo '<a href="' . $url . '?spp=' . $row[$i] . '">';
				echo '<em>' . $row[$i] . '</em>';
				echo '</a>';
			}
			// General case
			else echo ucfirst($row[$i]);

			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table>';
}

/* Builds the contents for the main wildlife category pages. Currently, this
   breaks the category apart into tabs separated by family (bee, butterfly/moth)
   or type (everything else, a broad enough category that division by family is
   far too granular), and lists the members of each family/type in a simple
   table for each tab.
	   $header: header text for the page
	   $type: 'Butterfly', 'Moth', 'Bee', or 'Other'; any unknown value will be treated as 'Other'
	   $query: query string that will select the category names used for the tabs
*/
function build_tabs($header, $type, $query) {
  // Header
  echo '<h1 class="text-center">' . $header . '</h1>';

  // Pills for family names
  echo '<ul class="nav nav-pills justify-content-center" id="pills-tab" role="tablist">';
  // Fetch family or type details for pills into $category_list
  global $conn;
  $stmt = $conn->prepare($query);
  $stmt->execute();
  $category_list = $stmt->fetchAll();
  // Build a pill around each name in $category_list, and mark first pill as active
  for ($i = 0; $i < count($category_list); $i++) {
    $category = $category_list[$i][0];
    echo '<li class="nav-item">'
         . '<a class="nav-link' . ($i == 0 ? ' active"' : '"' ) . ' data-toggle="pill" '
         . 'id="' . $category . '-tab" href="#' . $category . '" role="tab" '
         . 'aria-controls="' . $category . '-tab" aria-selected=' . ($i == 0 ? '"true"' : '"false"' )
         . '>' . $category . '</a>'
         . '</li>';
  }
  // Final pill (displays all results)
  echo '<li class="nav-item">'
       . '<a class="nav-link" data-toggle="tab" id="All-' . $type . '-tab" href="#All-' . $type . '" '
       . 'role="tab" aria-controls="all-' . $type . '-tab" aria-selected="false">(All)</a>'
       . '</li>'
       . '</ul>';

  // Tab contents
  echo '<div class="tab-content" id="pills-tabContent">';
  // Builds each tab
  for ($i = 0; $i < count($category_list); $i++) {
    $category = $category_list[$i][0];
    if ($type != 'Other') $desc = ' (' . $category_list[$i][1] . ')';
		else $desc = '';
    // Opening div tag
    echo '<div id="' . $category . '" class="tab-pane fade ' . ($i == 0 ? 'show active' : '')
         . '" role="tabpanel" aria-labelledby="' . $category . '-tab">';
    // Tab header
    echo '<h3 class="text-center">' . $category . $desc . '</h3>';
    build_tab_tables($category, $type);
    echo '</div>';
  }
  // Final tab
  echo '<div id="All-' . $type . '" class="tab-pane fade" '
       . 'role="tabpanel" aria-labelledby="All-' . $type . '-tab">'
       . '<h3 class="text-center">All species</h3>';
  build_tab_tables('All', $type);
  echo '</div>' . '<p>&nbsp;</p>';
}

/* Helper function for build_tabs() that builds the actual tables for each tab.
		 $category: The family or type name whose members should populate this table, or 'All' if the table should contain all results of type $type
	   $type: 'Butterfly', 'Moth', 'Bee', or 'Other'; any unknown value will be treated as 'Other' */
function build_tab_tables($category, $type) {
	if ($type == 'Butterfly' || $type == 'Moth') {
		$style = 'l';
    $query = "SELECT latin_name, common_name, family_name, img_url FROM Lep_full WHERE subtype='$type'" . ($category != 'All' ? " AND family_name = '$category'" : "") . " ORDER BY family_name, latin_name";
	}
	else if ($type == 'Bee') {
		$style = 'b';
    $query = "SELECT latin_name, common_name, family_name, img_url FROM Bee_full" . ($category != 'All' ? " WHERE family_name='$category'" : "") . " ORDER BY family_name, latin_name";
	}
  else { // Assume $type == 'Other'
    $style = 'o';
    $query = "SELECT latin_name, common_name, type, subtype, img_url FROM Creature_full WHERE latin_name NOT IN (SELECT latin_name FROM Bee) AND latin_name NOT IN (SELECT latin_name FROM Lepidopteran)" . ($category != 'All' ? " AND type='$category'" : "") . " ORDER BY type, subtype, latin_name";
  }

	global $conn;
	$stmt = $conn->prepare($query);
	$stmt->execute();

	$num_col = $stmt->columnCount();

	// Start of table
	echo '<table style="width: 80%">';
  // Header row: thumbnail image, latin name, common name, subtype (for other.php), sightings count
	echo '<tr><th>&nbsp;</th><th>Latin name</th><th>Common name</th>'
       . ($type == 'Other' ? '<th>Subtype</th>' : '') . '<th>Sightings</th></tr>';
  // Fill data cells for each row
	while ($row = $stmt->fetch()) {
		$name = $row['latin_name'];
		$substmt = $conn->prepare("SELECT COUNT(DISTINCT date) FROM Log WHERE latin_name='$name'");
		$substmt->execute();
		$seen = $substmt->fetch()[0];

    // If there's been at least one sighting, color-code the whole row
		if  ($seen == '0') echo '<tr>';
    else {
			if (explode(' ', $row['latin_name'])[1] == 'spp') echo '<tr class="seen-' . $style . '-genus">';
			else echo '<tr class="seen-' . $style . '">';
		}

		$lat = $row['latin_name'];
		$com = $row['common_name'];
		echo '<td>';
    thumbnail($row['img_url'], $row['latin_name'], '2rem'); // Thumbnail cell
    echo '</td>';
		echo '<td><a href="view.php?spp=' . $lat . '">' . $lat . '</a></td>'; // Latin name
		echo '<td>' . $com . '</td>'; // Common name
    if ($type == 'Other') echo '<td>' . $row['subtype'] . '</td>'; // Subtype
		echo '<td>' . $seen . '</td>'; // Number of sightings
		echo '</tr>';
	}
	echo '</table>';
}

/*******************************************************/
/* FUNCTIONS THAT PRINT THINGS WITHIN ELEMENTS */
/*******************************************************/

/* Builds a list of months (e.g. months a plant is in bloom) with tooltips */
/* Note: Eventually the bloom data will be converted to more precise date-based
   logging, which will probably render this defunct; the tooltips are the
	 compromise in the meantime. */
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

/* Breaks a notes field apart into a bulleted list. Uses periods as the delimiter,
   so each sentence ends up on a different line; will probably edit data later
	 to use a different delimiter for more flexibility. */
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
