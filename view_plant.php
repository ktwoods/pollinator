<?php
$cur_page = 'plants';
include_once 'header.html';

// Handles submitting edits, if returning from edit_plant.php
if (isset($_POST['latin'])) {
	$stmt = $conn->prepare("UPDATE Plant SET latin_name=?, common_name=?, family=?, have=?, want=?, bloom_length=?, tags=?, research_notes=?, observations=?, img_url=? WHERE latin_name=?");

	$success = $stmt->execute(array($_POST['latin'], $_POST['common'],$_POST['fam'], $_POST['have'], $_POST['want'], $_POST['blen'], $_POST['tags'], $_POST['notes'], $_POST['obs'], $_POST['img'], $_GET['spp'])) && $stmt->rowCount() != 0;
}

$stmt = $conn->prepare("SELECT * FROM Plant WHERE latin_name = ?");
$stmt->execute(array($_GET['sp']));
$main_data = $stmt->fetch(PDO::FETCH_ASSOC);
$name = $main_data['latin_name'];

// $logs = Array of all logs that mention this plant
$stmt = $conn->prepare("SELECT latin_name, date, stage, notes FROM Log WHERE notes LIKE CONCAT('%',?,'%') ORDER BY date DESC");
$stmt->execute(array($name));
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $full_spp_list = Array of all species known or suspected to feed on this plant
$stmt = $conn->prepare("SELECT creature_name AS latin_name, common_name, stage, Feeds.notes, family_name, subtype FROM Feeds JOIN Creature_full ON creature_name=Creature_full.latin_name WHERE plant_name=? ORDER BY subtype, type, stage DESC, family_name, latin_name");
$stmt->execute(array($name));
$full_spp_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// months blooming
$stmt = $conn->prepare("SELECT * FROM Blooms NATURAL JOIN Month WHERE latin_name = ? ORDER BY month_num");
$stmt->execute(array($name));
$blooming = $stmt->fetchAll(PDO::FETCH_ASSOC);
// months fruiting
$stmt = $conn->prepare("SELECT * FROM Fruits NATURAL JOIN Month WHERE latin_name = ? ORDER BY month_num");
$stmt->execute(array($name));
$fruiting = $stmt->fetchAll(PDO::FETCH_ASSOC);

