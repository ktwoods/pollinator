<?php
$cur_page = 'home';
include 'header.php';
include_once 'connect.php';
include_once 'build_table.php';

function build_card($table) {
  global $conn;
  $stmt = $conn->prepare("SELECT latin_name, common_name, img_url FROM $table WHERE img_url != ''");
  $stmt->execute();
  $images = $stmt->fetchAll();
  $rand_spp = $images[rand(0, count($images)-1)];
  $rand_link = "view.php?spp=".$rand_spp['latin_name'];
  if ($table == "Plant") {
    $header = "Plants";
    $rand_link = "view_plant.php?name=".$rand_spp['latin_name'];
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
?>
<div class="container">
  <div class="jumbotron">
    <div class="col">
      <h1 class="display-4">Species profiles</h1>
      <p class="lead">View and search categories of plants, insects, and animals by clicking one of the buttons below.</p>
    </div>
  </div>
  <div class="row">
      <?php
      build_card("Plant");
      build_card("Lep_full");
      build_card("Bee_full");
      build_card("Other");
      ?>
  </div>
<div>&nbsp;</div>
  <div class="jumbotron">
    <div class="col">
      <h1 class="display-4">Wildlife logs</h1>
      <p class="lead">A summary of recent and lifetime sightings.</p>
      <p><a href="new_log.php" class="btn btn-d btn-lg" style="color: white">New log</a></p>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-4">
      <div class="card">
        <div class="card-header d-flex justify-content-between"><strong>Today:</strong> <span>10 spp</span></div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between"><strong>Past 7 days:</strong> <span>10 spp, 30 logs</span></li>
          <li class="list-group-item d-flex justify-content-between"><strong>Past 30 days:</strong> <span>35 spp, 67 logs</span></li>
          <li class="list-group-item d-flex justify-content-between"><strong>This year:</strong> <span>109 spp, 342 logs</span></li>
        </ul>
      </div>
      <div>&nbsp;</div>
      <div class="card">
        <ul class="list-group list-group-flush">
          <li class="sec-l list-group-item d-flex justify-content-between"><strong>Butterflies:</strong> <span>45 / 75 spp</span></li>
          <li class="list-group-item d-flex justify-content-between">Hesperiidae</li>
          <li class="list-group-item d-flex justify-content-between">Lycaenidae</li>
          <li class="list-group-item d-flex justify-content-between">Nymphalidae</li>
          <li class="list-group-item d-flex justify-content-between">Papilionidae</li>
          <li class="list-group-item d-flex justify-content-between">Pieridae</li>
          <li class="sec-l list-group-item d-flex justify-content-between"><strong>Moths:</strong> <span>10 / 100 spp</span></li>
        </ul>
      </div>
      <div>&nbsp;</div>
      <div class="card">
        <ul class="list-group list-group-flush">
          <li class="sec-b list-group-item d-flex justify-content-between"><strong>Bees:</strong> <span>34 / 82 spp</span></li>
          <li class="list-group-item d-flex justify-content-between">Halictidae</li>
          <li class="list-group-item d-flex justify-content-between">Andrenidae</li>
          <li class="list-group-item d-flex justify-content-between">Megachilidae</li>
          <li class="list-group-item d-flex justify-content-between">Apidae</li>
          <li class="list-group-item d-flex justify-content-between">Colletidae</li>
        </ul>
      </div>
    </div>
    <div class="col-sm-8">
      <div class="card">
        <div class="card-header">Today's species</div>
        <div class="card-body text-center">
        <?php
          $stmt = $conn->prepare("SELECT latin_name, common_name, img_url FROM Log JOIN Creature_full USING (latin_name) WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) = 0 ORDER BY type, subtype, family_name, latin_name");
          $stmt->execute();
          $thumbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
          $curline = -1;
          for ($i = 0; $i < count($thumbs); $i++) {
            $url = str_replace('l.', 't.', $thumbs[$i]['img_url']);
            echo '<a href="view.php?spp='.$thumbs[$i]['latin_name'].'">';
            if ($url != '') echo '<img src="'.$url.'" width=80></a>';
            else echo '<div style="width:80px; height:80px; display:inline-block; background-color:#e9ecef; vertical-align: middle">&nbsp;</div></a>';
          }
        ?>
        </div>
      </div>
      <div>&nbsp;</div>
      <div class="card">
        <div class="card-header">Recent species</div>
        <div class="card-body text-center">
        <?php
          $stmt = $conn->prepare("SELECT DISTINCT latin_name, common_name, img_url FROM (SELECT latin_name, common_name, img_url, date FROM Log JOIN Creature_full USING (latin_name) WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) <= 30 ORDER BY date DESC, type, subtype, family_name, latin_name) Past_logs LIMIT 40");
          $stmt->execute();
          $thumbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
          $curline = -1;
          for ($i = 0; $i < count($thumbs); $i++) {
            $url = str_replace('l.', 't.', $thumbs[$i]['img_url']);
            echo '<a href="view.php?spp='.$thumbs[$i]['latin_name'].'">';
            if ($url != '') echo '<img src="'.$url.'" width=80></a>';
            else echo '<div style="width:80px; height:80px; display:inline-block; background-color:#e9ecef; vertical-align: middle">&nbsp;</div></a>';
          }
        ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
echo '<p>&nbsp;</p>';
include 'footer.html';
?>
