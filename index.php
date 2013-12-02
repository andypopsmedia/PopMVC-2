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

// Include the Config File
include 'config.php';

// Loop through and Include the Core Helper Files
foreach($config['autoload']['helpers'] as $helper)
{
	// Add the Full Filepath
	$helper_file = getcwd().'/system/helpers/'.$helper.'.php';
	
	// Check that the File Exists
	if (file_exists($helper_file))
	{
		// Load the Helper File
		include $helper_file;
	}
}

// Create a new Array of GET Variables
$_GET = array_merge($_GET, get_url_parameters());

// Loop through and Include the Core Application Files
foreach(glob("system/core/*.php") as $core_file)
{
	// Load the Core File
	include $core_file;
}

// Get the Controller Name
$controller_name = !empty($_GET['c']) ? $_GET['c'] : $config['default_controller'];

// Build the Controller Filename
$controller_file = getcwd().'/application/controllers/'.$controller_name.'.php';

// Check whether the File Exists
if (file_exists($controller_file))
{
	// Include the Controller Definition File
	include $controller_file;

	// Create a new instance of the Controller
	$controller = new $controller_name();

	// Call the Autoload Function
	$controller->autoload();

	// Get the Method Name
	$view = !empty($_GET['v']) ? $_GET['v'] : $config['default_view'];
	
	// Check whether the View Method Exists
	if (method_exists($controller, $view))
	{
		// Get any Parameters
		if (!empty($_GET['p']))
		{
			// Split the Parameters
			$params = explode('/', $_GET['p']);
			
			// Call the View Method with the Parameters
			call_user_func_array(array($controller, $view), $params);
		}
		else
		{
			// Just call the View Function
			$controller->$view();
		}
	}
	elseif ($controller->index_callable)
	{
		// Check if we have Extra Parameters
		if (!empty($_GET['p']))
		{
			// Get the View as a Parameter
			$view_param = array( $view );
			
			// Split the Parameters
			$extra_params = explode('/', $_GET['p']);
			
			// Merge
			$params = array_merge($view_param, $extra_params);
			
			// Call Index with Parameters
			$controller->index($params);
		}
		else
		{
			// Call Index
			$controller->index($view);
		}
	}
	else
	{
		// File doesn't exist, report an error
		report_error('Invalid View Name', 'The method <strong>'.$view_name.'</strong> does not exist.');
	}
}
else
{
	// File doesn't exist, report an error
	report_error('Invalid Filename', 'The file <strong>'.$controller_file.'</strong> does not exist.');
}
?>