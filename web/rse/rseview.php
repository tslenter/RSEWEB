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

$status = session_status();
if($status == PHP_SESSION_NONE){
    session_start();
}

if (isset($_SESSION['id']) && $_SESSION['un'] == true) {
} else {
    echo "<center>Please log in first to see this page.</center>";
    echo "<br>";
    echo '<center><a href="/rse">Return to login</a></center>';
    echo '<meta http-equiv="refresh" content="10.01;/rse"/>';
    die();
}

require_once 'rs_init.php';

if(isset($_GET['q'])) {
        $q = $_GET['q'];
	$b = $_GET['b'];
	if ($b > 3500) {
        	$b = 3500;
	}
        $query = $es->search([
		'size'=> $b,
		'index' => 'rsx-syslog*',
                'body'=>[
                        'query' => [
                                'bool' => [
                                        'should' => [
						'query_string' => [ 'query' => $q, 'fields'=> ['DATE', 'HOST_FROM', 'MESSAGE', 'LEGACY_MSGHDR']]
                                                ]
                                        ]
                                ],
			'sort' => [
				'R_ISODATE' => [
					'order' => 'desc'
					],
                                '_id' => [
                                        'order' => 'desc'
                                        ]
				]
			]
        ]);
}

if(isset($_GET['a'])) {
        $b = $_GET['b'];
        if ($b > 3500) {
                $b = 3500;
        }
        $query = $es->search([
                'size'=> $b,
		'index' => 'rsx-syslog*',
                'body'=>[
                        'query' => [
                                'wildcard' => [
					'MESSAGE' => '*'
					]
                                ],
                        'sort' => [
                                'R_ISODATE' => [
                                        'order' => 'desc'
                                        ],
                                '_id' => [
                                        'order' => 'desc'
                                        ]
                                ]
                        ]
        ]);
}

if($query['hits']['total']['value'] >=1) {
	$results = $query['hits']['hits'];
	$total_results = $query['hits']['total']['value'];
}

if (empty($total_results)) {
        echo "<br>";
        echo htmlspecialchars('No results ...', ENT_QUOTES, 'UTF-8');
        echo "<br>";
        die();
}

if ($total_results >= $b) {
	$total_results = $b;
}

#Debug results array
#        echo '<pre>', print_r($query), '</pre>';
#	 echo '<pre>', print_r($query['hits']['hits']['0']['_source']['MESSAGE']), '</pre>';
echo "<br>";
for ($output_num = $total_results - 1; ; $output_num -= 1){
	if ($output_num < 0){
		break;
	}
	$output_date = $results[$output_num]['_source']['DATE'];
	$output_host = $results[$output_num]['_source']['HOST_FROM'];
	$output_message = $results[$output_num]['_source']['MESSAGE'];
	$output_msghdr = $results[$output_num]['_source']['LEGACY_MSGHDR'];
	$final_output = ("  " . $output_date . " || " . $output_host . " - " . $output_msghdr . "" . $output_message);
	echo htmlspecialchars($final_output, ENT_QUOTES, 'UTF-8');
	echo "<br>";
}
die();
?>
