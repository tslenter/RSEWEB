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

global $url;
global $qe;
global $bu;
global $dash;

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Remote Syslog Elasticsearch</title>
<link rel="stylesheet" href='<?php echo ($url . "/fontawesome/css/all.css"); ?>'>
<link href='<?php echo ($url . "/style.css"); ?>' rel="stylesheet" type="text/css">
<link rel="stylesheet" href="bootstrap.min.css">

<style type="text/css">
.out {
    overflow: scroll;
    scroll-behavior: smooth;
    overflow-y:hidden;
    padding-bottom: 20px;
    font-family: monospace;
    font-size: small;
    white-space: nowrap;
    background: #212729;
    min-height: 1000px;
    color: lightgrey;
}

.nav {
    color:grey;
    background-color:silver;
    font-size: 15px;
}

.navbar-default {
    background-color:silver;
    border-color:darkgray;
    background-image: none;
    background-repeat: no-repeat;
    color:gray;
}

.navbar-default .navbar-brand {
    color:#2e2e2e;
    background-color:silver;
}

.navbar-default .dropdown-menu {
    color:#2e2e2e;
    background-color:silver;
    margin: auto;
}

input[type=text] {
    width: 100%;
    padding: 0px 0px;
    padding-bottom: 0px;
    box-sizing: border-box;
    margin: auto;
}

input[type=buffer] {
    width: 60px;
    padding: 0px 0px;
    padding-bottom: 0px;
    box-sizing: border-box;
    margin: auto;
}

.form
{
    float: right;
    padding-top: 10px;
    padding: 12px 0px;
    padding-bottom: 0px;
    margin: 12 auto;
}

.submit {
    display: inline-block;
    padding: 0px 10px;
    font-size: 15px;
    border-radius: 0;
    color:gray;
    -webkit-appearance: none;
}

.submit {
    background-color: silver;
    /**
     * If the input field has a border,
     * you need it here too to achieve equal heights.
     */
    border: 1px solid transparent;
}

.submit:hover {
    color: #2e2e2e;
}

a:link {
  color: silver;
  background-color: transparent;
  text-decoration: none;
}

a:visited {
  color: silver;
  background-color: transparent;
  text-decoration: none;
}
a:hover {
  color: gainsboro;
  background-color: transparent;
  text-decoration: underline;
}

</style>

<script src="jquery-latest.js"></script>

<?php
//load variables to PHP
     if (isset($_GET['qe'])) {
         $qe = $_GET["qe"];
     }
     if (isset($_GET['bu'])) {
         $bu = $_GET["bu"];
     }
     if (isset($_GET['dash'])) {
         $dash = $_GET["dash"];
     }
?>

<script>
//load variables to JAVA
    var qe = '<?php echo $qe; ?>';
    var bu = '<?php echo $bu; ?>';
    var dash = '<?php echo $dash; ?>';
</script>

<script>
//Adjust scrolling for tailing
    //Last known document height
    documentHeight = 0;
    //Last known scroll position
    scrollPosition = 0;
    //Should we scroll to the bottom?
    scroll = true;
    //This function scrolls to the bottom
    function scrollToBottom() {
        $("html, body").animate({scrollTop: $(document).height()}, "fast");
    }
    function ScrollNeeded() {
    //scoll down when bottom resize window
    $(window).resize(function() {
    if (scroll) {
        scrollToBottom();
    }
    });
    //scroll when bottom and keep display tailing
    $(window).scroll(function() {
        if($(window).scrollTop() + window.innerHeight == $(document).height()) {
           scroll = true;
       } else {
           scroll = false;
       }
    });
    }
</script>

<script>
//load data from php elasticsearch
if (bu == '') {
    var bu = '300';
}

if (bu >= 10000) {
    var bu = '10000';
}

