#!/bin/bash 

# make sure mysql & apache are up
if ! mysql -usolomon -psolomon --execute="select 'MySQL is Up!'"  cw_dev
then echo "Mysql server not up"
		 exit 3
fi
if ! curl http://localhost > /dev/null
then echo "Apache server not up"
		 exit 5
fi

cd $wROOT
if ! svn ls CrossWise.xml
then echo "SVN Server not up"
		exit 7
fi


updatePart ( )
{
	target=$2
	
	echo "----------------------------------------------------------- $1"
	echo "----------------------------------------------------------- $1"
	echo "----------------------------------------------------------- $1"
	sleep 1
	svn diff $target > diffs
	svn update $target || exit 5
	if [ -s diffs ]
	then
		more diffs
		read -p "Enter comment or ^C to not update/checkin... " comment
		svn ci $target -m "$comment" || exit 7
	fi
}

cd $wAUX
updatePart cwAux

# svn doesn't go into the db; dump it now
cd $wROOT
$wROOT/exportAll.sh export

# maybe i might wana make some mass search/replace changes
bbedit CrossWise.xml

updatePart "wiki content" CrossWise.xml

# is it worth it to load the wiki?  always if theres changes from the outside, 
# otherwise you can lose them cuz you didn't pick them up here.
echo "Now reloading the wiki..."
$wROOT/exportAll.sh import


cd $wROOT
updatePart "Main Src"

date
