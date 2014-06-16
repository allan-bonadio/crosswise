CrossWise extension files (all must go together and with the skins/crosswise code):

README.md - this file

cwAllFeatures.php - implements All Features page

cwAudit.php - used for verifying pages and titles ad categories are all correctly spelled etc

cwBackupPages.php - backs up content pages in db-id independent way

cwFSrc.php - Feature Source testing code to test examples in content

cwIniPages.php - creates iniital contents of any new page based on everything else

cwJavaScript.php - specific code for JS language & its FSrc

cwLangBox.php - code for language-changing box, new in CW 2

cwMain.php - main file; loads all others, sets hooks & templates

cwPHP.php - specific code for PHP language & its FSrc

cwRuby.php - specific code for Ruby language & its FSrc

cwView.php - code for the main View page that displays comparison columns

footLog/ - logger that displays output at bottom of page; separate MW plugin

Plugins supplied by MW that i'm using:

ConfirmEdit/

Nuke/

README

Renameuser/

SimpleAntiSpam/

TitleBlacklist/
