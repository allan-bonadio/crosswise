<?php
//
//  Feature Source  --  checking feature examples with a running source file in the language
//
 if(! defined('MEDIAWIKI')) { echo("\n"); die(-1);}

//////////////////////////////////////////////////////////////////////// Feature Source
// a Feature Source is a text file of source code in the target language, 
// that tests all the examples, and generates the raw Wiki text of 
// the feature page on stdout (or an analogous means)

// this will generate feature source for a feature, and do other 
// stuff like I dunno.
class cwFeatSrc {
	protected $example;  // collecting code text of example
	
	protected $serial;  // to distinguish multiple exs per rule
	
	// collect output source in these
	protected $sourceProcs = '';
	
	public function __construct(cwLang $lang) {
		$this->lang = $lang;
//var_export($lang);
//echo "<br>constructing a cwFeatSrc($lang->title)\n";
	}
	

	////////////////////////////////////////////////////////// latest line
	// many of these need to be done along with the most recent line of code in the example.
	// like " 5+6\n = 11".  This stuff saves/queues it and releases it when right.
	
	function resetLatestLine() {
		$this->latestLine = null;
	}
	
	// effectively, put out the line to both proc and output.   
	function saveLatestLine($line, $fake = '') {
		// Remove leading and trailing spaces, but just spaces not tabs cuz users 
		// can indent with real tab characters.
		$this->latestLine = $line ? preg_replace('/^ *(.*) *$/', '$1', $line) : null;
		$this->latestFake = $fake ? ' ' : '';
	}

	// effectively, put out the line to both proc and output.  or just output if fake.
	function flushLatestLine() {
//echo "flushLatestLine() ". ($this->latestLine === null ? 'null ll' : $this->latestLine)."\n";
flLog("flushLatestLine()... `". ($this->latestLine === null ? 'null' : $this->latestLine) .'`');
		if ($this->latestLine !== null) {
			if ($this->latestLine === '' || $this->latestLine == ' ')
				$this->exampleBlank();
			else
				$this->exampleStmt($this->latestLine, $this->latestFake);
		}
		$this->latestLine = null;
	}

	// you know that one example line we keep here?  retrieve it, 
	// but get rid of it in the 1-level queue to make sure it isn't taken again.
	// Pass $what if you really needed it, it'll go into a message.
	function takeLatestLine($what = false) {
		if (!$this->latestLine) {
			if ($what)
				$this->error("No previous line to $what with.");
			else
				$this->error("No previous line.");
			////debug_print_backtrace(); ////flTraceBack('take latest line');////
			return '';
		}
		$r = $this->latestLine;
		$this->latestLine = null;
		return $r;
	}

	////////////////////////////////////////////////////////// common primitives
	// one of these is called once for each line of example. 
	// that could be either a line of code 
	// or an expression, not one of the = forms.  The first space has already been 
	// trimmed off.  Line might be diverted into one of the = forms on the next line; it'll call takeLatestLine() if so.
	
	function exampleLine($line, $fake = '') {
//echo "exampleLine ". dumpText($line)."     BTWLL: `$this->latestLine`\n";
//flLog("exampleLine ". dumpText($line));
		$this->flushLatestLine();
		$this->saveLatestLine($line, $fake);
	}

	// use anywhere to signal an error in the output FS file and continue on
	function error($msg) {
			$this->parseErrors .= "!!!! Wiki Error on rule line $this->lineNum: $msg\n";
			
			// print previous line
			if ($this->lineNum > 0)
				$this->parseErrors .= '<br>line '. ($this->lineNum-1) .': '. $this->rLines[$this->lineNum-1] ."\n";
				
			// error line
			$this->parseErrors .= '<br>line '. $this->lineNum .': '. $this->rLines[$this->lineNum] ."\n";
			
			// and line after
			if ($this->lineNum+1 < count($this->rLines))
				$this->parseErrors .= '<br>line '. ($this->lineNum+1) .': '. $this->rLines[$this->lineNum+1] ."\n";
	}
	
