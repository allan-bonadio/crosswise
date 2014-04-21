<?
/*
**  CrossWise skin
**  @ingroup Skins
**  @version 2.0.0
**  @author Allan Bonadio orgmgr a la tactileint daht org
**  @license none yet
*/

// gee does this come out anywhere?
$wgExtensionCredits['skin'][] = array(
	'path' => __FILE__,
	'name' => 'CrossWise', // name as shown under [[Special:Version]]
	'version' => '2.0',
	'date' => '20140419', 
	'url' => "http://cw.tactileint.org",
	'author' => '[http://allan.tactileint.org Allan Bonadio]',
	'descriptionmsg' => 'Skin designed for CrossWise.  Don,t leave home without it.',
);

$wgValidSkinNames['crosswise'] = 'CrossWise';
$wgAutoloadClasses['SkinCrossWise'] = __DIR__ . '/CrossWise.skin.php';
$wgExtensionMessagesFiles['CrossWise'] = __DIR__ .'/CrossWise.i18n.php';
 
$wgResourceModules['skins.crosswise'] = array(
	'styles' => array(
		'crosswise/CrossWise.css' => array( 'media' => 'screen' ),
	),
	'remoteBasePath' => &$GLOBALS['wgStylePath'],
	'localBasePath' => &$GLOBALS['wgStyleDirectory'],
);

