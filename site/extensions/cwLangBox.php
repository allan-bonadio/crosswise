<?
//
//  LangBox  --  choose which languages in nifty box
//
//// if(! defined('MEDIAWIKI')) { echo("\n"); die(-1);}


/////////////////////////////////////////////////////////// Language Choice

// number of columns shown in Language_choice page (=max possible columns for view)
// Increase this as there gets to be more languages/versions.
// But past 5, not so useful.
global $nChLangCols;
$nChLangCols = 3;

////// draw inner part (old)
////function drawLCRowsCols() {
////	global $nChLangCols;
////	global $ChosenLangs, $ChosenFeatures, $allTheLangs;
////
////	////flLog("drawLCRowsCols() starting");////
////
////	$s = '';
////
////	// headings with titles for each column (Column 0, ...)
////	$s .= "<tr class=langRow>";
////	for ($col = 0; $col < $nChLangCols; ++$col) {
////		$vis = ($col < count($ChosenLangs)) ? 'table-cell' : 'none';
////		$s .= "<th id=lcCol_$col style=display:$vis>Column $col</th>";
////	}
////	$s .= "</tr>\n";
////
////
////	// now one row for each language (ultimately: each language version)
////	foreach($allTheLangs as $langF) {
////		global $nChLangCols;
////		////var_dump("tr:  nChLangCols=$nChLangCols langF=", $langF);////
////		$s .= "<tr class=langChoiceRow>";
////		for ($col = 0; $col < $nChLangCols; ++$col) {
////			$vis = ($col < count($ChosenLangs)) ? 'table-cell' : 'none';
////			$s .= "<td style=display:$vis><div id=but{$langF->langName}_$col class='langChoiceButton langChoiceButtonLook' ". 
////				"style=background-color:#fff >";
////			$s .= $langF->langName .' '. $langF->vers;
////			//$checked = (isset($ChosenLangs[$col]) && $langF->lang == $ChosenLangs[$col]->lang) 
////			//	? 'checked ' : '';
////			//$s .= "<input type=radio name=colLang$col id=colLang". $langF->lang ."$col value=". $langF->lang ." $checked/>";
////			//$s .= "<label for=colLang". $langF->lang ."$col>". $langF->lang ." ". $langF->vers ."</label>";
////			$s .= "</div></td>\n";
////		}
////		$s .= "</tr>";
////	}
////
////	// one row for No Language
////	$s .= "<tr class=langChoiceRow>";
////	for ($col = 0; $col < $nChLangCols; ++$col) {
////		$vis = ($col < count($ChosenLangs)) ? 'table-cell' : 'none';
////		$s .= "<td style=display:$vis><div id=butnone_$col class='langChoiceButton langChoiceButtonLook' ". 
////			"style=background-color:#fff  >";
////		$s .= 'none';
////		//$checked = !isset($ChosenLangs[$col]) ? 'checked ' : '';
////		//$s .= "<input type=radio name=colLang$col id=colLangNoDisp$col value=none $checked/>";
////		//$s .= "<label for=colLangNoDisp$col>none</label>";
////		$s .= "</div></td>\n";
////	}
////	$s .= "</tr></table>";
////
////	// help em out
////	$s .= "<div style=text-align:center;padding:1em;>click None to remove a column</div>";
////	$s .= "<div class='langRow langChoiceButtonLook' style='padding: 1em; border: outset gray 4px; text-align: center; '>click here to start using your new arrangement</div>";
////
////	////flLog("drawLCRowsCols() ending");////
////	return $s;
////}

// draw outer part of box (and its insides too) and return html string
function drawLCBox() {
////debug_print_backtrace();////
////	flLog("drawLCBox() starts");////
	
	// really this just wraps the rows and columns
	$s = <<<cwHAZY_LAYER
<div id=hazyLayer>
	<div style=height:4em; ></div>
	<div id=hazyBox>
	  <form id=langChoiceForm  class=outerBezel method=post>
		  <div class=bezelInner>
			<div >
				<div id=plusButton title='click to add YET ANOTHER column' style='float:right; cursor:pointer; width: 98px; height:39px; background-image: url(/skins/crosswise/PlusButton.png);'>
				</div>
				<br clear=right />
				<div></div>
			</div>
			<div >
				<div id=plusButton style='float:right;border:solid 2px #000; border-bottom: none;color:black; background-color:ff0; padding: 0 .2em; cursor:pointer; font-size:120%'>
					click to add YET ANOTHER column + 
				</div>
				<br clear=right />
				<div></div>
			</div>
		  </div>
cwHAZY_LAYER;

	////$s .= drawLCRowsCols();

	$s .= <<<cwHAZY_LAYER_ANNEX
		<br clear=right />
		<div></div>
	  </form>
	</div>
</div>
cwHAZY_LAYER_ANNEX;

////	flLog("drawLCBox() ends, html is ". strlen($s) ." long");////
////	flExport($s);////
	return $s;
}

