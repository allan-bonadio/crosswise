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
			<div class=bezelButtonWrapper>
				<img class='bezelButtons cancel' src=/skins/crosswise/langChooserCancel.png>
				<img class='bezelButtons plus'   src=/skins/crosswise/langChooserPlus.png>
				<img class='bezelButtons ok'     src=/skins/crosswise/langChooserOK.png>
			</div>
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

	// the list of languages available, and their details
	$jsTheLangs = '{';
	$jsTheLangsList = '[';
	$serial = 0;
	foreach ($allTheLangs as $langName => $langObj) {
		$jsTheLangs .= "$langName: {title: '$langObj->title', serial: $serial, onColor: '$langObj->headColor', offColor: '$langObj->examColor'}, ";
		$jsTheLangsList .= "'$langName', ";  // guarantees consistent order
		$serial++;
	}
	$jsTheLangs .= "none: {title: 'none', serial: $serial, onColor: '#888', offColor: '#ccc'}}";
	$jsTheLangsList .= "'none']";
	flExport($allTheLangs);
	
	// the list of languages that the user selected for viewing
	$jsLangSettings = '["';
	if (count($ChosenLangs)) {
		for ($col = 0; $col < count($ChosenLangs); ++$col)
			$jsLangSettings .= $ChosenLangs[$col]->langName .'", "';
		$jsLangSettings = substr($jsLangSettings, 0, -3) ."]";
	}
	else {
		$jsLangSettings = '["JavaScript", "PHP"]';
	}

	$s = <<<EMBEDDED_JS_START
<script>

var allTheLangs = $jsTheLangs;
var allTheLangsList = $jsTheLangsList;
var langSettings = $jsLangSettings;
</script>

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
<script>	
	
/////////////////////////////////////////////// sliders

// actual dom nodes for the wrapper .sliderStrip 
var sliders = [], sliderCellHeight, sliderHalfHeight, sliderTotalSlide;

// one of the sliding strips, enclosed in sliding mechanism.  Pass in serial 0, 1, ...
function drawOneSlidingStrip(n) {
	var la, s = "<div class='sliderStrip s"+ n +"'>\n";
	s += "<div class=topShadow></div><div class=bottomShadow></div>\n";
	s += "<ul class=slidingStrip>\n";
	
	for (la = 0; la < allTheLangsList.length; la++) {
		lang = allTheLangs[allTheLangsList[la]];
		s += "<li style=background:"+ lang.onColor +"> "+ lang.title +"</li>\n";
	}

	return s + "</ul></div>\n";
}

var interSliderWheelIcon = "<img src=/skins/crosswise/langSliderWheel.png style=float:left>\n";

function drawAllSlidingStrips() {
	var s = interSliderWheelIcon;
	for (var n = 0; n < langSettings.length; n++) {
		s += drawOneSlidingStrip(n);
		s += interSliderWheelIcon;
	}
	s += "<br class=endOfInnerBezel clear=left>\n";
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
				var forceRaw = (sliderHalfHeight - slider.slidePosition) % sliderCellHeight - sliderHalfHeight;
				slider.slideVelocity += forceRaw * 4.0;  // acceleration constant
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
	$('.slidingStrip', this).css('top', (this.slidePosition + sliderHalfHeight - 5) +'px');  // actually set position
}

// init this slider like a constructor.  slider=node and object itself; index=which lang column or -1 to append on end
function activateLangSlider(slider, index) {
	slider.adjustSlidePosition = adjustSlidePosition;  // install tweaker

	// which language to start off selecting (0=js, 1=php, ... or -1=some bland default)
	var langSerial = (index >= 0) ? allTheLangs[langSettings[index]].serial : 1.3;
	slider.slidePosition = - sliderCellHeight * langSerial;
	slider.slideVelocity = (index == 1) ? 15 : 0;  // start with one of them jiggling
	slider.adjustSlidePosition();  // actually move into position
}

// set handlers on the sliding strips
function activateSlidingStrips() {
	// only clickdowns in the strip itself.  This will catch slidingStrip's events even for new sliders
	$('.innerBezel').on('mousedown',  '.sliderStrip', slideDown);
	// superfluous? $('.bottomShadow, .topShadow').mousedown(slideDown);
	
	// but drags out to a wider area.  (the mousedown decides which slider is sliding)
	// far enough away and it's effectively a mouse release.
	$('.outerBezel').mousemove(slideMove).mouseup(slideUp);
	$('#langBox').mousemove(slideMove).mouseup(slideUp).mouseleave(slideUp);
	$('#hazyLayer').mousemove(slideUp);
	
	// must know the height of each language cell
	sliderCellHeight = $('.sliderStrip li')[1].offsetTop - $('.sliderStrip li')[0].offsetTop;
	sliderHalfHeight = sliderCellHeight / 2;
	sliderTotalSlide = $('.slidingStrip')[0].offsetHeight - sliderCellHeight;

	// set each slider to the current lang settings
	sliders = $('.sliderStrip');
	for (var s = 0; s < sliders.length; s++)
		activateLangSlider(sliders[s], s);
}


