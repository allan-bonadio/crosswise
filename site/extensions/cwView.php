<?php
//
//  View  --  the main pages users see, comparing features side-by-side
//
 if(! defined('MEDIAWIKI')) { echo("\n"); die(-1);}

/////////////////////////////////////////////////////////// new new lang choice popup dialog

function drawLangChoiceDialog() {
	global $cwNChLangCols;
	global $ChosenLangs, $ChosenFeatures, $allTheLangs;
	$s = '';
	

//<!--[if lt IE 7]><style>#hazyBox {left:8px;right:8px;top:6em;}</style><![endif]-->
//<!--[if gte IE 7]><style>#hazyBox {left:1px;right:1px;top:6em;}</style><![endif]-->
//<![if !IE]><style>#hazyBox {left:0;right:0;top:6em;}</style><![endif]>


	// the hazy layer - houses the lang selection (hazy) box
	$s .= <<<HAZYSTUFF
<div id=hazyLayer style='position:fixed; left:8px;right: 8px; height:100%; background-color:rgba(255,255,255,0.75); z-index:10; display:none;'>
<div style=height:4em; ></div>

HAZYSTUFF;

	// the style for the hazyBox; the dialog outer frame
	$s .= "<div id=hazyBox style='z-index:20; border-style: solid none; border-color: #ccc; border-width: 2px; ".
		"background-color:#fff'>";

	// start off with more cols than you need; hide the extra ones till the user clicks +
	$nChLangCols = count($ChosenLangs) + 10;
	if (count($ChosenLangs))
		$wid = (100 / count($ChosenLangs)) . '%';
	else
		$wid = '100%';
	$s .= "<form id=langChoiceForm method=post style='padding:1em 0'>";
	
	// plus button
	$s .= "<div ><div id=plusButton title='click to add YET ANOTHER column' style='float:right; cursor:pointer; width: 98px; height:39px; background-image: url(/skins/common/images/PlusButton.png);'></div>\n<br clear=right /><div></div></div>\n";
	////$s .= "<div ><div id=plusButton style='float:right;border:solid 2px #000; border-bottom: none;color:black; background-color:ff0; padding: 0 .2em; cursor:pointer; font-size:120%'>click to add YET ANOTHER column + </div>\n<br clear=right /><div></div></div>\n";
	$s .= "<table class=rulesTab>";
	substr_replace($s, "", -7, 0);

	// headings with titles for each column (Column 0, ...)
	$s .= "<tr class=langRow>";
	for ($col = 0; $col < $nChLangCols; ++$col) {
		$vis = ($col < count($ChosenLangs)) ? 'table-cell' : 'none';
		$s .= "<th id=lcCol_$col style=display:$vis>Column $col</th>";
	}
	$s .= "</tr>\n";
	
	// now one row for each language (ultimately: each language version)
	foreach($allTheLangs as $langF) {
		$s .= "<tr class=langChoiceRow>";
		for ($col = 0; $col < $nChLangCols; ++$col) {
			$vis = ($col < count($ChosenLangs)) ? 'table-cell' : 'none';
			$s .= "<td style=display:$vis><div id=but{$langF->langName}_$col class='langChoiceButton langChoiceButtonLook' ". 
				"style=background-color:#fff >";
			$s .= $langF->langName .' '. $langF->vers;
			//$checked = (isset($ChosenLangs[$col]) && $langF->lang == $ChosenLangs[$col]->lang) 
			//	? 'checked ' : '';
			//$s .= "<input type=radio name=colLang$col id=colLang". $langF->lang ."$col value=". $langF->lang ." $checked/>";
			//$s .= "<label for=colLang". $langF->lang ."$col>". $langF->lang ." ". $langF->vers ."</label>";
			$s .= "</div></td>\n";
		}
		$s .= "</tr>";
	}

	// one row for No Language
	$s .= "<tr class=langChoiceRow>";
	for ($col = 0; $col < $nChLangCols; ++$col) {
		$vis = ($col < count($ChosenLangs)) ? 'table-cell' : 'none';
		$s .= "<td style=display:$vis><div id=butnone_$col class='langChoiceButton langChoiceButtonLook' ". 
			"style=background-color:#fff  >";
		$s .= 'none';
		//$checked = !isset($ChosenLangs[$col]) ? 'checked ' : '';
		//$s .= "<input type=radio name=colLang$col id=colLangNoDisp$col value=none $checked/>";
		//$s .= "<label for=colLangNoDisp$col>none</label>";
		$s .= "</div></td>\n";
	}
	$s .= "</tr></table>";
	
	// help em out
	$s .= "<div style=text-align:center;padding:1em;>click None to remove a column</div>";
	$s .= "<div class='langRow langChoiceButtonLook' style='padding: 1em; border: outset gray 4px; text-align: center; '>click here to start using your new arrangement</div>";


	//  OK and Cancel lame-ass html buttons languages
	////$s .= <<<HAZYSTUFF
	////		<div style='padding:2px 1em;float:right;text-align:right;'>
	////			<input type=reset name=cancel 
	////				value=' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Cancel &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ' 
	////				onclick='onLangCancel()' style='font-size:140%' />
	////			<input type=submit name=ChLangCols 
	////				value=' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Go &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ' 
	////				onclick='onLangSubmit()' style='font-size:140%' />
	////		</div>
	////HAZYSTUFF;

	// end the form with the hidden item that conveys the new languages
	$s .= <<<HAZYSTUFF
			<br clear=right /><div></div>
		</form>
	</div>
</div>

HAZYSTUFF;

	$jsTheLangs = '{';
	foreach ($allTheLangs as $langName => $langObj)
		$jsTheLangs .= "$langName: {onColor: '$langObj->headColor', offColor: '$langObj->examColor'}, ";
	$jsTheLangs .= "none: {onColor: '#888', offColor: '#ccc'}}";
	
	$jsChosenLangs = '["';
	for ($col = 0; $col < count($ChosenLangs); ++$col)
		$jsChosenLangs .= $ChosenLangs[$col]->langName .'", "';
	if (! $jsChosenLangs)
		$jsChosenLangs = '"JavaScript", "PHP"';
	else
		$jsChosenLangs = substr($jsChosenLangs, 0, -3) ."]";

	// unless we do this, wiki spits <p> stuff all over  it
	global $parserAfterTidyText;
	$s .= '||ParserAfterTidy||';

	$parserAfterTidyText = <<<HAZYSCRIPTS
<script>

var origLangSettings = $jsChosenLangs;
var langSettings = new Array(origLangSettings.length);
var theLangs = $jsTheLangs;
////function $(id) {return document.getElementById(id)}

// set button assembly in col to given lang in response to user click or whatever
function setLangChoiceCol(col, lang) {
	langSettings[col] = lang;
	for (var la in theLangs) {
		var but = $('but'+ la +'_'+ col);
		if (langSettings[col] == la) {
			but.style.backgroundColor = theLangs[la].onColor;
			but.style.borderStyle = 'inset';
		}
		else {
			but.style.backgroundColor = '#fff';  //theLangs[la].offColor;
			but.style.borderStyle = 'outset';
		}
	}
}

function resetLangSettings() {
	var col;
	for (col = 0; col < origLangSettings.length; col++)
		setLangChoiceCol(col, origLangSettings[col]);
	while (langSettings.length > origLangSettings.length)
		langSettings.pop();
}

function downLangChoiceButton(event) {
	var butEl = event.target || event.srcElement;
	butEl.style.backgroundColor = '#000';
}

// a click on one of the cells in this table.  don't sumit yet!
function clickLangChoiceButton(event) {
	var butEl = event.target || event.srcElement;
	
	// which button is it
	var id = butEl.id.split('_', 2);
	var lang = id[0].substr(3);
	var col = id[1];
	setLangChoiceCol(col, lang);
	event.stop();
}


var columnActivateDisplay = 'table-cell';

function plusClick(event) {
	var newCol = langSettings.length;
	var h = $('lcCol_'+ newCol);
	if (!h) return true;
	try {
		h.style.display = columnActivateDisplay;
	} catch (e) {
		// IE strikes again.  v7 and 6.
		h.style.display = columnActivateDisplay = 'block';
	}
	
	for (var la in theLangs) {
		var td = \$('but'+ la +'_'+ newCol).parentNode;
		td.style.display = columnActivateDisplay;
		//td.style.display = 'table-cell';
	}
	setLangChoiceCol(newCol, 'none');
	
	event.stop();  // or the background will submit it!
}

function onLangSubmit(ev) {
	\$('hazyLayer').style.display = 'none';  // instant feedback

	// take our url and chop off existing lang codes
	var href = location.href
	// 1rmore slashes, plus plus, 1rmore nonslashes,slash, nonslashes to the end
	// get rid of last segment.  if there.
	href = href.replace(/\/+(\+\+[^\/]+)\/[^\/]+$/, '/$1');
	
	// slap on the new language codes.  But Ulp! omit None entries
	href += '/' + langSettings.join(',').replace(/,none/g, '').replace(/^none,/, '');
	ev.stop();
	location.href = href;
	// doesn't even submit!  never gets here. \$('langChoiceForm').submit();
}

function onLoadLangChoiceDialog() {
	resetLangSettings();
	$('hazyLayer').observe('click', onLangSubmit);
	
	// all the handlers for all the buttons
	var buts = document.getElementsByClassName('langChoiceButton');
	for (b = 0; b < buts.length; b++) {
		buts[b].observe('click', clickLangChoiceButton);
		buts[b].observe('mousedown', downLangChoiceButton);
	}
	$('plusButton').observe('click', plusClick);
	$('plusButton').observe('mousedown', function() {
			$('plusButton').style.backgroundImage = 'url(/skins/common/images/PlusButtonPressed.png)';
		});
	$('plusButton').observe('mouseup', function() {
			$('plusButton').style.backgroundImage = 'url(/skins/common/images/PlusButton.png)';
		});
}

addOnloadHook(onLoadLangChoiceDialog);


</script>
HAZYSCRIPTS;

	return $s;
}


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
			"src=	$wgScriptPath/skins/common/images/arrowRight.png />".
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

