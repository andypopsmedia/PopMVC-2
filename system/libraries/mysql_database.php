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
class MySQL_Database
{
	// Class Scope Variables
	var $conn;
	var $current_sql_string;
	var $last_query;
	
	// Individual Query Components
	var $s_select;
	var $s_from;
	var $s_where;
	var $s_order_by;
	var $s_limit;
	var $a_joins = array();
	
	// Component Flags
	var $first_order_by = TRUE;
	var $return_single_row = FALSE;

	// Default Constructor (connects to DB Server)
	public function __construct()
	{
		// Get the Global Configuration File
		global $config;
		
		// Connect to the Database Server
		$this->conn = mysql_connect($config['mysql']['host'], $config['mysql']['user'], $config['mysql']['pass'])
			or die(report_error('MySQL Connection Failed', 'Could not connect to MySQL host. Please check the details in the config file.', mysql_error()));
		
		// Select the Database
		mysql_select_db($config['mysql']['data'], $this->conn)
			or die(report_error('No Database', 'Could not find the database specified. Please check the details in the config file.', mysql_error()));
	}
	
	/**
	 * Builds the select component of the SQL String
	 *
	 * Builds the SELECT component of the SQL String from either a
	 * string or an array containing the column name(s).
	 *
	 * @param string	$column_names	String containing the name(s) of the column(s)
	 *									required for the select operation.
	 * @param array		$column_names	Array containing the name(s) of the column(s)
	 *									required for the select operation.
	 */
	public function select($column_names = '*')
	{
		// Begin the SELECT string
		$this->s_select = 'SELECT ';
		
		// Determine whether it's an array of columns or a string
		if (is_array($column_names))
		{
			// Join the Column Names by a Comma (,)
			$column_string = implode(', ', $column_names);
			
			// Add it to the Select Component
			$this->s_select .= $column_string;
		}
		else
		{
			// Just join the String onto the Select Component
			$this->s_select .= $column_names;
		}
	}
	
	/**
	 * Builds the from component of the SQL String
	 *
	 * Builds the FROM component of the SQL String from a given
	 * table name.
	 *
	 * @param string	$table_name		String containing the name of the table
	 *									required for the select operation.
	 */
	public function from($table_name)
	{
		// Begin the FROM string
		$this->s_from = ' FROM '.$table_name;
	}
	
	/**
	 * Adds a JOIN to the SQL String.
	 *
	 * @param string	$join_type		String containing the join time (e.g. 'left inner')
	 * @param string	$table_name		String containing the name of the table to join.
	 * @param string	$clause			String containing the join clause (e.g. 'tbl1.id = tbl2.tbl1_id')
	 */
	public function join($join_type, $table_name, $clause)
	{
		// Build the JOIN String
		$join = $join_type.' JOIN '.$table_name.' ON '.$clause;
		
		// Add the Join to the Array
		$this->a_joins[] = $join;
	}
	
	/**
	 * Builds the initial part of the where component of the SQL String
	 *
	 * Builds the initial part of the WHERE compontent of the SQL String
	 * from a given clause.
	 *
	 * @param string	$clause			String containing the WHERE clause
	 *									for the SELECT / UPDATE statement.
	 */
	public function where($clause)
	{
		// Begin the WHERE string
		$this->s_where = ' WHERE '.$clause;
	}
	
	/**
	 * Adds an additional clause to the where component of the SQL String
	 *
	 * Add an additional clause to the WHERE component of the SQL String
	 * joined by an AND (e.g. WHERE x = 1 AND y = 2)
	 *
	 * @param string	$clause		String containing the additional WHERE
	 *								clause for the SELECT / UPDATE statement.
	 */
	public function and_where($clause)
	{
		// Add onto the WHERE string
		$this->s_where .= ' AND '.$clause;
	}
	
	/**
	 * Adds an additional clause to the where component of the SQL String
	 *
	 * Add an additional clause to the WHERE component of the SQL String
	 * joined by an OR (e.g. WHERE x = 1 OR x = 2)
	 *
	 * @param string	$clause		String containing the additional WHERE
	 *								clause for the SELECT / UPDATE statement.
	 */
	public function or_where($clause)
	{
		// Add onto the WHERE string
		$this->s_where .= ' OR '.$clause;
	}
	
	/**
	 * Adds an order by component to the SQL String
	 *
	 * Adds an ORDER BY component to the SQL String. Note that this function
	 * can be called multiple times.
	 *
	 * @param string	$column_name	String containing the column name to order by.
	 * @param string	$direction		String containing the direction to order by.
	 */
	public function order_by($column_name, $direction = 'ASC')
	{
		// Check if it's the first Order By Clause
		if ($this->first_order_by)
		{
			// Begin the ORDER BY component
			$this->s_order_by = ' ORDER BY '.$column_name.' '.$direction;
		}
		else
		{
			// Add on the new ORDER BY component
			$this->s_order_by .= ', '.$column_name.' '.$direction;
		}
	}
	
