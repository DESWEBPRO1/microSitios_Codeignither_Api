<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	//'dsn'	=> 'pgsql:host=localhost;port=5432;dbname=bd_micrositios',
	'dsn'	=> 'pgsql:host=localhost;port=5432;dbname=bd_micrositios',
	'hostname' => 'localhost',
	'username' => 'postgres',
	'password' => 'Apps2K14W3b',
	'database' => '',
	'dbdriver' => 'pdo',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
