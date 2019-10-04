<?php
include_once 'connect.php';

if (isset($_POST['type'])) $spp_type = $_POST['type'];
else $spp_type = $_GET['type'];

if ($spp_type == 'lepidop') {
	$spp_type_full = 'butterfly/moth';
	$spp_class = 'l';
}
else if ($spp_type == 'bee') {
	$spp_type_full = 'bee';
	$spp_class = 'b';
}
else {
	$spp_type_full = 'creature';
	$spp_class = 'o';
}

$cur_page = $spp_type;
include 'header.html';
include_once 'funcs_general.php';

if (isset($_POST['latin'])) {
	$latin = $_POST['latin'];
	$common = $_POST['common'];
	$fam = $_POST['fam'];
	$id = $_POST['id'];
	$notes = $_POST['notes'];
	$img = $_POST['img'];
	if (isset($_POST['gen_host'])) $gen_host = $_POST['gen_host'];
	if (isset($_POST['gen_nect'])) $gen_nect = $_POST['gen_nect'];
	if (isset($_POST['spec'])) $spec = $_POST['spec'];

	$stmt = $conn->prepare("INSERT INTO Creature (latin_name, common_name, family_name, identification, notes, img_url) VALUES (:latin, :common, :fam, :id, :notes, :img)");
	$stmt->bindParam(':latin', $latin);
	$stmt->bindParam(':common', $common);
	$stmt->bindParam(':fam', $fam);
	$stmt->bindParam(':id', $id);
	$stmt->bindParam(':notes', $notes);
	$stmt->bindParam(':img', $img);

	if ($stmt->execute()) {
		echo '<h1 class="text-center">New '.$spp_type_full.' species</h1><p>&nbsp;</p>';
		if ($stmt->rowCount() != 0) {
			$url = str_replace(' ', '%20', $latin);
			echo "<p class='text-center'>Species <em>$latin</em> ($common) was added! <a href='view.php?spp=$url'>[View species profile]</a></p>";
		}
		else echo "<p class='text-center'>Error: failed to add species <em>$latin</em> ($common) to the database.</p>";

		$full_success = true;
		if ($spp_type == 'lepidop') {
			$stmt = $conn->prepare("INSERT INTO Lepidopteran (latin_name, host_prefs, nect_prefs) VALUES (:latin, :gen_host, :gen_nect)");
			$stmt->bindValue(':latin', $latin);
			$stmt->bindValue(':gen_host', $gen_host);
			$stmt->bindValue(':gen_nect', $gen_nect);
			if ($stmt->execute())
			{
				if ($stmt->rowCount() == 0) $full_success = false;
			}
		}
		else if ($spp_type == 'bee') {
			$stmt = $conn->prepare("INSERT INTO Bee (latin_name, specialization) VALUES (:latin, :spec)");
			$stmt->bindValue(':latin', $latin);
			$stmt->bindValue(':spec', $spec);
			if ($stmt->execute())
			{
				if ($stmt->rowCount() == 0) $full_success = false;
			}
		}
		if (!$full_success) echo '<p class="text-center">Warning: some fields may not have been successfully initialized; check species profile for more details.</p>';
	}
}

else { ?>
<div class="container-fluid">
	<h1 class="text-center">New <?php echo $spp_type_full ?> species</h1>
	<form action="new.php?type=<?php echo $spp_type ?>" method="post">
		<div class="row justify-content-center">
			<div class="col-sm-6 sec-<?php echo $spp_class ?>">
				<div>&nbsp;</div>
				<div class="form-group">
					<label for="latin">Latin name</label>
					<input type="text" class="form-control" id="latin" name="latin">
				</div>

				<div class="form-group">
					<label for="common">Common name</label>
					<input type="text" class="form-control" id="common" name="common">
				</div>

				<div class="form-group">
					<label for="fam">Family</label>
					<input type="text" class="form-control" id="fam" name="fam">
				</div>

				<?php if ($spp_type == 'bee') : ?>
					<div class="form-group">
						<label for="spec">Specialization</label>
						<input type="text" class="form-control" id="spec" name="spec" placeholder="What families or genera this species specializes on, if any">
					</div>
				<?php endif ?>
				<?php if ($spp_type == 'lepidop') : ?>
					<div class="form-group">
						<label for="host">General host preferences</label>
						<input type="text" class="form-control" id="host" name="gen_host">
					</div>
					<div class="form-group">
						<label for="nect">General nectar preferences</label>
						<input type="text" class="form-control" id="nect" name="gen_nect">
					</div>
				<?php endif ?>

				<div class="form-group">
					<label for="notes">Notes</label>
					<textarea class="form-control" id="notes" name="notes"></textarea>
				</div>

				<div class="form-group">
					<label for="id">Identification</label>
					<textarea class="form-control" id="id" name="id"></textarea>
				</div>

				<div class="form-group">
					<label for="img">Image URL</label>
					<input type="text" class="form-control" id="img" name="img" placeholder="Link to a photo of this species">
				</div>
				<div>&nbsp;</div>
			</div>
		</div>
		<div>&nbsp;</div>
		<div class="row justify-content-center"><div class="col-1"><button type="Submit" class="btn btn-<?php echo $spp_class ?>">Create</button></div></div>
	</form>
	<div>&nbsp;</div>
</div>
<?php } ?>
<?php include 'footer.html'; ?>
