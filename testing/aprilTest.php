#!/usr/bin/php
<?
// test whatever in crosswise

require_once('config.php');
global $targetSite, $connectTimeout, $otherTimeout;

////function cwAssertFailure($file, $line, $expr) {
	////echo "cwAssertFailure callback($file, $line, $expr)\n";
////}

////assert_options(ASSERT_CALLBACK, "cwAssertFailure");

######################################################### our own assert system

function cwAssert($expr, $msg, $a = null, $b = null, $c = null) {
	if (! $expr) {
		printf($msg ."\n", $a, $b, $c);
	}
}

######################################################### http requests to cw server

global $httpHeaders, $httpWhat;

function httpRequestHeaders($curl, $text) {
	global $httpHeaders;
	$tText = rtrim($text);
	
	echo "httpRequestHeaders(`$tText`)\n";////
	if (! $tText)
		return strlen($text);  // empty header we always get i dunno
	
	$info = explode(': ', rtrim($text), 2);
	if (preg_match('|^HTTP/1.[01] (\d\d\d) (.*)$|', $info[0], $m)) {
		// the all-important status header anomaly
		////var_dump("it matched the http header!", $m);
		$httpHeaders['status'] = (int) $m[1];
	}
	else {
		$val = preg_replace('/^"(.+)"$/', '$1', $info[1]);  // remove quotes if any
		if (is_numeric($val))
			$val = (int) $val;
		
		// all keys casefolded to match
		$httpHeaders[strtolower($info[0])] = $val;
	}
	return strlen($text);
}

// uri must/should start with a slash (the one right after the domain name)
function httpRequest($uri, &$page, &$headers) {
	global $targetSite, $connectTimeout, $otherTimeout;

	echo "calling httpRequest($uri)\n";////
	$curl = curl_init($targetSite . $uri);

	// some good options
	//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $connectTimeout);  // in seconds
	curl_setopt($curl, CURLOPT_TIMEOUT, $otherTimeout);  // in seconds


	//curl_setopt($curl, CURLOPT_COOKIE, "fruit=apple; colour=red");  // maybe for testing
	//curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: text/plain', 'Content-length: 100'));  // maybe for testing

	// collect headers for kicks
	curl_setopt($curl, CURLOPT_HEADERFUNCTION, 'httpRequestHeaders');
	global $httpHeaders;
	$httpHeaders = array();

	echo "just before exec $uri...\n";
	$page = curl_exec($curl);
	echo "...just after exec $uri\n";
	if (! $page) {
		echo "Error retrieving '$uri': ". curl_error($curl) ."\n";
	}
	var_dump('the result: ', $httpHeaders, $page);
	
	curl_close($curl);
}


############################################################## good urls

// make sure these uris return something recognizable
function confirmPageWorks($uri, $sample) {
	httpRequest($uri, $result, $headers);
	cwAssert(strlen($result) > 100, "returned data from uri '$uri' is too short,  bytes=". strlen($result));
	cwAssert($headers['status'] == 200, "regular safe uri '$uri' gets error");
	cwAssert(stripos($sample, $result), "returned file from '$uri' doesn't match '$result'");
}

// test the front page, different urls
confirmPageWorks("", "a quick reference for computer languages");
confirmPageWorks("/", "a quick reference for computer languages");
confirmPageWorks("/index.php", "a quick reference for computer languages");
confirmPageWorks("/%2525phpinfo.php", "a quick reference for computer languages");
confirmPageWorks("/skins/crosswise/CrossWiseLogo.jpg", 'JFIF');
confirmPageWorks("/anushka.jpg", 'JFIF');



############################################################## good 404s
// test various error situations; make sure people can't execute random php files or return random other files

// from now on, all 404s must look like this one
global $headers404, $result404;
httpRequest("/blahfngbnhbmjkj.html", $result404, $headers404);
$headers404['date'] = null;  // always different
assert($headers['status'] == 404, "arbitrary blah .html file found");

// confirms that this uri gives a "not found' error with no clue as to the 
// finer details (cuz some of these actually exist but should not be sereved!)
function confirm404($uri) {
	global $headers404, $result404;
	
	httpRequest($uri, $result, $headers);
	$headers['date'] = null;
	cwAssert($headers['status'] == 404, "presumed unfound file wrongly found");

	// no clues should be in the page text
	cwAssert($result404 == $result, "404 text doesn't match");

	// and the headers should be exactly the same
	$missing = array_diff_assoc($headers404, $headers);
	cwAssert(empty($missing), "404 headers missing %s", var_export($missing, 1));
	$extra = array_diff_assoc($headers, $headers404);
	cwAssert(empty($extra), "404 headers extra %s", var_export($extra, 1));
}

confirm404('/foosliehrihvn.html');
confirm404('/index.html');
confirm404('/README');
confirm404('/FAQ');
confirm404('/includes/Html.php');
confirm404('/includes/blahblahblahalskdjf.php');
confirm404('/FAQ');Html.php

