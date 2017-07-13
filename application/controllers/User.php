<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class User extends REST_Controller {

    //Code for token
	protected $code;

    //Code for hash
	protected $hash;

    //for output json
    protected $u_output = array();

    /**
     * @description  Constructor
     */
	public function __construct() {

		parent::__construct();

		//Initialize connection with database
		$this->load->database();

        //Load model
        $this->load->model('user_model');

        //Load helper
        $this->load->helper('mask');
		
	}

    /**
     * @description Set output to response
     * @param  id user
     * @param  name user
     * @param  email user
     * @param  url image user
     * @return void
     */
    protected function setOutput( $id, $name, $email, $image ) : void {

        //Mask the code
        $hash = Mask::hashGuest( $id );

        //Set data to generate token
        $data = array( 'hash' => $hash, 'code' => $id );        

        //Get the authorization token
        $this->u_output['token']   =  AUTHORIZATION::generateToken( $data );

        //Set name
        $this->u_output['name']    =  $name;

        //Set e-mail
        $this->u_output['email']   =  $email;

        //Set image with path to webservice
        $this->u_output['image']   =  base_url().$image;

        //Set id
        $this->u_output['id']      =  $id;

    }

    /**
     * @description  Register new user
     * @param  name, name user
     * @param  email, email user
     * @param  image, url image user
     * @link   user/register/post
     * @method POST
     * @return JSON Object
     */
    public function register_post() : void {

        //Load helper
        $this->load->helper('randomnumber');

        //load helper
        $this->load->helper('expressionregular');

        //Get post name
        $name        =   $this->post("name");

        //Get post e-mail
        $email       =   $this->post("email");

        //Get post link image
        $linkImage    =   $this->post("image");

        //Check if name is alphanumeric and e-mail is valid
        if( !isAlphaNumeric( $name ) || !isEmail( $email ) ) {

            //If not, send http status 401, unauthorized
            $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);

        }else {

            //Set url image is empty
            $urlImage   =   "";

            //Check if url is not broken
            if( $this->urlExists( $linkImage ) ) {

                //Download image
                $urlImage  =  $this->downlaodImage( $linkImage );

            }

            //Create user and get id
            $id   =   $this->user_model->createUser( $name, $email, $urlImage );

            //Set data to output
            $this->setOutput( $id, $name, $email, $urlImage );

            //Return token and anothers data
            $this->set_response($this->u_output, REST_Controller::HTTP_OK);

            //Simple return
            return;            

        }

    }

    /**
     * @description  sign user
     * @param  user, email for user
     * @param  pass, password for user
     * @link   user/sign/post
     * @method POST
     * @return Json Object
     */
    public function sign_post() {

        //Load helper
        $this->load->helper('expressionregular');

        //Get post user
        $user       =   $this->post("user");

        //Get post password
        $pass       =   $this->post("pass");

        //Check if e-mail is valid
        if( !isEmail( $user ) ) {

            //unauthorized, send http status 401
            $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);

        }else {

            //Initialize variable for response
            $response   =   array();

            //Get response login
            $response   =   $this->user_model->sign( $user, $pass );

            //Set data to output
            $this->setOutput( $response["id"], $response["name"], $user, $response["image"] );

            //Return token and anothers data
            $this->set_response($this->u_output, REST_Controller::HTTP_OK);

            //Simple return
            return; 

        }

    }


    /**
     * @description  download image of url
     * @param  url to download image
     * @return String
     */
    protected function downlaodImage( $url ): String {
        
        //Set directory to save image
        $dir   =  'assets/images/';

        //Set condition false
        $cond  =  false;

        //When cond is false
        while ( !$cond ) {

            //Generate random number and set extension for file
            $imgName  =   Randomnumber::getRandomNumber() . ".jpg";

            //Concatenate with directory
            $img      =   $dir . $imgName;

            //Check if file exists
            if( !file_exists( $img ) ) {

                //If not exists, get file content of url and save it with the name generated
                file_put_contents( $img, file_get_contents( $url ) );

                //Set condition true to stop while
                $cond = true;

            }

        }

        return $img;
        
    }

    /**
     * @description  check if url is valid
     * @param  url to verify
     * @return Bool
     */
    protected function urlExists( $url ) : Bool {
        
        //Initialize curl with url
        $ch = curl_init($url);    
        
        //Set true to get body of header
        curl_setopt($ch, CURLOPT_NOBODY, true);
        
        //Execute call
        curl_exec($ch);
        
        //Get http code
        $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //Check if code is 200 or 302
        $status =  ( $code == 200 || $code == 302 ) ? true : false;
        
        //Close call
        curl_close($ch);

        //return
        return $status;
    }

    /**
     * @description validate the token 
     * @param  Authorization, parameter header
     * @return void
     */
    protected function token_validate() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
            if ($decodedToken != false) {
                $this->hash = $decodedToken->hash;
                $this->code = $decodedToken->code;
                return true;
            }
        }
        return false;
    } 

    /**
     * @description for debug
     * @param  data for debug
     * @return void
     */
    protected function setLog( $log ) : void {

        //Start get output
        ob_start();

        //Debug
        var_dump( $log );

        //Return the contents of the output buffer
        $data = ob_get_contents();

        // Clean (erase) the output buffer and turn off output buffering
        ob_end_clean();

        // Write final string to file
        file_put_contents("assets/log.txt", $data);

    }

}