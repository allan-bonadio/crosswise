<?php
//
//  All Features  --  draws All Features matrix
//
 if(! defined('MEDIAWIKI')) { echo("\n"); die(-1);}
 
 
// one cell of the edit TOC, link and edit button.
// $tdth = which kind of table cell, either 'td' or 'th'
// $pageCode = name with underbars
// $pageTitle = title with spaces, and without namespace like 'Category:'
function drawEditTOCCell($tdth, $pageCode, $pageTitle) {
	//global $wgScript, $cwHttpHost;
	global $wgUser;

	$content = "<$tdth>\n";
	$content .= "[[:$pageCode|$pageTitle]]\n";
//flLog("etoc: `$pageCode` ". ($wgUser->isAnon() ? 'anon ' : 'loggedIn ') . ($wgUser->isAllowed('editinterface') ? 'canEdInts ' : 'noEdInts ') . (strncmp($pageCode, "Category:", 9) ? '=notCat' : 'isCat'));
	if (!$wgUser->isAnon()) {
		// has a userid => Let them edit features but not categories.
		// has privs?  let them edit even categories
		if (strncmp($pageCode, "Category:", 9) || $wgUser->isAllowed('editinterface'))
			$content .= drawHtmlEditBut($pageCode);  // but watch out will be parsed
	}
		
		
	//$content .= "<small style='border: 1px #884 solid; background-color: #cc8'>" .
	//		"[http://" . 
	//		$cwHttpHost . $wgScript .'?action=edit&title='. $pageCode .
	//		" edit]</small>\n";
	$content .= "</$tdth>\n";
//flLog($content);
	return $content;
}

// draw a 'table of contents' that can move the editor to 
// whatever Req or Feature or Lang page desired
function drawEditTOC() {
	global $allTheLangs, $allReqLevels, $wgParser;
	
//global $wgUser;
//flExport($wgUser->getRights());
	$styleTag = "<style>\n";
	$styleTag .= "table.editTOC th {text-align:left; padding: .3em 0;line-height:70%}\n";
	$styleTag .= "table.editTOC td {border-collapse:collapse; padding: 0; border-top: solid black 1px}\n";
	$styleTag .= "tr.headTOC th {line-height:120%}\n";
	$styleTag .= "</style>\n";
	
	
	$content = "<table class=editTOC>\n";
	$content .= "<tr class=headTOC>\n";
	$content .= drawEditTOCCell('th', 'Category:Requirements', 'Requirements');
	//$content .= "<th>\n";
	//$content .= "<a href='Category:Requirements'>Requirements</a>\n";
	//$content .= "<small><a href='Category:Requirements?action=edit'>edit</a></small>\n";
	//$content .= "</th>\n";
	foreach ($allTheLangs as $langName => $lang) {
		$content .= drawEditTOCCell('th', "Category:$langName", $langName);
		//$content .= "<th>\n";
		//$content .= "<a href='Category:$langName'>$langName</a>\n";
		//$content .= "<small><a href='Category:$langName?action=edit'>edit</a></small>\n";
		//$content .= "</th>\n";
	}
	$content .= "</tr>\n";

	foreach (GetAllReqCodes() as $rx => $reqCode) {
//flLog(" draw edit TOC: req known as $reqCode");
		$reqEnc = urlencode($reqCode);
		$reqTitle = codeToTitle($reqCode);
		$content .= "<tr>\n";
		$indent = substr('•&nbsp;•&nbsp;•&nbsp;•&nbsp;•&nbsp;', 0, 9*$allReqLevels[$rx] - 9);
		$content .= drawEditTOCCell('th', "Category:$reqCode", $reqTitle);
//$content .= "<th>$indent";
//$content .= "<a href='Category:$reqCode'>$reqTitle</a>\n";
//$content .= "<small><a href='Category:$reqCode?action=edit'>edit</a></small>\n";
//$content .= "</th>\n";
		foreach ($allTheLangs as $langName => $lang) {
			$content .= drawEditTOCCell('TD', $langName .'_'. $reqEnc, $langName .' '. $reqTitle);
//$content .= "<td>\n";
//$content .= "<a href='$featCode'>$featTitle</a>\n";
//$content .= "<small><a href='$featCode?action=edit'>edit</a></small>\n";
//$content .= "</td>\n";
		}
		$content .= "</tr>\n";
	}
	$content .= "</table>\n";
//flExport($content);
	//return $content;
	
	// ok so un-parse this unfortuate stuff
	$stuff = $wgParser->recursiveTagParse($content);
	$stuff = str_replace('&lt;', '<', str_replace('&gt;', '>', $stuff));
	return $styleTag . $stuff;
}

// a mw tag handler for <cwAllFeatures
function cwAllFeatures($input=null, $args=null, $parser=null) {
	return drawEditTOC();
}

