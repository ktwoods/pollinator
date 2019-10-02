<?php
$cur_page = 'plants';
include_once 'header.html';
include_once 'connect.php';
include_once 'funcs_general.php';

global $conn;
if (isset($_POST['spp'])) $name = $_POST['spp'];
else $name = $_GET['spp'];

include_once 'view_plant_edited.php'; // Handles submitting edits, if returning from edit_plant.php

/* */
function print_specialists($query) {
	global $name, $conn;
  $stmt = $conn->prepare($query);
  $stmt->bindValue(1, $name);
  $stmt->execute();

  $specialists = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo '<ul>';
  foreach ($specialists as $spp) {
    echo '<li>' . $spp['common_name'] . ' (<em><a href="view.php?spp=' . $spp['latin_name'] . '">' . $spp['latin_name'] . '</a></em>)' . ($spp['specialization'] ? ' &mdash; ' . $spp['specialization'] : '') . '</li>';
  }
  if (!$specialists) echo 'n/a';
	echo '</ul>';
}

$stmt = $conn->prepare("SELECT * FROM Plant WHERE latin_name = ?");
$stmt->bindValue(1, $name);
$stmt->execute();
$main_data = $stmt->fetch(PDO::FETCH_ASSOC);
$name = $main_data['latin_name'];

$stmt = $conn->prepare("SELECT * FROM Log WHERE notes LIKE CONCAT('%',?,'%')");
$stmt->bindValue(1, $name);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT creature_name AS latin_name, common_name, stage, Feeds.notes, family_name, type, subtype FROM Feeds JOIN Creature_full ON creature_name=Creature_full.latin_name WHERE plant_name=? ORDER BY subtype, type, family_name, latin_name");
$stmt->bindValue(1, $name);
$stmt->execute();
$full_spp_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$logs_by_spp;
foreach ($full_spp_list as $creature) {
	// $rel_logs stores the subset of logs that are associated with that plant
	foreach($logs as $l) {
		if ($l['latin_name'] == $creature['latin_name']) {
			$rel_logs[] = $l;
		}
	}
	if (isset($rel_logs)) $logs_by_spp[$creature['latin_name']] = $rel_logs;
	unset($l, $rel_logs);
}
unset($creature);
?>

