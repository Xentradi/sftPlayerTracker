<h3>Monthly Averages</h3>
<table width="100%" class="table">
<thead>
    <tr>
        <th scope="col">Month</th>
        <th scope="col">Average Player Count</th>
        <th scope="col">Peak Player Count</th>
    </tr>
</thead>
<tbody>
    <?php

    for ($i=0, $len=count($playersToday); $i < $len; $i++) {
        echo '<tr><td >' . $playersToday[$i]['timestamp'] . '</td><td >' . $playersToday[$i]['averagePlayerCount'] . '</td><td >' . $playersToday[$i]['maxPlayerCount'] . '</td></tr>';
    }

    ?>
</tbody>
</table>
