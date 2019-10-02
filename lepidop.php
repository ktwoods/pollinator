<?php
$cur_page = 'lepidop';
include_once 'header.php';
include_once 'connect.php';
include_once 'funcs_general.php';
include_once 'funcs_wildlife_category_pages.php';
?>
<!-- "Add species" button -->
<a href="new.php?type=lepidop" class="btn btn-l" style="margin-top: 15px; position:fixed;"><i class="fas fa-plus"></i> Add species</a>

<!-- Main container -->
<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8">
			<?php build_tabs('Butterflies', 'Butterfly', 'SELECT family_name, family_desc FROM Family WHERE subtype="Butterfly"'); ?>
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="col col-lg-8">
			<?php build_tabs('Moths', 'Moth', 'SELECT family_name, family_desc FROM Family WHERE subtype="Moth"'); ?>
		</div>
	</div>
</div>
<?php include 'footer.html'; ?>
