<?php
//
//  cwMain  --  main source for CrossWise extensions to MediaWiki
//
if(! defined('MEDIAWIKI')) { heading("Status: 404"); echo("\n"); die(-1);}

// these are MW 'Hooks'
$wgHooks['ParserFirstCallInit'][] = 'cwTagsInit';
$wgHooks['EditFormPreloadText'][] = 'initialPagesPopulateL';
$wgHooks['DoEditSectionLink'][] = 'cwEditSectionLink';
$wgHooks['EditPage::showEditForm:initial'][] = 'cwAddAuditToEditPageL';
$wgHooks['ParserAfterTidy'][] = 'cwParserAfterTidy';
$wgHooks['ArticleViewHeader'][] = 'cwArticleViewHeader';
// no such code in 1.22 $wgHooks['BeforePageDisplay'][]= 'cwConvertPageTitleBeforePageDisplay';

// somehow this must be done a little bit later
function cwTagsInit() {
	global $wgParser;

	// these are all XML element handlers for cw-only tags
	$wgParser->setHook('cwView', 'cwViewL');
	$wgParser->setHook('cwStartFeature', 'cwStartFeatureRender');
	$wgParser->setHook('cwEndFeature', 'cwEndFeatureRender');
	$wgParser->setHook('cwAudit', 'cwAuditL');
	$wgParser->setHook('verification', 'cwAuditL');
	$wgParser->setHook('cwFeatureSource', 'cwFeatureSourceL');
	$wgParser->setHook('cwPagesToBackUp', 'cwPagesToBackUpL');
	$wgParser->setHook('cwViewTOC', 'cwViewTOCL');
	$wgParser->setHook('cwAllFeatures', 'cwAllFeaturesL');
	$wgParser->setHook('cw', 'cwViewLink');
	$wgParser->setHook('cwTestLinks', 'cwTestLinksL');
	$wgParser->setHook('cwAltContext', 'cwAltContext');
	$wgParser->setHook('cwSlotmachine', 'cwSlotmachine');
	$wgParser->setHook('cwExamplePage', 'cwExamplePage');
	return true;
}

// dispatches to other src files

function cwViewL($input, $args, $parser) {
	require_once('cwView.php');
	return cwView($input, $args, $parser);
}

function cwViewTOCL($input, $args, $parser) {
	require_once('cwView.php');  // includes LangBox
	return cwViewTOC($input, $args, $parser);
}

function cwAllFeaturesL($input, $args, $parser) {
	require_once('cwAllFeatures.php');
	return cwAllFeatures($input, $args, $parser);
}

function cwAuditL($input, $args, $parser) {
	require_once('cwAudit.php');
	return cwAudit($input, $args, $parser);
}

function cwFeatureSourceL($input, $args, $parser) {
	require_once('cwFSrc.php');
	return cwFeatureSource($input, $args, $parser);
}

function cwPagesToBackUpL($input, $args, $parser) {
	require_once('cwAudit.php');
	return cwPagesToBackUp($input, $args, $parser);
}

function initialPagesPopulateL(&$text, &$title) {
	require_once('cwIniPages.php');
	return initialPagesPopulate($text, $title);
}

function cwTestLinksL($input, $args, $parser) {
	require_once('cwAudit.php');
	return cwTestLinks($input, $args, $parser);
}

function cwAddAuditToEditPageL($editPage) {
	require_once('cwAudit.php');
	return cwAddAuditToEditPage($editPage);
}

function cwcwParserAfterTidyL($editPage) {
	require_once('cwLangBox.php');
	return cwParserAfterTidy($editPage);
}

function cwArticleViewHeader(&$article, &$outputDone, &$pcache) {
	global $wgOut;
	////$wgOut->addHTML('<link rel=stylesheet href=/skins/crosswise/CrossWise.css>');
}

function cwAltContext($input, $args, $parser) {
	return '';
}

function cwSlotmachine($input, $args, $parser) {
	global $wgScriptPath;
	
	return <<<SLOT_MAC_INE
<div class=bezelOuter>
<div class=bezelInner>
<img src=$wgScriptPath/skins/crosswise/slotmachine.gif />
</div>
</div>
<br clear=both />
SLOT_MAC_INE;
}

