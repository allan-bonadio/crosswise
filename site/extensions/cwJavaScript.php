<?php
//
//  JavaScript Feature Source  --  generate FS for JS
//
 if(! defined('MEDIAWIKI')) { echo("\n"); die(-1);}


// single quote escape.  Given that we're going to put  this 
// string inside a pair of single quotes, then insert it into source code,
// prepare it by backslashing the ones that are already there.
function jssqe($code) {
	// change each backslash to two.  then change each appost to backsl appost
	$code = str_replace('\\', '\\\\', $code);
	return str_replace('\'', '\\\'', $code);
}


// generate the JavaScript source for a feature
class cwJavaScriptSrc extends cwFeatSrc {
	public $fileSuffix = '.html';
	public $shellCommandLine = 'Then open this downloaded file in your browser:
			<br><kbd>JavaScript_{{{REQ}}}.html</kbd><br>
			(You can just drag it in.)  It should display without any !!!!s.  Eventally you should be able to copy it out (as text) and that file should compare exactly equal to the other downloaded file <kbd>PHP_{{{REQ}}}.wiki</kbd>';

	//////////////////////////////////////////////// start and end of file
	function startGen($reqCode, $banner) {
		// cwToStr(): I wanted to make this a method on the various objects 
		// but 'undefined' wasnt an object.
		// all the stuff  at the top of the file
		$this->featureSource = <<<EOSP
<html><head>
    <title>$banner</title>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
</head><body>
<pre style='white-space:pre-wrap;'>
<script type='text/javascript'>
function cwToStr(val) {
	switch (typeof val) {
	case 'undefined':
		return 'undefined';

	case 'boolean':
	case 'number':
	case 'function':
		return val .toString();
		break;

	case 'string':
		return "'"+ val.replace(/[\\'"]/g, '\\\$&').replace(/\\n/mg, '\\n').replace(/\\t/mg, '\\t') +"'";

	case 'object':
		if (null === val) return 'null';
		if (val.constructor == Array) {
			var r = '';
			for (i = 0; i < val.length; i++)
				r += cwToStr(val[i]) +', ';
			return '['+ r.substr(0, r.length-2) +']';
		}
		else {
			var r = '';
			for (k in val) {
				r += k +': '+ cwToStr(val[k]) +', ';
			}
			return '{'+ r.substr(0, r.length-2) +'}';
		}
	}
}

function objUnEqual(a, b) {
	if (a.constructor !== b.constructor)
		return true;
	
	for (key in a) {
		if (cwUnEqual(a[key], b[key]))
			return true;
	}

	// arrays are assumed to have integer indices from 0 to just before .length
	// but the ones set to undefined are assumed the same as absent ones.
	// but for(key in array) does make a distinction.  So try in both directions.
	if (a.constructor == Array) {
		for (key in b) {
			if (cwUnEqual(a[key], b[key]))
				return true;
		}
	}

	// dates don't have any apparent members but they do have an internal value.
	if (a.constructor == Date) {
		if (cwUnEqual(a.getTime(), b.getTime()))
			return true;
	}

	return false;
}

function cwUnEqual(actual, expected) {
	if (actual === expected)
		return false;
	if (typeof actual != typeof expected)
		return true;
	
	switch (typeof actual) {
	case 'number': if (isNaN(actual) && isNaN(expected)) return false;
	case 'undefined': return false;
	case 'string': return actual !== expected;
	
	case 'function':
		if (actual.toString() != expected.toString())
			return true;
			
	case 'object':
		return objUnEqual(actual, expected);
	default:
		return true;
	}
}

function cwVerEqual(actual, expected, lineNo) {
	if  (cwUnEqual(actual, expected))
		document.writeln("!!\\41! test failed, should be\\n = "+ 
				cwToStr(actual) +"\\nrather than "+ cwToStr(expected));
}

function ___(text) {
	if (!text) text = '';
	document.writeln(text);
}

function rule______________________________(needName) {
	document.writeln("==="+ needName +"===");
}

function cwConsole() {
	this.reset();
}
cwConsole.prototype = {
	log: function(str) {
		this.printText += str + "\\n";
		this.totalPrintout += str + "\\n";
	}, 
	dir: function(val) {
		this.printText += cwToStr(val) + "\\n";
		this.totalPrintout += cwToStr(val) + "\\n";
	}, 
	verPrint: function(expected) {
		var s = this.printText.split("\\n");
		var thisLine = s.shift();
		this.printText = s.join('\\n');
		if (thisLine.replace(/^\s*/, '').replace(/\s*$/, '') != expected.replace(/^\s*/, '').replace(/\s*$/, '')) {
			document.writeln("!!\\41! test of print line failed, should be\\n => "+
				(thisLine ? thisLine : "  ...nothing printed!") +
				"\\nrather than `"+ expected +"`");
			this.hadError = true;
		}
	},
	reset: function() {
		if (this.hadError)
			document.writeln("!!!! See previous errors.  Total printout of last example: \\n`"+ 
				this.totalPrintout +"`");
		this.printText = '';
		this.totalPrintout = '';
		this.hadError = false;
	}, 
}
var origConsole = console;
console = new cwConsole();

function cwRun(func) {
	try {
		func();
	}
	catch (exc) {
		document.write("!!\\41! exception in example.  ");
		if (exc.name) document.write(exc.name +': ');
		if (exc.message) document.writeln(exc.message);
		if (exc.srcURL) {
			document.write(exc.srcURL);
			if (exc.line) document.writeln(':'+ exc.line);
		}
		//debugger();
	}
}

document.writeln("&lt;cwStartFeature ch='$reqCode' /&gt;");

EOSP;
	}
	
	// end of whole file
	function endGen($reqCode) {
		$arfl = drawAllReqFeatLinks($reqCode);
		$this->featureSource .= <<<EOSP
document.writeln("&lt;cwEndFeature ch='$reqCode' /&gt;");
document.writeln("$arfl");
document.writeln("[[Category:JavaScript]] [[Category:$reqCode]]");
</script>
</pre>
</body></html>
EOSP;

		global $testAllSrc, $testAllJS;
		if ($testAllSrc) {
			// it's a TEST ALL.  Toss in this file's contribution.
			$testAllSrc .= "<a href=JavaScript_$reqCode.html target=_blank>JavaScript_$reqCode</a><br>\n";
			$testAllJS .= "	open('JavaScript_$reqCode.html', '_blank');\n";
		}
	}
	
	//////////////////////////////////////////////// an Example
	// start one example in a rule
	function startExample($needCode, $serial) {
		flLog(";;;; JavaScript startExample($needCode, $serial) , splen=". strlen($this->featureSource));
		$this->procName = 'runEx_'. preg_replace('|[ -/:-@\]-`{-\xFF]|', 
			'_', $needCode) .'_'. $serial;
		$this->featureSource .= "function {$this->procName}() {\n";
		$this->featureSource .= $this->preface;  // example text from overview
		
	}
	
	function endExample() {
		// just in case the last example ended with an expression
		$this->exampleProc .= "   ;\n";
		// end the proc, run the proc
		$this->exampleProc .= "console.reset();\n";
		$this->exampleProc .= "}\n";
		$this->exampleProc .= "cwRun(". $this->procName .");\n\n";
		$this->featureSource .= $this->exampleProc;
		$this->exampleProc = null;
	}
	
	function exampleStmt($line, $fake = '') {
flLog("^^^exampleStmt($line, '$fake')");////
		$this->featureSource .= "___(' ". jssqe($line) . "');\n";
		if (! $fake) $this->exampleProc .= $line . "\n";
	}
	
	function exampleBlank() {
flLog("^^^exampleBlank()");////
		$this->featureSource .= "___();\n";
		$this->exampleProc .= "\n";
	}
	
	// anon test:  ^ expr      ^ =equivalent
	function exampleAnonTest($testExpr, $expected, $fake = '') {
		if (! $fake && substr($testExpr, -1, 1) == ';')
			$this->parseErrors .= "!!!! First half of test expr '$testExpr' should not end in semicolon!!!!\n";
		$this->featureSource .= "___(' $fake" . jssqe($testExpr) . "');\n";
		$this->featureSource .= "___(' $fake= " . jssqe($expected) . "');\n";
		if (! $fake) $this->exampleProc .= "  cwVerEqual($testExpr, $expected);\n";
	}
	
	function exampleNamedTest($varName, $expected, $fake = '') {
		////$this->featureSource .= "___(' $fake" . jssqe($testExpr) . "');\n";
		$this->featureSource .= "___(' $fake$varName= " . jssqe($expected) . "');\n";
		if (! $fake) $this->exampleProc .= "  cwVerEqual($varName, $expected);\n";
	}
	
	function examplePrintedTest($expected, $fake = '') {
flLog("^^^examplePrintedTest($expected, '$fake')");////
		//$this->featureSource .= "___(' $fake" . jssqe($testExpr) . "');\n";
		$this->featureSource .= "___(' $fake=> " . jssqe($expected) . "');\n";
		if (! $fake) $this->exampleProc .= "  console.verPrint('". jssqe($expected) ."');\n";
	}
	
	function exampleErrorTest($testLine, $expected, $fake = '') {
		$this->featureSource .= "___(' $fake" . jssqe($testLine) . "');\n";
		$this->featureSource .= "___(' $fake=! " . jssqe($expected) . "');\n";
		
		if (! $fake) {
			$this->exampleProc .= "try {\n";
			$this->exampleProc .= "      $testLine\n";
			$this->exampleProc .= "      ___('!!!! Failure: test in ". $this->procName 
					." incorrectly succeeded!  No error or message `". 
					jssqe($expected) ."`');\n";
			$this->exampleProc .= "} catch (exc) {\n";
			$this->exampleProc .= "      if (exc.message.indexOf('". jssqe($expected) ."') == -1)\n";
			$this->exampleProc .= "          ___('failure test in $this->procName failed, message `'+ exc.message +'` didn`t include `$expected` as it should');\n";
			$this->exampleProc .= "}\n";
		}
	}
	
	//////////////////////////////////////////////// TestAll file
	// generating the testall script, an .html file that launches one page for each feature
	
	function startTestAll() {
		global $testAllSrc, $testAllJS;
		
		$testAllSrc = <<<F_T_A_S
			<html><body>
F_T_A_S;

		$testAllJS = <<<J_T_A_S
			<script>
			function testAll() {
J_T_A_S;

	}
	
	// returns snippet of html for the web page for this request 
	// (unrelated to the .html files we're generating here)
	function finishTestAll() {
		global $testAllSrc, $testAllJS;
		
		// end the column of individual links
		$testAllSrc .= <<<J_T_A_E

			<p>
			<a href=# onclick=testAll() style=font-size:2em;color:red;>Test All</a><br>
J_T_A_E;

		// the JS for TestAll
		$testAllSrc .= $testAllJS;
		$testAllSrc .= <<<F_T_A_E
			}
			</script>
			</body></html>
F_T_A_E;

		global $cwFeatSrcDir;
		$fn = $cwFeatSrcDir . 'JavaScript_TestAll.html';
		flLog("Writing out file $fn with ". strlen($testAllSrc) ." bytes.");
		flLog("file_put_contents($fn, #testAllSrc)");
		file_put_contents($fn, $testAllSrc);
		@chmod($fn, 0777);

		// this should cause the testall file to be downloaded automatically
		return "<script>setTimeout(function() { location.href='$wgServer/sourceDownload.php?language=JavaScript' +amp+ 'req=TestAll' +amp+ 'type=html';alert('downloads done');}, ". delayVal() .");</script>
			Wait for all files to download.  Run the file JavaScript_TestAll.html to launch all the tests.\n";
	}
	
	//////////////////////////////////////////////// other
	function oneNeed($needCode, $rawRule) {
		$this->featureSource .= "rule______________________________('$needCode');\n";
	}
	
	function proseLine($rawLine) {
		$this->featureSource .= "___('" .  jssqe($rawLine) . "');\n";
	}
	
	function proseBlank() {
		$this->featureSource .= "___();\n";
	}
}

/* How to test the embedded code:
wrap this stuff around the startExample code:


<body onload="doIt()">

<script>


// IN HERE!!!

document.writeln(cwToStr('this is \\ \' \" " a string\n'));
document.writeln(cwToStr({running: 'tie', your: 'shoes'}));
document.writeln(cwToStr([3,4,5,'shot', 'keys', 'hat']));
document.writeln("<hr />");
document.writeln(cwToStr(null));
document.writeln(cwToStr(false));

var g = {running: 'tie', your: 'shoes'};
g[2] = 'bebe';
g[3] = 'rebozo';
document.writeln(cwToStr(g));

var match = /c(o)r(n)/.exec("corn, corn, i love corn");
document.writeln(cwToStr(match));
console.dir(match);



older:
var output = '';
function tsst(a, b) {
	output += a + (cwUnEqual(a, b) ? '!=' : '==') + b + '<br>\n';
}

function doIt() {
	tsst(4, 4);
	tsst(5, 4);
	tsst('five', 4);
	tsst('five', 'five');
	tsst('five', 'FIVE');
	tsst('five', 'fivE');
	tsst([], []);
	tsst([1,2,3], []);
	tsst([1,2,3], [1,2,3]);
	tsst([], []);
	tsst('five', 4);
	tsst('five', 4);

	document.getElementById('hog').innerHTML = output;
}

</script>

<div id=hog ></div>


</body>

*/
