<?php 
// This file is part of the Direwolf APRS Web Dashboard as available at https://github.com/PC7MM/Direwolf-APRS-Web-Dashboard
// Developed by Michael PC7MM and Richard PD3RFR as an extension of https://github.com/IZ7BOJ/direwolf_webstat and https://github.com/IZ7BOJ/APRS_dashboard as developed by Alfredo IZ7BOJ
// See config.php for adjustable parameters and see https://www.youtube.com/watch?v=7bMf7rWCfnE for more information

include 'initialize.php';

if(isset($_GET['ajax'])) {
	echo('<script src="table-sort.min.js"></script><BR>');
	echo('<TABLE class="normaltable igatetable table-sort table-arrows">');
	echo('<THEAD><TR><TH>Tracked Station</TH><TH>Last Heard</TH><TH>Status</TH><TH class="onload-sort">Beacon Age</TH><TH>Transmission</A><TH>Unproto Path</TH><TH>SysOp</TH></TR></THEAD><TBODY>');

	$nowtime = time();
	$stations=[];
	$inp_users_arr = explode (",", $users);
	$inp_stationusers_arr = explode (",", $usersquery);
	$arrContextOptions=array( "ssl"=>array("verify_peer"=>false,"verify_peer_name"=>false,),);
	$i=0;

	if (count($inp_stationusers_arr)!==count($inp_users_arr)) {
		echo('<b>Warning: number of Stations and SysOps are not equal, please check contents of $stationquery and $sysop in config.php.</b><BR><BR>');
		die();
	}

	for ($n=0; $n<ceil(count($inp_stationusers_arr)/20);$n++) {
		$inp_stations=implode(",",array_slice($inp_stationusers_arr,20*$n,20*(1+$n)));
		$json_url = "https://api.aprs.fi/api/get?name=".$inp_stations."&what=loc&apikey=".$apikey."&format=json";
		$json = file_get_contents( $json_url, false, stream_context_create($arrContextOptions));
		$json_output = json_decode( $json, true);
		$outp_stations_arr = $json_output[ 'entries' ];

	        if ($json_output["result"]!=="ok") {
			echo('<TR><TD colspan=7><B>Error message from aprs.fi API: '.$json_output['description'].'</b></TD></TR></TBODY></TABLE>');
			exit();
		}

		foreach ( $inp_stationusers_arr as $inp_station ) { // for each station as defined $usersquery in config.php
			foreach ( $outp_stations_arr as $outp_station ) { // for each element of the output of the API of aprs.ri
		                if ($outp_station["name"]==strtoupper($inp_station)) { // if the station from $usersquery equals the current station of the output of the API of aprs.fi
			                $date = new DateTimeImmutable();
			                $date = $date->setTimestamp($outp_station['lasttime']);
			                $date = $date->setTimezone(new DateTimeZone($timezone));

					if ($nowtime-$outp_station['lasttime']>$timeout) {
						$igateclass="notrunning";
						$igatedesc="NOT RUNNING";
					} else {
						$igateclass="running";
						$igatedesc="RUNNING";
					}
					if (strpos($outp_station['path'], 'qAC') !== false) {
						$aprspath="APRS-IS";
					} else {
						$aprspath="RF";
					}
					echo('<TD class="station"><a href="https://aprs.fi/?call='.$outp_station["name"].'" target="_blank">'.$outp_station["name"].'</a></TD>');
					echo('<TD>'.$date->format('H:i:s d-m-Y').'</TD>');
					echo('<TD class="'.$igateclass.'"><B>'.$igatedesc.'</B></TD>');
		                        echo('<TD>'.(secondsToTime($nowtime-$outp_station['lasttime'])).'</TD>');
					echo('<TD class="aprspath">'.$aprspath.'</TD>');
					echo('<TD>'.$outp_station["path"].'</TD>');
					echo('<TD class="station"><a href="https://qrz.com/db/'.$inp_users_arr[$i].'" target="_new">'.$inp_users_arr[$i].'</a></TD></TR>');
				}
			}
			$i++; // increase counter for referring to the right SysOp corresponding to the actual Station
		}
	}
	echo('</TBODY></TABLE>');
	exit();
}
include('menu.php');
?>
<div class="page">
<center>
Status "Not Running" after <b><?php echo ($timeout/60) ?> minutes</b> no beacon received | 
<input id="refresh" name="refresh" type="checkbox"> Refresh every <B><?php echo $refresh ?> msec</B>
<div id="ajaxcontent"></div>
<BR>
<object class="aprsmapobject" data="https://aprs-map.info/?snamelist=<?php echo strtoupper($usersquery) ?>"></object> 
</center>
</div>
</body>
</html>
