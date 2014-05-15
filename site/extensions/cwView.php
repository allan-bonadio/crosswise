<?php
//
//  View  --  the main pages users see, comparing features side-by-side
//
 if(! defined('MEDIAWIKI')) { echo("\n"); die(-1);}

require_once('cwLangBox.php');

///////////////////////////////////////////////////////////////////// Individual rows or cells

//// generate html for the yellow banner row AT THE TOP that lists all sub-chapters, 
////  tr to tr.  This is lame.
//function formatSubChapterLinks($reqCode, $nLangs) {
//	global $eachReqParent;
//	GetAllReqCodes();  // for every page load?  could precompile...
//	
//	// ok so who are we parents of
////flExport($eachReqParent);
//	$kids = array_keys($eachReqParent, $reqCode);
//	if (0 >= count($kids))
//		return '';
//	
//	$s = "<tr class=needRow><td colspan=$nLangs>See also these subchapters: ";
//	foreach ($kids as $kid) {
//		$s .= " &nbsp; | &nbsp; <a class=copperplate href=". cwViewURLPath($kid) .">". codeToTitle($kid) ."</a>";
//	}
//	return $s ."</td></tr>\n";
//}

// generate html for a yellow banner row that describes a need, 
// above the rules for that need.  tr to tr.  Once per need.
// includes <a name> tag so you can find it with a url
function formatNeedRow($nLangs, $needTitle, $needDesc, $kind = '-') {
	global $wgParser, 	$wgScriptPath;
	$needDesc = $wgParser->recursiveTagParse($needDesc);

	// a 'link' to another chapter
	if ($kind == '+')
		return "<tr class=needRow><td colspan=$nLangs class=linkRow ><a href='". 
			cwViewURLPath($needTitle) .
			"'>$needTitle <img width=20 height=20 ".
			"src=	$wgScriptPath/skins/crosswise/arrowRight.png />".
			"</a><small class=needDesc>$needDesc</small></td></tr>\n";

	// a regular need row.  It has an <a anchor
	return "<tr class=needRow><td colspan=$nLangs><a name='". 
		titleToCode($needTitle) .
		"'>$needTitle <small class=needDesc>$needDesc</small></a></td></tr>\n";
}

// draw one rule cell of the table.  input is raw wiki, or not if none for this rule.
// in the formatRule() state machine, see if you need to change state, 
// and if so, spew out the necessary html
// return taken rule, with our magic markup processed, for one rule (one need x one lang)
function formatRule($input, $cssClass, $varPat) {
	global $wgParser;
	
	// null means none specified for this language/need
	if (!$input)
		return "\n<td class=$cssClass>\n</td>\n";
	
	return "\n<td class=$cssClass>\n". $wgParser->recursiveTagParse($input) ."</td>\n";
}

// generate html for the row of rules for a given need.  tr to tr
// relies on $ChosenFeatures global
function formatRulesRow($needCode) {
	global $ChosenFeatures;
	$html = "<tr class=rulesRow>\n";

	// print each language's rule for the need.  Remember that each $feat 
	// might have null rules if there's no such page.
	foreach ($ChosenFeatures as $feat) {
		$ru = $feat->takeRule($needCode);
//flLog("format row: $needCode");
//flExport($ru);
		if ($ru)
			$html .= formatRule($ru, $feat->lang->langName ."Col", $feat->lang->varPat) ."\n";
		else
			$html .= "<td></td>";  // must be a better idea...
	}
	return $html . "</tr>\n";
}