function cwExamplePage($input, $args, $parser) {
	global $wgScriptPath;
	
	return <<<EXAM_PLEP_AGE
<table style="float:right; padding: 8px; width:450px;"><td>
<div style="font-size: 80%; padding:2em 10px 0 6px;">
This is what it looks like.  
* The code immediately under each language header is example code, used for the examples throughout this page.  
* Then comes the first entry, which explains how Ruby Hashes, PHP arrays and JavaScript Objects relate to the same language's Arrays.  
* The next entry describes how they relate to the same language's Objects.  Each entry on every chapter in CrossWise describes the same detail, in each of the languages you choose.
<br>&nbsp;⬅ Click on any title to the left to read that chapter.
<img id=example_page src=$wgScriptPath/skins/crosswise/example_page.gif />
</div>
</td></table>

EXAM_PLEP_AGE;
}

////////////////////////////////////////////////////////////////////// debug utils

// take a long character string, and show me what whitespace is where.
// returns string with grave accents around it.
function dumpText($str) {
	// change spaces to utf8 'dot above' U+02d9
	return '`'. str_replace("\n", '\n', str_replace("\t", '\t', str_replace(' ', "\xCB\x99", $str))) .'`';
}



////////////////////////////////////////////////////////////////////// name conversion for reqs
// for req names, wiki page names, need names

function titleToCode($title) {
	return str_replace(' ', '_', $title);
}

function codeToTitle($code) {
	return str_replace('_', ' ', $code);
}



////////////////////////////////////////////////////////////////////// get page text

// get the real-live source text for the page given $title (a string).
// or null if not found.
function cwGetPageText($title) {
	$titleObj = Title::newFromText($title);
//flLog(":::::::::::::::::::::::::::-- cwGetPageText ('$title'). ");
	if (!isset($titleObj))
		return null;
		
	// Get it from the DB
	$rev = Revision::newFromTitle($titleObj, 0);
	if (!$rev)
		return null;
	return $rev->getText();
}

///////////////////////////////////////////////////// Links, esp View/chapter

// Return the URL to cw-view this chapter/req in current user's language list
// Actually just the /path to the end.  pass in either the title or the code.
function cwViewURLPath($reqName) {
	global $cwSubDom, $wgScript, $ChosenLangList, $cwHttpHost;
//flLog("cwViewURLPath($reqName), cwSubDom=$cwSubDom, wgScript=$wgScript");
	$reqCode = titleToCode($reqName);
	
	if ($cwSubDom) {
		// cwSubDom set in LocalSettings if we have a proper cw. domain.
		$u = '/++'. $reqCode;
	}
	else {
	
		// next time they'll be in the correct domain with the right syntax
		$u = "http://$cwHttpHost/++$reqCode";
		
	
	
		// obsolet i think
		//$u = $wgScript ."?title=View&ch=". $reqCode;
		// if ($ChosenLangList)
		//	$u .= '&langs='. $ChosenLangList;
	}
	if ($ChosenLangList)
		$u .= '/'. $ChosenLangList;
	//flLog("cwViewURLPath   url=`$u`");
	return $u;
}

// just like cwViewURLPath(), but includes the http://domain part
// which you usually don't need but whatever
function cwFullViewURL($reqCode) {
	global $cwHttpHost;
	return 'http://'. $cwHttpHost . cwViewURLPath($reqCode);
}