// upon click of the Languages button
function openLangsBox() {
	$('#hazyLayer').show();
	$('.innerBezel').html(drawAllSlidingStrips());
	activateSlidingStrips();
	
	// interactive for the bezel buttons
	$('#langBox .bezelButtons').mousedown(function(ev) {
		$(ev.currentTarget).css('border-width', '1px 0 0 1px');
	});
	$('#langBox .bezelButtons').mouseup(function(ev) {
		$(ev.currentTarget).css('border-width', '');  // revert to default
	});
	
	// resulting action for the bezel buttons
	// these only trigger if the mouseup was in the same obj as the down
	$('#langBox .ok').click(langVirtualSubmit);
	$('#langBox .cancel').click(langVirtualDismiss);
	$('#langBox .plus').click(langPlus);

	setInterval(slideCoast, 100);
}

// called when somebody decides to submit it whereupon it constructs a new URL and goes there. 
// not really a form submission.  probably don't need the <form tag
function langVirtualSubmit() {
	langVirtualDismiss();
	
	// retrieve from sliders
	langString = '';
	for (var sl = 0; sl < sliders.length; sl++) {
		var lang = allTheLangsList[Math.round(-sliders[sl].slidePosition / sliderCellHeight)];
		if (lang != 'none')
			langString += lang + ',';
	}
	langString = langString.slice(0,-1);
	
	// take our url and chop off existing lang codes
	var href = location.href
	// 1rmore slashes, plus plus, 1rmore nonslashes,slash, nonslashes to the end
	// get rid of last segment.  if there.
	href = href.replace(/\/+(\+\+[^\/]+)\/[^\/]+$/, '/$1');
	
	// slap on the new language codes.  
	location.href = href +'/'+ langString;
	// doesn't even submit!
}

// get rid of dialog, ready for another attempt later
function langVirtualDismiss() {
	$('#hazyLayer').hide();  // instant feedback 
}

// add another column
function langPlus() {

	// make the new one, as text
	var ss = drawOneSlidingStrip(sliders.length);
	ss += interSliderWheelIcon;

	// insert it before our specially-placed <br tag
	$('#langBox .endOfInnerBezel').before(ss);
	
	// gimme that node and activate it
	var sNode = $('#langBox .sliderStrip').last()[0];
	sliders.push(sNode);
	activateLangSlider(sNode, -1);
}

/////////////////////////////////////////////// page init

// page startup init
function onLoadLangChoiceDialog() {
	// just submit if user clicks on the hazy layer
	$('#hazyLayer, #langBox').mousedown(function(ev) {
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

	flLog("drawLangChoiceDialog() starts");

	$html = drawLCBox();  // the html

	// unless we do this, wiki spits <p> stuff all over  it.  This is so embarassing
	// but I can't ram my JS through the MW parser, so there's this workaround.
	$html .= '||CleanCWJavaScript||';

	flLog("drawLangChoiceDialog() ends");
	return $html;
}


// Hook called in the later phases of html generation to get rid of WM's 
// disruptive formatting, exp <p>s.  
function cwParserAfterTidy(&$parser, &$text) {
	
	////flLog("cwParserAfterTidy: patt: '$parserAfterTidyText'");
	$jsStuff = '';
	$jsStuff .= drawJSVars();  // php values -> js
	$jsStuff .= drawLCJS();  // the js
	////flLog("cwParserAfterTidy() done with s='$html'");
	
	
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
	
	// yes it is possible to choose no languages at all; explode of '' ends up being ['']
	if (empty($langsAr) || !is_array($langsAr) || count($langsAr) < 1 || empty($langsAr[0]))
		$langsAr = array('JavaScript', 'PHP');
		
	////flLog("enactLangs() of:");
	flExport($langsAr);
	////flExport($allTheLangs);
	
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

	flLog("getPageLangs... req=");
	flExport($_REQUEST);
	$list = null;
	
	// make sure $_GET vars override $_COOKIE vars
	$variables_order = ini_get('variables_order');
	if (strpos($variables_order, 'G') >= strpos($variables_order, 'C'))
		die("variables_order EGPCS should have G before C"); 

	// the all-langs-in-one-arg way, as returned by the modern overlay dialog
	// url (_GET) has prioirity over cookie in _REQUEST
	if (array_key_exists('langs', $_REQUEST))
		return explode(',', $_REQUEST['langs']);
	
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
	flLog("chosenLangsString() starts with ChosenLangs=");flExport($ChosenLangs);////
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





