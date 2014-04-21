<?php
//
//  Init Pages  --  populate feature and req pages that are brand-new
//
 if(! defined('MEDIAWIKI')) { echo("\n"); die(-1);}

/////////////////////////////////////////////////// Blank Page Population

// called only when editing a nonexistent page, to supply initial content
function initialPagesPopulate(&$text, &$title) {
	global $allTheLangs, $origLangs;
//print "Initial Pages Pop $text $title\n";

	// is this a Req page?  name of the form "Category:blah" where blah isnt a language
	// this should go away after significant population
	$ti = explode(':', $title->getPrefixedText(), 2);
	if ($ti[0] == 'Category') {
		if (!isset($ti[1]))
			return true;  // must be something else!?!
		
		if (isALang($ti[1])) {
			// a language page.  What do they have on them again? um...
			$langName = $ti[1];
			$text = "The language $langName.   OK, say something.\n";
			$text .= "----\n[[Category:Languages]]\n";
		}
		else {
			// must be a req page
			$reqCode = titleToCode($ti[1]);
			
			// hmm... maybe there's a Ruby version -- TEMPORARY ONLY --
			//$feat = new cwFeature('Ruby', $reqCode);
//flLog("ru ruby? ".  isset($feat->rules));
			// try javascript first
			$feat = new cwFeature('JavaScript', $reqCode);
			if (! isset($feat->rules))
				$feat = new cwFeature('PHP', $reqCode);
//flLog("ru js? ".  isset($feat->rules));
			if (! isset($feat->rules))
				$feat = new cwFeature('PHP', $reqCode);
			if (! isset($feat->rules))
				return true;
			
			$text = "";
//flLog("ru and ru->rule order:");
//flExport($feat);
//flExport($feat->ruleOrder);
			foreach ($feat->ruleOrder as $needCode) {
				$needTitle = codeToTitle($needCode);
				$text .= "* '''$needTitle''' - \n";
			}
			$text .= "----\n";
			$text .= drawAllReqFeatLinks($reqCode);
			$text .= "\n[[Category:Requirements]]\n";
		}
		return true;
	}

	// no language name has an underbar in it; look this up and see if it's a rules page
//$text = "so you know you got to ipr wif title ". $title->getDBkey();
	$ti = explode('_', $title->getDBkey(), 2);
//var_export($ti);
	if (isALang($ti[0]) && isset($ti[1])) {
		// must be a rule
		$langName = $ti[0];
		$reqCode = $ti[1];
		$feature = codeToTitle($reqCode);
		
		// collect some other languages versions of same req to inspire
		$templFeats = array();
		foreach ($origLangs as $origLang) {
			if ($origLang->langName != $langName) {
				$tf = new cwFeature($origLang->langName, $reqCode);
				if (isset($tf->rules))
					$templFeats[] = $tf;
			}
		}
//print "so you know you got to ipr with RULE $langName _ $feature\n";
		
		$req = new cwReq($feature);
		//$text = "==$feature Rules==\n";
		$text .= "<cwStartFeature ch='$reqCode' />\n";
		foreach ($req->needs as $i => $need) {
			if ($need->kind != '-')
				continue;  // not for link needs
			$text .= "===$need->title===\n";
			$text .= "{{h|Describe $langName way to do: ". $need->desc ."}}\n";
//flLog("need desc: $need->desc");
			foreach ($templFeats as $tf) {
				if (isset($tf->rules[$need->code])) {
					$tx = $tf->rules[$need->code];
					// not if this is already init example stuff!!
					if (isset($tx) && ! strpos($tx, "{{h#")) {
						$prefix = "\n{{h#" . $tf->lang->langName ."|||";
						$text .= $prefix . str_replace("\n", $prefix, $tx);
						$text .= "}}\n\n";
					}
				}
			}
		}
		$text .= "<cwEndFeature ch='$reqCode' />\n";
		$text .= drawAllReqFeatLinks($reqCode);
		$text .= "\n[[Category:$langName]] [[Category:$reqCode]]\n\n";
		return true;
	}
	return true;
}

