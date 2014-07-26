<? 
/**
 *   CrossWise skin -- a takeoff on Nostalgia. 
 *			then adapted from 1.15 to 1.22 after MonoBook & 
 *			http://www.mediawiki.org/wiki/Manual:Skinning .
 *
 * @ingroup Skins
 * @version 2.0.0
 * @author Allan Bonadio orgmgr a la tactileint.org
 * @license none yet
 * one goal is to allow as wide an article area as possible.
 */

// do i need this?  do i watn it?
require_once( dirname( dirname( dirname( __FILE__ ) ) ) . '/includes/SkinTemplate.php');


/**
 * SkinTemplate class for My Skin skin
 * @ingroup Skins
 */
class SkinCrossWise extends SkinTemplate {
	var $skinname = 'crosswise', $stylename = 'crosswise',
		$template = 'CrossWiseTemplate', $useHeadElement = true;

	function initPage( OutputPage $out ) {
		parent::initPage( $out );
		$this->skinname  = 'crosswise';
		$this->stylename = 'crosswise';
		$this->template  = 'CrossWiseTemplate';
	}
	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );
		// Append to the default screen common & print styles...
		$out->addModuleStyles( 'skins.crosswise', 'screen' );
	}


	// no longer used in 1.22
	function getSkinName() {
		return "CrossWise";
	}

////	// lines near the top of the page, starting with the subtitle 'from Crosswise'
////	function xxxxxsubTitleLines() {
////		global $cwDoingViewPage, $cwChapter, $cwPageTitle, $wgRequest, $wgUser;
////flLog("xxxxsubTitleLines: cwDoingViewPage=$cwDoingViewPage\n");
////		
////		die(__FILE__ . __LINE__);////
////
////		// make the subtitle active; activates full header and footer
////		$subTitle = $this->pageSubtitle();
////
////		$subTitle = str_replace("From", 
////			"<span onclick=\"if (!event.shiftKey) return;".
////			"document.getElementById('topLinksBar').style.display='block';".
////			"document.getElementById('footer').style.display='block'\">From</span>\n", 
////			$subTitle);
////		if ($cwDoingViewPage)
////			$thatButton = "<span id=languagesButton ".
////				"class=cwButton style=margin-left:-2px>Languages</span>".
////				"&nbsp; | &nbsp; \n";
////		else
////			$thatButton = $this->editThisPage() ." &nbsp; | &nbsp; \n";
////
////		$reqMenu = "View Chapter &nbsp; ". formatReqMenu(
////			$wgRequest->getVal('ch', 'choose'), false, true, 'cwViewURLPath');
////		$reqMenu = str_replace("<select ", "<select onchange='".
////			'var sel = event.target || event.srcElement; '.
////			'location = sel.value'.
////			"' ", 
////			$reqMenu);
////		//'location = sel.options[sel.selectedIndex].value'.
////
////		// the user part: user name & login/logout
////		$uPart = "\n &nbsp; | &nbsp; ";
////		if ($wgUser->isAnon())
////			$uPart .= $this->specialLink('userlogin');
////		else {
////			// dont incorporate the username for the mass-market URLs
////			// so they can be cached by URL
////			$uName = '';
////			//if (!$cwSubDom || !isset($_REQUEST['langs']))
////			//	$uName = $wgUser->getName() .' ';
////			$uPart .= str_replace('>Preferences<', (">{$uName}Prefs<"), 
////				$this->specialLink('preferences'));
////			$uPart .= " &nbsp; | &nbsp; ";
////			$uPart .= $this->specialLink('userlogout');
////		}
////		
////		$stLines = $uPart ."<br />\n";
////		$stLines .= $thatButton . $reqMenu ." &nbsp; \n". 
////			"</p>\n";
//////flLog('------------ upart: '. $uPart);
//////flLog('------------ stLines ', $stLines);
////		return str_replace('</p>', $stLines, $subTitle);
////	}


	// links at top of each page
	function topLinks() {
	
		die(__FILE__ . __LINE__);////
		
		global $wgOut, $wgUser, $wgEnableUploads;
		$sep = " |\n";

		$s = $this->mainPageLink();

		if ( $wgOut->isArticle() ) {
			$s .= $sep . '<strong>' . $this->editThisPage() . '</strong>' . 
				$sep . $this->historyLink();
		}

		/* show links to different language variants */
		//$s .= $this->variantLinks();
		//$s .= $this->extensionTabLinks();


		// right here is the (best?  only?) time we can sort the special pages list.
		// but only do it for insiders
		global $cwDebug;
		if ($cwDebug) {
			// order Special Pages  before menu creation
			function specialPagesSorter($a, $b) {
				// never equal pretty sure
				return wfMsg(strtolower($a)) < wfMsg(strtolower($b)) ? -1 : 1;
			}
			uksort(SpecialPage::$mList, 'specialPagesSorter');
		}

		$s .= $sep . $this->specialPagesList();

		if ( $wgUser->isAnon() ) {
			$s .= $sep . $this->specialLink( 'userlogin' );
		} else {
			$name = $wgUser->getName();
			/* show user page and user talk links */
			$s .= $sep . '<br />';
			$s .= $sep . $this->link( $wgUser->getUserPage(), wfMsgHtml( 'mypage' ) );
			$s .= $sep . $this->link( $wgUser->getTalkPage(), wfMsgHtml( 'mytalk' ) );
			if ( $wgUser->getNewtalk() ) {
				$s .= ' *';
			}
			/* show watchlist link */
			$s .= $sep . $this->specialLink( 'watchlist' );
			/* show my contributions link */
			$s .= $sep . $this->link(
				SpecialPage::getSafeTitleFor( "Contributions", $wgUser->getName() ),
				wfMsgHtml( 'mycontris' ) );
			/* show my preferences link */
			$s .= $sep . $this->specialLink( 'preferences' );
			/* show upload file link */
			if ( $wgEnableUploads ) {
				$s .= $sep . $this->specialLink( 'upload' );
			}
			/* show log out link */
			$s .= $sep . $this->specialLink( 'userlogout' );
			
			// by default it creates the special links like /index.php
		}


		return $s;
	}

}

