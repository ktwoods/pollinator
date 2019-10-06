<?php
include_once 'funcs_general.php';

$name = $_GET['spp'];
// Determine what kind of species template is needed for this page (lepid, bee, or the general template)
$template = template_vals(get_type($name));
$cur_page = $template['type'];

include_once 'header.html';

// Handles submitting edits, if returning from edit.php
if (isset($_POST['latin'])) {
	$stmt = $conn->prepare("UPDATE Creature SET latin_name=?, common_name=?, family_name=?, identification=?, notes=?, img_url=? WHERE latin_name=?");
	$changed = $stmt->execute(array($_POST['latin'], $_POST['common'], $_POST['fam'], $_POST['id'], $_POST['notes'], $_POST['img'], $_GET['spp'])) && $stmt->rowCount() != 0;

	if ($template['type'] == 'lepidop') {
		$stmt = $conn->prepare("UPDATE Lepidopteran SET host_prefs=?, nect_prefs=? WHERE latin_name=?");
		if ($stmt->execute(array($_POST['gen_host'], $_POST['gen_nect'], $_GET['spp'])) && $stmt->rowCount() != 0) $changed = true;
	}
	else if ($template['type'] == 'bee') {
		$stmt = $conn->prepare("UPDATE Bee SET specialization=? WHERE latin_name=?");
		if ($stmt->execute(array($spec, $name)) && $stmt->rowCount() != 0) $changed = true;
	}

	success_fail_message($changed, 'Species record updated!');
}

/* Get all of the data that's going to be needed on this page */
// $main_data = attributes for this species
$stmt = $conn->prepare("SELECT * FROM ".$template['table']." WHERE latin_name = ?");
$stmt->execute(array($name));
$main_data = $stmt->fetch();

