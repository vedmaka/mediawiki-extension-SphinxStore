<?php
/**
 * HelloWorld SpecialPage for BoilerPlate extension
 *
 * @file
 * @ingroup Extensions
 */

class SpecialSphinxStore extends SpecialPage {
	public function __construct() {
		parent::__construct( 'SphinxStore' );
	}

	/**
	 * Show the page to the user
	 *
	 * @param string $sub The subpage string argument (if any).
	 *  [[Special:HelloWorld/subpage]].
	 */
	public function execute( $sub ) {
		$out = $this->getOutput();
		$out->setPageTitle('SphinxStore test page');
	}

	protected function getGroupName() {
		return 'other';
	}
}
