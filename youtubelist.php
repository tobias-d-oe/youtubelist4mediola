
<?php
// please define how to access the youtubelist
$baseurl="http://localhost.localdomain/youtubelist.php";

$favouriteradio = array("Musik1", "Musik2", Musik3", "Musik4", "Musik5", "Musik6");
$favouritevideo = array("Video1", "Video2", "Video3", "Video4", "Video5", "Video6");
?>

<html>
<head>
<title>VIDEOS</title>
<style>
table {
  border-spacing: 0px;
  margin:0px;
  padding:0px;
}

<?php
if (isset($_GET['vid'])) {
    $switch=$_GET['vid'];
}
if (isset($_GET['list'])) {
    $list=$_GET['list'];
}


if (isset($_GET['q'])) {
    $q=$_GET['q'];
}

function sentenceTrim($string, $maxLength = 300) {
    $string = preg_replace('/\s+/', ' ', trim($string)); // Replace new lines (optional)

    if (mb_strlen($string) >= $maxLength) {
        $string = mb_substr($string, 0, $maxLength);

        $puncs  = array('. ', '! ', '? '); // Possible endings of sentence
        $maxPos = 0;

        foreach ($puncs as $punc) {
            $pos = mb_strrpos($string, $punc);

            if ($pos && $pos > $maxPos) {
                $maxPos = $pos;
            }
        }

        if ($maxPos) {
            return mb_substr($string, 0, $maxPos + 1);
        }

        return rtrim($string) . '&hellip;';
    } else {
        return $string;
    }           
}
?>


tbody tr[scope=row]:nth-child(odd) td { 
  background-color: #eee; 
  color: #000;
  width: 300px; 
}
tbody tr[scope=row]:nth-child(even) td { 
  background-color: #aaa; 
  color: #000;
  width: 300px; 
}

tbody tr[scope=current]:nth-child(odd) td { 
  background-color: #d28b8b; 
  color: #000;
  width: 300px; 
}
tbody tr[scope=current]:nth-child(even) td { 
  background-color: #d28b8b; 
  color: #000;
  width: 300px; 
}




tr[scope=row] { 
  color: #c32e04;
  text-align: right; 
  width: 300px; 
}

td {
  padding: 0px;
  font-size: 15px;
}


html { 
  font-size: 10px; /* font-size 1em = 10px bei normaler Browser-Einstellung */ 
  margin:0px;
  padding:0px;
  scroll-behavior: smooth; 
} 

body {
  overflow-x: hidden;
}

progress {
	display:inline-block;
	width:190px;
	height:11px;
	padding:0px 0 0 0;
	margin:0;
	background:none;
	border: 0;
	border-radius: 15px;
	text-align: left;
	position:relative;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 0.8em;
}
progress::-webkit-progress-bar {
	height:11px;
	width:150px;
	margin:0 auto;
	background-color: #CCC;
	border-radius: 15px;
	box-shadow:0px 0px 6px #777 inset;
}

progress::-webkit-progress-value {
	display:inline-block;
	float:left;
	height:11px;
	margin:0px -10px 0 0;
	background: #F70;
	border-radius: 15px;
	box-shadow:0px 0px 6px #777 inset;
}

a {
  color: black;
  text-decoration: none; /* no underline */
}
</style>


</head>




<?php

//$WantedChannelgroup=$_GET['group'];

$ip = $_GET['ip'];
$url = 'http://'.$ip.'/jsonrpc';

if (isset($switch)) {
  $currentvid = $switch;
}




// switch channel if necessary
if (isset($_GET['vid'])) {
    $switch=$_GET['vid'];
    print "<body onload=\"document.getElementById('".$switch."').scrollIntoView();\">";
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => '{"jsonrpc":"2.0","id":1,"method":"Player.Open","params":{"item": {"file":"'.$_GET['vid'].'"}}}'
        )
    );
    
    print_r($options);
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
} else {
    print "<body>";
}

?>






<?php

