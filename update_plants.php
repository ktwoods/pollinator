<?php
$cur_page = 'plants';
include_once 'header.html';

$content = 'new';
$submit_successful = false;
$species_data;

// This is a new species being submitted
if (isset($_POST['latin'])) {
	$content = 'submission';
	// If coming back after submitting new species data, attempt to update
	$stmt = $conn->prepare("INSERT INTO Plant (latin_name, family, common_name, have, want, bloom_length, tags, research_notes, observations, img_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$exec = $stmt->execute(array($_POST['latin'], $_POST['fam'], $_POST['common'], $_POST['have'], $_POST['want'], $_POST['blen'], $_POST['tags'], $_POST['notes'], $_POST['obs'], $_POST['img']));

	$submit_successful = $exec && $stmt->rowCount() != 0;
}
// This is an existing species being edited
else if (isset($_GET['name'])) {
	$content = 'edit';
	$stmt = $conn->prepare("SELECT * FROM Plant WHERE latin_name = ?");
	$stmt->execute(array($_GET['name']));
	$species_data = $stmt->fetch();
}
?>

<div class="container-fluid">
	<h1 class="text-center" id="pageTitle">New plant species</h1>
	<div class="text-center" id="submitMessage" style="display: none">
		<p>&nbsp;</p>
	</div>
	<form action="update_plants.php" method="post" id="plantForm">
		<div class="row justify-content-center">
			<div class="col-lg-6 sec-p">
				<div>&nbsp;</div>
				<!-- Basic fields: common name, Latin name, family -->
				<div class="form-group">
					<label for="latin">Latin name</label>
					<input required type="text" class="form-control" id="latinName" name="latin">
				</div>
				<div class="form-group">
					<label for="common">Common name</label>
					<input required type="text" class="form-control" id="commonName" name="common">
				</div>
				<div class="form-group">
					<label for="fam">Family</label>
					<input required type="text" class="form-control" id="family" name="fam">
				</div>
				<!-- Radio toggle for haves/wants -->
				<div style="margin-bottom: 10px">
					<div class="form-check checkbox-inline">
						<input required class="form-check-input" type="radio" name="have" value="1" id="have"><label class="form-check-label" for="have" style="margin-left: 10px">have</label>
					</div>
					<div required class="form-check checkbox-inline">
						<input class="form-check-input" type="radio" name="have" value="0" id="dhave"><label class="form-check-label" for="dhave" style="margin-left: 10px">don't have</label>
					</div>
					<div class="form-check checkbox-inline">
						<input required class="form-check-input" type="radio" name="want" value="1" id="want"><label class="form-check-label" for="want" style="margin-left: 10px">want</label>
					</div>
					<div class="form-check checkbox-inline">
						<input required class="form-check-input" type="radio" name="want" value="0" id="dwant"><label class="form-check-label" for="dwant" style="margin-left: 10px">don't want</label>
					</div>
				</div>
				<!-- Tags, approximate length of bloom period -->
				<div class="form-group">
					<label for="fam">Tags</label>
					<input type="text" class="form-control" id="tags" name="tags" placeholder="shade, ephemeral, rabbit-proof, etc">
				</div>
				<div class="form-group">
					<label for="blen">Bloom length</label>
					<input type="text" class="form-control" id="blen" name="blen" placeholder="1 week, 3 days, 2+ months, etc">
				</div>
				<!-- Characteristics from research, notes from experience -->
				<div class="form-group">
					<label for="notes">Characteristics</label>
					<textarea class="form-control" id="notes" name="notes" placeholder="Sun/water/soil needs, size, other reported characteristics"></textarea>
				</div>
				<div class="form-group">
					<label for="obs">Notes</label>
					<textarea class="form-control" id="obs" name="obs" placeholder="Cultivar names, observations, and personal notes"></textarea>
				</div>
				<!-- Image url -->
				<div class="form-group">
					<label for="img">Image URL</label>
					<input type="text" class="form-control" id="img" name="img" placeholder="Link to a photo of this species">
				</div>
				<div>&nbsp;</div>
			</div>
		</div>
		<div>&nbsp;</div>
		<div class="row justify-content-center">
			<div class="col-1">
				<button type="Submit" class="btn btn-p">Create</button>
			</div>
		</div>
	</form>
	<div>&nbsp;</div>
</div>
<script>
	const form = document.getElementById('plantForm');
	const messageDiv = document.getElementById('submitMessage');

	const contentType = <?=json_encode($content)?>;
	const submitSuccessful = <?=json_encode($submit_successful)?>;
	const latinName = <?=json_encode($_POST['latin'])?>;
	const commonName = <?=json_encode($_POST['common'])?>;
	const speciesData = <?=json_encode($species_data)?>;

	if (contentType === 'submission') {
		form.style.display = 'none';
		messageDiv.style.display = 'block';

		const message = document.createElement('div');
		messageDiv.append(message);

		if (submitSuccessful) message.innerHTML = `Species <i>${latinName}</i> (${commonName}) was added! <a href="view_plant.php?spp=${latinName}">[View species profile]</a>`;
		else message.innerHTML = `Error: unable to add species <i>${latinName}</i> (${commonName}) to the database.`;
	}
	else if (contentType === 'edit') {
		const title = document.getElementById('pageTitle');
		title.innerHTML = 'Edit plant species: <i>' + speciesData['latin_name'] + '</i>';


		form.action = 'view_plant.php?spp=' + speciesData['latin_name'];

		// populate fields
		form.elements.latinName.value = speciesData['latin_name'];
		form.elements.commonName.value = speciesData['common_name'];
		form.elements.family.value = speciesData['family'];
		if (+speciesData['have']) document.getElementById('have').toggleAttribute('checked');
		else document.getElementById('dhave').toggleAttribute('checked');
		if (+speciesData['want']) document.getElementById('want').toggleAttribute('checked');
		else document.getElementById('dwant').toggleAttribute('checked');
		form.elements.tags.value = speciesData['tags'];
		form.elements.blen.value = speciesData['bloom_length'];
		form.elements.notes.value = speciesData['research_notes'];
		form.elements.obs.value = speciesData['observations'];
		form.elements.img.value = speciesData['img_url'];
	}
</script>
<?php include_once 'footer.html'; ?>
