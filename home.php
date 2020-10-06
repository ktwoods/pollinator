<?php
$cur_page = 'home';
include 'header.html';

// Lists for generating sample species from each category
$stmt = $conn->prepare("SELECT latin_name, common_name, img_url FROM Plant WHERE img_url != ''");
$stmt->execute();
$plant_species = $stmt->fetchAll();
$stmt = $conn->prepare("SELECT latin_name, common_name, img_url FROM Lep_full WHERE img_url != ''");
$stmt->execute();
$lep_species = $stmt->fetchAll();
$stmt = $conn->prepare("SELECT latin_name, common_name, img_url FROM Bee_full WHERE img_url != ''");
$stmt->execute();
$bee_species = $stmt->fetchAll();
$stmt = $conn->prepare("SELECT latin_name, common_name, img_url FROM Other WHERE img_url != ''");
$stmt->execute();
$misc_species = $stmt->fetchAll();

/* Stats by time period: */
// Today
$stmt = $conn->prepare("SELECT COUNT(latin_name) AS spp FROM Log WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) = 0");
$stmt->execute();
$today = $stmt->fetch(PDO::FETCH_ASSOC);
// Last 7 days
$stmt = $conn->prepare("SELECT COUNT(DISTINCT latin_name) AS spp, COUNT(latin_name) AS logs FROM Log WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) <= 7");
$stmt->execute();
$seven_day = $stmt->fetch(PDO::FETCH_ASSOC);
// Last 30 days
$stmt = $conn->prepare("SELECT COUNT(DISTINCT latin_name) AS spp, COUNT(latin_name) AS logs FROM Log WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) <= 30");
$stmt->execute();
$thirty_day = $stmt->fetch(PDO::FETCH_ASSOC);
// Since start of year
$stmt = $conn->prepare("SELECT COUNT(distinct latin_name) AS spp, COUNT(latin_name) AS logs FROM Log WHERE date LIKE CONCAT(YEAR(CURDATE()),'%')");
$stmt->execute();
$year = $stmt->fetch(PDO::FETCH_ASSOC);
// All logs
$stmt = $conn->prepare("SELECT COUNT(distinct latin_name) AS spp, COUNT(latin_name) AS logs FROM Log");
$stmt->execute();
$all_time = $stmt->fetch(PDO::FETCH_ASSOC);
/* Stats by family and type: */
// Counts for (seen) butterfly species by family
$bfly; $bee; $moth;
$stmt = $conn->prepare("SELECT family_name AS title, COUNT(latin_name) AS count FROM Lep_full WHERE latin_name IN (SELECT latin_name FROM Log) AND subtype = 'Butterfly' AND latin_name NOT LIKE '% spp' GROUP BY family_name");
$stmt->execute();
$bfly_seen_grouped = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Counts for (all) butterfly species by family
$stmt = $conn->prepare("SELECT family_name AS title, COUNT(latin_name) AS count FROM Lep_full WHERE subtype = 'Butterfly' AND latin_name NOT LIKE '% spp' GROUP BY family_name");
$stmt->execute();
$bfly_all_grouped = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Counts for (seen) bee species by family
$stmt = $conn->prepare("SELECT family_name AS title, COUNT(latin_name) AS count FROM Bee_full WHERE latin_name IN (SELECT latin_name FROM Log) AND latin_name NOT LIKE '% spp' GROUP BY family_name");
$stmt->execute();
$bee_seen_grouped = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Counts for (all) bee species by family
$stmt = $conn->prepare("SELECT family_name AS title, COUNT(latin_name) AS count FROM Bee_full WHERE latin_name NOT LIKE '% spp' GROUP BY family_name");
$stmt->execute();
$bee_all_grouped = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Counts for (seen) species grouped by subtype
$stmt = $conn->prepare("SELECT subtype AS title, COUNT(DISTINCT latin_name) AS count FROM Log JOIN Creature_full USING (latin_name) WHERE latin_name NOT LIKE '% spp' GROUP BY subtype");
$stmt->execute();
$groups = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
$bfly_seen_grouped[] = array('title' => 'Moths', 'count' => $groups['Moth'][0]);
array_unshift($bfly_seen_grouped, array('title' => 'Butterflies', 'count' => $groups['Butterfly'][0]));
array_unshift($bee_seen_grouped, array('title' => 'Bees', 'count' => $groups['Bee (long-tongued)'][0] + $groups['Bee (short-tongued)'][0]));
// Counts for (all) species grouped by subtype
$stmt = $conn->prepare("SELECT subtype, COUNT(latin_name) AS spp FROM Creature_full WHERE latin_name NOT LIKE '% spp' GROUP BY subtype");
$stmt->execute();
$groups = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
$bfly_all_grouped[] = array('title' => 'Moths', 'count' => $groups['Moth'][0]);
array_unshift($bfly_all_grouped, array('title' => 'Butterflies', 'count' => $groups['Butterfly'][0]));
array_unshift($bee_all_grouped, array('title' => 'Bees', 'count' => $groups['Bee (long-tongued)'][0] + $groups['Bee (short-tongued)'][0]));

