<?php
$cur_page = 'home';
include 'header.html';
include_once 'connect.php';
include_once 'funcs_general.php';

/* Builds card to represent a species category (plants, butterflies & moths,
   bees, everything else). Each card has three elements:
   -- Card header, with button linking to category page
   -- Randomly selected image of a species in that section, which links to its page
   -- Caption for the image with its common name and Latin name

   $table = name of the SQL table corresponding to that category ('Plant', 'Lepidopteran', 'Bee', or 'Creature') */
function category_card($table) {
  global $conn;
  $stmt = $conn->prepare("SELECT latin_name, common_name, img_url FROM $table WHERE img_url != ''");
  $stmt->execute();
  $images = $stmt->fetchAll();
  $rand_spp = $images[rand(0, count($images)-1)];
  $rand_link = "view.php?spp=".$rand_spp['latin_name'];
  if ($table == "Plant") {
    $header = "Plants";
    $rand_link = "view_plant.php?spp=".$rand_spp['latin_name'];
    $tbl_link = "plants.php";
    $btn_class = "p";
  }
  else if ($table == "Lep_full") {
    $header = "Butterflies & moths";
    $tbl_link = "lepidop.php";
    $btn_class = "l";
  }
  else if ($table == "Bee_full") {
    $header = "Bees";
    $tbl_link = "bees.php";
    $btn_class = "b";
  }
  else {
    $header = "Other species";
    $tbl_link = "other.php";
    $btn_class = "o";
  }
  echo '<div class="col-md-3"><div class="card text-center">';
  echo '<div class="card-header"><a href="'.$tbl_link.'" class="btn btn-lg btn-'.$btn_class.'">'.$header.'</a></div>';
  echo '<a href="'.$rand_link.'"><img src="'.$rand_spp['img_url'].'" class="card-img-top" alt="'.$table.'"></a>';
  echo '<div class="card-body text-muted sec-'.$btn_class.'">'.$rand_spp['common_name'].'<br/><em>'.$rand_spp['latin_name'].'</em></div>';
    echo '</div></div>';
}

/* Builds card indicating number of species seen out of total number of species
   known in each major category (butterflies, moths, bees) and for families
   within butterfly and bee. Moth families are not listed for space reasons:
   according to BAMONA, my county has 96 butterfly species within 5 families,
   and 750 moth species within 40 families. Since I'm not interested in tracking
   all 750, the usual "checklist" stats have relatively little meaning.

   $category = value of either 'bee' or 'bfly', to indicate which of the two cards should be built */
function category_stats_card($category) {
  global $stats;
  $full_list = $category . '_all_fams';
  $seen_list = $category . '_seen_fams';
  if ($category == 'bfly') {
    $class = 'sec-l';
    $title = 'Butterflies';
  }
  else {
    $class = 'sec-b';
    $title = 'Bees';
  }

  // Print header list item for category
  echo '<div class="card"><ul class="list-group list-group-flush">'
       . '<li class="'.$class.' list-group-item d-flex justify-content-between" data-toggle="collapse" data-target="#'.$category.'famlist">'
       . '<span><i class="fas fa-caret-down"></i> <strong>'.$title.'</strong></span>'
       . '<span>'.$stats[$category.'_seen'].' / '.$stats[$category.'_total'].' spp</span>'
       . '</li>';

  // Print list item for each family
  echo '<span class="collapse" id="' . $category . 'famlist">';
  for ($i = 0; $i < count($stats[$full_list]); $i++) {
    $family = $stats[$full_list][$i]['family_name'];
    $num_total = $stats[$full_list][$i]['num_spp'];
    if (isset($stats[$seen_list][$family])) $num_seen = $stats[$seen_list][$family][0];
    else $num_seen = 0;
    echo '<li class="list-group-item d-flex justify-content-between">'.$family.' </span><span>'.$num_seen.' / '.$num_total.' spp</span></li>';
  }
  echo '</span>';
  // Print final list item for moth category
  if ($category == 'bfly') {
    echo '<li class="sec-l list-group-item d-flex justify-content-between"><strong>Moths</strong> <span> '. $stats['moth_seen'].' / '.$stats['moth_total'].' spp</span></li>';
  }

  echo '</ul></div>';
}

/* Builds card containing image thumbnails of recently seen species.

   $card_title = header text for the card
   $query = SQL query used to generate the set of thumbnails */
