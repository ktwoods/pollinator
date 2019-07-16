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
  echo '<div class="col-sm-3"><div class="card text-center">';
  echo '<div class="card-header"><a href="'.$tbl_link.'" class="btn btn-lg btn-'.$btn_class.'">'.$header.'</a></div>';
  echo '<a href="'.$rand_link.'"><img src="'.$rand_spp['img_url'].'" class="card-img-top" alt="'.$table.'"></a>';
  echo '<div class="card-body text-muted sec-'.$btn_class.'">'.$rand_spp['common_name'].'<br/><em>'.$rand_spp['latin_name'].'</em></div>';
    echo '</div></div>';
}
?>
<div class="jumbotron">
  <div class="col">
    <h1 class="display-4">Wildlife logs</h1>
    <p class="lead">Here's some information about what species you've seen recently.</p>
    <p><a href="new_log.php" class="btn btn-d btn-lg">New log</a></p>
  </div>
</div>
  <div class="row">
    <div class="col">
      <div class="text-center">&nbsp;</div>

      <?php
        $stmt = $conn->prepare("SELECT latin_name, img_url, date FROM Log JOIN Creature USING (latin_name) WHERE TIMESTAMPDIFF(DAY, date, CURDATE()) <= 7 ORDER BY date DESC, latin_name ASC");
        $stmt->execute();
        $thumbs = $stmt->fetchAll();
        for ($i = 0; $i < count($thumbs); $i++) {
          $url = str_replace('l.', 't.', $thumbs[$i]['img_url']);
          $now = date_create(date('Y-m-d'));
          $then = date_create($thumbs[$i]['date']);
          $age = (int)date_diff($now, $then)->format('%d');
          #$opacity = abs(20-$age)/10;
          echo '<a href="view.php?spp='.$thumbs[$i]['latin_name'].'"><img src="'.$url.'" height=70></a>';
        }
      ?>
    </div>
  </div>
</div>
<div class="jumbotron">
  <div class="col">
    <h1 class="display-4">Species profiles</h1>
    <p class="lead">View and search categories of plants, insects, and animals by clicking one of the buttons below.</p>
  </div>
</div>
<div class="container">
  <div class="row">
      <?php
      build_card("Plant");
      build_card("Lep_full");
      build_card("Bee_full");
      build_card("Other");
      ?>
  </div>
</div>
<?php
echo '<p>&nbsp;</p>';
include 'footer.html';
?>
