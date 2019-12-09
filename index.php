<?php
require __DIR__ . '/config.php';


try {
    $conn = new PDO("mysql:host=$dbHostname;dbname=sftTesting", $dbUsername, $dbPassword);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully<br />";
} catch (PDOException $e) {
    //echo "Connection failed: " . $e->getMessage();
}

/**
  * Retreive the list of servers from the database
 */
$stmt = $conn->prepare("SELECT * FROM `" . $dbTableName_serverList . "`;");
$stmt->execute();
$SERVERS = $stmt->fetchAll(PDO::FETCH_ASSOC);
$SERVER_COUNT = count($SERVERS);


if (isset($_GET['date'])) {
    $queryDate = $_GET['date'];
} else {
    //echo date("Y-m-d H:i");
    $queryDate = date("Y-m-d");
}

if (isset($_GET['serverID'])) {
    $serverID = $_GET['serverID'];
} else {
    $serverID = 1;
}

if (isset($_GET['type'])) {
    $queryType = $_GET['type'];
} else {
    $queryType = 'daily';
}

$sqlDaily = "SELECT UNIX_TIMESTAMP(`timestamp`) as 'timestamp', `playercount`,`playerList` FROM `" . $dbTableName_counter . "` WHERE `serverID` = :serverID AND DATE(`timestamp`) = :date ORDER BY `timestamp`";
$sqlWeekly = "SELECT UNIX_TIMESTAMP(`timestamp`) as 'timestamp', `playercount`,`playerList` FROM `" . $dbTableName_counter . "` WHERE `serverID` = :serverID AND yearweek(`timestamp`) = yearweek(:date) ORDER BY `timestamp`";
//$sqlMonthly = "SELECT UNIX_TIMESTAMP(`timestamp`) as 'timestamp', `playercount`,`playerList` FROM `" . $dbTableName_counter . "` WHERE `serverID` = :serverID AND month(`timestamp`) = month(:date) ORDER BY `timestamp`";
$sqlMonthly = "SELECT DAY(`timestamp`) as 'timestamp', ROUND(AVG(DISTINCT `playercount`),0) as 'averagePlayerCount', MAX(`playercount`) as 'maxPlayerCount' FROM `" . $dbTableName_counter . "` WHERE `serverID` = :serverID AND month(`timestamp`) = month(:date) GROUP BY MONTH(`timestamp`), DAY(`timestamp`)  ORDER BY `timestamp`";

$sqlYearly = "SELECT MONTH(`timestamp`) as 'timestamp', ROUND(AVG(DISTINCT `playercount`),0) as 'averagePlayerCount', MAX(`playercount`) as 'maxPlayerCount' FROM `" . $dbTableName_counter . "` WHERE `serverID` = :serverID AND YEAR(`timestamp`) = YEAR(:date) GROUP BY YEAR(`timestamp`), MONTH(`timestamp`) ORDER BY MONTH(`timestamp`)";

if ($queryType == 'daily') {
    $stmt = $conn->prepare($sqlDaily);
} elseif ($queryType == 'weekly') {
    $stmt = $conn->prepare($sqlWeekly);
} elseif ($queryType == 'monthly') {
  $stmt = $conn->prepare($sqlMonthly);
} elseif ($queryType == 'yearly') {
  $stmt = $conn->prepare($sqlYearly);
}

$stmt->bindValue(":serverID", $serverID);
$stmt->bindValue(":date", $queryDate);
$stmt->execute();
$playersToday = $stmt->fetchAll(PDO::FETCH_ASSOC);
$json = json_encode($playersToday);

//print_r($playersToday);
function _dateConvert($vDate)
{
    $date1 = new DateTime('@' . $vDate);
    $date2 = "Date(Date.UTC(".date_format($date1, 'Y').", ".((int) date_format($date1, 'm') - 1).", ".((int)date_format($date1, 'd') - 1).", ".date_format($date1, 'H').", ".date_format($date1, 'i').", ".date_format($date1, 's')."))";
    $date2 = "Date(".date_format($date1, 'Y').", ".((int) date_format($date1, 'm') - 1).", ".((int)date_format($date1, 'd') - 0).", ".date_format($date1, 'H').", ".date_format($date1, 'i').", ".date_format($date1, 's').")";
    return $date2;
}


