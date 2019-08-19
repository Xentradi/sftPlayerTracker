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

$stmt = $conn->prepare("SELECT `timestamp`, `playercount` FROM `counter` WHERE DATE(`timestamp`) = CURDATE() ORDER BY `timestamp`");
$stmt->execute();
$playersToday = $stmt->fetchAll(PDO::FETCH_ASSOC); 
$json = json_encode($playersToday);
//print_r($playersToday);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        table, th, td {
  border: 1px solid black;
}
    </style>
</head>
<body>
<table width="300">
    <tr>
        <td align="center">Timestamp</td>
        <td align="center">Player Count</td>
    </tr>
    <?php

    for ($i=0, $len=count($playersToday); $i < $len; $i++){
        echo '<tr><td align="center">' . $playersToday[$i][timestamp] . '</td><td align="center">' . $playersToday[$i][playercount] . '</td></tr>';
    }

    ?>
</table>


</body>
</html>