// Return the Wiki URL to A Page, any Page in the Wiki.  doesn't MW do this?  I'll find it one day.
// pass in official Wiki pagename (code or title) and any intended qs (after the ?)
// You'll get the Full URL, http: onward
function cwFullWikiURL($pageName = 'Main_Page', $queryString = '') {
//flLog(" cwFullWikiURL($pageName, $queryString)");
	global $cwHttpHost, $wgScript;

	$suf = titleToCode(rawurlencode($pageName));
	if ($queryString)
		$suf .= '?'. $queryString;
	return 'http://'. $cwHttpHost . $wgScript ."/". $suf;
}
// Make a cw view link from a <cw> tag.  Tag handler.
// the syntax of this is like, "<cw Arrays />", embedded in some page somewhere.
// Make it a link into the view page of Arrays.  or whatever.
function cwViewLink($input, $args, $parser) {
//flLog(" cw View Link: $input");
//flExport($args);
	$reqName = isset($args['c']) ? $args['c'] :
				(isset($args['ch']) ? $args['ch'] :
				(isset($args['req']) ? $args['req'] : false));
	if (!$reqName) {
		// the real hack: take attr names & glom into a req
		// we depend on their order being the same (pray)
		$reqName = '';
		foreach ($args as $word => $one)
			$reqName .= ucwords(codeToTitle($word)) .' ';

		// clean up: and->&, trim off spaces
		$reqName = preg_replace('/[ _][Aa][Nn][Dd][ _]/', 
				' & ', trim($reqName));
	}
//flLog(" cw View Link is '$reqName'");
	if (!$reqName)
		return "<div style=background-color:#f64>Badly formed &lt;cw&gt; tag, try like".
			" <b>&lt;cw ch='Binary Data' /&gt;</b></div>\n";

	$label = $input ? $input
			: (isset($args['label']) ? $args['label'] 
			: codeToTitle($reqName));
	return "<a href='".
		cwViewURLPath($reqName) ."'>". $label ."</a>";
}


// adjusts '[edit]' section links on feature pages.
function cwEditSectionLink($skin, $title, $sectionNum, $tooltip, $result) {
	// get rid of the brackets & make it look like an edit button.
	$result = str_replace('class="editsection">[<a ', 'style=display:block;float:right><a class="editBut" ', str_replace('>]<', '><', $result));
	return true;
}

// draw the kind of edit button we use here, 
// as an a-tag, knowing it wont be wikiparsed.  
function drawHtmlEditBut($pageCode, $extraAttrs = '') {
	return "<a class=editBut href='/index.php?title=".
					$pageCode ."&action=edit' title='click to edit $pageCode' ".
					$extraAttrs ." >edit</a>\n";
}

////////////////////////////////////////////////////////////////////// Requirement
// including all need names in order

class cwNeed {
	function __construct($name, $desc, $kind) {
		$this->code = titleToCode($name);  // programming code
		$this->name = $this->code;
		$this->title = codeToTitle($name);  // user visible
		$this->desc = $desc;  //  description from req file
		$this->kind = $kind;  //  '+' if hyperlink to a subreq or any other req; '-' if regular
	}
}

// a Requirement, as read in from the defining file, Category:<reqname>
class cwReq {
	// pass either code or title of req
	// pass audit true to check more carefully everything
	function __construct($reqName, $audit = false) {
		global $AuditReport;
//flLog("^^^^^^^^^^^^^^^^^^^^^^ new cwReq($reqName, $audit)");

		$this->code = titleToCode($reqName);
		$this->title = codeToTitle($reqName);
		
		// Get the needs page for this feature and trim off the ends
		$src = cwGetPageText("Category:$reqName");
		if (!$src) {
			// if you edit a new feature page, and there's no Req page, you come here.  
			// Or if you flatten your ++url to lower case.  hmmm...
			// Or if the urls chapter is anyhow not found.
			// Get an error message to the human somehow.
			$this->needs[] = new cwNeed("Error", 
				"no such page Category:$reqName, needed to compose Requirement, in ".
				__FILE__.__LINE__, '-');
			return;
		}
//		$s = explode("\n==$reqName Needs==\n", $src, 2);
		if ($audit)
				scanForBadChars($src, "[[:Category:$reqName]]");
		$ss = explode("\n----\n", $src);
		if ($audit)
			AuditReqConstruction($reqName, $ss);
		$needs = $ss[0];
//flLog("^^^^^^^^^^^^^^^^^^^^^^ new cwReq, needs txt'$needs'.");
		
		// break up into individual needs and make rows for each
		$needs = explode("* '''", $needs);
		$this->overview = trim(array_shift($needs));  // stuff before first need
		if ($audit) {
			if (strlen($this->overview) <= 0)
				$AuditReport .= "* No overview  for requirement '$reqName'<br>\n";
		}

		$this->needs = array();
		foreach($needs as $i => $ntx) {
			// at this point, $ntx is like `'''- returns a True value only if both arguments are true`
			// or a + instead of -  if its a link to another req.
			$nn = explode('\'\'\' ', $ntx, 2);
			$kind = $nn[1][0];
		
			$desc = trim(substr($nn[1], 1));
			$this->needs[$i] = new cwNeed(trim($nn[0]), $desc, $kind);
//flLog("^^^^^^^^^^^^^^^^^^^^^^ new cwReq, need'".$this->needs[$i]->name ."' desc '".$this->needs[$i]->desc."'.");
			if ($audit) {
				if (strlen($nn[0]) <= 0)
					$AuditReport .= "* No need name for requirement '$reqName' line '$ntx'<br>\n";
				if (strlen($nn[1]) <= 0)
					$AuditReport .= "* No description for requirement '$reqName' line '$ntx'<br>\n";
				if ($kind != '-' && $kind != '+')
					$AuditReport .= "* Unknown req kind $kind for '$reqName' line '$ntx'<br>\n";
			}
		}
//flLog("^^^^^^^^^^^^^^^^^^^^^^ new cwReqdone with constructor.");
	}
}

