<?php
$cur_page = 'plants';
include 'header.html';
include_once 'connect.php';
include_once 'funcs_general.php';
?>

<div class="container-fluid">
	<?php
	global $conn;

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
		
		echo '<h1 class="text-center">New plant species</h1><p>&nbsp;</p>';
		$stmt = $conn->prepare("Insert into Plant (latin_name, family, common_name, have, want, bloom_length, tags, research_notes, observations, img_url) values (:latin, :fam, :common, :have, :want, :blen, :tags, :notes, :obs, :img)");
		$stmt->bindParam(':latin', $latin);
		$stmt->bindParam(':fam', $fam);
		$stmt->bindParam(':common', $common);
		$stmt->bindParam(':have', $have);
		$stmt->bindParam(':want', $want);
		$stmt->bindParam(':blen', $blen);
		$stmt->bindParam(':tags', $tags);
		$stmt->bindParam(':notes', $notes);
		$stmt->bindParam(':obs', $obs);
		$stmt->bindParam(':img', $img);
		
		if ($stmt->execute())
		{
			$rows_affected = $stmt->rowCount();
			if ($rows_affected != 0)
			{
				$url = str_replace(' ', '%20', $latin);
				echo "<p class='text-center'>Species <em>$latin</em> ($common) was added! <a href='view_plant.php?spp=$url'>[View species profile]</a></p>";
			}
			else echo "<p class='text-center'>Error: failed to add species <em>$latin</em> ($common) to the database.</p>";
		}
	}
	else { ?>
	
	<h1 class="text-center">New plant species</h1>
	<form action="new_plant.php" method="post">
		<div class="row justify-content-center">
			<div class="col-lg-6 sec-p">
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
			
				<div class="form-group">
					<label for="fam">Tags</label>
					<input type="text" class="form-control" id="tags" name="tags" placeholder="shade, ephemeral, rabbit-proof, etc">
				</div>
				
				<div class="form-group">
					<label for="blen">Bloom length</label>
					<input type="text" class="form-control" id="blen" name="blen" placeholder="1 week, 3 days, 2+ months, etc">
				</div>
				
				<div class="form-group">
					<label for="notes">Characteristics</label>
					<textarea class="form-control" id="notes" name="notes" placeholder="Sun/water/soil needs, size, other reported characteristics"></textarea>
				</div>
				
				<div class="form-group">
					<label for="obs">Notes</label>
					<textarea class="form-control" id="obs" name="obs" placeholder="Cultivar names, observations, and personal notes"></textarea>
				</div>
				
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

<?php include 'footer.html'; ?>