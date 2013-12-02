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

function report_error($title, $message, $code = null)
{
	// Get the Config Variable
	global $app_mode;
	
	// If we are in Debug Mode
	if ($app_mode == 'DEBUG')
	{
		// Build a Simple Error Message Box
		$error_box = '<div style="font-size: 12px; font-family: Helvetica, sans-serif; padding: 10px; border-radius: 4px; border: 1px solid #CCC; background-color: #EEE; margin: 0px auto; width: 600px">';
		$error_box .= '<h4>'.$title.'</h4><p>'.$message.'</p>';
		
		// Check if we have any associated code attached
		if (!is_null($code)) {
			$error_box .= '<pre style="font-size: 10px; font-family: Consolas, monospace">'.$code.'</pre>';
		}
		
		$error_box .= '</div>';
		
		// Print the Message and Kill the Application
		exit($error_box);
	}
	else
	{
		// Add to the Error Log
		$error_message = date('d/m/Y H:i:s')."\n".$title."\n".$message;
		if (!is_null($code)) $error_message .= "\n".$code;
		$error_message .= "\n\n\n";
		
		// Append to the Data Logger
		file_put_contents(getcwd().'/error_log.txt', $error_message, FILE_APPEND);
		
		// Exit the Application
		exit("Application Error");
	}
}

function return_error($title, $message, $code = null)
{
	// Build a Simple Error Message Box
	$error_box = '<div style="font-size: 12px; font-family: Helvetica, sans-serif; padding: 10px; border-radius: 4px; border: 1px solid #CCC; background-color: #EEE; margin: 0px auto; width: 600px">';
	$error_box .= '<h4>'.$title.'</h4><p>'.$message.'</p>';
	
	// Check if we have any associated code attached
	if (!is_null($code)) {
		$error_box .= '<pre style="font-size: 10px; font-family: Consolas, monospace">'.$code.'</pre>';
	}
	
	$error_box .= '</div>';
	
	// Print the Message and Kill the Application
	return $error_box;
}
?>