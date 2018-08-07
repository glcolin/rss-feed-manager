<?php

class rssManager{

	protected $dataset = array();
	protected $sources = array();

    /**
    * Constructor.
    */
	public function __construct($sources = array(), $autoFetch = true) {

		$this->sources = $sources;
        //The child class can set this to be false to run its own fetching array.
		if($autoFetch) $this->fetchPosts();

    }

    /**
    * The default method to fetch rss feed data.
    */
    protected function method_default(){

    	if(!empty($this->sources)){
	    	foreach($this->sources as $key => $src){

	    		try{
		    		$parseFile = @simplexml_load_file($src,'SimpleXMLElement', LIBXML_NOCDATA);
		    		if(false === $parseFile) throw new Exception('Rss file "'.$src.'" not found or failed to open!');
		    		foreach($parseFile->channel->item as $item){
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

    /**
    * Run thru different methods to fetch the data from the rss feed urls.
    */
    protected function fetchPosts($methods = array('method_default')){

    	$this->dataset = array();

    	//Since not all the xml files are in the same format,
    	//the rssManager will have to try to fecth the data in a variety of methods.
    	foreach($methods as $method){
    		call_user_func_array(array($this, $method), array());
    	}

    	$this->sortPosts();
    	//$this->logUnfetchedPosts();

    }

    /**
    * Log if any link is failed to fetched.
    */
    protected function logUnfetchedPosts(){

    	try{
	    	//After all methods above, if there is still source is unable to be managed,
		    //Add message to log:
		    if(!empty($this->sources)){
				$fh = fopen(dirname(__FILE__).'/fetch_log.txt', 'w');
		    	foreach($this->sources as $key => $src){
		    		fwrite($fh, 'unable to handle '.$src."\n\n");
		    	}
		    	fclose($fh);
		    }else{
		    	if(file_exists(dirname(__FILE__).'/fetch_log.txt')) {
				    unlink(dirname(__FILE__).'/fetch_log.txt');
				}
		    }
		}
	    catch(Exception $ex){/*Do Nothing*/}

    }

    /**
    * Sort the posts by date desc.
    */
    protected function sortPosts(){

    	//Bubble sort dataset:
    	for($i = 0; $i < count($this->dataset); $i++){
    		for($j = $i+1; $j < count($this->dataset)-1; $j++){
    			if(strtotime($this->dataset[$j]->datetime) > strtotime($this->dataset[$j+1]->datetime)){
    				//Swap 2 data object:
    				$t = $this->dataset[$j];
    				$this->dataset[$j] = $this->dataset[$j+1];
    				$this->dataset[$j+1] = $t;
    			}
    		}
    	}

    }

    /**
    * Return all posts.
    */
    public function getPosts(){

    	return $this->dataset;

    }

    /**
    * Param $number is the number of lastest posts to get.
    * Return the latest posts.
    */
    public function getLatestPosts($number = 10){

    	$result = array();
    	for($i = 0; $i < $number; $i++){
    		$result[] = $this->dataset[$i];
    	}
    	return $result;

    }


}