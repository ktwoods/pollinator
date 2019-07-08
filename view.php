<?php
include_once 'connect.php';
include_once 'build_table.php';
global $conn;
if (isset($_POST['spp'])) $name = $_POST['spp'];
else $name = $_GET['spp'];

// Determine what kind of species template is needed for this page (lepid, bee, or the general template)
$type = get_type($name);

if ($type == 'Lepidopteran') {
	$spp_type = 'lepidop'; // Tells the header which part of the menu we're in
	$spp_table = 'Lep_full'; // For the query
	$spp_class = 'l'; // For styling
}
else if ($type == 'Bee') {
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

	if ($spp_type == 'lepidop') {
		$stmt = $conn->prepare("UPDATE Lepidopteran SET host_prefs=:gen_host, nect_prefs=:gen_nect WHERE latin_name=:name");
		$stmt->bindValue(':name', $name);
		$stmt->bindValue(':gen_host', $gen_host);
		$stmt->bindValue(':gen_nect', $gen_nect);
		if ($stmt->execute())
		{
			if ($stmt->rowCount() != 0) $changed = true;
		}
	}
	else if ($spp_type == 'bee') {
		$stmt = $conn->prepare("UPDATE Bee SET specialization=:spec WHERE latin_name=:name");
		$stmt->bindValue(':name', $name);
		$stmt->bindValue(':spec', $spec);
		if ($stmt->execute())
		{
			if ($stmt->rowCount() != 0) $changed = true;
		}
	}

	echo '<div class="alert alert-success alert-dismissible text-center" role="alert">';
	if ($changed) echo "Species record updated!";
	else echo "No changes made.";
	echo '<button type="button" class="close" data-dismiss="alert" aria-label="close"><span aria-hidden="true">&times;</span></button></div>';
}

// $main_data = attributes for this species
$stmt = $conn->prepare("SELECT * FROM ".$spp_table." WHERE latin_name = ?");
$stmt->bindValue(1, $name);
$stmt->execute();
$main_data = $stmt->fetch();

// $logs = all logged sightings of this species
$stmt = $conn->prepare("SELECT date, stage, notes FROM Log WHERE latin_name = ? ORDER BY date DESC");
$stmt->bindValue(1, $name);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $larval_food_spp and $adult_food_spp = all plants that this species should interact or has interacted with as larva and adult respectively
$stmt = $conn->prepare("SELECT latin_name, common_name, have, Feeds.notes FROM Plant JOIN Feeds ON (latin_name=plant_name) WHERE creature_name=? AND stage='larva' ORDER BY latin_name ASC");
$stmt->bindValue(1, $name);
$stmt->execute();
$larval_food_spp = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $conn->prepare("SELECT latin_name, common_name, have, bloom_length, Feeds.notes FROM (Plant NATURAL JOIN Blooms NATURAL JOIN Month) JOIN Feeds ON (latin_name=plant_name) WHERE creature_name=? AND stage='adult' GROUP BY latin_name ORDER BY MIN(month_num) ASC, latin_name ASC");
$stmt->bindValue(1, $name);
$stmt->execute();
$adult_food_spp = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $larval_food_logs and $adult_food_logs store all logs sorted by plant species
$larval_food_logs;
foreach ($larval_food_spp as $plant) {
	// $rel_logs stores the subset of logs that are associated with that plant
	foreach($logs as $l) {
		if (strpos($l['notes'], $plant['latin_name']) !== FALSE) {
			$rel_logs[] = $l;
		}
	}
	if (isset($rel_logs)) $larval_food_logs[$plant['latin_name']] = $rel_logs;
	unset($l, $rel_logs);
}
unset($plant);
$adult_food_logs;
foreach ($adult_food_spp as $plant) {
	// $rel_logs stores the subset of logs that are associated with that plant
	$rel_logs;
	foreach ($logs as $l) {
		if (strpos($l['notes'], $plant['latin_name']) !== FALSE) {
			$rel_logs[] = $l;
		}
	}
	if (isset($rel_logs)) $adult_food_logs[$plant['latin_name']] = $rel_logs;
	unset($l, $rel_logs);
}
unset($plant);
?>

