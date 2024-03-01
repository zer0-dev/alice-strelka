<?php
class Config{
	private $cmds;
	
	public static $user_input_states = [3, 5, 7];
	public static $db_cred = ['ip' => 'localhost', 'user' => '', 'password' => '', 'db' => ''];
	
	public static $yes = ['да', 'давай', 'хорошо', 'ок', 'окей', 'давайте', 'да давай', 'верно', 'хорошо', 'вперед', 'вперёд', 'да давайте', 'правильно', 'да верно', 'да правильно'];
	public static $no = ['нет', 'не надо', 'не нужно', 'не', 'потом'];
	
	public static $yes_no_btn = [
		[
			'title' => 'Да',
			'hide' => true
		],
		[
			'title' => 'Нет',
			'hide' => true
		]
	];
	
	public static $help_btn = [
		[
			'title' => 'Баланс',
			'hide' => true
		],
		[
			'title' => 'Добавь карту',
			'hide' => true
		],
		[
			'title' => 'Удали карту',
			'hide' => true
		],
		[
			'title' => 'Список карт',
			'hide' => true
		],
	];
	
	public function __construct(){
		$start_callback = function($uid, $req, $s){
			$text = 'Привет, я Стрелка! Я помогаю быстро узнавать баланс карты "Стрелка". Сказать, сколько у вас сейчас денег?';
			$tts = 'Привет, я Стрелка! Я помогаю быстро узнавать баланс карты "Стр+елка". Сказать, сколько у вас сейчас денег?';
			$btn = array_merge(self::$yes_no_btn, [['title' => 'Помощь', 'hide' => true]]);
			return new Response($text, 0, false, $tts, $btn);
		};
		
		$cardlist_callback = function($uid, $req, $s){
			return Config::cardlistCallback($uid, $req, $s);
		};
		
		$help_callback = function($uid, $req, $s){
			return new Response('Я могу сохранять номера ваших карт Стрелка, а затем сообщать их баланс.\nЧтобы узнать баланс всех сохранённых карт, скажите "баланс".\nЧтобы добавить новую карту, скажите "добавь карту".\nЧтобы удалить карту, скажите "удали карту".\nЧтобы получить список ваших сохранённых карт, скажите "список карт".', 0, false, null, self::$help_btn);
		};
		
		$add_card_callback = function($uid, $req, $s){
			$db = new Database();
			$cards = $db->getCards($uid);
			$text = '';
			$state = '';
			$tts = null;
			$btn = [];
			$end = false;
			if(count($cards) < 5){
				$text = 'Окей, назовите номер вашей карты Стрелка, каждую цифру по отдельности.';
				$state = 3;
			} else{
				$text = 'Я могу хранить не больше 5 номеров одновременно. Попробуем почистить список?';
				$tts = 'Я могу хранить не больше пяти номеров одновременно. Попробуем почистить список?';
				$state = 8;
				$btn = self::$yes_no_btn;
			}
			return new Response($text, $state, $end, $tts, $btn);
		};
		
		$end_callback = function($uid, $req, $s){
			return new Response('Хорошо. Если понадоблюсь - позовите.', 0, true);
		};
		
		$card_input_callback = function($uid, $req, $s){
			$text = '';
			$state = 0;
			$end = false;
			$add_info = [];
			$tts = null;
			$btn = [];
			$number = [];
			
			$nlu = $req->nlu->tokens;
			for($i = 0; $i < count($nlu); $i++){
				$token = $nlu[$i];
				if(is_numeric($token)){
					$number[] = $token;
				} else{
					$text = 'Извините, не совсем поняла вас. Назовите, пожалуйста, 11-значный номер карты Стрелка, каждую цифру отдельно.';
					$tts = 'Извините, не совсем поняла вас. Назовите, пожалуйста одинадцатизначный номер карты Стрелка, каждую цифру отдельно.';
					$state = 3;
					break;
				}
			}
			if(strlen(implode($number)) == 11){
				$text = 'Номер карты - '.implode($number).'. Я правильно поняла?';
				$tts = 'Номер карты - '.implode(' ', str_split(implode($number))).'. Я правильно поняла?';
				$state = 4;
				$add_info = ['card' => implode($number)];
				$btn = self::$yes_no_btn;
			} else{
				$text = 'Извините, не совсем поняла вас. Назовите, пожалуйста, 11-значный номер карты Стрелка, каждую цифру отдельно.';
				$tts = 'Извините, не совсем поняла вас. Назовите, пожалуйста одинадцатизначный номер карты Стрелка, каждую цифру отдельно.';
				$state = 3;
			}
			return new Response($text, $state, $end, $tts, $btn, $add_info);
		};
		
		$name_prompt_callback = function($uid, $req, $s){
			$text = '';
			$tts = null;
			$state = 0;
			$add_info = [];
			$btn = [];
			$db = new Database();
			
			if(Strelka::checkCard($s->card)){
				if(!$db->checkNumber($uid, $s->card)){
					$text = 'Теперь придумайте имя, под которым карта будет сохранена. Оно должно быть не длиннее 10 символов и состоять из одного слова. Например, Главная.';
					$tts = 'Теперь придумайте имя, под которым карта будет сохранена. Оно должно быть не длиннее десяти символов и состоять из одного слова. Например, Главная.';
					$state = 5;
					$add_info = ['card' => $s->card];
				} else{
					$text = 'Этот номер уже сохранён.\nЧтобы узнать баланс всех сохранённых карт, скажите "баланс".\nЧтобы добавить новую карту, скажите "добавь карту".\nЧтобы удалить карту, скажите "удали карту".\nЧтобы получить список ваших сохранённых карт, скажите "список карт".';
					$state = 0;
					$btn = self::$help_btn;
				}
			} else{
				$text = 'К сожалению, номер неверен, либо карта заблокирована. Попробуйте ещё раз.';
				$state = 3;
			}
			return new Response($text, $state, false, $tts, $btn, $add_info);
		};
		
		$name_input_callback = function($uid, $req, $s){
			$text = '';
			$state = 0;
			$tts = null;
			$add_info = ['card' => $s->card];
			$btn = [];
			$db = new Database();
			
			if(mb_strlen($req->command) < 11 && !strpos($req->command, ' ') && mb_strlen($req->command) > 0){
				if(!$db->checkCard($uid, Util::mb_ucfirst($req->command, "utf8"))){
					$text = 'Итак, карта будет сохранена под именем '.Util::mb_ucfirst($req->command, "utf8").'. Верно?';
					$state = 6;
					$add_info = ['card' => $s->card, 'name' => Util::mb_ucfirst($req->command, "utf8")];
					$btn = self::$yes_no_btn;
				} else{
					$text = 'Карта с таким названием уже сохранена. Выберите, пожалуйста, другое имя.';
					$state = 5;
				}
			} else{
				$text = 'Извините, такое имя я запомнить не смогу. Оно должно быть не длиннее 10 символов и состоять из одного слова. Попробуйте ещё раз.';
				$tts = 'Извините, такое имя я запомнить не смогу. Оно должно быть не длиннее десяти символов и состоять из одного слова. Попробуйте ещё раз.';
				$state = 5;
			}
			return new Response($text, $state, false, $tts, $btn, $add_info);
		};
		
		$save_card_callback = function($uid, $req, $s){
			$db = new Database();
			
			if($db->addCard($uid, $s->card, $s->name)){
				return Config::cardlistCallback($uid, $req, ['value' => 0]);
			} else{
				return new Response('Произошла ошибка при сохранении данных. Повторите попытку позже.', 0, true);
			}
		};
		
		$delete_card_callback = function($uid, $req, $s){
			$text = 'Скажите название карты, которую хотите удалить. Я могу напомнить названия ваших карт, только попросите.';
			$state = 7;
			$btn = [
				[
					'title' => 'Напомни',
					'hide' => true
				]
			];
			return new Response($text, $state, false, null, $btn);
		};
		
		$delete_card_input_callback = function($uid, $req, $s){
			$text = '';
			$state = 0;
			$add_info = [];
			$btn = [];
			$db = new Database();
			
			$keywords = array_merge(self::$yes, ['напомни', 'наопмните', 'список карт', 'все карты', 'список', 'все', 'всё', 'список моих карт', 'все мои карты', 'мои карты']);
			if(in_array($req->command, $keywords)) return Config::listCallback($uid, $req, $s);
			
			if($db->checkCard($uid, Util::mb_ucfirst($req->command, "utf8"))){
				$text = 'Удалить карту '.Util::mb_ucfirst($req->command, "utf8").'?';
				$add_info = ['name' => Util::mb_ucfirst($req->command, "utf8")];
				$state = 9;
				$btn = self::$yes_no_btn;
			} else{
				$text = 'Не нашла карты с таким названием в списке. Повторите, пожалуйста.';
				$state = 7;
			}
			return new Response($text, $state, false, null, $btn, $add_info);
		};
		
		$confirm_delete_card_callback = function($uid, $req, $s){
			$text = '';
			$state = 0;
			$btn = [];
			$db = new Database();
			
			if($db->deleteCard($uid, Util::mb_ucfirst($s->name, "utf8"))){
				$text = 'Удалила! Чтобы узнать баланс всех сохранённых карт, скажите "баланс". Чтобы добавить новую карту, скажите "добавь карту". Чтобы удалить карту, скажите "удали карту". Чтобы получить список ваших сохранённых карт, скажите "список карт".';
				$state = 0;
				$btn = self::$help_btn;
			} else{
				$text = 'Произошла ошибка при подключении к базе данных. Повторите попытку позже.';
				$state = 0;
			}
			return new Response($text, $state, false, null, $btn);
		};
		
		$cancel_delete_callback = function($uid, $req, $s){
			return new Response('Хорошо, не буду.\nЧтобы узнать баланс всех сохранённых карт, скажите "баланс".\nЧтобы добавить новую карту, скажите "добавь карту".\nЧтобы удалить карту, скажите "удали карту".\nЧтобы получить список ваших сохранённых карт, скажите "список карт".', 0, false, null, self::$help_btn);
		};
		
		$list_callback = function($uid, $req, $s){
			return Config::listCallback($uid, $req, $s);
		};
			
		$this->cmds = [
			new Command([''], 0, $start_callback),
			new Command(self::$no, 0, $end_callback),
			new Command(['помощь', 'что ты умеешь', 'что ты можешь', 'что вы умеете', 'что вы можете', 'помоги', 'помогите', 'как пользоваться', 'нужна помощь'], 0, $help_callback),
			new Command(array_merge(self::$yes, ['баланс', 'сколько денег', 'какой баланс', 'какой у меня баланс', 'какой баланс у меня', 'сколько у меня денег', 'сколько денег у меня', 'сколько денег у меня на карте', 'сколько денег на карте', 'сколько денег на карте у меня', 'баланс карты', 'сколько денег у меня на картах', 'сколько денег на картах', 'сколько денег на картах у меня', 'баланс карт', 'сколько денег у меня на стрелке', 'сколько денег на стрелке', 'сколько денег на стрелке у меня', 'баланс стрелки', 'скажи баланс', 'скажи сколько денег', 'скажи какой баланс', 'скажи какой у меня баланс', 'скажи какой баланс у меня', 'скажи сколько у меня денег', 'скажи сколько денег у меня', 'скажи сколько денег у меня на карте', 'скажи сколько денег на карте', 'скажи сколько денег на карте у меня', 'скажи баланс карты', 'скажи сколько денег у меня на картах', 'скажи сколько денег на картах', 'скажи сколько денег на картах у меня', 'скажи баланс карт', 'скажи сколько денег у меня на стрелке', 'скажи сколько денег на стрелке', 'скажи сколько денег на стрелке у меня', 'скажи баланс стрелки', 'назови баланс', 'назови мой баланс', 'мой баланс']), 0, $cardlist_callback),
			new Command(['добавь карту', 'добавить карту', 'добавьте карту', 'добавь', 'добавить', 'добавьте', 'новая карта', 'новая', 'создай карту', 'создать карту', 'создайте карту', 'создать', 'создай', 'создайте'], 0, $add_card_callback),
			new Command(['удали карту', 'удалить карту', 'удалите карту', 'удали', 'удалить', 'удалите', 'удали карты', 'удалить карты', 'удалите карты'], 0, $delete_card_callback),
			new Command(['список карт', 'все карты', 'список', 'все', 'всё', 'список моих карт', 'все мои карты', 'мои карты', 'перечисли', 'перечислите', 'перечисли карты', 'перечисли все карты', 'перечисли мои карты', 'перечисли все мои карты', 'перечисли мои все карты', 'перечислите карты', 'перечислите все карты', 'перечислите мои карты', 'перечислите все мои карты', 'перечислите мои все карты', 'назови карты', 'назови все карты', 'назови мои карты', 'назови все мои карты', 'назови мои все карты', 'назовите карты', 'назовите все карты', 'назовите мои карты', 'назовите все мои карты', 'назовите мои все карты'], 0, $list_callback),
			
			new Command(self::$yes, 2, $add_card_callback),
			new Command(self::$no, 2, $end_callback),
			
			new Command([], 3, $card_input_callback),
			
			new Command(self::$yes, 4, $name_prompt_callback),
			new Command(self::$no, 4, $add_card_callback),
			
			new Command([], 5, $name_input_callback),
			
			new Command(self::$yes, 6, $save_card_callback),
			new Command(self::$no, 6, $name_prompt_callback),
			
			new Command([], 7, $delete_card_input_callback),
			
			new Command(self::$yes, 8, $delete_card_callback),
			new Command(self::$no, 8, $end_callback),
			
			new Command(self::$yes, 9, $confirm_delete_card_callback),
			new Command(self::$no, 9, $cancel_delete_callback)
		];
	}
	
