<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'SphinxStore' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['SphinxStore'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['SphinxStoreAlias'] = __DIR__ . '/SphinxStore.i18n.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for SphinxStore extension. Please use wfLoadExtension ' .
		'instead, see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die( 'This version of the SphinxStore extension requires MediaWiki 1.25+' );
}
