<?php
//
//  Ruby Feature Source  --  generate FS for Ruby
//
 if(! defined('MEDIAWIKI')) { echo("\n"); die(-1);}


// single quote escape.  Given that we're going to put  this 
// string inside a pair of single quotes, then insert it into source code,
// prepare it by backslashing the ones that are already there.
function rbsqe($code) {
	// change each backslash to two.  then change each appost to backsl appost
	$code = str_replace('\\', '\\\\', $code);
	return str_replace('\'', '\\\'', $code);
}

// generate the Ruby source for a feature
class cwRubySrc extends cwFeatSrc {
	public $fileSuffix = '.rb';
	public $shellCommandLine = 'Then run this command:<br />
			<kbd>$ ruby Ruby_{{{REQ}}}.rb | diff - Ruby_{{{REQ}}}.wiki</kbd>';

	//////////////////////////////////////////////// start and end of file
	function startGen($reqCode, $banner) {
		// all the stuff  at the top of the file
		$this->featureSource = <<<EOSP
#!/usr/bin/env ruby -w
# $banner
def toStr(value)
	return value.to_s
end

def unEqual(actual, expected)
	return actual != expected
end

# verify that this equality assertion works
def verEqual(actual, expected, lineNo)
	if unEqual(actual, expected)
		\$cwErrorText += "!!\\41! equality test failed in #{__FILE__}:#{lineNo}, `#{actual.inspect}` != `#{expected.inspect}`.\\n" +
				"Should be: \\n = #{actual.inspect}\\n"
	end
end

def ___(text = '')
	puts text
end

def rule______________________________(needName)
	puts "===" + needName + "==="
end


require 'stringio'
#printQueue = '';
\$testOut = StringIO.new('', 'w')
\$testIn = StringIO.new('', 'r')
\$cwErrorText = ''

# verify that print content matches up with expectations
def verPrint(expected, lineNo)
#_t 'verPrint start'
   actual = \$testIn.gets
   \$testIn.pos += actual ? actual.length : 0  # bug in StringIO?
#_t 'verPrint after gets'
#\$stderr.puts 'verprint: act exp'
#\$stderr.puts actual.inspect
#\$stderr.puts expected.inspect
	if actual.strip != expected.strip
        \$cwErrorText += "\\n!!\\41! print test failed in #{__FILE__}:#{lineNo}, actually was\\n" + 
            " => " + actual + "\\n" +
            "!!\\41! rather than " + expected + "\\n"
	end
#_t 'verPrint end'
end

def cwBeforeEx
   \$testIn.string = \$testOut.string = ''
   \$origStdout = \$stdout
   \$stdout = \$testOut
   \$cwErrorText = ''
end

def cwAfterEx
#_t 'cwAfterEx'
   #leftovers = \$testIn.gets(nil)
   \$stdout = \$origStdout
#p 'cwAfterEx: leftovers=' + leftovers.inspect
   \$stdout.write \$cwErrorText;
#p 'cwAfterEx: cwErrorText=' + \$cwErrorText.inspect
end

# test rtn to dump the print buffer
#def _t(title = 'printBuf')
#	\$stderr.write "\\n<" + title + ': <<'
#	\$stderr.write \$testIn.string[\$testIn.pos...9999]
#	\$stderr.write "|||"
#	\$stderr.write \$testOut.string
#	\$stderr.write ">>>\\n"
#end

# start a feature page
puts "<cwStartFeature ch='$reqCode' />\\n"

EOSP;
	}
	
	function endGen($reqCode) {
		$arfl = drawAllReqFeatLinks($reqCode);
		$this->featureSource .= <<<EOSP
\$testIn.close
\$testOut.close
puts "<cwEndFeature ch='$reqCode' />\\n"
puts "$arfl\\n"
puts "[[Category:Ruby]] [[Category:$reqCode]]\\n"

EOSP;
	}
	
	//////////////////////////////////////////////// an Example
	// in ruby, we run each example inside of a class definition rather than a function.
	// You cant define constants, or declare classes, in a function.  You can in a class.
	// and all the local vars look like member vars.  Then we never instantiate the class; 
	// we declare it only to run our code  the way we want.
	function startExample($needCode, $serial) {
//flLog(";;;;;;;;;;;;;;;;;;;; Ruby startExample($needCode, $serial) , splen=". strlen($this->featureSource));
		$this->procName = 'Examp_'. preg_replace('|[ -/:-@\]-`{-\xFF]|', '_', $needCode) .'_'. $serial;
		$this->exampleProc .= "cwBeforeEx\n";
		$this->exampleProc .= "begin\n";
		$this->exampleProc .= "  class {$this->procName}\n";
		$this->exampleProc .= $this->preface;  // example text from overview
	}
	
