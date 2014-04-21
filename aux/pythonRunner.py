#!/usr/bin/python 
# to debug: add this to end of prev line:    -m pdb

import sys, string

exceptions = []

# push most recent exception onto our queue here
def rememberExc():
	global exceptions
	exceptions += [[str(sys.exc_type), str(sys.exc_value)]]

# print out all exceptions and reset queue
def printoutExc():
	global exceptions
	rememberExc()
	for ex in exceptions:
		print "||Caught Exc: " + ex[0]
		print "||      "+ ex[1]
		#print "||      "+ str(sys.exc_traceback)
	exceptions = []

print "####"

while (True):
	line = sys.stdin.readline()
	line = string.strip(line)  # trim out newline at end or spaces at start
	if (line):
		try:
			res = eval(line)
			print ' ' + line
			print ' = ' + repr(res)
		except:
			rememberExc()
			try:
				exec(line)
				print ' ' + line
			except:
				print ' ' + line
				printoutExc()
		sys.stdin.seek(0)
	else:
		print "####"



