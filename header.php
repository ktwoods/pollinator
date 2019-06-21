<!DOCTYPE html>
<html lang="en">
<head>
  <title>Pollinator Planner</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Bootstrap -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
	<!-- Font Awesome icons -->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/solid.css" integrity="sha384-+0VIRx+yz1WBcCTXBkVQYIBVNEFH1eP6Zknm16roZCyeNg2maWEpk/l/KsyFKs7G" crossorigin="anonymous">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/fontawesome.css" integrity="sha384-jLuaxTTBR42U2qJ/pm4JRouHkEDHkVqH0T1nyQXn1mZ7Snycpf6Rl25VBNthU4z0" crossorigin="anonymous">
	<!-- personal stylesheet mods -->
	<link rel="stylesheet" type="text/css" href="ppstyle.css">
</head>

<body>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
			<a class="navbar-brand" href="home.php">Pollinator Planner</a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarFull" aria-controls="navbarFull" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			
			<div class="collapse navbar-collapse" id="navbarFull">
				<ul class="navbar-nav">
					<li class="nav-item <?php if ($cur_page == 'home') echo 'active' ?>"><a class="nav-link" href="home.php">Home</a></li>
					
					<li class="nav-item <?php if ($cur_page == 'plants') echo 'active' ?>"><a class="nav-link" href="plants.php">Plants</a></li>
					
					<li class="nav-item dropdown <?php if ($cur_page == 'lepidop' || $cur_page == 'bees' || $cur_page == 'other') echo 'active' ?>">
						<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownWildlife" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Wildlife</a>
						<div class="dropdown-menu" aria-labelledby="navbarDropdownWildlife">
							<a class="dropdown-item <?php if ($cur_page == 'lepidop') echo 'active' ?>" href="lepidop.php">Butterflies & moths</a>
							<a class="dropdown-item <?php if ($cur_page == 'bees') echo 'active' ?>" href="bees.php">Bees</a>
							<a class="dropdown-item <?php if ($cur_page == 'other') echo 'active' ?>" href="other.php">Other</a>
						</div>
					</li>
					
					<li class="nav-item dropdown <?php if ($cur_page == 'lep_logs' || $cur_page == 'bee_logs' || $cur_page == 'new_log' || $cur_page == 'edit_log') echo 'active' ?>"> <!-- main menu item -->
						<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownLog" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Wildlife log</a>
						<div class="dropdown-menu" aria-labelledby="navbarDropdownLog">
							<a class="dropdown-item" href="lep_logs.php">Butterflies & moths</a>
							<a class="dropdown-item" href="bee_logs.php">Bees</a>
							<a class="dropdown-item" href="new_log.php">New entry</a>
						</div>
					</li>
				</ul>
			</div>
	</nav>
	<div class="container-fluid">
