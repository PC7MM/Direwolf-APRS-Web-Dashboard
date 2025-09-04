<?php 
// This file is part of the Direwolf APRS Web Dashboard as available at https://github.com/PC7MM/Direwolf-APRS-Web-Dashboard
// Developed by Michael PC7MM and Richard PD3RFR as an extension of https://github.com/IZ7BOJ/direwolf_webstat and https://github.com/IZ7BOJ/APRS_dashboard as developed by Alfredo IZ7BOJ
// See config.php for adjustable parameters and see https://www.youtube.com/watch?v=7bMf7rWCfnE for more information

/* log structure:
0:chan
1:utime
2:isotime
3:source
4:heard
5:level
6:error
7:dti
8:name
9:symbol
10:latitude
11:longitude
12:speed
13:course
14:altitude
15:frequency
16:offset
17:tone
18:system
19:status
20:telemetry
21:comment
*/

function cmp($a, $b) { // custom sorting function
	if ($a[1] == $b[1]) {
		return 0;
	}
	return ($a[1] > $b[1]) ? -1 : 1;
}

function utilcpu() {
        $cpu1 = str_getcsv(shell_exec("cat /proc/stat | grep 'cpu '")," ",escape: "\\");
        sleep(2); // wait for two seconds to create difference in cpu utilization metrics
        $cpu2 = str_getcsv(shell_exec("cat /proc/stat | grep 'cpu '")," ",escape: "\\");
	// idle = idle + iowait
	// busy = user + nice + system + irq + softirq + steal
        $idle1 = $cpu1[5]+$cpu1[6];
        $busy1 = $cpu1[2]+$cpu1[3]+$cpu1[4]+$cpu1[7]+$cpu1[8]+$cpu1[9];
        $idle2 = $cpu2[5]+$cpu2[6];
        $busy2 = $cpu2[2]+$cpu2[3]+$cpu2[4]+$cpu2[7]+$cpu2[8]+$cpu2[9];
        $idledelta  = $idle2 - $idle1;
        $totaldelta = ($busy2+$idle2) - ($busy1+$idle1);
        return (intval(100 - ($idledelta / $totaldelta) *100));
}

function initializelog() { // function for initializing logfile
	global $logpath; //input
        global $logname; //input
	global $fixedlogname; //input
	global $newlogname; //output
	global $logfile; //output
        global $log; //output
	if (!isset($_SESSION['daysback'])) { $_SESSION['daysback']=0; } // go to today
        if ($fixedlogname!="") {
                $log=$logpath.$fixedlogname;
        } else { // change to new logfile and if not exists return empty array
		$daysback = date("d") - $_SESSION['daysback'];
		$newlogname=date("Y-m-d",mktime(0,0,0,NULL,NULL+$daysback,NULL)).'.log';
		$log=$logpath.$newlogname;
		if (is_file($log)) $logfile = file($log); else $logfile = [];
        }
}

function stationparse($frame) { //function for parsing station information
	global $stationcall;
	global $receivedstations;
	global $staticstations;
	global $movingstations;
	global $otherstations;
	global $viastations; //stations received via digi
	global $directstations; //stations received directly
	global $callraw;
	global $time;
	global $distance;
	global $bearing;
	global $speed;
	global $if;
	global $framesoninterface;

	if($frame[0]==$if) //if frame received on selected radio interface
	{
		$framesoninterface++;
		$frame=str_getcsv($frame,",",escape: "\\");
		$utime = $frame[1];
		if($utime > $time) { //if frame was received in time range
			$stationcall = strtoupper($frame[8]);
			if(array_key_exists($stationcall, $receivedstations)) { //if this callsign is already on stations list
				$receivedstations[$stationcall][0]++; //increment the number of frames from this station
			} else { //if this callsign is not on the list
				$receivedstations[$stationcall][0] = 1; //add callsign to the list
			}
			$receivedstations[$stationcall][1] = $utime; //add last time
	        	if(($frame[10] !=="") and ($frame[11] !== "")) { //if it's a frame with position
				haversine($frame);
				$receivedstations[$stationcall][2] = $distance; //add last distance
				$receivedstations[$stationcall][3] = $bearing; //add last bearing
				$receivedstations[$stationcall][4] = $speed; // add last speed
				$receivedstations[$stationcall][5] = $frame[10]; // add last latitude
				$receivedstations[$stationcall][6] = $frame[11]; // add last longitude

			}
			if($frame[12]==NULL) { //if speed is not null, it's a static station
				if(!in_array($stationcall, $staticstations)) {
					$staticstations[] = $stationcall;
				}
			} else {
				$movingstations[] = $stationcall;
			}
		}

		if($frame[3]==$frame[4]) { //if source=heard condition, the frame was heard directly
			if(!in_array($stationcall, $directstations)) {
				$directstations[] = $stationcall;
			}
		} else {
			if(!in_array($stationcall, $viastations)) {
				$viastations[] = $stationcall;
			}
		return;
		}
	} //closes if received on seleceted interface
}

