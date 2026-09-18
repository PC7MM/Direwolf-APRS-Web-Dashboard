<?php
// This file is part of the Direwolf APRS Web Dashboard as available at https://github.com/PC7MM/Direwolf-APRS-Web-Dashboard
// Developed by Michael PC7MM and Richard PD3RFR as an extension of https://github.com/IZ7BOJ/direwolf_webstat and https://github.com/IZ7BOJ/APRS_dashboard as developed by Alfredo IZ7BOJ
// See config.php for adjustable parameters and see https://www.youtube.com/watch?v=7bMf7rWCfnE for more information

include 'initialize.php';

if (isset($_SESSION['callsign'])) $callsign = $_SESSION['callsign']; else $callsign="";
if (!empty($logfile)) $header = str_getcsv($logfile[0],escape: "\\"); else $header=[];
$framesoninterface=0;

$htmloutput='<th>Date</th><th class="onload-sort order-by-desc">Time(Z)</th>';
for ($c=3; $c < count($header); $c++) { $htmloutput.='<th>'.$header[$c].'</b></th>'; }
$htmloutput.='</tr>';

if (isset($callsign) and ($callsign !== "")) {
	$callsign = strtoupper($callsign);
        $linesinlog = count($logfile); // starts with line 0 as header in logfile
        $counter= 1; // skip header line in logfile

        while($counter < $linesinlog) { 
		$line = $logfile[$counter];
                $parts = str_getcsv($line,",",escape: "\\"); //split all fields
                if (str_contains(strtoupper($parts[8]),$callsign) and ($parts[0]==$_SESSION['if'])) { // str_contains for implicit wildcard search
			$htmloutput.='<tr><td>'.htmlspecialchars(strip_tags(substr($parts[2],0,strpos($parts[2],"T")))).'</td><td>'.htmlspecialchars(strip_tags(substr($parts[2],-strpos($parts[2],"T")+1,-1))).'</td>';
                        for ($c=3; $c < count($parts)-1; $c++) { $htmloutput.='<td>'.htmlspecialchars(strip_tags($parts[$c])).'</td>'; }
			$htmloutput.='<td>'.chunk_split(htmlspecialchars(strip_tags($parts[21])),60,"<BR>").'</td>'; // strip_tags to get rid of possible malicious comments, chunk_split for adding linebreaks in long comments
			$htmloutput.='</tr>';
                        $framesoninterface++;
                }
	        $counter++;
        }
}

if (isset($_GET['ajax'])) {
	if ($fixedlogname=="") echo('<a href="?daysback='.($_SESSION['daysback']+1).'">Earlier</a> | <a href="?daysback=0">Today</a> | <a href="?daysback='.($_SESSION['daysback']-1).'">Later</a> | ');
	echo('<B>'.(max(count($logfile)-1,0)).'</B> frames in logfile: <B>'.$newlogname.'</B> | ');
	echo('<B>'.$framesoninterface.'</B> frames on Radio Interface <B>'.$intdesc[$if].'</B> for callsign containing: "<B>'.$callsign.'</B>" | ');
	echo('Refresh Rate: <B>'.$refresh.' msec.</B><BR><BR>');

  	if(count($logfile)>0) {
		echo('<script src="table-sort.min.js"></script>');
		echo('<table class="normaltable framestable table-sort table-arrows">');
	        echo('<tbody>');
		echo $htmloutput;
	        echo('</tbody>');
		echo('</table>');
	} else {
		echo('Logfile is empty or does not exist');
	}
	exit();
}
include('menu.php');
?>
<div class="page">
<center>
<form action="frames.php" method="get">
<input id="refresh" name="refresh" type="checkbox" checked> Refresh every <B><?php echo $refresh ?> msec</B> | 
Limit view to frames containing: 
<input type="text" name="getcall" size="9" value="<?php echo $callsign ?>"> 
<input type="submit" value="Show"></form><BR>
<div id="ajaxcontent"></div>
</center>
</div>
</body>
</html>
