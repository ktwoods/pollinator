<?php
$cur_page = 'bees';
include 'header.php';
include_once 'connect.php';
include_once 'build_table.php';
?>
<a href="new.php?type=bee" class="btn btn-b" style="margin-top: 15px; position: fixed;"><i class="fas fa-plus"></i> Add species</a>
<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8">
			<h1 class="text-center">Bees</h1>
			<ul class="nav nav-pills justify-content-center" id="pills-tab" role="tablist">
				<?php
				global $conn;
				$stmt = $conn->prepare("SELECT family_name, family_desc FROM Family WHERE family_name IN (SELECT DISTINCT family_name FROM Bee_full)");
				$stmt->execute();
				$families = $stmt->fetchAll();

				for ($i = 0; $i < count($families); $i++) {
					$fam = $families[$i][0];
					echo "<li class='nav-item'>";
					echo '<a class="nav-link'.($i == 0 ? ' active' : '' ).'" data-toggle="pill" id="'.$fam.'-tab" href="#'.$fam.'" role="tab" aria-controls="'.$fam.'-tab" aria-selected='.($i == 0 ? '"true"' : '"false"' ).'>'.$fam.'</a>';
					echo '</li>';
				}
				?>
				<li class="nav-item"><a class="nav-link" data-toggle="tab" id="all-tab" href="#all" role="tab" aria-controls="all-tab" aria-selected="false">(All)</a></li>
			</ul>
			<div class="tab-content" id="pills-tabContent">
				<?php
				for ($i = 0; $i < count($families); $i++) {
					$fam = $families[$i][0];
					$desc = $families[$i][1];
					if ($i == 0) echo '<div id="'.$fam.'" class="tab-pane fade show active" role="tabpanel" aria-labelledby="'.$fam.'-tab">';
					else echo '<div id="'.$fam.'" class="tab-pane fade" role="tabpanel" aria-labelledby="'.$fam.'-tab">';
					echo '<h3 class="text-center">'.$fam.' ('.$desc.')'.'</h3>';
					fam_table($fam);
					echo '<p>&nbsp;</p></div>';
				}
				?>
				<div id="all" class="tab-pane fade" role="tabpanel" aria-labelledby="all-tab">
					<?php fam_table('All bees'); ?>
					<p>&nbsp;</p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
function fam_table($family) {
	if ($family == 'All bees') {
		$query = "SELECT latin_name, common_name FROM Bee_full ORDER BY subtype, family_name, latin_name";
		echo "<h3 class='text-center'>All species</h3>";
	}
	else
		$query = "SELECT latin_name, common_name FROM Bee_full WHERE family_name ='$family' ORDER BY latin_name";

	global $conn;
	$stmt = $conn->prepare($query);
	$stmt->execute();
	$num_col = $stmt->columnCount();

	echo "<table style='width: 100%'>";
	# Print header row
	echo "<tr>";
	for ($i = 0; $i < $num_col; $i++) {
		$meta = $stmt->getColumnMeta($i);
		$hname = ucfirst($meta['name']);
		$hname = str_replace("_", " ", $hname);
		echo "<th>" . $hname . "</th>";
	}
	echo "<th>Sightings</th>";
	echo "</tr>";

	# Print data rows
	while ($row = $stmt->fetch()) {
		$name = $row['latin_name'];
		$substmt = $conn->prepare("SELECT COUNT(date) FROM Log WHERE latin_name='$name'");
		$substmt->execute();
		$seen = $substmt->fetch()[0];

		if  ($seen != "0") {
			if (explode(' ', $row['latin_name'])[1] == 'spp') echo "<tr class=\"seen-b-genus\">";
			else echo "<tr class=\"seen-b\">";
		}
		else echo "<tr>";

		for ($i = 0; $i < $num_col; $i++) {
			$meta = $stmt->getColumnMeta($i);
			$dname = $meta['name'];
			$dtype = $meta['native_type'];

			echo "<td>";

			# Make first cell a link
			if ($i == 0) {
				echo "<a href='view.php?spp=".$row['latin_name']."'>";
			}

			# If species name, use italics
			if ($dname == "latin_name") echo "<em>" . $row[$i] . "</em>";

			else echo $row[$i];

			if ($i == 0) echo "</a>";
			echo "</td>";
		}
		echo "<td>$seen</td>";
		echo "</tr>";
	}
	echo "</table>";
}
?>
<?php include 'footer.html'; ?>
