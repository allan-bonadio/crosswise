<?php
//
//  Audit  --  checking feature and req pages for simple syntax & continuity
//
 if(! defined('MEDIAWIKI')) { echo("\n"); die(-1);}

////////////////////////////////////////////////////////// Verification & Auditing

// form for:
//   verification: running examples to make sure they work
//   auditing: scanning pages to make sure they're there and formatted right

// for a page, if you want to scan for nonbreaking spaces or other general-text 
// problems, call this.  Pass in all the text, and a  string like "[[Ruby_Arrays]]" 

//////////////////////////////////////// Virtual List of All Pages
// * All Requirements pages (eg Category:Arrays)
// * All Language pages (eg Category:Ruby)
// * All Language x Requirement = Features pages (eg Ruby_Arrays)
// * the OK pages, see just below:
//
// these are pages that are OK despite the fact that theyre 
// not part of the table-driven lists.  Please use underbars; spaces wont match
// pages of form Category:*, CrossWise:*, Help:*, and * in the main namespace
global $okCats, $okCWs, $okHelps, $okMains;
$okCats = array('Requirements'=>1, 'Languages'=>1);
$okCWs = array('PagesToBackUp'=>1, 'Public_Bug_List'=>1, 'IntBugList'=>1,  );
$okHelps = array('Contents'=>1, 'Examples'=>1, 'Editing'=>1, 'All_Features'=>1, 
		"Author's_Guide"=>1, 'How_it_Works'=>1, 
		'About'=>1, 'Glossary'=>1, 
		'Feature_Source'=>1, 
		'Chapter_Links'=>1);
$okMains = array('View'=>1, 'Main_Page'=>1, 'Bug_List'=>1,
		'Verification'=>1, 'Test_Links'=>1);

global $cwDebug;

// self-check these tables
if ($cwDebug) {
	function ckNs(array $ok) {
		foreach ($ok as $title => $one)
			if (strpos($title, ' ')) die(__FILE__ .':'. __LINE__ ." - space in ok Title $title");
	}
	ckNs($okCats);
	ckNs($okCWs);
	ckNs($okHelps);
	ckNs($okMains);
}

// so handy for the below routines
$pageListBuilding = array();
function appendPL($prefix, $name, $asHash) {
	global $pageListBuilding;
	$ti = $prefix . $name;
	if ($asHash)
		$pageListBuilding[$ti] = $asHash;
	else
		$pageListBuilding[] = $ti;
}


// return an array listing all existing pages in this wiki for a given namespace.
// Just a string with the page title, each.
// namespaces: '' is main (rules & others), 
// 'Categories' is categories (reqs and langs)
function GetAllPages($ns, $asHash = false) {
	global $pageListBuilding;
	global $wgContLang;
	$namespace = array_search($ns, $wgContLang->getNamespaces());

	$dbr = wfGetDB(DB_SLAVE);
	$res = $dbr->select('page', array('page_title'), 
		array('page_namespace' => $namespace), __METHOD__, 
		array('ORDER BY'  => 'page_title'));

	$pageListBuilding = array();  // in case there arent any
	while ($row = $dbr->fetchObject($res)) {
		appendPL('', titleToCode($row->page_title), $asHash);
	}
	$dbr->freeResult($res);

//var_dump($pageListBuilding);
	return array_merge($pageListBuilding);
}

// get an array listing all pages that SHOULD be 
// in the Category namespace for CW.
// Not sorted but deterministic order
function GetCategoryPages($asHash = false) {
	global $pageListBuilding, $okCats, $allTheLangs;
	$pageListBuilding = array();
	foreach ($okCats as $ti=>$one)
		appendPL("Category:", $ti, $asHash);
	foreach (GetAllReqCodes() as $reqCode)
		appendPL("Category:", $reqCode, $asHash);
	foreach ($allTheLangs as $name => $langObj)
		appendPL("Category:", $name, $asHash);
	return array_merge($pageListBuilding);
}

// get an array listing all pages that SHOULD be 
// in the CrossWise namespace for CW
function GetCrossWisePages($asHash = false) {
	global $pageListBuilding, $okCWs;
	$pageListBuilding = array();
	foreach ($okCWs as $ti=>$one)
		appendPL("CrossWise:", $ti, $asHash);
	return array_merge($pageListBuilding);
}

