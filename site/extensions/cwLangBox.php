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


// draw outer part of box (and its insides too) and return html string
function drawLCBox() {
////debug_print_backtrace();////
////	flLog("drawLCBox() starts");////
	
	// really this just wraps the rows and columns
	$s = <<<cwHAZY_LAYER
<div id=hazyLayer>
	<div style=height:4em; ></div>
	<div id=langBox>
		<form id=langChoiceForm  class=outerBezel>
			<div class=innerBezel></div>
			<div></div>
		</form>
	</div>
</div>
cwHAZY_LAYER;

////	flLog("drawLCBox() ends, html is ". strlen($s) ." long");////
////	flExport($s);////
	return $s;
}

// php arrays to js vars
function drawJSVars() {
	global $ChosenLangs, $ChosenFeatures, $allTheLangs;
////	flLog("startin drawin lcjs");////

	$jsTheLangs = '{';
	foreach ($allTheLangs as $langName => $langObj)
		$jsTheLangs .= "$langName: {title: '$langObj->title', onColor: '$langObj->headColor', offColor: '$langObj->examColor'}, ";
	$jsTheLangs .= "none: {title: 'none', onColor: '#888', offColor: '#ccc'}}";
	flExport($allTheLangs);
	
	$jsLangSettings = '["';
	if (count($ChosenLangs)) {
		for ($col = 0; $col < count($ChosenLangs); ++$col)
			$jsLangSettings .= $ChosenLangs[$col]->langName .'", "';
		$jsLangSettings = substr($jsLangSettings, 0, -3) ."]";
	}
	else {
		$jsLangSettings = '["JavaScript", "PHP"]';
	}

<script>

var langSettings = $jsLangSettings;
var theLangs = $jsTheLangs;

EMBEDDED_JS_START;

	return $s;
}

