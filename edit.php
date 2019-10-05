<?php
include_once 'funcs_general.php';

$name = $_GET['spp'];
// Determine what kind of species template is needed for this page (lepid, bee, or the general template)
$template = template_vals(get_type($name));
$cur_page = $template['type'];

include_once 'header.html';

// Get current species attributes to populate edit form
$stmt = $conn->prepare("SELECT * FROM {$template['table']} WHERE latin_name = ?");
$stmt->execute(array($name));
$main_data = $stmt->fetch();
?>
<div class="container-fluid">
	<h1 class="text-center">Edit species profile</h1>
	<form action="view.php?spp=<?php echo $name ?>" method="post">
		<div class="row justify-content-center">
			<div class="col-med-8 col-lg-6 sec-<?php echo $template['class'] ?>">
				<!-- Basic fields: name, Latin name, family -->
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
				<!-- Optional field: bee specialization -->
				<?php if ($template['type'] == 'bee') : ?>
					<div class="form-group">
						<label for="spec">Specialization</label>
						<input type="text" class="form-control" id="spec" name="spec" value="<?php echo $main_data['specialization'] ?>">
					</div>
				<?php endif ?>
				<!-- Optional fields: butterfly/moth host and nectar preferences -->
				<?php if ($template['type'] == 'lepidop') : ?>
					<div class="form-group">
						<label for="host">General host preferences</label>
						<input type="text" class="form-control" id="host" name="gen_host" value="<?php echo $main_data['host_prefs'] ?>">
					</div>
					<div class="form-group">
						<label for="nect">General nectar preferences</label>
						<input type="text" class="form-control" id="nect" name="gen_nect" value="<?php echo $main_data['nect_prefs'] ?>">
					</div>
				<?php endif ?>
				<!-- General and identification notes -->
				<div class="form-group">
					<label for="notes">Notes</label>
					<textarea type="text" class="form-control" id="notes" name="notes" rows="5"><?php echo $main_data['notes'] ?></textarea>
				</div>
				<div class="form-group">
					<label for="id">Identification</label>
					<textarea type="text" class="form-control" id="id" name="id" rows="5"><?php echo $main_data['identification'] ?></textarea>
				</div>
				<!-- Image url -->
				<div class="form-group">
					<label for="img">Image URL</label>
					<input type="text" class="form-control" id="img" name="img" value="<?php echo $main_data['img_url'] ?>">
				</div>
			</div>
		</div>
		<div>&nbsp;</div>
		<div class="row justify-content-center"><div class="col-1"><button type="Submit" class="btn btn-<?php echo $template['class'] ?>">Save</button></div></div>
	</form>
	<div>&nbsp;</div>
</div>
<?php include_once 'footer.html'; ?>
