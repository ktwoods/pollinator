<?php
$cur_page = 'home';
include 'header.php';
include_once 'connect.php';
include_once 'build_table.php';
echo '<br/><h1 style="text-align: center">Blooming in '.getdate()['month'].':</h1>';
display("SELECT common_name, latin_name, verified, notes FROM Plant NATURAL JOIN Blooms NATURAL JOIN Month WHERE have!=0 AND month_num=month(curdate())");
echo '<h1 style="text-align: center">Recent logs:</h1>';
display("SELECT common_name, latin_name, overall_type, date, stage, Log.notes FROM Log JOIN Creature_full USING (latin_name) ORDER BY date DESC LIMIT 20");
echo '<p>&nbsp;</p>';
include 'footer.html';
?>
