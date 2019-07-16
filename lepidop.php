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
				#Generate family names for pills
				global $conn;
				$stmt = $conn->prepare("SELECT family_name, family_desc FROM Family WHERE subtype='Butterfly'");
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
					build_family_table($fam, 'Butterfly');
					echo '<p>&nbsp;</p></div>';
				}
				?>
				<div id="all-b" class="tab-pane fade" role="tabpanel" aria-labelledby="all-b-tab">
					<?php build_family_table('All', 'Butterfly'); ?>
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
				$stmt = $conn->prepare("SELECT family_name, family_desc FROM Family WHERE subtype='Moth'");
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
					build_family_table($fam, 'Moth');
					echo '<p>&nbsp;</p></div>';
				}
				?>
				<div id="all-m" class="tab-pane fade" role="tabpanel" aria-labelledby="all-m-tab">
					<h3 class="text-center">All species</h3>
					<?php build_family_table('All', 'Moth'); ?>
					<p>&nbsp;</p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php include 'footer.html'; ?>
