<?php
include_once 'funcs_general.php';
include_once 'header.html';

$type = get_type($_GET['sp']);

if ($type == 'Plant') $ret_url = 'plants.php';
else {
	$type = 'Creature';
	$ret_url = 'other.php';
	if ($type == 'Lepidopteran') $ret_url = 'lepidop.php';
	else if ($type == 'Bee') $ret_url = 'bees.php';
}

$stmt = $conn->prepare("DELETE FROM $type WHERE latin_name = ?");
if ($stmt->execute(array($_GET['sp']))) $rows_affected = $stmt->rowCount();
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-sm">
			<h1>Deleting</h1>
			<div>&nbsp;</div>
			<div id="count" class="text-center"> species deleted.</div>
			<div class="text-center"><a id="returnLink" href="#">[Return to main page]</a></div>
			<div>&nbsp;</div><div>&nbsp;</div>
		</div>
	</div>
</div>

<script>
	$('h1').first().append(' <i><?=$_GET['sp']?></i>');
	$('#count').prepend(<?=$rows_affected?>);
	$('#returnLink').attr('href', <?=json_encode($ret_url)?>);
</script>

<?php include_once 'footer.html' ?>
