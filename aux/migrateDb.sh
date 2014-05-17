#!/bin/bash

echo "Exports or Imports the content to/from CrossWise"
echo "   in the process, deletes contributor names & dates"
cd `dirname $0`

if [ `id -u` != "0" ]
then
	echo Must run as root, now doing a sudo
	sudo $0 $*
	exit $?
fi


case `uname -n` in
kashmir)	urlStart='http://10.0.0.95/~allan/wiki'
				temp='/Volumes/orissa'
				wAUX=~/Sites/wiki
				#importSurrogate="open $wAUX"
				#surfToSurrogate='open -a Safari'
				;;

cajamarca*)	urlStart='http://localhost/~allan/wiki'
				temp='/junk'
				wAUX=~/Sites/wiki
				#importSurrogate="open $wAUX"
				#surfToSurrogate='open -a Safari'
				;;

prague*)	urlStart='http://localhost/~allan/wiki'
				temp='/tmp'
				wAUX=~/public_html/wiki
				#importSurrogate="echo 'import this file:  $wAUX/cwImport.xml'"
				#surfToSurrogate='echo "surf to:   " '
				;;

santiago*)	urlStart='http://cw/'
				temp='/tmp'
				wAUX=/dvl/crosswise/aux
				wROOT=/dvl/crosswise/site
				#importSurrogate="echo 'import this file:  $wAUX/cwImport.xml'"
				#surfToSurrogate='echo "surf to:   " '
				;;

flores*)	urlStart='http://cw/~allan/wiki'
				temp='/tmp'
				wAUX=/dvl/crosswise/aux
				wROOT=/dvl/crosswise/site
				#importSurrogate="echo 'import this file:  $wAUX/cwImport.xml'"
				#surfToSurrogate='echo "surf to:   " '
				;;

pepper.he.net)	urlStart='http://tactileint.com/cw'
				temp='/tmp'
				wAUX=`dirname $0`
				echo "Choosing the distro at " $wAUX
				
				#importSurrogate="echo 'import this file:  $wAUX/cwImport.xml'"
				#surfToSurrogate='echo "surf to:   " '
				if [ "$1" = 'dump' ]
				then
					echo "don't run this on the server; run it on the workstation"
					exit
				fi
				;;

*)				echo "do not recognize uname -n value:" `uname -n`
				exit 5
esac
xmlFile=$wAUX/CrossWise.xml
tempFile=$wAUX/cwRawTemp.xml
ptbuFile=$wAUX/cwPagesToBackUp.txt
if ls -l "$xmlFile"
then echo
else exit
fi

# get db dump from Wiki by http.  Save XML file and wipe out some of the unneeded
# but constantly changing values.  Save in $xmlFile
doExport ( )
{
	# start with the list of pages to back up.  In a consistent order.  Must be like:
	#   'pages=page1%0Apage2%0Apage3%0A...'
	curl "$urlStart/index.php?title=CrossWise:PagesToBackUp&action=render" \
		| sed -e '1d' -e '/@@@@/,$d' \
		| sort \
		| tr '\n' '%' \
		| sed -e 's/%/%0A/g' -e 's/&amp;/%26/g' -e 's/^/pages=/' \
		> $ptbuFile
	echo "Got list of pages, $ptbuFile   rc=$?"
	if [ ! -s $ptbuFile ]
	then echo "No pages to export!!!!"
			exit 13
	fi
	
	# now get the data itself.  
	# Get rid of all id tags (page, rev, user), nobody uses them & they tax svn.
	# Neuter all timestamp tags; they tax svn too & hard to see changes.
	# also these change annoyingly: base, minor, comment
	# also make mass changes that keep on changing back
	curl --data @$ptbuFile \
		--data curonly=1 \
		"$urlStart/index.php?title=Special:Export&action=submit" > prelimDeleteMe.xml
	
	cat prelimDeleteMe.xml \
		| sed -e '/^ *<id>[0-9]*<.id>$/d'    -e 's|<timestamp>[-:TZ0-9]*</timestamp>|<ts />|'   \
		| sed -e '/^ *<base>.*<.base>$/d'  \
		| sed -e '/^ *<comment>.*<.comment>$/d'  \
		| sed -e '/^ *<username>.*<.username>$/d'  \
		| sed -e '/^ *<contributor>$/d'  \
		| sed -e '/^ *<\/contributor>$/d'  \
		| sed -e '/^ *<ip>.*<.ip>$/d'  \
		| sed -e '/^ *<ip \/>$/d'  \
		| sed -e '/^ *<minor.>$/d'  \
		| sed -e 's/Feature name=/Feature ch=/'  \
		> $xmlFile
		
	echo "Did download, rc=$?, to $xmlFile"
	echo "You can delete prelimDeleteMe.xml now:     rm prelimDeleteMe.xml"
	
	# show off
	echo
	echo
	grep '<title' $xmlFile \
		| sed -e 's|<title>||g' -e 's|</title>||g' -e 's/    /	/g' -e 's/ /_/g' \
		| tr '\n' '\t'
	echo
	echo
	
	echo "Total pages:" `grep '<title' $xmlFile | wc -l`
	echo "Categ pages:" `grep '<title>Category:' $xmlFile | wc -l`
}

