<?php
// Set the Application Mode
$app_mode = 'DEBUG';

// Setup the Default Controller and View
$config['default_controller'] = 'main';
$config['default_view'] = 'index';

// Setup the Autoloads
$config['autoload']['helpers'] = array( 'url' );
$config['autoload']['models'] = array();
$config['autoload']['libraries'] = array( 'oauth' );

// Setup the Cache Path
$config['cache_path'] = 'cache/';

// Set a Hash Key for Session Variables
$config['sess_hash'] = 'p0pmVc2';

// Upload Path
$config['upload_path'] = getcwd().'/';

// Setup the Facebook OAuth Details
$config['oauth']['facebook']['app_id'] = '';
$config['oauth']['facebook']['secret'] = '';

// Setup the Twitter OAuth Details
$config['oauth']['twitter']['app_id'] = '';
$config['oauth']['twitter']['secret'] = '';

// Lay out some Configuration Details
if ($app_mode == 'DEBUG')
{
	error_reporting('E_ALL');
	// Setup the MySQL Database
	$config['mysql']['host'] = 'localhost';
	$config['mysql']['user'] = 'root';
	$config['mysql']['pass'] = 'root';
	$config['mysql']['data'] = 'popvc';
}
elseif ($app_mode == 'RELEASE')
{
	// Setup the MySQL Database
	$config['mysql']['host'] = 'localhost';
	$config['mysql']['user'] = 'root';
	$config['mysql']['pass'] = 'root';
	$config['mysql']['data'] = 'popvc';
}
?>