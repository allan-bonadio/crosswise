<?php
//
//  PHP Feature Source  --  generate FS for PHP
//
 if(! defined('MEDIAWIKI')) { echo("\n"); die(-1);}

// single quote escape.  Given that we're going to put  this 
// string inside a pair of single quotes, then insert it into source code,
// prepare it by backslashing the ones that are already there.
function phpsqe($code) {
	// change each backslash to two.  then change each appost to backsl appost
	$code = str_replace('\\', '\\\\', $code);
	return str_replace('\'', '\\\'', $code);
}



// generate the PHP source for a feature
class cwPHPSrc extends cwFeatSrc {
	public $fileSuffix = '.php';
	public $shellCommandLine = 'Then run this command:<br />
			<kbd>$ php PHP_{{{REQ}}}.php | diff - PHP_{{{REQ}}}.wiki</kbd>';

	//////////////////////////////////////////////// start and end of file
	function startGen($reqCode, $banner) {
		// all the stuff  at the top of the file
		$this->featureSource = <<<EOSP
<?php
// $banner
function erHand(\$no, \$str, \$file, \$line) {
	global \$cwRecentErrorMsg;
	\$cwRecentErrorMsg = \$str;
    return true;
}
set_error_handler("erHand");

function toStr(\$value) {
   if (is_array(\$value)) {
      \$out = 'array(';
      for (\$ix = 0; isset(\$value[\$ix]); \$ix++)
         \$out .= toStr(\$value[\$ix]) . ', ';
      foreach (\$value as \$n => \$el) {
         if (!is_int(\$n) || \$n < 0 || \$n >= \$ix)
            \$out .= \$n .'=>'. toStr(\$el) . ', ';
      }
      return substr(\$out, 0, -2) .')';
   }
   if (\$value instanceof DateTime)
      return strftime('"%d-%b-%Y %T"', \$value->getTimestamp());
   return var_export(\$value, true);
}

function unEqual(\$actual, \$expected) {
   global \$cwErrorText;////
   if (\$actual instanceof DateTime)
      return date_timestamp_get(\$actual) != strtotime(\$expected);
   else {
      ////\$cwErrorText .= "gonna check actual vs expected \$actual, \$expected\\n";
      switch (gettype(\$actual)) {
      
      case 'double':
      	// we want -is same as- not 'equal'
		////\$cwErrorText .= "   doubles: check for nans\\n";////
        if (is_nan(\$actual) && is_nan(\$expected))
            return false;
		////\$cwErrorText .= "   doubles: check for infinite ". is_infinite(\$actual) .','. is_infinite(\$expected).','. (0 < \$expected) .','. (0 < \$actual) ."<\\n";////
        if (is_infinite(\$actual) && is_infinite(\$expected))
            return (0 < \$expected) != (0 < \$actual);
		////\$cwErrorText .= "   doubles: check for zeroes\\n";////
        if (\$actual == 0 || \$expected == 0 || !is_finite(\$actual) || !is_finite(\$expected))
           return \$actual != \$expected;
        \$mag = (abs(\$actual) + abs(\$expected)) * 1e-13;
		////\$cwErrorText .= "   doubles:". abs(\$actual - \$expected) .','. \$mag ."\\n";////
        return abs(\$actual - \$expected) > \$mag;

      case 'string':
        if (!is_string(\$expected))
            return true;
        return \$actual != \$expected;

      // php equality seems to handle these alright
      case 'array':
        if (!is_array(\$expected))
           return true;
        return \$actual != \$expected;

      case 'object':
        if (!is_object(\$expected))
           return true;
        return \$actual != \$expected;

      case 'boolean':
      case 'integer':
		////\$cwErrorText .= "   bool ints:" . is_int(\$actual) .','. is_bool(\$actual) .','. is_int(\$expected) .','. is_bool(\$expected) .','."\\n";////
        if (gettype(\$expected) != gettype(\$expected))
           return true;
        return \$actual != \$expected;

      default:
        return \$actual != \$expected;
      }
   }
}

function verEqual(\$actual, \$expected, \$lineNo) {
   global \$cwErrorText;
   if  (unEqual(\$actual, \$expected))
      \$cwErrorText .= "!!\\41! test in ". __FILE__ .':'. \$lineNo ." failed, should be\\n = ". 
            toStr(\$actual) ."\\n!!\\41! rather than ". toStr(\$expected) ."\\n";
}

function ___(\$text = '') {
   echo \$text . "\\n";
}

function rule____________________________________(\$needName) {
   echo "===" . \$needName . "===\\n";
}


function verPrint(\$expected, \$lineNo) {
   global \$cwPrintText, \$cwErrorText;
   \$cwPrintText .= ob_get_clean();
   list(\$actual, \$cwPrintText) = explode("\\n", \$cwPrintText, 2);
   if  (unEqual(rtrim(\$actual), rtrim(\$expected)))
      \$cwErrorText .= "\\n!!\\41! print test in ". __FILE__ .':'. \$lineNo ." failed, should be\\n". 
            " => ". \$actual ."\\n".
            "!!\\41! rather than ". \$expected ."\\n\\n";
}

// run, catching stdout and errors
function cwRun(\$proc) {
   global \$cwPrintText, \$cwErrorText;
   \$cwPrintText = \$cwErrorText = '';
   ob_start();
   \$proc();
   \$cwPrintText .= ob_get_clean();
   ob_end_clean();
   echo \$cwErrorText;
}

echo "<cwStartFeature ch='$reqCode' />\\n";

EOSP;
	}
	