<div class="container-fluid">
	<!-- Main profile -->
	<div class="row">
		<a href="edit_plant.php?name=<?php echo $name ?>" class="btn btn-p btn-edit"><i class="fas fa-edit"></i></a>
		<!-- Image -->
		<div class="col-sm-4">
			<?php if ($main_data['img_url']) {
				echo '<a href="' . $main_data['img_url'] . '"><img src="' . $main_data['img_url'] . '" class="img-fluid center-block" style="max-height: 100%"></a>';
			} ?>
		</div>
		<div class="col-sm-8">
			<div style="margin-left: .5em">
				<!-- Page header (common name, Latin name, family name) -->
				<h1><?php echo $main_data['common_name']; ?><br/>
				<small><em><?php echo $main_data['latin_name'] ?></em>
				<br/><?php echo $main_data['family'] ?></small></h1>

				<!-- Have/want badges -->
				<div>
					<?php
					if ($main_data['have'] == 0) echo '<span class="spp-tag tag-no">have &mdash;</span>';
					else echo '<span class="spp-tag">have &#x2713;</span>';
					if ($main_data['want'] == 0) echo '<span class="spp-tag tag-no">want &mdash;</span>';
					else echo '<span class="spp-tag">want &#x2713;</span>';
					?>
				</div>

				<!-- Profile -->
				<div class="row">
					<!-- Column 1: tags, blooms, bloom length, fruits -->
					<div class="col-sm-6">
						<table class="spp spp-p">
							<tr><th>Tags</th><td><?php echo $main_data['tags'] ?></td></tr>
							<tr>
								<th>Blooms</th><td><?php display_months('Blooms', $name); ?></td>
							</tr>
							<tr>
								<th>Bloom length</th>
								<td><?php echo ($main_data['bloom_length'] == '' ? 'unknown' : $main_data['bloom_length']) ?></td>
							</tr>
							<tr><th>Fruits</th><td><?php display_months('Fruits', $name); ?></td>
							<tr><td colspan="2" style="background-color: white">&nbsp;</td></tr>
						</table>
					</div>
					<!-- Column 2: characteristics, notes -->
					<div class="col-sm-6">
						<table class="spp spp-p" style="width: 100%">
							<tr><th colspan="2">Characteristics</th></tr>
							<tr><td colspan="2" id="p-list"><?php display_list($main_data['research_notes']); ?></td></tr>
							<tr><th colspan="2">Notes</th></tr>
							<tr><td colspan="2" id="p-list"><?php display_list($main_data['observations']); ?></td></tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Plant interaction tables -->
	<div class="row">
		<div class="col-sm-4">
			<!-- Logbook -->
			<?php logbook('SELECT latin_name, date, stage, notes FROM Log WHERE notes LIKE CONCAT("%",?,"%") ORDER BY date DESC', $name, count($logs), 'p'); ?>
		</div>
		<div class="col-sm-8">
			<!-- Specialist butterflies, moths, and bees -->
			<table class="spp spp-p" style="table-layout: auto;">
				<tr><th colspan="2" style="font-size: 1.5em">Specialist insects</th></tr>
				<tr>
					<th>Host plant for</th>
					<td id="p-list">
						<?php
						print_specialists("SELECT latin_name, common_name FROM Lep_full JOIN Feeds ON (creature_name=latin_name) WHERE plant_name=? AND stage='larva' ORDER BY family_name, latin_name");
						?>
					</td>
				</tr>
				<tr>
					<th>Specialist bees</th>
					<td id="p-list">
						<?php
						$plant_genus = explode(' ', $main_data['latin_name'])[0];
						print_specialists("SELECT latin_name, common_name, specialization FROM Bee_full WHERE specialization LIKE '%{$plant_genus}%' OR specialization LIKE '%{$main_data['family']}%' ORDER BY subtype, family_name, latin_name");
						?>
					</td>
				</tr>
			</table>

			<!-- All visitors, organized by type -->
			<table class="spp spp-p"><tr><th style="font-size: 1.5em">All visitors</th></tr></table>
			<!-- Tabs -->
			<ul class="nav nav-pills nav-p justify-content-center" id="type-tabs" role="tablist">
				<?php
				$tabs_content[' '] = '';
				for ($i = 0; $i < count($full_spp_list); $i++) {
					$spp = $full_spp_list[$i];
					$type = $spp['subtype'];

					// If this is a new type, construct a new tab and add opening/ending table tags to the relevant tabs in $tab_content
					if ($i == 0 || $full_spp_list[$i-1]['subtype'] != $full_spp_list[$i]['subtype']) {
						$url = str_replace(' ', '-', $type);
						$url = str_replace('(', '', $url);
						$url = str_replace(')', '', $url);
						echo '<li class="nav-item"><a class="nav-link'.($i == 0 ? ' active' : '').'" id="'.$url.'-tab" href="#'.$url.'" data-toggle="pill" role="tab" aria-controls="'.$url.'" aria-selected="'.($i == 0 ? 'true' : 'false').'">'.$type.'</a></li>';

						if ($i != 0) $tabs_content[$full_spp_list[$i-1]['subtype']] .= '</table>';

						$tabs_content[$type] = '<table class="spp spp-p" style="width: auto"><tr>';
						$tabs_content[$type] .= '<th>Logs</th><th>Species</th><th>Family</th><th>Stage</th><th>Notes</th>';
						$tabs_content[$type] .= '</tr>';
					}

					// Append row to tab content
					$tabs_content[$type] .= '<tr>';

					$tabs_content[$type] .= '<td style="text-align: center">';
					if (isset($logs_by_spp) && array_key_exists($spp['latin_name'], $logs_by_spp)) {
						$tabs_content[$type] .= '<a href="#" data-toggle="popover" data-trigger="hover" data-html="true" data-placement="top" data-content="';
						$tabs_content[$type] .= '<table class=&quot;spp spp-p&quot;><tr><th>Date</th><th>Notes</th></tr>';
						foreach ($logs_by_spp[$spp['latin_name']] as $log) {
							$tabs_content[$type] .= '<tr>';
							$tabs_content[$type] .= '<td>'.$log['date'].'</td>';
							$tabs_content[$type] .= '<td>'.$log['notes'].'</td>';
							$tabs_content[$type] .= '</tr>';
						}
						$tabs_content[$type] .= '</table>"><span class="badge badge-dark">'.count($logs_by_spp[$spp['latin_name']]).'</span></a></td>';
					}
					else $tabs_content[$type] .= '<span class="badge badge-light">0</span></td>';

					$tabs_content[$type] .= '<td>'.$spp['common_name'].'<br/>(<em><a href="view.php?spp='.$spp['latin_name'].'">'.$spp['latin_name'].'</a></em>)</td>';
					$tabs_content[$type] .= '<td>'.$spp['family_name'].'</td>';
					$tabs_content[$type] .= '<td>'.ucfirst($spp['stage']).'</td>';
					$tabs_content[$type] .= '<td>'.$spp['notes'].'</td>';

					$tabs_content[$type] .= '</tr>';

					if ($i == (count($full_spp_list) - 1)) $tabs_content[$type] .= '</table>';
				}
				?>
			</ul>
			<!-- Tab contents -->
			<div class="tab-content" id="type-tabsContent">
				<?php
				foreach ($tabs_content as $type => $tab) {
					$url = str_replace(' ', '-', $type);
					$url = str_replace('(', '', $url);
					$url = str_replace(')', '', $url);

					if (count($full_spp_list) != 0 && $type == $full_spp_list[0]['subtype']) echo '<div class="tab-pane fade show active" id="'.$url.'" role="tabpanel" aria-labelledby="'.$url.'-tab">';
					else echo '<div class="tab-pane fade" id="'.$url.'" role="tabpanel" aria-labelledby="'.$url.'-tab">';
					echo $tab;
					echo '</div>';
				}
				?>
			</div>
		</div>
	</div>
</div>
<?php delete_button($name, 'p') ?>
<?php include_once 'footer.html'; ?>
