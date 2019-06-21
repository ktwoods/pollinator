<?php
$cur_page = 'lep_logs';
include 'header.php';
include_once 'connect.php';
include_once 'build_table.php';
?>

<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8">
			<h1 class="text-center">Butterfly & moth sightings</h1>
			<?php
			global $conn;
			$stmt = $conn->prepare('SELECT Log.latin_name, common_name, stage, date, Log.notes FROM Log JOIN Lep_full USING (latin_name) ORDER BY date DESC');
			$stmt->execute();
			$logs = $stmt->fetchAll();
			$year = $logs[0]['date'];
			?>
			<table style="width: 100%">
			<?php
			foreach ($logs as $log) {
				if (substr($log['date'], 0, 4) != $year) :
					$year = substr($log['date'], 0, 4);
					$stmt = $conn->prepare("SELECT COUNT(DISTINCT Log.latin_name) AS num_spp, COUNT(Log.latin_name) as num_logs from Log JOIN Lep_full USING (latin_name) WHERE date LIKE '$year%'");
					$stmt->execute();
					$stats = $stmt->fetch(PDO::FETCH_ASSOC);
					?>
			</table>
			<h1 class="text-center"><small><?php echo $year.' ('.$stats['num_logs'].' logs, '.$stats['num_spp'].' spp)' ?></small></h1>
			<table style="width: 100%">
				<th>&nbsp;</th><th>Name</th><th>Date</th><th>Stage</th><th>Notes</th>
				<?php endif; ?>					
				<tr>
					<td><a href="edit_log.php?name=<?php echo $log['latin_name'] ?>&date=<?php echo $log['date'] ?>&stage=<?php echo $log['stage'] ?>" class="btn btn-l">
						<i class="fas fa-edit"></i>
					</a></td>
					<td style="white-space: nowrap"><?php echo $log['common_name'].'<br/><em>('.$log['latin_name'].')</em>' ?></td>
					<td style="white-space: nowrap"><?php echo $log['date'] ?></td>
					<td><?php echo $log['stage'] ?></td>
					<td><?php echo $log['notes'] ?></td>
				</tr>
				<?php } ?>
			</table>		
			<p>&nbsp;</p>
		</div>
	</div>
</div>

<?php include 'footer.html'; ?>