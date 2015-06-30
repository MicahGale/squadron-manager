# Introduction #

Thank you for your help with developing this project, before you begin please familiarize yourself with this.  This will introduce you to our project's developing philosophy, and also has links to important information, such as the file hierarchy, and the database structure.


# Development Philosophy #

## Security First ##

We do not believe in _"Security through obscurity"_, the only true way to make something secure is to make a program that has complete code transparency, and still has no exploits.

Our product, Squadron Manager, is trusted by many people to hold their personal information, and keep it secure.  Since we have this great responsibility all of our code must be created with security first in mind.  Also to ensure this security all updates and releases must survive intensive penetration testing, before we will as a community endorse that version.  We have many software practices that helps ensure this security, which are listed below.  However this does not prevent all threats, and server administrators are strongly urged to follow _**this guide**_ on hardware/server specific security.

### Security Features ###

**1. SQL injection and XSS sanitation:**  All data passed to server is sanitized before being processed.  The whole process is: First, all dangerous characters are escaped, then the input is verified as the proper type, and of the proper length.  If any attempts are detected it is logged into the tables AUDIT\_LOG/AUDIT\_DUMP.

**2. Session Hijacking:** All activity in the staff side (/login/) is analyzed before the page is allowed to execute.  First the requesting IP address is compared to the IP address that started the session. If the user is logging in the page must be (/login/index.php) else they user is redirected to said page.  Next it sees if the user should be viewing this page, based upon an array of of possible page visits created from the last page visit.  The possible page visits are created based on which pages the user may visit, and any special page that they may need to visit to complete a task, such as the page to finalize a member deletion after visiting the page to select a member to delete.

**Database Permissions:**All staff users are users on the DBMS and have the minimum database permissions necessary for them to complete their task.  This adds another layer of protection against SQL injections, and also protects against any security glitches.

## Portability ##

Squadron Manager is meant to be able to be used by as many Squadrons as possible.  for this reason it should be portable to any hardware configuration or software configuration.  Since PHP is an interpreted language, the only software portability that is of concern is working with multiple DBMS's, so all database calls are done through internal functions (better explained in the next section).  So to port to a different DBMS than the initial one (MYSQL) the database functions just need to be changed.

## API ##

All of the functions in this project that need to be used multiple times are kept in one file called projectFunctions.php.  This file contains all commonly used functions, database calls, and processes, and any objects that are used by the project.

## Contributing ##

If you would like to contribute, just email the current project Owner, and they will add you.  The current owner is Micah Gale (<a href='mailto:Micah.Gale@gmail.com'>Micah.Gale@gmail.com</a>).  Also if you make a contribution please add your name and email to the contributors table in authors.php.