<div class="container-fluid">
	<!-- Basic profile -->
	<div class="row">
		<a href="edit.php?spp=<?php echo $name ?>" class="btn btn-<?php echo $spp_class ?> btn-edit"><i class="fas fa-edit"></i></a>
		<!-- Image -->
		<div class="col-sm-4">
			<?php if ($main_data['img_url'] != NULL): ?><a href="<?= $main_data['img_url'] ?>"><img src="<?= $main_data['img_url'] ?>" class="img-fluid center-block" style="max-height: 100%"></a><?php endif; ?>
		</div>
		<div class="col-sm-8">
			<div style="margin-left: .5em">
				<!-- Page header -->
				<h1><?= $main_data['common_name']; ?>

				<!-- [LEP: BAMONA button] -->
				<?php
				if ($spp_type == 'lepidop' && explode(' ', $name)[1] != 'spp') :
					$url = str_replace(' ', '-', $main_data['latin_name']); ?>
					<a href="https://www.butterfliesandmoths.org/species/<?= $url ?>" class="btn btn-<?php echo $spp_class ?>" style="margin-left: 2em;"><i class="fas fa-external-link-alt"></i> BAMONA</a>
				<?php endif ?>

				<!-- [BEE: Discover Life button] -->
				<?php if ($spp_type == 'bee' && explode(' ', $name)[1] != 'spp') :
					$url = str_replace(' ', '+', $main_data['latin_name']); ?>
					<a href="http://www.discoverlife.org/20/q?search=<?= $url ?>" class="btn btn-<?php echo $spp_class ?>" style="margin-left: 2em;"><i class="fas fa-external-link-alt"></i> Discover Life</a>
				<?php endif ?>

				<br/><small><em><?php echo $main_data['latin_name'] ?></em>
				<br/><?php echo $main_data['family_name'] ?></small></h1>

				<!-- Profile -->
				<div class="row">
					<div class="col-sm-6">
						<table class="spp spp-<?php echo $spp_class ?>">
							<tr><th>Type</th><td><?= $main_data['subtype'] ?></td></tr>
							<?php if ($spp_type == 'bee') echo '<tr><th>Specialization</th><td>'.$main_data['specialization'].'</td></tr>' ?>
							<tr><th colspan="2">Notes</th></tr>
							<tr><td colspan = "2"><?php display_list($main_data['notes']); ?></td></tr>
						</table>
					</div>
					<div class="col-sm-6">
						<table class="spp spp-<?php echo $spp_class ?>">
							<tr><th colspan="2">Identification</th></tr>
							<tr><td colspan="2"><?php display_list($main_data['identification']); ?></td></tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<!-- Logs -->
		<div class="col-sm-4">
			<div class="card">
				<div class= "card-header prim-<?php echo $spp_class ?>" id="logbookHeader">
					<div class="mb-0" style="font-size: 1.5em; font-weight: bold;">
						<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#logs" >Logbook <?php echo (count($logs) == 0 ? '<span class="badge badge-light">0</span>' : '<span class="badge badge-dark">'.count($logs).'</span>') ?></button>
					</div>
				</div>
				<div id="logs" class="collapse" aria-labelledby="logbookHeader">
					<div class="card-body">
						<?php if (count($logs) != 0) {
							echo '<table class="spp spp-'.$spp_class.'" style="width: auto">';
							echo '<tr><th>Date</th><th>Stage</th><th>Notes</th></tr>';
							build_rows('SELECT date, stage, notes FROM Log WHERE latin_name=? ORDER BY date DESC', $name);
							echo '</table>';
						} ?>
					</div>
				</div>
			</div>
		</div>
		<!-- Plant interaction table(s) -->
		<div class="col-sm-8">
			<!-- [LEP: Host/nectar plant tables] -->
			<?php if ($spp_type == 'lepidop') : ?>
				<table class="spp spp-l">
					<tr><th colspan="3" style="font-size: 1.5em">Host plants</th></tr>
					<tr><th colspan="2">General preferences</th><td><?= $main_data['host_prefs'] ?></td></tr>
					<tr><th>Logs</th><th>Have</th><th>Plant species</th></tr>
					<?php
					foreach ($larval_food_spp as $plant) {
						echo '<tr>';
						echo '<td style="text-align: center">';
						if (isset($larval_food_logs) && array_key_exists($plant['latin_name'], $larval_food_logs)) {
							echo '<a href="#" data-toggle="popover" data-trigger="hover" data-html="true" data-placement="top" data-content="';
							echo '<table class=&quot;spp spp-'.$spp_class.'&quot;><tr><th>Date</th><th>Notes</th></tr>';
							foreach ($larval_food_logs[$plant['latin_name']] as $log) {
								echo '<tr>';
								echo '<td>'.$log['date'].'</td>';
								echo '<td>'.$log['notes'].'</td>';
								echo '</tr>';
							}
							echo '</table>"><span class="badge badge-dark">'.count($larval_food_logs[$plant['latin_name']]).'</span></a></td>';
						}
						else echo '<span class="badge badge-light">0</span></td>';

						if ($plant['have'] != 0) echo '<td style="text-align: center">&#x2713;</td>';
						else echo '<td style="text-align: center">&mdash;</td>';

						echo '<td>'.$plant['common_name'].' (<em><a href="view_plant.php?name='.$plant['latin_name'].'">'.$plant['latin_name'].'</a></em>)</td>';
						echo '</tr>';
					}
					unset($plant);
					?>
				</table>

				<table class="spp spp-l" style="width: auto">
					<tr><th colspan="6" style="font-size: 1.5em">Nectar plants</th></tr>
					<tr><th colspan="2">General preferences</th><td colspan="4"><?= $main_data['nect_prefs'] ?></td></tr>
			<?php else: ?>
				<table class="spp spp-<?php echo $spp_class ?>" style="width: auto">
					<tr><th colspan="6" style="font-size: 1.5em">Plant interactions</th></tr>
			<?php endif ?>
					<tr><th>Logs</th><th>Have</th><th>Plant species</th><th>Blooms</th><th>Bloom length</th><th>Feeding notes</th></th></tr>
					<?php
					foreach ($adult_food_spp as $plant) {
						echo '<tr>';
						echo '<td style="text-align: center">';
						if (isset($adult_food_logs) && array_key_exists($plant['latin_name'], $adult_food_logs)) {
							echo '<a href="#" data-toggle="popover" data-trigger="hover" data-html="true" data-placement="top" data-content="';
							echo '<table class=&quot;spp spp-'.$spp_class.'&quot;><tr><th>Date</th><th>Notes</th></tr>';
							foreach ($adult_food_logs[$plant['latin_name']] as $log) {
								echo '<tr>';
								echo '<td>'.$log['date'].'</td>';
								$log['notes'] = str_replace('"','&quot;',$log['notes']);
								echo '<td>'.$log['notes'].'</td>';
								echo '</tr>';
							}
							echo '</table>"><span class="badge badge-dark">'.count($adult_food_logs[$plant['latin_name']]).'</span></a></td>';
						}
						else echo '<span class="badge badge-light">0</span></td>';

						if ($plant['have'] != 0) echo '<td style="text-align: center">&#x2713;</td>';
						else echo '<td style="text-align: center">&mdash;</td>';

						echo '<td>'.$plant['common_name'].'<br/>(<em><a href="view_plant.php?name='.$plant['latin_name'].'">'.$plant['latin_name'].'</a></em>)</td>';

						echo '<td style="text-align: center">';
						display_months('Blooms', $plant['latin_name']);
						echo '</td>';

						echo '<td>'.$plant['bloom_length'].'</td>';

						echo '<td>'.$plant['notes'].'</td>';
						echo '</tr>';
					}
					unset($plant);
					?>
				</table>
		</div>
	</div>
</div>
<a href="#" class="btn btn-<?php echo $spp_class ?> btn-del" data-toggle="modal" data-target="#delModal"><i class="fas fa-trash-alt"></i></a>
<div class="modal fade" id="delModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header"><h3 class="modal-title" id="delLabel">Delete species</h3></div>
			<div class="modal-body">Are you sure you want to delete all data for this species?</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
				<a href="delete.php?spp=<?php echo $name ?>" target="_blank" class="btn btn-primary">Delete</a>
			</div>
		</div>
	</div>
</div>
<?php include 'footer.html'; ?>
