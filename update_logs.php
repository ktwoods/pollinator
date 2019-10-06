<?php
$cur_page = 'update_logs';
include_once 'funcs_general.php';
include_once 'header.html';

$editing = ($_GET['do'] == 'edit');

if (!$editing) {
  $url = 'update_logs.php?do=add';

  // Attempt to submit log, and print success/fail alert
  if (isset($_POST['name'])) { // Submitting data
    $stmt = $conn->prepare("INSERT INTO Log VALUES (?, ?, ?, ?)");

    $success = $stmt->execute(array($_POST['name'], $_POST['date'], $_POST['notes'], $_POST['stage'])) && $stmt->rowCount() != 0;
    success_fail_message($success, "New log added for {$_POST['stage']} <em>{$_POST['name']}</em>, {$_POST['date']}!");
  }
}
else {
  $name = $_GET['n'];
  $date = $_GET['d'];
  $stage = $_GET['s'];
  // Get notes field
  $stmt = $conn->prepare("SELECT notes FROM Log WHERE latin_name=? AND date=? AND stage=?");
  $stmt->execute(array($name, $date, $stage));
  $notes = $stmt->fetch()['notes'];

  // Get type for rerouting to correct url
  $type = get_type($name);
  if ($type == 'Lepidopteran') $type = 'lep';
  else if ($type == 'Bee') $type = 'bee';
  else $type = 'other';
  $url = 'logs.php?type='.$type.'&on='.$name.'&od='.$date.'&os='.$stage;
}
?>

<div class="container-fluid">
	<h1 class="text-center"><?php echo ($editing ? 'Edit' : 'New') ?> log entry</h1>
	<form action="<?php echo $url ?>" method="post">
		<div class="row justify-content-center">
			<div class="col-sm-6">
        <!-- Date -->
				<div class="form-group" style="margin-top: 10px">
					<label for="date">Date</label>
          <?php
          if (!$editing)
					{
            $today = getdate();
  					$mon = $today['mon'];
  					$day = $today['mday'];
  					$datestring = $today['year'].'-'.($mon < 10? '0'.$mon : $mon).'-'.($day < 10? '0'.$day : $day);
          }
					?>
					<input type="text" class="form-control" id="date" name="date" value="<?php echo ($editing ? $date : $datestring) ?>">
          <small id="dateHelp" class="form-text text-muted">YYYY-MM-DD</small>
				</div>
				<!-- Species name selector -->
				<div class="form-group">
					<label for="latin">Species</label>
					<input class="form-control" id="searchSpp" type="text" placeholder="<?php echo ($editing ? $name : 'Search species') ?>">
					<select class="form-control" id="latin-list" name="name" size="10">
						<?php
						$stmt = $conn->prepare("SELECT latin_name, common_name FROM Creature");
						$stmt->execute();
						$all_rows = $stmt->fetchAll();
						foreach ($all_rows as $row) {
							echo '<option value="'.$row['latin_name'].'"';
							if ($editing && $name == $row['latin_name']) echo ' selected="selected"';
							echo '>'.$row['latin_name'].' ('.$row['common_name'].')</option>';
						}
						?>
					</select>
				</div>
        <!-- Stage ('adult' selected by default, if new log) -->
				<div class="form-check checkbox-inline" id="stage">
					<input class="form-check-input" type="radio" name="stage" value="larva" id="larva" <?php if ($editing && $stage == 'larva') echo 'checked' ?>><label class="form-check-label" for="larva" style="margin-left: 10px">Larva</label>
				</div>
				<div class="form-check checkbox-inline">
					<input class="form-check-input" type="radio" name="stage" value="adult" id="adult" <?php if (($editing && $stage == 'adult') || !$editing) echo 'checked' ?>><label class="form-check-label" for="adult" style="margin-left: 10px">Adult</label>
				</div>
        <!-- Notes -->
				<div class="form-group">
					<label for="notes" style="margin-top: 1em">Notes</label>
					<textarea class="form-control" id="notes" name="notes"><?php if ($editing) echo $notes ?></textarea>
				</div>
			</div>
		</div>
    <!-- 'Save' button -->
		<div class="row justify-content-center"><div class="col-1"><button type="Submit" class="btn btn-d" style="color: white">Save</button></div></div>
	</form>
	<div>&nbsp;</div>
</div>
<script>
$(document).ready(function(){
  $("#searchSpp").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#latin-list option").filter(function() { $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1); });
  });
});
</script>
<?php include_once 'footer.html'; ?>
