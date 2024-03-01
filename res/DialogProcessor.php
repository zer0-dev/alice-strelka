<?php
class DialogProcessor{
	
	private $uid;
	private $req;
	private $state;
	private $config;
	
	public function __construct($obj){
		$this->uid = $obj->session->application->application_id;
		$this->req = $obj->request;
		$this->state = $obj->state->session;
		$this->config = new Config();
	}
	
	public function process(){
		for($i = 0; $i < count($this->config->getCmds()); $i++){
			$c = $this->config->getCmds()[$i];
			if($this->state->value == $c->getState()){
				if(in_array($this->req->command, $c->getKeywords()) || in_array($this->state->value, Config::$user_input_states)) return call_user_func_array($c->callback, [$this->uid, $this->req, $this->state]);
			}
		}
		return new Response('Не совсем поняла, что вы хотели сказать. Я могу сохранять номера ваших карт Стрелка, а затем сообщать их баланс. Прямо сейчас вы можете меня попросить проверить баланс всех сохранённых карт, добавить новую карту или удалить уже добавленную, а также перечислить все добавленные карты.', $this->state->value);
	}
}
?>