	public function getCmds(){
		return $this->cmds;
	}
	
	public static function cardlistCallback($uid, $req, $s){
		$db = new Database();
		$cards = $db->getCards($uid);
		$text = '';
		$tts = '';
		$state = 0;
		$btn = [];
		$end = true;
		
		if(is_array($cards)){
			if(count($cards) > 0){
				for($i = 0; $i < count($cards); $i++){
					if(Strelka::checkCard($cards[$i]->getNumber())){
						$card = Util::declension(Strelka::getBalance($cards[$i]));
						$text .= $cards[$i]->getName().' имеет на балансе '.$card.'\n';
						$tts .= $cards[$i]->getName().' имеет на балансе '.$card.', ';
					} else{
						$text .= $cards[$i]->getName().' была заблокирована, либо срок её действия истёк\n';
						$tts .= $cards[$i]->getName().' была заблокирована, либо срок её действия истёк, ';
					}
				}
				$state = 1;
			} else{
				$text = 'Я не знаю номера вашей карты. Добавим?';
				$state = 2;
				$end = false;
				$btn = self::$yes_no_btn;
			}
		} else{
			$text = 'Ошибка запроса к базе данных: '.$cards;
		}
		return new Response($text, $state, $end, $tts, $btn);
	}
	
	public static function listCallback($uid, $req, $s){
		$db = new Database();
		$cards = $db->getCards($uid);
		$text = '';
		$tts = null;
		$btn = [];
		$state = $s->value;
		
		if(count($cards) > 0){
			for($i = 0; $i < count($cards); $i++){
				$text .= $cards[$i]->getName().' ('.$cards[$i]->getNumber().')\n';
				$tts .= $cards[$i]->getName().' ('.implode(' ', str_split($cards[$i]->getNumber())).'), ';
			}
		} else{
			$text = 'Я не знаю номера вашей карты. Добавим?';
			$state = 2;
			$btn = self::$yes_no_btn;
		}
		return new Response($text, $state, false, $tts, $btn);
	}
}
?>