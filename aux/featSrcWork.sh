#!/bin/bash 

langName=$1
reqCode=`echo $2 | sed 's/ /_/g' `
featCode=$1_$reqCode
if [ -z "$reqCode" ]
then
	echo "Usage:   $0 PHP Dates_and_Times    # separate with space "
	exit 1
fi
echo "Feature Source for feature '$featCode' (lang '$langName' req '$reqCode'"

cd $wAUX

case "$langName" in
JavaScript)
	runCmd='open -a Safari '
	suffix=.html
	;;
	
PHP)
	runCmd='php '
	suffix=.php
	;;
	
Ruby)
	runCmd='ruby '
	suffix=.rb
	;;

*)	echo "!!!! Error: Unknown Language $langName";
	exit 17
esac

# config settings: feel free to tinker
urlStart=http://cw.${CWHOST:-tactileint.com}
editCmd=bbedit

# get the original source
curl "$urlStart/iedit.php?auth=ravvstilllabclog&action=getPage&page=$featCode" > fSrc/$featCode.wiki || exit 7
echo "Deposited wiki source in '$featCode.wiki'"

# detect and announce if theres an error and quit
if grep '!!!!Error: ' fSrc/$featCode.wiki
then exit 9
fi
$editCmd fSrc/$featCode.wiki


# send the wiki file up, generate feature source from that
getFeatureSrc ( )
{
	# curl has a problem with filenames I think with commas and who knows what else
	cp fSrc/$featCode.wiki test.wiki
	fileArg=wikiSrc=@test.wiki
	urlArg="$urlStart/iedit.php?auth=ravvstilllabclog&action=featSrc&feature=$featCode"
	curl --progress-bar  --form $fileArg  $urlArg > fSrc/$featCode$suffix
		
	$editCmd fSrc/$featCode$suffix
	date '+%m/%d/%y %I:%M:%S %P'
}

# loop to try regenerating and running till ^C or big failure
while :
do
	getFeatureSrc
	$runCmd fSrc/$featCode$suffix > fSrc/$featCode.out 2>&1
	if [ $langName != 'JavaScript' ]
	then
		sleep 1
		$editCmd fSrc/$featCode.out
	fi
	read -p "Hit return to redo it or ^C to quit: "
done

