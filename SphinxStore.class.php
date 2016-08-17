<?php

use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Drivers\Mysqli\Connection;

/**
 * Helper class for managing Sphinx and SMW connections
 */
class SphinxStore {
    
    protected static $instance;
    protected $index;
	protected $category;
    protected $connection;
    
    public static function getInstance()
    {
        if( !self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct()
    {
        global $wgSphinxStoreServer, $wgSphinxStorePort, $wgSphinxStoreIndex, $wgSphinxStoreCategory;
        $this->connection = new Connection();
        $this->connection->setParams( array(
            'host' => $wgSphinxStoreServer,
            'port' => $wgSphinxStorePort
        ));
        $this->index = $wgSphinxStoreIndex;
	    $this->category = $wgSphinxStoreCategory;
    }

	/**
	 * Deletes document with provided $id from index.
	 * @param $id
	 */
    public function deleteDoc( $id )
    {
        SphinxQL::create( $this->connection )
            ->delete()
            ->from( $this->index )
            ->where('id', $id)
            ->execute();
    }

	/**
	 * Deletes all documents from index.
	 */
    public function deleteAll()
    {

    	SphinxQL::create( $this->connection )
		    ->delete()
		    ->from( $this->index )
		    ->where('id','>',0)
		    ->execute();

    }

	/**
	 * Normalize property name to be used in sphinx JSON attribute:
	 * - removes all non-alphanumeric characters
	 * - replaces whitespaces with underscores
	 * - converts string to lower case
	 * @param $propertyName
	 *
	 * @return mixed
	 */
    protected function normalizePropertyName( $propertyName )
    {
    	$propertyName = preg_replace("/[^a-zA-Z\\d\\s:]/", "", $propertyName);
	    $propertyName = str_replace(" ", "_", $propertyName);
	    $propertyName = mb_strtolower($propertyName);
	    return $propertyName;
    }

	/**
	 * Called from semantic store to parse semantic data and store or update it inside search index.
	 * @param \SMW\SemanticData $data
	 */
    public function parseSemanticData( \SMW\SemanticData $data )
    {
        
        $mwTitle = $data->getSubject()->getTitle();

	    if( !$mwTitle || !$mwTitle->exists() ) {
		    return;
	    }

		$mwPage = new WikiPage( $mwTitle );

        //TODO: review
        // In general we should ignore all pages except NS_MAIN
        // along with taking in account only 'Card' category pages.
        // Considering this it will be a good idea to make these things configurable.
        
        if( $mwTitle->getNamespace() != NS_MAIN ) {
            return;
        }

        //TODO: was temporary disabled because smw update happens before
	    // article get saved, by this reason we're unable to query for categories here,
	    // considering this it is better to apply filter later, in the query condition.

		// I still think it is not a problem that index will be updated with some delay, but filtering though Category is necessary!
        if( $this->category ) {
	        $pageCategories = SFUtils::getCategoriesForPage( $mwTitle );
	        if ( ! in_array( $this->category, $pageCategories ) ) {
		        return;
	        }
        }
        
        // Prepare some fields and constant attributes variables
        $fTitle = $mwTitle->getText();
	    $fAliasTitle = $fTitle;
        $fContent = $mwPage->getContent()->getWikitextForTransclusion();
        $fProperties = array();
        
        // Iterate through properties and store their values as data
	    /** @var \SMW\DIProperty $property */
	    foreach ( $data->getProperties() as $property ) {

	    	//TODO: _INST = Categories
	        if( in_array( $property->getKey(), array( '_SKEY', '_REDI', '_INST' ) ) ) {
	        	continue;
	        }

		    $propertyName = $this->normalizePropertyName( $property->getLabel() );

		    // Just skip system properties
		    if( $propertyName == 'has_query' || $propertyName == 'has_subobject' ) {
		    	continue;
		    }

		    /** @var SMWDataItem $di */
		    foreach ( $data->getPropertyValues( $property ) as $di ) {

			    if ( $di instanceof SMWDIError ) {
				    // error values, ignore
				    continue;
			    }

			    // Semantic Title class support
			    // TODO: do we're really need real page name and just can not override regular title with this ?
			    if( class_exists('SemanticTitle') && isset( $GLOBALS['wgSemanticTitleProperties'] ) ) {
				    $match = false;
				    foreach ( $GLOBALS['wgSemanticTitleProperties'] as $namespace => $propName ) {
				    	if( $mwTitle->getNamespace() == $namespace && $propertyName == mb_strtolower( $propName ) ) {
				    		$match = true;
						    break;
					    }
				    }
				    if( $match && in_array( $di->getDIType(), array( SMWDataItem::TYPE_BLOB, SMWDataItem::TYPE_STRING ) ) ) {
					    $fAliasTitle = $di->getString();
					    break; // Out of cycle for this property
				    }
			    }

			    //TODO: since property can have many values we should consider that and store it as array in JSON
			    // to query use: SELECT *, ANY(x='essay' FOR x IN meta.publication_type) as p FROM url WHERE p=1;
			    switch ( $di->getDIType() ) {
				    case SMWDataItem::TYPE_STRING:
				    case SMWDataItem::TYPE_BLOB:
					    // Handle string-based properties normally
						/** @var SMWDIBlob $di */
						$fProperties[ $propertyName ][] = $di->getString();
					    break;
				    case SMWDataItem::TYPE_NUMBER:
				    	// Handle numeric property value
						/** @var SMWDINumber $di */
					    $fProperties[ $propertyName ][] = $di->getNumber();
					    break;
				    case SMWDataItem::TYPE_WIKIPAGE:
				    	// Handle wikipage type value
						/** @var SMWDIWikiPage $di */
						$fProperties[ $propertyName ][] = $di->getTitle()->getText();
				    	break;
				    default:
					    // Do nothing otherwise..
					    break;
			    }

			    //break; //TODO: see above

		    }

        }

        $modDate = $mwPage->getRevision()->getTimestamp();
		$this->addDocument( $mwTitle->getArticleID(), $fTitle, $fAliasTitle, $fContent, $fProperties, $modDate );
        
    }

	/**
	 * Adds or updates existing document in the search index.
	 *
	 * @param        $id
	 * @param        $title
	 * @param null   $fAliasTitle
	 * @param string $content
	 * @param array  $properties
	 * @param null   $modificationDate
	 */
    public function addDocument( $id, $title, $fAliasTitle = null, $content = '', $properties = array(), $modificationDate = null )
    {

    	// If there're no alias title for this documents lets use normal title
    	if( $fAliasTitle == null ) {
			$fAliasTitle = $title;
	    }

	    SphinxQL::create( $this->connection )
		    ->replace()
		    ->into( $this->index )
		    ->set(array(
		    	'id' => $id,
			    'title' => $fAliasTitle, // We use alias-title for search
			    'page_title' => $title, // and real title as document attribute
			    'alias_title' => $fAliasTitle, // though it is still good idea to store alias title as attr too
			    'content' => $content,
			    'properties' => json_encode( $properties ),
			    'modification_date' => ( $modificationDate != null ) ? wfTimestamp( TS_UNIX, $modificationDate ) : 0
		    ))->execute();

    }
    
}