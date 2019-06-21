<?php
include_once 'connect.php';
global $conn;
if (isset($_POST['spp'])) $name = $_POST['spp'];
else $name = $_GET['spp'];

// Determine what kind of species template is needed for this page (lepid, bee, or the general template)
$stmt = $conn->prepare("SELECT order_name, overall_type, latin_name FROM Creature NATURAL JOIN Family WHERE latin_name=?");
$stmt->bindValue(1, $name);
$stmt->execute();
$type = $stmt->fetch(PDO::FETCH_ASSOC);
$name = $type['latin_name']; // ok I don't know if this actually does anything, but it's intended to make sure that if somebody made up a value for name, the page will break without affecting the database

if ($type['order_name'] == 'Lepidoptera') {
	$spp_type = 'lepidop';
	$spp_table = 'Lep_full';
	$spp_class = 'l';
}
else if (stripos($type['overall_type'], 'bee') !== FALSE) {
	$spp_type = 'bee';
	$spp_table = 'Bee_full';
	$spp_class = 'b';
}
else {
	$spp_type = 'other';
	$spp_table = 'Creature_full';
	$spp_class = 'o';
}

$cur_page = $spp_type;
include 'header.php';
include_once 'build_table.php';

if (isset($_POST['spp'])) $name = $_POST['spp'];
else $name = $_GET['spp'];

$stmt = $conn->prepare("Select * from $spp_table where latin_name = ?");
$stmt->bindValue(1, $name);
$stmt->execute();
$main_data = $stmt->fetch();
?>
<div class="container-fluid">
	<h1 class="text-center">Edit species profile</h1>
	<form action="view.php?spp=<?= $name ?>" method="post">
		<div class="row justify-content-center">
			<div class="col-med-8 col-lg-6 sec-<?php echo $spp_class ?>">
				<!-- Basic fields -->
				<div>&nbsp;</div>
				<div class="form-group">
					<label for="latin">Latin name</label>
					<input type="text" class="form-control" id="latin" name="latin" value="<?php echo $main_data['latin_name'] ?>">
				</div>
				<div class="form-group">
					<label for="common">Common name</label>
					<input type="text" class="form-control" id="common" name="common" value="<?php echo $main_data['common_name'] ?>">
				</div>
				<div class="form-group">
					<label for="fam">Family</label>
					<input type="text" class="form-control" id="fam" name="fam" value="<?php echo $main_data['family_name'] ?>">
				</div>
				
				<?php if ($spp_type == 'bee') : ?>
					<div class="form-group">
						<label for="spec">Specialization</label>
						<input type="text" class="form-control" id="spec" name="spec" value="<?php echo $main_data['specialization'] ?>">
					</div>
				<?php endif ?>
				<?php if ($spp_type == 'lepidop') : ?>
					<div class="form-group">
						<label for="host">General host preferences</label>
						<input type="text" class="form-control" id="host" name="gen_host" value="<?php echo $main_data['host_prefs'] ?>">
					</div>
					<div class="form-group">
						<label for="nect">General nectar preferences</label>
						<input type="text" class="form-control" id="nect" name="gen_nect" value="<?php echo $main_data['nect_prefs'] ?>">
					</div>
				<?php endif ?>
				
				<div class="form-group">
					<label for="notes">Notes</label>
					<textarea type="text" class="form-control" id="notes" name="notes" rows="5"><?php echo $main_data['notes'] ?></textarea>
				</div>
				<div class="form-group">
					<label for="id">Identification</label>
					<textarea type="text" class="form-control" id="id" name="id" rows="5"><?php echo $main_data['identification'] ?></textarea>
				</div>
				
				<div class="form-group">
					<label for="img">Image URL</label>
					<input type="text" class="form-control" id="img" name="img" value="<?php echo $main_data['img_url'] ?>">
				</div>
			</div>
		</div>
		<div>&nbsp;</div>
		<div class="row justify-content-center"><div class="col-1"><button type="Submit" class="btn btn-<?php echo $spp_class ?>" >Save</button></div></div>
	</form>
	<div>&nbsp;</div>
</div>
<?php include 'footer.html'; ?>