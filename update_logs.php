<?php
$cur_page = 'updateLogs';
include_once 'funcs_general.php';
include_once 'header.html';

$is_new_log = !isset($_GET['n']);

if ($is_new_log && isset($_POST['latin'])) {
  // If a value for 'latin' exists, this is a new log to submit
  $stmt = $conn->prepare("INSERT INTO Log VALUES (?, ?, ?, ?)");
  $success = $stmt->execute(array($_POST['latin'], $_POST['date'], $_POST['notes'], $_POST['stage'])) && $stmt->rowCount() != 0;
}
else {
  // Is an existing log being edited, so fetch the full log
  $stmt = $conn->prepare("SELECT * FROM Log WHERE latin_name=? AND date=? AND stage=?");
  $stmt->execute(array($_GET['n'], $_GET['d'], $_GET['s']));
  $cur_log = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmt = $conn->prepare("SELECT latin_name, common_name FROM Creature");
$stmt->execute();
$all_creatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
	<h1 class="text-center"></h1>
	<form method="post">
		<div class="row justify-content-center">
			<div class="col-sm-6">
        <!-- Date -->
				<div class="form-group" style="margin-top: 10px">
					<label for="date">Date</label>
					<input type="date" class="form-control" name="date" pattern="(?:19|20)\d{2}-(?:0[1-9]|1[0-2])-(?:0[1-9]|[12]\d|3[01])" required>
				</div>
				<!-- Species name -->
				<div class="form-group">
					<label for="latinName">Species</label>
					<input class="form-control" name="latin" id="latinName" type="text" list="nameList" autocomplete="off" required>
          <datalist id="nameList"></datalist>
				</div>
        <!-- Stage -->
        <div id="stageRadios">
  				<div class="form-check checkbox-inline">
  					<input class="form-check-input" type="radio" name="stage" value="larva" id="larva" required>
            <label class="form-check-label" for="larva" style="margin-left: 10px">Larva</label>
  				</div>
  				<div class="form-check checkbox-inline">
  					<input class="form-check-input" type="radio" name="stage" value="adult" id="adult" required>
            <label class="form-check-label" for="adult" style="margin-left: 10px">Adult</label>
  				</div>
        </div>
        <!-- Notes -->
				<div class="form-group">
					<label for="notes" style="margin-top: 1em">Notes</label>
					<textarea class="form-control" id="notes" name="notes"></textarea>
				</div>
			</div>
		</div>
    <!-- 'Save' button -->
		<div class="row justify-content-center">
      <div class="col-1">
        <button type="Submit" id="submitButton" class="btn btn-d" style="color: white">Save</button>
      </div>
    </div>
	</form>
	<div>&nbsp;</div>
</div>

<script>
  const isNewLog = <?=json_encode($is_new_log)?>;
  const previousLogDate = <?=json_encode($_POST['date'])?>;
  const log = <?=json_encode($cur_log)?>;
  let type = <?=json_encode(get_type($cur_log['latin_name']))?>;
  if (type == 'Lepidopteran') type = 'lep';
  else if (type != 'Bee') type = 'other';
  const allCreatures = <?=json_encode($all_creatures)?>;

  //    Get elements from DOM
  const title = $('h1').first();
  const logForm = document.getElementsByTagName('form')[0];
  const logDate = logForm.elements.date;
  const logSpecies = logForm.elements.latin;
  const nameList = $('#nameList');
  const logNotes = logForm.elements.notes;
  const submitButton = logForm.elements.submitButton;

  //    Generate species options
  for (let species of allCreatures) {
    nameList.append($(`<option data-latin-name="${species['latin_name']}">${species['latin_name']} (${species['common_name']})</option>`));
  }
  //    Build today's date in YYYY-MM-DD format and use for date validation
  let today = new Date();
  let month = today.getMonth() + 1;
  let day = today.getDate();
  today = `${today.getFullYear()}-${(month < 10 ? '0' : '') + month}-${(day < 10 ? '0' : '') + day}`;
  logDate.setAttribute('max', today);

  // Print results if a new log has just been submitted
  if (<?=json_encode($success)?>) {
  		title.before(changeAlert(true, `New log added for ${<?=json_encode($_POST['stage'])?>} <em>${<?=json_encode($_POST['latin'])?>}</em>, ${<?=json_encode($_POST['date'])?>}!`));
  }

  // Set up for a new log, or prefill with existing log
  if (isNewLog) {
    title.text('New log entry');

    logDate.value = previousLogDate || today;
    logSpecies.setAttribute('placeholder', 'Search species by Latin or common name');
    logForm.elements.adult.toggleAttribute('checked');

    logForm.setAttribute('action', 'update_logs.php');
  }
  else {
    title.text('Edit log entry');

    logDate.value = log['date'];
    logSpecies.value = log['latin_name'];
    logForm.elements[log['stage']].toggleAttribute('checked');
    logNotes.textContent = log['notes'];

    logForm.setAttribute('action', `logs.php?type=${type}&on=${log['latin_name']}&od=${log['date']}&os=${log['stage']}`);
  }

  // Make sure user has entered a valid species
  function validateSpecies() {
    let creatureInput = logSpecies.value;
    for (let creature of allCreatures) {
      if (creatureInput.includes(creature['latin_name'])) {
        logSpecies.value = creature['latin_name'];
        logSpecies.setCustomValidity('');
        return;
      }
    }
    logSpecies.setCustomValidity('Please enter a known species name.');
  }
  submitButton.addEventListener('click', validateSpecies);
</script>
<?php include_once 'footer.html'; ?>