// get an array listing all pages that SHOULD be 
// in the Help namespace for CW
function GetHelpPages($asHash = false) {
	global $pageListBuilding, $okHelps;
	$pageListBuilding = array();
	foreach ($okHelps as $ti=>$one)
		appendPL("Help:", $ti, $asHash);
	return array_merge($pageListBuilding);
}

// get an array listing all pages that SHOULD be 
// in the Main namespace for CW - includes the main matrix
function GetMainPages($asHash = false) {
	global $pageListBuilding, $okMains, $allTheLangs;
	$pageListBuilding = array();
	foreach ($okMains as $ti=>$one)
		appendPL('', $ti, $asHash);

	// now the feature matrix

	foreach ($allTheLangs as $name => $langObj) {
		$prefix = $name .'_';
		foreach (GetAllReqCodes() as $reqCode)
			appendPL($prefix, $reqCode, $asHash);
	}
	return $pageListBuilding;
}
////////////////////////////////// scanForBadChars


// in case of error
function scanForBadChars($src, $link) {
	global $cwAuditReport;
	$n = substr_count($src, "\xA0");  // nonbreaking space
	if ($n)
		$cwAuditReport .= "Page $link has $n nonbreaking spaces in it.<br>\n";
}


////////////////////////////////// Verify (getting old)

//// runs upon submission of Verification dialog.  kinda defunct.
//function verifyLangFeature($language, $feature) {
//	global $cwAuditReport;
//
//	if (!$language)
//		$language = 'Ruby';
//	if (!$feature)
//		$feature = 'Arrays';
////flLog(";;;;;;;;;;;;;;;;;;;; verifyLangFeature($language, $feature) ");
//	$cwAuditReport .= "<h2>Verify Running Examples</h2>\n";
//	
//	$langF = new cwFeature($language, $feature);
//	return $langF->verify();
//}

////// generate the feature source for this comb of language & feature
////// DEPRECATED
////function srcLangFeatureX($language, $reqCode) {
////	if (!$language)
////		$language = 'PHP';
////	if (!$reqCode)
////		$reqCode = 'Dates & Times';
//////flLog(";;;;;;;;;;;;;;;;;;;; srcLangFeatureX($language, $reqCode) ");
////	require_once('cwFSrc.php');
////
////	if ($language == '*' || $language == '^') {
////		echo "Must choose one specific language<br>\n";
////		return;
////	}
////	if ($reqCode == '*' || $reqCode == '^') {
////		echo "Must choose one specific req<br>\n";
////		return;
////	}
////	$feat = new cwFeature($language, $reqCode);
////	return $feat->lang->featSrc()->doFeatSrcFile($feat);
////}

////////////////////////////////// audit

// collects nasty audit messages, if $audit flag is on
global $cwAuditReport;
$cwAuditReport = '';

// this is actually called in cwFeature creation if you pass the audit flag.
// $feat is the full feature object; $src is complete raw wiki text for page
function AuditFeatTags($feat, $src) {
//flLog("AuditFeatTags($feat->reqName, `$src`)");
	global $cwAuditReport, $cwAuditSitu;
	$where = ($cwAuditSitu == 'auditPage') ? "in src for [[$feat->featName]]" : '';

	if (strpos($src, "<cwStartFeature ch='$feat->reqName' />") === false &&
				strpos($src, "<cwStartFeature ch=\"$feat->reqName\" />") === false)
		$cwAuditReport .= "* no proper cwStartFeature $where  Use &lt;cwStartFeature ch='$feat->reqName' /&gt;  <br>\n";

	if (strpos($src, "<cwEndFeature ch='$feat->reqName' />") === false &&
				strpos($src, "<cwEndFeature ch=\"$feat->reqName\" />") === false)
		$cwAuditReport .= "* no proper cwEndFeature $where  Use &lt;cwEndFeature ch='$feat->reqName' /&gt;<br>\n";

	// for some reason, trailing newlines are always removed so it 
	// always (usually?) ends mid-line.
}


function pruneDown($str) {
	$str = preg_replace('/[^a-zA-Z0-9]/', '', $str);  // get rid of punctuation
	return strtolower($str);
}

// return true of either half of $a is found in $b
function kinda($a, $b) {
	$half = max(floor(strlen($a)/2), 6);
	$aa = substr($a, 0, $half);
	if (strpos($b, $aa) !== false)
		return true;
	$aa = substr($a, $half, $half);
	if (strpos($b, $aa) !== false)
		return true;
	return false;
}

