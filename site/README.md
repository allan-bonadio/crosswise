Root of the CrossWise site.

This is based on mediawiki 1.22; expand that source in this directory and intermingle with git sources.

Most of these are mediawiki files and subdirs.  The relevant ones:
%25phpinfo.php - php info, with cryptic name to avoid detection
LocalSettings.php - mediawiki settings, sets up permissions, paths, extensions etc
api.php - i think this does ajaxes; somehow i don't do much with this
crosswise.sitemap - for robots.txt (cw not mw)
extensions/ - most of the CW code is here; source files named cw*.php
index.php - all page requests go thru this
load.php - proxy loader for css and js files
maintenence/ - tons of mw scripts for all sorts of things
robots.txt - the usual
sitemap.xml - older sitemap
skins/ - where all the MW skins reside; cw uses only 'crosswise' skin
tests/ - MW tests.  See ../testing for cw tests
