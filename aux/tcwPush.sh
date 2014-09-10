#!/bin/bash 

echo OLD
exit


env

cd /home/tactile/public_html

echo "Move TCW staging sourcetree to CW production"
echo "current CW is at " `cat cw/version.txt`
echo "current TCW is at " `cat tcw/version.txt`
[ ! -d tcw ] && exit 15   # make sure

echo " --- doing it.  OK slide over, delete old versions"
rm -Rf prevCw/penult
mv -v prevCw/prev prevCw/penult
echo " --- done with old versions"

echo "now quickly..."
mv -v cw prevCw/prev || exit 13
mv -v  tcw cw || exit 17

echo rebuild sitemap
curl 'http://cw.tactileint.com/--Verification?sitemap=checked&go=GO' > /dev/null

echo "OK Done.  Now try it out."
ls -ld cw prevCw/*
if [ -d tcw ]
then
	echo tcw still exists
	ls -dl tcw
	ls tcw
fi


