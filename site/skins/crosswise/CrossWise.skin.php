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
	function getStylesheet() {
		return 'crosswise/CrossWise.css';
	}
	// no longer used in 1.22
	function getSkinName() {
		return "CrossWise";
	}

	// no longer used in 1.22
	// special for CW: show therequirement in the title if view pg
	function pageTitle() {
		die(__FILE__ . __LINE__);////

		global $wgOut, $wgRequest, $wgTitle;
		global $cwDoingViewPage, $cwChapter, $cwPageTitle, $allTheLangs;

//flTraceBack('in my skw: pageTitle and setting cwdvp false');
		// this was causing lang button to turn to view source $cwDoingViewPage = false;
		$ti = $wgOut->getPageTitle();
//shouldnt need		if (0 == $wgTitle->getNamespace()) {
//shouldnt need			// the Main namespace
//shouldnt need			if ('View' == $ti) {
//shouldnt need				// VIEW pages - our whole raison d'être
//shouldnt need				$cwDoingViewPage = true;
//shouldnt need				$ti = $wgRequest->getVal('ch', '');
//shouldnt need				if ($ti == '')
//shouldnt need					$ti = 'Table of Contents';
//shouldnt need				else if ($p = strpos($ti, '/')) {
//shouldnt need					// remove '/PHP,Ruby' on end of ch
//shouldnt need					$ti = substr($ti, 0, $p);
//shouldnt need					// but wait!  do this so search engines pick it up
//shouldnt need					$langs = str_replace(',', ' vs ', substr($ti, $p + 1));
//shouldnt need					$ti .= " in " . $langs;
//shouldnt need				}
//shouldnt need				$ti = str_replace('_', ' ', $ti);
//shouldnt need				$wgOut->setHTMLTitle($ti);
//shouldnt need			}
//shouldnt need		}
		$s = '<h1 class="pagetitle">' . $ti . "</h1>\n";
		
		return $s;
	}
	
	// lines near the top of the page, starting with the subtitle 'from Crosswise'
	function subTitleLines() {
		global $cwDoingViewPage, $cwChapter, $cwPageTitle, $wgRequest, $wgUser;
flLog("subTitleLines: cwDoingViewPage=$cwDoingViewPage\n");
		
		die(__FILE__ . __LINE__);////

		// make the subtitle active; activates full header and footer
		$subTitle = $this->pageSubtitle();

		$subTitle = str_replace("From", 
			"<span onclick=\"if (!event.shiftKey) return;".
			"document.getElementById('topLinksBar').style.display='block';".
			"document.getElementById('footer').style.display='block'\">From</span>\n", 
			$subTitle);
		if ($cwDoingViewPage)
			$thatButton = "<span onclick=\"".
				"var b = document.getElementById('hazyLayer').style;".
				"b.display = (b.display=='block') ? 'none' : 'block'\" ".
				"class=cwButton style=margin-left:-2px>Languages</span>".
				"&nbsp; | &nbsp; \n";
		else
			$thatButton = $this->editThisPage() ." &nbsp; | &nbsp; \n";

		$reqMenu = "View Chapter &nbsp; ". formatReqMenu(
			$wgRequest->getVal('ch', 'choose'), false, true, 'cwViewURLPath');
		$reqMenu = str_replace("<select ", "<select onchange='".
			'var sel = event.target || event.srcElement; '.
			'location = sel.value'.
			"' ", 
			$reqMenu);
		//'location = sel.options[sel.selectedIndex].value'.

		// the user part: user name & login/logout
		$uPart = "\n &nbsp; | &nbsp; ";
		if ($wgUser->isAnon())
			$uPart .= $this->specialLink('userlogin');
		else {
			// dont incorporate the username for the mass-market URLs
			// so they can be cached by URL
			$uName = '';
			//if (!$cwSubDom || !isset($_REQUEST['langs']))
			//	$uName = $wgUser->getName() .' ';
			$uPart .= str_replace('>Preferences<', (">{$uName}Prefs<"), 
				$this->specialLink('preferences'));
			$uPart .= " &nbsp; | &nbsp; ";
			$uPart .= $this->specialLink('userlogout');
		}
		
		$stLines = $uPart ."<br />\n";
		$stLines .= $thatButton . $reqMenu ." &nbsp; \n". 
			"</p>\n";
//flLog('------------ upart: '. $uPart);
//flLog('------------ stLines ', $stLines);
		return str_replace('</p>', $stLines, $subTitle);
	}

	// no longer used in 1.22
	// actually does #content, which encloses the top part,
	// then starts #article, which encloses the actual content
	function doBeforeContent() {
		global $cwDoingViewPage, $cwChapter, $wgUser, $wgRequest;
		
		die(__FILE__ . __LINE__);////

		// this is needed to make word wrap correctly outside of IE, and to work 
		// at all in IE.  6 & 7 dont have pre-wrap, so you get a scroll bar.  ugh. 
		$s = "<!--[if IE]><style>pre {white-space: pre; word-wrap:break-word;}</style><![endif]-->\n".
			"<![if !IE]><style>pre {white-space: pre-wrap;}</style><![endif]>\n";

		$s .= "\n<div id='content'>\n<div id='top'>\n";
		$s .= "<div id=\"logo\">".$this->logoText( "right" )."</div>\n";

		$s .= $this->pageTitle();
		$s .= $this->subTitleLines() . "\n";

		// the topLinksBar is hidden (secret egg)
		$s .= "<div id='topLinksBar' style=display:none>";
			$s .= $this->topLinks() . "\n<br />";
	
			$notice = wfGetSiteNotice();
			if( $notice ) {
				$s .= "\n<div id='siteNotice'>$notice</div>\n";
			}
			$s .= " |\n" . $this->pageTitleLinks();
	
			$ol = $this->otherLanguages();
			if($ol) $s .= "<br />" . $ol;
	
			$cat = $this->getCategoryLinks();
			if($cat) $s .= "<br />" . $cat;

		$s .= "<br clear='all' /></div>\n";  // closes #topLinksBar
		
		if ($cwDoingViewPage) {
			require_once(dirname(dirname(__FILE__)) .'/extensions/cwView.php');  // needed if no cookies

			// a bar to change the language choices
			// obsolete $s .= cwChangeLanguageBar();
		}
		
		$s .= "</div>\n</div>\n";  // closes #top, then #content

		// the start of the article.  closing comes later.
		$s .= "\n<div id='article'>";

		return $s;
	}

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
		}


		return $s;
	}

	// no longer used in 1.22
	function doAfterContent() {
		global $cwHttpHost, $wgScriptPath;
		$s = "\n</div><br clear='all' />\n";
		
				die(__FILE__ . __LINE__);////

		
		// what do I want to put in the footer....
		$s .= "<p class=subtitle>\n";
		$s .= "<a href=http://www.mediawiki.org/ style=float:right >".
			"<img width=81 height=31 src=http://$cwHttpHost$wgScriptPath/".
			"skins/common/images/poweredby_mediawiki_88x31.png /></a>\n";

		// no not CrossWise:About $s .= $this->aboutLink() ." &nbsp; |  &nbsp; \n";
		
		$s .= "<a href=". cwFullWikiURL("Help:About") .">About</a> &nbsp; |  &nbsp; \n";
		$s .= "<a href=". cwFullWikiURL("Bug List") .">Bug List</a> &nbsp; |  &nbsp; \n";
		$s .= "<a href=". cwFullWikiURL("Help:Glossary") .">Glossary</a> &nbsp; |  &nbsp; \n";
		$s .= "<a href=mailto:allan-at-TactileInt.com?Subject=CrossWise>Feedback\n";
		$s .= "<img width=16 height=16 ".
			"src=	$wgScriptPath/skins/crosswise/cwTelecom.gif /></a>\n";
		$s .= "</p>\n";

		// the normal wiki footer that I mostly ignore.  Hidden.
		$s .= "\n<div id='footer' style=display:none><hr />";

		$s .= $this->bottomLinks();
		$s .= "\n<br />" . $this->pageStats();
		$s .= "\n<br />" . $this->mainPageLink()
		  . " | " . $this->aboutLink()
		  . " | " . $this->searchForm();

		$s .= "\n</div>\n";

		return $s;
	}
}


