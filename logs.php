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

// Get array of plant species names
$stmt = $conn->prepare("SELECT latin_name FROM Plant");
$stmt->execute();
$plant_names = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Binary search to check if a two-word phrase is a valid plant species name.
	 Returns name of plant if found, or nothing if not found. Used for hyperlinking
	 within log notes.

	 $spp = two-word string to search for in plant species list
	 $start = starting index for search
	 $end = ending index for search */
function matches_plant($spp, $start, $end) {
	global $plant_names;

	if ($end < $start) return;
	$mid = (int)(($start + $end)/2);
	$mid_item = $plant_names[$mid]['latin_name'];
	if (strcasecmp($spp, $mid_item) > 0) return matches_plant($spp, $mid+1, $end);
	else if (strcasecmp($spp, $mid_item) < 0) return matches_plant($spp, $start, $mid-1);
	else return $mid_item;
}

/* Makes updates to a log entry, if coming here from update_logs.php */
function submit_edits() {
	global $conn;

	$stmt = $conn->prepare("UPDATE Log SET latin_name=:new_name, date=:new_date, notes=:new_notes, stage=:new_stage WHERE latin_name=:name AND date=:date AND stage=:stage");
	$stmt->bindParam(':name', $_GET['on']);
	$stmt->bindParam(':date', $_GET['od']);
	$stmt->bindParam(':stage', $_GET['os']);
	$stmt->bindParam(':new_name', $_POST['name']);
	$stmt->bindParam(':new_date', $_POST['date']);
	$stmt->bindParam(':new_stage', $_POST['stage']);
	$stmt->bindParam(':new_notes', $_POST['notes']);

	$success = ($stmt->execute() && $stmt->rowCount() != 0);
	success_fail_message($success, "Log entry for {$_POST['stage']}  {$_POST['name']}, {$_POST['date']} updated.");
}
?>

<div class="container-fluid">
	<?php if (isset($_GET['on'])) submit_edits(); ?>
	<div class="row justify-content-center">
		<div class="col col-lg-8">
			<h1 class="text-center"><?php echo $header ?> sightings</h1>
			<?php
			$stmt = $conn->prepare("SELECT latin_name, common_name, img_url, date, stage, Log.notes FROM $table ORDER BY date DESC, latin_name ASC");
			$stmt->execute();
			$logs = $stmt->fetchAll();
			$year = $logs[0]['date'];
			echo '<table style="width: 100%">';

			// Print each log and the enclosing tables
			foreach ($logs as $log) {
				// If we've hit a new year, end the previous table and start a new one
				if (substr($log['date'], 0, 4) != $year) {
					$year = substr($log['date'], 0, 4);
					$stmt = $conn->prepare("SELECT COUNT(DISTINCT latin_name) AS num_spp, COUNT(latin_name) as num_logs from $table ".(strpos($table, " WHERE ") === FALSE ? "WHERE" : "AND")." date LIKE '$year%'");
					$stmt->execute();
					$stats = $stmt->fetch(PDO::FETCH_ASSOC);

					echo '</table>'
							 . '<h1 class="text-center"><small>'
							 . $year . ' (' . $stats['num_logs'] . ' log' . ($stats['num_logs'] == 1 ? '' : 's') . ', ' . $stats['num_spp'].' spp)'
							 . '</small></h1>'
							 . '<table style="width: 100%">'
							 . '<th colspan=2>&nbsp;</th><th>Name</th><th>Date</th><th>Notes</th>';
				} ?>
				<tr>
					<!-- Edit button -->
					<td><a href="update_logs.php?do=edit&n=<?php echo $log['latin_name'] ?>&d=<?php echo $log['date'] ?>&s=<?php echo $log['stage'] ?>" class="btn btn-<?php echo $btn_class ?>">
						<i class="fas fa-edit"></i>
					</a></td>
					<!-- Thumbnail -->
					<td><?php thumbnail($log['img_url'], $log['latin_name'], '3rem'); ?></td>
					<!-- Species name -->
					<td style="white-space: nowrap"><?php echo $log['common_name'].'<br/><em>(<a href="view.php?spp='.$log['latin_name'].'">'.$log['latin_name'].'</a>)</em>' ?></td>
					<!-- Date -->
					<td style="white-space: nowrap"><?php echo $log['date'] ?></td>
					<!-- Notes -->
					<td>
						<?php
						/* Adds hyperlinks to species pages within notes field */
						// Split notes into words
						$notes = explode(" ", $log['notes']);
						for ($i = 0; $i < count($notes) - 1; $i++) {
							// Remove leading/trailing punctuation for search
							$genus = ltrim($notes[$i], "()[].,:;!?");
							$spp = rtrim($notes[$i+1], "()[].,:;!?");
							// Search
							$match = matches_plant($genus.' '.$spp, 0, count($plant_names) - 1);
							if ($match) {
								// Determine where to start/end hyperlink (avoids including any punctuation in the hyperlink)
								$startdif = strlen($notes[$i]) - strlen($genus);
								$enddif = strlen($notes[$i+1]) - strlen($spp);
								// Add opening tag
								$notes[$i] = substr($notes[$i], 0, $startdif)."<a href='view_plant.php?spp=$match'>".substr($notes[$i], $startdif);
								// Add closing tag
								$notes[$i+1] = substr($notes[$i+1], 0, strlen($notes[$i+1])-$enddif)."</a>".substr($notes[$i+1], strlen($notes[$i+1])-$enddif, $enddif);
								// Advance index beyond end of species name
								$i++;
							}
						}
						echo implode(" ", $notes); // Recombine into string and print
						?>
					</td>
				</tr>
			<?php } // END OF LOG-PRINTING LOOP ?>
			</table>
			<p>&nbsp;</p>
		</div>
	</div>
</div>
<?php include_once 'footer.html'; ?>
