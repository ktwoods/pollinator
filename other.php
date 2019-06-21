<?php
$cur_page = 'other';
include 'header.php';
include_once 'connect.php';
include_once 'build_table.php';
?>
<a href="new.php?type=other" class="btn btn-o" style="margin-top: 15px; position: fixed;"><i class="fas fa-plus"></i> Add species</a>
<h1 class="text-center">Other creatures</h1><p>&nbsp;</p>
<?php
display("SELECT latin_name, common_name, overall_type AS type FROM Creature NATURAL JOIN Family WHERE overall_type NOT IN ('Butterfly', 'Moth') AND overall_type NOT LIKE '%bee%' ORDER BY overall_type, family_name, latin_name");
echo "<p>&nbsp;</p>";

include 'footer.html';
?>