//////////////////////////////////////////////////////////// All Requirements

global $allReqCodes, $allReqLevels, $isAReq;
$allReqCodes = null;     // $allReqCodes[n] is code for n-th req in master list
$allReqLevels = null;  // 1, 2, ... hierarchical level of n-th req
$eachReqParent = null;  // $eachReqParent[code] = code in $allReqCodes[] of its 
			// parent req or '' if top level.  note this is NOT indexed by n, but by code!
$isAReq = null;    // $isAReq[aReqCode] is true

// just gimme an array of all requirement codes (with _ not space)
// from Category:Requirements
// while you're at it, audit if flag is on
// while you're at it, store it in $allReqCodes global
// and fill $allReqLevels and $eachReqParent (see above)
function GetAllReqCodes($audit = false) {
	global $AuditReport;
	global $allReqCodes, $allReqLevels, $eachReqParent;
	
	if ($allReqCodes)
		return $allReqCodes;
	$allReqCodes = array();
	$allReqLevels = array();

	$src = cwGetPageText('Category:Requirements');
	if (!$src)
		die("no such page Category:Requirements, in ". __FILE__.__LINE__);
	if ($audit)
			scanForBadChars($src, "[[:Category:Requirements]]");
	$entryLines = explode("\n", $src);
	$startPart = true;
	$endPart = false;
	$parents = array('');
	foreach ($entryLines as $line) {
		// skip over intro which is freeform prose
		if ($startPart && ($line == '' || $line[0] != '*'))
			continue;
		$startPart = false;
		
		// and skip over the end part, starts upon first blank line
		if ($line == '')
			$endPart == true;
		if ($endPart)
			continue;
			
		// now the starred lines
		if (preg_match('/^(\*+) \[\[:Category:([^|]*)\|(.*)\]\]/', $line, $match) != 1) {
			if ($audit)
				$AuditReport .= "* stray line in Category:Requirements, '$line'<br>\n";
		}
		else {
			if ($audit) {
				if (codeToTitle($match[2]) != $match[3])
					$AuditReport .= "* bad req line in [[:Category:Requirements]], '" .
						$match[2] ."' != '". $match[3] ."'<br>\n";
				if (strlen($match[0]) >= strlen($line))
					$AuditReport .= "* should have description in Category:Requirements, '" .
						$match[2] ."'<br>\n";
			}
			
			// now collect req name
			$allReqCodes[] = $reqCode = str_replace('Category:', '', titleToCode($match[2]));
			$allReqLevels[] = $depth = strlen($match[1]);
			
			// who's your daddy?
			$eachReqParent[$reqCode] = $parents[$depth-1];
			$parents[$depth] = $reqCode;
		}
	}
	return $allReqCodes;
}

// return true if name is a code of a req
function isAReq($name) {
	global $isAReq;
	if (!$isAReq) {
		$reqs = array_values(GetAllReqCodes());
		array_unshift($reqs, 0);  // so first in list doesnt get value zero
		$isAReq = array_flip($reqs);
	}
	
	return isset($isAReq[$name]);
}

///////////////////////////////////////////////////////////////// Req Menu