function strKindaCmp($a, $b) {
	return kinda($a, $b) || kinda($b, $a);
}

// audit a page with name like PHP_Arrays
function auditAFeature($langName, $reqName) {
	global $cwAuditReport;
//flLog("/;;;;;;;;;;;;;;;;;;;;;;;  auditAFeature($langName, $reqName) ");
	$feat = new cwFeature($langName, $reqName, true);
	if (!isset($feat->rules)) {
		$cwAuditReport .= "\n* [[{$langName}_$reqName]] feature not available<br>";
		return;
	}
	
	// check sequence of rules against sequence of needs in req
	$req = new cwReq($reqName);  // wasteful but whatever
	if (!$req) {
		$cwAuditReport .= "\n* <a href=". cwFullWikiURL("Category:". $reqName) .">$reqName</a> requirement not available<br>";
		return;
	}
	
	$lastNeedTitle = 'the start';
	$whichRule = 0;
	foreach ($req->needs as $need) {
		// start off assuming they should be in order.  sortof
//echo "\n $need->name !=? ". $feat->ruleOrder[$whichRule] ."<br>\n";
		if ($whichRule < count($feat->ruleOrder) && $need->name != $feat->ruleOrder[$whichRule]) {
//flLog("audit: $whichRule < ". count($feat->ruleOrder) ." && $need->name != ". $feat->ruleOrder[$whichRule]);
			// not right but maybe it's moved somewhere
			if (isset($feat->rules[$need->name])) {
				// ok its somewhere, find it
				$newWhich = array_search($need->name, $feat->ruleOrder);
				if ($newWhich !== false) {
					if ($newWhich < $whichRule)
						$how = 'down '. ($whichRule-$newWhich);
					else
						$how = 'up '. ($newWhich-$whichRule);
					$cwAuditReport .= "\n* Rule '$need->title' is not after '$lastNeedTitle' like it should be in feature <a href=". cwFullWikiURL($langName .'_'. $reqName) .">{$langName}_$reqName</a>.  It should be moved $how.<br>\n";
				}
				else {
					$cwAuditReport .= "\n* Error: Rule '$need->title' seems to be in feature <a href=". cwFullWikiURL($langName .'_'. $reqName) .">{$langName}_$reqName</a>.  But something's fishy.<br>\n";
				}
				$whichRule = $newWhich + 1;
				$lastNeedTitle = $need->title;
				$feat->takeRule($need->name);
			}
			else {
				$cwAuditReport .= "\n* Rule '$need->title' in the req page <a href=". cwFullWikiURL("Category:". $reqName) .">$reqName</a> is not listed in feature <a href=". cwFullWikiURL($langName .'_'. $reqName) .">{$langName}_$reqName</a> (could just be a typo).  ";
				$nn = pruneDown($need->name);
				$cwAuditReport .= "Use this:\n ===$need->title===\n<br>and put it after $lastNeedTitle\n";
				foreach (array_keys($feat->rules) as $rKey) {
					if (strKindaCmp(pruneDown($rKey), $nn))
						$cwAuditReport .= "<small>(looks similar to rule '$rKey')</small><br>\n";
				}
				$lastNeedTitle = $need->title;
			}
		}
		else {
			$feat->takeRule($need->name);
			$whichRule++;
			$lastNeedTitle = $need->title;
		}
	}
	
	// now the other way - find rules not listed among the needs, that is, not taken
	$needNeeds = '';
	foreach ($feat->rules as $needName => $ruleText) {
		if (!$feat->isRuleTaken($needName))
			$needNeeds .= "<nowiki>* <b>". codeToTitle($needName) ."</b> - desc</nowiki><br>\n";
	}
	if ($needNeeds)
		$cwAuditReport .= "\n* there's some rules in <a href=". 
				cwFullWikiURL($langName .'_'. $reqName) .">{$langName}_$reqName</a> not listed in <a href=". 
				cwFullWikiURL("Category:". $reqName) .">Category:". $reqName .
				"</a>; add these to the Category page to make them cross-language:<br>\n" . $needNeeds;
}

