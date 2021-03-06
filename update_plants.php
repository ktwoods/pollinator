<?php
$cur_page = 'plants';
include_once 'header.html';

$action = 'new';
$submit_successful = false;
$species_data;

// This is a new species being submitted
if (isset($_POST['latin'])) {
	$action = 'submit';
	// If coming back after submitting new species data, attempt to update
	$stmt = $conn->prepare("INSERT INTO Plant (latin_name, family, common_name, have, want, bloom_length, tags, research_notes, observations, img_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$exec = $stmt->execute(array(trim($_POST['latin']), trim($_POST['fam']), trim($_POST['common']), $_POST['have'], $_POST['want'], trim($_POST['blen']), trim($_POST['tags']), trim($_POST['notes']), trim($_POST['obs']), trim($_POST['img'])));

	$submit_successful = $exec && $stmt->rowCount() != 0;
}
// This is an existing species being edited
else if (isset($_GET['name'])) {
	$action = 'edit';
	$stmt = $conn->prepare("SELECT * FROM Plant WHERE latin_name = ?");
	$stmt->execute(array($_GET['name']));
	$species_data = $stmt->fetch();
}
?>

<div class="container-fluid">
	<h1>New plant species</h1>
	<div class="text-center" id="submitMessage" hidden>
		<p>&nbsp;</p>
	</div>
	<form action="update_plants.php" method="post" id="plantForm">
		<div class="row justify-content-center">
			<div class="col-lg-6 plant-color-2">
				<div>&nbsp;</div>
				<!-- Basic fields: common name, Latin name, family -->
				<div class="form-group">
					<label for="latin">Latin name (*)</label>
					<input required type="text" class="form-control" id="latinName" name="latin">
				</div>
				<div class="form-group">
					<label for="common">Common name (*)</label>
					<input required type="text" class="form-control" id="commonName" name="common">
				</div>
				<div class="form-group">
					<label for="fam">Family (*)</label>
					<input required type="text" class="form-control" id="family" name="fam">
				</div>
				<!-- Radio toggle for haves/wants -->
				<div style="margin-bottom: 10px">
					<div class="form-check checkbox-inline">
						<input required class="form-check-input" type="radio" name="have" value="1" id="have"><label class="form-check-label" for="have">have</label>
					</div>
					<div required class="form-check checkbox-inline">
						<input class="form-check-input" type="radio" name="have" value="0" id="dhave"><label class="form-check-label" for="dhave">don't have</label>
					</div>
					<div class="form-check checkbox-inline">
						<input required class="form-check-input" type="radio" name="want" value="1" id="want"><label class="form-check-label" for="want">want</label>
					</div>
					<div class="form-check checkbox-inline">
						<input required class="form-check-input" type="radio" name="want" value="0" id="dwant"><label class="form-check-label" for="dwant">don't want</label>
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

	const action = <?=json_encode($action)?>;
	const submitSuccessful = <?=json_encode($submit_successful)?>;
	const latinName = <?=isset($_POST['latin']) ? json_encode($_POST['latin']) : json_encode($species_data['latin_name'])?>;
	const commonName = <?=isset($_POST['common']) ? json_encode($_POST['common']) : json_encode($species_data['common_name'])?>;
	const speciesData = <?=isset($species_data) ? json_encode($species_data) : 'null'?>;

	if (action === 'submit') {
		form.setAttribute('hidden', '');
		let messageDiv = $('#submitMessage');
		messageDiv.removeAttr('hidden');

		if (submitSuccessful) messageDiv.append(`Species <i>${latinName}</i> (${commonName}) was added! <a href="view_plant.php?sp=${latinName}">[View species profile]</a>`);
		else messageDiv.append(`Error: unable to add species <i>${latinName}</i> (${commonName}) to the database.`);
	}
	else if (action === 'edit') {
		$('h1').first().html('Edit plant species: <i>' + speciesData['latin_name'] + '</i>');
		$('.btn').text('Update');

		form.action = 'view_plant.php?sp=' + speciesData['latin_name'];
		// populate fields
		form.elements.latinName.value = speciesData['latin_name'];
		form.elements.commonName.value = speciesData['common_name'];
		form.elements.family.value = speciesData['family'];
		if (+speciesData['have']) $('#have').attr('checked', '');
		else $('#dhave').attr('checked', '');
		if (+speciesData['want']) $('#want').attr('checked', '');
		else $('#dwant').attr('checked', '');
		form.elements.tags.value = speciesData['tags'];
		form.elements.blen.value = speciesData['bloom_length'];
		form.elements.notes.value = speciesData['research_notes'];
		form.elements.obs.value = speciesData['observations'];
		form.elements.img.value = speciesData['img_url'];
	}
</script>
<?php include_once 'footer.html'; ?>
