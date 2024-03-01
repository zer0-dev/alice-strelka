<?php
class Database{
	private $db;
	
	public function __construct(){
		$cred = Config::$db_cred;
		$this->db = new mysqli($cred['ip'], $cred['user'], $cred['password'], $cred['db']);
	}
	
	public function getCards($uid){
		$res = [];
		
		$prep = $this->db->prepare("SELECT * FROM YANDEX WHERE UID = ?");
		$prep->bind_param('s', $uid);
		$prep->execute();
		$req = $prep->get_result();
		
		if(!empty($this->db->error)){
			return $this->db->error;
		}
		
		while($row = $req->fetch_assoc()){
			$res[] = new Card($row['NUMBER'], $row['NAME']);
		}
		return $res;
	}
	
	public function addCard($uid, $number, $name){
		$prep = $this->db->prepare("INSERT INTO YANDEX (UID, NUMBER, NAME) VALUES (?, ?, ?)");
		$prep->bind_param('sss', $uid, $number, $name);
		$prep->execute();
		$req = $prep->get_result();
		
		if(!empty($this->db->error)){
			return false;
		}
		
		return true;
	}
	
	public function checkCard($uid, $name){
		$prep = $this->db->prepare("SELECT * FROM YANDEX WHERE UID = ? AND NAME = ?");
		$prep->bind_param('ss', $uid, $name);
		$prep->execute();
		$req = $prep->get_result();
		
		if(!empty($this->db->error)){
			return $this->db->error;
		}
		
		return $req->num_rows > 0;
	}
	
	public function checkNumber($uid, $number){
		$prep = $this->db->prepare("SELECT * FROM YANDEX WHERE UID = ? AND NUMBER = ?");
		$prep->bind_param('ss', $uid, $number);
		$prep->execute();
		$req = $prep->get_result();
		
		if(!empty($this->db->error)){
			return $this->db->error;
		}
		
		return $req->num_rows > 0;
	}
	
	public function deleteCard($uid, $name){
		$prep = $this->db->prepare("DELETE FROM YANDEX WHERE UID = ? AND NAME = ?");
		$prep->bind_param('ss', $uid, $name);
		$prep->execute();
		$req = $prep->get_result();
		
		if(!empty($this->db->error)){
			return false;
		}
		
		return true;
	}
}
?>