	function endExample() {
		// end the class.  Runs inline.  
		$this->exampleProc .= "  end\n";
		$this->exampleProc .= "rescue Exception\n";
		$this->exampleProc .= "  \$cwErrorText += \"!!\41! Uncaught Exception in Example: \" + \$!.message + \"\\n\"\n";
		$this->exampleProc .= "  \$cwErrorText += \$!.backtrace.join(\"\\n\") + \"\\n\"\n";
		$this->exampleProc .= "end\n";
		$this->exampleProc .= "cwAfterEx\n";
		$this->featureSource .= $this->exampleProc;
		$this->exampleProc = null;
	}
	
	
	//////////////////////////////////////////////// Lines in an Example
	// lines in a function, even just expressions, are valid without ending semicolon in ruby
	function exampleStmt($line, $fake = '') {
		$f_ = $fake ? ' ' : '';
//flLog("             --- exampleStmt: ". dumpText($line));
//echo("             --- exampleStmt: ". dumpText($line));
		$this->featureSource .= "___ ' ". rbsqe($line) . "'\n";
		$this->exampleProc .= '      '. $line . "\n";
//echo("             --- exampleStmt: ep so foar: ". dumpText($this->exampleProc)."\n\n");
	}
	
	function exampleBlank() {
		$this->featureSource .= "___\n";
		$this->exampleProc .= "\n";
	}
	
	function exampleAnonTest($testExpr, $expected, $fake = '') {
		$this->featureSource .= "___ ' " . rbsqe($testExpr) . "'\n";
		$this->featureSource .= "___ ' $fake= " . rbsqe($expected) . "'\n";
		if (! $fake) $this->exampleProc .= "      verEqual($testExpr, $expected, __LINE__)\n";
	}
	
	function exampleFakeAnonTest($testExpr, $expected) {
		$this->featureSource .= "___ ' " . rbsqe($testExpr) . "'\n";
		$this->featureSource .= "___ '  = " . rbsqe($expected) . "'\n";
	}
	
	function exampleNamedTest($varName, $expected, $fake = '') {
		$this->featureSource .= "___ ' $varName= " . rbsqe($expected) . "'\n";
		if (! $fake) $this->exampleProc .= "      verEqual($varName, $expected, __LINE__)\n";
	}
	
	function examplePrintedTest($expected, $fake = '') {
		$this->featureSource .= "___ ' => " . rbsqe($expected) . "'\n";
		if (! $fake) $this->exampleProc .= "      verPrint('" . rbsqe($expected) . "', __LINE__)\n";
	}
	
	function exampleErrorTest($testLine, $expected, $fake = '') {
		$this->featureSource .= "___ ' $fake" . rbsqe($testLine) . "'\n";
		$this->featureSource .= "___ ' $fake=! " . rbsqe($expected) . "'\n";
		
		if (! $fake) {
			$this->exampleProc .= "    begin\n";
			$this->exampleProc .= "      $testLine\n";
			$this->exampleProc .= "      \$cwErrorText += \"!!\41! Error test in $this->procName incorrectly succeeded!  No message `$expected`.\"\n";
			$this->exampleProc .= "    rescue Exception\n";
			$this->exampleProc .= "      if ! \$!.message['" . rbsqe($expected) . "']\n";
			$this->exampleProc .= "        \$cwErrorText += \"!!\41! Error: test in $this->procName before line \" + __LINE__ + ' failed, ‘.
								but message excludes `$expected`.  Try this line:'\n";
			$this->exampleProc .= "        \$cwErrorText += ' =! ' + \$!.message\n";
			$this->exampleProc .= "      end\n";
			$this->exampleProc .= "    end\n";
		}
	}

	//////////////////////////////////////////////// other
	function oneNeed($needCode, $rawRule) {
		$this->featureSource .= "rule______________________________ '$needCode'\n";
	}
	
	function proseLine($rawLine) {
		$this->featureSource .= "___ '" .  rbsqe($rawLine) . "'\n";
	}
	
	function proseBlank() {
//flLog("             --- proseBlank ");
		$this->featureSource .= "___\n";
	}

}

