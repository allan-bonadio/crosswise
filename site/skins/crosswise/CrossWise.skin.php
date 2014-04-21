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
		global $wgOut, $wgRequest, $wgTitle;
		global $cwDoingViewPage, $cwChapter, $allTheLangs;

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
		global $cwDoingViewPage, $cwChapter, $wgRequest, $wgUser;
flLog("subTitleLines: cwDoingViewPage=$cwDoingViewPage\n");
		
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
	public function execute() {
		global $wgRequest;
 
		$skin = $this->data['skin'];
 
		// suppress warnings to prevent notices about missing indexes in $this->data
		wfSuppressWarnings();
 
		$this->html( 'headelement' );


		/////////////////////// adapted from http://www.mediawiki.org/wiki/Manual:Skinning



		if( $this->data['sitenotice'] ) {
			?><div id="siteNotice"><? $this->html('sitenotice') ?></div><? 
		} 
		
	?>
		<h1 id="firstHeading"><? $this->html('title'); ?> inTHeH1FoeRSURE</h1>
		<div id="contentSub"><? $this->html('subtitle') ?></div>
	<?
		$this->html('bodytext');
		if( $this->data['catlinks'] )
			$this->html('catlinks');
			
		/* recommended by the mediawiki people http://www.mediawiki.org/wiki/Manual:Skinning/Tutorial 
			the following, just moving down the page. search for snippets to understand.  */?>
		<div id="mw-js-message" style="display:none;"></div>
		<?php if ( $this->data['newtalk'] ) { ?>$this->data['newtalk']<?php } ?>newtalk
		<?php if ( $this->data['sitenotice'] ) { ?>$this->data['sitenotice']<?php } ?>sitenotice
		<?php $this->text( 'sitename' ); ?>sitename
		<h1 id="firstHeading" class="firstHeading"><?php $this->html('title') ?>theSecondOne</h1> title of page
		<div id="content">contentNeedthihs? and a <div id="bodyContent">bodyCOntentNeedthis?
		<div id="siteSub"><?php $this->msg( 'tagline' ); ?></div>
		<?php if ( $this->data['subtitle'] ) { $this->html( 'subtitle' ); } ?>dasSubtitle
		<?php if ( $this->data['undelete'] ) { $this->html( 'undelete' ); } ?>dasUndelete

		<?php $this->html( 'bodytext' ) ?> that was da body
		<?php $this->html( 'dataAfterContent' ); ?>OrElseSomeExtensionsWontWorkRIght?
		<?php $this->html( 'catlinks' ); ?>dat was da catlinks


		<ul>
		<?php foreach ( $this->getPersonalTools() as $key => $item ) { ?>
			<?php echo $this->makeListItem($key, $item); ?>
 
		<?php } ?>
		</ul>thatWasThePersonalTools

<ul>
<?php foreach ( $this->data['content_navigation']['namespaces'] as $key => $tab ) { ?>
	<?php echo $this->makeListItem( $key, $tab ); ?>
 
<?php } ?>
</ul>thatwas"just the namespaces category of links"useThatOrElse:
		
		
		
		<?php foreach ( $this->data['content_navigation'] as $category => $tabs ) { ?>
<ul>
<?php foreach ( $tabs as $key => $tab ) { ?>
	<?php echo $this->makeListItem( $key, $tab ); ?>
 
<?php } ?>
</ul>
<?php } ?>datWas"all the categories of links as separate lists"OruseTheAbovewhatever

<ul>
<?php
	foreach ( $this->data['content_navigation'] as $category => $tabs ) {
		foreach ( $tabs as $key => $tab ) { ?>
			<?php echo $this->makeListItem( $key, $tab ); ?>
 
<?php
		}
	} ?>
</ul>dawazAYetA3tdWayToDoCategories


<ul>
<?php
	foreach ( $this->data['content_actions'] as $key => $tab ) { ?>
		<?php echo $this->makeListItem( $key, $tab ); ?>
 
<?php
	} ?>
</ul>flatContentActionsTscheckItOut


Sidebar:IcompletelyBluewThisOffCheckoutTHPage http://www.mediawiki.org/wiki/Manual:Skinning/Tutorial
also languageLInks


<ul>
<?php
	foreach ( $this->getToolbox() as $key => $tbitem ) { ?>
		<?php echo $this->makeListItem( $key, $tbitem ); ?>
 
<?php
	}
	wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this ) ); ?>
