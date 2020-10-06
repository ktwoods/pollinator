<?php
$cur_page = 'bees';
include_once 'header.html';

$stmt = $conn->prepare('SELECT family_name, family_desc FROM Family WHERE family_name IN (SELECT DISTINCT family_name FROM Bee_full)');
$stmt->execute();
$bee_families = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $conn->prepare ('SELECT img_url, latin_name, common_name, family_name FROM Bee_full ORDER BY family_name, latin_name');
$stmt->execute();
$bee_species = $stmt->fetchAll(PDO::FETCH_ASSOC);

$log_counts;
foreach ($bee_species as $sp) {
	$stmt = $conn->prepare('SELECT COUNT(DISTINCT date) AS sightings FROM Log WHERE latin_name="' . $sp['latin_name'] . '"');
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$log_counts[$sp['latin_name']] = $result['sightings'];
}
?>
<!-- "Add species" button -->
<a href="new.php?type=bee" class="btn btn-b" style="margin-top: 15px; position: fixed;"><i class="fas fa-plus"></i> Add species</a>

<!-- Main container -->
<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8" id="beeList">
			<h1 class="text-center">Bees</h1>
		</div>
	</div>
</div>
<p>&nbsp;</p>

<script>
	const beeSpecies = <?=json_encode($bee_species)?>;
	const families = <?=json_encode($bee_families)?>;
	families.push({'family_name': 'All', 'family_desc': 'Bee'});
	const counts = <?=json_encode($log_counts)?>;
	beeSpecies.forEach(spp => { spp['sightings'] = +counts[spp['latin_name']] });

	$('#beeList').append(buildTabsByCategory('family_name', families, beeSpecies));
	$('table').css('width', '80%');
	$('.seen').addClass('bee-color-1');
	$('.seen-genus').addClass('bee-color-2');
</script>
<?php include_once 'footer.html'; ?>
