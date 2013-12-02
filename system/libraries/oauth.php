<?php
/**
 * Copyright (c) 2013 Andypops Media Limited
 *
 * @author Andy Mills
 *
 * This code is created and distributed under the GNU
 * General Public License (GPL)
 *
 */

define('FACEBOOK_USER', 2);
define('TWITTER_USER', 4);

class OAuth
{
	// Facebook App Details
	var $fb_app_id;
	var $fb_secret;
	
	// Twitter App Details
	var $tw_app_id;
	var $tw_secret;
	
	// User Information
	var $current_user;
	var $user_type;
	
	// Allowed App Permissions for Facebook
	private static $fb_extended_permissions = array( 'email', 'read_friendslist', 'read_insights', 'read_mailbox', 'read_requests', 'read_stream', 'xmpp_login', 'ads_management', 'create_event', 'manage_friendslist', 'manage_notifications', 'user_online_presence', 'friends_online_presence', 'publish_checkins', 'publish_actions', 'rsvp_event' );

	/**
	 * Generate a Unique Session Identifier
	 *
	 * @param string		$key			the name of the session variable.
	 *
	 * @return string
	 */
	private function construct_session_variable_name($key)
	{
		return 'oa_'.substr(md5($this->fb_app_id.$this->tw_app_id), 0, 20).'_'.$key;
	}
	
	/**
	 * Set the Session Variable 'state'
	 *
	 * @param string		$value			the value of the current state.
	 */
	private function setSessState($value)
	{
		// Create the Session Variable
		$session_variable_name = $this->construct_session_variable_name('state');
		$_SESSION[$session_variable_name] = $value;
	}
	
	/**
	 * Get the current 'state' Session Variable.
	 */
	private function getSessState()
	{
		// Create the Session Variable Name
		$session_variable_name = $this->construct_session_variable_name('state');
		return isset($_SESSION[$session_variable_name]) ? $_SESSION[$session_variable_name] : null;
	}
	
	/**
	 * Set the Session Variable 'access_token'
	 *
	 * @param string		$value			the value of the current access token.
	 */
	private function setSessAccessToken($value)
	{
		// Create the Session Variable
		$session_variable_name = $this->construct_session_variable_name('access_token');
		$_SESSION[$session_variable_name] = $value;
	}
	
	/**
	 * Get the current 'access_token' Session Variable.
	 */
	private function getSessAccessToken()
	{
		// Create the Session Variable Name
		$session_variable_name = $this->construct_session_variable_name('access_token');
		return isset($_SESSION[$session_variable_name]) ? $_SESSION[$session_variable_name] : null;
	}
	
	/**
	 * Set the Session Variable 'user_type'
	 *
	 * @param string		$value			the value of the current user_type.
	 */
	private function setSessUserType($value)
	{
		// Create the Session Variable
		$session_variable_name = $this->construct_session_variable_name('user_type');
		$_SESSION[$session_variable_name] = $value;
	}
	
	/**
	 * Get the current 'user_type' Session Variable.
	 */
	private function getSessUserType()
	{
		// Create the Session Variable Name
		$session_variable_name = $this->construct_session_variable_name('user_type');
		return isset($_SESSION[$session_variable_name]) ? $_SESSION[$session_variable_name] : null;
	}
	
	/**
	 * Get Data from the Facebook Graph API
	 *
	 * @param string		$query_string	the url query string to use (e.g. '&fields=id,name')
	 *
	 * @return object
	 */
	private function getFacebookGraphData($query_string = null)
	{
		// Get the Access Token
		$access_token = $this->getSessAccessToken();
		
		// Build the URL
		$fb_url = 'https://graph.facebook.com/me?access_token='.$access_token;
		
		// Add the Query String if it's there
		if (!is_null($query_string)) {
			$fb_url .= '&'.$query_string;
		}
		
		// Attempt to Fetch the Data
		$response = curl_file_get_contents($fb_url);
		
		// Decode the Result
		$content = json_decode($response);
		
		// Return the Object
		return $content;
	}
	
	// Default Constructor
	public function __construct()
	{
		// Get the Global Configuration
		global $config;
		
		// Set the Facebook App Details
		$this->fb_app_id = $config['oauth']['facebook']['app_id'];
		$this->fb_secret = $config['oauth']['facebook']['secret'];
		
		// Set the Twitter App Details
		$this->tw_app_id = $config['oauth']['twitter']['app_id'];
		$this->tw_secret = $config['oauth']['twitter']['secret'];
	}
	