// all the javascript as one chunk, returned
function drawLCJS() {
	////echo "<br>Starting drawLCJS()<br>";
	flLog("Starting drawLCJS()<br>");
	################################################## Start of Embedded Javascript
	// single quotes means i don't have to mess with the dollar signs
	$html = <<<'EMBEDDED_JS_BODY'
	
	
/////////////////////////////////////////////// sliders

// actual dom nodes for the wrapper .sliderStrip 
var sliders = [], sliderCellHeight, sliderHalfHeight, sliderTotalSlide;

// one of the sliding strips, enclosed in sliding mechanism.  Pass in serial 0, 1, ...
function drawOneSlidingStrip(n) {
	var la, s = "<div class='sliderStrip s"+ n +"'>\n";
	s += "<div class=topShadow></div><div class=bottomShadow></div>\n";
	s += "<ul class=slidingStrip>\n";
	
	for (la in theLangs)
		s += "<li style=background:"+ theLangs[la].onColor +"> "+ theLangs[la].title +"</li>\n";

	return s + "</ul></div>\n";
}

function drawAllSlidingStrips() {
	var wheels = "<img src=/skins/crosswise/langSliderWheel2.png style=float:left>\n";
	var s = wheels;
	for (var n = 0; n < langSettings.length; n++) {
		s += drawOneSlidingStrip(n);
		s += wheels;
	}
	s += "<br clear=left>\n";
	return s;
}

var whichSlideIsSliding = null;
var clickDownY = 0;

// mouse down, move and up handlers for each slider
function slideDown(ev) {
	whichSlideIsSliding = ev.target; 
	while (! whichSlideIsSliding.classList.contains('sliderStrip'))
		whichSlideIsSliding = whichSlideIsSliding.parentNode;  // seek the .sliderStrip node
	clickDownY = ev.pageY;
	ev.stopPropagation();  // no submit from a slider click!
	ev.preventDefault();
}

function slideMove(ev) {
	// roll the dice.  which browsers do out/leave events, which use which or button, ...
	if (! ev.which && ! ev.button)
		return slideUp(ev);

	////console.log("slideMove: whichSlideIsSliding=" + (whichSlideIsSliding ? whichSlideIsSliding.className : 'null'));
	if (whichSlideIsSliding) {
		var deltaY = ev.pageY - clickDownY;
		whichSlideIsSliding.adjustSlidePosition(deltaY);
		clickDownY = ev.pageY;
	}
	ev.stopPropagation();  // no text selection in slider!
	ev.preventDefault();
}

function slideUp(ev) {
	if (whichSlideIsSliding) {
		whichSlideIsSliding = null;
		ev.stopPropagation();  // no text selection in slider!
		ev.preventDefault();
	}
}

// heartbeat method to effect momentum, runs several times a second to move sliders
function slideCoast() {
	////var traceLine = '';
	for (var s = 0; s < sliders.length; s++) {
		var slider = sliders[s];
		
		if (slider == whichSlideIsSliding) {
			// this one is being slid by the human - suspend physics till they let go
			// but measure the velocity, maybe we'll end up coasting
			var v = slider.slidePosition - slider.slidePrevPosition;  // velocity from human movement
			slider.slideVelocity = .75 * slider.slideVelocity + .25 * v;  // moving average
		}
		else {
			// now impose the forces
		
			// clickstop force
			if (slider.slidePosition > 0)
				slider.slideVelocity -= slider.slidePosition;
			else if (slider.slidePosition < -sliderTotalSlide)
				slider.slideVelocity -= slider.slidePosition + sliderTotalSlide;
			else {
				// slidePosition <= 0 mostly
				var clickPos = (sliderHalfHeight - slider.slidePosition) % sliderCellHeight - sliderHalfHeight;
				slider.slideVelocity += clickPos * 4.0;  // acceleration constant
			}
		
			slider.slideVelocity *= 0.5;  // kinetic friction
			if (Math.abs(slider.slideVelocity) < 1) {  // static friction
				// stop it moving and put it where the force is zero
				slider.slideVelocity = 0;
				slider.slidePosition = Math.round(slider.slidePosition / sliderCellHeight) * sliderCellHeight;
			}
			
			// velocity in units of px per 100ms
			slider.adjustSlidePosition(slider.slideVelocity);
		}
		
		// the user will also tweak the position thru event handlers; remember this so you'll know how much
		slider.slidePrevPosition = slider.slidePosition;
		////traceLine += s +' pos='+ slider.slidePosition.toFixed(2) +' vel='+ slider.slideVelocity.toFixed(2) +'    ';
	}
	////console.log(traceLine);
}

// Actually change the slide position by dy, and display it.  Previous direct setting of slidePosition taken into acct.
// this is an object specific method we use on our sliders.
// so 'this' means the node itself, where we attach all sorts of our variables
function adjustSlidePosition(dy) {
	//console.debug("hey adjustSlidePosition("+ dy +") from "+ this.slidePosition +" to "+ (this.slidePosition + dy) +".");
	if (dy)
		this.slidePosition += dy;
	$('.slidingStrip', this).css('top', (this.slidePosition + sliderHalfHeight) +'px');  // actually set position
}

// set handlers on the sliding strips
function activateSlidingStrips() {
	// only clickdowns in the strip itself
	$('.sliderStrip .slidingStrip').mousedown(slideDown);
	$('.bottomShadow, .topShadow').mousedown(slideDown);
	
	// but drags out to a wider area
	$('.outerBezel').mousemove(slideMove).mouseup(slideUp);
	$('.langBox').mousemove(slideMove).mouseup(slideUp).mouseleave(slideUp);

	$('.hazyLayer').mousemove(slideUp);
	
	sliders = $('.sliderStrip');
	for (var s = 0; s < sliders.length; s++) {
		sliders[s].slidePosition = sliderCellHeight * ?;
		sliders[s].slideVelocity = 0;
		sliders[s].adjustSlidePosition = adjustSlidePosition;  // install tweaker
	}
	
	// must know the height of each language cell
	sliderCellHeight = $('.sliderStrip li')[1].offsetTop - $('.sliderStrip li')[0].offsetTop;
	sliderHalfHeight = sliderCellHeight / 2;
	sliderTotalSlide = $('.slidingStrip')[0].offsetHeight - sliderCellHeight;
}


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

// upon click of the Languages button
function openLangsBox() {
	$('#hazyLayer').show();
	$('.innerBezel').html(drawAllSlidingStrips());
	activateSlidingStrips();

	setInterval(slideCoast, 100);
}

// called when somebody decides to submit it whereupon it constructs a new URL and goes there. 
function langVirtualSubmit() {
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
	// doesn't even submit!
}



/////////////////////////////////////////////// page init

// page startup init
function onLoadLangChoiceDialog() {
	// just submit if user clicks on the hazy layer
	$('#hazyLayer').mousedown(function(ev) {
		if (ev.target == ev.currentTarget)
			langVirtualSubmit();
	});
	
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
	return $html;
}

/////////////////////////////////////////////////////// Main Level

// this 'draws' it, hidden, and returns an html string.  This is the entry to this file from the lang box code.
function drawLangChoiceDialog() {
	global $nChLangCols;
	global $ChosenLangs, $ChosenFeatures, $allTheLangs;

	////flLog("drawLangChoiceDialog() starts");

	$html = drawLCBox();  // the html

	// unless we do this, wiki spits <p> stuff all over  it.  This is so embarassing
	// but I can't ram my JS through the MW parser, so there's this workaround.
	$html = '||CleanCWJavaScript||';

	return $html;
}


// Hook called in the later phases of html generation to get rid of WM's 
// disruptive formatting, exp <p>s.  
function cwParserAfterTidy(&$parser, &$text) {
	
	////flLog("cwParserAfterTidy: patt: '$parserAfterTidyText'");
	$jsStuff = '';
	$jsStuff .= drawJSVars();  // php values -> js
	$jsStuff .= drawLCJS();  // the js
	////flLog("drawLangChoiceDialog() done with s='$html'");
	
	
	if ($jsStuff)
		$text = str_replace('||CleanCWJavaScript||', $jsStuff, $text);
	return true;
}



function someOtherCrap() {////
	global $nChLangCols;

//<!--[if lt IE 7]><style>#langBox {left:8px;right:8px;top:6em;}</style><![endif]-->
//<!--[if gte IE 7]><style>#langBox {left:1px;right:1px;top:6em;}</style><![endif]-->
//<![if !IE]><style>#langBox {left:0;right:0;top:6em;}</style><![endif]>


	// the hazy layer - houses the lang selection (hazy) box
////	$s .= <<<HAZYSTUFF
////HAZYSTUFF;

	// the style for the langBox; the dialog outer frame
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





