<?php

require dirname(__FILE__).'/class.rssManager.php';


class customRssManager extends rssManager{

	/**
    * Constructor.
    */
	public function __construct($sources = array()) {

		parent::__construct($sources,false);
		//Run its own method list.
		$this->fetchPosts(array('method_default','method_ssl'));

    }

    /**
    * A method that can fetch the links with the https protocol.
    */
    protected function method_ssl(){

    	if(!empty($this->sources)){
	    	foreach($this->sources as $key => $src){

	    		try{
	    			//Open up files & deal with SSL issue:
	    			$arrContextOptions=array(
					    "ssl"=>array(
					        "verify_peer"=>false,
					        "verify_peer_name"=>false,
					    ),
					);  
		    		$content = @file_get_contents($src,false, stream_context_create($arrContextOptions));
		    		if(false === $content) throw new Exception('Rss file "'.$src.'" not found or failed to open!');
		    		$parseContent = simplexml_load_string($content);
		    		foreach($parseContent->channel->item as $item){
		    			$object = new stdClass();
		    			$object->title = isset($item->title)?(String)$item->title:'';
		    			$object->link = isset($item->link)?(String)$item->link:'';
		    			$object->description = isset($item->description)?(String)$item->description:'';
		    			$object->datetime = isset($item->pubDate)?(String)$item->pubDate:'0';
		    			$this->dataset[] = $object;
		    		}
		    		unset($this->sources[$key]);
				}catch (Exception $e) {
					//echo $e->getMessage();
				}

	    	}
	    }
  	
    }


}