<?php
// This file is part of the Direwolf APRS Web Dashboard as available at https://github.com/PC7MM/Direwolf-APRS-Web-Dashboard
// Developed by Michael PC7MM and Richard PD3RFR as an extension of https://github.com/IZ7BOJ/direwolf_webstat and https://github.com/IZ7BOJ/APRS_dashboard as developed by Alfredo IZ7BOJ
// See config.php for adjustable parameters and see https://www.youtube.com/watch?v=7bMf7rWCfnE for more information

?>
<body>
<div class="menu">
<center>
<?php if(file_exists($logourl)){ ?>
<img src="<?php echo $logourl ?>" width="100px" height="100px">
<BR><BR>
<?php } ?>
<a href="index.php">Home</a> |
<a href="system.php">System Status</a> | 
<a href="chgif.php">Change Radio Interface</a> | 
<a href="frames.php">View Specified Station</a> | 
<a href="traffic.php">Traffic Monitor</a> | 
<a href="users.php">Users Monitor</a> | 
<a href="igate.php">iGate Monitor</a> | 
<a href="aprsis.php">APRS-IS Server</a> | 
<a href="viewer.php">Console Viewer</a> | 
<a href="about.php">About</a>
<BR>
<BR>
</center>
</div>