	/**
	 * Facebook Login Function
	 *
	 * @param string		$current_url			Current Page URL (used for swapping code
	 *												for access token).
	 * @param string		$redirect_url			URL to redirect to after login.
	 * @param array			$extended_permissions	Array of Permissions for the App to
	 *												request.
	 */
	public function facebook_login($current_url, $redirect_url = null, $extended_permissions = null)
	{
		// Start the Session
		session_start();

		// Get the Access Token
		$access_token = $this->getSessAccessToken();

		// Before doing anything, check if we have an access token (therefore logged on)
		if (empty($access_token))
		{
			// Check if we have any Extended Permissions Specified
			if (!is_null($extended_permissions) && is_array($extended_permissions))
			{
				// Loop through the Specified Permissions...
				foreach($extended_permissions as $permission)
				{
					// Check it's validity
					if (in_array($permission, self::$fb_extended_permissions))
					{
						// Add it to the Scope String
						if (!isset($scope)) $scope = '&scope='.$permission;
						else $scope .= $permission;
					}
				}
			}
	
			// Get the Code URL Variable
			$code = $_GET['code'];
			
			// Check if we have a Code URL Variable
			if (empty($code))
			{
				// Generate a Unique ID for CSRF Protection
				$this->setSessState(md5(uniqid(rand(), TRUE)));
				
				// Get the State Variable
				$state = $this->getSessState();
				
				// Set the Dialog URL
				$dialog_url = "https://www.facebook.com/dialog/oauth?client_id=" 
							.$this->fb_app_id."&redirect_uri=".urlencode($current_url)."&state="
							.$state;
				
				// Add Scope, if we have it
				if (!empty($scope)) $dialog_url .= $scope;

				// Redirect
				header("Location: ".$dialog_url);
			}
	
			// Immediately swap our Code Variable for an Access Token
			if ($this->getSessState() === $_GET['state'])
			{
				// If we have no Redirect URL, use the Current URL
				if (is_null($redirect_url)) $redirect_url = $current_url;
				
				// Set the Token URL
				$token_url = "https://graph.facebook.com/oauth/access_token?"
							."client_id=".$this->fb_app_id."&redirect_uri=".urlencode($current_url)
							."&client_secret=".$this->fb_secret."&code=".$code;
				
				// Get the URL Response and put it into a Params Array
				$response = file_get_contents($token_url);
				$params = null;
				parse_str($response, $params);
				
				// Set the Access Token Session Variable
				$this->setSessAccessToken($params['access_token']);
				
				// Set the User Type
				$this->setSessUserType(FACEBOOK_USER);
				
				// Redirect the Page
				redirect($redirect_url);
			}
			else
			{
				// Report an Error
				report_error('OAuth Error', "The state variables did not match. You could be a victim of CSRF.");
			}
		}
	}
	
	/**
	 * Check whether a user is logged in.
	 *
	 * @return bool
	 */
	public function check_user_logged_in()
	{
		// Start the Session
		session_start();
		
		// Get the Access Token
		$access_token = $this->getSessAccessToken();
		
		// Get the User Type
		$user_type = $this->getSessUserType();
		
		// Check the Access Token Exists
		if (!empty($access_token))
		{
			// Check the appropriate details
			if ($user_type == FACEBOOK_USER)
			{
				// We'll just query the User ID and see if it works
				$response = $this->getFacebookGraphData('fields=id');
				
				// Check the Result for Errors
				if (!empty($response->error)) {
					return FALSE;
				} else {
					return TRUE;
				}
			}
		}
		else
		{
			// No Access Token, return FALSE
			return FALSE;
		}
	}
	
	/**
	 * Get the current user details.
	 *
	 * @param array			$fields					The fields to return (e.g. id, name, email_address)
	 *		  string
	 *
	 * @return object
	 */
	public function get_user($fields = null)
	{
		// Get the Access Token
		$access_token = $this->getSessAccessToken();
		
		// Get the User Type
		$user_type = $this->getSessUserType();
		
		// Check that the Access Token exists
		if (!empty($access_token))
		{
			// Check the Appropriate Details
			if ($user_type == FACEBOOK_USER)
			{
				// Check if we have any Fields Set
				if (!is_null($fields))
				{
					// Check if we have the fields as an array or a string
					if (is_array($fields))
					{
						// Implode the Array
						$query_string = 'fields='.implode(',', $fields);
					}
					else
					{
						// Just Tack the Fields String straight on
						$query_string = 'fields='.$fields;
					}
					
					// Return the Result
					return $this->getFacebookGraphData($query_string);
				}
				else
				{
					// Return the Result
					return $this->getFacebookGraphData();
				}
			}
			// ELSE TWITTER USER
		}
		// ELSE NO ACCESS TOKEN
	}
	
	/**
	 * Log the current user out.
	 *
	 * @param bool			$revoke_permissions		Revoke application permissions as well.
	 *
	 * @return bool
	 */
	public function logout($revoke_permissions = FALSE)
	{
		// Start the Session
		session_start();
		
		// Get the Access Token
		$access_token = $this->getSessAccessToken();
		
		// Get the User Type
		$user_type = $this->getSessUserType();
		
		// Unset the State Session
		$sess_name = $this->construct_session_variable_name('state');
		unset($_SESSION[$sess_name]);
		
		// Check the Access Token Exists
		if (!empty($access_token))
		{
			// Check which User Type we are
			if ($user_type == FACEBOOK_USER)
			{
				// Check if we're doing a full revoke
				if ($revoke_permissions)
				{
					// Set the Graph URL
					$graph_url = "https://graph.facebook.com/me/permissions?method=delete&access_token=".$access_token;
					
					// Get the Contents
					$result = json_decode(file_get_contents($graph_url));
					
					if ($result)
					{
						// Get the Access Token Session Variable Name
						$sess_name = $this->construct_session_variable_name('access_token');
						
						// Unset the Session Variable
						unset($_SESSION[$sess_name]);
						
						// Check that it's Unset
						return (!isset($_SESSION[$sess_name]));
					}
					else return FALSE;
				}
				else
				{
					// Get the Access Token Session Variable Name
					$sess_name = $this->construct_session_variable_name('access_token');
					
					// Unset the Session Variable
					unset($_SESSION[$sess_name]);
					
					// Check that it's Unset
					return (!isset($_SESSION[$sess_name]));
				}
			}
		}
		
		// Return True
		return TRUE;
	}
}
?>