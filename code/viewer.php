<?php
// This file is part of the Direwolf APRS Web Dashboard as available at https://github.com/PC7MM/Direwolf-APRS-Web-Dashboard
// Developed by Michael PC7MM and Richard PD3RFR as an extension of https://github.com/IZ7BOJ/direwolf_webstat and https://github.com/IZ7BOJ/APRS_dashboard as developed by Alfredo IZ7BOJ
// See config.php for adjustable parameters and see https://www.youtube.com/watch?v=7bMf7rWCfnE for more information

include 'initialize.php';

if (file_exists($consolelog)) $handle = fopen($consolelog, 'rb');
if (!isset($_SESSION['offsetfile'])) $_SESSION['offsetfile']=0;

if (isset($_GET['ajax'])) {
	if (!is_file($consolelog)) {
        	echo ('<div class="console"><BR><center>Logfile is empty or does not exist</center></div>');
		exit();
	}

	$readbytes=max((filesize($consolelog)-$maxreadbytes),$_SESSION['offsetfile']);
        $data = htmlspecialchars(strip_tags(stream_get_contents($handle, -1 , $readbytes)));
        if (filesize($consolelog) > $_SESSION['offsetfile'])  {
		$_SESSION['offsetfile'] = filesize($consolelog);
		if (substr($data,-1)=="\n") { $data=substr($data,0,-1); }
		if (str_contains(PHP_OS, 'WIN')) {
			$newdata=str_replace("\n\r","<BR>",$data);
		} else {
			$newdata=str_replace("\n","<BR>",$data);
		}
		if ($display_datestamps==1) {
			$newdata.="<BR><div class=\"fileupdated\">".date('d-m-y h:i:s')." | File updated | New file size: ".filesize($consolelog)." | ";
			$newdata.="Read from file: ".(filesize($consolelog)-$readbytes)." new bytes</div><BR>";
		}
		echo('<div class="console">'.$newdata.'</div');
        }
	if (filesize($consolelog) < $_SESSION['offsetfile']) {
		$newdata="<BR><div class=\"fileshrinked\">".date('d-m-y h:i:s')." | File shrinked | New file size: ".filesize($consolelog);
		echo('<div class="console">'.$newdata.'</div');
	     	$_SESSION['offsetfile'] = 0;
	}
        exit();
} else {
	unset($_SESSION['offsetfile']);
}
include('menu.php');

?>
<div id="page" class="page">
<div class="checkboxline">
<input id="refresh" name="refresh" type="checkbox" checked> Refresh every <B><?php echo $refresh ?> msec</B> | 
Enable <b>automatic scroll down</b> after every refresh <input id="scrolldown" name="scrolldown" type="checkbox" checked>

</div>
<?php
	if (str_contains(PHP_OS, 'WIN') and $_SESSION['showedbufferwarning']!=1) {
		echo '<script>window.alert("Direwolf for Windows might write console output in a 4kB output buffer instead of writing it directly to the console logfile. If such a buffer is being used, information in this Console Viewer will only be updated after Direwolf flushes that buffer.");</script>';
		$_SESSION['showedbufferwarning']=1;
	}
?>
<BR><div class="startedlogging">
<?php
	if (is_file($consolelog) and filesize($consolelog)>0) $consolelogsize=filesize($consolelog); else $consolelogsize="0";
	echo(date('d-m-y h:i:s')." | File: ".$consolelog." | File size: ".$consolelogsize." bytes | Max bytes read from file per update: ".$maxreadbytes);
?>
</div>
<div id="ajaxcontent"></div>
</div>
</body>
</html>
