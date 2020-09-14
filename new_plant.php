<?php
$cur_page = 'plants';
include_once 'funcs_general.php';
include_once 'header.html';
?>

<div class="container-fluid">
	<h1 class="text-center">New plant species</h1>
	<?php
	// If coming back after submitting new species data, attempt to update and print message
	if (isset($_POST['latin'])) {
		$stmt = $conn->prepare("INSERT INTO Plant (latin_name, family, common_name, have, want, bloom_length, tags, research_notes, observations, img_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

		$exec = $stmt->execute(array($_POST['latin'], $_POST['fam'], $_POST['common'], $_POST['have'], $_POST['want'], $_POST['blen'], $_POST['tags'], $_POST['notes'], $_POST['obs'], $_POST['img']));

		echo '<p>&nbsp;</p><p class="text-center">';

		if ($exec && $stmt->rowCount() != 0)
		{
			echo "Species <em>$latin</em> ($common) was added! <a href='view_plant.php?spp=$latin'>[View species profile]</a>";
		}
		else echo "Error: failed to add species <em>$latin</em> ($common) to the database.";
		echo '</p>';
	}
	// Otherwise, show the form for creating a new species
	else { ?>
	<form action="new_plant.php" method="post">
		<div class="row justify-content-center">
			<div class="col-lg-6 sec-p">
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
				<!-- Radio toggle for haves/wants -->
				<div style="margin-bottom: 10px">
					<div class="form-check checkbox-inline">
						<input class="form-check-input" type="radio" name="have" value="1" id="have"><label class="form-check-label" for="have" style="margin-left: 10px">have</label>
					</div>
					<div class="form-check checkbox-inline">
						<input class="form-check-input" type="radio" name="have" value="0" id="dhave"><label class="form-check-label" for="dhave" style="margin-left: 10px">don't have</label>
					</div>
					<div class="form-check checkbox-inline">
						<input class="form-check-input" type="radio" name="want" value="1" id="want"><label class="form-check-label" for="want" style="margin-left: 10px">want</label>
					</div>
					<div class="form-check checkbox-inline">
						<input class="form-check-input" type="radio" name="want" value="0" id="dwant"><label class="form-check-label" for="dwant" style="margin-left: 10px">don't want</label>
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
		<div class="row justify-content-center"><div class="col-1"><button type="Submit" class="btn btn-p" >Create</button></div></div>
	</form>
	<div>&nbsp;</div>
	<?php } ?>
</div>
<script src="validate.js"></script>
<?php include_once 'footer.html'; ?>
