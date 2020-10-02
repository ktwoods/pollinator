<?php
$cur_page = 'other';
include_once 'header.html';

$stmt = $conn->prepare('SELECT DISTINCT type FROM Creature_full WHERE family_name NOT IN (SELECT family_name FROM Bee_full) AND family_name NOT IN (SELECT family_name FROM Lep_full)');
$stmt->execute();
$types = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $conn->prepare ('SELECT img_url, latin_name, common_name, type, subtype FROM Creature_full WHERE latin_name NOT IN (SELECT latin_name FROM Bee) AND latin_name NOT IN (SELECT latin_name FROM Lepidopteran) ORDER BY type, subtype, latin_name');
$stmt->execute();
$species = $stmt->fetchAll(PDO::FETCH_ASSOC);

$log_counts;
foreach ($species as $sp) {
	$stmt = $conn->prepare('SELECT COUNT(DISTINCT date) AS sightings FROM Log WHERE latin_name="' . $sp['latin_name'] . '"');
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$log_counts[$sp['latin_name']] = $result['sightings'];
}
?>
<!-- "Add species" button -->
<a href="new.php?type=other" class="btn btn-o" style="margin-top: 15px; position: fixed;"><i class="fas fa-plus"></i> Add species</a>

<!-- Main container -->
<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8" id="generalList">
      <h1 class="text-center">Other creatures</h1>
    </div>
  </div>
</div>
<p>&nbsp;</p>

<script>
	const species = <?=json_encode($species)?>;
	const types = <?=json_encode($types)?>;
	types.push({'type': 'All', 'family_desc': 'Creature'});
	const counts = <?=json_encode($log_counts)?>;
	species.forEach(spp => { spp['sightings'] = +counts[spp['latin_name']] });

	$('#generalList').append(buildTabsByCategory('type', types, species));
	$('table').css('width', '80%');
	$('.seen').addClass('misc-color-2');
</script>
<?php include_once 'footer.html'; ?>