// Outputs the entire contents of the page
class CrossWiseTemplate extends BaseTemplate {
	
	/////////////////////////////// supporting functions for execute, the template function
	
	// small site logo in corner
	private function exSiteLogo() {
		$skin = $this->data['skin'];
		?>
		<!-- "Site Logo" -->
		<div class="portlet" id="p-logo">
			<a href="<?= htmlspecialchars($this->data['nav_urls']['mainpage']['href']) ?>" 
				<?= $skin->tooltipAndAccesskeyAttribs('n-mainpage')  ?>  >
				<img src="<?= htmlspecialchars( $this->data['logopath'] ) ?>" border="0" />
			</a>   
		</div>
		<?
	}
		
	// title plus tagline below it
	private function exTitle() {
		global $wgOut, $wgRequest, $wgTitle;
		global $cwDoingViewPage, $cwChapter, $cwPageTitle;

		echo "<div id=pageTitleBlock>\n";
		echo "<h1 id=firstHeading class=firstHeading>$cwPageTitle</h1>\n";

		// "from CrossWise".  watch out something modifies this - it wriggles out of containing elements
		echo $this->msg( 'tagline' );  /////
		echo "<span id=fromTrigger onmouseup=if(event.altKey)$('#cwSpecialPagesMenu').show()> &nbsp; </span>";  /////

		echo '</div>';  // end of page title block
	}
	

