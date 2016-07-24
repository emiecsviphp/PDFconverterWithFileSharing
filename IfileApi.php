<?php 
/**
 * @author ifile.it
 * @copyright 2011, Simplified BSD License
 * 
Copyright <2011> <ifile.it>. All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are
permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, this list of
      conditions and the following disclaimer.

   2. Redistributions in binary form must reproduce the above copyright notice, this list
      of conditions and the following disclaimer in the documentation and/or other materials
      provided with the distribution.

THIS SOFTWARE IS PROVIDED BY <ifile.it> ''AS IS'' AND ANY EXPRESS OR IMPLIED
WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL <ifile.it> OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

The views and conclusions contained in the software and documentation are those of the
authors and should not be interpreted as representing official policies, either expressed
or implied, of <ifile.it>.
 * 
 * @name IfileApi
 * @requires php curl extension
 * @note tested on PHP 5.3, no guarantees this might work on earlier
 * @abstract A PHP5 wrapper to make use of the new ifile.it api (September 2011 on)
 * see example files in this folder for examples of use of this api via this code
 * this code is deliberately kept as simple as possible in order to increase understanding
 */





class IfileApi {
	
	
	/**
	 * send a request over SSL to the api to give you your apikey,
	 * your apikey changes everytime you change your password, 
	 * and provided the password is not changed on your account can be cached by your application,
	 * the api key is required on any api calls involving your account such as
	 * renaming files, uploading and adding to your account and so on 
	 * 
	 * @param string, $username
	 * @param string, $password
	 * @return string, $apikey
	 * @throws IfileApiException
	 */
	public function fetchApiKey( $username, $password ){
		
		//send a request to authenticate
		try {
			
			$result = $this->post(
				'https://secure.ifile.it/api-fetch_apikey.api',
				array(
					'username' 	=> 	$username,
					'password'	=>	$password
				)
			);	
			
			
			if ( array_key_exists('akey', $result) == true ){
				
				return 	$result['akey'];
			}
			else{
				
				throw new IfileApiException( 'Unable to get api key, wrong username/password?' );
			}
		}
		catch ( IfileApiException $e){
			
			throw new IfileApiException( $e->getMessage() );
		}
	}
	
	
	
	
	
	/**
	 * uploads a file to ifile.it, and optionally attaches it to a user account
	 * 
	 * @param string, $filepath
	 * @param string, $apikey OPTIONAL defaults to uploading as anonymous user
	 * @throws IfileApiException
	 */
	public function upload( $filepath, $apikey = false ){
		
		//check the file is actually readable
		if ( (is_readable($filepath) == false ) || (is_file( $filepath ) == false )){
			
			throw new IfileApiException('Input file is not readable');
		}
		
		
		//first things first lets determine an upload server
		try {
			
			$response	=	$this->post(
				'http://ifile.it/api-fetch_upload_url.api',
				array()
			);
			
			if ( array_key_exists('upload_url', $response) == true ){
				
				$uploadUrl = $response['upload_url'];
			}
			else{
				
				throw new IfileApiException('No upload url available, uploads disabled?');
			}
		}
		catch ( IfileApiException $e){
			
			throw new IfileApiException('Unable to determine upload server, uploads disabled?');
		}
		
		
		
		
		
		//decide whether to send an apikey or upload as anonymous
		$params = array();
		if ( $apikey != false ){
			
			$params['akey']	=	$apikey;
		}
		
		
		
		//add our file to POST field
		$params["Filedata"] = "@".$filepath;
		
		
		//secondly lets upload the file via php curl
		if ( ( $handle	= curl_init() ) === false ) {
			
			throw new IfileApiException('Unable to create curl handle, check php curl extension is available?');
		}
		
		
		//set request options
		curl_setopt( $handle, CURLOPT_URL, $uploadUrl );
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true  );
		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 5 );
		curl_setopt( $handle, CURLOPT_POST, true );
		curl_setopt( $handle, CURLOPT_POSTFIELDS, $params );
		
      
		//execute the request
		if ( ($result = curl_exec( $handle )) === false) {
			
			throw new IfileApiException('HTTP upload failed');
		}

		
		
		//check for http 200 ok code
		$code 	=	curl_getinfo( $handle, CURLINFO_HTTP_CODE  );
		if ($code != '200') {
			
			throw new IfileApiException('HTTP upload failed. HTTPCODE #'.$code );
		}
		
		
		
		//close the connection
		curl_close($handle); 
		
		

		//decode our response
		$result = json_decode( $result,true );
		
		
			
		//check the response is correctly formatted
		if (
			( is_array( $result ) === true )
			&&
			( array_key_exists('status', $result ) == true )
			&&
			( $result['status'] == 'ok')
		){
			
			//add url to result
			$result['url']	=	'http://ifile.it/'.$result['ukey'].'/'.rawurlencode( $result['name'] );
			
			return 	$result;
		}
		else{
			
			if ( ( is_array( $result ) === true ) &&( array_key_exists('message', $result ) == true )){
				
				throw new IfileApiException( $result['message'] );
			}
			else{
				
				throw new IfileApiException( 'Badly formatted reponse received' );
			}	
		}
	}
	
	

	
	
	
	/**
	 * Performs a http post to an api endpoint, 
	 * decodes the json response received in reply
	 * 
	 * @param string, $url
	 * @param array, $params
	 * @return array, $result
	 * @throws IfileApiException
	 */
	public function post( $url, array $params = array() ){
		

		//create a handle
		if ( ( $handle	= curl_init() ) === false ) {
			
			throw new IfileApiException('Unable to create curl handle, check php curl extension is available?');
		}
		
		
		//set request options
		curl_setopt( $handle, CURLOPT_URL, $url );
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true  );
		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 5 );
		curl_setopt( $handle, CURLOPT_POST, true );
		curl_setopt( $handle, CURLOPT_POSTFIELDS, $params );
		
      
		//execute the request
		if ( ($result = curl_exec( $handle )) === false) {
			
			throw new IfileApiException('HTTP request to the api failed');
		}

		
		
		//check for http 200 ok code
		$code 	=	curl_getinfo( $handle, CURLINFO_HTTP_CODE  );
		if ($code != '200') {
			
			throw new IfileApiException('HTTP request to the api failed. HTTPCODE #'.$code );
		}
		
		
		
		//close the connection
		curl_close($handle); 
		
		

		//decode our response
		$result = json_decode( $result,true );
		
		
		//check the response is correctly formatted
		if (
			( is_array( $result ) === true )
			&&
			( array_key_exists('status', $result ) == true )
			&&
			( $result['status'] == 'ok')
		){
			
			return 	$result;
		}
		else{
			
			if ( ( is_array( $result ) === true ) &&( array_key_exists('message', $result ) == true )){
				
				throw new IfileApiException( $result['message'] );
			}
			else{
				
				throw new IfileApiException( 'Badly formatted reponse received' );
			}	
		}
	}
}





class IfileApiException extends Exception{ 
	public function __construct($message, $code = 0) { 
		parent::__construct($message, $code); 
	} 
}

?>