// draw row with radios that change the language
function formatLangChoiceRow() {
	global $ChosenLangs, $ChosenFeatures, $allTheLangs;

	// the language row, headings for each lang column
	$content = "<tr id=langChoiceRow style=display:none>";
//flExport($ChosenFeatures);

	// for each column
	foreach ($ChosenFeatures as $col => $feat) {
		$content .= "<td style=vertical-align:top' ><div>choose new language for this column:</div>\n";
		foreach ($allTheLangs as $la){
			$content .= "<div class=chooseLangsRadios style='background-color:$la->headColor' >".
					"<input type=radio name=colLang$col id=colLang$la->code$col value=$la->code ". 
					($feat->lang->code == $la->code ? 'checked />' : '/>') .
					"<label for=colLang$la->code$col>$la->title</label></div>\n";
		}

		// 'none' entry
		$content .= "<div class=chooseLangsRadios style='float:left; background-color:#eee' >".
				"<input type=radio name=colLang$col id=colLangNone$col value=None ". 
				"/>".
				"<label for=colLangNone$col>None</label></div>\n";

		// submit button, only on last col
		if ($col == count($ChosenFeatures)-1)
			$content .= "<br clear=both /><div style=float:right>".
					"<input type=submit name=ChLangCols value='Save Changes' />".
					"</div>\n";
	}

	 
///			$content .= "<input type=radio name=colLang$col id=colLang". $langF->lang ."$col value=". $langF->lang ." $checked/>";
///			$content .= "<label for=colLang". $langF->lang ."$col>". $langF->lang ." ". $langF->vers ."</label>";
///			$content .= "</td>\n";


	
			//"<a class=editBut href='/index.php?title=".
			//		$lang->langName .'_'. $reqCode //."&action=edit'>edit</a>\n";
	return $content ."</tr>\n";
}

