<?php
// use it this way:
//    flLog('a debug memssage here');
//    flExport($anObjectOrArray);
//
// be sure to include/require it in LocalSettings.php!  Like this:
//     // my hack debug tracing; nice and simple.  DEV ONLY!!
//     include_once("$IP/extensions/footLog/footLog.body.php");
//     flLog("hi there foot log. Testink.  1 2 3 Testink.  ");
// Put it anywhere in LocalSettings, after the call to DefaultSettings.php



global $wgHooks;

$wgHooks['SkinAfterBottomScripts'][] = 'flAppendLog';
global $flLogAr;
$flLogAr = array();

function flLog($str) {
	global $flLogAr;
	$flLogAr[] = htmlentities($str, ENT_QUOTES, 'UTF-8');
}

function flExport($obj) {
	global $flLogAr;
	$flLogAr[] = htmlentities(var_export($obj, true), ENT_QUOTES, 'UTF-8');
}

function flTraceBack($title = 'some TraceBack') {
	flLog("++++++++++++ TraceBack: $title");
	foreach (debug_backtrace() as $i => $f) {
		flLog("$i ". (isset($f['class']) ? $f['class'] : '') ." ". 
			(isset($f['type']) ? $f['type'] : '') ." ". 
			(isset($f['function']) ? $f['function'] : '') ."     ...". substr(
			(isset($f['file']) ? $f['file'] : ''), -24));
	}
}

// need a flTraceback()
// var_dump(debug_backtrace());



// sticks on our log just before the page goes out
function flAppendLog($that, &$bottomScriptText) {
	global $flLogAr;
	
	$xtra = '<div style="background-color:#afe; padding:1em; border:1px solid; font-size:10pt; font-family:courier" >';
	$bottomScriptText .= $xtra . implode("<br />\n", $flLogAr) . '</div>';
	return true;
}



//flLog("right? he said 'right', didn't he?  <she> said \"ok\".  ");
//flLog('Right?');
//flLog('RIGHT?');
//flLog('right?');
//flExport($wgHooks);
//flExport(array('über' => 'over', 'ünter'=> 'under'));


