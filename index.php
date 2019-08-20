<?php
$servername = 'x2.xentradi.com';
$username = 'sftTesting';
$password = 'Hawa11an!';

try {
    $conn = new PDO("mysql:host=$servername;dbname=sftTesting", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully<br />"; 
    }
catch(PDOException $e)
    {
    //echo "Connection failed: " . $e->getMessage();
    }
if(isset($_GET['date'])){
  $queryDate = $_GET['date'];
} else {
  $queryDate = date("Y-m-d");
}

if(isset($_GET['type'])) {
  $queryType = $_GET['type'];
} else {
  $queryType = 'daily';
}

$sqlDaily = "SELECT `timestamp`, `playercount`,`playerList` FROM `counter` WHERE DATE(`timestamp`) = :date ORDER BY `timestamp`";
$sqlWeekly = "SELECT `timestamp`, `playercount`,`playerList` FROM `counter` WHERE yearweek(`timestamp`) = yearweek(:date) ORDER BY `timestamp`";

if($queryType == 'daily') {
  $stmt = $conn->prepare($sqlDaily);
} else if($queryType == 'weekly') {
  $stmt = $conn->prepare($sqlWeekly);
}


//$stmt = $conn->prepare("SELECT `timestamp`, `playercount` FROM `counter` WHERE DATE(`timestamp`) = :date ORDER BY `timestamp`");
$stmt->bindValue(":date", $queryDate);
$stmt->execute();
$playersToday = $stmt->fetchAll(PDO::FETCH_ASSOC); 
$json = json_encode($playersToday);
function _dateConvert($vDate) {
    $date1 = new DateTime($vDate);
    //$date2 = "Date(Date.UTC(".date_format($date1, 'Y').", ".((int) date_format($date1, 'm') - 1).", ".date_format($date1, 'd').", ".date_format($date1, 'H').", ".date_format($date1, 'i').", ".date_format($date1, 's')."))";
    $date2 = "Date(".date_format($date1, 'Y').", ".((int) date_format($date1, 'm') - 1).", ".date_format($date1, 'd').", ".date_format($date1, 'H').", ".date_format($date1, 'i').", ".date_format($date1, 's').")";
    return $date2;
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
  <select name="type" id="type">
    <option value="daily" <?php if($_GET['type'] == 'daily') {echo 'selected';} ?> >Daily</option>
    <option value="weekly" <?php if($_GET['type'] == 'weekly') {echo 'selected';} ?> >Weekly</option>
    <!-- <option value="yearly">Yearly</option> -->
  </select>
  <input type="submit" value='submit'>
</form>
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
            for ($i=0, $len=count($playersToday); $i < $len; $i++){
                echo '[new ' . _dateConvert($playersToday[$i][timestamp]) . ', ' . $playersToday[$i][playercount] . ']';
                if($i !== $len-1) { echo ', ';}
            }
            ?>
        ]);

        var options = {
          title: 'Players <?php echo $queryDate; ?> (UTC)',
          hAxis: {
            format: 'Y-M-d HH:mm',
            //gridlines: {count: 6}
          },
          vAxis: {
            gridlines: {color: 'none'},
            minValue: 0
          }
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

        chart.draw(data, options);
      }
  

      $(window).resize(function(){
        drawChart();
      });
    </script>
<table width="100%">
    <tr>
        <td align="center">Timestamp (HST)</td>
        <td align="center">Player Count</td>
        <td align="center">Player List</td>
    </tr>
    <?php

    for ($i=0, $len=count($playersToday); $i < $len; $i++){
        echo '<tr><td align="center">' . $playersToday[$i][timestamp] . '</td><td align="center">' . $playersToday[$i][playercount] . '</td><td align="center">' . $playersToday[$i][playerList] . '</td></tr>';
    }

    ?>
</table>
</body>
</html>