	////////////////////////////////////////////////////////// process each kind of line
	// this line in the source starts with a space.  One of several forms; handle it.
	function processExampleLine($line, $needCode) {
//echo "processExampleLine ". dumpText($line) ." ,$needCode)  BTWLL: `$this->latestLine`\n";
//flLog("processExampleLine ". dumpText($line) ." ,$needCode)");
		if (!$this->inExample) {
			$this->startExample($needCode, $this->serial);
			$this->resetLatestLine();
		}
		$this->inExample = true;
	
		// here's where we interpret all the space-equals codes.
		// Note the fake ones never execute the code they just repeat them into the output
		if (strncmp(' = ', $line, 3) == 0) {
			// an ' = 5.5' result tpe
			$this->exampleAnonTest($this->takeLatestLine('compare'), substr($line, 3));
		}
		else if (strncmp('  = ', $line, 4) == 0) {
			// an extra space suppresses the actual test
			$this->exampleAnonTest($this->takeLatestLine('fake compare'), substr($line, 4), ' ');
		}
		else if (strncmp(' => ', $line, 4) == 0){
			// printing output
			$this->flushLatestLine();
			$this->examplePrintedTest(substr($line, 4));
		}
		else if (strncmp('  => ', $line, 5) == 0){
			// fake printing output
			$this->flushLatestLine();
			$this->examplePrintedTest(substr($line, 4), ' ');
		}
		else if (strncmp(' =! ', $line, 4) == 0){
			// an error message
			$this->exampleErrorTest($this->takeLatestLine('intercept message'), substr($line, 4));
		}
		else if (strncmp('  =! ', $line, 5) == 0){
			// a fake failure message.  dont run this, we,ll die
			$this->exampleErrorTest($this->takeLatestLine('run for message'), substr($line, 5), ' ');
		}
		else if (preg_match("/^ ( ?". $this->lang->varPat .")= (.*)$/", $line, $match)){
			// maybe it's a ' var= expression' result type.  $1=var, $2=expression.
			$this->flushLatestLine();
			$this->exampleNamedTest($match[1], $match[2]);
		}
		else if (strncmp('  ', $line, 2) == 0){
			// fake example text - just repeat it
			$this->exampleLine(substr($line, 1), ' ');
			if (!$needCode)
				$this->preface .= $line . "\n";
		}
		else {
			// plain example text - no = in line[1].  Could be an expression, or a whole statement.  
			// For some languages it doesn't matter.
			$this->exampleLine(substr($line, 1));
			if (!$needCode)
				$this->preface .= $line . "\n";
		}
	}
	
	// either a prose line, blank lines, or the end of the rule, 
	// but we gotta end any example.  If not in one, does nothing.
	function finishOngoingExample() {
//echo "finishOngoingExample()\n";
		if ($this->inExample) {
			if ($this->funnyExBlankState()) {
				$this->takeLatestLine('finishOngoingExample');
				$this->proseBlank();
			}
			else
				$this->flushLatestLine();
			$this->endExample();
			$this->serial++;
		}
		$this->inExample = false;
	}
	
	// one line of prose please
	function processProseLine($line) {
//echo "processProseLine ". dumpText($line)." ...    BTWLL: ". dumpText($this->latestLine)."   but before that:\n";
//flLog("processProseLine ". dumpText($line));
		$this->finishOngoingExample();
		if ($line === '' || $line == ' ')
			$this->proseBlank();
		else
			$this->proseLine($line);
//echo "processProseLine done with ". dumpText($line)."\n";
	}
	
	// wiki trims spaces off the ends of lines.   Therefore you can't have a line with just one space.  No it doesn't!!!
	function processExBlankLine() {
		// the wiki's rules about blank lines seems to be: if it's in between two 
		// pre-lines (start with space), then it's also a pre-line (therfore for us, part of the same example).
		if (!$this->funnyExBlankState()) {
//echo "processExBlankLine - funny ex blank state Start    BTWLL: ". dumpText($this->latestLine)."\n";
//flLog("processExBlankLine - funny ex blank state Start");
			// first blank line in example - maybe it should be in the example.
			// push out the previous line and get ready.
			$this->exampleLine('');  // turns on funnyExBlankState()
		}
		else {
//echo "processExBlankLine - funny ex blank state End & two lines\n";
//flLog("processExBlankLine - funny ex blank state End & two lines");
			// second blank line in a row - this is two prose lines
			$this->takeLatestLine('processExBlankLine');
			$this->proseBlank();
			$this->proseBlank();
		}
	}
	
	function funnyExBlankState() {
		return $this->inExample && $this->latestLine === '';
	}
	
