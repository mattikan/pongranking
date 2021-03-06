<?php
require ('lib/password.php');
class Player extends BaseModel{

	public $id, $name, $password, $organisation, $rating;

	public function __construct($attributes){
		parent::__construct($attributes);
	}

	public static function all(){
		$query = DB::connection()->prepare('SELECT * FROM Player');
		$query->execute();

		$rows = $query->fetchAll();
		$players = array();

		foreach($rows as $row){
			$players[] = new Player(array(
				'id' => $row['id'],
				'name' => $row['name'],
				'password' => $row['password'],
				'organisation' => $row['organisation'],
				'rating' => $row['rating']
			));
		}
		return $players;
	}

	public static function find($id){
		$query = DB::connection()->prepare('SELECT * FROM Player WHERE id = :id');
	    $query->execute(array('id' => $id));
		$row = $query->fetch();

		if($row){
			$player = new Player(array(
				'id' => $row['id'],
				'name' => $row['name'],
				'password' => $row['password'],
				'organisation' => $row['organisation'],
				'rating' => $row['rating']
			));
			return $player;
		}
		return null;
	}

	public static function destroy($id){
		$query = DB::connection()->prepare('DELETE FROM Player WHERE id = :id');
	    $query->execute(array('id' => $id));
	}

	public function check_validity(){
		$errors = array();
		if($this->name == '' || $this->name == null){
			$errors[] = 'name must not be empty';
		}
		if (strlen($this->password) < 8){
        	$errors[] = "Password too short!";
    	}
    	if (!preg_match("#[0-9]+#", $this->password)){
        	$errors[] = "Password must include at least one number!";
    	}
    	if (!preg_match("#[a-zA-Z]+#", $this->password)) {
        	$errors[] = "Password must include at least one letter!";
		}
		return $errors;
	}

	public function save(){
		$query = DB::connection()->prepare('INSERT INTO Player (name, password, organisation, rating) VALUES (:name, :password, :organisation, :rating) RETURNING id');
		$hash = password_hash($this->password, PASSWORD_BCRYPT);
		$query->execute(array('name' => $this->name, 'password' => $this->password, 'organisation' => $this->organisation, 'rating' => $this->rating));
		$row = $query->fetch();
	}

	public function update_rating(){
		$query = DB::connection()->prepare('UPDATE Player SET rating = :rating WHERE id = :id');
		$query->execute(array('id' => $this->id, 'rating' => $this->rating));
		$row = $query->fetch();
	}

	public static function authenticate($name, $password){
		$hash = password_hash($password, PASSWORD_BCRYPT);
		$query = DB::connection()->prepare('SELECT * FROM Player WHERE name = :name AND password = :password LIMIT 1');
		$query->execute(array('name' => $name, 'password' => $password));
		$row = $query->fetch();
		if($row){
			$player = new Player(array(
				'id' => $row['id'],
				'name' => $row['name'],
				'password' => $row['password'],
				'organisation' => $row['organisation'],
				'rating' => $row['rating']
			));
			return $player;
		}else{
			return null;
		}
	}
}