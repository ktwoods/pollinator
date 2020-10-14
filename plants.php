<?php
$cur_page = 'plants';
include_once 'header.html';

$stmt = $conn->prepare("SELECT img_url, latin_name, common_name, have, want, tags FROM Plant WHERE tags NOT LIKE '%shrub%' AND tags NOT LIKE '%tree%' AND tags NOT LIKE '%vine%' ORDER BY have DESC, latin_name ASC");
$stmt->execute();
$herb_plants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT img_url, latin_name, common_name, have, want, tags FROM Plant WHERE tags LIKE '%shrub%' OR tags LIKE '%tree%' OR tags LIKE '%vine%' ORDER BY have DESC, latin_name ASC");
$stmt->execute();
$woody_plants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- "Add species" button -->
<a href="update_plants.php" class="btn btn-p btn-new"><i class="fas fa-plus"></i> Add species</a>

<!-- Main container -->
<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8">
      <h1>Plants</h1>
			<ul class="nav nav-pills justify-content-center" id="pills-tab" role="tablist">
				<li class="nav-item"><a class="nav-link active" id="herb-tab" data-toggle="pill" href="#herb" role="tab" aria-controls="pills-herb" aria-selected="true">Herbaceous</a></li>
				<li class="nav-item"><a class="nav-link" id="woody-tab" data-toggle="pill" href="#woody" role="tab" aria-controls="pills-woody" aria-selected="false">Woody</a></li>
			</ul>
			<div>&nbsp;</div>
			<div class="tab-content" id="pills-tabContent">
				<div id="herb" class="tab-pane fade show active" role="tabpanel" aria-labelledby="pills-herb">
					<input class="form-control" id="searchHerb" type="text" placeholder="Search species">
				</div>
				<div id="woody" class="tab-pane fade" role="tabpanel" aria-labelledby="pills-woody">
					<input class="form-control" id="searchWood" type="text" placeholder="Search species">
				</div>
				<p>&nbsp;</p>
			</div>
		</div>
	</div>
</div>

<script>
	const herbSpecies = <?=json_encode($herb_plants)?>;
	const woodSpecies = <?=json_encode($woody_plants)?>;

	const herbTable = $(table(herbSpecies, 'view_plant.php'));
	herbTable.attr('id', 'herbList');
	herbTable.css('width', '100%');
	$('#herb').append(herbTable);

	const woodTable = $(table(woodSpecies, 'view_plant.php'));
	woodTable.attr('id', 'woodList');
	woodTable.css('width', '100%');
	$('#woody').append(woodTable);

	$(document).ready(function(){
	  $("#searchHerb").on("keyup", function() {
	    var value = $(this).val().toLowerCase();
	    $("#herbList tbody>tr").filter(function() { $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1); });
	  });
	});

	$(document).ready(function(){
	  $("#searchWood").on("keyup", function() {
	    var value = $(this).val().toLowerCase();
	    $("#woodList tbody>tr").filter(function() { $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1); });
	  });
	});
</script>
<?php include_once 'footer.html'; ?>
