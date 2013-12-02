<?php
/**
 * Redirect to a new location.
 *
 * @param string		$url		URL to redirect to.
 */
function redirect($url)
{
	// Set the Header
	header("Location: $url");
}

/**
 * Get the Root URL of the Website.
 *
 * @param string		$extra		Sub URL to add to the end of the Root URL
 *									(e.g. 'path/to/page.html')
 *
 * @return string
 */
function root_url($extra = '')
{
	// Get the Domain Name
	$Domain = $_SERVER['HTTP_HOST'];
	
	// Get the Protocol (HTTP / HTTPS)
	list($Protocol) = explode('/',$_SERVER['SERVER_PROTOCOL']);
	$Protocol = strtolower($Protocol);

	// Get the Path
	$Path = str_replace( basename($_SERVER['SCRIPT_FILENAME']), '', $_SERVER['PHP_SELF'] );

	$url = $Protocol.'://'.$Domain.$Path;

	return $url.$extra;
}

/**
 * Get any additional URL Parameters.
 *
 * @return array
 */
function get_url_parameters()
{
	// Get any additional parameters after the URL rewrite
	$url_params = preg_replace('([a-zA-Z0-9-_\/\.]+)', '', $_SERVER['REQUEST_URI'], 1);
	$url_params = substr($url_params, 1, strlen($url_params) - 1);

	// Create an Array of Key-Value Pairs
	$key_value_pairs = explode('&', $url_params);

	// Create a Returnable Array
	$url_array = array();
	
	// Loop through and Add the Values
	if (!empty($key_value_pairs))
	{
		foreach($key_value_pairs as $key_value_string)
		{
			// Add the Key and Value to the Array
			if (!empty($key_value_string))
			{
				$key_value_pair = explode('=', $key_value_string);
				$url_array[$key_value_pair[0]] = $key_value_pair[1];
			}
		}
	}
	// Return the Result
	return $url_array;
}

/**
 *  Returns the file / URL contents using a CURL post.
 *
 * @param string		$url			URL of the file to get content from.
 */
function curl_file_get_contents($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	$contents = curl_exec($ch);
	curl_close($ch);
	return (!empty($contents)) ? $contents : FALSE;
}
?>