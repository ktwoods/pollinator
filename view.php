<?php
include_once 'funcs_general.php';
include_once 'header.html';

$name = $_GET['sp'];
// Determine what kind of species template is needed for this page (lepid, bee, or the general template)
$template = template_vals(get_type($name));
$cur_page = $template['type'];

include_once 'header.html';

// Handles submitting edits, if returning from edit.php
if (isset($_POST['latin'])) {
	$stmt = $conn->prepare("UPDATE Creature SET latin_name=?, common_name=?, family_name=?, identification=?, notes=?, img_url=? WHERE latin_name=?");
	$changed = $stmt->execute(array($_POST['latin'], $_POST['common'], $_POST['fam'], $_POST['id'], $_POST['notes'], $_POST['img'], $name)) && $stmt->rowCount() != 0;

	if ($template['type'] == 'lep') {
		$stmt = $conn->prepare("UPDATE Lepidopteran SET host_prefs=?, nect_prefs=? WHERE latin_name=?");
		if ($stmt->execute(array($_POST['gen_host'], $_POST['gen_nect'], $name)) && $stmt->rowCount() != 0) $changed = true;
	}
	else if ($template['type'] == 'bee') {
		$stmt = $conn->prepare("UPDATE Bee SET specialization=? WHERE latin_name=?");
		if ($stmt->execute(array($_POST['spec'], $name)) && $stmt->rowCount() != 0) $changed = true;
	}
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
// Bloom months for each species
$blooms;
foreach ($adult_food_spp as $sp) {
	$stmt = $conn->prepare("SELECT month, verified, notes FROM Blooms NATURAL JOIN Month where latin_name=? ORDER BY month_num");
	$stmt->execute(array($sp['latin_name']));
	$blooms[$sp['latin_name']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid" id="pageContainer">
	<!-- Basic profile -->
	<div class="row">
		<a href="#" id="editURL" class="btn btn-edit"><i class="fas fa-edit"></i></a>
		<!-- Image -->
		<div id="image" class="col-sm-4"></div>
		<div class="col-sm-8">
			<div style="margin-left: .5em">
				<!-- Page header (common name, Latin name, family name) -->
				<h1><a href="#" id="refURL" class="btn" style="margin-left: 2em" hidden><i class="fas fa-external-link-alt"></i></a></h1>
				<!-- Column 1: Type, specialization (if bee), general notes -->
				<div class="row">
					<div class="col-sm-6">
						<table>
							<tr id="type"><th>Type</th><td>&nbsp;</td></tr>
							<tr id="specialization" hidden><th>Specialization</th><td>&nbsp;</td></tr>
							<tr><th colspan="2">Notes</th></tr><tr id="notes"><td colspan="2">&nbsp;</td></tr>
						</table>
					</div>
					<!-- Column 2: identification notes -->
					<div class="col-sm-6">
						<table>
							<tr><th>Identification</th></tr><tr id="identify"><td>&nbsp;</td></tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<!-- Logbook -->
		<div class="col-sm-4" id="logbook"></div>
		<!-- Plant interaction table(s) -->
		<div class="col-sm-8">
			<!-- [LEP: Host/nectar plant tables] -->
			<table id="lepHosts" hidden>
				<tr><th colspan="4" style="font-size: 1.5em">Host plants</th></tr>
				<tr id="hostPrefs"><th colspan="2">General preferences</th><td colspan="2"></td></tr>
				<tr><th>Logs</th><th>Have</th><th>Plant species</th><th>Feeding notes</th></tr>
			</table>
			<table id="lepNectar" hidden>
				<tr><th colspan="6" style="font-size: 1.5em">Nectar plants</th></tr>
				<tr id="nectarPrefs"><th colspan="2">General preferences</th><td colspan="4"></td></tr>
				<tr><th>Logs</th><th>Have</th><th>Plant species</th><th>Blooms</th><th>Bloom length</th><th>Feeding notes</th></tr>
			</table>
			<!-- Bee/etc: general table] -->
			<table id="generalPlants">
				<tr><th colspan="6" style="font-size: 1.5em">Plant interactions</th></tr>
				<tr><th>Logs</th><th>Have</th><th>Plant species</th><th>Blooms</th><th>Bloom length</th><th>Feeding notes</th></tr>
			</table>
		</div>
	</div>
	<a href="#" class="btn btn-del" data-toggle="modal" data-target="#deleteModal"><i class="fas fa-trash-alt"></i></a>
	<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header"><h3 class="modal-title">Delete species</h3></div>
				<div class="modal-body">Are you sure you want to delete all data for this species?</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<a href="#" id="deleteURL" target="_blank" class="btn btn-primary">Delete</a>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	const creature = <?=json_encode($main_data)?>;
	const pageType = <?=json_encode($template['type'])?>;
	const logs = <?=json_encode($logs)?>;
	const larvalPlants = <?=json_encode($larval_food_spp)?>;
	larvalPlants.forEach(sp => { sp.logs = logs.filter(log => log['stage'] === 'larva' && log['notes'].includes(sp['latin_name'])) });
	const adultPlants = <?=json_encode($adult_food_spp)?>;
	adultPlants.forEach(sp => { sp.logs = logs.filter(log => log['stage'] === 'adult' && log['notes'].includes(sp['latin_name'])) });
	const bloomMonths = <?=json_encode($blooms)?>;

	console.log('full', bloomMonths);

	if (<?=json_encode(isset($_POST['latin']))?>) {
		$('#pageContainer').prepend(changeAlert(<?=json_encode($changed)?>, 'Species record updated!'));
	}

	// Buttons
	$('#deleteURL').attr('href', 'delete.php?spp=' + creature['latin_name']);
	$('#editURL').attr('href', 'update_species.php?sp=' + creature['latin_name']);
	if (creature['latin_name'].split(' ')[1] !== 'spp') {
		let btn = $('#refURL');
		// If this page is for a butterfly/moth and describes a species,
		// not a genus, link to its BAMONA profile
		if (pageType === 'lep') {
			btn.attr('href', 'https://www.butterfliesandmoths.org/species/' + creature['latin_name'].replace(' ', '-'));
			btn.append(' BAMONA');
			btn.removeAttr('hidden');
		}
		// If this page is for a bee and describes a species, not a genus,
		// link to its Discover Life profile
		else if (pageType === 'bee') {
			btn.attr('href', 'http://www.discoverlife.org/20/q?search=' + creature['latin_name'].replace(' ', '+'));
			btn.append(' Discover Life');
			btn.removeAttr('hidden');
		}
	}

	// Image
	if (creature['img_url']) {
		$('#image').append(`<a href="${creature['img_url']}"><img src="${creature['img_url']}" class="img-fluid center-block" style="max-height: 100%"></a>`);
	}

	// Basic stats
	$('h1>a').before(creature['common_name']);
	$('h1>a').after(`<br/><small><i>${creature['latin_name']}</i><br/>${creature['family_name']}</small>`);

	$('#type>td').text(creature['subtype']);
	if (creature['specialization']) {
		$('#specialization').removeAttr('hidden');
		$('#specialization>td').html((creature['specialization'] ? creature['specialization'] : 'Unknown'));
	}
	if (creature['notes']) $('#notes>td').html('<ul><li>' + creature['notes'].replace(/\.[ ]*(?=.)/g, '.</li><li>') + '</li></ul>');
	if (creature['identification']) $('#identify>td').html('<ul><li>' + creature['identification'].replace(/\.[ ]*(?=.)/g, '.</li><li>') + '</li></ul>');

	// Logbook
	$('#logbook').append(logbook(logs));

	// Host and nectar plant tables
	function tableRows(headerRow, items) {
		const headerCells = headerRow.children();
		for (let i of items) {
			let row = $('<tr class="text-center"/>');
			$.each(headerCells, (index, val) => {
				let cell = '<td>&nbsp;</td>';
				//console.log(i);
				switch (val.textContent) {
					case 'Logs': cell = $('<td/>').append(countBadgePopover(i['logs'])); break;
					case 'Have': cell = '<td><span class="text-center">' + (+i['have'] ? '&#x2713' : '&mdash;') + '</span></td>'; break;
					case 'Plant species': cell = `<td>${i['common_name']}<br/>(<i><a href="view_plant?spp=${i['latin_name']}">${i['latin_name']}</a></i>)</td>`; break;
					case 'Blooms': if (bloomMonths[i['latin_name']]) cell = '<td>' + monthTooltips(bloomMonths[i['latin_name']]) + '</td>'; break;
					case 'Bloom length': cell = '<td>' + i['bloom_length'] + '</td>'; break;
					case 'Feeding notes': cell = '<td>' + i['notes'] + '</td>'; break;
				}
				row.append(cell);
			});
			row.insertAfter(headerRow);
		}
	}

	if (pageType === 'lep') {
		$('#generalPlants').attr('hidden', '');
		const hostTable = $('#lepHosts');
		const nectarTable = $('#lepNectar');
		hostTable.removeAttr('hidden');
		nectarTable.removeAttr('hidden');
		$('#hostPrefs>td').text(creature['host_prefs']);
		$('#nectarPrefs>td').text(creature['nect_prefs']);
		tableRows($('#lepHosts>tbody').children().last(), larvalPlants);
		tableRows($('#lepNectar>tbody').children().last(), adultPlants);

		$('.btn').not('.modal-footer>.btn').addClass('btn-l');
		$('table').addClass('spp spp-l');
		$('#logbook .card-header').addClass('lep-color-1');
	}
	else {
		tableRows($('#generalPlants>tbody').children().last(), adultPlants);
	}

	// Styling
	if (pageType === 'bee') {
		$('.btn').not('.modal-footer>.btn').addClass('btn-b');
		$('table').addClass('spp spp-b');
		$('#logbook .card-header').addClass('bee-color-1');
	}
	else if (pageType !== 'lep') {
		$('.btn').not('.modal-footer>.btn').addClass('btn-m');
		$('table').addClass('spp spp-o');
		$('#logbook .card-header').addClass('misc-color-1');
	}
</script>
<?php include 'footer.html'; ?>