	function endGen($reqCode) {
		$arfl = drawAllReqFeatLinks($reqCode);
		$this->featureSource .= <<<EOSP
echo "<cwEndFeature ch='$reqCode' />\\n";
echo "$arfl\\n";
echo "[[Category:PHP]] [[Category:$reqCode]]";

EOSP;
	}
	
	//////////////////////////////////////////////// an Example
	function startExample($needCode, $serial) {
flLog("function startExample($needCode, $serial)\n");
//echo(";;;;;;;;;;;;;;;;;;;; PHP startExample($needCode, $serial) , splen=". strlen($this->featureSource) ."\n");
		$this->procName = 'runEx_'. preg_replace('|[ -/:-@\]-`{-\xFF]|', '_', $needCode) .'_'. $serial;
		$this->exampleProc = "function {$this->procName}() {\n";
		$this->exampleProc .= $this->preface;  // example text from overview
	}
	
	function endExample() {
flLog("function endExample()\n");
		// just in case the last example ended with an expression
		$this->exampleProc .= "   ;\n";
		// end the proc, run the proc
		$this->exampleProc .= "}\n";
//echo "PHPEndExample: example  proc goin out: ". dumpText($this->exampleProc) ."\n";
		$this->featureSource .= $this->exampleProc;
		$this->exampleProc = null;
		$this->featureSource .= "cwRun('". $this->procName ."');\n\n";
	}
	
	//////////////////////////////////////////////// Lines in an Example
	// we got code but prefixed with two spaces.  Means just imagine that you run it.
	
	function exampleStmt($line, $fake = '') {
flLog("function exampleStmt($line)\n");////
		// lines with one space in examples actually become empty in wiki
		
		$line = rtrim($line);
		$this->featureSource .= "___(' $fake". phpsqe($line) . "');\n";

		// php demands semicolons!  expressions don't have them.  at the end.
		if ($line[-1] != ';')
			$line .= ';';
		
		if (! $fake) $this->exampleProc .= $line . "\n";
	}
	
	function exampleBlank() {
flLog("function exampleBlank()\n");////
		$this->featureSource .= "___(' ');\n";
		$this->exampleProc .= "\n";
	}
	
	function exampleAnonTest($testExpr, $expected, $fake = '') {
flLog("function exampleAnonTest($testExpr, $expected)\n");////
//echo("             --- exampleAnonTest: ". dumpText($line). "\n");
		if (substr($testExpr, -1, 1) == ';')
			$this->parseErrors .= "!!\\41! First half of test expr '$testExpr' should not end in semicolon!!\\41!\n";
		$this->featureSource .= "___(' $fake" . phpsqe($testExpr) . "');\n";
		$this->featureSource .= "___(' $fake= " . phpsqe($expected) . "');\n";
		if (! $fake) $this->exampleProc .= "   verEqual($testExpr, $expected, __LINE__);\n";
	}
	
	function exampleNamedTest($varName, $expected, $fake = '') {
flLog("exampleNamedTest($varName, $expected) ");////
		$this->featureSource .= "___(' $fake$varName= " . phpsqe($expected) . "');\n";
		if (! $fake) $this->exampleProc .= "   verEqual($varName, $expected, __LINE__);\n";
flLog("exampleNamedTestoutputs:  verEqual($varName, $expected, __LINE__);");////
	}
	
	function examplePrintedTest($expected, $fake = '') {
flLog("function examplePrintedTest($expected)\n");////
		$this->featureSource .= "___(' $fake=> " . phpsqe($expected) . "');\n";
		if (! $fake) $this->exampleProc .= "   verPrint('". phpsqe($expected) ."', __LINE__);\n";
	}
	
	
	function exampleErrorTest($testLine, $expected, $fake = '') {
flLog("function exampleErrorTest($testLine, $expected)\n");////
		$this->featureSource .= "___(' $fake" . phpsqe($testLine) . "');\n";
		$this->featureSource .= "___(' $fake=! " . phpsqe($expected) . "');\n";
		
		if (! $fake) {
			// note that the __FILE__ & __LINE__ are in the ultimate FS, not here
			// Just run the line and see what comes up
			$this->exampleProc .= " 	global \$cwRecentErrorMsg;\n";
			$this->exampleProc .= " 	\$cwRecentErrorMsg == \"\";\n";
			$this->exampleProc .= " ". $testLine ."\n";
			$this->exampleProc .= "      if (strpos(\$cwRecentErrorMsg, '". 
				phpsqe($expected) ."') === false)\n";
			$this->exampleProc .= "         ___('!!'.'!! failure test in $this->procName '. ".
				"__FILE__ .':'. __LINE__ .' didn`t match, message \\n => '. \$cwRecentErrorMsg .\"\\n\" ".
				".' didn`t include `$expected`.');\n";
			$this->exampleProc .= "   }\n";
		}
	}
		
	
	//////////////////////////////////////////////// other
	function oneNeed($needCode, $rawRule) {
flLog("function oneNeed($needCode, $rawRule)\n");////
		$this->featureSource .= "rule____________________________________('$needCode');\n";
	}
	
	function proseLine($rawLine) {
flLog("function proseLine($rawLine)\n");
//echo("             --- proseLine: ". dumpText($rawLine) ."\n");
		$this->featureSource .= "___('" .  phpsqe($rawLine) . "');\n";
	}
	
	function proseBlank() {
flLog("function proseBlank()\n");
//echo("             --- proseBlank \n");
//debug_print_backtrace();
		$this->featureSource .= "___();\n";
	}
	
}

