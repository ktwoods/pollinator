<?php
include_once 'header.html';
include_once 'funcs_general.php';
// Determine what kind of species template is needed for this page (lepid, bee, or the general template)
$template = template_vals(get_type($_GET['sp']));
$cur_page = $template['type'];

$action = 'new';
$submit_successful = false;
// If coming back after submitting new species data, attempt to update
if (isset($_POST['latin'])) {
	$action = 'submit';
	$latin = $_POST['latin'];
	$common = $_POST['common'];

	$query = "START TRANSACTION; INSERT INTO Creature (latin_name, common_name, family_name, identification, notes, img_url) VALUES (:latin, :common, :fam, :id, :notes, :img);";

	if ($spp_type == 'lep') {
		$query .= " INSERT INTO Lepidopteran (latin_name, host_prefs, nect_prefs) VALUES (:latin, :gen_host, :gen_nect);";
	}
	else if ($spp_type == 'bee') {
		$query .= " INSERT INTO Bee (latin_name, specialization) VALUES (:latin, :spec);";
	}
	$query .= " COMMIT;";

	$stmt = $conn->prepare($query);
	$bindVars = array(':latin' => $latin, ':common' => $common, ':fam' => $_POST['fam'], ':id' => $_POST['id'], ':notes' => $_POST['notes'], ':img' => $_POST['img']);

	if ($spp_type == 'lep') {
		$bindVars[':gen_host'] = $_POST['gen_host'];
		$bindVars[':gen_nect'] = $_POST['gen_nect'];
	}
	else if ($spp_type == 'bee') {
		$bindVars[':spec'] = $_POST['spec'];
	}
	if ($stmt->execute($bindVars)) $submit_successful = true;
}
// This is an existing species being edited
else if (isset($_GET['sp'])) {
	$action = 'edit';
	$stmt = $conn->prepare("SELECT * FROM Creature_full WHERE latin_name = ?");
	$stmt->execute(array($_GET['sp']));
	$species_data = $stmt->fetch();
}
?>

<div class="container-fluid">
	<h1 class="text-center">New creature species</h1>
	<div class="text-center" id="submitMessage" hidden>
		<p>&nbsp;</p>
	</div>
	<form action="update_species.php" method="post" id="speciesForm">
		<div class="row justify-content-center">
			<div id="formBox" class="col-sm-6">
				<div>&nbsp;</div>
				<!-- Basic fields: common name, Latin name, family -->
				<div class="form-group">
					<label for="latin">Latin name</label>
					<input required type="text" class="form-control" id="latin" name="latin">
				</div>
				<div class="form-group">
					<label for="common">Common name</label>
					<input required type="text" class="form-control" id="common" name="common">
				</div>
				<div class="form-group">
					<label for="fam">Family</label>
					<input required type="text" class="form-control" id="fam" name="fam">
				</div>
				<!-- Optional field: bee specialization -->
				<div id="beeFields" hidden>
					<div class="form-group">
						<label for="spec">Specialization</label>
						<input type="text" class="form-control" id="spec" name="spec" placeholder="What families or genera this species specializes on, if any">
					</div>
				</div>
				<!-- Optional fields: butterfly/moth host and nectar preferences -->
				<div id="lepFields" hidden>
					<div class="form-group">
						<label for="host">General host preferences</label>
						<input type="text" class="form-control" id="host" name="gen_host">
					</div>
					<div class="form-group">
						<label for="nect">General nectar preferences</label>
						<input type="text" class="form-control" id="nect" name="gen_nect">
					</div>
				</div>
				<!-- General and identification notes -->
				<div class="form-group">
					<label for="notes">Notes</label>
					<textarea class="form-control" id="notes" name="notes"></textarea>
				</div>
				<div class="form-group">
					<label for="id">Identification</label>
					<textarea class="form-control" id="id" name="id"></textarea>
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
		<div class="row justify-content-center"><div class="col-1"><button type="Submit" class="btn">Create</button></div></div>
	</form>
	<div>&nbsp;</div>
</div>

<script>
	const form = document.getElementById('speciesForm');

	const creatureType = <?=json_encode($template['type'])?>;
	const action = <?=json_encode($action)?>;
	const submitSuccessful = <?=json_encode($submit_successful)?>;
	const latinName = <?=json_encode($_POST['latin'])?>;
	const commonName = <?=json_encode($_POST['common'])?>;
	const speciesData = <?=json_encode($species_data)?>;

	form.action = 'update_species.php?type=' + creatureType;

	if (action === 'submit') {
		form.setAttribute('hidden', '');
		let messageDiv = $('#submitMessage');
		messageDiv.removeAttr('hidden');

		if (submitSuccessful) messageDiv.append(`Species <i>${latinName}</i> (${commonName}) was added! <a href="view.php?sp=${latinName}">[View species profile]</a>`);
		else messageDiv.append(`Error: unable to add species <i>${latinName}</i> (${commonName}) to the database.`);
	}
	else if (action === 'edit') {
		$('h1').first().html('Edit species: <i>' + speciesData['latin_name'] + '</i>');
		$('.btn[type=Submit]').text('Update');

		if (creatureType === 'lep') {
			form.elements.host = speciesData['host_prefs'] || '';
			form.elements.nect = speciesData['nect_prefs'] || '';
		}
		else if (creatureType === 'bee') {
			form.elements.specialization = speciesData['specialization'];
		}

		form.action = 'view.php?sp=' + speciesData['latin_name'];
		// populate fields
		form.elements.latin.value = speciesData['latin_name'];
		form.elements.common.value = speciesData['common_name'];
		form.elements.fam.value = speciesData['family_name'];
		form.elements.id.value = speciesData['identification'] || '';
		form.elements.notes.value = speciesData['notes'] || '';
		form.elements.img.value = speciesData['img_url'] || '';
	}
	else {
		if (creatureType === 'lep') {
			$('h1').text('New butterfly or moth species');
		}
		else if (creatureType === 'bee') {
			$('h1').text('New bee species');
		}
	}

	$('#formBox').addClass(creatureType + '-color-2');
	$('.btn').addClass('btn-' + creatureType[0]);
</script>
<?php include_once 'footer.html'; ?>