class CrossWiseTemplate extends BaseTemplate {
	/**
	 * Outputs the entire contents of the page
	 */
	/**  OLD COMMENT:
	 * Template filter callback for this skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 */
////	public function OldExecute() {
////		global $wgRequest;
//// 
////		$skin = $this->data['skin'];
//// 
////		// suppress warnings to prevent notices about missing indexes in $this->data
////		wfSuppressWarnings();
//// 
////		$this->html( 'headelement' );
////
////
////		/////////////////////// adapted from http://www.mediawiki.org/wiki/Manual:Skinning
////
////
////
////		if( $this->data['sitenotice'] ) {
////			? > <div id="siteNotice">< ? $this->html('sitenotice') ? ></div>< ? 
////		} 
////		
////	? >
////		<h1 id="firstHeading">< ? $this->html('title'); ? ></h1>
////		<div id="contentSub">< ? $this->html('subtitle') ? ></div>
////	< ?
////		$this->html('bodytext');
////		if( $this->data['catlinks'] )
////			$this->html('catlinks');
////			
////		/* recommended by the mediawiki people http://www.mediawiki.org/wiki/Manual:Skinning/Tutorial 
////			the following, just moving down the page. search for snippets to understand.  */? >
////		<div id="mw-js-message" style="display:none;"></div>
////		< ?php if ( $this->data['newtalk'] ) { ? >$this->data['newtalk']< ?php } ? >newtalk
////		< ?php if ( $this->data['sitenotice'] ) { ? >$this->data['sitenotice']< ?php } ? >sitenotice
////		< ?php $this->text( 'sitename' ); ? >sitename< ?
////		
////	}
	
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

		echo '</div>';  // end of page title block
	}
	

	// login, preferences, etc buttons in top right corner
	private function exPersonalTools() {
		echo "<ul id=personalToolsBar class=horizontal>\n";
			foreach ( $this->getPersonalTools() as $key => $item )
				echo $this->makeListItem($key, $item);
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
				$('#langsButton').click(function(ev) {
					$('#hazyLayer').show();
				});
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
 
		// Goes First! generates the <head and stuff above <body
		$this->html( 'headelement' );

 		// DECIDE on the title and overal kind of page it is (view or not)
 		if ($this->data['title']) {
			$cwPageTitle = $this->data['title'];
			if ($cwPageTitle == 'View') {
				// our hacked-up view page.  Instead of the edit/history buttons, do a language button and a menu choosing .
			
				$cwDoingViewPage = true;
				$cwPageTitle = $wgRequest->getVal('ch', '');
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




