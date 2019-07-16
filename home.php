<?php
$cur_page = 'home';
include 'header.php';
include_once 'connect.php';
include_once 'build_table.php';

function random_spp($table) {
  global $conn;
  $stmt = $conn->prepare("SELECT latin_name, common_name, img_url FROM $table WHERE img_url != ''");
  $stmt->execute();
  $images = $stmt->fetchAll();
  $random = $images[rand(0, count($images)-1)];
  return $images[rand(0, count($images)-1)];
}

$rand_plant = random_spp("Plant");
$rand_bee = random_spp("Bee_full");
$rand_lep = random_spp("Lep_full");
$rand_oth = random_spp("Other");
?>
<div class="container">
  <div class="text-center">&nbsp;</div>
  <div class="row align-items-end">
    <div class="col-sm">
      <div class="card text-center">
        <a href="view_plant.php?name=<?php echo $rand_plant['latin_name'] ?>"><img src="<?php echo $rand_plant['img_url'] ?>" class="card-img-top" alt="Plants"></a>
        <div class="card-header text-muted"><?php echo $rand_plant['common_name'].'<br/> <em>'.$rand_plant['latin_name'].'</em>' ?></div>
        <div class="card-body">
          <a href="plants.php" class="btn btn-p"><strong>All plants</strong></a>
        </div>
      </div>
    </div>
    <div class="col-sm">
      <div class="card text-center">
        <a href="view.php?spp=<?php echo $rand_lep['latin_name'] ?>"><img src="<?php echo $rand_lep['img_url'] ?>" class="card-img-top" alt="Butterflies & moths"></a>
        <div class="card-header text-muted"><?php echo $rand_lep['common_name'].'<br/> <em>'.$rand_lep['latin_name'].'</em>' ?></div>
        <div class="card-body">
          <a href="lepidop.php" class="btn btn-l"><strong>All butterflies & moths</strong></a>
        </div>
      </div>
    </div>
    <div class="col-sm">
      <div class="card text-center">
        <a href="view.php?spp=<?php echo $rand_bee['latin_name'] ?>"><img src="<?php echo $rand_bee['img_url'] ?>" class="card-img-top" alt="Bees"></a>
        <div class="card-header text-muted"><?php echo $rand_bee['common_name'].'<br/><em>'.$rand_bee['latin_name'].'</em>' ?></div>
        <div class="card-body">
          <a href="bees.php" class="btn btn-b"><strong>All bees</strong></a>
        </div>
      </div>
    </div>
    <div class="col-sm">
      <div class="card text-center">
        <a href="view.php?spp=<?php echo $rand_oth['latin_name'] ?>"><img src="<?php echo $rand_oth['img_url'] ?>" class="card-img-top" alt="Other creatures"></a>
        <div class="card-header text-muted"><?php echo $rand_oth['common_name'].'<br/> <em>'.$rand_oth['latin_name'].'</em>' ?></div>
        <div class="card-body">
          <a href="other.php" class="btn btn-o"><strong>All other creatures</strong></a>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <div class="text-center">&nbsp;</div>
      <div class="text-center"><a href="new_log.php" class="btn btn-d">New log</a></div>
    </div>
  </div>
</div>


<?php
echo '<p>&nbsp;</p>';
include 'footer.html';
?>