// generate the reqs menu.
// $selReqCode is the selected item; no default (top of menu)
// $allItem true to include an "All Reqirements" item at the top
// element name='req' values returned are the req codes BUT
// pass $urlValues true and you get the URLs instead with cwViewURLPath
function formatReqMenu($selReqCode, $allItem = false, $chooseItem = false, $urlFilter = null) {
	global $allReqLevels;

	$reqMenu = "<select name=req>\n";
	if ($allItem)
		$reqMenu .= "<option value=*". ($selReqCode=='*' ? ' selected' : '') .
				">All Requirements</option>\n";
	if ($chooseItem)
		$reqMenu .= "<option value=choose". ($selReqCode=='choose' ? ' selected' : '') .
				">Choose Chapter</option>\n";
	$reqs = GetAllReqCodes();
	foreach ($reqs as $r => $reqCode) {
		$ti = codeToTitle($reqCode);
		$val = $urlFilter ? $urlFilter($reqCode) : $reqCode;
		$reqMenu .= "<option value='$val'". 
				($selReqCode==$reqCode ? ' selected' : '') .">" . 
				substr("********", 0, $allReqLevels[$r]-1) .' '. $ti ."</option>\n";
	}
	return $reqMenu ."</select>\n";
}


///////////////////////////////////////////////////////////////// Language

// All the languages we support and handy-dandy objects for them, name is the key.
// How to add a new programming language:
// - add it here to $allTheLangs
// - add a subclass of cwLang above like the rest
// - surf to Help:All_Features, and reload till it shows up.  
//     Fill in all the pages that are missing.
global $allTheLangs;
$allTheLangs = array(
	'JavaScript' => new JavaScriptLang(),
	'PHP' => new PHPLang(), 
	'Ruby' => new RubyLang(), 
	'Python' => new PythonLang(), 
);

// just the original 3 languages, sortof examples/templates for future languages
$origLangs = array(
	'JavaScript' => $allTheLangs['JavaScript'],
	'PHP' => $allTheLangs['PHP'],
	//'Ruby' => $allTheLangs['Ruby'],
	// oops 'Python' => $allTheLangs['Python'],
);

// test to verify correct lang spelling
function isALang($name) {
	global $allTheLangs;
	return isset($allTheLangs[$name]);
}

// superclass of each language object
class cwLang {
	// points to cwFeatSrc object for this language
	protected $featSrc = null;
	
	// get singleton (sortof) object.  
	// Note this doesn't depend on feature or req name; only lang.
	// you pass in a req to most routines if needed.
	public function featSrc() {
		if (!$this->featSrc) {
			require_once("cw{$this->code}.php");
			$className = "cw{$this->code}Src";
			$this->featSrc = new $className($this);
		}
		return $this->featSrc;
	}

}

class JavaScriptLang extends cwLang {
	public $lang = 'cwMain500JavaScript';  // deprecated
	public $langName = 'JavaScript';
	public $title = 'JavaScript';
	public $code = 'JavaScript';
	public $vers = '1.5';

	public $colColor = '#f8f8ff';
	public $examColor = '#eef';
	public $headColor = '#aaf';

	public $varPat = "[a-zA-Z_][a-zA-Z0-9_]*";
}

class PHPLang extends cwLang {
	public $lang = 'cwMain500PHP';  // deprecated
	public $langName = 'PHP';
	public $title = 'PHP';
	public $code = 'PHP';
	public $vers = '5.2';

	public $colColor = '#f8fff8';
	public $examColor = '#efe';
	public $headColor = '#afa';

	public $varPat = "\\\$[a-zA-Z_][a-zA-Z0-9_]*";
}

// specifics of Ruby
class RubyLang extends cwLang {
	public $lang = 'cwMain500Ruby';  // deprecated!
	public $langName = 'Ruby';
	public $title = 'Ruby';
	public $code = 'Ruby';
	public $vers = '1.8.6';  // will go away

	public $colColor = '#fff8f8';
	public $examColor = '#fee';
	public $headColor = '#faa';
	
	// the rules for var names, as regex phrase
	public $varPat = "[a-zA-Z_][a-zA-Z0-9_$]*[=!?]?";
}

// specifics of Python
class PythonLang extends cwLang {
	public $lang = 'cwMain500Python';  // deprecated!
	public $langName = 'Python';
	public $title = 'Python';
	public $code = 'Python';
	public $vers = '2.7.2';  // will go away?