if (isset($_GET['q'])) {
        
        print ("<table id=main width=295px border=0>");
        
        // Get the list of channels in the channelgroup
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => '{"jsonrpc": "2.0", "method": "Files.GetDirectory", "params": { "properties" : ["title", "plot", "genre", "rating", "art", "file", "runtime"], "directory":"plugin://plugin.video.youtube/search/?q='.$q.'&order=shuffle" }, "id": "1"}'
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) { /* Handle error */ }
        
        $movies = json_decode($result)->{'result'}->{'files'};
        
        
        // loop through channels
        foreach ($movies as $video) {
        
          if ( $video->{'filetype'} == "file" ) {
          $label=$video->{'label'};
          $plot=$video->{'plot'};
          $plot=preg_replace('/\[.*\]/i','',$plot);
          $regex = "@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@";
          $plot = preg_replace($regex, '', $plot);
          $plot = filter_var($plot, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        //  $plots=str_split($plot,220)[0];
          $plots=sentenceTrim($plot,220);
          $thumbnail=$video->{'art'}->{'thumb'};
          $thumbnail=str_replace('image://','',$thumbnail);
          $thumbnail=rtrim($thumbnail,'/');
          $thumbnail=urldecode($thumbnail);
          $file=$video->{'file'};
          $runtime=round(intval($video->{'runtime'})/60);
          $runtime=$runtime." min";
          $genre=implode(", ", $video->{'genre'});
          print ("
           <tr scope=\"row\" border=0>
            <td border=0>
                <a href=\"".$baseurl."?ip=".$ip."&q=".$q."&vid=".$file."\"> 
                <table border=0 width=100% >
                  <tr id=\"".$file."\" style=\"height:100px;\">
                    <td rowspan=3 style=\"width:70px;font-size:10px\"><center>");
          if (strlen($thumbnail) >= 6) {
            print ("<img border=0 src=\"".$thumbnail."\" width=\"50px\" heigh=\"50px\">");
          } else {
            print ("<img border=0 src=\"./logos/vid-unknown.png\" width=\"50px\" heigh=\"50px\">");
          }
          print ("<br><font style=\"font-size:10px \">".$runtime."</font></center></td>
                    <td style=\"width:350px;height:50px;font-size:12px;vertical-align:top\"><b>".$label."</b><br><br>".$plots."</font></td>
                    <!--<td style=\"width:5px;font-size:10px\">-</td>            
                    <td style=\"width:30px;font-size:10px\">".$file."</td> -->
                  </tr>
                </table>
                </a>
            </td>
          </tr>");
          }
        }
        print ("</table>");
} elseif (isset($_GET['fav'])) {
        print ("<table id=main width=295px border=0>");
        if (isset($_GET['list'])) {
           $favourites = $favouriteradio;
        } else {
           $favourites = $favouritevideo;
        }
        foreach ($favourites as $fav) {
            print ("
               <tr scope=\"row\" border=0>
                <td border=0>
                    <a href=\"".$baseurl."?ip=".$ip."&q=".$fav."\"> 
                    <table border=0 width=100% >
                      <tr id=\"".$file."\" style=\"height:100px;\">
                        <td rowspan=3 style=\"width:70px;font-size:10px\"><center>");
            print ("<img border=0 src=\"./logos/fav.png\" width=\"50px\" heigh=\"50px\">");
            print ("</center></td>
                        <td style=\"width:350px;height:10px;font-size:12px;vertical-align:middle\"><b>".$fav."</b></font></td>
                      </tr>
                    </table>
                    </a>
                </td>
              </tr>");
    
        }



        print ("</table>");
} else {
        print ("<table id=main width=295px border=0>");
        print ("   <tr border=0>");
        print ("    <center>");
        print ("    <td border=0>");
        print ("    <center>");
        print ("    <br>");
        print ("    <img width=100px src=\"./logos/youtube.png\">");
        print ("    <br>");
        print ("    <br>");

        print ("
        <font style=\"color:white \"><br/><br/>
        <form action=\"youtubelist.php\">
          <input style=\"font-size: 20px\" type=\"text\" id=\"q\" name=\"q\" value=\"Doku\"><br>
          <input type=\"hidden\" id=\"ip\" name=\"ip\" value=\"$ip\"><br><br>
          <input type=\"image\" name=\"action\" src=\"./logos/suche.png\">
        </form> 
        </font>");
        if (isset($_GET['list'])) {
        print ("<a href=\"youtubelist.php?fav=1&ip=" . $_GET['ip'] . "&list=1\">");
        } else {
        print ("<a href=\"youtubelist.php?fav=1&ip=" . $_GET['ip'] . "\">");
        }
        print("<img border=0 style=\"vertical-align: middle\" src=\"./logos/fav.png\" width=\"50px\" heigh=\"50px\"><b>Favouriten</b></a>
        ");
        
        print ("    </center>");
        print ("    </td>");
        print ("  </tr>");

        print ("</table>");
}
?>
    

</body>
<html>
