<?php
class Command{
	private $keywords;
	private $state;
	public $callback;
	
	public function __construct($keywords, $state, $callback){
		$this->keywords = $keywords;
		$this->state = $state;
		$this->callback = $callback;
	}
	
	public function getKeywords(){
		return $this->keywords;
	}
	
	public function getState(){
		return $this->state;
	}
}
?>