</ul>daToolbox!IthinkIwant tolimit this like the FromblockInV1


Search FormBlewThatOffToo

<?php
foreach ( $this->getFooterLinks() as $category => $links ) { ?>
<ul>
<?php
	foreach ( $links as $key ) { ?>
	<li><?php $this->html( $key ) ?></li>
 
<?php
	} ?>
</ul>
<?php
} ?>FooterLinksTry1

<ul>
<?php foreach ( $this->getFooterLinks( "flat" ) as $key ) { ?>
	<li><?php $this->html( $key ) ?></li>
 
<?php } ?>
</ul>FooterLinksTry2


footer icons i blew off too
"Taking care of special cases"
Go read that on down







		

<?php
	echo Html::element( 'a', array(
		'href' => $this->data['nav_urls']['mainpage']['href'],
		'style' => "background-image: url({$this->data['logopath']});" )
		+ Linker::tooltipAndAccesskeyAttribs('p-logo') ); ?>

	?>

		<!-- "page toolbar" -->
		<div id="p-cactions" class="portlet">
			<h5><? $this->msg('views') ?></h5> <!-- Page Toolbar Label/Caption [optional] -->
			<div class="pBody">
				<ul><? 
					foreach( $this->data['content_actions'] as $key => $tab ) {
						echo '
					<li id="', Sanitizer::escapeId( "ca-$key" ), '"';
						if ( $tab['class'] ) {
							echo ' class="', htmlspecialchars($tab['class']), '"';
						}
						echo '><a href="', htmlspecialchars($tab['href']), '"',
							$skin->tooltipAndAccesskeyAttribs('ca-'.$key), '>',
							htmlspecialchars($tab['text']),
							'</a></li>';
					}?>
				</ul>
			</div>
		</div>

		<!-- "User Toolbar" -->
		<div class="portlet" id="p-personal">
			<h5><? $this->msg('personaltools') ?></h5> <!-- User Toolbar Label/Caption [optional] -->
			<div class="pBody">
				<ul>
				<? foreach( $this->data['personal_urls'] as $key => $item ) { ?>
					<li id="<?= Sanitizer::escapeId( "pt-$key" ) ?>"<? 
						if ($item['active'])
							?> class="active"<? ; ?>
					>
					<a href="<?= htmlspecialchars( $item['href'] ) ?>"
						<?= $skin->tooltipAndAccesskeyAttribs('pt-'.$key) ?> <?
						if( !empty( $item['class'] ) ) { ?>
							class="<?= htmlspecialchars( $item['class'] ) ?>" <?
						}?>
					>
						<?= htmlspecialchars( $item['text'] ) ?>
					</a>
				</li><?
				} ?>
				</ul>
			</div>
		</div>


		<!-- "Site Logo" -->
		<div class="portlet" id="p-logo">
			<a href="<?= htmlspecialchars($this->data['nav_urls']['mainpage']['href']) ?>" 
				<?= $skin->tooltipAndAccesskeyAttribs('n-mainpage')  ?>  >
				<img src="<?= htmlspecialchars( $this->data['logopath'] ) ?>" border="0" />
			</a>   
		</div>
		<script type="<? $this->text('jsmimetype') ?>">
			if (window.isMSIE55)
				fixalpha();
		</script> <!-- IE alpha-transparency fix -->


		<!-- "Toolbox" -->
			<div class="portlet" id="p-tb">
				<h5><? $this->msg('toolbox') ?></h5>
				<div class="pBody">
					<ul>
						<? if( $this->data['notspecialpage'] ) { ?>
							<li id="t-whatlinkshere">
								<a href="<?= htmlspecialchars($this->data['nav_urls']['whatlinkshere']['href']) ?>"
									<?= $skin->tooltipAndAccesskeyAttribs('t-whatlinkshere') ?>
								>
									<? $this->msg('whatlinkshere') ?>
								</a>
							</li>
							<? if( $this->data['nav_urls']['recentchangeslinked'] ) { ?>
								<li id="t-recentchangeslinked">
									<a href="<?= htmlspecialchars($this->data['nav_urls']['recentchangeslinked']['href']) ?>"
										<?= $skin->tooltipAndAccesskeyAttribs('t-recentchangeslinked') ?>
									>
									<? $this->msg('recentchangeslinked') ?>
									</a>
								</li>
							<? }
						}
						
						if( isset( $this->data['nav_urls']['trackbacklink'] ) ) {
							?><li id="t-trackbacklink">
								<a href="<?= htmlspecialchars($this->data['nav_urls']['trackbacklink']['href']) ?>"
									<?= $skin->tooltipAndAccesskeyAttribs('t-trackbacklink') ?>
								>
								<? $this->msg('trackbacklink') ?>
								</a>
							</li><?
						}
						if( $this->data['feeds'] ) {
							?><li id="feedlinks"><?
							foreach($this->data['feeds'] as $key => $feed) {
								?><span id="feed-<?= Sanitizer::escapeId($key) ?>">
								<a href="<?= htmlspecialchars($feed['href']) ?>"
									<?= $skin->tooltipAndAccesskeyAttribs('feed-'.$key) ?>
								>
								<?= htmlspecialchars($feed['text'])?>
								</a>&nbsp;
								</span>
							<? } 
							?></li><? 
						}
 
						foreach( array( 'contributions', 'blockip', 'emailuser', 'upload', 'specialpages' ) as $special ) {
 
							if( $this->data['nav_urls'][$special] ) {
								?><li id="t-<?= $special ?>">
									<a href="<?= htmlspecialchars($this->data['nav_urls'][$special]['href'])?>"
										<?= $skin->tooltipAndAccesskeyAttribs('t-'.$special) ?>
									>
									<? $this->msg($special) ?>
									</a>
								</li>
							<?}
						}
 
						if( !empty( $this->data['nav_urls']['print']['href'] ) ) {
							?><li id="t-print">
								<a href="<?= htmlspecialchars($this->data['nav_urls']['print']['href']) ?>"
									<?= $skin->tooltipAndAccesskeyAttribs('t-print') ?>
								>
									<? $this->msg('printableversion') ?>
								</a>
							</li><? 
						}
 
						if( !empty( $this->data['nav_urls']['permalink']['href'] ) ) {
							?><li id="t-permalink">
								<a href="<?= htmlspecialchars($this->data['nav_urls']['permalink']['href']) ?>"
									<?= $skin->tooltipAndAccesskeyAttribs('t-permalink') ?>
								>
									<? $this->msg('permalink') ?>
								</a>
							</li><? 
						} elseif( $this->data['nav_urls']['permalink']['href'] === '' ) {
							?><li id="t-ispermalink"<?= $skin->tooltip('t-ispermalink') ?>>
								<? $this->msg('permalink') ?>
							</li><? 
						}
 
						wfRunHooks( 'SkinTemplateToolboxEnd', array( &$this ) );
				?>
			</ul>
		</div>
	</div>





	<div id="footer"><?
	// new syntax style - put the ? > junk at the end of each line.
	if ( $this->data['poweredbyico'] ) { ?>
		<div id="f-poweredbyico"><? $this->html('poweredbyico') ?></div><?
	}
	if ( $this->data['copyrightico'] ) { ?>
		<div id="f-copyrightico"><? $this->html('copyrightico') ?></div><?
	 }
	// generate additional footer links
	$footerlinks = array(
		'lastmod', 'viewcount', 'numberofwatchingusers', 'credits', 'copyright',
		'privacy', 'about', 'disclaimer', 'tagline',
	);?>
	<ul id="f-list"><? 
		foreach ( $footerlinks as $aLink ) {
			if ( isset( $this->data[$aLink] ) && $this->data[$aLink] ) { ?>
				<li id="<?= $aLink ?>"><? $this->html( $aLink ) ?></li><?
	 		}
		}
	?>
	</ul>
</div>






<!-- scripts and debugging information -->
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