	////////////////////////////////////////////////////////// parsing pages and rules
	// parse rule to pick out the examples and verify them
	function parseRule($needCode, $rawRule) {
////echo ";;;;;;;;;;;;;;;;;;;; parseRule('$needCode', ". dumpText($rawRule) .")\n";////
//debug_print_backtrace();

		$needTitle = codeToTitle($needCode);
		if ($needCode)
			$this->oneNeed($needTitle, $rawRule);  // just draws ===req===
		else
			$this->preface = '';  // the overview: construct the preface
		
		$this->rLines = explode("\n", $rawRule);
//echo "             ;;; parseRule exploded: ";var_export($this->rLines);
		if (array_pop($this->rLines) != '')
			die("no newline at end of rule $needCode");
			// always a newline at the end of every rule
		$this->serial = 0;
		$this->inExample = $this->altContext = false;
		$this->resetLatestLine();

		// unfortunately this linenum is within the rule, not the file
		foreach ($this->rLines as $this->lineNum => $line) {
//echo "             ;;; parseRule Line: ". dumpText($line)."\n";
//flLog("             ;;; parseRule Line: ". dumpText($line));
			if (strncmp($line, "<cwAltContext ", 14)) {
				$line = preg_replace("<cwAltContext .*/.*>", "", $line);
				$this->altContext = true;
			}
			if (strlen($line) <= 0 && $this->inExample)
				$this->processExBlankLine();
			else if (strlen($line) > 0 && $line[0] == ' ')
				$this->processExampleLine($line, $needCode);
			else
				$this->processProseLine($line);
		}
		$this->finishOngoingExample();
	}
	
	// generate all the source into this->featureSource.
	// Note we match the rule order;  rearranging them to fit the Req, maybe someday
	function genSrc(cwFeature $feat) {
//echo "genSrc(cwFeature $feat->featName)\n";
		$banner = "Feature Test Source ". $feat->featName . strftime(" generated %F %T %z on ") . $_SERVER["HTTP_HOST"];
		$this->parseErrors = '';
		$this->startGen($feat->reqCode, $banner);
//echo "genSrc.... ". dumpText($feat->overview)."\n";

		// create example preface & overview.  Always comes with one extra 
		// newline on the front and one too few on the end
		$this->parseRule('', substr($feat->overview, 1) ."\n");
		
		// now the real rules
		foreach ($feat->rules as $needCode => $rule)
			$this->parseRule($needCode, $rule);
		$this->endGen($feat->reqCode);
		$this->featureSource = $this->parseErrors . $this->featureSource;
	}
	
	// handle the file writing errors in a consistent manner
	function fsError($filename) {
//echo "fsError($filename)\n";
		global $cwPrevFeatureWikiSrc;
		$msg = "<h2>!!!! file $filename could not be generated:</h2>";
		$er = error_get_last();
		$msg .= "(". $er['type'] .") ". $er['message'] .'   '. $er['file'] .':'. $er['line'] ."\n";
		return array($msg, $this->featureSource, $cwPrevFeatureWikiSrc);
	}
	
	// generate the FeatureSource, and original wiki source, write it to files
	// of the correct names on the server, return the text as an array:
	// [0] = any error message or false if successful
	// [1] = the feature source text
	// [2] = the original wiki text
	// Also generate a parallel file with the original Wiki source 
	function doFeatSrcFile(cwFeature $feat) {
		global $cwFeatSrcDir, $cwPrevFeatureWikiSrc;
		
		// hmmm why are these urlencoded?  JScould have used unencoded.
		$this->srcFileName = $cwFeatSrcDir . rawurlencode($feat->featName) . $this->fileSuffix;
		$this->wikiFileName = $cwFeatSrcDir . rawurlencode($feat->featName) . '.wiki';
		
		// write out the original wiki raw text
		flLog("file_put_contents($this->wikiFileName, #cwPrevFeatureWikiSrc)");
		$nWikiBytes = file_put_contents($this->wikiFileName, $cwPrevFeatureWikiSrc);
		if ($nWikiBytes <= 0)
			return $this->fsError($this->wikiFileName);
		chmod($this->wikiFileName, 0777);
		
		// generate the feature-source file
		$this->genSrc($feat);
//echo "about to put contents to '". $this->srcFileName ."'\n";
//flLog("about to put contents to '". $this->srcFileName ."'");
		$nBytes = file_put_contents($this->srcFileName, $this->featureSource);
//echo "done with doFeatSrcFile($feat->reqCode) fs write, wasa return value:\n";
//flLog("done with doFeatSrcFile($feat->reqCode) fs write, wasa return value:");
//flExport($nBytes);
		if ($nBytes <= 0)
			return $this->fsError($this->srcFileName);
		@chmod($this->srcFileName, 0777);
		return array('', $this->featureSource, $cwPrevFeatureWikiSrc);
	}
}

