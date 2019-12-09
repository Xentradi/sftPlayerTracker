<h3>Records</h3>
<table width="100%" class="table">
<thead>
    <tr>
        <th scope="col">Timestamp</th>
        <th scope="col">Player Count</th>
        <th scope="col">Player List</th>
    </tr>
</thead>
<tbody>
    <?php

    for ($i=0, $len=count($playersToday); $i < $len; $i++) {
        echo '<tr><td >' . convertDateToUTC($playersToday[$i]['timestamp']) . " (" . $playersToday[$i]['timestamp'] . ')'  . '</td><td >' . $playersToday[$i]['playercount'] . '</td><td >' . $playersToday[$i]['playerList'] . '</td></tr>';
    }

    ?>
</tbody>
</table>
