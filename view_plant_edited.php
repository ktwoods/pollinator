<?php
// If page edits have just been submitted, update the page
if (isset($_POST['latin'])) {
	$latin = $_POST['latin'];
	$common = $_POST['common'];
	$fam = $_POST['fam'];
	$have = $_POST['have'];
	$want = $_POST['want'];
	$tags = $_POST['tags'];
	$blen = $_POST['blen'];
	$notes = $_POST['notes'];
	$obs = $_POST['obs'];
	$img = $_POST['img'];

	$stmt = $conn->prepare("UPDATE Plant SET latin_name=:latin, common_name=:common, family=:fam, have=:have, want=:want, bloom_length=:blen, tags=:tags, research_notes=:notes, observations=:obs, img_url=:img WHERE latin_name=:name");
	$stmt->bindValue(':name', $name);
	$stmt->bindValue(':latin', $latin);
	$stmt->bindValue(':common', $common);
	$stmt->bindValue(':fam', $fam);
	$stmt->bindValue(':have', $have);
	$stmt->bindValue(':want', $want);
	$stmt->bindValue(':blen', $blen);
	$stmt->bindValue(':tags', $tags);
	$stmt->bindValue(':notes', $notes);
	$stmt->bindValue(':obs', $obs);
	$stmt->bindValue(':img', $img);

	$changed = false;
	if ($stmt->execute()) {
		if ($stmt->rowCount() != 0) $changed = true;
	}

	echo '<div class="alert alert-success alert-dismissible text-center" role="alert">';
	if ($changed) echo "Species record updated!";
	else echo "No changes made.";
	echo '<button type="button" class="close" data-dismiss="alert" aria-label="close"><span aria-hidden="true">&times;</span></button></div>';
}
?>
