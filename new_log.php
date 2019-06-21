<?php
$cur_page = 'new_log';
include 'header.php';
include_once 'connect.php';
include_once 'build_table.php';
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

	if (isset($_POST['latin'])) // Submitting data
	{
		$latin = $_POST['latin'];
		$date = $_POST['date'];
		$stage = $_POST['stage'];
		$notes = $_POST['notes'];
		
		$stmt = $conn->prepare("INSERT INTO Log VALUES (:latin, :date, :notes, :stage)");
		$stmt->bindParam(':latin', $latin);
		$stmt->bindParam(':date', $date);
		$stmt->bindParam(':stage', $stage);
		$stmt->bindParam(':notes', $notes);
		
		if ($stmt->execute())
		{
			$rows_affected = $stmt->rowCount();
			if ($rows_affected != 0) echo "<div class='alert alert-success alert-dismissable text-center'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>New log added for <em>$latin</em>, $date!</div>";
			else echo "<div class='alert alert-warning alert-dismissable text-center'><a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>Error: unable to add entry for <em>$latin</em> on $date to the database.</div>";
		}
	}
	?>
	
	<h1 class="text-center">New log entry</h1>
	<form action="new_log.php" method="post">
		<div class="row justify-content-center">
			<div class="col-sm-6">
				<div class="form-group" style="margin-top: 10px">
					<label for="date">Date</label>
					<?php
					$today = getdate();
					$mon = $today['mon'];
					$day = $today['mday'];
					$datestring = $today['year'].'-'.($mon < 10? '0'.$mon : $mon).'-'.($day < 10? '0'.$day : $day);
					?>
					<input type="text" class="form-control" id="date" name="date" value="<?= $datestring ?>">
					<small id="dateHelp" class="form-text text-muted">YYYY-MM-DD</small>
				</div>
				
				<!-- Latin name -->
				<div class="form-group">
					<label for="latin">Species</label>
					<input class="form-control" id="searchSpp" type="text" placeholder="Search species">
					<select class="form-control" id="latin-list" name="latin" size="10">
						<?php
						$stmt = $conn->prepare("SELECT latin_name, common_name FROM Creature");
						$stmt->execute();
						$all_rows = $stmt->fetchAll();
						foreach ($all_rows as $row) {
							echo '<option value="'.$row['latin_name'].'"><em>'.$row['latin_name'].'</em> ('.$row['common_name'].')</option>';
						}
						?>
					</select>
				</div>
				<div class="form-check checkbox-inline" id="stage">
					<input class="form-check-input" type="radio" name="stage" value="larva" id="larva"><label class="form-check-label" for="larva" style="margin-left: 10px">Larva</label>	
				</div>
				<div class="form-check checkbox-inline">
					<input class="form-check-input" type="radio" name="stage" value="adult" id="adult" checked><label class="form-check-label" for="adult" style="margin-left: 10px">Adult</label>	
				</div>
				
				<div class="form-group">
					<label for="notes" style="margin-top: 1em">Notes</label>
					<textarea class="form-control" id="notes" name="notes"></textarea>
				</div>	
			</div>
		</div>
		<div class="row justify-content-center"><div class="col-1"><button type="Submit" class="btn btn-d" style="color: white">Create</button></div></div>
	</form>
	<div>&nbsp;</div>
</div>

<?php include 'footer.html'; ?>