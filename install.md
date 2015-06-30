
# Introduction #

[Jump to Linux Install](#Linux.md) <br>
<a href='#Manual_Install.md'>Jump to Automatic Windows Installation</a>

This Guide will walk you through the process of installing Squadron Mange. This will cover how to prepare and install this system on multiple systems, both automatically and manually. If you any troubles you can get help through the <a href='http://groups.google.com/group/squadron-manager-users'>User Google Group</a>

The major steps for the installation will be:<br>
<ol><li>Install required Software<br>
</li><li>Configure Apache and MySQL<br>
</li><li>Install the Database and website for Squadron Manager<br>
</li><li>Add Users, and use Squadron Manager<br>
</li><li>Customize the website<br>
</li><li>Secure the Server</li></ol>

<b>Legal Note:</b> <i>Squadron Manager is free software licensed under the GNU General Public License version 3. You may redistribute and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.</i>

<i>Squadron Manager comes without a warranty; without even the implied warranty of merchantability or fitness for a particular purpose. See the GNU General Public License version 3 for more details.</i>

<h1>Hardware Requirements</h1>
To run Squadron Manager you need at least one computer, although it is suggested that you use a dedicated Server/computer to host the site.<br>
This computer must:<br>
<ul><li>Be running Windows, Linux, or Unix<br>
</li><li>Have A CPU with two or more cores running at 2gHz is recommended<br>
</li><li>Have At least 1GB of RAM is recommended<br>
</li><li>Have 1-10gb of free hard drive space is recommended</li></ul>

<h1>Software Requirements</h1>
You will need to install:<br>
<ul><li>A http sever- <a href='http://httpd.apache.org/'>Apache</a> is recommended.<br>
</li><li>A Database Server (DBMS) <a href='http://dev.mysql.com/downloads/mysql/'>MySQL</a> is currently the only one supported<br>
</li><li><a href='http://us.php.net/'>PHP</a>
</li><li>The PHP mysql native driver (PHP5-mysqlnd)<br>
</li><li><a href='https://drive.google.com/folderview?id=0B-3DXkTNAIwmT2pNbVkyRU9heFU&usp=sharing'>Squadron manger</a></li></ul>

<h1>Linux</h1>
Squadron Manager was built on a Linux system (Ubuntu), so it will be most stable running on Linux, although Windows is still supported. This guide works best on a Debian/Ubuntu based system, but can be easily used on other systems. If you have a Debian System follow the guidelines below, otherwise jump to the <a href='#Generic_Linux.md'>Generic Linux Section</a>

<h2>Automatic Debian Install</h2>
<b>Note:</b> This method only works for Ubuntu and Debian, but the package can be converted to another Distribution using <a href='http://rpmfind.net/linux/rpm2html/search.php?query=alien'>Alien</a>, and that distribution’s package management  software.<br>
<br>
<ul><li>First you need to set-up a static IP address for the server. <a href='http://www.wikihow.com/Assign-an-IP-Address-on-a-Linux-Computer'>This article</a> provides a pretty good walk-through. If you need more help please ask for it in the Google Groups.<br>
</li><li>Install the required programs with the commands below. This will install the LAMP server as well the database driver that is need. Also make sure to select a secure password for the Mysql root account, and remember that password, you will need it later.<br>
<pre><code>sudo apt-get install tasksel<br>
sudo tasksel lamp-server<br>
sudo apt-get install PHP5-mysqlnd<br>
sudo apt-get remove tasksel<br>
</code></pre></li></ul>

<ul><li>Download the most current Debian file from the <a href='https://drive.google.com/#folders/0B-3DXkTNAIwmVEs0MTdrdjVsdGM'>Google Drive</a>. It will be squadMan<code>_*</code>version<code>*_</code>x86_x64.deb.</li></ul>

<ul><li>In terminal you will need to navigate to where it is and install it using dpkg:<br>
<pre><code>sudo dpkg -i squadMan_Version_x64_x86_Debian.deb<br>
</code></pre></li></ul>

<ul><li>Follow the on-screen instructions to complete the process. Please note you will need access to a web-browser during this process, either on the computer, or another computer connected to the same network. For more information walking through the installation please see <a href='Installation_walk.md'>The Installation walk through</a>
If this does not work please do the <a href='install#Manual_Install.md'>Manual install</a></li></ul>

<h2>Generic Linux</h2>

This is how to install the generic linux package on any Linux/Unix system. If these steps fail please go to the <a href='Manual_Install.md'>Manual Install</a>.<br>
<br>
<ul><li>First set up a static IP address. <a href='http://www.wikihow.com/Assign-an-IP-Address-on-a-Linux-Computer'>This article should be very helpful</a></li></ul>

<ul><li>Next you will need to install <a href='#Software_Requirements.md'>the required software</a>. This will vary from distribution to distribution, so it's difficult to have one guide for this. The best way would be to Google how to install a LAMP server on your distribution as well as how to install PHP5-mysqlnd (PHP mysql native driver). Or you can ask how in the user group.</li></ul>

<ul><li>Now you will need to download the proper package to install from <a href='https://drive.google.com/#folders/0B-3DXkTNAIwmVEs0MTdrdjVsdGM'>Google Drive</a>. It will be squadMan<code>_*</code>version<code>*_</code>x86_x64-generic.tar.gz. Then extract the tar ball to use it. You should extract to some place that is out of the way such ~/squadMan<code> or </code> /tmp/squadMan`<br>
<pre><code>tar -xzvf foo_bar.tar.gz -C /path/To/Extraction<br>
</code></pre></li></ul>

<ul><li>Now navigate to where you extracted it (<code>/path/to/Extraction</code>) and run install.sh as root. Follow the on-screen instructions through this process. For more information on this please see <a href='Installation_walk.md'>The Installation walk-through</a>
<pre><code>sudo bash install.sh<br>
</code></pre></li></ul>

<ul><li>Install is now complete! just delete the install files and the tar ball since you don't need them anymore. Also make sure you have a secure configuration, and customize your implementation.<br>
<h1>Windows</h1>
You will need to install a WAMP, to do this follow <a href='http://www.wikihow.com/Install-WAMP'>these instructions</a>, but <b><i>DO NOT</i></b> install PHPMyadmin for security purposes, all database management should be done directly on the Server or over a remote desktop.</li></ul>

Next download the most recent windows installer from the <a href='https://drive.google.com/#folders/0B-3DXkTNAIwmZU5XSmF3WExILXM'>Google Drive</a>, it will be squadMan<code>_*</code>version<code>*_</code>x86_x64_windows.msi, and run it<br>
<h1>Setup https</h1>

<h1>Manual Install</h1>
For this you will need to download the Linux or Windows generic tar ball or zip file from the <a href='https://drive.google.com/folderview?id=0B-3DXkTNAIwmT2pNbVkyRU9heFU&usp=sharing'>Google Drive</a>. These will be SquadMan_version_x86_x64_linux.tar.gz and squadMan_version_x86_x64_windows.zip respectively. These will be almost identical, they only differ in a few lines of code, so from now on they will be referred to as the same, as they set up the same way. Once the Download is complete extract the contents somewhere you have easy access to, and follow these steps:<br>
<ol><li>Go to the install folder, and run db_dump.sql in terminal or command prompt with the command <code>mysql -u root -p db_dump.sql</code> You will be then prompted for the mysql root password, enter it, and mysql with then run the file, setting up the database for Squadron Manager.<br>
</li><li>Install the website. There are two sites, one http one that redirects all requests to the https, and the actual https. In the archive var/www contains the actual site and var/www-redirect contains the redirect site. Copy var/www to where you would like on Linux /var/www is suggested, and for Windows C:\www do the same with var/www-redirect but instead /var/www-redirect and C:\www-redirect are recommended.