	/**
	 * Adds a limit component to the SQL String
	 *
	 * @param int		$upper_limit	Number of records to return.
	 * @param int		$starting_point	Number of record to start returning from.
	 */
	public function limit($upper_limit, $starting_point = null)
	{
		// Check that the Limits are Numeric
		if (is_numeric($upper_limit) && (is_numeric($starting_point) || is_null($starting_point)))
		{
			// Begin the LIMIT component
			$this->s_limit = ' LIMIT ';
			
			// If we have a Starting Point, start with that
			if (!is_null($starting_point))
			{
				// Build the First Half of the LIMIT
				$this->s_limit .= $starting_point;
				$this->s_limit .= ', ';
			}
			
			// Add the Upper Limit
			$this->s_limit .= $upper_limit;
			
			// If the Upper Limit is 1, we return a single row
			if ($upper_limit == 1) {
				$this->return_single_row = TRUE;
			}
		}
	}
	
	/**
	 * Executes the current SELECT statement and returns the results.
	 *
	 * Executes the current SELECT statement and returns the results as an
	 * object.
	 *
	 * @return object
	 */
	public function get_results()
	{
		// First, check that we have the beginning of the SELECT statement
		if (!empty($this->s_select) && !empty($this->s_from))
		{
			// Begin the SQL String
			$this->current_sql_string = $this->s_select.$this->s_from;

			// Check if we have any JOINs
			if (!empty($this->a_joins))
			{
				// Add the JOINs to the SQL String
				$joins = ' '.implode(' ', $this->a_joins);
				$this->current_sql_string .= $joins;
			}
			
			// Check if we have a WHERE component
			if (!empty($this->s_where))
			{
				// Add the WHERE to the SQL String
				$this->current_sql_string .= $this->s_where;
			}
			
			// Check if we have an ORDER BY component
			if (!empty($this->s_order_by))
			{
				// Add the ORDER BY to the SQL String
				$this->current_sql_string .= $this->s_order_by;
			}
			
			// OK, we've got all our components, now it's time to EXECUTE
			$result = mysql_query($this->current_sql_string, $this->conn) or die(report_error('MySQL Error', mysql_error(), $this->current_sql_string));
			$num_rows = mysql_num_rows($result);
			$num_fields = mysql_num_fields($result);
			
			// Get the Field Types, so we don't have automatic string casting
			$field_array = array();
			for ($i = 0; $i < $num_fields; $i++) {
				$field_array[mysql_field_name($result, $i)] = mysql_field_type($result, $i);
			}
			
			// Check that we have a Result
			if ($num_rows > 0)
			{			
				// Create a Result Array
				$result_array = array();
				
				// Check if we're returning multiple rows
				if ($this->return_single_row)
				{
					// Get the Row Returned
					$row = mysql_fetch_assoc($result);
					
					foreach($row as $key=>&$value)
					{
						switch($field_array[$key])
						{
							case "int":
								$value = (int) $value;
								break;
							case 'float':
								$value = (float) $value;
								break;
							default:
								$value = (string) stripslashes($value);
								break;
						}
					}
					
					// Just get the First Row returned
					$result_array = $row;
				}
				else
				{
					// Loop through the fetched assoc array
					while($row = mysql_fetch_assoc($result))
					{
						foreach($row as $key=>&$value)
						{
							switch($field_array[$key])
							{
								case "int":
									$value = (int) $value;
									break;
								case 'float':
									$value = (float) $value;
									break;
								default:
									$value = (string) stripslashes($value);
									break;
							}
						}
						
						// Push the Returned Row into the Result Array
						$result_array[] = (object) $row;
					}
				}
				
				// Set the Last Query
				$this->last_query = $this->current_sql_string;

				// Clear ALL the SQL Parts
				$this->current_sql_string
					= $this->s_select
					= $this->s_from
					= $this->s_where
					= $this->s_order_by
					= $this->s_limit = '';

				// Blank the Join Array
				$this->a_joins = array();
				
				// Set to Return Multiple Rows again
				$this->return_single_row = FALSE;

				// Create the Result Object
				$result_obj =  (object) $result_array;
				return $result_obj;
			}
			else
			{
				// Set the Last Query
				$this->last_query = $this->current_sql_string;
				
				// Clear ALL the SQL Parts
				$this->current_sql_string
					= $this->s_select
					= $this->s_from
					= $this->s_where
					= $this->s_order_by
					= $this->s_limit = '';

				// Blank the Join Array
				$this->a_joins = array();
				
				// Set to Return Multiple Rows again
				$this->return_single_row = FALSE;
				return null;
			}
		}
		else return null;
	}
	
