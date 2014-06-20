Preamble
==========
Source code for the Syndicates browsergame: syndicates-online.de

ATTENTION: The code of this project as is is not usable without further manual modifications! This is a _best effort_ attempt to make the codebase of the game
available to the community and everyone who might be interested in operating it. The way of coding and structure of the project is outdated and may be hard
to comprehend.

There may and will be many 3rd party dependencies which need to be resolved. Some dependencies may be broken due to removed files.
There may and will be security implications due to possible sql-injection attacks and possible cross site scripting issues.
Before using thise code base please keep all this in mind.


Jannis Breitwieser, 19.06.2014




Setup
=====
- Syndciates Requires Mysql 5.1+
- Syndciates Requires PHP (TODO - check required version number)
--- Required PHP-Modules: ./configure --with-apxs2=/www/bin/apxs --with-zlib --with-mysql=/home/mysql --with-gd --with-mcrypt --with-jpeg-dir=/opt --with-png-dir=/opt --with-zlib-dir=/opt --with-curl --with-mysql-sock=/home/mysql/data --with-mysqli=/home/mysql/bin/mysql_config --enable-mbstring
--- Should use APC
--- Install necessary os-modules:
----- Suse:  yast -i wget unzip flex bison libxml2 libxml2-devel libpng12-0 libmcrypt libmcrypt-devel libcurl4 libcurl-devel autoconf gcc gcc-c++ make libltdl3 libtool libjpeg8 libjpeg8-devel libpng12-devel
----- Debian: apt-get install insserv build-essential ntp rpm zlib-bin zlib1g-dev libssl-dev libxml2-dev libpng-dev libjpeg-dev libcurl4-openssl-dev libmcrypt-dev autoconf


- Syndicates was operated on apache (2.2.x) so far - an example vhost file can be found in  /etc/vhosts_example.conf

- Database config can be found in /src/config/connectdb.php --> replace your credentials here
- Various game-parameters are set in /src/inc/ingame/globalvars.php
- Setup contract/impressum in /src/inc/impressum.php
- Define terms of usage in /src/inc/nutzungsbedingungen.php
- For Facebook login setup credentials in /src/lib/facebook.php
- Maybe necessary for facebook: /src/lib/fb_ca_chain_bundle.crt (has been removed from repositoy - ask r4bbit for details)
- Define cronimon user in /src/lib/omnilib.php if cronimon is supposed to be used for stats tracking
- Twitter account in /src/lib/twitter.php - update credentials here
- Templace, Code and GraphicPack directories MAY need write permissions by either webserver - or cronjob users




Emogames-Dependencies
=====================
- Syndicates was originally operated by Emogames, starting 2005
- Emogames proviced a custom interface/web service for authentication and some other functionalities
- Stubs to that interface still exist in various places and are indicated by function calls prefixed with EMOGAMES_
- Those functionalities need to be replaced for operating this version of the game 


Sourcecode
==========
- The login is configured against the emogames interface - small tweaks in /src/public/index.php may be necessary to authenticate against md5 hashed user passwords
- Email sending MAY still be configured against emogames interface - replace this by local email-sending via php mail() function is necessary and applicable see /src/lib/subs.php function sendthemail
- Emogames has been replaced by BETREIBER in most places
- Krawall registration and login have been removed
- Some ads have been removed
- Google analytics has been removed in some places (possibly all)


- Removed or emptied files due to potential copyright/3rd party implications:
	- /src/lib/k_subs.php
	- /src/lib/abofunctions.sphp
	- /src/lib/mod_interface_pmbox.php
	- /src/includes_old.php
	- /src/public/interface.php
	- /src/public/interface.php
	- /src/public/style_krawall.css
	- /src/public/syndicates/style_krawall.css
	- /src/public/php/arial.ttf
	- /src/public/php/CB_Cookie.swf - unclear copyright situation, check http://www.nuff-respec.com/technology/cross-browser-cookies-with-flash
	- /src/public/php/CB_Cookie.js - unclear copyright situation, check http://www.nuff-respec.com/technology/cross-browser-cookies-with-flash
	- /src/public/php/evercookie.js - unclear copyright situation, check https://github.com/samyk/evercookie
	- /src/public/php/evercookie.swf - unclear copyright situation, check https://github.com/samyk/evercookie
	- /src/public/php/evercookie.php - unclear copyright situation, check https://github.com/samyk/evercookie
	- /src/public/php/evercookie.xab - unclear copyright situation, check https://github.com/samyk/evercookie
	- /src/public/php/evercookie.fla - unclear copyright situation, check https://github.com/samyk/evercookie
	- /src/public/php/evercookie_cache.php - unclear copyright situation, check https://github.com/samyk/evercookie
	- /src/public/php/evercookie_etag.php - unclear copyright situation, check https://github.com/samyk/evercookie
	- /src/public/php/evercookie_png.php - unclear copyright situation, check https://github.com/samyk/evercookie
	- /src/public/php/reminder.php
	- /src/public/php/verdana.ttf
	- /src/public/php/admin/k-stats.php
	- /config/interfaces/emogames.php
	- /src/lib/mod_interface.php
	- /src/lib/mod_interface_emogames.php
	

Cronjobs
========
- all cronjobs are located in /src/crons
- Various cronjobs in /src/crons have email-sending functionality included - edit email-adresses here if you want to use this functionality
- cron_main.php coordinates all relevant cronjobs and should be scheduled once a minute
- Most important cronjob is update.php which procudes game-progess 


Database
========
- The database contents are to be found in /sql/syndicates_complete.sql.gz
- Unzip the sql file
- Login to mysql, use target db
- Execute 'source /sql/syndicates_complete.sql'
- All private data has been stripped off the database
- User Accounts (users-table) only hold username + hashed password --> this will allow users that remember their passwords to use their accounts and re-enter data
- Stats for the final round of syndicates operated by emogames have not been stored - all other stats should be in place


Graphic-Packs
=============
- Only the basic graphic packs are to be found in the open source version
- Due to potential copyright implications all data from krawall - including graphic packs - has been removed 

Images
======
- Due to potential copyright implications all voting buttons or other third party images have been removed

