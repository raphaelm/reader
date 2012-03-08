<?php
class OPML_Parser {
	private $xml = null;
	private $feeds = array();
	private $errors = false;
	
	public function __construct(){
	}
	
	private function add_feed($title, $url){
		$this->feeds[] = array("title" => $title, "url" => $url);
	}
	
	private function parse_outline($el, $firstlevel = false){
		if(isset($el->outline)){
			foreach ($el->outline as $child) {
				$this->parse_outline($child);
			}
		}else{
			$attr = $el->attributes();
			if(isset($attr["type"]) && $attr["type"] == "rss"){
				$this->add_feed((string) $attr["title"], (string) $attr["xmlUrl"]);
			}
		}
		
	}
	
	public function get_errors(){
		return $this->errors;
	}
	
	public function get_feeds(){
		return $this->feeds;
	}
	
	public function parse($xmlstring){
		libxml_use_internal_errors(true);
		$this->xml = simplexml_load_string($xmlstring);
		if(!$this->xml){
			$this->errors = libxml_get_errors();
			libxml_clear_errors();
			return false;
		}
		if(!isset($this->xml->body))
			return false;
		
		$this->parse_outline($this->xml->body, true);		
		return true;
	}
}
