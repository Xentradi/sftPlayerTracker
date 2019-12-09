   
<script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {

        var data = new google.visualization.DataTable();
        
		<?php 
        if ($queryType == 'yearly' || $queryType == 'monthly') {
			echo "data.addColumn('string', 'Month');\n";
			echo "data.addColumn('number', 'Average Player Count');\n";
			echo "data.addColumn('number', 'Peak Player Count');\n";
        } else {
			echo "data.addColumn('datetime', 'Time of Day');\n";
			echo "data.addColumn('number', 'Player Count');\n";
		}
		?>

        data.addRows([
            <?php
            for ($i=0, $len=count($playersToday); $i < $len; $i++) {
				if ($queryType == 'yearly' || $queryType == 'monthly') {
					echo '["' . $playersToday[$i]['timestamp'] . '", ' . $playersToday[$i]['averagePlayerCount'] . ', ' . $playersToday[$i]['maxPlayerCount'] . ']';
				} else {
					echo '[new ' . _dateConvert($playersToday[$i]['timestamp']) . ', ' . $playersToday[$i]['playercount'] . ']';
				}
                
                if ($i !== $len-1) {
                    echo ', ';
                }
            }
            ?>
        ]);

        var options = {
          title: 'Players <?php echo $queryDate; ?>',
          backgroundColor: '#68a845',
          hAxis: {
            //format: 'M-d HH:mm',
            gridlines: {
              count: <?php echo count($playersToday); ?>,
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