// called from the cwReq constructor if you pass in audit flag
function AuditReqConstruction($reqName, $ss) {
	global $cwAuditReport;
//flLog("^^^^^^^^^^^^^^^^^^^^^^ new cwReq, auditing.");
//flExport($ss);
//flExport(count($ss));
	//if (strlen($s[0]) > 0)
	//	$cwAuditReport .= "* Stray text ahead of title for requirement '$reqName'<br>\n";
	
	// make sure the categories line at the end is exactly right
	if (isset($ss[1])) {
		$cl = trim($ss[1]);
		$shouldBe = drawAllReqFeatLinks($reqName) .' [[Category:Requirements]]';
//flLog("^^^^^^^^^^^^^^^^^^^^^^ new cwReq, catline='$cl', shbe='$shouldBe'.");
		if ($cl != $shouldBe) {
			$cwAuditReport .= "\n* for requirement /<a href=". cwFullWikiURL("Category:". $reqName) .">$reqName</a>/: category line is wrong.<br>\n";
			$cwAuditReport .= "** should be: <nowiki>$shouldBe</nowiki><br>\n";
			$cwAuditReport .= "** was instead: <nowiki>$cl</nowiki><br>\n";
		}
	}
	else
		$cwAuditReport .= "\n* for requirement /<a href=". cwFullWikiURL("Category:". $reqName) .">$reqName</a>/: category line is absent, or ---- line isn't above it.<br>\n";
}

// audit a page with a name like Category:Arrays
// and maybe all its language implementations
function auditAReq($reqName) {
//flLog("/;;;;;;;;;;;;;;;;;;;;;;;  auditAReq($reqName)  ");
	$req = new cwReq($reqName, true);
}

function auditByLang($langName, $reqName) {
	global $allTheLangs;
//flLog("/;;;;;;;;;;;;;;;;;;;;;;;  auditByLang($langName, $reqName)  ");
	if ($langName == '*') {
		auditAReq($reqName);
		$allLangs = array_keys($allTheLangs);
		foreach ($allLangs as $la)
			auditAFeature($la, $reqName);
	}
	else if ($langName == '^' || $langName == 'Requirements')
		auditAReq($reqName);
	else
		auditAFeature($langName, $reqName);
}

// runs upon submission of Auditing dialog
// audit a page with a name like Category:Arrays
// and maybe all its language implementations
function auditLangFeature($langName, $reqName) {
	global $cwAuditReport;
//flLog("/;;;;;;;;;;;;;;;;;;;;;;;  auditLangFeature('$langName', '$reqName')  ");
	$cwAuditReport .= "<h2>Audit Reqs, Needs, Features and Rules</h2>\n";
	if ($reqName == '*') {
		$reqs = GetAllReqCodes(true);
		foreach ($reqs as $reqName)
			auditByLang($langName, $reqName);
	}
	else 
		auditByLang($langName, $reqName);
	$cwAuditReport .= "<hr>\n";
}

////////////////////////////////// strays
// runs upon submission of Auditing dialog
// finds pages that arent part of the system, left hanging around


function scanForStrayPages() {
	global $cwAuditReport;
	global $okCats, $okCWs, $okHelps, $okMains;
	$cwAuditReport .= "<h2>Stray Pages</h2>\n";
	
	$cats = GetAllPages('Category');

	foreach ($cats as $cat) {
//flLog("startin on cat '$cat'");
//flExport($okCats);
		// categories pages: now eliminate the ones that should be here
		if (isset($okCats[$cat])) continue;
//flLog(" not ok $cat");
		if (isAReq($cat)) continue;
//flLog(" not a req $cat");
		if (isALang($cat)) continue;
//flLog(" not a lang either, report $cat");
		$cwAuditReport .= "* Cat Page [[:Category:$cat]] [". 
				cwFullWikiURL("Category:$cat", "action=delete") .
				" (DEL)] not listed in [[:Category:Requirements]].<br>\n";
	}
	unset($cat);

	$CWs = GetAllPages('CrossWise');
	foreach ($CWs as $cw) {
//flLog("startin on cw '$cw'");
		if (isset($okCWs[$cw])) continue;

//flLog("scanForStrayPages: ok CWS $cw $cat ");
		$cwAuditReport .= "* CW Page [[CrossWise:$cw]] [". 
				cwFullWikiURL("CrossWise:$cw", "action=delete") .
				" (DEL)] not listed.<br>\n";
	}

	$Helps = GetAllPages('Help');
//var_export($okHelps);
//var_export($Helps);
	foreach ($Helps as $help) {
//flLog("startin on help '$help'");
		if (isset($okHelps[$help])) continue;
//flLog("scanForStrayPages: ok helps $help  ");
		$cwAuditReport .= "* Help Page [[Help:$help]] [". 
				cwFullWikiURL("Help:$help", "action=delete") .
				" (DEL)] not listed.<br>\n";
	}

	// namespace Main, including all the features
	$pags = GetAllPages('');
	foreach ($pags as $pag) {
		// NS_MAIN pages: should disassemble into a language and need
		$ss = explode('_', $pag, 2);
		if (isset($okMains[$pag])) continue;

		if (isset($ss[1]) && isALang($ss[0]) && isAReq($ss[1])) continue;
		$reason = '';
		if (!isset($ss[1]))
			$reason .= ', no underbars';
		else if (!isAReq($ss[1]))
			$reason .= ", $ss[1] [". 
				cwFullWikiURL("$pag", "action=delete") .
				" (DEL)] is not listed in [[:Category:Requirements]]";
		if (!isALang($ss[0])) $reason .= ", $ss[0] is not a language";
		$cwAuditReport .= "* Page [[$pag]] not listed$reason.<br>\n";
//flLog("scanForStrayPages: page not listed $pag $reason ");
	}
	$cwAuditReport .= "<hr>\n";
}

