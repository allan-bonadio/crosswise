#!/bin/bash +x


echo OLD
exit


echo "Start a new CrossWise site from scratch:"


case  `uname -n` in
kashmir)
	mediaWikiArch=/Users/allan/Sites/wikiMedia1150/mediawiki-1.15.0.tar.gz
	;;

cajamarca)
	mediaWikiArch=/Users/allan/Sites/wikiMedia1150/mediawiki-1.15.0.tar.gz
	;;
esac

targetHost=tactile@pepper.he.net
targetPath=/home/tactile/public_html/
uploadPath=/home/tactile
mediaWikiArchTopDir=mediawiki-1.15.0

echo create the cw.tbz2 archive
# just the checked-in cw-specific files
# including the content in CrossWise.xml
rm -Rf /tmp/tcw
svn export svn://kashmir/wiki /tmp/tcw || exit 23
cd /tmp
tar cvfj cw.tbz2 tcw || exit 21

echo upload the cw archive, and mediawiki archive
scp /tmp/cw.tbz2 $targetHost:$uploadPath/ || exit 17
scp $mediaWikiArch $targetHost:$uploadPath/mediaWiki.tgz || exit 19

echo Go up there and unpack/merge  them properly
ssh $targetHost << GUTAUMTP
     set +x
     cd $targetPath || exit 15
     
     echo First the mediaWiki archive
     tar xvfz $uploadPath/mediaWiki.tgz
     mv $mediaWikiArchTopDir tcw || exit 13
     
     echo Second merge in the CW srcs
     tar xvfj $uploadPath/cw.tbz2 || exit 11
    
     echo See what we got
     cd tcw || exit 9
     ls
     ls extensions
    
     echo OK  now phony up that this is the first time its run
     mv LocalSettings.php rightLocalSettings.php || exit 31
GUTAUMTP


echo
echo "OK now ready to fire up the Wiki config script."
echo "* Browse to the server => $targetPath/tcw/config/index.php"
echo "* Fill out the form & activate.  "
echo "* Then toss that LocalSettings.php in favor of rightLocalSettings.php"
echo "    or should probably do a diff so you know what youre doing"
echo "* You also need to cross-hack htaccess-dot with the .htaccess at the docroot"
echo 
