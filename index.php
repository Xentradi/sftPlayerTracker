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
$sqlMonthly = "SELECT UNIX_TIMESTAMP(`timestamp`) as 'timestamp', `playercount`,`playerList` FROM `" . $dbTableName_counter . "` WHERE `serverID` = :serverID AND month(`timestamp`) = month(:date) ORDER BY `timestamp`";

if ($queryType == 'daily') {
    $stmt = $conn->prepare($sqlDaily);
} elseif ($queryType == 'weekly') {
    $stmt = $conn->prepare($sqlWeekly);
} elseif ($queryType == 'monthly') {
  $stmt = $conn->prepare($sqlMonthly);
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
    <style>
      .chart {
        width: 100%; 
        min-height: 450px;
      }
      .row {
        margin:0 !important;
      }
    </style>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>
<body>


<form action="" method="GET">
  <input type="date" id="date" name="date" value="<?php echo $queryDate; ?>">
  <select name="serverID" id="serverID">
      <?php
        for($i = 0; $i < $SERVER_COUNT; $i++) {
      ?>
          <option value="<?php echo $SERVERS[$i]['id'] ?>" <?php if($serverID == $SERVERS[$i]['id']) echo "selected" ?> > <?php echo $SERVERS[$i]['name']; ?></option>
      <?php
        }
      ?>
  </select>
  <select name="type" id="type">
    <option value="daily" <?php if ($queryType == 'daily') {echo 'selected';} ?> >Daily</option>
    <option value="weekly" <?php if ($queryType == 'weekly') {echo 'selected';} ?> >Weekly</option>
    <option value="monthly" <?php if ($queryType == 'monthly') {echo 'selected';} ?> >Monthly</option>
    <!-- <option value="yearly">Yearly</option> -->
  </select>
  <input type="submit" value='submit'>
</form>
<h3>All dates & times are in UTC.</h3>
<div id="chart_div" class="chart"></div>
   
<script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {

        var data = new google.visualization.DataTable();
        data.addColumn('datetime', 'Time of Day');
        data.addColumn('number', 'Player Count');

        data.addRows([
            <?php
            for ($i=0, $len=count($playersToday); $i < $len; $i++) {
                echo '[new ' . _dateConvert($playersToday[$i][timestamp]) . ', ' . $playersToday[$i][playercount] . ']';
                if ($i !== $len-1) {
                    echo ', ';
                }
            }
            ?>
        ]);

        var options = {
          title: 'Players <?php echo $queryDate; ?>',
          hAxis: {
            //format: 'M-d HH:mm',
            gridlines: {
              count: -1,
              units: {
                days: {format: ['MMM dd']},
                hours: {format: ['HH:mm', 'ha']},
              }
            },
            minorGridlines: {
            units: {
              hours: {format: ['hh:mm:ss a', 'ha']},
              minutes: {format: ['HH:mm a Z', ':mm']}
            }
          }
          },
          vAxis: {
            gridlines: {color: 'none'},
            minValue: 0
          }
        };

        var formatter = new google.visualization.DateFormat({pattern: 'yyyy-MM-dd HH:mm:ss', timeZone: +0});
        //formatter.format(data, 0);

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
  

      $(window).resize(function(){
        drawChart();
      });
      console.log(new <?php echo _dateConvert(1566327602); ?>);
    </script>
<?php
if($showRecordTable == true) {
?>

<table width="100%">
    <tr>
        <td align="center">Timestamp</td>
        <td align="center">Player Count</td>
        <td align="center">Player List</td>
    </tr>
    <?php

    for ($i=0, $len=count($playersToday); $i < $len; $i++) {
        echo '<tr><td align="center">' . convertDateToUTC($playersToday[$i][timestamp]) . " (" . $playersToday[$i][timestamp] . ')'  . '</td><td align="center">' . $playersToday[$i][playercount] . '</td><td align="center">' . $playersToday[$i][playerList] . '</td></tr>';
    }

    ?>
</table>

<?php } ?>


</body>
</html>