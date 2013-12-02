<?php
function get_browser_info()
{
	// Get the User Agent String
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	
	$data = array();
	/*
	Mozilla/[version] ([system and browser information]) [platform] ([platform details]) [extensions]
	*/
	
	// Trim the Mozilla Bit
	$user_agent = preg_replace('/Mozilla\/(\d)(\.?)(\d?)/', '', $user_agent);
	
	// Get the 
	echo $user_agent;
	/*
	$preg = '/Mozilla\/(\d)(\s)/';
	// Check that we have a User Agent
	if (!empty($user_agent))
	{
		// Check for Opera
		if (preg_match($preg, $user_agent, $data))
		{
			print_r($data);
		}
	}
	else return NULL;*/
}