// $logs = all logged sightings of this species
$stmt = $conn->prepare("SELECT date, stage, notes FROM Log WHERE latin_name = ? ORDER BY date DESC");
$stmt->execute(array($name));
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $larval_food_spp and $adult_food_spp = all plants that this species should interact or has interacted with as larva and adult respectively
$stmt = $conn->prepare("SELECT latin_name, common_name, have, Feeds.notes FROM Plant JOIN Feeds ON (latin_name=plant_name) WHERE creature_name=? AND stage='larva' ORDER BY latin_name ASC");
$stmt->execute(array($name));
$larval_food_spp = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT latin_name, common_name, have, bloom_length, Feeds.notes FROM (Plant NATURAL JOIN Blooms NATURAL JOIN Month) JOIN Feeds ON (latin_name=plant_name) WHERE creature_name=? AND stage='adult' GROUP BY latin_name ORDER BY MIN(month_num) ASC, latin_name ASC");
$stmt->execute(array($name));
$adult_food_spp = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $larval_food_logs and $adult_food_logs store all logs sorted by plant species
foreach ($larval_food_spp as $plant) {
	// $rel_logs stores the subset of logs that are associated with that plant
	foreach($logs as $l) {
		if (strpos($l['notes'], $plant['latin_name']) !== FALSE && $l['stage'] == 'larva') {
			$rel_logs[] = $l;
		}
	}
	if (isset($rel_logs)) $larval_food_logs[$plant['latin_name']] = $rel_logs;
	unset($l, $rel_logs);
}
unset($plant);
foreach ($adult_food_spp as $plant) {
	foreach ($logs as $l) {
		if (strpos($l['notes'], $plant['latin_name']) !== FALSE && $l['stage'] == 'adult') {
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
		<a href="edit.php?spp=<?php echo $name ?>" class="btn btn-<?php echo $template['class'] ?> btn-edit"><i class="fas fa-edit"></i></a>
		<!-- Image -->
		<div class="col-sm-4">
			<?php if ($main_data['img_url']) {
				echo '<a href="' . $main_data['img_url'] . '"><img src="' . $main_data['img_url'] . '" class="img-fluid center-block" style="max-height: 100%"></a>';
			} ?>
		</div>
		<div class="col-sm-8">
			<div style="margin-left: .5em">
				<!-- Page header -->
				<h1>
					<?php
					echo $main_data['common_name'];

					/* Some buttons for convenience; I refer to BAMONA and DL frequently,
					   and their URLs are easy to calculate */
					// If this page is for a butterfly/moth and describes a species,
					// not a genus, link to its BAMONA profile
					if ($template['type'] == 'lepidop' && explode(' ', $name)[1] != 'spp') {
						$url = str_replace(' ', '-', $main_data['latin_name']);
						echo '<a href="https://www.butterfliesandmoths.org/species/' . $url
						     . '" class="btn btn-' . $template['class']
								 . '" style="margin-left: 2em;"><i class="fas fa-external-link-alt"></i> BAMONA</a>';
					}
					// If this page is for a bee and describes a species, not a genus,
					// link to its Discover Life profile
					else if ($template['type'] == 'bee' && explode(' ', $name)[1] != 'spp') {
						$url = str_replace(' ', '+', $main_data['latin_name']);
						echo '<a href="http://www.discoverlife.org/20/q?search=' . $url
								 . '" class="btn btn-' . $template['class']
								 . '" style="margin-left: 2em;"><i class="fas fa-external-link-alt"></i> Discover Life</a>';
					}
					?>

					<!-- Page subheader (Latin name and family name) -->
					<br/><small><em><?php echo $main_data['latin_name'] ?></em>
					<br/><?php echo $main_data['family_name'] ?></small>
				</h1>

				<!-- Column 1: Type, specialization (if bee), general notes -->
				<div class="row">
					<div class="col-sm-6">
						<table class="spp spp-<?php echo $template['class'] ?>">
							<tr><th>Type</th><td><?php echo $main_data['subtype'] ?></td></tr>
							<?php if ($template['type'] == 'bee') echo '<tr><th>Specialization</th><td>'.$main_data['specialization'].'</td></tr>' ?>
							<tr><th colspan="2">Notes</th></tr>
							<tr><td colspan = "2"><?php display_list($main_data['notes']) ?></td></tr>
						</table>
					</div>
					<!-- Column 2: identification notes -->
					<div class="col-sm-6">
						<table class="spp spp-<?php echo $template['class'] ?>">
							<tr><th colspan="2">Identification</th></tr>
							<tr><td colspan="2"><?php display_list($main_data['identification']) ?></td></tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<!-- Logbook -->
		<div class="col-sm-4">
			<?php logbook('SELECT date, stage, notes FROM Log WHERE latin_name=? ORDER BY date DESC', $name, count($logs), $template['class']); ?>
		</div>
		<!-- Plant interaction table(s) -->
		<div class="col-sm-8">
			<!-- [LEP: Host/nectar plant tables] -->
			<?php if ($template['type'] == 'lepidop') : ?>
				<table class="spp spp-l">
					<tr><th colspan="4" style="font-size: 1.5em">Host plants</th></tr>
					<tr><th colspan="2">General preferences</th><td colspan="2"><?php echo $main_data['host_prefs'] ?></td></tr>
					<tr><th>Logs</th><th>Have</th><th>Plant species</th><th>Feeding notes</th></tr>
					<?php
					foreach ($larval_food_spp as $plant) {
						echo '<tr>';
						// First cell: logs button (indicates count, and clicking brings up the associated table in a popover)
						echo '<td style="text-align: center">';
						echo popover_badge($larval_food_logs, $plant['latin_name'], $template['class']);
						echo '</td>';

						// Second cell: check mark or em-dash indicating if plant is owned
						if ($plant['have'] != 0) echo '<td style="text-align: center">&#x2713;</td>';
						else echo '<td style="text-align: center">&mdash;</td>';

						// Third cell: Plant species (common name and Latin name, linking to plant page)
						echo '<td>' . $plant['common_name']
						     . ' (<em><a href="view_plant.php?spp=' . $plant['latin_name'] . '">' . $plant['latin_name']
								 . '</a></em>)</td>';

						// Fourth cell: Any notes associated with this plant
						echo '<td>' . $plant['notes'] . '</td>';

						echo '</tr>';
					}
					unset($plant);
					?>
				</table>
			<!-- The "Nectar plants" table for butterflies/moths and the "Plant interactions" table
			for everything else use the same template; they just need different headers -->
				<!-- Start the butterfly/moth version before exiting "if ($template['type'] == 'lepidop')"-->
				<table class="spp spp-l" style="width: 100%">
					<tr><th colspan="6" style="font-size: 1.5em">Nectar plants</th></tr>
					<tr><th colspan="2">General preferences</th><td colspan="4"><?php echo $main_data['nect_prefs'] ?></td></tr>
			<!-- Else it's not a lepidopteran, so just start the regular version -->
			<?php else: ?>
				<table class="spp spp-<?php echo $template['class'] ?>" style="width: 100%">
					<tr><th colspan="6" style="font-size: 1.5em">Plant interactions</th></tr>
			<!-- Continue building the rest of the table -->
			<?php endif ?>
					<tr><th>Logs</th><th>Have</th><th>Plant species</th><th>Blooms</th><th>Bloom length</th><th>Feeding notes</th></th></tr>
					<?php
					foreach ($adult_food_spp as $plant) {
						echo '<tr>';
						// First cell: logs button (indicates count, and clicking brings up the associated table in a popover)
						echo '<td style="text-align: center">';
						echo popover_badge($adult_food_logs, $plant['latin_name'], $template['class']);
						echo '</td>';

						// Second cell: check mark or em-dash indicating if plant is owned
						if ($plant['have'] != 0) echo '<td style="text-align: center">&#x2713;</td>';
						else echo '<td style="text-align: center">&mdash;</td>';

						// Third cell: Plant species (common name and Latin name, linking to plant page)
						echo '<td>'.$plant['common_name'].'<br/>(<em><a href="view_plant.php?spp='.$plant['latin_name'].'">'.$plant['latin_name'].'</a></em>)</td>';

						// Fourth cell: Months during which this plant blooms
						echo '<td style="text-align: center">';
						display_months('Blooms', $plant['latin_name']);
						echo '</td>';

						// Fifth cell: Approximate length of bloom period
						echo '<td>'.$plant['bloom_length'].'</td>';

						// Sixth cell: Any notes associated with this plant
						echo '<td>'.$plant['notes'].'</td>';
						echo '</tr>';
					}
					unset($plant);
					?>
				</table>
		</div>
	</div>
</div>
<!-- Delete button -->
<?php delete_button($name, $template['class']) ?>
<?php include 'footer.html'; ?>
