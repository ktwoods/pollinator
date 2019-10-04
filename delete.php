<?php
include_once 'funcs_general.php';
include_once 'header.html';

$type = get_type($_GET['spp']);

if ($type == 'Plant') $ret_url = 'plants.php';
else {
	$type = 'Creature';
	$ret_url = 'other.php';
	if ($type == 'Lepidopteran') $ret_url = 'lepidop.php';
	else if ($type == 'Bee') $ret_url = 'bees.php';
}
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-sm">
			<?php
			$stmt = $conn->prepare("DELETE FROM $type WHERE latin_name = ?");
			$stmt->bindValue(1, $_GET['spp']);
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

<?php include_once 'footer.html' ?>
