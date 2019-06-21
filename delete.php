<?php
include 'header.php';
include_once 'connect.php';
include_once 'build_table.php';

global $conn;
if (isset($_POST['spp'])) $spp = $_POST['spp'];
else $spp = $_GET['spp'];
if (isset($_POST['type'])) $type = $_POST['type'];
else $type = $_GET['type'];
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-sm">
			<?php
			if ($type == 'p') {
				$type = 'Plant';
				$ret_url = 'plants.php';
			}
			else if ($type == 'l') {
				$type = 'Creature';
				$ret_url = 'lepidop.php';
			}
			else if ($type == 'b') {
				$type = 'Creature';
				$ret_url = 'bees.php';
			}
			else if ($type == 'o') {
				$type = 'Creature';
				$ret_url = 'other.php';
			}
			else $type = ''; /* If user tries to fudge the type val in url, it won't do anything */
			
			$stmt = $conn->prepare("DELETE FROM $type WHERE latin_name = ?");
			$stmt->bindValue(1, $spp);
			if ($stmt->execute()) $rows_affected = $stmt->rowCount();
			?>
			<h1 class="text-center">Delete <?php echo $spp ?></h1>
			<div>&nbsp;</div>
			<div class="text-center"><?php echo $rows_affected ?> species deleted.</div>
			<div class="text-center"><a href="<?php echo $ret_url ?>">[Return to main page]</a></div>
			<div>&nbsp;</div><div>&nbsp;</div>
		</div>
	</div>
</div>

<?php include 'footer.html' ?>