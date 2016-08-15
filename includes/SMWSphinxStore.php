<?php

class SMWSphinxStore extends \SMW\Store {

	static protected $smBaseStore = null;


	/**
	 * Get a handle for the storage backend that is used to manage the data.
	 * Currently, it just returns one globally defined object, but the
	 * infrastructure allows to set up load balancing and task-dependent use of
	 * stores (e.g. using other stores for fast querying than for storing new
	 * facts), somewhat similar to MediaWiki's DB implementation.
	 *
	 * @return SMWStore
	 */
	static function &getBaseStore() {
		global $wgSphinxStoreOriginalStore;

		if ( self::$smBaseStore === null ) {
			self::$smBaseStore = new $wgSphinxStoreOriginalStore();
		}

		return self::$smBaseStore;
	}

	/**
	 * @param \SMW\DIWikiPage $subject
	 * @param bool            $filter
	 *
	 * @return mixed
	 */
	public function getSemanticData( \SMW\DIWikiPage $subject, $filter = false ) {
		return self::getBaseStore()->getSemanticData( $subject, $filter );
	}

	/**
	 * @param mixed           $subject
	 * @param \SMW\DIProperty $property
	 * @param null            $requestoptions
	 *
	 * @return array
	 */
	public function getPropertyValues( $subject, \SMW\DIProperty $property, $requestoptions = null ) {
		return self::getBaseStore()->getPropertyValues( $subject, $property, $requestoptions );
	}

	/**
	 * @param \SMW\DIProperty $property
	 * @param                 $value
	 * @param null            $requestoptions
	 *
	 * @return \SMW\DIWikiPage[]
	 */
	public function getPropertySubjects( \SMW\DIProperty $property, $value, $requestoptions = null ) {
		return self::getBaseStore()->getPropertySubjects( $property, $value, $requestoptions );
	}

	/**
	 * @param \SMW\DIProperty $property
	 * @param null            $requestoptions
	 *
	 * @return \SMW\DIWikiPage[]
	 */
	public function getAllPropertySubjects( \SMW\DIProperty $property, $requestoptions = null ) {
		return self::getBaseStore()->getAllPropertySubjects( $property, $requestoptions );
	}

	/**
	 * @param \SMW\DIWikiPage $subject
	 * @param null            $requestOptions
	 *
	 * @return SMWDataItem
	 */
	public function getProperties( \SMW\DIWikiPage $subject, $requestOptions = null ) {
		return self::getBaseStore()->getProperties( $subject, $requestOptions );
	}

	/**
	 * @param SMWDataItem $object
	 * @param null        $requestoptions
	 *
	 * @return mixed
	 */
	public function getInProperties( SMWDataItem $object, $requestoptions = null ) {
		return self::getBaseStore()->getInProperties( $object, $requestoptions );
	}

	/**
	 * @param Title $subject
	 *
	 * @return mixed
	 */
	public function deleteSubject( Title $subject ) {
		
		SphinxStore::getInstance()->deleteDoc( $subject->getArticleID() );
		
		return self::getBaseStore()->deleteSubject( $subject );
	}

	/**
	 * @param \SMW\SemanticData $data
	 *
	 * @return mixed
	 */
	public function doDataUpdate( \SMW\SemanticData $data ) {
		
		SphinxStore::getInstance()->parseSemanticData( $data );
		
		return self::getBaseStore()->doDataUpdate( $data );
	}

	/**
	 * @param Title $oldtitle
	 * @param Title $newtitle
	 * @param       $pageid
	 * @param int   $redirid
	 *
	 * @return mixed
	 */
	public function changeTitle( Title $oldtitle, Title $newtitle, $pageid, $redirid = 0 ) {
		return self::getBaseStore()->changeTitle( $oldtitle, $newtitle, $pageid, $redirid );
	}

	/**
	 * @param SMWQuery $query
	 *
	 * @return SMWQueryResult
	 */
	public function getQueryResult( SMWQuery $query ) {
		return self::getBaseStore()->getQueryResult( $query );
	}

	/**
	 * @param null $requestoptions
	 *
	 * @return array
	 */
	public function getPropertiesSpecial( $requestoptions = null ) {
		return self::getBaseStore()->getPropertiesSpecial( $requestoptions );
	}

	/**
	 * @param null $requestoptions
	 *
	 * @return array
	 */
	public function getUnusedPropertiesSpecial( $requestoptions = null ) {
		return self::getBaseStore()->getUnusedPropertiesSpecial( $requestoptions );
	}

	/**
	 * @param null $requestoptions
	 *
	 * @return array
	 */
	public function getWantedPropertiesSpecial( $requestoptions = null ) {
		return self::getBaseStore()->getWantedPropertiesSpecial( $requestoptions );
	}

	/**
	 * @return array
	 */
	public function getStatistics() {
		return self::getBaseStore()->getStatistics();
	}

	/**
	 * @param bool $verbose
	 *
	 * @return bool
	 */
	public function setup( $verbose = true ) {
		return self::getBaseStore()->setup( $verbose );
	}

	/**
	 * @param bool $verbose
	 *
	 * @return mixed
	 */
	public function drop( $verbose = true ) {
		return self::getBaseStore()->drop( $verbose );
	}

	/**
	 * @param int  $index
	 * @param int  $count
	 * @param bool $namespaces
	 * @param bool $usejobs
	 *
	 * @return float
	 */
	public function refreshData( &$index, $count, $namespaces = false, $usejobs = true ) {
		return self::getBaseStore()->refreshData( $index, $count, $namespaces, $usejobs );
	}

	public function clear() {
		self::getBaseStore()->clear();
	}

	// Use __call to pass-through to original store
	function __call( $name, $arguments ) {
		return call_user_func_array( array( self::getBaseStore(), $name ), $arguments );
	}

}