////////////////////////////////// form
// draw form & service request to verify

// service requests
function cwAudit($input, $args, $parser) {
	global $wgParser, $wgRequest;
	global $cwAuditReport, $cwAuditSitu;
	$language = $wgRequest->getVal('language', 'Ruby');
	$req = $wgRequest->getVal('req', 'Arrays');

	$cwAuditReport = '';
	$cwAuditSitu = 'auditPage';
	if ($wgRequest->getVal('go')) {
		if ($wgRequest->getVal('strays'))
			scanForStrayPages();
		if ($wgRequest->getVal('audit'))
			auditLangFeature($language, $req);
		if ($wgRequest->getVal('sitemap'))  // on verify page!
			buildSiteMap();
		//if ($wgRequest->getVal('verify'))
			//verifyLangFeature($language, $req);
		//if ($wgRequest->getVal('featSrc'))
		//	srcLangFeatureX($language, $req);
	}
	$content = $wgParser->recursiveTagParse($cwAuditReport);
	return $content . drawAuditForm($language, $req);
}

// draw form
function drawAuditForm($language, $req) {
	$langMenu = formatLangMenu($language, 'language', true, true);
	$reqMenu = formatReqMenu($req, true);
//flLog("the req menu iz: '$reqMenu'");
	
	global $wgRequest;
	$stra = 'checked';   // always returns checked anyway $wgRequest->getVal('strays', 'checked');
	$audi = $wgRequest->getVal('audit', '');
	$sitem = $wgRequest->getVal('sitemap', '');
//	$veri = $wgRequest->getVal('verify', '');
	$feSr = $wgRequest->getVal('featSrc', '');
//echo "Herez my flags: stra=$stra audi=$audi veri=$veri feSr=$feSr lang=$language req=$req\n";

	$content = '';
	$content .= "<form><br>\n";
	$content .= "<input type=checkbox name=strays id=strays value=checked $stra>".
		"<label for=strays>Check for Stray Pages</label><br>\n";
	$content .= "language: $langMenu<br>";
	//<input name=language value=".$language."> or ^ for Reqs or * for all<br>\n";
	$content .= "requirement: $reqMenu<br>";
	//<input name=req value='".$req."'> or * for all<br>\n";

	$content .= "<input type=checkbox name=audit id=audit value=checked $audi>".
		"<label for=audit>Audit language & req Pages</label><br>\n";
	$content .= "<input type=checkbox name=sitemap id=sitemap value=checked $sitem>".
		"<label for=sitemap>generate SiteMap</label><br>\n";
//	$content .= "<input type=checkbox name=verify id=verify value=checked $veri>".
//		"<label for=verify>Verify language & req Examples</label><br>\n";
//	$content .= "<input type=checkbox name=featSrc id=featSrc value=checked $feSr>".
//		"<label for=featSrc>FeatSrc language & req Examples</label><br>\n";
	$content .= "<input type=submit name=go value='GO'>\n";
	return $content . "</form>\n";
}

////////////////////////////////// Pages to Back Up

