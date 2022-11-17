<?php
	// SYSTEM GLOBALS
	define('PROJECT_NAME', 'ProAutismoApp');

	define('WEBSERVER', 0); // 0 = Local, 1 = Shared Hosting
	
	// DATABASE CONSTANTS #####################################################
	define('DB_CONTROLLER', 'mysql');
	define('DB_HOST', 'localhost'); // Compatibility
	define('DB_PORT', '3306');

	if ( WEBSERVER == 1 ) {
		define('DB_NAME', 'u881531570_ProAutismoDB');
		define('DB_USER', 'u881531570_ProAutismo_USER');
		define('DB_PASSWORD', 'AdmProAutismo2022!');
	} else {
		define('DB_NAME', 'ProAutismoDB');
		define('DB_USER', 'root');
		//define('DB_PASSWORD', ''); // XAMPP Test Server
		define('DB_PASSWORD', 'root'); // MAMP Test Server
	};
	// ########################################################################

	define('DEBUG_MODE', true);

	// APP GLOBALS
	date_default_timezone_set('America/Tijuana');

	// APP SETTINGS
	define('ITEMS_PER_PAGE', 30);
?>