# take xmlFile and import it th right way, whole thing.
doImport ( )
{
	# first put timestamps back into file.  This is how wiki import knows to replace pages.
	ts=`date -u +%FT%TZ`
	cat $xmlFile | sed "s|<ts />|<timestamp>$ts</timestamp>|" > $tempFile

	# defines $wgDBadminuser $wgDBadminpassword among others
	# no, the maint commands get it from LocalSettings.php   source /etc/tactileint/crosswise_keys.sh	

	# the proper way
	cd $wROOT
	php maintenance/importDump.php --server=http://cw $tempFile 
	#php maintenance/importDump.php --dbuser $wgDBadminuser --dbpass "$wgDBadminpassword" $tempFile 
####	echo "importDump script starting in background in pid=$$.  It will take a few mins."

	# this never works.  Need authentication and stuff.
	#curl --form xmlinput=@$tempFile \
	#	--form source=upload --form logcomment=exportAll  --form 'submit=Upload File'  \
	#	"$urlStart/index.php?title=Special:Import&action=submit"  > um...
	#echo "Did download, rc=$?, to $xmlFile"
	
	# and the browser hack is screwy on Linux cuz FF cant be commanded 
	# to go to a url, and it always requires that manual step, 
	# and it often runs out of ex time.
			#$importSurrogate
			#echo
			#echo "OK in that window, look for $tempFile and import it."
			#echo
			
			#$surfToSurrogate http://localhost/~allan/wiki/index.php/Special:Import
}

doDump ( )
{
	# dump whole db out to this file
	echo
	echo "ssh-ing into server"
	ssh tactile@pepper.he.net <<DUMP_TACTILE_INDUSTRIES
		cd ~/cwAux
		echo "starting mysqldump"
		mysqldump  -h localhost -u tactile \
			--complete-insert  "-pyaw tiye xibwa" --hex-blob \
			--routines --triggers --dump-date  \
			tactile > tactileDump.sql
		echo "finished mysqldump, starting gzip"
		rm -fv tactileDump.sql.gz
		gzip tactileDump.sql
DUMP_TACTILE_INDUSTRIES

	bkName=tactileDumpR$(( $RANDOM & 3 )).sql.gz
	rm -fv tactileDump.sql.gz tactileDump.sql
	echo "About to download compressed sql"
	sftp tactile@pepper.he.net <<DUMP_TACTILE_RAILROADS
		cd /home/tactile/cwAux
		get tactileDump.sql.gz
		rm $bkName
		rename tactileDump.sql.gz $bkName
DUMP_TACTILE_RAILROADS
	gunzip tactileDump.sql.gz
	
	echo "dumped:"
	ls -l tactileDump*.sql
	
	echo 
	echo "Want me to load into local MySQL?"
	read -p "^C if not: " loadInto
	
	time cat tactileDump.sql | rmysql cw_dev
}




case "$1" in
im*)	doImport
			;;
			
ex*)	doExport
			;;
			
du*)	doDump
			;;
			
*)			echo "Usage:"
			echo "  \$ $0 import  # read aux/CrossWise.xml file into this wiki"
			echo "  \$ $0 export  # write CrossWise.xml file out from this wiki"
			echo "  \$ $0 dump  # dump whole database - as for a backup"
			;;
esac

