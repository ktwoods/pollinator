<?php
// If page edits have just been submitted, update the page
if (isset($_POST['latin'])) {
	$latin = $_POST['latin'];
	$common = $_POST['common'];
	$fam = $_POST['fam'];
	if (isset($_POST['gen_host'])) $gen_host = $_POST['gen_host'];
	if (isset($_POST['gen_nect'])) $gen_nect = $_POST['gen_nect'];
	if (isset($_POST['spec'])) $spec = $_POST['spec'];
	$id = $_POST['id'];
	$notes = $_POST['notes'];
	$img = $_POST['img'];

	$stmt = $conn->prepare("UPDATE Creature SET latin_name=:latin, common_name=:common, family_name=:fam, identification=:id, notes=:notes, img_url=:img WHERE latin_name=:name");
	$stmt->bindValue(':name', $name);
	$stmt->bindValue(':latin', $latin);
	$stmt->bindValue(':common', $common);
	$stmt->bindValue(':fam', $fam);
	$stmt->bindValue(':id', $id);
	$stmt->bindValue(':notes', $notes);
	$stmt->bindValue(':img', $img);

	$changed = false;
	if ($stmt->execute())
	{
		if ($stmt->rowCount() != 0) $changed = true;
	}

	if ($template['type'] == 'lepidop') {
		$stmt = $conn->prepare("UPDATE Lepidopteran SET host_prefs=:gen_host, nect_prefs=:gen_nect WHERE latin_name=:name");
		$stmt->bindValue(':name', $name);
		$stmt->bindValue(':gen_host', $gen_host);
		$stmt->bindValue(':gen_nect', $gen_nect);
		if ($stmt->execute())
		{
			if ($stmt->rowCount() != 0) $changed = true;
		}
	}
	else if ($template['type'] == 'bee') {
		$stmt = $conn->prepare("UPDATE Bee SET specialization=:spec WHERE latin_name=:name");
		$stmt->bindValue(':name', $name);
		$stmt->bindValue(':spec', $spec);
		if ($stmt->execute())
		{
			if ($stmt->rowCount() != 0) $changed = true;
		}
	}

  // Display message — either "Species record updated" or "No changes made" if edit failed
	echo '<div class="alert alert-success alert-dismissible text-center" role="alert">';
	if ($changed) echo "Species record updated!";
	else echo "No changes made.";
	echo '<button type="button" class="close" data-dismiss="alert" aria-label="close"><span aria-hidden="true">&times;</span></button></div>';
}
?>
