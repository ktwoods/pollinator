<?php
$cur_page = 'query';
include_once 'header.html';
include_once 'funcs_general.php';

if (isset($_POST['query']))
{
	$query = $_POST['query'];

	$query = str_replace('\n', '', $query);
	$query = str_replace('\r', '', $query);
	$words = explode(" ", $query);
	$query_type = ltrim(strtolower($words[0]), '(');

	$stmt = $conn->prepare($query);
	$stmt->execute();
	$rows_affected = $stmt->rowCount();
	$result;
	if ($query_type == "select" || $query_type == "describe" || $query_type == "show") {
		// Query will return a table, so just fetch it
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	else if ($query_type == "update" || $query_type == "insert" || $query_type == "delete" || $query_type == "alter") {
		// Query will not return a table result; if the query was successful, fetch the affected table
		if ($rows_affected) {
			if ($query_type == "update") $followup_query = "SELECT * FROM " . $words[1];
			else $followup_query = "SELECT * FROM " . $words[2];

			$stmt = $conn->prepare($followup_query);
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
	}
	else {
		// something something error
	}
}
?>

<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col col-lg-8">
			<div class="text-center">
				<h1>Custom query</h1>
				<p>&nbsp;</p>
				<form action="query.php" method="post">
					<div class="form-group">
						<label for="query" style="margin-top: 1em">MySQL:</label>
						<textarea class="form-control" id="query" name="query" style="width: 400px; margin-left: auto; margin-right: auto"></textarea>
					</div>
					<br/>
					<button type="Submit" class="btn">Submit</button>
				</form>
				<p>&nbsp;</p>
				<div class="text-center" id="queryResult">
					<code id="query"></code>
					<p>&nbsp;</p>
					<p id="queryChanges"></p>
				</div>
			</div>
			<p>&nbsp;</p>
		</div>
	</div>
</div>

<script>
	const query = <?=json_encode($query)?>;
	const madeChanges = <?=json_encode($followup_query)?>;

	if (query) {
		const result = <?=json_encode($result)?>;
		const rowsAffected = <?=json_encode($rows_affected)?>;

		$('#query').text(query);
		if (madeChanges) {
			if (rowsAffected) $('#queryChanges').html(`<strong>Success:</strong> ${rowsAffected} row${rowsAffected > 1 ? 's' : ''} affected.`);
			else $('#queryChanges').text('0 rows affected.');
		}
		const resultTable = table(result);
		resultTable.css('width', 'auto');
		$('#queryResult').append(resultTable);
	}
</script>

<?php include_once 'footer.html'; ?>