// draw top row with lang names in big letters; 
// also rounded corners for whole table
function formatLangTitlesRow($reqCode) {
	global $ChosenLangs, $ChosenFeatures, $wgUser;

	// round all corners for the whole table.
	// this has to go inside the table somewhere.  anywhere. 
	// but just once.  Also table must be relative.
	$cornerStuff = "<div class=nwCorn></div>\n".
			"<div class=neCorn></div>\n".
			"<div class=swCorn></div>\n".
			"<div class=seCorn></div>\n";

	// the language row, headings for each lang column
	$content = "<tr class=langRow>";
//flExport($ChosenFeatures);
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
	$cimg = "url($wgScriptPath/skins/common/images/cwCorners.gif)";
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
	////echo("drawViewTable- ". var_export($args, true) . $reqCode);
	////var_dump($ChosenLangs);

	// figure out the colums
	$nLangs = count($ChosenLangs);
	$ChosenFeatures = array();
	foreach ($ChosenLangs as $lang)
		$ChosenFeatures[] = new cwFeature($lang->langName, $reqCode);
	// remember that a feature will be stunted (rules undefined) if there wasnt a page
	
	$content = "<script src=$wgScriptPath/skins/common/prototype.js type=text/javascript></script>\n";
	//$wid = ($nLangs ? (100 / $nLangs) : 100) . '%';
	$content .= drawViewStyles();
	$content .= "<br clear=right style='height: 8px'>";

	// hidden lang choice panel, start form in case of edit, then table tag
	$content .= drawLangChoiceDialog() ."<form method=post><table class=rulesTab>";
	
	// the col elements including col bg color
	foreach ($ChosenLangs as $lang)
		$content .= "<col class=". $lang->langName ."Col style=display:table-column;visibility:on />";
//Hey this last line: correct it if youre fixing debug stuff.	
	$content .= formatLangChoiceRow();
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
	
//flLog("drawViewTable done ");
//flLog("content=`$content` ");
	//$input = $parser->recursiveTagParse($input);
	return $content;
}

