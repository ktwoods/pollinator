<?php
$cur_page = 'other';
include 'header.html';
include_once 'connect.php';
include_once 'funcs_general.php';
?>
<!-- "Add species" button -->
<a href="new.php?type=other" class="btn btn-o" style="margin-top: 15px; position: fixed;"><i class="fas fa-plus"></i> Add species</a>

<!-- Main container -->
<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8">
      <?php build_tabs('Other creatures', 'Other', 'SELECT DISTINCT type FROM Creature_full WHERE family_name NOT IN (SELECT family_name FROM Bee_full) AND family_name NOT IN (SELECT family_name FROM Lep_full)'); ?>
    </div>
  </div>
</div>
<?php include 'footer.html'; ?>