//the automatic downloads screw  up if you do them too close together.  
// Instead, generate successive delay values for use by the JS.  In js time which is milliseconds.
function delayVal() {
	static $n = 4000;  // starts after
	
	$n += 3000;  // increments by
	return $n;
}

////////////////////////////////////////////////////// top level, flashy version

// generate the feature source for this combo of language & req
// and generate html for that (small) section of the Feature Source page.
// ONly called upon a submit of Feature SOurce page.
function srcLangFeature($langName, $reqCode) {
	global $wgRequest, $cwTestAllSrc;

	flLog("starting srcLangFeature($langName, $reqCode)");

	$feat = new cwFeature($langName, $reqCode);
	if (!isset($feat->rules))
		return "<span style=color:red>No such feature page $feat->featName, or feature test source not available.</span>";
	$fsRes = $feat->lang->featSrc()->doFeatSrcFile($feat);

	// the rest of this routine generates the wikitext for insertion into the FS page
	$content = 'Two files will download.  These are your Feature Test Source. ';

	if ($fsRes[0]) {
	   // an error!!  return red text
		$content .= "<div style='margin: 1em; padding:1em; border:8px red solid; color:red;'>Error:\n";
		$content .= $fsRes[0];
		$content .= "</div>";
		return $content;
	}

	// these will send headers Content-disposition: attachment
	// so it'll download, not surf to or execute
	$fileSuffix = substr($feat->lang->featSrc()->fileSuffix, 1);
	
	$shellCommandLine = str_replace('{{{REQ}}}', rawurldecode($reqCode), 
			$feat->lang->featSrc()->shellCommandLine);
	
	// avoid newlines in the js &html cuz they somehow turn into <p>
	global $wgServer;
	$content .= "<script>\n";
	$content .= "var amp = '&'.substr(0, 1);\n";  // yes mediawiki turns it to &amp;
	$rqc = rawurlencode($reqCode);
	
	// after a slight delay, download them both automatically
	$content .= "setTimeout(function() { location.href=
		'$wgServer/sourceDownload.php?language=$langName' +amp+ 'req=$rqc' +amp+ 'type='+'$fileSuffix'; }, ". delayVal() .");\n";
	$content .= "setTimeout(function() { location.href=
		'$wgServer/sourceDownload.php?language=$langName' +amp+ 'req=$rqc' +amp+ 'type='+'wiki';}, ". delayVal() .");\n";
	$content .= "</script>\n";
	//$content .= "<button onclick=location.href=cwubase+'$fileSuffix'>Download Code</button>";
	//$content .= "<button onclick=location.href=cwubase+'wiki'>Download Wiki</button>";
	$content .= "<br />$shellCommandLine\n";

	$content .= "<br>You will need both the .$fileSuffix file and the .wiki file. \n";
	$content .= "To generate another file pair, submit this again.\n";

	return $content;
}


function drawFeatureSourceForm($langName, $reqCode) {
	$langMenu = formatLangMenu($langName);
	$reqMenu = formatReqMenu($reqCode, true);  // Including 'all' item
	
	$content = '';
	$content .= "<form>";
	$content .= "language: $langMenu<br>";
	$content .= "requirement: $reqMenu<br>";

	$content .= "<input type=submit name=go value='GO'>\n";
	return $content . "</form>\n";
}

// called by the <cwFeatureSource> tag from the 'Help:Feature Source' page
function cwFeatureSource($input, $args, $parser) {
	global $wgRequest;
	global $allTheLangs;

	// get these arguments, at least to refill the form with
	$langName = $wgRequest->getVal('language', 'JavaScript');
	$reqCode = $wgRequest->getVal('req', 'Arrays');
	
	$content = '';
	$content .= "<div style='margin: 1em; padding:1em; border: 8px #546 solid; background-color: #eef;'>";
	$content .= "Choose the feature to generate:";
	$content .= drawFeatureSourceForm($langName, $reqCode);
	if ($wgRequest->getVal('go')) {
		if ($reqCode == '*') {
			// ALL requirements for given language
			$lang = $allTheLangs[$langName];
			$lang->featSrc()->startTestAll();
			$reqs = GetAllReqCodes();
			foreach ($reqs as $rc) {
				$content .= "<hr>\n";
				$content .= srcLangFeature($langName, $rc);
			}
			$content .= "<hr>\n";
			$content .= $lang->featSrc()->finishTestAll();

		}
		else
			$content .= srcLangFeature($langName, $reqCode);
	}
	$content .= "</div>\n";

	return $content;
}


