<?php
/*
    Builds the contents for the main wildlife category pages. Currently, this
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
    if ($type != 'Other') $desc = $category_list[$i][1];
    // Opening div tag
    echo '<div id="' . $category . '" class="tab-pane fade ' . ($i == 0 ? 'show active' : '')
         . '" role="tabpanel" aria-labelledby="' . $category . '-tab">';
    // Tab header
    echo '<h3 class="text-center">' . $category . ($type != 'Other' ? ' (' . $desc . ')' : '') . '</h3>';
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

/*
    Builds the actual tables for each tab.

    $category: The family or type name whose members should populate this table,
        or 'All' if the table should contain all results of type $type
    $type: 'Butterfly', 'Moth', 'Bee', or 'Other'; any unknown value will be treated as 'Other'
*/
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
		thumbnail($row['img_url'], $row['latin_name'], '2rem'); // Thumbnail cell
		echo '<td><a href="view.php?spp=' . $lat . '">' . $lat . '</a></td>'; // Latin name
		echo '<td>' . $com . '</td>'; // Common name
    if ($type == 'Other') echo '<td>' . $row['subtype'] . '</td>'; // Subtype
		echo '<td>' . $seen . '</td>'; // Number of sightings
		echo '</tr>';
	}
	echo '</table>';
}
?>