function haversine($frame) { // function for calculating distance between own and remote stations
	global $stationlat;
	global $stationlon;
	global $distance;
	global $bearing;
	global $speed;
	global $declat;
	global $declon;

	$declat = $frame[10];
	$declon = $frame[11];
	$speed = $frame[12];

	//haversine formula for distance calculation
	$latFrom = deg2rad($stationlat);
	$lonFrom = deg2rad($stationlon);
	$latTo = deg2rad($declat);
	$lonTo = deg2rad($declon);

	$latDelta = $latTo - $latFrom;
	$lonDelta = $lonTo - $lonFrom;

	$bearing = rad2deg(atan2(sin($lonDelta)*cos($latTo), cos($latFrom)*sin($latTo)-sin($latFrom)*cos($latTo)*cos($latDelta)));
	if($bearing < 0) $bearing += 360;
	$bearing = round($bearing, 1);

	$angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
	cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
	$distance = round($angle * 6371, 2); //gives result in km rounded to 2 digits after comma
}

function secondsToTime($seconds) { // converts an amount of seconds to a time difference
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%d:%H:%I:%S');
}

function report_sysver() { // reports windows/linux operating system version
	if (str_contains(PHP_OS, 'WIN')) {
		return (explode("=",shell_exec("wmic os get Caption /value")))[1];
	} elseif (str_contains(PHP_OS, 'Linux')) {
		return shell_exec ("cat /etc/os-release | grep PRETTY_NAME |cut -d '=' -f 2");
	} else {
		return "Operating System not supported";
	}
}

function report_kernelver() { // reports windows/linux (kernel) version
	if (str_contains(PHP_OS, 'WIN')) {
		return substr(explode("[",exec("ver"))[1],0,-1);
	} elseif (str_contains(PHP_OS, 'Linux')) {
		return shell_exec ("uname -r");
	} else {
		return "Operating System not supported";
	}
}

function report_direwolfversion() { // reports windows/linux direwolf version
	global $direwolfversion;
	if (str_contains(PHP_OS, 'WIN')) {
		if ($direwolfversion=="") {
			$direwolffileversion=shell_exec('powershell "@(get-process direwolf -FileVersionInfo)[0].FileVersion"');
			$direwolfprodversion=shell_exec('powershell "@(get-process direwolf -FileVersionInfo)[0].ProductVersion"');
			if (strlen($direwolffileversion)!=0) {
				return $direwolffileversion;
			} elseif (strlen($direwolfprodversion)!=0) {
				return $direwolfprodversion;
			} else {
				return '<span class="notrunning">Cannot be determined, please specify manually in config.php</span>';
			}
		} else {
			return $direwolfversion;
		}
	} elseif (str_contains(PHP_OS, 'Linux')) {
		if ($direwolfversion=="") {
			$direwolfver = shell_exec ("apt-cache policy direwolf | grep -m 1 'Installed' | cut -d ' ' -f 4");
			if (str_contains($direwolfver,"(none)")) {
				return '<span class="notrunning">Cannot be determined, please specify manually in config.php</span>';
			} else {
				return $direwolfver;
			}
		} else {
			return $direwolfversion;
		}
	} else {
		return "Operating System not supported";
	}
}

function report_uptime() { // reports windows/linux sytem uptime
	if (str_contains(PHP_OS, 'WIN')) {
		$uptimeseconds=intval(shell_exec('powershell "@((get-date) - (gcim Win32_OperatingSystem).LastBootUpTime)[0].TotalSeconds"'));
		$dtZero = new \DateTime('@0');
		$dtTime = new \DateTime("@$uptimeseconds");
		return $dtZero->diff($dtTime)->format('%a days, %h hours, %i minutes and %s seconds');
	} elseif (str_contains(PHP_OS, 'Linux')) {
		return shell_exec('uptime -p');
	} else {
		return "Operating System not supported";
	}
}

function report_cputemp() { // reports windows/linux cpu temperature
	if (str_contains(PHP_OS, 'WIN')) {
		return (intval(shell_exec('powershell "@(Get-WMIObject -Query \"SELECT * FROM Win32_PerfFormattedData_Counters_ThermalZoneInformation\" -Namespace \"root/CIMV2\")[0].HighPrecisionTemperature"'))/10)-273;
	} elseif (str_contains(PHP_OS, 'Linux')) {
		if (file_exists ("/sys/class/thermal/thermal_zone0/temp")) {
			exec("cat /sys/class/thermal/thermal_zone0/temp", $cputemp);
			return $cputemp[0] / 1000;
		} else {
			return 'Cannot be detemined automatically';
        }
	} else {
		return "Operating System not supported";
	}
}