////// draw row with radios that change the language
////function formatLangChoiceRow() {
////	global $ChosenLangs, $ChosenFeatures, $allTheLangs;
////
////	// the language row, headings for each lang column
////	$content = "<tr id=langChoiceRow style=display:none>";
//////flExport($ChosenFeatures);
////
////	// for each column
////	foreach ($ChosenFeatures as $col => $feat) {
////		$content .= "<td style=vertical-align:top' ><div>choose new language for this column:</div>\n";
////		foreach ($allTheLangs as $la){
////			$content .= "<div class=chooseLangsRadios style='background-color:$la->headColor' >".
////					"<input type=radio name=colLang$col id=colLang$la->code$col value=$la->code ". 
////					($feat->lang->code == $la->code ? 'checked />' : '/>') .
////					"<label for=colLang$la->code$col>$la->title</label></div>\n";
////		}
////
////		// 'none' entry
////		$content .= "<div class=chooseLangsRadios style='float:left; background-color:#eee' >".
////				"<input type=radio name=colLang$col id=colLangNone$col value=None ". 
////				"/>".
////				"<label for=colLangNone$col>None</label></div>\n";
////
////		// submit button, only on last col
////		if ($col == count($ChosenFeatures)-1)
////			$content .= "<br clear=both /><div style=float:right>".
////					"<input type=submit name=ChLangCols value='Save Changes' />".
////					"</div>\n";
////	}
////
////	 
///////			$content .= "<input type=radio name=colLang$col id=colLang". $langF->lang ."$col value=". $langF->lang ." $checked/>";
///////			$content .= "<label for=colLang". $langF->lang ."$col>". $langF->lang ." ". $langF->vers ."</label>";
///////			$content .= "</td>\n";
////
////
////	
////			//"<a class=editBut href='/index.php?title=".
////			//		$lang->langName .'_'. $reqCode //."&action=edit'>edit</a>\n";
////	return $content ."</tr>\n";
////}

// draw top row with lang names in big letters; 
// also rounded corners for whole table
function formatLangTitlesRow($reqCode) {
	global $ChosenLangs, $ChosenFeatures, $wgUser;

	// round all corners for the whole table.
	// this has to go inside the table somewhere.  anywhere. 
	// but just once.  Also table must be relative.
	////$cornerStuff = "<div class=nwCorn></div>\n".
		////	"<div class=neCorn></div>\n".
			////"<div class=swCorn></div>\n".
	////		"<div class=seCorn></div>\n";
	$cornerStuff = '';

	// the language row, headings for each lang column
	$content = "<tr class=langRow>";
//flExport($ChosenFeatures);
flExport('ChosenLangs', $ChosenLangs);////
	foreach ($ChosenLangs as $lang) {
		$content .= "<th class=". $lang->langName ."Col>";
		if (!$wgUser->isAnon())
			$content .= drawHtmlEditBut($lang->langName .'_'. $reqCode, 
				' style=display:block;float:right');
			
			//"<a class=editBut href='/index.php?title=".
			//		$lang->langName .'_'. $reqCode //."&action=edit'>edit</a>\n";
		$content .= $lang->langName ." ". $lang->vers . $cornerStuff ."</th>\n";
		$cornerStuff = '';
	}
	return $content ."</tr>\n";
}

