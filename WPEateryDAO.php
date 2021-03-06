<?php
class WPEateryDAO{
	public static $DATABASE_ERROR = "Database Error!";
	public static $USER_NOT_FOUND = "User Not Found!";
	public static $UNKNOWN_ERROR = "Unknown Error!";
	
	/**
	 * Database host.
	 * @var String
	 */
	
	private static $DB_HOST = "us-cdbr-iron-east-04.cleardb.net";
	/**
	 * Database username.
	 * @var String
	 */
	private static $DB_USER = "b50d8c2726eda5";
	/**
	 * Database password.
	 * @var String
	 */
	private static $DB_PASS = "4a9690b1";
	/**
	 * Database name.
	 * @var String
	 */
	private static $DB_NAME = "heroku_cdb11dd97f00e5b";
	
	/**
	 * Mysqli object used to database communication.
	 * @var MySQLi
	 */
	protected $mysqli;
	
	/**
	 * Indicates whether or not there is a database connection error.
	 * @var boolean
	 */
	protected $connectionError = false;
	
	/**
	 * Indiates whether or not there is a MySQL error
	 * @var boolean
	 */
	protected $mysqlError = false;
	
	/**
	 * Constructor. Creates a MySQLi object and connects to the database.
	 */
	function __construct(){
		$this->connectionError = false;
		$this->mysqli = new mysqli(self::$DB_HOST, self::$DB_USER, self::$DB_PASS, self::$DB_NAME);
		if($this->mysqli->connect_errno){
			$this->connectionError = true;
		}
	}
	
	/**
	 * Returns true if there was a database connection issue, otherwise returns false.
	 * @return boolean
	 */
	public function hasConnectionError(){
		return $this->connectionError;
	}
	
	/**
	 * Returns true if there was a mysql error in the last statement, otherwise returns false.
	 * @return boolean
	 */
	public function hasMysqlError(){
		return $this->mysqlError;
	}
	
	/**
	 * Accepts a username and returns the password hash for that user if the user exists. Otherwise, returns self::$USER_NOT_FOUND.
	 * @param unknown $username $password
	 * @return AdminID 
	 */
	public function getUserID($username, $password){
		$query = "SELECT AdminID FROM adminusers WHERE Username = ? AND Password = ?";
		$stmt = $this->mysqli->prepare($query);
		$stmt->bind_param('ss', $username, $password);  //To find matched AdminID with the username and the password. 
		$stmt->execute();
		
		if($stmt->error){
			$this->mysqlError = true;
		} else {
			$result = $stmt->get_result();
			$row = $result->fetch_assoc();
			if($result->num_rows == 1){
				return $row['AdminID'];    //Pass AdminID when the username and password are matched with AdminID.
			} else {
				return self::$USER_NOT_FOUND;
			}
		}
		
	}
	/**
	 * Adds a user to the adminusers table. Returns the username.
	 * @param String $username
	 * @param String $password
	 * @return AdminUser the new user.
	 */
	public function add_user($username, $password){
		//We do not want to store the password as plain text. Instead, we
		//are going to get a hash of the password using password_hash.
		//This way, in the event of a database comprimise, your user's 
		//passwords will not be given away to attackers. This is especially
		//important becuase many people use the same password on all sites.
		$phash = password_hash($password, PASSWORD_DEFAULT);
		$query = 'INSERT INTO adminusers (Username, Password) VALUES (?,?)';
		$stmt = $this->mysqli->prepare($query);
		$stmt->bind_param('ss', $username, $phash);
		$stmt->execute();
		if($stmt->error){
			return self::$DATABASE_ERROR;
		} else {
			return new AdminUser($username, $phash);
		}
	}
	
}
?>