#!/bin/bash

# hmmmm too personal.  Instead error on SSH_AGENT_PID
. ~allan/bin/james_bond.sh

echo "Update an existing CrossWise site from SVN etc."
echo "Only svn checked-in files are pushed onto an original of MediaWiki,"
echo "your working files are ignored whether managed or not."

case  `uname -n` in
kashmir*)
	scratch=/Volumes/orissa
	;;

cajamarca*)
	scratch=/devel
	;;

*)
	echo "machine must be either cajamarca or kashmir"
	exit 7
esac

targetHost=tactile@pepper.he.net
targetPath=/home/tactile/public_html/
uploadPath=/home/tactile
mediaWikiArchTopDir=mediawiki-1.15.0

echo create the cw.tbz2 archive
# just the checked-in cw-specific files
# NOT ANY MORE (jan 2012) including the content in CrossWise.xml
rm -Rf $scratch/tcw
echo "    export"
svn export -q svn://kashmir/wiki $scratch/tcw || exit 23
cd $scratch
version=`date +%Y%m%d,%H%M%S`
echo -n $version > tcw/version.txt
echo "    tarring & bzipping"
tar cvfj cw.tbz2 tcw || exit 21

echo "upload the cw archive, couple mins.  " `date +%H:%M:%S`
echo "    Must have ssh agent, or enter your ssh key pw"
scp $scratch/cw.tbz2 $targetHost:$uploadPath/ || exit 17
echo "    upload done   " `date +%H:%M:%S`


echo "Go up there and unpack/merge  them properly.  again, agent or pw"
ssh $targetHost << GUTAUMTP
	set +x
	cd $targetPath || exit 15
	
	echo "  first move the old ones out of the way (errors OK)"
	mkdir -p prevCw
	rm -Rf prevCw/tcw
	mv -f tcw prevCw/tcw
	
	echo "  Unroll the mediaWiki archive"
	tar xfz $uploadPath/mediaWiki.tgz
	mv $mediaWikiArchTopDir tcw || exit 13
	
	echo "  Next merge in the CW srcs"
	tar xvfj $uploadPath/cw.tbz2 || exit 11
	
	echo
	echo "  See what we got"
	cd tcw || exit 9
	ls -l
	echo
	ls -l extensions
	echo
	ls -l skins
	
	# use LIVE content only
	# no more echo "You still need to import the content"
	# echo "decide somehow and do it with exportAll.sh import"
	#echo "now, assuming theres no changes already up there,  load the pages"
	#./exportAll.sh import
GUTAUMTP


echo
echo "OK Done!  when you're happy with tcw.ti, move it to cw with cw/tcwPush.sh"
echo 
