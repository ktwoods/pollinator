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

	$changed = ($stmt->execute() && $stmt->rowCount() != 0);

	if ($template['type'] == 'lepidop') {
		$stmt = $conn->prepare("UPDATE Lepidopteran SET host_prefs=:gen_host, nect_prefs=:gen_nect WHERE latin_name=:name");
		$stmt->bindValue(':name', $name);
		$stmt->bindValue(':gen_host', $gen_host);
		$stmt->bindValue(':gen_nect', $gen_nect);
		if ($stmt->execute() && $stmt->rowCount() != 0) $changed = true;
	}
	else if ($template['type'] == 'bee') {
		$stmt = $conn->prepare("UPDATE Bee SET specialization=:spec WHERE latin_name=:name");
		$stmt->bindValue(':name', $name);
		$stmt->bindValue(':spec', $spec);
		if ($stmt->execute() && $stmt->rowCount() != 0) $changed = true;
	}

	success_fail_message($changed, 'Species record updated!');
}
?>
