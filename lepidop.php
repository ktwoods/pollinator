<?php
$cur_page = 'lepidop';
include_once 'header.html';

$stmt = $conn->prepare('SELECT family_name, family_desc FROM Family WHERE subtype="Butterfly"');
$stmt->execute();
$b_families = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $conn->prepare ('SELECT img_url, latin_name, common_name, family_name FROM Lep_full WHERE subtype="Butterfly" ORDER BY family_name, latin_name');
$stmt->execute();
$b_species = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare ('SELECT family_name, family_desc FROM Family WHERE subtype="Moth"');
$stmt->execute();
$m_families = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $conn->prepare ('SELECT img_url, latin_name, common_name, family_name FROM Lep_full WHERE subtype="Moth" ORDER BY family_name, latin_name');
$stmt->execute();
$m_species = $stmt->fetchAll(PDO::FETCH_ASSOC);

$log_counts;
foreach ($b_species as $sp) {
	$stmt = $conn->prepare('SELECT COUNT(DISTINCT date) AS sightings FROM Log WHERE latin_name="' . $sp['latin_name'] . '"');
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$log_counts[$sp['latin_name']] = $result['sightings'];
}
foreach ($m_species as $sp) {
	$stmt = $conn->prepare('SELECT COUNT(DISTINCT date) AS sightings FROM Log WHERE latin_name="' . $sp['latin_name'] . '"');
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$log_counts[$sp['latin_name']] = $result['sightings'];
}
?>
<!-- "Add species" button -->
<a href="new.php?type=lepidop" class="btn btn-l btn-new"><i class="fas fa-plus"></i> Add species</a>

<!-- Main container -->
<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8" id="butterflyList">
			<h1>Butterflies</h1>
		</div>
	</div>
	<p>&nbsp;</p>
	<div class="row justify-content-center">
		<div class="col col-lg-8" id="mothList">
			<h1>Moths</h1>
		</div>
	</div>
</div>
<p>&nbsp;</p>

<script>
	const butterflySpecies = <?=json_encode($b_species)?>;
	const butterflyFamilies = <?=json_encode($b_families)?>;
	butterflyFamilies.push({'family_name': 'All', 'family_desc': 'Butterfly'});
	const mothSpecies = <?=json_encode($m_species)?>;
	const mothFamilies = <?=json_encode($m_families)?>;
	mothFamilies.push({'family_name': 'All', 'family_desc': 'Moth'});
	const counts = <?=json_encode($log_counts)?>;
	const appendCounts = spp => { spp['sightings'] = +counts[spp['latin_name']] };
	butterflySpecies.forEach(appendCounts);
	mothSpecies.forEach(appendCounts);

	$('#butterflyList').append(buildTabsByCategory('family_name', butterflyFamilies, butterflySpecies));
	$('#mothList').append(buildTabsByCategory('family_name', mothFamilies, mothSpecies));
	$('table').css('width', '80%');
	$('.seen').addClass('lep-color-3');
	$('.seen-genus').addClass('lep-color-2');
</script>
<?php include_once 'footer.html'; ?>