//scrolling data
if (qe != '') {
    $(document).ready(function() {
        $("#responsecommand").load("rseview.php?q="+qe+"&b="+bu);
        var refreshId = setInterval(function() {
        $("#responsecommand").load('rseview.php?q='+qe+'&b='+bu+'&randval='+ Math.random());
        ScrollNeeded();
        if (scroll) {
            scrollToBottom();
        }
        }, 1000);
    $.ajaxSetup({ cache: false });
    });
} else if (dash != '') {
    $(document).ready(function() {
        $("#responsecommand").load("dash.php?dash="+dash);
        var refreshId = setInterval(function() {
        $("#responsecommand").load('dash.php?dash='+dash+'&randval='+ Math.random());
        }, 15000);
    $.ajaxSetup({ cache: false });
    });
} else if (qe == 0) {
    $(document).ready(function() {
        $("#responsecommand").load("rseview.php?a=noquery"+"&b="+bu);
        var refreshId = setInterval(function() {
        $("#responsecommand").load("rseview.php?a=noquery"+"&b="+bu+"&randval="+ Math.random());
        ScrollNeeded();
        if (scroll) {
            scrollToBottom();
        }
        }, 1000);
    $.ajaxSetup({ cache: false });
    });
}
</script>

</head>
<body style="background-color:#212729;">
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand" href=""><img src="logo_black.png" alt="RS Logo" width="45" height="45" style="margin:-20px 0px; margin-right: 20px;" align="middle"</img>Remote Syslog Elasticsearch</a>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li><a href="?dash=x <?php $dash=""; ?>">Dashboard</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Options<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                           <li><a href="?check1=tes <?php if (isset($_GET['check1'])) { if ($_GET['check1'] == "tes") { exec('logger -n 127.0.0.1 -d "This is a UDP test message!"; logger -T -P 514 -n 127.0.0.1 "This is a TCP test message!"'); } } $check1=""; ?>">Test message</a></li>
                           <li><a href="?check2=rml <?php if (isset($_GET['check2'])) { if ($_GET['check2'] == "rml") { exec('curl -XDELETE --header "Content-Type: application/json" http://localhost:9200/rsx-syslog-ng*'); } } $check2=""; ?>">Clear live log archive</a></li>
                           <li><a href="https://github.com/tslenter/RSX-RSC/blob/master/LICENSE" target="_blank">License</a></li>
                        </ul>
                    </li>
                    <li><a href="logout.php">Logout</a></li>
                    <li>
                    <form onclick=="return false" methode="get" class="form" autocomplete="off">
                       <! –– create some space between the menu and the searchbar ––>
                       &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                       <label><input type="text" placeholder="Search.." name="qe" id='txtquery' onkeyup='saveValue(this);'></label>
                       <label><input type="buffer" placeholder="Buffer.." name="bu" id='txtbuffer' onkeyup='saveValue(this);'></label>
                       <button class="submit" type="submit"><i class="fas fa-search"></i>
                       <script type="text/javascript">
                           document.getElementById("txtquery").value = getSavedValue("txtquery"); // set the value to this input
                           document.getElementById("txtbuffer").value = getSavedValue("txtbuffer");  // set the value to this input
                           /* Here you can add more inputs to set value. if it's saved */

                           //Save the value function - save it to localStorage as (ID, VALUE)
                           function saveValue(e) {
                               var id = e.id;  // get the sender's id to save it .
                               var val = e.value; // get the value.
                               localStorage.setItem(id, val); // Every time user writing something, the localStorage's value will override .
                           }

                           //get the saved value function - return the value of "v" from localStorage.
                           function getSavedValue(v) {
                               if (!localStorage.getItem(v)) {
                                   return ""; // You can change this to your default value.
                               }
                           return localStorage.getItem(v);
                           }
                    </script>
                    </form>
                    </li>
                </ul>
              <p class="navbar-text navbar-right" id="current"></p>
            </div>
        </div>
    </div>
    <br></br>
    <div id="responsecommand" class="out"></div>
    </div>
    <div align="center">
        <br></br>
        <?php echo "<font color=\"silver\">Remote Syslog Elasticsearch v0.1 - <a href='https://github.com/tslenter/RSX-RSC/blob/master/README.md' target='_blank'>Donate and help</a></font><br>"; ?>
        <br></br>
    </div>
    <script src="bootstrap.min.js"></script>
</body>
</html>
