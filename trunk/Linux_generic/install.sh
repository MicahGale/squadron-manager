#!/bin/bash
# The post install for Squadron Manager
#This will install the database for squadMan, and the database permissions.
#It will also prepare the system for use by configuring it, and setting up an initial user.

# Copyright 2014 Micah Gale
#
# This file is a part of Squadron Manager
#
#Squadron Manager is free software licensed under the GNU General Public License version 3.
# You may redistribute and/or modify it under the terms of the GNU General Public License
# version 3 as published by the Free Software Foundation.
#
# Squadron Manager comes without a warranty; without even the implied warranty of merchantability
# or fitness for a particular purpose. See the GNU General Public License version 3 for more
# details.
#
# You should have received the GNU General Public License version 3 with this in GPL.txt
# if not it is available at <http://www.gnu.org/licenses/gpl.txt>. 
#keep the original php config file just in case
if [ -e /etc/php5/apache2/php.ini ] ; then
    mv /etc/php5/apache2/php.ini /etc/php5/apache2/php.ini.orig

#move all the files into place
cp -rp usr /
cp -rp var /
cp -rp etc /
cp -rp tmp /
#Tell the User what's going on 
echo "Welcome to Squadron Manager, Copyright 2014 Micah Gale
This will complete the Install Process for the program.
"
echo "The Apache server is being reconfigured to serve Squadron Manager"
#disable default websites
a2dissite 000-default
a2dissite default-ssl

#enable the squadMan sites
a2ensite squadMan-http
a2ensite squadMan-ssl

#get random data for passwords for mysql passwords and salts
echo "
Random passwords need to be generated to make the database users secure."
echo "Please note: This process will take awhile (aprox. 15 min.)
Please do not exit, and be patient, this whole process only needs to be done once"
echo "Randomly type and move the mouse to create secure random data, and
speed up the process" 
users_thing=("login" "salt" "Logger" "Viewer" "Sign-in" "delete" "ViewNext" "notif")
spinny_thing=("-" "\\" "|" "/")
#let's generate some passwords!
sizes=${#users_thing[@]}
declare -i num
num=0
#loop through 
declare -a pass
#create a file to communicate
touch /dev/shm/stuffAndThings
chmod 600 /dev/shm/stuffAndThings
for i in ${users_thing[@]}
do
    dd if=/dev/random status=none bs=1 count=56 | base64 >>/dev/shm/stuffAndThings & #generate password
    echo -n "Creating password: `expr $num + 1` of $sizes "     #tell what's going of
    echo -en "\033[s"
    while [[ $(jobs) == *Running* ]]  #while the program is still running 
    do
        for j in ${spinny_thing[@]} #now spin you beotch!
        do 
           echo -en "\033[u\033[1D$j       \033[7D"
           read -N 100000000 -t 0.01 useless
        done
    done
    echo -e "\033[1D "
    num=`expr $num + 1`
done

#now to save the data properly to /etc/squadMan/psswd.ini
location="/etc/squadMan/psswd.ini"
echo ";Do not directly edit this file! It will break Squadron Manager!">$location
while read line
do
    pass+=($line)
done < /dev/shm/stuffAndThings
shred -n 10 -u /dev/shm/stuffAndThings
num=0
users_thing+=("Useless") #add the useless user for using mysqli sql cleaners
pass+=("password")
#get the mysql root password
echo "
We now need to set up the MySql Database.
We will need root access to set up the database,
add users, and change permissions."
IFS=""
read -s -p "Please enter Mysql's root password:" password # get the password
$(mysql -u root -p$password -e "describe mysql.user" > /dev/null)
while [ $? != 0 ] #check the password and make sure it works based on the exit code
do
    read -s -p "Please enter Mysql's root password:" password # get the password
    $(mysql -u root -p$password -e "describe mysql.user" > /dev/null)
done
num=0
sizes=${#users_thing[@]}
while [ $num -lt $sizes ] 
do
    echo "${users_thing[$num]}=\"${pass[$num]}\"">>$location #save it to files
    mysql -u root -p$password -e "GRANT USAGE on *.* to '${users_thing[$num]}'@'localhost' IDENTIFIED BY '${pass[$num]}'"
    if [ $? != 0 ] ; then  #if failed exit 
        echo failed to create MySql users
        exit 1
    fi
    let num++
done
#get the DB ready now!
echo "MySql users created"
#start setting up the db
mysql -u root -p$password < /tmp/squadMan/db_dump.sql
if [ $? != 0 ]
then
    echo failed to set up database
    exit 1
fi
echo Database has been installed
#start setting up the permissions
mysql -u root -p$password < /tmp/squadMan/DB_PERMISSIONS.sql
if [ $? != 0 ] #error handling
then 
    echo failed to set up database permissions
    exit 1
fi
echo User priviledges have been added
shred -n 10 -u $HOME/.mysql_history # destroy the history of the passwords

#now to configure PHP for the new IP address

echo "Now PHP needs to configured for the static IP address that has
been set up. If you haven\'t set up a static IP address please see:
<https://code.google.com/p/squadron-manager/wiki/install>"
ip=$(ifconfig  | grep -E 'inet addr' | grep -v '127.0.0.1' | awk 'sub(/inet addr:/,""){print $1}')
if [[ "$ip" != "" ]]; then # if couldn't find IP adress ask
    echo "We couldn't find your IP address; please enter it below."
else
    echo "We think your IP address is: $ip, if this is correct press enter
    otherwise enter your actual IP address."
fi
read -p "IP Adress:[$ip]" new_ip
if [ "$new_ip" != "" ] ; then
    ip=$new_ip #if the address is changed update it
fi
#now to actually save it to the PHP.ini file...yaaaaayyy
mv /etc/php5/apache2/php.ini /etc/php5/apache2/php.ini.squadMan.inst #move the fresh install one 
sed 's/session.cookie_domain[:blank:]*=[:print:]*/session.cookie_domain = $ip/' /etc/php5/apache2/php.ini.squadMan.inst > /etc/php5/apache2/php.ini #create the new php.ini in place

#now let's make the ssl key.
echo "
Now a ssl certificate needs to be made to allow for https.
Please follow the on-screen instructions, and enter the 
required information. 
NOTE: For common name enter the IP adress you entered above."
#generate the key
mkdir /etc/ssl/crt/
openssl req -x509 -nodes -days 365 -newkey rsa:4096 -keyout /etc/ssl/crt/mysitename.key -out /etc/ssl/crt/mysitename.crt
if [ $? != 0 ] ; then
    echo "Key generation failed" 
    exit 1     #error handling
fi
echo "Key generated successful"
chown -R www-data root /etc/ssl/crt/
chmod -R 640 /etc/ssl/crt/
chmod 750 /etc/ssl/crt/           #set permissions for the file
a2enmod ssl
service apache2 restart #reload the apache configuration

echo "
Now a system administrator account needs to be created.
Go to <https://$ip/finish.php> to set create a new member.
Once that is done return here."
read -p "[enter]" useless
echo "install was successfull"
rm -r /tmp/squadMan
rm /var/www/finish.php