	public $colColor = '#fcf8ff';
	public $examColor = '#f8eeff';
	public $headColor = '#daf';
	
	// the rules for var names, as regex phrase
	public $varPat = "[a-zA-Z_][a-zA-Z0-9_$]*";  // ?? lookitup someday
}


///////////////////////////////////////////////////////////// Lang Menu

// generate the languages menu <select - mostly for audit and FS stuff
// $language is name of selected one; null i guess defaults to top of menu
// $itemName is a name to stick to the element name
// $allItem true to include 'All Langs' item, val='*'
// $reqsItem true to include 'Reqs Only' item, value '^'
function formatLangMenu($language, $itemName = 'language', 
		$allItem = false, $reqsItem = false, $chooseItem = false, $noneItem = false) {
	global $allTheLangs;

	$langMenu = "<select name=$itemName>\n";
	if ($chooseItem)
		$langMenu .= "<option value=none". ($language=='none' ? ' selected' : '') .
				">Choose Language</option>\n";
	if ($allItem)
		$langMenu .= "<option value=^". ($language=='^' ? ' selected' : '') .
				">Reqs only</option>\n";
	if ($reqsItem)
		$langMenu .= "<option value=*". ($language=='*' ? ' selected' : '') 
				.">All Languages</option>\n";
	foreach ($allTheLangs as $la){
		$langMenu .= "<option value=$la->code ". ($language==$la->code ? 'selected' : '') .
				">$la->title</option>\n";
	}
	if ($noneItem)
		$langMenu .= "<option value=none". 
				">none</option>\n";
	return $langMenu . "</select>\n";
}

///////////////////////////////////////////////////////////////// Feature
// a requirement in a language

// The object with all the info we need to:
//   - construct the view column for this language & feature.
//   - test/verify examples in the rules for a lang & feature.
// contains hash for looking up rule text by need name.
// And other stuff.  
class cwFeature {
	public $lang;  // points to object
	public $vers;  // points to char string or null if none
	
	// langVers should be like 'PHP@5.0' or just 'PHP' is OK but version will be null.
	// if Lang_Req page not found, all rules arrays will be unassigned.
	// If Lang is not a language (eg page Bug_List), this will DIE.
	// pass wiki content for $altContent to base rules on different wiki input
	function __construct($langVers, $reqName, $audit = false, $altContent = null) {
		global $allTheLangs;
//flLog("=========================== newcwFeature ('$langVers'_'$reqName', $audit)");
		$z = explode('@', $langVers);
	
		if (!isALang($z[0]))
			die("Cannot find language $langVers");

		$this->lang = $allTheLangs[$z[0]];

		// the language is already in using a class-specific var
		$this->vers = isset($z[1]) ? $z[1] : null;
		$this->audit = $audit;

		$this->req = $reqName;  // deprecated!
		$this->reqName = titleToCode($reqName);
		$this->reqCode = titleToCode($reqName);
		$this->reqTitle = codeToTitle($reqName);
		//$this->featureName = $reqName;  // deprecated!
		
		$this->featName = $this->lang->code .'_'. $this->reqName; 
		
//flLog("=========================== newcwFeature about to init rules");
		return $this->initRules($altContent);
	}
	