	// login, preferences, etc buttons in top right corner
	private function exPersonalTools() {
		echo "<ul id=personalToolsBar class=horizontal>\n";
			////var_dump('personal tools!', $this->getPersonalTools());////
			foreach ( $this->getPersonalTools() as $key => $item )
			{////
				////var_dump($key, $item['links'][0]);////
				echo $this->makeListItem($key, $item);
			}////
		echo "</ul>\n";
	}
		
		
	// buttons like 'edit' or 'talk page' or 'history', for editors of normal pages.  OR,
	// the Languages button and Chapters menu for view page.
	private function exContentActions() {
		global $wgOut, $wgRequest, $wgTitle;
		global $cwDoingViewPage, $cwChapter, $cwPageTitle;

		echo "<ul id=contentActionsToolbar class=horizontal>\n";
		if ($cwDoingViewPage) {
			$langsButton = "<span id=langsButton  ".
				"class=cwButton style=margin-left:-2px>Languages</span>".
				"&nbsp; | &nbsp; \n";

			$reqMenu = "View Chapter &nbsp; ". formatReqMenu(
				$wgRequest->getVal('ch', 'choose'), false, true, 'cwViewURLPath');
			$reqMenu = str_replace("<select ", "<select onchange='".
				'var sel = event.target || event.srcElement; '.
				'location = sel.value'.
				"' ", 
				$reqMenu);
			//'location = sel.options[sel.selectedIndex].value'.
			
			echo <<<HOW_DO_YOU_KNOW_THAT
			<script>
			jQuery(function() {
				$('#langsButton').click(openLangsBox);
			});
			</script>
HOW_DO_YOU_KNOW_THAT;
			
			echo $langsButton . $reqMenu ." &nbsp; \n";

		}
		else {

			// normal pages: all the content actions: edit, history, discussion, ...
			foreach ( $this->data['content_actions'] as $key => $tab )
				echo $this->makeListItem( $key, $tab );
		}
		echo "</ul>\n";
	}
		
	// main stuff
	private function exBodyText() {
		$this->html( 'bodytext' );
		$this->html( 'dataAfterContent' );
	}
	
	private function exFooterLinks() {
		global $cwDoingViewPage, $cwChapter, $cwPageTitle;
		?>
		<ul class=horizontal><?

		// "footer links"  privacy etc, but also view count and last mod datetime
		if (!$cwDoingViewPage) {
			// on the view page, don't display the latest change to the 'view' page!
			// just bag it for now
			echo '<li style="font-size: .7em;">';
			$this->html('lastmod');
			echo '</li> &nbsp;';
		}
		echo '<li>';
		//$this->html('viewcount');
		//echo '</li> &nbsp; <li>';
		$this->html('privacy');
		echo '</li> &nbsp; <li>';
		$this->html('about');
		echo '</li> &nbsp; <li>';
		$this->html('disclaimer');
		echo "</li>\n";

		if ( $this->data['poweredbyico'] ) {
			echo '<li id="f-poweredbyico">';
			$this->html('poweredbyico');
			echo '</li>';
		}
		if ( $this->data['copyrightico'] ) {
			echo '<li id="f-copyrightico">';
			$this->html('copyrightico');
			echo '</li>';
		}
		echo "</ul>\n";
	}
		
	//----------------------New Template-------------------------------
		
