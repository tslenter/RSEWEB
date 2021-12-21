<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Remote Syslog Elasticsearch</title>
<?php
/*
License:
"Remote Syslog" is a free application what can be used to view syslog messages.
Copyright (C) 2020 Tom Slenter
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
For more information contact the author:
Name author: Tom Slenter
E-mail: info@remotesyslog.com
*/
?>

<?php
/**
 * Check loggedin
 */
$status = session_status();
if($status == PHP_SESSION_NONE){
    //There is no active session
    session_start();
}

if (isset($_SESSION['id']) && $_SESSION['un'] == true) {
/**
 * Just continue if logged in
 */
} else {
    echo "<center>Please log in first to see this page.</center>";
    echo "<br>";
    echo '<center><a href="/rse">Return to login</a></center>';
    echo '<meta http-equiv="refresh" content="10.01;/rse"/>';
    die();
}
?>

<?php
require_once 'rs_init.php';

$top10host = [
  "size" => 10,
  "body" => [
        "size" => 0,
        "aggs" => [
                  "user_agg" => [
                                "terms" => [
                                           "field" => "HOST_FROM.keyword"
                                           ],
                                "aggs" => [
                                          "sum_agg" => [
                                                       "sum" => [
                                                                "field" => "numeric_field"
                                                                ]
                                                       ]
                                          ]
                                ]
                   ]
         ]
   ];

$top10program = [
  "size" => 10,
  "body" => [
        "size" => 0,
        "aggs" => [
                  "user_agg" => [
                                "terms" => [
                                           "field" => "FACILITY.keyword"
                                           ],
                                "aggs" => [
                                          "sum_agg" => [
                                                       "sum" => [
                                                                "field" => "numeric_field"
                                                                ]
                                                       ]
                                          ]
                                ]
                   ]
         ]
   ];

$top10priority = [
  "size" => 10,
  "body" => [
        "size" => 0,
        "aggs" => [
                  "user_agg" => [
                                "terms" => [
                                           "field" => "PRIORITY.keyword"
                                           ],
                                "aggs" => [
                                          "sum_agg" => [
                                                       "sum" => [
                                                                "field" => "numeric_field"
                                                                ]
                                                       ]
                                          ]
                                ]
                   ]
         ]
   ];


$hosttop10 = $es->search($top10host);
$searchhosttop10 = $hosttop10['aggregations']['user_agg']['buckets'];
$programtop10 = $es->search($top10program);
$searchprogramtop10 = $programtop10['aggregations']['user_agg']['buckets'];
$prioritytop10 = $es->search($top10priority);
$searchprioritytop10 = $prioritytop10['aggregations']['user_agg']['buckets'];

//echo '<pre>', print_r($searchhosttop10), '</pre>';
//echo '<pre>', print_r($searchhosttop10[0]['key']), '</pre>';
//echo '<pre>', print_r($searchhosttop10[10]['doc_count']), '</pre>';

if (sizeof($searchhosttop10) > 10) {
        $count_hosttop10 = 10;
} else {
        $count_hosttop10 = sizeof($searchhosttop10);
}

if (sizeof($searchprogramtop10) > 10) {
        $count_programtop10 = 10;
} else {
        $count_programtop10 = sizeof($searchprogramtop10);
}

if (sizeof($searchprioritytop10) > 10) {
        $count_prioritytop10 = 10;
} else {
        $count_prioritytop10 = sizeof($searchprioritytop10);
}

for ($x = 0; $x <= $count_hosttop10 - 1; $x++) {
        if ($searchhosttop10[$x]['doc_count'] != 0) {
            $doc_counttophost[] = $searchhosttop10[$x]['doc_count'];
            $keytophost[] = $searchhosttop10[$x]['key'];
        } else {
            $x = 10;
        }
}
for ($x = 0; $x <= $count_programtop10 - 1; $x++) {
        if ($searchprogramtop10[$x]['doc_count'] != 0) {
            $doc_counttopprogram[] = $searchprogramtop10[$x]['doc_count'];
            $keytopprogram[] = $searchprogramtop10[$x]['key'];
        } else {
            $x = 10;
        }
}

for ($x = 0; $x <= $count_prioritytop10 - 1; $x++) {
        if ($searchprioritytop10[$x]['doc_count'] != 0) {
            $doc_counttoppriority[] = $searchprioritytop10[$x]['doc_count'];
            $keytoppriority[] = $searchprioritytop10[$x]['key'];
        } else {
            $x = 10;
        }
}

//echo '<pre>', print_r($doc_counttophost), '</pre>';
//echo '<pre>', print_r($keytophost), '</pre>';

//extract node data
$cluster_url = 'http://localhost:9200/_cluster/health';
$cluster_json = file_get_contents($cluster_url);
$cluster_json = json_decode($cluster_json);
$cluster_json = (array)$cluster_json;
//Modify keys
unset($cluster_json['number_of_pending_tasks']);
unset($cluster_json['delayed_unassigned_shards']);
unset($cluster_json['number_of_in_flight_fetch']);
unset($cluster_json['timed_out']);
unset($cluster_json['active_shards_percent_as_number']);
unset($cluster_json['unassigned_shards']);
unset($cluster_json['active_shards_percent_as_number']);
//extract lifecycle
$lifecycle_url = 'http://localhost:9200/_ilm/policy';
$lifecycle_json = file_get_contents($lifecycle_url);
$lifecycle_json = json_decode($lifecycle_json);
$lifecycle_json = (array)$lifecycle_json;
$lifecycle_json = (array)$lifecycle_json['rs-policy'];
$lifecycle_json = (array)$lifecycle_json['policy'];
$lifecycle_json = (array)$lifecycle_json['phases'];
$lifecycle_json = (array)$lifecycle_json['hot'];
$lifecycle_json = (array)$lifecycle_json['actions'];
$lifecycle_json = (array)$lifecycle_json['rollover'];
$lifecycle_day = $lifecycle_json['max_age'];
$lifecycle_ss = $lifecycle_json['max_primary_shard_size'];
?>

