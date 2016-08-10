<?php
/**
 * Hooks for BoilerPlate extension
 *
 * @file
 * @ingroup Extensions
 */

class SphinxStoreHooks {

    public static function onExtensionLoaded()
    {
        global $wgSphinxStoreOriginalStore, $smwgDefaultStore;

	    // Save original SMW store to global variable
	    $wgSphinxStoreOriginalStore = $smwgDefaultStore;

	    // Replace original store ( in most cases it is SMWSQLStore3 ) by our wrapper
	    $smwgDefaultStore = 'SMWSphinxStore';

    }

}