function thumbnails_card($card_title, $query) {
  global $conn;
  $stmt = $conn->prepare($query);
  $stmt->execute();
  $thumbs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo '<div class="card"><div class="card-header">'.$card_title.'</div><div class="card-body" style="padding:0px">';
  for ($i = 0; $i < count($thumbs); $i++) {
    thumbnail($thumbs[$i]['img_url'], $thumbs[$i]['latin_name'], '4rem', 'view.php', $thumbs[$i]['common_name']);
  }
  echo '</div></div>';
}

/* Generates the stats used to populate this page, and returns them as an associative array. Array keys are:
   -- 'today' (integer count)
   -- 'seven_day', 'thirty_day', 'year', 'all' (associative arrays with keys 'num_spp' and 'num_logs')
   -- 'bfly_seen_fams', 'bfly_all_fams', 'bee_seen_fams', 'bee_all_fams' (indexed array of family stats, where each item is an associative array with keys 'family_name' and 'num_spp')
   -- 'moth_seen', 'bfly_seen', 'bee_seen', 'moth_total', 'bfly_total', 'bee_total' (integer count) */
function getStats() {
  global $conn;

  /* Stats by time period: */
  // Today
  $stmt = $conn->prepare("SELECT COUNT(latin_name) AS num_spp FROM Log WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) = 0");
  $stmt->execute();
  $stats['today'] = $stmt->fetch(PDO::FETCH_ASSOC)['num_spp'];
  // Last 7 days
  $stmt = $conn->prepare("SELECT COUNT(DISTINCT latin_name) AS num_spp, COUNT(latin_name) AS num_logs FROM Log WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) <= 7");
  $stmt->execute();
  $stats['seven_day'] = $stmt->fetch(PDO::FETCH_ASSOC);
  // Last 30 days
  $stmt = $conn->prepare("SELECT COUNT(DISTINCT latin_name) AS num_spp, COUNT(latin_name) AS num_logs FROM Log WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) <= 30");
  $stmt->execute();
  $stats['thirty_day'] = $stmt->fetch(PDO::FETCH_ASSOC);
  // Since start of year
  $stmt = $conn->prepare("SELECT COUNT(distinct latin_name) AS num_spp, COUNT(latin_name) AS num_logs FROM Log WHERE date LIKE CONCAT(YEAR(CURDATE()),'%')");
  $stmt->execute();
  $stats['year'] = $stmt->fetch(PDO::FETCH_ASSOC);
  // All logs
  $stmt = $conn->prepare("SELECT COUNT(distinct latin_name) AS num_spp, COUNT(latin_name) AS num_logs FROM Log");
  $stmt->execute();
  $stats['all'] = $stmt->fetch(PDO::FETCH_ASSOC);

  /* Stats by family and type: */
  // Counts for (seen) butterfly species by family
  $stmt = $conn->prepare("SELECT family_name, COUNT(latin_name) AS num_spp FROM Lep_full WHERE latin_name IN (SELECT latin_name FROM Log) AND subtype = 'Butterfly' AND latin_name NOT LIKE '% spp' GROUP BY family_name");
  $stmt->execute();
  $stats['bfly_seen_fams'] = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
  // Counts for (all) butterfly species by family
  $stmt = $conn->prepare("SELECT family_name, COUNT(latin_name) AS num_spp FROM Lep_full WHERE subtype = 'Butterfly' AND latin_name NOT LIKE '% spp' GROUP BY family_name");
  $stmt->execute();
  $stats['bfly_all_fams'] = $stmt->fetchAll();
  // Counts for (seen) bee species by family
  $stmt = $conn->prepare("SELECT family_name, COUNT(latin_name) AS num_spp FROM Bee_full WHERE latin_name IN (SELECT latin_name FROM Log) AND latin_name NOT LIKE '% spp' GROUP BY family_name");
  $stmt->execute();
  $stats['bee_seen_fams'] = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
  // Counts for (all) bee species by family
  $stmt = $conn->prepare("SELECT family_name, COUNT(latin_name) AS num_spp FROM Bee_full WHERE latin_name NOT LIKE '% spp' GROUP BY family_name");
  $stmt->execute();
  $stats['bee_all_fams'] = $stmt->fetchAll();

  // Counts for (seen) species grouped by subtype
  $stmt = $conn->prepare("SELECT subtype, COUNT(DISTINCT latin_name) AS num_spp FROM Log JOIN Creature_full USING (latin_name) WHERE latin_name NOT LIKE '% spp' GROUP BY subtype");
  $stmt->execute();
  $groups = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
  $stats['moth_seen'] = $groups['Moth'][0];
  $stats['bfly_seen'] = $groups['Butterfly'][0];
  $stats['bee_seen'] = $groups['Bee (long-tongued)'][0] + $groups['Bee (short-tongued)'][0];
  // Counts for (all) species grouped by subtype
  $stmt = $conn->prepare("SELECT subtype, COUNT(latin_name) AS num_spp FROM Creature_full WHERE latin_name NOT LIKE '% spp' GROUP BY subtype");
  $stmt->execute();
  $groups = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
  $stats['moth_total'] = $groups['Moth'][0];
  $stats['bfly_total'] = $groups['Butterfly'][0];
  $stats['bee_total'] = $groups['Bee (long-tongued)'][0] + $groups['Bee (short-tongued)'][0];

  return $stats;
}

