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
    $header = "All plants";
    $rand_link = "view_plant.php?name=".$rand_spp['latin_name'];
    $tbl_link = "plants.php";
    $btn_class = "p";
  }
  else if ($table == "Lep_full") {
    $header = "All butterflies & moths";
    $tbl_link = "lepidop.php";
    $btn_class = "l";
  }
  else if ($table == "Bee_full") {
    $header = "All bees";
    $tbl_link = "bees.php";
    $btn_class = "b";
  }
  else {
    $header = "All other species";
    $tbl_link = "other.php";
    $btn_class = "o";
  }
  echo '<div class="col-sm-3"><div class="card text-center">';
  echo '<a href="'.$rand_link.'"><img src="'.$rand_spp['img_url'].'" class="card-img-top" alt="'.$table.'"></a>';
  echo '<div class="card-header text-muted sec-'.$btn_class.'">'.$rand_spp['common_name'].'<br/><em>'.$rand_spp['latin_name'].'</em></div>';
  echo '<div class="card-body"><a href="'.$tbl_link.'" class="btn btn-'.$btn_class.'"><strong>'.$header.'</strong></a></div>';
  echo '</div></div>';
}
?>
<div class="container">
  <div class="text-center">&nbsp;</div>
  <div class="row align-items-end">
      <?php
      build_card("Plant");
      build_card("Lep_full");
      build_card("Bee_full");
      build_card("Other");
      ?>
  </div>
  <div class="row">
    <div class="col">
      <div class="text-center">&nbsp;</div>
      <div class="text-center"><a href="new_log.php" class="btn btn-d"><strong>New log</strong></a></div>
      <?php
        $stmt = $conn->prepare("SELECT latin_name, img_url, date FROM Log JOIN Creature USING (latin_name) WHERE img_url != '' AND TIMESTAMPDIFF(DAY, date, CURDATE()) <= 7 ORDER BY date DESC");
        $stmt->execute();
        $thumbs = $stmt->fetchAll();
        for ($i = 0; $i < count($thumbs); $i++) {
          $url = str_replace('l.', 't.', $thumbs[$i]['img_url']);
          $now = date_create(date('Y-m-d'));
          $then = date_create($thumbs[$i]['date']);
          $age = (int)date_diff($now, $then)->format('%d');
          $opacity = (14-$age)/10;
          echo '<a href="view.php?spp='.$thumbs[$i]['latin_name'].'"><img src="'.$url.'" height=75 style="opacity: '.$opacity.'"></a>';
        }
      ?>
    </div>
  </div>
</div>

<?php
echo '<p>&nbsp;</p>';
include 'footer.html';
?>
