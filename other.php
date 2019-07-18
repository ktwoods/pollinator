<?php
$cur_page = 'other';
include 'header.php';
include_once 'connect.php';
include_once 'build_table.php';
?>
<a href="new.php?type=other" class="btn btn-o" style="margin-top: 15px; position: fixed;"><i class="fas fa-plus"></i> Add species</a>
<h1 class="text-center">Other creatures</h1><p>&nbsp;</p>
<?php
display("SELECT img_url, latin_name, common_name, type, subtype FROM Creature_full NATURAL JOIN Family WHERE family_name NOT IN (SELECT family_name FROM Bee_full) AND family_name NOT IN (SELECT family_name FROM Lep_full)");
echo "<p>&nbsp;</p>";

include 'footer.html';
?>
