MaterialsDatabase
=================

Materials Database is an interface to lookup, store, and export material properties.
This application is a Mediawiki extension.

Pre-requisites:
-PHP
-Apache
-MySQL
-Mediawiki - https://www.mediawiki.org/wiki/Download
-Phpmyadmin (optional, but recommended)

Note: It is recommended to use a linux distribution for deployment. Windows might give your problems while installing Mediawiki.

Here's how to setup the application and use it:

1. Install PHP, Apache and MySQL.
2. Install MediaWiki and set it up. Make a note of the database prefix (name) you enter while installing Mediawiki.
3. Clone this repository into the path: /server root directory/mediawiki/extensions/ (This is usually /var/lib/mediawiki or /var/www/mediawiki/ if you're using a linux distribution).
4. Edit the mediawiki/LocalSettings.php file and append this line at the end: require_once "$IP/extensions/materials_database/materials_database.php";
5. Copy the images under materials_database/images/ to /mediawiki/skins/common/images/ (create this directory if it's not present). 
6. Start the Apache server and MySQL.
7. Execute the wikimaterial.sql file from /materials_database/ directory in the Mediawiki Database you created while installing Mediawiki (from step 2). This will create the database required for the materials database extension. [Check your Mediawiki database name through http://localhost/phpmyadmin if you have phpmyadmin installed]. 
8. Visit the URL http://localhost/$MW/index.php/Special:materials_database from your browser to use the application. Here $MW stands for the version of MediaWiki you have installed. For example http://localhost/mediawiki-1.26.2/index.php/Special:materials_database. [Try http://localhost/mediawiki/index.php/Special:materials_database if you're not sure about your version]
9. You're all set. Sign up and use Materials Database!