function convertDateToUTC($vDate)
{
    //$theDate = strtotime($vDate);
    //echo $theDate;
    return gmdate("Y-M-d H:i:s", $vDate);
    //return strtotime($theDate);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>SFT Player Count <?php echo $queryDate; ?></title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
		integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

	<style>
	body {
		font-family: verdana, Geneva, sans-serif;
	}

	.chart {
		width: 100%;
		min-height: 450px;
	}

	.row {
		margin: 0 !important;
	}

	.logo {
		float: left;
		margin-top: 43px
	}

	#sky {
		width: 100%;
		height: 265px;
		position: absolute;
		left: 0;
		top: 0;
		z-index: -1;
		background: url(//www.superfuntime.org/lib/images/skybg.png) repeat-x
	}

	#sky {
		width: 100%;
		height: 265px;
		position: absolute;
		left: 0;
		top: 0;
		z-index: -1;
		background: url(//www.superfuntime.org/lib/images/skybg.png) repeat-x
	}

	#cloudbig {
		height: 265px;
		background: url(//www.superfuntime.org/lib/images/cloudbig.png) repeat-x scroll left top
	}

	#cloudsmall {
		height: 265px;
		background: url(//www.superfuntime.org/lib/images/cloudsmall.png) repeat-x scroll left top;
		margin-top: -265px
	}

	#logoholder {
		width: 473px;
		height: 192px;
		margin-top: 43px;
		position: absolute
	}

	#container {
		width: 1024px;
		min-height: 800px;
		margin: -265px auto 0
	}

	#menu {
		height: 30px;
		background-color: #000;
		margin: -265px auto 0;
		-webkit-border-bottom-right-radius: 5px;
		-webkit-border-bottom-left-radius: 5px;
		-moz-border-radius-bottomright: 5px;
		-moz-border-radius-bottomleft: 5px;
		border-bottom-right-radius: 5px;
		border-bottom-left-radius: 5px
	}

	#header {
		height: 235px;
		background: url(//www.superfuntime.org/lib/images/headerbg.png) no-repeat bottom;
		margin-bottom: 12px;
		margin-top: 33px;
	}
	</style>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
	<div id="sky"></div>
	<div id="cloudbig"></div>
	<div id="cloudsmall"></div>
	<div id="mountainleft"></div>

	<div class="container" id="container">
		<div id="header">
			<div id="logoholder">
				<div id="logo">
					<a href="/">
						<img src="//www.superfuntime.org/lib/images/logoSFT.png" alt="logo sft" id="logo_sft" />
					</a>
				</div>
			</div>
		</div>
		<form action="" method="GET" class="form-inline">
				<input type="date" id="date" name="date" value="<?php echo $queryDate; ?>" class="form-control">
				<select name="serverID" id="serverID" class="form-control">
					<?php
                for($i = 0; $i < $SERVER_COUNT; $i++) {
              ?>
					<option value="<?php echo $SERVERS[$i]['id'] ?>"
						<?php if($serverID == $SERVERS[$i]['id']) echo "selected" ?>>
						<?php echo $SERVERS[$i]['name']; ?>
					</option>
					<?php
                }
              ?>
				</select>
				<select name="type" id="type" class="form-control">
					<option value="daily" <?php if ($queryType == 'daily') {echo 'selected';} ?>>Daily</option>
					<option value="weekly" <?php if ($queryType == 'weekly') {echo 'selected';} ?>>Weekly</option>
					<option value="monthly" <?php if ($queryType == 'monthly') {echo 'selected';} ?>>Monthly</option>
					<option value="yearly" <?php if ($queryType == 'yearly') {echo 'selected';} ?>>Yearly</option>
				</select>
				<input type="submit" value='Filter' class="btn btn-primary">
		</form>
    <br />
		<div id="chart_div" class="chart"></div>
    <hr />
		<?php
      include 'src/script.php';
      if ($queryType == 'yearly' || $queryType == 'monthly') {
          include 'src/YearlyTable.php';
      } elseif($showRecordTable == true) {
        include 'src/RecordTable.php';
      }
    ?>
	</div>
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
		integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous">
	</script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
		integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
	</script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
		integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous">
	</script>
</body>

</html>