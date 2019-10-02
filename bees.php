<?php
$cur_page = 'bees';
include_once 'header.php';
include_once 'connect.php';
include_once 'funcs_general.php';
include_once 'funcs_wildlife_category_pages.php';
?>
<!-- "Add species" button -->
<a href="new.php?type=bee" class="btn btn-b" style="margin-top: 15px; position: fixed;"><i class="fas fa-plus"></i> Add species</a>

<!-- Main container -->
<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8">
			<?php build_tabs('Bees', 'Bee', 'SELECT family_name, family_desc FROM Family WHERE family_name IN (SELECT DISTINCT family_name FROM Bee_full)'); ?>
		</div>
	</div>
</div>
<?php include_once 'footer.html'; ?>