$stats = getStats();
?>

<div class="container">
  <!-- Top half of page: Links to main category pages -->
  <div class="jumbotron">
    <div class="col">
      <h1 class="display-4">Species profiles</h1>
      <p class="lead">View and search categories of plants, insects, and animals by clicking one of the buttons below.</p>
    </div>
  </div>
  <div class="row">
      <?php
      category_card("Plant");
      category_card("Lep_full");
      category_card("Bee_full");
      category_card("Other");
      ?>
  </div>
  <div>&nbsp;</div>
  <!-- Bottom half of page: Log stats -->
  <div class="jumbotron">
    <div class="col">
      <h1 class="display-4">Wildlife logs</h1>
      <p class="lead">A summary of recent and lifetime sightings.</p>
      <p><a href="new_log.php" class="btn btn-d btn-lg" style="color: white">New log</a></p>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <!-- Card with stats breakdown by time period -->
      <div class="card">
        <div class="card-header d-flex justify-content-between"><strong>Today</strong> <span><?php echo $stats['today'] ?> spp</span></div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between"><strong>Past 7 days</strong> <span><?php echo $stats['seven_day']['num_spp'] ?> spp, <?php echo $stats['seven_day']['num_logs'] ?> logs</span></li>
          <li class="list-group-item d-flex justify-content-between"><strong>Past 30 days</strong> <span><?php echo $stats['thirty_day']['num_spp'] ?> spp, <?php echo $stats['thirty_day']['num_logs'] ?> logs</span></li>
          <li class="list-group-item d-flex justify-content-between"><strong>This year</strong> <span><?php echo $stats['year']['num_spp'] ?> spp, <?php echo $stats['year']['num_logs'] ?> logs</span></li>
          <li class="list-group-item d-flex justify-content-between"><strong>All time</strong> <span><?php echo $stats['all']['num_spp'] ?> spp, <?php echo $stats['all']['num_logs'] ?> logs</span></li>
        </ul>
      </div>
      <div>&nbsp;</div>
      <!-- Cards with stats breakdown by species category (butterfly/moth and bee, with further breakdowns by butterfly/bee family) -->
      <?php category_stats_card('bfly'); ?>
      <div>&nbsp;</div>
      <?php category_stats_card('bee'); ?>
    </div>
    <!-- Cards with thumbnail links for species from today and the last 30 days (max 40 species) -->
    <div class="col-md-auto" style="max-width: 42rem">
      <?php
      thumbnails_card('Today\'s species', "SELECT latin_name, common_name, img_url FROM Log JOIN Creature_full USING (latin_name) WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) = 0 ORDER BY type, subtype, family_name, latin_name");
      ?>
      <div>&nbsp;</div>
      <?php
      thumbnails_card('Recent species', "SELECT DISTINCT latin_name, common_name, img_url FROM (SELECT latin_name, common_name, img_url, date FROM Log JOIN Creature_full USING (latin_name) WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) <= 30 ORDER BY date DESC, type, subtype, family_name, latin_name) Past_logs LIMIT 40");
      ?>
    </div>
  </div>
</div>
<?php
echo '<p>&nbsp;</p>';
include 'footer.html';
?>
