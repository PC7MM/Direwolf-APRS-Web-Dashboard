<?php
// This file is part of the Direwolf APRS Web Dashboard as available at https://github.com/PC7MM/Direwolf-APRS-Web-Dashboard
// Developed by Michael PC7MM and Richard PD3RFR as an extension of https://github.com/IZ7BOJ/direwolf_webstat and https://github.com/IZ7BOJ/APRS_dashboard as developed by Alfredo IZ7BOJ
// See config.php for adjustable parameters and see https://www.youtube.com/watch?v=7bMf7rWCfnE for more information

include 'initialize.php';

if (isset($_GET['ajax'])) {
	if (is_file($log)) {
		$handle = fopen($log, 'r');
		if (isset($_SESSION['offsetlog'])) { //this part is executed from 2nd cycle
			$rawdata = stream_get_contents($handle, -1, $_SESSION['offsetlog']);
			if ($rawdata !== "")  { //only if last cycle got something, process new data, otherwise skip to next cycle
				$_SESSION['offsetlog'] += strlen($rawdata);
				$rows=explode("\n", $rawdata, -1); //if more rows are received in the same cycle, divide it. -1 is necessary because last element would be empty
				foreach($rows as $row) {
					showrow($row);
				}
			}
		} else { //only at the beginning, print last rows
			$rows=count($logfile); // starts at 1
			$startrow=max($rows-$startrows+1,2); // starts at 1, skip first header row from logfile
			while ($startrow<=$rows) {
				$row=$logfile[$startrow-1]; // starts at 0
				showrow($row);
				$startrow++;
			} //close while
			fseek($handle, 0, SEEK_END);
			$_SESSION['offsetlog'] = ftell($handle);
		}
	}
	exit();
} else {
	unset($_SESSION['offsetlog']);
}

function showrow($row) {
	global $displayallchannels;
	$fields=str_getcsv($row,",",escape: "\\");
	$channel=htmlspecialchars(strip_tags($fields[0]));
	$timestamp=htmlspecialchars(strip_tags($fields[2]));
    	$source=htmlspecialchars(strip_tags($fields[3]));
	$heard=htmlspecialchars(strip_tags($fields[4]));
	$level=htmlspecialchars(strip_tags($fields[5]));
	$name=htmlspecialchars(strip_tags($fields[8]));
	$lat=htmlspecialchars(strip_tags($fields[10]));
	$long=htmlspecialchars(strip_tags($fields[11]));
	$comment=chunk_split(htmlspecialchars(strip_tags($fields[21]),60,"<BR>"));

	if ($fields[0]==$_SESSION['if'] or $displayallchannels==1) {
		echo('<table class="normaltable traffic"><tr>');
		echo('<td width="50px">'.$channel.'</td><td width="200px">'.$timestamp.'</td><td width="100px">'.$source.'</td><td width="100px">'.$heard.'</td><td width="100px">'.$level.'</td><td width="100px">'.$name.'</td>');
		echo('<td width="100px">'.$lat.'</td><td width="100px">'.$long.'</td><td width="500px">'.$comment.'</td></tr></table>');
    	}
}

include('menu.php');
?>
<div class="page">
  	<center>Traffic Monitor for <?php echo ($displayallchannels==1) ? "<B>All Radio Interfaces" : "Radio Interface <B>".$intdesc[$if]; ?></B> | Logfile: <b><?php echo $newlogname ?></b> | 
        <input id="refresh" name="refresh" type="checkbox" checked> Refresh every <B><?php echo $refresh ?> msec</b></center> <BR>
        <table class="normaltable traffic">
	<thead>
		<tr>
			<th width="50px" >Ch</th>
			<th width="200px">Timestamp</th>
			<th width="100px">Source</th>
			<th width="100px">Heard</th>
			<th width="100px">Level</th>
			<th width="100px">Name</th>
			<th width="100px">Lat</th>
			<th width="100px">Long</th>
			<th width="500px">Comment</th>
		</tr>
	</thead>
	</table>
	<div id="ajaxcontent"></div>
	</body>
</div>
</html>
