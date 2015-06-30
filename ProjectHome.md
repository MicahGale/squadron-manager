# General #

This is a program meant to handle what eServices doesn't for maintaining Civil Air Patrol Records. It is a centralized web-based local data management system. Meaning it can not only maintain the membership records that are needed locally, but also allows member to easily sign-in to meetings, and sign-up for testing easily using the Unit's network.  It is meant to be run on a server (please see requirements below), so that any device connected to your squadron's network may use it. Such as a cadet could sign-in to the meeting, and sign up to take their Wright Brothers test, or to promote to Wright Brothers all on their smart-phone. At the same time the testing officer could view this request on their laptop, and administer the test.  The Deputy Commander of Cadets could simultaneously view this promotion request, along with all the other promotion requests, all on one sheet. They could see weather or not the cadet is ready to promote, and approve or deny the promotion all from the same page.

# Installing #

To install please follow the directions on our [Installation Page](install.md).  Also after you install Squadron Manager, please join [our Google Group](https://groups.google.com/forum/#!forum/squadron-manager-users)  so you may stay up to date on the latest versions, and ask all the questions about Squadron Manger your heart desires.

## Contributing ##

If you would like to contribute please Join [Our Developer's Google Group](https://groups.google.com/forum/#!forum/squadron-manager-developers).  I will try to add you if you contribute to the Group, if that doesn't work please email me at [Micah.Gale@gmail.com](mailto:Micah.Gale@gmail.com) and just give a quick blurb about why you think you should be added as a developer. **_Note:_** you will need a Google Account for both of these.  To start working on the source code just go to the Source tab, and there will be directions on how to check-out the code using subversion.

## Technical Details ##

This project is a collection of PHP scripts, and a basic database setup.  It is primarily meant to be ran on a LAMP Server (Linux Apache Mysql PHP), but can be run on any server that can handle http and https (preferably TLS 1.0+), interpret PHP, and has MySQL.  Currently it can only handle MySQL, as we have not ported it to any other Database Management Software(DBMS) programs.  Porting to other DBMS's should be a quick process as all DB calls are done through methods, that would need to modified.  The DBMS must support Prepared Statements, or else the Porting will take much longer as some of the project's logic would have to change. (please see our [Developing Philosophy Page](http://code.google.com/p/squadron-manager/wiki/DevelopingPhilosophy) for more details).