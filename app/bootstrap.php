<?php
	// Load Application Configuration
	require_once 'config/config.php';

	// Auto Load Libraries
	spl_autoload_register(function($className){
		require_once "libraries/$className.php";
	});
?>