// php arrays to js vars
function drawJSVars() {
	global $ChosenLangs, $ChosenFeatures, $allTheLangs;
	global $parserAfterTidyText;
////	flLog("startin drawin lcjs");////

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

	// unless we do this, wiki spits <p> stuff all over  it.  This is so embarassing
	// but I can't ram my JS through the MW parser, so there's this workaround.
	global $parserAfterTidyText;
	$s = '||ParserAfterTidy||';

	// insert these variables, get it out of the way.  This variable will get more appended!
	$parserAfterTidyText = <<<EMBEDDED_JS_START
<script>

var origLangSettings = $jsChosenLangs;
var langSettings = new Array(origLangSettings.length);
var theLangs = $jsTheLangs;

EMBEDDED_JS_START;

	return $s;
}

// all the javascript as one chunk, returned
function drawLCJS() {
	global $parserAfterTidyText;
	////echo "<br>Starting drawLCJS()<br>";
	flLog("Starting drawLCJS()<br>");
	################################################## Start of Embedded Javascript
	// single quotes means i don't have to mess with the dollar signs
	global $parserAfterTidyText;
	$parserAfterTidyText .= <<<'EMBEDDED_JS_BODY'
	
	
/////////////////////////////////////////////// sliders

// actual dom nodes for the wrapper .sliderStrip 
var sliders = [];

// one of the sliding strips, enclosed in sliding mechanism.  Pass in serial 0, 1, ...
function drawOneSlidingStrip(n) {
	var s = "<div class='sliderStrip s"+ n +"'>\n";
	s += "<ul class=slidingStrip>\n";
	
	for (var la = 0; la < theLangs.length; la++)
		s += "<li>"+ theLangs[la] +"</li>\n";

	return s + "</ul></div>";
}

function drawAllSlidingStrips() {
	var s = '';
	for (var n = 0; n < origLangSettings.length; n++) {
		s += drawOneSlidingStrip(n);
	}
	$('.innerBezel').html(s);
}

var whichSlideIsSliding = null;
var clickDownY = 0;

function slideDown(ev) {
	whichSlideIsSliding = ev.target.parentNode();
	clickDownY = ev.pageY - ev.target.offsetTop;
}

function slideMove(ev) {
	console.log("slideMove: whichSlideIsSliding=" + (whichSlideIsSliding ? whichSlideIsSliding->className : 'null'));
	if (whichSlideIsSliding) {
		var newY = ev.pageY - clickDownY;
		ev.target.offsetTop = newY;
	}
	ev.stopPropagation();  // no text selection in slider!
}

function slideUp(ev) {
	whichSlideIsSliding = null;
}

function activateSlidingStrips() {
	// only clickdowns in the strip itself
	$('.sliderStrip .slidingStrip').mousedown(slideDown);
	
	// but drags out to a wider area
	$('.outerBezel').mousemove(slideMove).mousedown(slideUp).mouseout(slideUp);
	
	sliders = $('.sliderStrip');
}


/////////////////////////////////////////////// sliders

////// set button assembly in col to given lang in response to user click or whatever
////function setLangChoiceCol(col, lang) {
////	langSettings[col] = lang;
////	for (var la in theLangs) {
////		var but = $('#but'+ la +'_'+ col)[0];
////		if (but) {
////			if (langSettings[col] == la) {
////				but.style.backgroundColor = theLangs[la].onColor;
////				but.style.borderStyle = 'inset';
////			}
////			else {
////				but.style.backgroundColor = '#fff';  //theLangs[la].offColor;
////				but.style.borderStyle = 'outset';
////			}
////		}
////		else
////			console.error('no but on setLangChoiceCol('+ col +', '+ lang +')');
////	}
////}
////
////function resetLangSettings() {
////	var col;
////	for (col = 0; col < origLangSettings.length; col++)
////		setLangChoiceCol(col, origLangSettings[col]);
////	while (langSettings.length > origLangSettings.length)
////		langSettings.pop();
////}
////
////function downLangChoiceButton(event) {
////	var butEl = event.target || event.srcElement;
////	butEl.style.backgroundColor = '#000';
////}
////
////// a click on one of the cells in this table.  don't sumit yet!
////function clickLangChoiceButton(event) {
////	var butEl = event.target || event.srcElement;
////	
////	// which button is it
////	var id = butEl.id.split('_', 2);
////	var lang = id[0].substr(3);
////	var col = id[1];
////	setLangChoiceCol(col, lang);
////	event.stop();
////}
////
////
////var columnActivateDisplay = 'table-cell';
////
////function plusClick(event) {
////	var newCol = langSettings.length;
////	var h = $('#lcCol_'+ newCol)[0];
////	if (!h) return true;
////	try {
////		h.style.display = columnActivateDisplay;
////	} catch (e) {
////		// IE strikes again.  v7 and 6.
////		h.style.display = columnActivateDisplay = 'block';
////	}
////	
////	for (var la in theLangs) {
////		var td = $('#but'+ la +'_'+ newCol)[0].parentNode;
////		td.style.display = columnActivateDisplay;
////		//td.style.display = 'table-cell';
////	}
////	setLangChoiceCol(newCol, 'none');
////	
////	event.stop();  // or the background will submit it!
////}

function onLangSubmit(ev) {
	$('#hazyLayer').hide();  // instant feedback

	// take our url and chop off existing lang codes
	var href = location.href
	// 1rmore slashes, plus plus, 1rmore nonslashes,slash, nonslashes to the end
	// get rid of last segment.  if there.
	href = href.replace(/\/+(\+\+[^\/]+)\/[^\/]+$/, '/$1');
	
	// slap on the new language codes.  But Ulp! omit None entries
	href += '/' + langSettings.join(',').replace(/,none/g, '').replace(/^none,/, '');
	////ev.stop();
	location.href = href;
	// doesn't even submit!  never gets here. $('#langChoiceForm').submit();
}



/////////////////////////////////////////////// page init

// page startup init
function onLoadLangChoiceDialog() {
	////resetLangSettings();
	$('#hazyLayer').bind('click', onLangSubmit);
	
	// all the handlers for all the buttons
	////$('.langChoiceButton').click(clickLangChoiceButton).mousedown(downLangChoiceButton);
	////$('#plusButton').click(plusClick);
	$('#plusButton').mousedown(function() {
			$('#plusButton')[0].style.backgroundImage = 'url(/skins/crosswise/PlusButtonPressed.png)';
		});
	$('#plusButton').mouseup(function() {
			$('#plusButton')[0].style.backgroundImage = 'url(/skins/crosswise/PlusButton.png)';
		});
}

// upon click of the Languages button
function openLangsBox() {
	$('#hazyLayer').show();
	activateSlidingStrips();
}



addOnloadHook(onLoadLangChoiceDialog);

addOnloadHook(function() {
	// get rid of these empty p nodes mw puts all over!
	$('#hazyLayer p *').unwrap();
});

</script>
EMBEDDED_JS_BODY;
	#################################################### End of Embedded Javascript
	
	////flLog( "<br>Finishing drawLCJS()<br>");
	////echo "<br>Finishing drawLCJS()<br>";
	return '';
}

