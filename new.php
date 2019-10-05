<?php
$cur_page = $_GET['type'];
include_once 'funcs_general.php';
include_once 'header.html';

$spp_type = $cur_page;
$spp_type_full = 'creature';
$spp_class = 'o';

if ($spp_type == 'lepidop') {
	$spp_type_full = 'butterfly/moth';
	$spp_class = 'l';
}
else if ($spp_type == 'bee') {
	$spp_type_full = 'bee';
	$spp_class = 'b';
}
?>

<div class="container-fluid">
	<h1 class="text-center">New <?php echo $spp_type_full ?> species</h1>
	<?php
	// If coming back after submitting new species data, attempt to update and print message
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

		$query = "START TRANSACTION; INSERT INTO Creature (latin_name, common_name, family_name, identification, notes, img_url) VALUES (:latin, :common, :fam, :id, :notes, :img);";

		if ($spp_type == 'lepidop') {
			$query .= " INSERT INTO Lepidopteran (latin_name, host_prefs, nect_prefs) VALUES (:latin, :gen_host, :gen_nect);";
		}
		else if ($spp_type == 'bee') {
			$query .= " INSERT INTO Bee (latin_name, specialization) VALUES (:latin, :spec);";
		}
		$query .= " COMMIT;";
		$stmt = $conn->prepare($query);
		$stmt->bindParam(':latin', $latin);
		$stmt->bindParam(':common', $common);
		$stmt->bindParam(':fam', $fam);
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':notes', $notes);
		$stmt->bindParam(':img', $img);
		
		if ($spp_type == 'lepidop') {
			$stmt->bindValue(':latin', $latin);
			$stmt->bindValue(':gen_host', $gen_host);
			$stmt->bindValue(':gen_nect', $gen_nect);
		}
		else if ($spp_type == 'bee') {
			$stmt->bindValue(':latin', $latin);
			$stmt->bindValue(':spec', $spec);
		}

		echo '<p>&nbsp;</p><p class="text-center">';
		if (!$stmt->execute())
		{
			echo "Error: failed to add species <em>$latin</em> ($common) to the database.</p>";
		}
		else echo "Species <em>$latin</em> ($common) was added! <a href='view.php?spp=$latin'>[View species profile]</a></p>";
	}
	// Otherwise, show the form for creating a new species
	else { ?>
	<form action="new.php?type=<?php echo $spp_type ?>" method="post">
		<div class="row justify-content-center">
			<div class="col-sm-6 sec-<?php echo $spp_class ?>">
				<div>&nbsp;</div>
				<!-- Basic fields: common name, Latin name, family -->
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
				<!-- Optional field: bee specialization -->
				<?php if ($spp_type == 'bee') : ?>
					<div class="form-group">
						<label for="spec">Specialization</label>
						<input type="text" class="form-control" id="spec" name="spec" placeholder="What families or genera this species specializes on, if any">
					</div>
				<?php endif ?>
				<!-- Optional fields: butterfly/moth host and nectar preferences -->
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
		<div class="row justify-content-center"><div class="col-1"><button type="Submit" class="btn btn-<?php echo $spp_class ?>">Create</button></div></div>
	</form>
	<div>&nbsp;</div>
</div>
<?php } ?>
<?php include_once 'footer.html'; ?>