function report_cpufreq() { // reports windows/linux cpu frequency
	if (str_contains(PHP_OS, 'WIN')) {
		return (explode("=",shell_exec("wmic cpu get currentclockspeed /value")))[1];
	} elseif (str_contains(PHP_OS, 'Linux')) {
		if (file_exists ("/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq")) {
			exec("cat /sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq", $cpufreq);
			return $cpufreq[0] / 1000;
		} else {
			return 'Cannot be detemined automatically';
		}
	} else {
		return "Operating System not supported";
	}
}

function report_cpuutil() { // reports windows/linux cpu utilization
	if (str_contains(PHP_OS, 'WIN')) {
		return (explode("=",shell_exec("wmic cpu get loadpercentage /value")))[1]."%";
	} elseif (str_contains(PHP_OS, 'Linux')) {
		return utilcpu()."%";
	} else {
		return "Operating System not supported";
	}
}

function report_freemem() { // reports windows/linux free and total memory and free/total ratio
	if (str_contains(PHP_OS, 'WIN')) {
		$mem[0]=intval((explode("=",shell_exec("wmic os get freephysicalmemory /value")))[1]/1024);
		$mem[1]=intval((explode("=",shell_exec("wmic computersystem get totalphysicalmemory /value")))[1]/(1024*1024));
		$mem[2]=intval($mem[0]/$mem[1]*100);
		return $mem;
	} elseif (str_contains(PHP_OS, 'Linux')) {
		$memoryinfo = str_getcsv(shell_exec('/usr/bin/free -m | grep "Mem:" | tr -s " "')," ", escape: "\\");
		$mem[0]=$memoryinfo[2];
		$mem[1]=$memoryinfo[1];
		$mem[2]=intval($mem[0]/$mem[1]*100);
		return $mem;
	} else {
		return "Operating System not supported";
	}
}

function report_aprsisserver(&$aprsisserverip) { // reports windows/linux aprs-is server
	global $aprsisserverport;
	if (str_contains(PHP_OS, 'WIN')) {
		$aprsisserverip="";
		$aprsisserverip=shell_exec('netstat -n | findstr /r /c:"'.$aprsisserverport.' *ESTABLISHED"');
		if ($aprsisserverip!="") {
			$aprsisserverip=(explode(":",(explode(" ",preg_replace('/\s\s+/', ' ', trim($aprsisserverip))))[2]))[0];//array with only ip and port
			return '<a href="http://'.$aprsisserverip.':14501" target="_new">'.$aprsisserverip.'</a>';
		} else {
			return '<span class="notrunning">Not connected</span>';
		}
	} elseif (str_contains(PHP_OS, 'Linux')) {
		$aprsisserverip = shell_exec('netstat -n | grep '.$aprsisserverport.' | grep ESTABLISHED | head -n 1 | tr -s " " | cut -f 5 -d " " | cut -f 1 -d ":" | tr -d "\n"');
		if (strlen($aprsisserverip)>0) {
			return '<a href="http://'.$aprsisserverip.':14501" target="_new">'.$aprsisserverip.'</a>';
		} else {
			return '<span class="notrunning">Not connected</span>';
		}
	} else {
		return "Operating System not supported";
	}
}

function report_direwolfstatus() {
    if (str_contains(PHP_OS, 'WIN')) {
        if (str_contains(shell_exec('powershell "@(get-process direwolf)[0].ProcessName"'),"direwolf")) {
            return '<span class="running">Running</span>';
        } else {
            return '<span class="notrunning">Not running</span>';
        }
    } elseif (str_contains(PHP_OS, 'Linux')) {
        exec("pgrep -x direwolf", $output, $retval);
        if ($retval === 0) {
            return '<span class="running">Running</span>';
        } else {
            return '<span class="notrunning">Not running</span>';
        }
    } else {
        return "Operating System not supported";
    }
}

function report_clientips() {
    global $clientiplookuphost;

    if (!str_contains(PHP_OS, 'Linux')) {
        return "OS not supported";
    }

    // Comando mejorado para extraer IPs de clientes conectados a puertos 80, 443
    $cmd = "netstat -tn | grep ':80 .*ESTABLISHED\\|:443 .*ESTABLISHED' | tr -s ' ' | cut -d ' ' -f5 | cut -d: -f1";
    $output = shell_exec($cmd);
    $ips = array_filter(array_map('trim', explode("\n", $output)));

    $clientips = [];
    foreach ($ips as $ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP) && !preg_match('/^(127\.0\.0\.1|::1|0\.0\.0\.0)$/', $ip)) {
            $clientips[] = $ip;
        }
    }

    $clientips = array_unique($clientips);

    if (empty($clientips)) {
        return "No active users";
    }

    $html = "";
    foreach ($clientips as $ip) {
        $html .= '<a href="' . htmlspecialchars($clientiplookuphost . $ip) . '" target="_new">' . htmlspecialchars($ip) . '</a><br>';
    }

    return $html;
}

