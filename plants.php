<?php
$cur_page = 'plants';
include 'header.php';
include_once 'connect.php';
include_once 'build_table.php';
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
		<div class="col col-lg-10">
			<p>&nbsp;</p>
			<ul class="nav nav-pills justify-content-center" id="pills-tab" role="tablist">
				<li class="nav-item"><a class="nav-link active" id="herb-tab" data-toggle="pill" href="#herb" role="tab" aria-controls="pills-herb" aria-selected="true">Herbaceous</a></li>
				<li class="nav-item"><a class="nav-link" id="woody-tab" data-toggle="pill" href="#woody" role="tab" aria-controls="pills-woody" aria-selected="false">Woody</a></li>
			</ul>
			<div class="tab-content" id="pills-tabContent">
				<div id="herb" class="tab-pane fade show active" role="tabpanel" aria-labelledby="pills-herb">
					<div class="col col-lg-4 col-med-6 col-sm-8"><input class="form-control" id="searchHerb" type="text" placeholder="Search species"></div>
					<table>
						<thead>
							<th>Common name</th><th>Latin name</th><th>Have</th><th>Want</th><th>Tags</th>
						</thead>
						<tbody id="herb-list">
							<?php build_rows("select common_name, latin_name, have, want, tags from Plant where tags not like '%shrub%' and tags not like '%tree%' and tags not like '%vine%' order by have desc, latin_name asc"); ?>
						</tbody>
					</table>
					<p>&nbsp;</p>
				</div>
				<div id="woody" class="tab-pane fade" role="tabpanel" aria-labelledby="pills-woody">
					<div class="col col-lg-4 col-med-6 col-sm-8"><input class="form-control" id="searchWood" type="text" placeholder="Search species"></div>
					<table>
						<thead>
							<tr><th>Common name</th><th>Latin name</th><th>Have</th><th>Want</th><th>Tags</th></tr>
						</thead>
						<tbody id="wood-list">
							<?php build_rows("select common_name, latin_name, have, want, tags from Plant where tags like '%shrub%' or tags like '%tree%' or tags like '%vine%' order by have desc, latin_name asc"); ?>
						</tbody>
					</table>
					<p>&nbsp;</p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php include 'footer.html'; ?>