	// fill in the ->rules, rulesTaken and ruleOrder arrays given data 
	// from Feature & Req pages.  Returns this->rules unset if no such feature page.
	function initRules($altContent) {
		global $AuditReport, $cwPrevFeatureWikiSrc;

//echo("got ". (isset($altContent) ? strlen($altContent) : 'no') ." bytes, for alt content");
		// get the text, but only the parts between the Feature tags
		if ($altContent)
			$src = $cwPrevFeatureWikiSrc = $altContent;
		else
			$src = $cwPrevFeatureWikiSrc = cwGetPageText($this->featName);
//flLog("============ newcwFeature cwGetPageText($this->featName) returnz ". isset($src));
		if (!$src) {
			return;
		}
//flLog("============ newcwFeature cwGetPageText($this->featName) continue with rules from ". strlen($src));
			
		// do this now in case there's nothing there		
		$this->rules = array();
		$this->ruleTaken = array();
		$this->ruleOrder = array();

		if ($this->audit)
			scanForBadChars($src, "[[$this->featName]]");

		$p = preg_split("/<cwStartFeature[^>]*>/", $src, 2);
		if (count($p) != 2) {
			$p[1] = $p[0];
			if ($this->audit)
				$AuditReport .= "* feature page [[$this->featName]] needs exactly one cwStartFeature tag\n";
		}
		$pp = preg_split("/\n<cwEndFeature[^>]*>/", $p[1], 2);
		if (count($pp) != 2) {
			if ($this->audit)
				$AuditReport .= "* feature page [[$this->featName]] needs exactly one cwEndFeature tag\n";
		}
		$rules = substr($pp[0], 1);  // remove newline right after start feat tag
		if ($this->audit)
			AuditFeatTags($this, $src);
		
//flLog(" the cwFeature source iz [[[$rules]]]".'...For '. $this->featName);
		// now break up into individual rules
		// Each rule is like this->rules['Dates_and_Times'] = "rule text"
		$rules = "\n". $rules;
		$rules = explode("\n===", $rules);  // note this removes ending newline on each rule
		$this->overview = array_shift($rules);
//flLog('_______ this->overview= "'.$this->overview.'"');
		for ($i = 0; $i < count($rules); $i++) {
			$ru = $rules[$i] ."\n";  // and this puts it back
			
			// break into name and rule body.  Note: don't get fooled by === which 
			// many languages have it int!  but rarely at the end of a line
			$r = explode("===\n", $ru, 2);
			$ruleCode = titleToCode($r[0]);
			@$this->rules[$ruleCode] = $r[1];
			
			// a space at the end of the line ruined this once
			if (strpos("\n", $ruleCode) !== false) {
				$this->rules[$ruleCode] = "Bad rule name " . $ruleCode;
				if ($this->audit)
					$AuditReport .= "* bad rule name: ". str_replace($r[0], "\n", '\\n') ."\n";
			}


			//// if a rule is empty and two headings are right against each 
			//// other, that explode wont work cuz newline gone.  fixup.
			//if (!isset($r[1]) && substr_compare($ru, '===', -3, 3) == 0) {
			//	$r[0] = substr($ru, 0, -3);
			//	$r[1] = '';
			//}
			
			$this->ruleTaken[$ruleCode] = false;
			$this->ruleOrder[] = $ruleCode;
//flLog('_______ this->rules['.$ruleCode.'] = '. dumpText($r[1]));
		}
		return;  // ok
	}
	
	

	
	//////////////////////////////////////////////////////////////////////// Rule Display

	// return given rule, raw text only, then mark it as having been used for view.
	// return null if not found or already used.
	function takeRule($needName) {
		$ruleCode = titleToCode($needName);
//flLog("take rule($needName -> $ruleCode), ruletaken:". (isset($this->rules[$ruleCode]) ? $this->ruleTaken[$ruleCode] : "no such rule in feature") .")");
		// if there WAS a feature page, and it had this rule, and it's not taken yet
		if (isset($this->rules) && isset($this->rules[$ruleCode]) && !$this->ruleTaken[$ruleCode]) {
			$r = $this->rules[$ruleCode];
			$this->ruleTaken[$ruleCode] = true;
			return $r;
		}
//flExport($this->rules);
		return null;  // none.   Or, already listed.   thats ok.
	}
	
	function isRuleTaken($needName) {
		$ruleCode = titleToCode($needName);
		return $this->ruleTaken[$ruleCode];
	}
	
}

// this appears at the bottom of each feature, second to bottom line.  
// Includes all current languages.  Looks like:
// [[:Category:Arrays]] [[Ruby_Arrays]] [[PHP_Arrays]] [[JavaScript_Arrays]] 
function drawAllReqFeatLinks($reqCode) {
	global $allTheLangs;

	$text = "[[:Category:$reqCode]] ";
	foreach ($allTheLangs as $lang) 
		$text .= "[[{$lang->code}_$reqCode]] ";
	return $text;
}

/////////////////////////////////////////////////////////// Rule & Feature
// these tags are at the start and end of each feature.
// Handled in parsing; executing doesn't really do anything.

function cwStartFeatureRender($input, $args, $parser) {
	return '';
}

function cwEndFeatureRender($input, $args, $parser) {
	return '';
}