// Draw THE view table.  Column languages as specified, 
// row rules in req order for given req.
// This is a tag handler.
function cwView($input, $args, $parser) {
	global $wgRequest;

	// if no req specified on url line, give em the TOC. 
	$reqCode = $wgRequest->getVal('ch', '');
	$r = explode('/', $reqCode, 2);  // strip off possible langs
	$reqCode = $r[0];
	
	loadChosenLangs();

//flLog("cwView Page: reqCode='$reqCode'");
//flLog("cwView Page: ch='". $_REQUEST['ch'] ."'");
//flExport($_REQUEST);
//flExport($_SERVER);
	if ($reqCode && $reqCode != 'choose')
		return drawViewTable($args, $reqCode);
	else
		return cwViewTOC();
}

/////////////////////////////////////////////////// Lang Choice

global $ChosenLangs, $ChosenVerss;
$ChosenLangs = $ChosenVerss = $ChosenLangList = null;

// take this array of lang names (and maybe @versions)
// and set up the ChosenLangs and ChosenVers globals correctly.
// Called on view startup.
function enactLangs(array $langsAr) {
	global $ChosenLangs, $ChosenVerss, $allTheLangs;
	
	// yes it is possible to choose no languages at all
	if (!isset($langsAr) || !is_array($langsAr) || count($langsAr) < 1)
		$langsAr = array('JavaScript', 'PHP');
		
	flLog("enactLangs() of:");
	flExport($langsAr);
	flExport($allTheLangs);
	
	// collect the real thing.  make sure they're only bonafide langauges.
	$ChosenLangs = array();
	$ChosenVerss = array();
	foreach ($langsAr as $langName) {
		$z = explode('@', $langName, 2);
		if (isset($z[0]) && isset($allTheLangs[$z[0]])) {
			$ChosenLangs[] = $allTheLangs[$z[0]];
			$ChosenVerss[] = null;  //isset($z[1]) ? $z[1] : null;
		}
	}
	
	// yes if the langs array contained all bogus languages or 'none'
	if (count($ChosenLangs) <= 0) {
		$ChosenLangs = array($allTheLangs['JavaScript'], $allTheLangs['PHP']);
		$ChosenVerss[] = array(null, null);
	}
	
	flLog("enactLangs result: ");
	flDumpChosenLangs();
}

