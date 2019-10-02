<?php
if (isset($_POST['type'])) $type = $_POST['type'];
else $type = $_GET['type'];

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

include 'header.html';
include_once 'connect.php';
include_once 'funcs_general.php';
global $conn;

# For hyperlinking within log notes:
# Binary search to check if a two-word phrase is a valid species name
function matches_plant($plants, $spp, $start, $end) {
	if ($end < $start) return;
	$mid = (int)(($start + $end)/2);
	$mid_item = $plants[$mid]['latin_name'];
	if (strcasecmp($spp, $mid_item) > 0) return matches_plant($plants, $spp, $mid+1, $end);
	else if (strcasecmp($spp, $mid_item) < 0) return matches_plant($plants, $spp, $start, $mid-1);
	else {
		return $mid_item;
	}
}
?>

<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8">
			<h1 class="text-center"><?php echo $header ?> sightings</h1>
			<?php
			$stmt = $conn->prepare("SELECT latin_name, common_name, img_url, date, Log.notes FROM $table ORDER BY date DESC, latin_name ASC");
			$stmt->execute();
			$logs = $stmt->fetchAll();
			$year = $logs[0]['date'];
			?>
			<table style="width: 100%">
			<?php
			# Get array of plant species names
			$stmt = $conn->prepare("SELECT latin_name FROM Plant");
			$stmt->execute();
			$plant_names = $stmt->fetchAll(PDO::FETCH_ASSOC);

			# Print each log and the enclosing tables
			foreach ($logs as $log) {
				# If we've hit a new year, end the previous table and start a new one
				if (substr($log['date'], 0, 4) != $year) :
					$year = substr($log['date'], 0, 4);
					$stmt = $conn->prepare("SELECT COUNT(DISTINCT latin_name) AS num_spp, COUNT(latin_name) as num_logs from $table ".(strpos($table, " WHERE ") === FALSE ? "WHERE" : "AND")." date LIKE '$year%'");
					$stmt->execute();
					$stats = $stmt->fetch(PDO::FETCH_ASSOC);
					?>
			</table>
			<h1 class="text-center"><small><?php echo $year.' ('.$stats['num_logs'].' log'.($stats['num_logs'] == 1 ? '' : 's').', '.$stats['num_spp'].' spp)' ?></small></h1>
			<table style="width: 100%">
				<th colspan=2>&nbsp;</th><th>Name</th><th>Date</th><th>Notes</th>
			<?php endif; # END OF IF STATEMENT ?>
				<tr>
					<!-- Edit button -->
					<td><a href="edit_log.php?name=<?php echo $log['latin_name'] ?>&date=<?php echo $log['date'] ?>&stage=<?php echo $log['stage'] ?>" class="btn btn-<?php echo $btn_class ?>">
						<i class="fas fa-edit"></i>
					</a></td>
					<!-- Thumbnail -->
					<td><?php thumbnail($log['img_url'], $log['latin_name'], "3rem"); ?></td>
					<!-- Species name -->
					<td style="white-space: nowrap"><?php echo $log['common_name'].'<br/><em>(<a href="view.php?spp='.$log['latin_name'].'">'.$log['latin_name'].'</a>)</em>' ?></td>
					<!-- Date -->
					<td style="white-space: nowrap"><?php echo $log['date'] ?></td>
					<!-- Notes -->
					<td>
						<?php
						# Adds hyperlinks to species pages within notes field
						$notes = explode(" ", $log['notes']);
						for ($i = 0; $i < count($notes) - 1; $i++) {
							# Remove leading/trailing punctuation for search
							$genus = ltrim($notes[$i], "()[].,:;!?");
							$spp = rtrim($notes[$i+1], "()[].,:;!?");
							# Search
							$match = matches_plant($plant_names, $genus.' '.$spp, 0, count($plant_names) - 1);
							if ($match) {
								# Determine where to start/end hyperlink
								$startdif = strlen($notes[$i]) - strlen($genus);
								$enddif = strlen($notes[$i+1]) - strlen($spp);
								# Add opening tag
								$notes[$i] = substr($notes[$i], 0, $startdif)."<a href='view_plant.php?spp=$match'>".substr($notes[$i], $startdif);
								# Add closing tag
								$notes[$i+1] = substr($notes[$i+1], 0, strlen($notes[$i+1])-$enddif)."</a>".substr($notes[$i+1], strlen($notes[$i+1])-$enddif, $enddif);
								# Advance index beyond end of species name
								$i++;
							}
						}
						echo implode(" ", $notes); # Recombine into string
						?>
					</td>
				</tr>
			<?php } # END OF LOG-PRINTING LOOP ?>
			</table>
			<p>&nbsp;</p>
		</div>
	</div>
</div>

<?php include 'footer.html'; ?>