// draw the <style> block we need
function drawViewStyles() {
	global $ChosenLangs, $wgScriptPath;

	// Avoid \n cuz it turns into <p>
	$s = <<< EOSTYLES
<style>
table.rulesTab {background:#444; position:relative; width:100%; table-layout:fixed}
.langRow {background:#ffc; font-size: 140%; 
    font-weight: bolder; text-align:center}
tr.needRow {}
tr.needRow td {padding-top:3px;padding-left: 6px;background:#ffc}
tr.needRow td.linkRow a {vertical-align: middle; padding:3px}
small.needDesc {float:right; font-size: 80%; 
    padding-top: .2em; padding-right: .4em; color:#880}
tr.rulesRow { }
tr.rulesRow td {padding-left: 6px; vertical-align:top; color:#444; font-size: 80%;}
tr.rulesRow td pre {margin-left: -6px; font-size: 120%}
tr.langChoiceRow { }
tr.langChoiceRow td {padding: 2px}
div.langChoiceButton {padding: 2px 1em; }
div.langChoiceButtonLook {border: outset gray 4px; cursor:pointer; 
		text-align: center; font-size: 140%; font-weight: bolder}

EOSTYLES;

	// bg colors for each column
	foreach ($ChosenLangs as $lang) {
		$s .= "th.". $lang->langName ."Col {background-color:". $lang->headColor ."} ";
		$s .= "col.". $lang->langName ."Col   {background-color:". $lang->colColor  .";} ";
		$s .= "td.". $lang->langName ."Col pre {background-color:". $lang->examColor ."} ";
	}
	$s = str_replace("\n", ' ', $s) . "</style>\n";
//flLog("this is s: ". dumpText($s));

	// for the corner rounding.  IE6 cant do it.
	// lets use css3 corner rounding.  someday.  image is gone.
	$cimg = 'transparent'; ////"url($wgScriptPath/skins/crosswise/cwCorners.gif)";
	$c = <<< EOCORNER
<![if gte IE 7]><style>
div.neCorn {position: absolute; width:6px; height:6px; right:0; top:0;
 background: $cimg no-repeat right top}
div.nwCorn {position: absolute; width:6px; height:6px; left:0; top:0;
 background: $cimg no-repeat left top}
div.seCorn {position: absolute; width:6px; height:6px; right:0; bottom:0;
 background: $cimg no-repeat right bottom}
div.swCorn {position: absolute; width:6px; height:6px; left:0; bottom:0;
 background: $cimg no-repeat left bottom}
EOCORNER;
	$c = str_replace("\n", ' ', $c) . "</style><![endif]>\n";
//flLog("this is c: ". dumpText($c));
	
	return $s . $c;
}

		// this is needed to make word wrap correctly outside of IE, and to work 
		// at all in IE.  6 & 7 dont have pre-wrap, so you get a scroll bar.  ugh. 
		//$s = "<!--[if IE]><style>pre {white-space: pre; //word-wrap:break-word;}</style><![endif]-->\n".
			//"<![if !IE]><style>pre {white-space: pre-wrap;}</style><![endif]>\n";

////////////////////////////////////////////////// draw whole table

// actually draw the table for view
function drawViewTable(array $args, $reqCode) {
	global $wgRequest, $wgScriptPath;
	global $ChosenLangs, $ChosenFeatures;
	////flLog("drawViewTable- ". var_export($args, true) . $reqCode);
	////var_dump('ChosenLangs: ', $ChosenLangs);

	// figure out the colums
	$nLangs = count($ChosenLangs);
	$ChosenFeatures = array();
	foreach ($ChosenLangs as $lang)
		$ChosenFeatures[] = new cwFeature($lang->langName, $reqCode);
	// remember that a feature will be stunted (rules undefined) if there wasnt a page
	
	$content = '';  ////"<script src=$wgScriptPath/skins/common/prototype.js type=text/javascript></script>\n";
	//$wid = ($nLangs ? (100 / $nLangs) : 100) . '%';
	$content .= drawViewStyles();
	//$content .= "<br clear=right style='height: 8px'>";

	// This form is ... ?? in case user clicks edit?
	$content .= "<form method=post><table class=rulesTab>";
	
	// the col elements including col bg color
	foreach ($ChosenLangs as $lang)
		$content .= "<col class=". $lang->langName ."Col style=display:table-column;visibility:on />";
//Hey this last line: correct it if youre fixing debug stuff.	
	//$content .= formatLangChoiceRow();
	$content .= formatLangTitlesRow($reqCode);
	
	
	
	//$content .= formatSubChapterLinks($reqCode, $nLangs);

	

	// print each language's overview for this feature
	$content .= "<tr class=rulesRow>\n";
	foreach ($ChosenFeatures as $feat) {
		$ov = isset($feat->overview) ? $feat->overview : '';
//flLog("lang overviews row: {$feat->lang->langName} overview");
//flExport($ov);
		if ($ov)
			$content .= formatRule($ov, $feat->lang->langName ."Col", $feat->lang->varPat) ."\n";
		else
			$content .= "<td></td>";  // must be a better idea...
	}
	$content .= "</tr>\n";

	// but the order of rules displayed comes from the needs list for this feature.
	// break up into individual needs and make rows for each
	$req = new cwReq($reqCode);
	foreach($req->needs as $i => $need) {
//flLog("a need: formatNeedRow($nLangs, $need->title=$need->name, $need->desc);");
		$content .= formatNeedRow($nLangs, $need->title, $need->desc, $need->kind);
		$content .= formatRulesRow($need->name);
	}
	
//flLog("drawViewTable( start wildcat ");
	// ok so much for the Official needs.  How about needs that are in the rules but not the reqs?
	// go picking thru unrendered rules
	foreach ($ChosenFeatures as $oFeat) {
		if (isset($oFeat->rules)) {
			// so for each lang-feature, go thru its lefover rules
			// need=title for need-rule   rule=raw text of rurle
			foreach ($oFeat->rules as $needCode => $rule) {
				if (!$oFeat->isRuleTaken($needCode)) {
					// pretend the need is cross-lang for now
					$content .= formatNeedRow($nLangs, codeToTitle($needCode), 'no description');
					$content .= formatRulesRow($needCode);
					// that will take that need out of each of the ChosenFeatures.
					// Eventually we'll get them all out.
	
				}
			}
		}
	}
	$content .= "</table></form>\n";
	
////flLog("drawViewTable done ");
//flLog("content=`$content` ");
	//$input = $parser->recursiveTagParse($input);
	return $content;
}

// Draw THE view table.  Column languages as specified, 
// row rules in req order for given req.
// This is a MW tag handler.
function cwView($input, $args, $parser) {
	global $wgRequest;
	////flLog("starting cwView()");

	loadChosenLangs();

////var_dump($args);////
////flExport($args);////
////flLog("cwView(input, args, parser)");////

	// this generates html for the (as yet invisible) language choooser box 
	// put this somewhere; hidden
	$lcd = drawLangChoiceDialog();
////flLog("drawLangChoiceDialog() output length: ". strlen($lcd));////
////flLog("drawLangChoiceDialog() whole texxt sheesh: `$lcd`");////

	// if no req specified on url line, give em the TOC. 
	$reqCode = $wgRequest->getVal('ch', '');
	$r = explode('/', $reqCode, 2);  // strip off possible langs
	$reqCode = $r[0];
	
	////flLog("finishing cwView() with reqCode='$reqCode'");
	if ($reqCode && $reqCode != 'choose')
		return $lcd . drawViewTable($args, $reqCode);
	else
		return $lcd . cwViewTOC();
}

/////////////////////////////////////////////////////// View TOC

// draw a 'table of contents' or other kind of list of requirements.
// entry is the template for each req, use entryStr() on output
// deep is string to insert if nesting level goes deeper
// shallow is string to insert if nesting level goes more shallow
function drawReqList($entry, $deep, $shallow) {
	global $allReqLevels;
	
	$levelNow = 0;
	$content = '';
	foreach (GetAllReqCodes() as $r => $reqCode) {
		while ($levelNow < $allReqLevels[$r]) {
			$content .= $deep;
			$levelNow++;
		}

		while ($levelNow > $allReqLevels[$r]) {
			$content .= $shallow;
			$levelNow--;
		}

		$e = str_replace('|title|', codeToTitle($reqCode), $entry);
		$content .= str_replace('|viewUrl|', cwViewURLPath(urlencode($reqCode)), $e);
	}

	// close all those dangling levels
	while ($levelNow > 0) {
		$content .= $shallow;
		$levelNow--;
	}

	return $content;
}

// tag handler for <cwViewTOC;  it ignores its arguments
function cwViewTOC($input=null, $args=null, $parser=null) {
	return drawReqList("<li><a href=|viewUrl|>|title|</a></li>\n", "<ul>\n", "</ul>\n");
}

///////////////////////////////////////////////////////////////// If  tag
// not done.  never tested or even run.
function verscmp($vers1, $vers2) {
	$split1 = explode('.', $vers1);
	$n1 = count($split1);
	$split2 = explode('.', $vers2);
	$n2 = count($split2);
	for ($i = 0; $i < min($n1, $n2); $i++) {
		$seg1 = $split1[$i];
		$seg2 = $split2[$i];
		if (pcre_match('/^\d+$/', $seg1) && pcre_match('/^\d+$/', $seg2))
			$cmp = ((int) $seg1) - ((int) $seg2);
		else
			$cmp = strcmp($seg1, $seg2);
		if ($cmp)
			return $cmp;
	}
	// then the longest one wins
	return $n1 - $n2;
}

// not done.  never tested or even run.
function ifHandler($input, $args, $parser) {
	// do we really need this?  how about if the lang is implied.
	if (isset($args['lang'])) {
		$lang = $args['lang'];
	}

	// all these are And-ed together.   Like since=5.0 and before=7.0
	$output = $input;
	if (isset($args['before']) && verscmp($vers, $args['before']) >= 0)
		$output = '';
	if (isset($args['after']) && verscmp($vers, $args['after']) <= 0)
		$output = '';
	if (isset($args['until']) && verscmp($vers, $args['until']) > 0)
		$output = '';
	if (isset($args['since']) && verscmp($vers, $args['since']) > 0)
		$output = '';
	return $output;
}


