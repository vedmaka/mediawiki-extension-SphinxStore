<?php

use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Drivers\Mysqli\Connection;

/**
 * Helper class for managing Sphinx and SMW connections
 */
class SphinxStore {
    
    protected static $instance;
    protected $index;
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
        global $wgSphinxStoreServer, $wgSphinxStorePort, $wgSphinxStoreIndex;
        $this->connection = new Connection();
        $this->connection->setParams( array(
            'host' => $wgSphinxStoreServer,
            'port' => $wgSphinxStorePort
        ));
        $this->index = $wgSphinxStoreIndex;
    }
    
    public function deleteDoc( $id )
    {
        SphinxQL::create( $this->connection )
            ->delete()
            ->from( $this->index )
            ->where('id', $id)
            ->execute();
    }
    
    public function parseSemanticData( SMWSemanticData $data )
    {
        
        if( !$mwTitle ) {
            return;
        }
        
        $mwTitle = $data->getSubject()->getTitle();
        $mwArticle = new Article( $mwTitle );
        
        //TODO: review
        // In general we should ignore all pages except NS_MAIN
        // along with taking in account only 'Card' category pages.
        // Considering this it will be a good idea to make these things configurable.
        
        if( $mwTitle->getNamespace() != NS_MAIN ) {
            return;
        }
        
        $pageCategories = SFUtils::getCategoriesForPage( $mwTitle );
        if( !in_array('Card', $pageCategories) ) {
            return;
        }
        
        // Prepare some fields and constant attributes variables
        $fTitle = $mwTitle->getText();
        $fContent = $mwArticle->getRawText();
        $fProperties = array();
        
        // Iterate through properties and store their values as data
        foreach ( $data->getProperties() as $property ) {
            
        }   
        
    }
    
}