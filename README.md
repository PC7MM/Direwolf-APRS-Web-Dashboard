# Direwolf APRS Web Dashboard for Linux and Windows

This Direwolf APRS Web Dashboard offers all kinds of information for both Operators and Users of a Direwolf-based APRS iGate node, which is not easily visible through a user interface of Direwolf itself.

The dashboard aggregates and visualizes information from the the Direwolf radio interface packet logfiles, from the Direwolf console, from aprs.fi and aprsdirect.de, from the connected APRS-IS server, and from the Linux Operating System. See this video for an introduction to the initial version of the dashboard: https://www.youtube.com/watch?v=7bMf7rWCfnE

This dashboard is made by Michael PC7MM and Richard PD3RFR and finds it roots in the code of Alfredo IZ7BOJ, who developed direwolf_webstat in 2021 and APRS_dashboard in 2018, and with this we express our gratitude for his work and inspiration! Alfredo can be found on github via https://github.com/IZ7BOJ

![screen1](./screenshots/01.%20Home.jpg)

## Main features of the Direwolf APRS Web Dashboard

(See the screenshots in the repository for details)

- Show aggregated and detailed information per heard station in tables and on maps
- Show static/moving indicator and via indicator (digi and/or direct)
- Show several Node and Operating System related status information
- Show IP addresses of viewers of the Direwolf APRS Web Dashboard
- Show raw packet information for selected specific stations
- Show actual traffic on one or all Direwolf radio interfaces chronologically
- Show actual status of iGates that are monitored, obtained from aprs.fi
- Show map of iGates that are monitored, obtained from aprsdirect.de
- Show information about the APRS-IS server that is currently used by Direwolf
- Show output of the Direwolf console including automatic browser scrolldown 
- Show a conceptual diagram of the APRS infrastructure

- Ability to display both APRS and AIS information from Direwolf logfiles
- Ability to browse through actual and past daily Direwolf packet logfiles
- Ability to customize refresh rate and turn automatic refresh on and off
- Ability to filter by Direwolf radio interface or to view all channels at once
- Ability to limit data displayed to specific time frames
- Ability to sort data in tables per column both ascending and descending
- Ability to view activity in multiple Direwolf radio interfaces
- Ability to search for specific stations, including wildcard search
- Ability to change layout through CSS stylesheet and custom logo file

- One configuration file that contains all adjustable parameters

This version is developed and tested on a Raspberry Pi Model 4b with Debian GNU/Linux 12 (bookworm) with lighttp and PHP 8, and is tested on Microsoft Windows 10 with Abyss Web Server X1 and PHP 8 as well.

## Instructions for installation and configuration on Linux

Since every single setup is differrent, universal installation and configuration instructions cannot easily be given. Having stated that as a disclaimer, the instructions as mentioned below serve as a guideline and will most likely result in a running Direwolf APRS Web Dashboard on Debian-based hosts.

Update your Operating System:

    sudo apt-get update -y ; sudo apt-get upgrade -y

Install (latest version of) PHP and required modules:

    sudo apt-get install php8.2-common php8.2-cgi php -y

Install and configure lighttpd, a lightweight webserver:

    sudo apt-get install lighttpd -y

    sudo lighty-enable-mod fastcgi

    sudo lighty-enable-mod fastcgi-php

    sudo service lighttpd force-reload

Download or clone all files of this repository to your home directory and copy the files in the "code" folder to /var/www/html/

	sudo git clone https://github.com/PC7MM/Direwolf-APRS-Web-Dashboard ~/Direwolf-APRS-Web-Dashboard 

	sudo cp ~/Direwolf-APRS-Web-Dashboard/code/* /var/www/html/

Open config.php and carefully adjust the parameters as needed for your specific situation. 
	
	sudo nano /var/www/html/config.php

Change the Direwolf startup command to request Direwolf creating both packet logfiles and a console logfile, for example:

	/usr/bin/direwolf -c /etc/direwolf.conf -l /var/log/direwolf -daknpwtoihfxd > /var/log/direwolf/console.log

## Instructions for installation and configuration of Windows

Download the Abyss X1 Web Server via https://aprelium.com/abyssws/download.php and run the installer

Enable PHP support for the Abyss X1 Webserver as explained on https://aprelium.com/abyssws/php.html

Download or clone all files of this repository to the Downloads directory and copy the files in the "code" folder to C:\Abyss Web Server\htdocs\

        git clone https://github.com/PC7MM/Direwolf-APRS-Web-Dashboard C:\users\<user>\Downloads\Direwolf-APRS-Web-Dashboard

        copy C:\users\<user>\Downloads\Direwolf-APRS-Web-Dashboard\code\*.* C:\Abyss Web Server\htdocs\

Open config.php and carefully adjust the parameters as needed for your specific situation. 

        notepad C:\Abyss Web Server\htdocs\config.php

Change the Direwolf startup command to request Direwolf creating both packet logfiles and a console logfile, for example:

	direwolf.exe -c "C:\direwolf\direwolf.conf" -l "C:\direwolf\logs" -daknpwtoihfx > "C:\direwolf\logs\console.log"

Direwolf for Windows might write console output in a 4kB output buffer instead of writing it directly to the console logfile. If such a buffer is being used, information in the Web Console Viewer will only be updated after Direwolf flushes that buffer.

## Instruction video

PD3RFR and PC7MM made a video that explains how to setup an RX-only iGate with RTL-SDR from scratch. This video was made before this Direwolf APRS Web Dashboard was made, so it is not refered to in the video. The video explains however how direwolf_webstat as made by Alfredo IZ7BOJ needs to be installed, and this procedure is equal to installing this Direwolf APRS Web Dashboard. If you are interested, see: https://www.youtube.com/watch?v=tuR0dZxdv1o 

## Disclaimer

The developers have some experience in developing software but are not professional developers in their daily lives. Although this software was developed and tested with great care, it might not function properly in specific situations. The software can be used at your own risk and the developers do not accept any liability as a result of the usage of this software. We hope you benefit from this Direwolf APRS Web Dashboard. If you like it, share it! And if you have questions, ask us!

Best regards, Michael PC7MM & Richard PD3RFR & Alfredo IZ7BOJ

## License

This Direwolf APRS Web Dashboard can be freely used and modified for non-commercial purposes, as long as a link the developers (Alfredo IZ7BOJ & Richard PD3RFR & Michael PC7MM) is preserved. Please contact the developers in case of questions related to commercial usage of (parts of) this Direwolf APRS Web Dashboard.

## Screenshots

Home -  APRS Channel
![screen1](./screenshots/01a.%20Home%20APRS%20channel.jpg)

Home - AIS Channel
![screen2](./screenshots/01b.%20Home%20AIS%20channel.jpg)

System Status
![screen3](./screenshots/02.%20System%20Status.jpg)

Change Radio Interface
![screen4](./screenshots/03.%20Change%20Radio%20Interface.jpg)

View Specified Station
![screen5](./screenshots/04.%20View%20Specified%20Station.jpg)

Traffic Monitor
![screen6](./screenshots/05.%20Traffic%20Monitor.jpg)

IGate Monitor
![screen7](./screenshots/06.%20iGate%20Monitor.jpg)

APRS-IS Server
![screen8](./screenshots/07.%20APRS-IS%20Server.jpg)

Console Viewer
![screen9](./screenshots/08.%20Console%20Viewer.jpg)

About
![screen10](./screenshots/09.%20About.jpg)