// whole separate http request, from exportAll.sh
function cwPagesToBackUp() {
//flLog("/;;;;;;;;;;;;;;;;;;;;;;;  cwPagesToBackUp");
	
	$cats = GetCategoryPages();
	sort($cats, SORT_STRING);
	$cws = GetCrossWisePages();
	sort($cws, SORT_STRING);
	$helps =GetHelpPages();
	sort($helps, SORT_STRING);
	$mains = GetMainPages();
	sort($mains, SORT_STRING);
	$pages = array_merge($cats, $cws, $helps, $mains);
	$out = join("\n", $pages);
	
//	// ALL of them that exist
//	$out = '';
//	$cats = GetAllPages('Category');
//	$out .= "Category:" . join("\nCategory:", $cats) . "\n";
//	
//	$CWs = GetAllPages('CrossWise');
//	$out .= "CrossWise:" . join("\nCrossWise:", $CWs) . "\n";
//	
//	$Helps = GetAllPages('Help');
//	$out .= "Help:" . join("\nHelp:", $Helps) . "\n";
//	
//	$pags = GetAllPages('');
//	$out .= join("\n", $pags) . "\n";

	return $out;
}



////////////////////////////////////////////////////////////// build SiteMap

// chance each chapter/lang combination has of making it to the sitemap
define('SITEMAP_CHANCE', 0.95);  // adjust this so we have ~< 100 sitemap entries

function bsmAddURL($path, $prio = 0.4, $freq = 'monthly') {
	global $cwAuditReport, $nLinksInSiteMap, $nProposedLinksInSiteMap;

	$nProposedLinksInSiteMap++;
	
	// some there just isn't room for
	////$cwAuditReport .= mt_rand() . " thats the rand and this is the chance ". SITEMAP_CHANCE ."\n";////
	if (mt_rand(0, 1000) / 1000. > SITEMAP_CHANCE) return;
	$nLinksInSiteMap++;

	global $cwSM;
	$rootURL = 'http://'. $_SERVER["HTTP_HOST"] .'/';
	
	$cwSM .= <<<AN_URL_BLOCK
    <url>
        <loc>$rootURL$path</loc>
        <changefreq>$freq</changefreq>
        <priority>$prio</priority>
    </url>

AN_URL_BLOCK;
}

// builds sitemap of view pages for pairs of languages.
// Actual content pages nor req nor lang pages should be in sitemap, just what attracts: view pages.
function buildSiteMap() {
	global $cwAuditReport, $nLinksInSiteMap, $nProposedLinksInSiteMap;
	
	$cwAuditReport .= "<h2>Rebuild Site Map</h2>\n";
	$cwAuditReport .= "Rebuild Site Map: sure its gettin thru \n";
	global $cwSM, $IP;
	$destFileName = $IP . '/sitemap.xml';

	// start of file
	$cwSM = <<<SITE_STUFF
<?xml version="1.0" encoding="utf-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

SITE_STUFF;
	$nLinksInSiteMap = $nProposedLinksInSiteMap = 0;
	
	// now add urls that should be in the sitemap.
	bsmAddURL('index.php/Main_Page', 1.0, 'yearly');

	// all of the content of each chapter as pairs of langauges
	// we want to omit pages that don't exist yet; this lists existing ones
	global $allTheLangs;
	$isARealPage = GetAllPages('', true);
	////flExport($isARealPage);
	
	// for each req, list all pairs of languages where both feature pages exist
	foreach (GetAllReqCodes() as $reqCode) {
	
		foreach ($allTheLangs as $langCodeA => $langObjA) {
			$does = $langCodeA .'_'. $reqCode;
			if (isset($isARealPage[$does])) {
				$bonusA = ($langCodeA == 'PHP' || $langCodeA == 'JavaScript')
					? 0.2 : 0;
				foreach ($allTheLangs as $langCodeB => $langObjB) {
					// omit same language cases.  Eliminate reorders of langs.
					$izABee = $langCodeB .'_'. $reqCode;
					if ($langCodeA < $langCodeB &&
								isset($isARealPage[$izABee])) {
						$bonusB = ($langCodeB == 'PHP' || $langCodeB == 'JavaScript')
							? 0.2 : 0;
						$prio = 0.5 + $bonusA + $bonusB;
						////flLog("adding link for $reqCode in $langCodeA vs $langCodeB, prio $prio");
						bsmAddURL("++$reqCode/$langCodeA,$langCodeB", $prio);
					}
				}
			}
		}
	}

	
	
	// end of file
	$cwSM .= <<<SITE_STUFF
</urlset>

SITE_STUFF;
	$cwAuditReport .= "done with site map building.<br>";
	$cwAuditReport .= "$nLinksInSiteMap generated for $nProposedLinksInSiteMap proposed links.<br><hr>\n";

	if (! file_put_contents($destFileName, $cwSM)) {
		flLog("Error writing sitemap file(($destFileName)): ". posix_strerror(posix_get_last_error()));
	}
	else {
		flLog("Done");
		@chmod($destFileName, 0666);  // whoever creates it should chmod it, maybe that's us
		//passthru("ls $destFileName");
		//passthru("cat $destFileName");
		
		// hey!  Tell some search engines.
/*		It's pretty simple all you need to do is send a HTTP request to:
http://search-engine-ping-url-goes-here/ping?sitemap=sitemap_url
A few notes:

The search engine ping url for google is http://www.google.com/webmasters/tools/ping
Your sitemap url should be URL encoded, that means use the URLEncodedFormat function if your using ColdFusion.
If the ping is valid, it will return a HTTP 200 OK response code.


ok so like this
http://www.google.com/webmasters/tools/ping?sitemap=http://cw.tactileint.com/sitemap.xml



PJ Hyett has discovered that his out of date google sitemap was the reason for his drop in traffic from google. So if you created a sitemap when they first came out and forgot about it, you either want to keep it updated (creating it dynamically would be a good way to go), or remove it from google.

I went through the whole drawn out process, added my site to their service, and completely forgot about it...
I had a feeling that this was why my site stopped appearing on Google... I promptly deleted the link to my site on Sitemaps. 
Sure enough, after checking this afternoon, this site appears again on Google when you search for my name. My advice to you is not to use Sitemaps unless you stay on top of it, who knows how much search engine traffic I've lost.




*/

	}
}


