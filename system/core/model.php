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

class Model
{
	// MySQL Database Instance
	var $db;
	
	// Base Constructor
	public function __construct()
	{
		// Load the MySQL Library
		$mysql_filename = getcwd().'/system/libraries/mysql_database.php';
		
		// Check that we haven't already loaded the DB Class
		if (!class_exists('MySQL_Database'))
		{
			// Load the File
			include $mysql_filename;
		}
		
		// Create an Instance of it
		$this->db = new MySQL_Database();
	}
}
?>