<?php
$cur_page = 'plants';
include_once 'header.html';
include_once 'connect.php';
include_once 'funcs_general.php';
?>
<script>
$(document).ready(function(){
  $("#searchHerb").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#herb-list tr").filter(function() { $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1); });
  });
});

$(document).ready(function(){
  $("#searchWood").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#wood-list tr").filter(function() { $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1); });
  });
});
</script>

<a href="new_plant.php" class="btn btn-p" style="margin-top: 15px; position: fixed;"><i class="fas fa-plus"></i> Add species</a>

<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8">
      <h1 class="text-center">Plants</h1>
			<ul class="nav nav-pills justify-content-center" id="pills-tab" role="tablist">
				<li class="nav-item"><a class="nav-link active" id="herb-tab" data-toggle="pill" href="#herb" role="tab" aria-controls="pills-herb" aria-selected="true">Herbaceous</a></li>
				<li class="nav-item"><a class="nav-link" id="woody-tab" data-toggle="pill" href="#woody" role="tab" aria-controls="pills-woody" aria-selected="false">Woody</a></li>
			</ul>
			<div>&nbsp;</div>
			<div class="tab-content" id="pills-tabContent">
				<div id="herb" class="tab-pane fade show active" role="tabpanel" aria-labelledby="pills-herb">
					<input class="form-control" id="searchHerb" type="text" placeholder="Search species">
					<?php table("SELECT img_url, latin_name, common_name, have, want, tags FROM Plant WHERE tags NOT LIKE '%shrub%' AND tags NOT LIKE '%tree%' AND tags NOT LIKE '%vine%' ORDER BY have DESC, latin_name ASC", '', array('tbody_id' => 'wood-list', 'width' => '100%')); ?>
					<p>&nbsp;</p>
				</div>
				<div id="woody" class="tab-pane fade" role="tabpanel" aria-labelledby="pills-woody">
					<input class="form-control" id="searchWood" type="text" placeholder="Search species">
					<?php table("SELECT img_url, latin_name, common_name, have, want, tags FROM Plant WHERE tags LIKE '%shrub%' OR tags LIKE '%tree%' OR tags LIKE '%vine%' ORDER BY have DESC, latin_name ASC", '', array('tbody_id' => 'wood-list', 'width' => '100%')); ?>
					<p>&nbsp;</p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php include_once 'footer.html'; ?>