/////////////////////////////////////////////////////////////////////// Test Links

// called only on the 'Test Links' page
function cwTestLinks($input, $args, $parser) {
	global $wgScriptPath, $wgScript, $cwHttpHost;

	if (!isset($_SERVER)) return '';  // if run during an import or other cmd line thing
	
	$output = '';
	$uname = php_uname('n');
	
	// output links table - so user can test them all on all servers
	$output .= "{| border=1\n";
	$output .= "|+ Test All Servers & Flag Combinations\n";
	foreach (array($uname, 'kashmir', 'tactileint.com') as $server) {
		$output .= "|-\n";
		foreach (array('', 'd', 't', 'dt') as $cwFlags)
			$output .= "| [http://{$cwFlags}cw.$server/index.php/Test_Links {$cwFlags}cw.$server] \n";
	}
	$output .= "|}\n\n";

	// the links to test: wiki [[bracket]] links
	$output .= "{| border=3 id=WikiLinkTestCases\n";
	$output .= "|+ Test All Syntaxes of Wiki Links\n";
	$output .= "| http://salon.com\n";
	$output .= "| [http://salon.com salon]\n";
	$output .= "| [[Ruby_Arrays]]\n";
	$output .= "| [[:Category:Arrays]]\n";
	$output .= "|}\n";

	// the links to test: cw
	$testChapters = array('Arrays', 'Binary Data', 'IO, Files, Filesystem');
	$output .= "{| border=2 id=CWLinkTestCases\n";
	$output .= "|+ Test All Syntaxes of cw Links - should all result in identical links\n";
	foreach ($testChapters as $chapter) {
		$output .= "|-\n";
		$chapterCode = titleToCode($chapter);
		
		// All of these should produce the same link url!!
		// with ch attribute, with space
		$output .= "| <cw ch='$chapter' />\n";
		$output .= "| <cw ch=\"$chapter\" />\n";
		$output .= "| <cw ch='$chapterCode' />\n";
		$output .= "| <cw ch=\"$chapterCode\" />\n";
		$output .= "| <cw ch=$chapterCode />\n";

		// with ch attribute, no space
		$output .= "| <cw ch='$chapter'/>\n";
		$output .= "| <cw ch=\"$chapter\"/>\n";
		$output .= "| <cw ch='$chapterCode'/>\n";
		$output .= "| <cw ch=\"$chapterCode\"/>\n";
		$output .= "| <cw ch=$chapterCode/>\n";

		// no ch attribute
		$chapterEnc = strpos($chapter, ',') ? 'ch='. $chapterCode : strtoupper($chapter);  // punt on punct
		$output .= "| <cw $chapterEnc />\n";
		$output .= "| <cw $chapterEnc/>\n";
	}
	$output .= "|}\n";
//flLog($output);

	// now run all those thru the parser
	$output = $parser->recursiveTagParse($output);
	
	// now the JS that tests it.  Indenting by one space surrounds it 
	// with <pre> but also makes sure it is otherwise  unmolested by <p> tags and stuff
	// hmm maybe theres a better way
	$output .= " <script src=$wgScriptPath/skins/common/prototype.js type=text/javascript></script>\n";
	$output .= " <script type=text/javascript>\n";
	$output .= " function TestAllLinks() {\n";
	$output .= "   var errors = '';\n";
	$output .= " \n";
	$output .= "   var waTags = $('#WikiLinkTestCases').find('a');\n";
$output .= " console.log(waTags[0].href);\n";
$output .= " console.log(waTags[1].href);\n";
$output .= " console.log(waTags[2].href);\n";
$output .= " console.log('http://". $cwHttpHost . 
				$wgScript ."/Ruby_Arrays');\n";
$output .= " console.log(waTags[3].href);\n";
$output .= " console.log('http://". $cwHttpHost . 
						$wgScript ."/Category:Arrays');\n";
	$output .= "   if (waTags[0].href!= 'http://salon.com/')\n";
	$output .= "     errors += '1st `http://salon.com` failed \\n';\n";
	$output .= "   if (waTags[1].href!= 'http://salon.com/')\n";
	$output .= "     errors += '2nd `http://salon.com` failed \\n';\n";
	$output .= "   if (waTags[2].href!= 'http://". $cwHttpHost . 
									$wgScript ."/Ruby_Arrays')\n";
	$output .= "     errors += '3rd `Ruby_Arrays` failed \\n';\n";
	$output .= "   if (waTags[3].href!= 'http://". $cwHttpHost . 
									$wgScript ."/Category:Arrays')\n";
	$output .= "     errors += '4th `:Category:Arrays` failed \\n';\n";
	$output .= " \n";
	$output .= "   var trTags = $('#CWLinkTestCases').find('tr');\n";
	$output .= "   var chapters = ". json_encode($testChapters) .";\n";
	$output .= "   var chapURLs = ". 
					json_encode(array_map('cwFullViewURL', $testChapters)) .";\n";
	$output .= "   for (var row = 0; row < chapters.length; row++) {\n";
	$output .= "     var chapter = chapters[row];\n";
	$output .= "     var aList = trTags[row].select('a');\n";
	$output .= "     for (var col = 0; col < aList.length; col++) {\n";
	$output .= "   	   var aTag = aList[col];\n";
	$output .= "   	   if (aTag.href!= chapURLs[row])\n";
	$output .= "   	     errors += '`'+ chapter +'` col'+ col +' failed\\n';\n";
//$output .= "       console.dir(aTag);\n";
$output .= "       console.debug(chapURLs[row]);\n";
$output .= "       console.debug(aTag.href);\n";
	$output .= "     }\n";
	$output .= "   }\n";
	$output .= " \n";
	$output .= "   if (errors)\n";
	$output .= "     $('#testRes').html(errors);\n";
	$output .= "   else\n";
	$output .= "     $('#testRes').html('<h1>All Link Tests Succeeded</h1>');\n";
	$output .= " }\n";
	$output .= " \n";
	$output .= " </script>\n";
	$output .= "<button onclick='TestAllLinks()'>Test</button>\n";
	$output .= "<pre id=testRes>click the button</pre>\n";
	
	return $output;
}


// called by a hook as edit page is being assembled
function cwAddAuditToEditPage($editPage) {
	global $cwAuditReport, $wgTitle;
	
	if (0 == $wgTitle->getNamespace()) {
		$tiObj = $editPage->mTitle;
//flLog("title s = ? "+ ($tiObj === $wgTitle));
		$title = $tiObj->getText();
//flLog("page title: `$title`");
		$tisplit = explode(' ', $title, 2);
		
		// if this is a feature page, audit now and display results for user to fix.
		if (isALang($tisplit[0])) {
	
			$cwAuditReport = '';
			$cwAuditSitu = 'editPage';
			auditAFeature($tisplit[0], $tisplit[1]);
			
			if ($cwAuditReport)
				$editPage->editFormTextTop = "<h3 style=color:red>Please Fix Audit Glitches:</h3>$cwAuditReport<br>\n";
			else
				$editPage->editFormTextTop = "<h3>Audit: Existing Page Passes.</h3><br>\n";
		}
	}

	return true;
}