<script>
    var keytophost = [<?php echo '"'.implode('","', $keytophost).'"' ?>];
    var doc_counttophost = [<?php echo '"'.implode('","', $doc_counttophost).'"' ?>];
    var keytopprogram = [<?php echo '"'.implode('","', $keytopprogram).'"' ?>];
    var doc_counttopprogram = [<?php echo '"'.implode('","', $doc_counttopprogram).'"' ?>];
    var keytoppriority = [<?php echo '"'.implode('","', $keytoppriority).'"' ?>];
    var doc_counttoppriority = [<?php echo '"'.implode('","', $doc_counttoppriority).'"' ?>];
</script>

<script src="chart.js"></script>

<script>
var ctx = document.getElementById('myChart');
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: keytophost,
        datasets: [{
            label: 'Amount of docs',
            data: doc_counttophost,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        animation: {
           duration: 0
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
});
</script>

<script>
var ctx = document.getElementById('myChart3');
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: keytoppriority,
        datasets: [{
            label: 'Amount of docs',
            data: doc_counttoppriority,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(255, 99, 132, 0.2)',
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        animation: {
           duration: 0
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
});
</script>

<script>
var ctx = document.getElementById('myChart2');
var myChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: keytopprogram,
        datasets: [{
            label: 'Amount of docs',
            data: doc_counttopprogram,
            backgroundColor: ['rgba(75, 192, 192, 1)', '#1F618D', '#F1C40F', '#27AE60', '#884EA0', '#D35400'],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        animation: {
           duration: 0
        }
    }
});
</script>

<style>
.grid-container {
  white-space: pre;
  position: relative;
  top: 0px;
  display: grid;
  grid-template-columns: auto auto;
  padding: 0px;
  text-align: center;
  justify-content: center;
}
.grid-bar {
  width: 600px;
  hight: 600px;
  padding: 10px;
  font-size: 15px;
  text-align: center;
  justify-content: center;
}
.grid-donut {
  width: 300px;
  hight: 300px;
  padding: 10px;
  font-size: 15px;
  text-align: center;
  justify-content: center;
}
.grid-cluster {
  width: 300px;
  hight: 300px;
  padding: 16px;
  font-size: 15px;
  text-align: left;
  justify-content: left;
}
</style>

</head>
<body>
<div class="grid-container">
  <div class="grid-bar"><h3 align="left">Top 10 hosts:</h3><br><canvas id="myChart"></canvas></div>
  <div class="grid-bar"><h3 align="left">Top 10 priority:</h3><br><canvas id="myChart3"></canvas></div>
  <div class="grid-donut"><h3 align="left">Top 10 facility:</h3><br><canvas id="myChart2"></canvas></div>
  <div class="grid-cluster"><h3 align="left">Cluster health:</h3><br>
  <table>
      <br>
      <tbody>
          <tr>
              <td><?php echo("Cluster name:   "); ?></td>
              <td><?php echo($cluster_json['cluster_name']); ?></td>
          </tr>
          <tr>
              <td><?php echo("Status:   "); ?></td>
              <td><?php echo($cluster_json['status']); ?></td>
          </tr>
          <tr>
              <td><?php echo("Number of nodes:   "); ?></td>
              <td><?php echo($cluster_json['number_of_nodes']); ?></td>
          </tr>
          <tr>
              <td><?php echo("Number of data nodes:   "); ?></td>
              <td><?php echo($cluster_json['number_of_data_nodes']); ?></td>
          </tr>
          <tr>
              <td><?php echo("Active primary shards:   "); ?></td>
              <td><?php echo($cluster_json['active_primary_shards']); ?></td>
          </tr>
          <tr>
              <td><?php echo("Active shards:   "); ?></td>
              <td><?php echo($cluster_json['active_shards']); ?></td>
          </tr>
          <tr>
              <td><?php echo("Relocating shards:   "); ?></td>
              <td><?php echo($cluster_json['relocating_shards']); ?></td>
          </tr>
          <tr>
              <td><?php echo("Initializing shards:   "); ?></td>
              <td><?php echo($cluster_json['initializing_shards']); ?></td>
          </tr>
          <tr>
              <td><?php echo("Task waiting in queue:   "); ?></td>
              <td><?php echo($cluster_json['task_max_waiting_in_queue_millis']); ?></td>
          </tr>
          <!-- <tr>
              <td><// ?php echo("Lifecycle max index size:   "); ?></td>
              <td><// ?php echo($lifecycle_ss); ?></td>
         </tr>
         <tr>
              <td><// ?php echo("Lifecycle max index lifetime:   "); ?></td>
              <td><// ?php echo($lifecycle_day); ?></td>
         </tr> -->
      </tbody>
  </table>
  </div>
</div>
</body>
</html>