	/**
	 * Counts the number of results for a particular table. Where clause may be pre-specified.
	 *
	 * @param string $table_name			Name of the table to count from.
	 *
	 * @return int
	 */
	public function count_all_results($table_name)
	{
		// Build the Update Query
		$this->current_sql_string = 'SELECT COUNT(*) as `count` FROM '.$table_name;
		
		// Check if we have a WHERE component
		if (!empty($this->s_where))
		{
			// Add the WHERE to the SQL String
			$this->current_sql_string .= ' '.$this->s_where;
			
			// Clear the String
			$this->s_where = '';
		}
		
		// OK, we've got all our components, now it's time to EXECUTE
		$result = mysql_query($this->current_sql_string, $this->conn) or die(report_error('MySQL Error', mysql_error(), $this->current_sql_string));
		
		// Get the Result Array
		$row = mysql_fetch_assoc($result);
		
		// Return the Result
		return $row['count'];
	}
	
	/**
	 * Performs an insert on the database.
	 *
	 * Performs an INSERT on the specified table with the specified data.
	 *
	 * @param string		$table_name		Name of the table to insert the row into.
	 * @param array			$data			An assoc array of the values to insert.
	 *										Create as array( colname => value )
	 */
	public function insert($table_name, $data, $return_id = FALSE)
	{
		// Build the Insert Query
		$this->current_sql_string = 'INSERT INTO '.$table_name;
		
		// Arrays for the Fields and Values
		$field_array = array();
		$value_array = array();
		
		// Build the Field and Value Strings
		foreach($data as $field=>$value)
		{
			// Add the Field and Value to their Arrays
			$field_array[] = $field;
			$value_array[] = "'".mysql_real_escape_string($value)."'";
		}
		
		// Add the Fields and Values
		$this->current_sql_string .= '('.implode(', ', $field_array).') VALUES('.implode(', ', $value_array).')';
		
		// OK, we've got all our components, now it's time to EXECUTE
		$result = mysql_query($this->current_sql_string, $this->conn) or die(report_error('MySQL Error', mysql_error(), $this->current_sql_string));
		
		// Return an ID if specified
		if ($return_id) {
			return mysql_insert_id($this->conn);
		}
		
		// Return the Result
		return $result;
	}
	
	/**
	 * Performs an update on the database.
	 *
	 * Performs an UPDATE on the specified table with the specified data.
	 *
	 * @param string		$table_name		Name of the table to update.
	 * @param array			$data			An assoc array of the values to modify.
	 *										Create as array( colname => value )
	 */
	public function update($table_name, $data)
	{
		// Build the Update Query
		$this->current_sql_string = 'UPDATE '.$table_name.' SET';
		
		// Flag for Commas
		$is_first = TRUE;
		
		// Add the Values to Change
		foreach($data as $field=>$value)
		{
			// Add the Comma if necessary
			if (!$is_first) $this->current_sql_string .= ", ";
			
			// Append to SQL String
			$this->current_sql_string .= "`".$field."` = '".$value."'";
			
			// Set the Flag
			$is_first = FALSE;
		}
		
		// Check if we have a WHERE component
		if (!empty($this->s_where))
		{
			// Add the WHERE to the SQL String
			$this->current_sql_string .= ' '.$this->s_where;
			
			// Clear the String
			$this->s_where = '';
		}
		
		// OK, we've got all our components, now it's time to EXECUTE
		$result = mysql_query($this->current_sql_string, $this->conn) or die(report_error('MySQL Error', mysql_error(), $this->current_sql_string));
		
		// Return the Result
		return $result;
	}
	
	/**
	 * Performs a delete on the database.
	 *
	 * Performs a DELETE on the specified table. WARNING: without a where clause
	 * specified BEFORE the delete function is called, ALL rows for the table will
	 * be deleted.
	 *
	 * @param string		$table_name		Name of the table to delete from.
	 */
	public function delete($table_name)
	{
		// Build the Update Query
		$this->current_sql_string = 'DELETE FROM '.$table_name;
		
		// Check if we have a WHERE component
		if (!empty($this->s_where))
		{
			// Add the WHERE to the SQL String
			$this->current_sql_string .= ' '.$this->s_where;
			
			// Clear the String
			$this->s_where = '';
		}
		
		// OK, we've got all our components, now it's time to EXECUTE
		$result = mysql_query($this->current_sql_string, $this->conn) or die(report_error('MySQL Error', mysql_error(), $this->current_sql_string));
		
		// Return the Result
		return $result;
	}
}
?>