// caterpillar species
$stmt = $conn->prepare("SELECT latin_name, common_name FROM Lep_full JOIN Feeds ON (creature_name=latin_name) WHERE plant_name=? AND stage='larva' ORDER BY family_name, latin_name");
$stmt->execute(array($name));
$caterpillars = $stmt->fetchAll(PDO::FETCH_ASSOC);
// bee species
$plant_genus = explode(' ', $main_data['latin_name'])[0];
$stmt = $conn->prepare("SELECT latin_name, common_name, specialization FROM Bee_full WHERE specialization LIKE '%{$plant_genus}%' OR specialization LIKE '%{$main_data['family']}%' ORDER BY subtype, family_name, latin_name");
$stmt->execute();
$bees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid" id="pageContainer">
	<!-- Main profile -->
	<div class="row">
		<a href="#" class="btn btn-edit"><i class="fas fa-edit"></i></a>
		<!-- Image -->
		<div id="image" class="col-sm-4"></div>
		<div class="col-sm-8">
			<!-- Page header (common name, Latin name, family name) -->
			<h1></h1>
			<!-- Have/want badges -->
			<div id="haveWant"></div>
			<!-- Profile -->
			<div class="row">
				<!-- Column 1: tags, blooms, bloom length, fruits -->
				<div class="col-sm-6">
					<table>
						<tr id="tags"><th>Tags</th><td>&nbsp;</td></tr>
						<tr id="bloomMonths"><th>Blooms</th><td>&nbsp;</td></tr>
						<tr id="bloomLength"><th>Bloom length</th><td>&nbsp;</td></tr>
						<tr id="fruitMonths"><th>Fruits</th><td>&nbsp;</td>
						<tr><td colspan="2" style="background-color: white">&nbsp;</td></tr>
					</table>
				</div>
				<!-- Column 2: characteristics, notes -->
				<div class="col-sm-6">
					<table style="width: 100%">
						<tr><th colspan="2">Characteristics</th></tr>
						<tr id="characteristics"><td colspan="2" class="text-left">&nbsp;</td></tr>
						<tr><th colspan="2">Notes</th></tr>
						<tr id="notes"><td colspan="2" class="text-left">&nbsp;</td></tr>
					</table>
				</div>
			</div>
		</div>
	</div>
	<!-- Plant interaction tables -->
	<div class="row">
		<!-- Logbook -->
		<div class="col-sm-4" id="logbook"></div>
		<div class="col-sm-8">
			<!-- Specialist butterflies, moths, and bees -->
			<table style="table-layout: auto;">
				<tr><th colspan="2" style="font-size: 1.5em">Specialist insects</th></tr>
				<tr><th>Host plant for</th><td class="text-left"><ul id="caterpillarList">n/a</ul></td></tr>
				<tr><th>Specialist bees</th><td class="text-left"><ul id="beeList">n/a</ul></td></tr>
			</table>
			<!-- All visitors, organized by type -->
			<table><tr><th style="font-size: 1.5em">All visitors</th></tr></table>
			<div id="visitorsByType"></div>
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
	const plant = <?=json_encode($main_data)?>;
	plant.blooming = <?=json_encode($blooming)?>;
	plant.fruiting = <?=json_encode($fruiting)?>;
	plant.caterpillars = <?=json_encode($caterpillars)?>;
	plant.bees = <?=json_encode($bees)?>;

	const logs = <?=json_encode($logs)?>;
	const creatureSpecies = <?=json_encode($full_spp_list)?>;
	creatureSpecies.forEach(species => { species.logs = logs.filter(log => log['latin_name'] === species['latin_name']) });

	if (<?=json_encode(isset($_POST['latin']))?>) {
		$('#pageContainer').prepend(changeAlert(<?=json_encode($success)?>, 'Species record updated!'));
	}

	// Basic plant stats
	$('.btn-edit').attr('href', 'update_plants.php?name=' + plant['latin_name']);

	if (plant['img_url']) {
		$('#image').append(`<a href="${plant['img_url']}"><img src="${plant['img_url']}" class="img-fluid center-block" style="max-height: 100%"></a>`);
	}

	$('h1').first().append(`${plant['common_name']}<br/><small><i>${plant['latin_name']}</i><br/>${plant['family']}</small>`);

	let havePill = (+plant['have'] ? '<span class="spp-tag">have &#x2713;</span>' : '<span class="spp-tag tag-no">have &mdash;</span>');
	let wantPill = (+plant['want'] ? '<span class="spp-tag">want &#x2713;</span>' : '<span class="spp-tag tag-no">want &mdash;</span>');
	$('#haveWant').append(havePill, wantPill);

	$('#tags>td').text(plant['tags']);
	$('#bloomMonths>td').html(monthTooltips(plant.blooming));
	$('#bloomLength>td').text((plant['bloom_length'] ? plant['bloom_length'] : 'n/a'));
	$('#fruitMonths>td').html(monthTooltips(plant.fruiting));
	if (plant['research_notes']) $('#characteristics>td').html('<ul><li>' + plant['research_notes'].replace(/\.[ ]*(?=.)/g, '.</li><li>') + '</li></ul>');
	if (plant['observations']) $('#notes>td').html('<ul><li>' + plant['observations'].replace(/\.[ ]*(?=.)/g, '.</li><li>') + '</li></ul>');

	// Specialists table
	let list = '';
	for (let cat of plant.caterpillars) {
		list += `<li>${cat['common_name']} (<i><a href="view.php?spp=${cat['latin_name']}">${cat['latin_name']}</a></i>)</li>`;
	}
	if (list) $('#caterpillarList').html(list);
	list = '';
	for (let bee of plant.bees) {
		list += `<li>${bee['common_name']} (<i><a href="view.php?spp=${bee['latin_name']}">${bee['latin_name']}</a></i>) — ${bee['specialization']}</li>`;
	}
	if (list) $('#beeList').html(list);

	// Logbook
	$('#logbook').append(logbook(logs));

	// All visitors
	const subtypeSet = new Set();
	for (let i = 0; i < creatureSpecies.length; i++) {
		let sp = creatureSpecies[i];
		creatureSpecies[i] = {
			'logs': countBadgePopover(sp['logs']),
			'species': `${sp['common_name']}<br/>(<i><a href="view_plant?spp=${sp['latin_name']}">${sp['latin_name']}</a></i>)`,
			'family': sp['family_name'],
			'stage': sp['stage'],
			'notes': sp['notes'],
			'subtype': sp['subtype']
		};
		subtypeSet.add(sp['subtype']);
	}
	const subtypes = [];
	subtypeSet.forEach(subtype => { subtypes.push({'subtype': subtype}) });
	$('#visitorsByType').append(buildTabsByCategory('subtype', subtypes, creatureSpecies));

	$('#deleteURL').attr('href', 'delete.php?spp=' + plant['latin_name']);

	$('.btn').not('.modal-footer>.btn').addClass('btn-p');
	$('table').addClass('spp spp-p');
	$('.tab-pane>table').css('width', 'auto');
	$('#logbook .card-header').addClass('plant-color-1');
</script>
<?php include_once 'footer.html'; ?>
