<?php
$cur_page = 'plants';
include 'header.php';
include_once 'connect.php';
include_once 'build_table.php';

global $conn;
if (isset($_POST['name'])) $name = $_POST['name'];
else $name = $_GET['name'];

$stmt = $conn->prepare("Select * from Plant where latin_name = ?");
$stmt->bindValue(1, $name);
$stmt->execute();
$main_data = $stmt->fetch();
$name = $main_data['latin_name'];
?>
<div class="container-fluid">
	<h1 class="text-center">Edit plant profile</h1>
	<form action="view_plant.php?name=<?php echo $name ?>" method="post">
		<div class="row justify-content-center">
			<!-- Basic fields -->
			<div class="col-lg-6 sec-p">
				<div>&nbsp;</div>
				<div class="form-group">
					<label for="common">Common name</label>
					<input type="text" class="form-control" id="common" name="common" value="<?php echo $main_data['common_name'] ?>">
				</div>
				<div class="form-group">
					<label for="latin">Latin name</label>
					<input type="text" class="form-control" id="latin" name="latin" value="<?php echo $main_data['latin_name'] ?>">
				</div>
				<div class="form-group">
					<label for="fam">Family</label>
					<input type="text" class="form-control" id="fam" name="fam" value="<?php echo $main_data['family'] ?>">
				</div>
				<div class="form-group">
					<label for="blen">Bloom length</label>
					<input type="text" class="form-control" id="blen" name="blen" value="<?php echo $main_data['bloom_length'] ?>">
				</div>
				<div class="form-group">
					<label for="tags">Tags</label>
					<input type="text" class="form-control" id="tags" name="tags" value="<?php echo $main_data['tags'] ?>">
				</div>
				<div class="form-group">
					<label for="notes">Characteristics</label>
					<textarea type="text" class="form-control" id="notes" name="notes" rows=4><?php echo $main_data['research_notes'] ?></textarea>
				</div>
				<div class="form-group">
					<label for="obs">Notes</label>
					<textarea type="text" class="form-control" id="obs" name="obs" rows=3><?php echo $main_data['observations'] ?></textarea>
				</div>
				<div class="form-group">
					<label for="img">Image URL</label>
					<input type="text" class="form-control" id="img" name="img" value="<?php echo $main_data['img_url'] ?>">
				</div>
			</div>
			<!-- Haves/wants -->
			<div class="col-lg-2 sec-p">
				<div>&nbsp;</div><div>&nbsp;</div>
				<div><div class="form-check checkbox-inline">
					<input class="form-check-input" type="radio" name="have" value="1" id="have" <?php if ($main_data['have']) echo 'checked'; ?>><label class="form-check-label" for="have" style="margin-left: 10px">have</label>	
				</div>
				<div class="form-check checkbox-inline">
					<input class="form-check-input" type="radio" name="have" value="0" id="dhave" <?php if (!$main_data['have']) echo 'checked'; ?>><label class="form-check-label" for="dhave" style="margin-left: 10px">don't have</label>	
				</div>&nbsp;</div>
				<div class="form-check checkbox-inline">
					<input class="form-check-input" type="radio" name="want" value="1" id="want" <?php if ($main_data['want']) echo 'checked'; ?>><label class="form-check-label" for="want" style="margin-left: 10px">want</label>	
				</div>
				<div class="form-check checkbox-inline">
					<input class="form-check-input" type="radio" name="want" value="0" id="dwant" <?php if (!$main_data['want']) echo 'checked'; ?>><label class="form-check-label" for="dwant" style="margin-left: 10px">don't want</label>	
				</div>
				<div>&nbsp;</div>
			</div>
		</div>
		<div>&nbsp;</div>
		<div class="row justify-content-center"><div class="col-1"><button type="Submit" class="btn btn-p" >Save</button></div></div>
	</form>
	<div>&nbsp;</div>
</div>
<?php include 'footer.html'; ?>