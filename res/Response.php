<?php
class Response{
	
	private $text;
	private $state;
	private $end_session;
	private $tts;
	private $add_info;
	private $btn;
	
	public function __construct($text, $state, $end = false, $tts = null, $btn = [], $add_info = []){
		$this->text = $text;
		$this->state = $state;
		$this->end_session = $end;
		$this->tts = ($tts == null) ? str_replace('\n', ', ', $text) : $tts;
		$this->btn = $btn;
		$this->add_info = $add_info;
	}
	
	public function toJson(){
		$res = [
			'response' => [
				'text' => $this->text,
				'tts' => $this->tts,
				'end_session' => $this->end_session,
				'buttons' => $this->btn
			],
			'session_state' => array_merge(['value' => $this->state], $this->add_info),
			'version' => '1.0'
		];
		return json_encode($res);
	}
}
?>