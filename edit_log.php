<?php
$cur_page = 'edit_log';
include 'header.html';
include_once 'connect.php';
include_once 'funcs_general.php';
?>
<script>
$(document).ready(function(){
  $("#searchSpp").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#latin-list option").filter(function() { $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1); });
  });
});
</script>

<div class="container-fluid">
	<?php
	global $conn;
	if (isset($_POST['name'])) $name = $_POST['name'];
	else $name = $_GET['name'];
	if (isset($_POST['date'])) $date = $_POST['date'];
	else $date = $_GET['date'];
	if (isset($_POST['stage'])) $stage = $_POST['stage'];
	else $stage = $_GET['stage'];
	
	// Submit edits
	if (isset($_POST['new_name'])) {
		$new_name = $_POST['new_name'];
		$new_date = $_POST['new_date'];
		$new_stage = $_POST['new_stage'];
		$new_notes = $_POST['new_notes'];
		
		$stmt = $conn->prepare("UPDATE Log SET latin_name=:new_name, date=:new_date, notes=:new_notes, stage=:new_stage WHERE latin_name=:name AND date=:date AND stage=:stage");
		$stmt->bindParam(':name', $name);
		$stmt->bindParam(':date', $date);
		$stmt->bindParam(':stage', $stage);
		
		$stmt->bindParam(':new_name', $new_name);
		$stmt->bindParam(':new_date', $new_date);
		$stmt->bindParam(':new_notes', $new_notes);
		$stmt->bindParam(':new_stage', $new_stage);
		
		if ($stmt->execute())
		{
			$rows_affected = $stmt->rowCount();
			if ($rows_affected != 0)
				echo "<div class='alert alert-success alert-dismissable text-center'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>Log entry updated</div>";
			else
				echo "<div class='alert alert-success alert-dismissable text-center'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>No changes made</div>";
		}
	}
	
	// Prompt for edits
	if ($stage != NULL) {
		$stmt = $conn->prepare("SELECT * FROM Log WHERE latin_name=:name AND date=:date AND stage=:stage");
		$stmt->bindParam(':stage', $stage);
	}
	else $stmt = $conn->prepare("SELECT * FROM Log WHERE latin_name=:name AND date=:date");
	$stmt->bindParam(':name', $name);
	$stmt->bindParam(':date', $date);
	
	$stmt->execute();
	$main_data = $stmt->fetch();
	$url = 'edit_log.php?name='.$main_data['latin_name'].'&date='.$main_data['date'].'&stage='.$main_data['stage'];
	?>
	
	<h1 class="text-center">Edit log entry</h1>
	<form action="<?php echo $url ?>" method="post">
		<div class="row justify-content-center">
			<div class="col-sm-6">
				<div class="form-group" style="margin-top: 10px">
					<label for="date">Date</label>
					<input type="text" class="form-control" id="date" name="new_date" value="<?php echo $main_data['date'] ?>">
				</div>
				
				<!-- Latin name -->
				<div class="form-group">
					<label for="latin">Species</label>
					<input class="form-control" id="searchSpp" type="text" placeholder="<?php echo $main_data['latin_name'] ?>">
					<select class="form-control" id="latin-list" name="new_name" size="10">
						<?php
						$stmt = $conn->prepare("SELECT latin_name, common_name FROM Creature");
						$stmt->execute();
						$all_rows = $stmt->fetchAll();
						foreach ($all_rows as $row) {
							echo '<option value="'.$row['latin_name'].'"';
							if ($main_data['latin_name'] == $row['latin_name']) echo ' selected="selected"';
							echo '><em>'.$row['latin_name'].'</em> ('.$row['common_name'].')</option>';
						}
						?>
					</select>
				</div>
				<div class="form-check checkbox-inline" id="stage">
					<input class="form-check-input" type="radio" name="new_stage" value="larva" id="larva" <?php if ($main_data['stage'] == 'larva') echo 'checked' ?>><label class="form-check-label" for="larva" style="margin-left: 10px">Larva</label>	
				</div>
				<div class="form-check checkbox-inline">
					<input class="form-check-input" type="radio" name="new_stage" value="adult" id="adult" <?php if ($main_data['stage'] == 'adult') echo 'checked' ?>><label class="form-check-label" for="adult" style="margin-left: 10px">Adult</label>	
				</div>
				
				<div class="form-group">
					<label for="notes" style="margin-top: 1em">Notes</label>
					<textarea class="form-control" id="notes" name="new_notes"><?php echo $main_data['notes'] ?></textarea>
				</div>
			</div>
		</div>
		<div class="row justify-content-center"><div class="col-1"><button type="Submit" class="btn btn-d" style="color: white">Save</button></div></div>
	</form>
	<div>&nbsp;</div>
</div>

<?php include 'footer.html'; ?>