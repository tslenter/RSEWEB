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
global $stop;

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
    transition: width 0.4s ease-in-out;
    background-image: linear-gradient(to right, lightgrey, lightgreen);
}

input[type=buffer] {
    width: 60px;
    padding: 0px 0px;
    padding-bottom: 0px;
    box-sizing: border-box;
    margin: auto;
    transition: width 0.4s ease-in-out;
    background-image: linear-gradient(to right, lightgrey, lightgreen);
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

a.hb:link {color: grey;}
a.hb:visited {color: grey;}
a.hb:hover {color: #2e2e2e; text-decoration: none;}

.hb {
   margin-top: 5px;
}

/* Center the loader */
#loader {
  position: absolute;
  left: 50%;
  top: 50%;
  z-index: 1;
  width: 120px;
  height: 120px;
  margin: -76px 0 0 -76px;
  border: 16px solid lightgrey;
  border-radius: 50%;
  border-top: 16px solid green;
  -webkit-animation: spin 2s linear infinite;
  animation: spin 2s linear infinite;
}

@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Add animation to "page content" */
.animate-bottom {
  position: relative;
  -webkit-animation-name: animatebottom;
  -webkit-animation-duration: 1s;
  animation-name: animatebottom;
  animation-duration: 1s
}

@-webkit-keyframes animatebottom {
  from { bottom:-100px; opacity:0 }
  to { bottom:0px; opacity:1 }
}

@keyframes animatebottom {
  from{ bottom:-100px; opacity:0 }
  to{ bottom:0; opacity:1 }
}

#extra_loader_div {
  display: none;
  text-align: left;
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
         if ($bu >= 3500) {
             $bu = 3500;
         }
     }
     if (isset($_GET['dash'])) {
         $dash = $_GET["dash"];
     }
     if (isset($_GET['stop'])) {
         $stop = $_GET["stop"];
     }
?>

<script>
//load variables to JAVA
    var qe = '<?php echo $qe; ?>';
    var bu = '<?php echo $bu; ?>';
    var dash = '<?php echo $dash; ?>';
    var stop = '<?php echo $stop; ?>';
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

if (bu >= 3500) {
    var bu = '3500';
}

//scrolling data
if (qe != '') {
    if (stop == 'y') {
        $(document).ready(function() {
            $("#responsecommand").load("rseview.php?q="+qe+"&b="+bu);
            var refreshId = setInterval(function() {
            ScrollNeeded();
            if (scroll) {
                scrollToBottom();
            }
            }, 3000);
        $.ajaxSetup({ cache: false });
        });
    } else if (stop == '') {
        $(document).ready(function() {
            $("#responsecommand").load("rseview.php?q="+qe+"&b="+bu);
            var refreshId = setInterval(function() {
            $("#responsecommand").load('rseview.php?q='+qe+'&b='+bu+'&randval='+ Math.random());
            ScrollNeeded();
            if (scroll) {
                scrollToBottom();
            }
            }, 3000);
        $.ajaxSetup({ cache: false });
        });
    }
} else if (dash == 'x') {
    $(document).ready(function() {
        $("#responsecommand").load("dash.php?dash="+dash);
        var refreshId = setInterval(function() {
        $("#responsecommand").load('dash.php?dash='+dash+'&randval='+ Math.random());
        }, 15000);
    $.ajaxSetup({ cache: false });
    });
} else if (qe == 0) {
    if (stop == 'y') {
        $(document).ready(function() {
            $("#responsecommand").load("rseview.php?a=noquery"+"&b="+bu);
            var refreshId = setInterval(function() {
            ScrollNeeded();
            if (scroll) {
                scrollToBottom();
            }
            }, 3000);
        $.ajaxSetup({ cache: false });
        });
    } else if (stop == '') {
        $(document).ready(function() {
            $("#responsecommand").load("rseview.php?a=noquery"+"&b="+bu);
            var refreshId = setInterval(function() {
            $("#responsecommand").load("rseview.php?a=noquery"+"&b="+bu+"&randval="+ Math.random());
            ScrollNeeded();
            if (scroll) {
                scrollToBottom();
            }
            }, 3000);
        $.ajaxSetup({ cache: false });
        });
    }
}
</script>

</head>
<body style="background-color:#212729;" onload="myLoader()" style="margin:0;">
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
                       <label><input type="text" placeholder="Search.." name="qe" id='txtquery' onkeyup='saveValue(this);' pattern="[A-Za-z0-9_.-;:= ]{0,30}"></label>
                       <label><input type="buffer" placeholder="Buffer.." name="bu" id='txtbuffer' onkeyup='saveValue(this);' pattern='[0-9]{0,4}'></label>
                       <button class="submit" type="submit"><i class="fas fa-search"></i>
                       <button class="submit" name="stop" value="y" type="submit"><i class="fas fa-stop"></i>
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
                    <li>
                    <form class="form">
                    &nbsp;
                    <a class="hb fas fa-redo" href="?qe=&bu="></a>
                    &nbsp;
                    <a class="hb fas fa-download" href="#" id="downloadLink"></a>
                    </form>
                    </li>
                </ul>
              <p class="navbar-text navbar-right" id="current"></p>
            </div>
        </div>
    </div>
    <br></br>
    <div id="loader"></div>
    <div style="display:none;" id="extra_loader_div" class="animate-bottom"></div>
    <div id="responsecommand" class="out"></div>
    </div>
    <div align="center">
        <br></br>
        <?php echo "<font color=\"silver\">Remote Syslog Elasticsearch v0.1 - <a href='https://github.com/tslenter/RS/blob/main/README.md' target='_blank'>Donate and help</a></font><br>"; ?>
        <br></br>
    </div>
    <script src="bootstrap.min.js"></script>
    <script>
    var extra_loader_div;

    function myLoader() {
        extra_loader_div = setTimeout(showPage, 4000);
    }

    function showPage() {
       document.getElementById("loader").style.display = "none";
       document.getElementById("extra_loader_div").style.display = "block";
    }
    function downloadInnerHtml(filename, elId, mimeType) {
       var elHtml = document.getElementById(elId).innerHTML;
       var link = document.createElement('a');
       mimeType = mimeType || 'text/html';

       link.setAttribute('download', filename);
       link.setAttribute('href', 'data:' + mimeType  +  ';charset=utf-8,' + encodeURIComponent(elHtml));
       link.click();
    }

    var fileName =  'export.html';

    $('#downloadLink').click(function(){
    downloadInnerHtml(fileName, 'responsecommand','text/html');
    });
    </script>
</body>
</html>
