<?php
$cur_page = 'lepidop';
include 'header.php';
include_once 'connect.php';
include_once 'build_table.php';
?>
<a href="new.php?type=lepidop" class="btn btn-l" style="margin-top: 15px; position:fixed;"><i class="fas fa-plus"></i> Add species</a>
<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8">
			<h1 class="text-center">Butterflies</h1>
			<ul class="nav nav-pills justify-content-center" id="pills-tab" role="tablist">
				<?php
				global $conn;
				$stmt = $conn->prepare("SELECT family_name, family_desc FROM Family WHERE overall_type='Butterfly'");
				$stmt->execute();
				$families = $stmt->fetchAll();
				
				for ($i = 0; $i < count($families); $i++) {
					$fam = $families[$i][0];
					echo "<li class='nav-item'>";
					echo '<a class="nav-link'.($i == 0 ? ' active' : '' ).'" data-toggle="pill" id="'.$fam.'-tab" href="#'.$fam.'" role="tab" aria-controls="'.$fam.'-tab" aria-selected='.($i == 0 ? '"true"' : '"false"' ).'>'.$fam.'</a>';
					echo '</li>';
				}
				?>
				<li class="nav-item"><a class="nav-link" data-toggle="tab" id="all-b-tab" href="#all-b" role="tab" aria-controls="all-b-tab" aria-selected="false">(All)</a></li>
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
				<div id="all-b" class="tab-pane fade" role="tabpanel" aria-labelledby="all-b-tab">
					<?php fam_table('All butterflies'); ?>
					<p>&nbsp;</p>
				</div>
			</div>
		</div>
	</div>
		<div class="row justify-content-center">
		<div class="col col-lg-8">
			<h1 class="text-center">Moths</h1>
			<ul class="nav nav-pills justify-content-center" id="pills-tab" role="tablist">
				<?php
				global $conn;
				$stmt = $conn->prepare("SELECT family_name, family_desc FROM Family WHERE overall_type='Moth'");
				$stmt->execute();
				$families = $stmt->fetchAll();
				
				for ($i = 0; $i < count($families); $i++) {
					$fam = $families[$i][0];
					echo "<li class='nav-item'>";
					echo '<a class="nav-link'.($i == 0 ? ' active' : '' ).'" data-toggle="pill" id="'.$fam.'-tab" href="#'.$fam.'" role="tab" aria-controls="'.$fam.'-tab" aria-selected='.($i == 0 ? '"true"' : '"false"' ).'>'.$fam.'</a>';
					echo '</li>';
				}
				?>
				<li class="nav-item"><a class="nav-link" data-toggle="tab" id="all-m-tab" href="#all-m" role="tab" aria-controls="all-m-tab" aria-selected="false">(All)</a></li>
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
				<div id="all-m" class="tab-pane fade" role="tabpanel" aria-labelledby="all-m-tab">
					<?php fam_table('All moths'); ?>
					<p>&nbsp;</p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
function fam_table($family) {
	if ($family == 'All butterflies') {
		$query = "SELECT latin_name, common_name, family_name FROM Lep_full WHERE overall_type='Butterfly' ORDER BY family_name, latin_name";
		echo "<h3 class='text-center'>All butterflies</h3>";
	}
	else if ($family == 'All moths') {
		$query = "SELECT latin_name, common_name, family_name FROM Lep_full WHERE overall_type='Moth' ORDER BY family_name, latin_name";
		echo "<h3 class='text-center'>All moths</h3>";
	}
	else
		$query = "SELECT latin_name, common_name FROM Lep_full WHERE family_name='$family' ORDER BY latin_name";
	
	global $conn;
	$stmt = $conn->prepare($query);
	$stmt->execute();
	
	$num_col = $stmt->columnCount();
	
	echo "<table style='width: 80%'>";
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
		$substmt = $conn->prepare("SELECT COUNT(DISTINCT date) FROM Log WHERE latin_name='$name'");
		$substmt->execute();
		$seen = $substmt->fetch()[0];
		
		if  ($seen != "0") {
			if (explode(' ', $row['latin_name'])[1] == 'spp') echo "<tr class=\"seen-l-fam\">";
			else echo "<tr class=\"seen-l\">";
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