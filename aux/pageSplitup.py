#!/usr/bin/python 
# -m pdb

# hey this is handy if you're diffing them
#  diff -r wiki/pagesSplit cwDownld8Jan/pagesSplit > cwDownld8Jan.wdiff
#  grep 'diff -r wiki' cwDownld8Jan.wdiff | sed 's/diff -r/read -p next...; bbedit /'

import sys
import os
import re

wROOT = os.environ['wROOT']
os.chdir(wROOT)

def doSplitup():
	print "split.  Pages:"
	wikiIn = open(wROOT +'/CrossWise.xml', 'r')
	#print wikiIn
	if os.path.exists('pagesSplit'):
		sys.exit("pagesSplit directory already exists.  Please move it out of the way.")
	os.mkdir('pagesSplit')
	thisArticle = ''
	thisArticleName = '!start.xfrag'
	titlePat = re.compile('<title>(.*)</title>')
	wikiLine = wikiIn.readline()
	while wikiLine:
		####print wikiLine
		wText = wikiLine.strip()
		if '<page>' == wText:
			if thisArticleName == '!start.xfrag':
				pOut = open('pagesSplit/!start.xfrag', 'w')
				pOut.write(thisArticle)
				pOut.close()
				thisArticle = ''
				
			thisArticleName = None  # demand new name
			thisArticle += wikiLine  # the <page line
			
		elif '</page>' == wText:
			thisArticle += wikiLine  # the </page line
			pOut = open('pagesSplit/' + thisArticleName + '.wpage', 'w')
			pOut.write(thisArticle)
			pOut.close()
			
			thisArticle = ''
			thisArticleName = None
			
		else:
			mat = titlePat.match(wText)
			if mat:
				# a <title tag!  use this to name file
				thisArticleName = mat.group(1)
				# spaces and colons in filenames are awkward.  we'll have to change back on join.
				thisArticleName = re.sub(' ', '_', re.sub(':', '%', thisArticleName))
				print thisArticleName, 
			
			thisArticle += wikiLine  # another line of current article, even if a title
			
		wikiLine = wikiIn.readline()

	pOut = open('pagesSplit/~end.xfrag', 'w')
	pOut.write(thisArticle)
	pOut.close()
	thisArticle = ''
	


def doJoin():
	print "join"
	if os.path.exists('CrossWise.xml'):
		sys.exit("CrossWise.xml file already exists.  Please move it out of the way.")
		
	# fundamentally it's easy
	os.system('cat pagesSplit/* > CrossWise.xml')
	
	#fileNames = os.listdir('pagesSplit')
	#fileNames.sort()
	#xOut = open("CrossWise.xml", "w")

	#for thisArticleName in fileNames
	#		if thisArticleName != '!start.xfrag'
	#			xOut.write("  <page>\n")
	#		pIn = open('pagesSplit/' + thisArticleName + '.wpage', 'r')
	#		xOut.write(pIn.read())
	#		pIn.close()
	#		if thisArticleName != '~end.xfrag'
	#			xOut.write("  </page>\n")


	#xOut.write(thisArticle)
	#xOut.close()


def doTest():
	print "test - must have existingCrossWise.xml file and no CrossWise.xml file or pagesSplit dir."
	print "   this test will clean out any existing pagesSplit dir!!"
	if os.system('cp existingCrossWise.xml CrossWise.xml'):
		exit(3)
	os.system('rm -Rf pagesSplit')
	doSplitup()
	os.system('rm -i CrossWise.xml')
	doJoin()
	print '-' * 50 + ' differences'
	os.system('diff existingCrossWise.xml CrossWise.xml')
	print '-' * 50


#print sys.argv
cmd = ''
if len(sys.argv) > 1:
	cmd = sys.argv[1]
#print cmd

if 'split' == cmd:
	doSplitup()
		
elif 'join' == cmd:
	doJoin()

elif 'test' == cmd:
	doTest()

else:
	print "Usage:"
	print sys.argv[0] +' split   # divide up wROOT/CrossWise.xml into dir wROOT/pagesSplit'
	print sys.argv[0] +' join   # recombine dir wROOT/pagesSplit into wROOT/CrossWise.xml'



