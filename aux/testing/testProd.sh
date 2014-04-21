#!/bin/bash
# test production -- from this (client) machine, tests over net to production
# cw site, to make sure front page and view page work as intended and 
# don't change (or promote if they do)

if [ -z "$wAUX" ]
then export wAUX=`dirname $0`
fi
cd $wAUX
#echo TestProd from Directory $PWD

if [ -z "$1" ]
then
	echo "Usage:  $0  test   # runs tests" 1>&2
	echo "        $0  pro mainPage cwMainPage  # promotes given actual pages to ref pages" 1>&2
	echo "        $0  view mainPage cwMainPage.blah.blah  # view actual pages in Safari" 1>&2
	echo "        $0  view all   # view All actual pages in Safari" 1>&2
	exit 0
fi

if [ "$1" == 'pro' ]
then
	echo "special: promote given act files $* to ref files"
	while shift && [ -n "$1" ]
	do
		mv -iv tProdRefs/$1.act.html tProdRefs/$1.ref.html 
	done
	exit 0
fi

if [ "$1" == 'view' ]
then
	shift
	args="$@"
	echo "view actual html for ($args)"
	if [ "$args" == 'all' ]
	then
		cd tProdRefs
		args=`echo *.act.html`
		cd ..
	fi
	echo "that is, ($args)"
	for rName in $args
	do
		rName=`echo $rName|sed 's/\..*//' `
		open -a Safari file://$wAUX/tProdRefs/$rName.act.html
	done
	exit 0
fi



if [ "$1" != 'test' ]
then
	echo "subcommand $1 not found - run with no args for usage"
	exit 7
fi

# test please
nErrors=0

testOnePage ( )
{
	url="http://${1}tactileint.com/$2"
	refName=$3
	
	# retrieve file off of live, filter out some problematic lines, store.
	curl --silent --show-error $url -o tProdRefs/act.html
	sed -e '/REMOTE_PORT/d;/REQUEST_TIME/d' \
			-e '/Saved in parser cache with key/d' \
			-e '/This page has been accessed/d' \
			-e '/<!-- Served in /d' \
			tProdRefs/act.html \
			> tProdRefs/$refName.act.html
	
	diff tProdRefs/$refName.ref.html  tProdRefs/$refName.act.html \
		>  tProdRefs/$refName.diff.txt
	if [ -s tProdRefs/$refName.diff.txt ]
	then
		((nErrors++))
		echo '!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!' \
				Error $nErrors: page $refName is different.  First ten lines:
		head tProdRefs/$refName.diff.txt
		open -a Safari file://$wAUX/tProdRefs/$refName.diff.txt
		# works very well.  In the morning there's a 
		# page of gray text displaying in safari.
		# SOMEDAY: filter out the various caching moans 
		# and groans that show up in false positives.
	else
		rm tProdRefs/$refName.diff.txt
	fi
}


#mkdir -p tProdRefs

#testOnePage '' cw/index.php/Main_Page    mainPage
testOnePage 'cw.' index.php/Main_Page    cwMainPage

#testOnePage '' 'cw/index.php/View?ch=Arrays&langs=JavaScript,PHP,Ruby'    viewArrays
testOnePage 'cw.' '++Arrays/JavaScript,PHP,Ruby'    cwViewArrays

testOnePage 'cw.' '++Regexes/JavaScript,Python'  cwViewRegexes
testOnePage 'cw.' '++Conditionals/JavaScript,Python'  cwViewConditionals


ls -l tProdRefs
echo "$nErrors page errors detected"
