#!/bin/sh

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

#This program is a very simple script to automatically backup the Squadron Manager database.
#it uses duplicity to backup the server locally and remotely on your schedule (set in crontab)
#A backup will be ran every execution so the backup schedule is controlled by crontab.
#the program primarily focuses on maintaining old backups.
#You can control how long the incremental and full backups are kept. 
#The incremental backups are done every backup and will be the first to be deleted.
#The full backups will be made when the incremental backups are deleted and will be kept for the time specified.
#The backup will backup the binary files of the database so the shell will need to be ran as a user with access 
#to this (usually /var/lib/mysql)

#Configuration options
#The location that the mysql server information is kept
DATA_DIR="/var/lib/mysql/"
#The location of the local backup
LOCAL_DIR="/var/lib/mysql-backup/"
#The remote backup url
REMOTE="webdav://nasageek16@gmail.com:password@dav.box.com/dav/"
#time to keep incremental backups remotely
INCREM_LIFE="1 month"
#the time to keep the full backups remotely (see the time format of the duplicity man page for formatting)
FULL_LIFE="1Y"

######Environmental variables#####
##the password for encrypting the data
export PASSWORD="password"