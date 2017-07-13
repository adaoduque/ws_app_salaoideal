<?php
class User_model extends CI_Model {

	//id user
	protected $id;
	
	//Table default
	protected $table = 'clientes';
	
	//Condition where
	public $where    =  null;

	/**
	 * @description  Constructor
	 */
    public function __construct() {
        parent::__construct();
    }

	/**
	 * @description  Execute query
	 * @return array
	 */
	public function get() : array {

		//Set table
	    $this->db->from( $this->table );
	    
	    //Check if $where is not empty
	    if( trim( $this->where ) != '' )
	    	
	    	//If not, add it
	    	$this->db->where( $this->where );
	    
	    //Execute query and get std class model
	    $this->dbObject = $this->db->get();

	    return $this->dbObject->result();

	}    

	/**
	 * @description  Set fields to select
	 * @param   fields to add in query
	 * @return  this object
	 */
	public function setFields( $fields ) : User_model {

		//Set field
		$this->db->select( $fields, false );
		
		//Return this class
		return $this;

	}

	/**
	 * @description  Set condition in clausule where
	 * @param  condition to set in clausule where
	 * @return User_model
	 */
	public function setWhere( $where ) : User_model {

		//Check if $where is empty
        if( trim( $this->where ) != '' )
            
            //If not, add operator AND
            $this->where  .=   " AND $where ";
        else 

        	//else, add only parameter
            $this->where  .=   "$where";

        //Return this class
        return $this;
	}

	/**
	 * @description  Set inner join, left and right
	 * @param  name table for first table in clausule join
	 * @param  condition to join clausule
	 * @param  orientation: inner, left, right 
	 * @return User_model
	 */
	public function setJoin( $table, $condition, $orientation = 'inner' ) : User_model {

		//Set join with parameter
		$this->db->join($table, $condition, $orientation);

		//Return this class
		return $this;
	}

	/**
	 * @description  Create user guest
	 * @param  name for user
	 * @param  email for user
	 * @param  path image for user
	 * @return int, code of user recent created
	 */
    public function createUser( $name, $email, $image ): int {
		
		//Create user
		$this->db->set('cli_data_cadastro', 'NOW()', FALSE);

		//Set array to register client
		$data  =   array(
			"cli_primeiro_nome"  =>  $name,
			"cli_email"          =>  $email,
			"cli_flag_ativo"     =>  1
		);

		//Insert data
		$this->insert( $data );

		//Get last id inserted
		$this->id  =  $this->db->insert_id();


		//Create image user
		$this->db->set('imgcli_criado', 'NOW()', FALSE);

		//Set array to register image for client
		$data  =   array(
			"cli_id"              =>  $this->id,
			"imgcli_path"         =>  $image,
			"imgcli_flag_ativo"   =>  1
		);

		//Set table for images_clientes
		$this->table  =  "image_clientes";

		//Insert data
		$this->insert( $data );

		//Get id image
		$idImage  =   $this->db->insert_id();


		//Create image profile user
		$this->db->set('imgcliuse_criado', 'NOW()', FALSE);

		//Set array for image active for profile
		$data  =   array(
			"imgcli_id"         =>  $idImage,
			"imgcliuse_active"  =>  1
		);	

		//Set table image_clientes_use
		$this->table  =  "image_clientes_use";	

		//Insert data
		$this->insert( $data );


		//Create user login
		$this->db->set('lgncli_cadastro', 'NOW()', FALSE);

		//Set array to register login
		$data  =   array(
			"cli_id"             =>  $this->id,
			"lgncli_user"        =>  $email,
			"lgncli_pass"        =>  $this->getPasswordTemp(),
			"lgncli_flag_ativo"  =>  1
		);

		//Set table login_clientes
		$this->table  =  "login_clientes";

		//Insert data
		$this->insert( $data );

		//Return id cliente
		return $this->id + 2000;

    }

	/**
	 * @description  Valide credentials login for users
	 * @param email for user
	 * @param password for user
	 * @return array
	 */
    public function sign( $user, $pass ): array {

    	//Set table
    	$this->table  =   "login_clientes AS LOG";

    	//Get data
    	$data   =   $this->setFields( "CLI.cli_id, cli_primeiro_nome, imgcli_path" ) //Set fields
	                     ->setJoin( "clientes AS CLI", "CLI.cli_id = LOG.cli_id" ) //Set inner join clientes and login_clientes
	                     ->setJoin( "image_clientes AS IMGCLI", "IMGCLI.cli_id = CLI.cli_id" ) //Set inner join clientes and image_clientes
	                     ->setJoin( "image_clientes_use AS IMGCLIUSE", "IMGCLIUSE.imgcli_id = IMGCLI.imgcli_id" ) //Set inner join image_clientes and image_clientes_use
	                     ->setWhere( "lgncli_user = '$user' AND lgncli_pass = '$pass'" ) //Set condition to valide login
	                     ->setWhere( "lgncli_flag_ativo = 1 AND cli_flag_ativo = 1 " ) //Set condition to valide user active
	                     ->get(); //Execute query

	    //Initialize array to response
	    $response   =   array();

	    //Check response data is equals 1
	    if( count( $data ) == 1 ) {

	    	//Fetch data
	    	$response["id"]     =   $data[0]->cli_id;
	    	$response["name"]   =   $data[0]->cli_primeiro_nome;
	    	$response["image"]  =   $data[0]->imgcli_path;

	    }

	    //Return
	    return $response;
    }

	/**
	 * @description  Update data in database
	 * @param  array with all data to update
	 * @return void
	 */
    public function update( $data = array() ) : void {
    	//Set condition
        $this->db->where("cligue_id = $this->id", null);

        //Update data by condition
  		$this->db->update($this->table, $data);
    }    

	/**
	 * @description  Insert data in database
	 * @param  array with all data to insert
	 * @return void
	 */
    public function insert($data = array()) : void{

    	//Insert data
		$this->db->insert($this->table, $data );
	}

	/**
	 * @description  Generate random password
	 * @param  desired password size
	 * @return String
	 */
	public function getPasswordTemp( $length = 8 ) : String {

		//Chars allowed
	    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	    //Count to get length
	    $count = mb_strlen($chars);

	    //For...
	    for ($i = 0, $pass = ''; $i < $length; $i++) {

	    	//Generate number randomic
	        $index = rand(0, $count - 1);

	        //Get char by number generated
	        $pass .= mb_substr($chars, $index, 1);

	    }

	    //Return password
	    return $pass;

	}

	/**
	 * @description  Debug last query executed
	 * @return void
	 */
	public function showLastQuery() {
		echo preg_replace('/`/', '', $this->db->last_query() ); exit;
	}	

}