// Image thumbnails
$thumbs;
$stmt = $conn->prepare("SELECT latin_name, common_name, img_url FROM Log JOIN Creature_full USING (latin_name) WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) = 0 ORDER BY type, subtype, family_name, latin_name");
$stmt->execute();
$thumbs['today'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $conn->prepare("SELECT DISTINCT latin_name, common_name, img_url FROM (SELECT latin_name, common_name, img_url, date FROM Log JOIN Creature_full USING (latin_name) WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) <= 7 ORDER BY date DESC, type, subtype, family_name, latin_name) Past_logs");
$stmt->execute();
$thumbs['seven_days'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $conn->prepare("SELECT DISTINCT latin_name, common_name, img_url FROM (SELECT latin_name, common_name, img_url, date FROM Log JOIN Creature_full USING (latin_name) WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) <= 30 ORDER BY date DESC, type, subtype, family_name, latin_name) Past_logs LIMIT 40");
$stmt->execute();
$thumbs['thirty_days'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
      <div class="col-md-3"><div id="plantCard" class="card text-center">
        <div class="card-header"><a href="plants.php" class="btn btn-lg btn-p">Plants</a></div>
        <a id="plantSampleImg" href="#"><img class="card-img-top" alt="Plant species"></img></a>
        <div id="plantSampleSpecies" class="card-body text-muted plant-color-2"></div>
      </div></div>
      <div class="col-md-3"><div id="lepCard" class="card text-center">
        <div class="card-header"><a href="plants.php" class="btn btn-lg btn-l">Butterflies & moths</a></div>
        <a  id="lepSampleImg"href="#"><img class="card-img-top" alt="Butterfly/moth species"></img></a>
        <div id="lepSampleSpecies" class="card-body text-muted lep-color-2"></div>
      </div></div>
      <div class="col-md-3"><div id="beeCard" class="card text-center">
        <div class="card-header"><a href="plants.php" class="btn btn-lg btn-b">Bees</a></div>
        <a id="beeSampleImg" href="#"><img class="card-img-top" alt="Bee species"></img></a>
        <div id="beeSampleSpecies" class="card-body text-muted bee-color-2"></div>
      </div></div>
      <div class="col-md-3"><div id="miscCard" class="card text-center">
        <div class="card-header"><a href="plants.php" class="btn btn-lg btn-m">Other species</a></div>
        <a id="miscSampleImg" href="#"><img class="card-img-top" alt="Creature species"></img></a>
        <div id="miscSampleSpecies" class="card-body text-muted misc-color-2"></div>
      </div></div>
  </div>
  <div>&nbsp;</div>
  <!-- Bottom half of page: Log stats -->
  <div class="jumbotron">
    <div class="col">
      <h1 class="display-4">Wildlife logs</h1>
      <p class="lead">A summary of recent and lifetime sightings.</p>
      <p><a href="update_logs.php" class="btn btn-d btn-lg" style="color: white">New log</a></p>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <!-- Card with stats breakdown by time period -->
      <div id="timeStats" class="card"></div>
      <div>&nbsp;</div>
      <!-- Cards with stats breakdown by species category (butterfly/moth and bee, with further breakdowns by butterfly/bee family) -->
      <div id="butterflyStats" class="card"></div>
      <div>&nbsp;</div>
      <div id="beeStats" class="card"></div>
    </div>
    <!-- Cards with thumbnail links for species from today, the past 7 days, and the past 30 days (max 40 species) -->
    <div class="col-md-auto" style="max-width: 42rem">
      <div class="card">
        <div class="card-header"><strong>Today's species</strong></div>
        <div id="dayPics" class="card-body" style="padding: 0px"></div>
      </div>
      <div>&nbsp;</div>
      <div class="card">
        <div class="card-header"><strong>Past week's species</strong></div>
        <div id="weekPics" class="card-body" style="padding: 0px"></div>
      </div>
      <div>&nbsp;</div>
      <div class="card">
        <div class="card-header"><strong>All recent species</strong></div>
        <div id="monthPics" class="card-body" style="padding: 0px"></div>
      </div>
      <div>&nbsp;</div>
    </div>
  </div>
  <p>&nbsp;</p>
</div>

<script>
  // Category cards
  const images = {
    plant: <?=json_encode($plant_species)?>,
    lep: <?=json_encode($lep_species)?>,
    bee: <?=json_encode($bee_species)?>,
    misc: <?=json_encode($misc_species)?>
  };
  for (let list in images) {
    if (list) {
      const index = Math.floor(Math.random() * images[list].length);
      const species = images[list][index];
      $(`#${list}SampleImg`).attr('href', (list === 'plant' ? 'view_plant.php?sp=' : 'view.php?sp=') + species['latin_name']);
      $(`#${list}SampleImg>img`).attr('src', species['img_url']);
      $(`#${list}SampleSpecies`).html(`${species['common_name']}<br/><i>${species['latin_name']}</i>`);
    }
  }

  // Stat cards
  function speciesVsLogs(count) {
    return '<span>' + count['spp'] + (+count['spp'] === 1 ? ' sp' : ' spp')
    + (count['logs'] ? ', ' + count['logs'] + (+count['logs'] === 1 ? ' log' : ' logs') : '') + '</span>';
  }
  function speciesVsCategory(count) {
    return '<span>' + count.seen + ' / ' + count.all + (+count.all === 1 ? ' sp' : ' spp') + '</span>';
  }
  function statCard(card, stats, format) {
    let container = card;
    let tag = 'div';
    let itemClass = 'card-header';

    for (let i = 0; i < stats.length; i++) {
      let element = $(`<${tag} class="${itemClass} d-flex justify-content-between"><span>${stats[i].title}</span> ${format(stats[i].count)}</${tag}>`);
      container.append(element);

      if (i === 0) {
        container = $('<ul class="list-group list-group-flush"></ul>');
        card.append(container);
        tag = 'li';
        itemClass = 'list-group-item';
      }
    }
  }
  function formatGroupStats(seenArr, allArr) {
    let combined = [];
    for (let i = 0; i < seenArr.length; i++) {
      let title = seenArr[i]['title'];
      combined.push({ title: seenArr[i]['title'], count: {seen: seenArr[i]['count'], all: allArr[i]['count']} });
    }
    return combined;
  }

  const timeStats = [
    {title: 'Today', count: <?=json_encode($today)?>},
    {title: 'Past 7 days', count: <?=json_encode($seven_day)?>},
    {title: 'Past 30 days', count: <?=json_encode($thirty_day)?>},
    {title: 'This year', count: <?=json_encode($year)?>},
    {title: 'All time', count: <?=json_encode($all_time)?>}
  ];
  statCard($('#timeStats'), timeStats, speciesVsLogs);

  statCard($('#butterflyStats'), formatGroupStats(<?=json_encode($bfly_seen_grouped)?>, <?=json_encode($bfly_all_grouped)?>), speciesVsCategory);
  let colorRow = $('#butterflyStats').children().first();
  colorRow.addClass('lep-color-2').children().first().html('<strong>' + colorRow.children().first().html() + '</strong>');
  colorRow = colorRow.next().children().last();
  colorRow.addClass('lep-color-2').children().first().html('<strong>' + colorRow.children().first().html() + '</strong>');

  statCard($('#beeStats'), formatGroupStats(<?=json_encode($bee_seen_grouped)?>, <?=json_encode($bee_all_grouped)?>), speciesVsCategory);
  colorRow = $('#beeStats').children().first();
  colorRow.addClass('bee-color-2').children().first().html('<strong>' + colorRow.children().first().html() + '</strong>');

  // Thumbnail cards
  function thumbList(cardBody, images) {
    for (let img of images) {
      cardBody.append(thumbnail(img['img_url'], img['latin_name'], '4rem', 'view.php', img['common_name']));
    }
  }
  const thumbData = <?=json_encode($thumbs)?>;
  thumbList($('#dayPics'), thumbData['today']);
  thumbList($('#weekPics'), thumbData['seven_days']);
  thumbList($('#monthPics'), thumbData['thirty_days']);
</script>
<?php include 'footer.html'; ?>
