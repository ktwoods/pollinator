<?php
$type = $_GET['type'];
$cur_page = 'all_logs';
$header = 'All';
$table = 'Log JOIN Creature USING (latin_name)';
$btn_class = 'o';

if ($type == 'lep') {
	$cur_page = 'lep_logs';
	$header = 'Butterfly & moth';
	$table = 'Log JOIN Lep_full USING (latin_name)';
	$btn_class = 'l';
}
else if ($type == 'bee') {
	$cur_page = 'bee_logs';
	$header = 'Bee';
	$table = 'Log JOIN Bee_full USING (latin_name)';
	$btn_class = 'b';
}
else if ($type == 'other') {
	$cur_page = 'other_logs';
	$header = 'Other creature';
	$table = 'Log JOIN Creature USING (latin_name) WHERE Log.latin_name NOT IN (SELECT latin_name FROM Bee UNION SELECT latin_name FROM Lepidopteran)';
	$btn_class = 'o';
}

include_once 'funcs_general.php';
include_once 'header.html';

// Make updates to a log entry, if coming here from update_logs.php
if (isset($_GET['on'])) {
	$stmt = $conn->prepare("UPDATE Log SET latin_name=:new_name, date=:new_date, notes=:new_notes, stage=:new_stage WHERE latin_name=:name AND date=:date AND stage=:stage");
	$bindVars = array(':name' => $_GET['on'], ':date' => $_GET['od'], ':stage' => $_GET['os'], ':new_name' => $_POST['latin'], ':new_date' => $_POST['date'], ':new_stage' => $_POST['stage'], ':new_notes' => $_POST['notes']);

	$success = ($stmt->execute($bindVars) && $stmt->rowCount() != 0);
}

// Get plant species names
$stmt = $conn->prepare("SELECT latin_name FROM Plant");
$stmt->execute();
$plant_names = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get logs
$stmt = $conn->prepare("SELECT img_url, latin_name, common_name, date, stage, Log.notes FROM $table ORDER BY date DESC, latin_name ASC");
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8" id="logContainer">
			<h1 class="text-center"></h1>
		</div>
	</div>
</div>
<p>&nbsp;</p>

<script>
	const logs = <?=json_encode($logs)?>;
	const buttonClass = <?=json_encode($btn_class)?>;
	let plantNamesStr;
	for (let name of <?=json_encode($plant_names)?>) {
		plantNamesStr += name['latin_name'] + ' ; ';
	}

	const title = $('h1').first();
	title.text('<?=$header?> sightings');
	const container = $('#logContainer');

	if (<?=json_encode(isset($_GET['on']))?>) {
		title.before(changeAlert(<?=json_encode($success)?>, `Log entry for ${<?=json_encode($_POST['stage'])?>} <i>${<?=json_encode($_POST['latin'])?>}</i> (${<?=json_encode($_POST['date'])?>}) updated.`), header);
	}

	let curYear, numLogsInYear, yearTable, yearTableHeader;
	let speciesInYear = new Set();
	for (let log of logs) {
		// if this log is a new year, start a new header and table
		if (curYear !== +log['date'].substr(0,4)) {
			// fill in text for previous header (if it exists, which it won't if we're at the start of the logs)
			if (yearTableHeader) yearTableHeader.text(`${curYear} (${numLogsInYear} logs, ${speciesInYear.size} spp)`);
			// move to new header and table
			yearTableHeader = $('<h2/>');
			container.append(yearTableHeader);

			numLogsInYear = 0;
			speciesInYear.clear();
			curYear = +log['date'].substr(0,4);

			yearTable = $('<table><tr><th colspan="2">&nbsp;</th><th>Name</th><th>Date</th><th>Notes</th></tr></table>');
			container.append(yearTable);
			yearTable.css('width', '100%');
		}

		let logRow = $('<tr/>');
		yearTable.append(logRow);

		// edit button
		let logCell = $('<td/>');
		logRow.append(logCell);
		logCell.append($('<a/>', {
			'href': `update_logs.php?do=edit&n=${log['latin_name']}&d=${log['date']}&s=${log['stage']}`,
			'class': 'btn btn-' + buttonClass,
			'html': '<i class="fas fa-edit"></i>'
		}));
		// thumbnail
		logRow.append($('<td/>').append(thumbnail(log['img_url'], log['latin_name'], '3rem')));
		// Latin and common name
		logRow.append($(`<td style="white-space: nowrap">${log['common_name']}<br/><i>(<a href="view.php?spp=${log['latin_name']}">${log['latin_name']}</a>)</i></td>`));
		// date
		logRow.append('<td style="white-space: nowrap">' + log['date'] + '</td>');
		// notes
		let noteHTML = '';
		let noteWords = log['notes'].split(' ');
		if (noteWords.length === 1) noteHTML = log['notes'];
		else {
			for (let i = 0; i < noteWords.length - 1; i++) {
				let possibleNameOrig = noteWords[i] + ' ' + noteWords[i+1];
				let possibleName = possibleNameOrig.replace(/^[^a-zA-Z ]+|[^a-zA-Z ]+$/g, '');

				if (plantNamesStr.includes(possibleName)) {
					// append both words enclosed in a hyperlink
					possibleNameOrig = possibleNameOrig.replace(possibleName, `<a href="view_plant.php?name=${possibleName}"><i>${possibleName}</i></a>`);
					noteHTML += possibleNameOrig + ' ';
					i++;
				}
				else noteHTML += noteWords[i] + ' ';

				if (i == noteWords.length - 2) noteHTML += noteWords[i+1];
			}
		}
		logRow.append($('<td>' + noteHTML + '</td>'));

		numLogsInYear++;
		speciesInYear.add(log['latin_name']);
	}
	yearTableHeader.text(`${curYear} (${numLogsInYear} logs, ${speciesInYear.size} spp)`);
</script>
<?php include_once 'footer.html'; ?>