/////////////////////////////////////////////////////// Main Level

// this 'draws' it, returns an html string.  This is the entry to this file from the lang box code.
function drawLangChoiceDialog() {
	global $nChLangCols;
	global $ChosenLangs, $ChosenFeatures, $allTheLangs;
	////flLog("drawLangChoiceDialog() starts");

	$s = drawLCBox();  // the html
	$s .= drawJSVars();  // php values -> js
	$s .= drawLCJS();  // the js
	////flLog("drawLangChoiceDialog() done with s='$s'");
	return $s;
}

function someOtherCrap() {////
	global $nChLangCols;

//<!--[if lt IE 7]><style>#hazyBox {left:8px;right:8px;top:6em;}</style><![endif]-->
//<!--[if gte IE 7]><style>#hazyBox {left:1px;right:1px;top:6em;}</style><![endif]-->
//<![if !IE]><style>#hazyBox {left:0;right:0;top:6em;}</style><![endif]>


	// the hazy layer - houses the lang selection (hazy) box
////	$s .= <<<HAZYSTUFF
////HAZYSTUFF;

	// the style for the hazyBox; the dialog outer frame
	$s .= "";

	// start off with more cols than you need; hide the extra ones till the user clicks +
	$nChLangCols = count($ChosenLangs) + 10;
	if (count($ChosenLangs))
		$wid = (100 / count($ChosenLangs)) . '%';
	else
		$wid = '100%';
	$s .= "";
	
	// plus button
	$s .= "\n";
	$s .= "<table class=rulesTab>";
	substr_replace($s, "", -7, 0);


	return $s;
}