// check all sources of chosen languages; return an array listing them
// each as 'lang' or as 'lang@vers'
function getPageLangs() {
	global $wgRequest, $wgCookiePrefix;
	global $cwNChLangCols;

	flLog("getPageLangs... req=");
	flExport($_REQUEST);
	$list = null;

	// the all-langs-in-one-arg way, as returned by the modern overlay dialog
	// first priority
	// no now uses next method if (array_key_exists('langs', $_REQUEST))
	//	return explode(',', $_REQUEST['langs']);
	
	// best cuz its cacheable: after the req code in the query string.  
	// As from like "/++Arrays/PHP,Ruby" from a <cw link or the view TOC.
	$reqCode = $wgRequest->getVal('ch', '');
flLog("got reqCode from ch attr: `$reqCode`");
	$sp = explode('/', $reqCode, 2);
	flExport( isset($sp[1]) ? explode(',', $sp[1]) : 'no sp1');
	if (isset($sp[1]))
		$list = explode(',', $sp[1]);
	else {
		// the cookie, as set when used, only used when not in URL or set by choice
		// third priority
		if (array_key_exists('langs', $_COOKIE))
			$list = explode(',', $_COOKIE['langs']);
		else {
			// ok, a default default
			flLog("using default default langs");
			$list = array('JavaScript', 'PHP');
		}
	}
	
	foreach($list as $item)
		$result[] = trim($item);
	return $result;
}

// make sure the $ChosenLangs (and $ChosenVerss) globals are filled
// with the languages the user intended.
// And activate them.
function loadChosenLangs() {
	global $ChosenLangs, $ChosenLangList;
	global $cwDoingViewPage, $cwChapter;
	
	flLog("loadChosenLangs() starts ");
	
	if ($ChosenLangs) return;  // already done
	enactLangs(getPageLangs());
	$ChosenLangList = chosenLangsString();
	flDumpChosenLangs();

////flExport($_SERVER);////
	// remove the old per-path cookie;  totally confusing.  Each of these will not interfere with the correct cookie unless the path is already '/'
	if ($_SERVER['REQUEST_URI'] != '/')////
		setcookie('langs', '', 0);   //// Remove this statement in 2013 or later.
	setcookie('cw_dev_cwLangs', '', 0, '/');   //// Remove this statement in 2013 or later.

	// set the cookie if not done already; trying to make the default lang setting global
	if (empty($_COOKIE['langs']))
		setcookie('langs', $ChosenLangList, time() + 4e7, '/');
}

// return me a compressed string indicating my langs, as seen in cookie or url.
// EG "PHP@5.2,JavaScript@1.5"     May return '' if none chosen or they got lost.
function chosenLangsString() {
	global $ChosenLangs, $ChosenVerss;
	flLog("chosenLangsString() starts ");
	$con = '';
	foreach ($ChosenLangs as $i => $lang) {
		if (isset($ChosenVerss[$i]))
			$con .= $lang->langName .'@'. $ChosenVerss[$i] .',';
		else
			$con .= $lang->langName .',';
	}
	return rtrim($con, ',');
}

function flDumpChosenLangs() {
	global $ChosenLangs, $ChosenVerss, $ChosenLangList;
//flLog("Dump - Chosen - Languages:");
//flExport($ChosenLangs);
	if (!is_array($ChosenLangs)) {
		flLog("flDumpChosenLangs: ChosenLangs not array: ". var_export($ChosenLangs, true));
		return;
	}
	foreach ($ChosenLangs as $i => $lang) {
		if (isset($ChosenVerss[$i]))
			flLog(" - - ". $lang->langName ."@". $ChosenVerss[$i]);
		else
			flLog(" - - ". $lang->langName ."    (no vers)");
	}
	flLog("ChosenLangCookie = `$ChosenLangList`");
}

/////////////////////////////////////////////////////////// Language Choice

// number of columns shown in Language_choice page (=max possible columns for view)
// Increase this as there gets to be more languages/versions.
// But past 5, not so useful.
global $cwNChLangCols;
$cwNChLangCols = 3;

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