	public function execute() {
		global $wgOut, $wgRequest, $wgTitle;
		global $cwDoingViewPage, $cwChapter, $cwPageTitle;
 
 		// all of this code directly does an echo to stdout
		// Goes First! generates the <head and stuff above <body
		$this->html( 'headelement' );		

 		// DECIDE on the title and overal kind of page it is (view or not)
 		if ($this->data['title']) {
			$cwPageTitle = $this->data['title'];
			if ($cwPageTitle == 'View') {
				// our hacked-up view page.  Title is really the CW chapter.
				// Instead of the edit/history buttons, do a language button and a menu choosing .
			
				$cwDoingViewPage = true;
				$cwPageTitle = str_replace('_', ' ', $wgRequest->getVal('ch', ''));
				if ($cwPageTitle == '')
					$cwPageTitle = 'CrossWise Table of Contents';
				else {
					// possible languages on end
					$pti = explode('/', $cwPageTitle, 2);
					$cwChapter = $cwPageTitle = $pti[0];
					if (!empty($pti[1])) {
						$langs = str_replace(',', ' vs ', $pti[1]);
						$cwPageTitle .= " in " . $langs;
					}
				}
			}
			else {
				// else normal wiki title for page
				$cwDoingViewPage = false;
			}
		}


		// suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();
		
 		// the head block: top few inches of every page.  Logo icon on left defines top and bottom.
 		echo "<div id=headBlock style=position:relative >\n";
			$this->exSiteLogo();
			$this->exPersonalTools();  // logout etc northeast corner

			// the title.  big text <h1
			$this->exTitle();
			$this->exContentActions();
			?>
			<br clear=both>
		</div>
		<div id="content"  <?= $cwDoingViewPage ? '' : ' class=padPage ' ?>>
		<div id="bodyContent"><?
			if ( $this->data['undelete'] )
				$this->html( 'undelete' );  // ?? what is this?

			$this->exBodyText();
		
			// category links: small directory of all pages in this category
			$this->html( 'catlinks' );

			$this->exFooterLinks();
		
			// i have no idea what this is but it usually shows up blank
			echo Html::element( 'a', array(
				'href' => $this->data['nav_urls']['mainpage']['href'],
				'style' => "background-image: url({$this->data['logopath']});" )
				+ Linker::tooltipAndAccesskeyAttribs('p-logo') ); 
		?>
		</div>

		<!-- MediaWiki scripts and debugging information -->
		<? $this->html('bottomscripts'); /* JS call to runBodyOnloadHook */ ?>
		<? $this->html('reporttime') ?>
		<? if ( $this->data['debug'] ): ?>
		<!-- Debug output:
			<? $this->text( 'debug' ); ?>
 
			-->
		<? endif; ?>
		
		<?/* special pages menu on bottom */?>
		<select id=cwSpecialPagesMenu onchange=cwExecSpecialPage(event) style=display:none>
		<?	
			$sPages = SpecialPageFactory::getUsablePages();
			ksort($sPages);
			foreach ($sPages as $code => $sp)
				echo "<option value=$code>$code</option>\n";
		?>
		</select>
		<script>
		function cwExecSpecialPage(ev) {
			location = '/--Special:' + ev.currentTarget.value;
		}
		</script>


		</body>
		</html>
		<? 
		wfRestoreWarnings();
	} // end of execute() method
} // end of class




// my hack to alter the title of the page for view pages, etc
// this is the BeforePageDisplay hook handler
function cwConvertPageTitleBeforePageDisplay(&$out, &$sk) {
	global $wgOut, $wgRequest, $wgTitle;
	global $cwDoingViewPage, $cwChapter;

	$cwDoingViewPage = false;
	$cwChapter = '';
	if (0 == $wgTitle->getNamespace()) {
		// the Main namespace
		$ti = $wgOut->getPageTitle();
		////flLog("cwConvertPageTitleBeforePageDisplay: ti=$ti\n");////
		if ('View' == $ti) {
			// VIEW pages - our whole raison d'être
			$cwDoingViewPage = true;
			////flLog("cwConvertPageTitleBeforePageDisplay: cwDoingViewPage=$cwDoingViewPage\n");////
			$cwChapter = $ti = $wgRequest->getVal('ch', '');
			////flLog("cwConvertPageTitle: doitin it `$ti`");////
			if ($ti == '')
				$ti = 'CrossWise Table of Contents';
			else {
				if ($p = strpos($ti, '/')) {
					// remove '/PHP,Ruby' on end of ch
					$langs = substr($ti, $p + 1);
					$cwChapter = $ti = substr($ti, 0, $p);
					// but wait!  do this so search engines pick it up
					//flLog("cwConvertPageTitle langs: $p `$langs`");
					if ($langs) {
						$langs = str_replace(',', ' vs ', $langs);
						//flLog("cwConvertPageTitle langs: `$langs`");
						$ti .= " in " . $langs;
					}
				}
			}
			$ti = str_replace('_', ' ', $ti);
			//flLog("cwConvertPageTitle ti: `$ti`");////
			$wgOut->setPageTitle($ti);  // so it shows up in <title>
		}
	}
	
	// add a meta 'description' for the search engines
	$desc = 'CrossWise compares web scripting languages, feature-for-feature, to help you master the syntax and semantic details of each programming language.';
	if ($cwChapter)
		$desc .= "  This page shows how each language varies for ". 
					str_replace('_', ' ', $cwChapter);
	$out->addMeta('description', $desc);

	return true;
}