/////////////////////////////////////////////////// Lang Choice mgmt

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
		
	////flLog("enactLangs() of:");
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
	
	////flLog("enactLangs result: ");
	cwDumpChosenLangs();
}

// check all sources of chosen languages; return an array listing them
// each as string 'lang' or as 'lang@vers'
function getPageLangs() {
	global $wgRequest, $wgCookiePrefix;
	global $nChLangCols;

	////flLog("getPageLangs... req=");
	////flExport($_REQUEST);
	$list = null;

	// the all-langs-in-one-arg way, as returned by the modern overlay dialog
	// first priority
	// no now uses next method if (array_key_exists('langs', $_REQUEST))
	//	return explode(',', $_REQUEST['langs']);
	
	// best cuz its cacheable: after the req code in the query string.  
	// As from like "/++Arrays/PHP,Ruby" from a <cw link or the view TOC.
	$reqCode = $wgRequest->getVal('ch', '');
	////flLog("got reqCode from ch attr: `$reqCode`");////
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
	
	if ($ChosenLangs) return;  // already done
	
	////flLog("loadChosenLangs() starts ");////
	enactLangs(getPageLangs());
	$ChosenLangList = chosenLangsString();
	cwDumpChosenLangs();

////flExport($_SERVER);////
	// remove the old per-path cookie;  totally confusing.  Each of these will not interfere with the correct cookie unless the path is already '/'
	//if ($_SERVER['REQUEST_URI'] != '/')////
	//	setcookie('langs', '', 0);   //// Remove this statement in 2013 or later.
	////setcookie('cw_dev_cwLangs', '', 0, '/');   //// Remove this statement in 2013 or later.

	// set the cookie if not done already; trying to make the default lang setting global
	if (empty($_COOKIE['langs']))
		setcookie('langs', $ChosenLangList, time() + 4e7, '/');  // 1 yr = 3.2e7

	////flLog("loadChosenLangs() ends ");
}

// return me a compressed string indicating my langs, as seen in cookie or url.
// EG "PHP@5.2,JavaScript@1.5"     May return '' if none chosen or they got lost.
function chosenLangsString() {
	global $ChosenLangs, $ChosenVerss;
	////flLog("chosenLangsString() starts ");
	$con = '';
	foreach ($ChosenLangs as $i => $lang) {
		if (isset($ChosenVerss[$i]))
			$con .= $lang->langName .'@'. $ChosenVerss[$i] .',';
		else
			$con .= $lang->langName .',';
	}
	return rtrim($con, ',');
}

function cwDumpChosenLangs() {
	global $ChosenLangs, $ChosenVerss, $ChosenLangList;

	flLog("Dump - Chosen - Languages:");
//flExport($ChosenLangs);
	if (!is_array($ChosenLangs)) {
		flLog("cwDumpChosenLangs: ChosenLangs not array: ". var_export($ChosenLangs, true));
		return;
	}
	foreach ($ChosenLangs as $i => $lang) {
		if (isset($ChosenVerss[$i]))
			flLog(" - - ChosenLangs[$i]=". $lang->langName ."@". $ChosenVerss[$i]);
		else
			flLog(" - - ChosenLangs[$i]=". $lang->langName);
	}
	flLog("ChosenLangCookie = `$ChosenLangList`");
}





