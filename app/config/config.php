<?php 


/**
 * Import required libraries, then define system values and variables for 
 * application operation.
 * 
 */

/////////////////////////////////////////////////////////
// Import required third party libraries or autoloads  //
/////////////////////////////////////////////////////////


// Include composer autoload
require_once '../modules/vendor/autoload.php';

// Include other third party libraries
require_once '../modules/paginator.class.php';
require_once "../modules/gifresizer.class.php";
require_once '../modules/php_image_magician.php';


//////////////////////////////////////////////
// Initialise Private Environment variables //
//////////////////////////////////////////////
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

///////////////////////////////////////////////
// Initialise system variables and constants //
///////////////////////////////////////////////

// stores the folder name for the current project
$host_appfname = "homonculusmvc";

// customr filemanager name for blog upload section
$host_name = "homonculusmvc";

// application display name
$host_dapp_name = "RevvTech";

// default host address
$host_target_addr = "";

// default email address for the current project
$host_email_addr = "no-reply@revv.dreambench.io";

$host_website_url = "revv.dreambench.io";

// setup smtp default values for sending blog emails out
// via the server provided details
$host_smtp = "relay-hosting.secureserver.net";
$host_smtp_email = "no-reply@revv.dreambench.io";
$host_smtp_username = "no-reply@revv.dreambench.io";
$host_smtp_pwrd = "noreply";
$host_smtp_port = 80;
$host_smtp_auth = true;
$host_smtp_ssl = 'tls';


// defines which push notification api should be used for the platform
$host_push_apis = [ "onesignal", "webpushr"/*, "pushengage"*/];

$host_push_api = $host_push_apis[0] ?? "";

// store the default url for website root on the web
$host_directaddr = "https://revv.dreambench.io/";

// local manifest file for pwa
$host_manifest = "manifestlocal.json";

// variable for holding site app name, this name is to be used in session variables
// specific to the app being built
$host_rendername = "homonculusmvc";

//variable for holding default name of the site owner
$host_title_name = "Homonculus MVC";

// store the empty date format for db tables date row data
$host_empty_date = "0000-00-00 00:00:00";

// store the default date format for db tables date row data
$host_empty_start_date = "1970-01-01 01:00:00";

if (isset($_SERVER) && isset($_SERVER['HTTPS'])) {
	$host_cur_page = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
	$host_target_addr = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}/";
}

$test_env = getenv('HTTP_HOST');
if (!is_null($test_env) && $host_target_addr == "") {

	$host_cur_page = 'http' . (getenv('HTTPS') == "on" ? 's' : '') . '://' . "" . getenv('HTTP_HOST') . "" . getenv('REQUEST_URI') . "";

	$host_target_addr = 'http' . (getenv('HTTPS') == "on" ? 's' : '') . '://' . "" . getenv('HTTP_HOST') . "/";

}


///////////////////////////////////////////////
// Hardcoded Application and Company Details //
///////////////////////////////////////////////
$host_website_name = "Homonculus MVC";
$host_web_n = str_replace(" ", "", $host_website_name);
$host_meta_appname = "Homonculus MVC";
$host_companyname = "DreamBench Technologies";
$host_company_name = $host_companyname;
$host_company_website = "http://dreambench.io/";
$host_company_address = "5B, Sateru Lane, Ocean Bay Estates, Lekki, Lagos.";
$host_company_phone = "+(234) 808 437 3591";



////////////////////////////////////////////////////////////////
// Update variables depending on detected environment via url //
////////////////////////////////////////////////////////////////

if (strpos($host_target_addr, "localhost/")) {
	// for local server
	$host_test = "default";
	$host_addr = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . "://localhost/$host_appfname/";
	$host_faddr = $host_addr;
	// setup headers for delivering any content in a local environment without
	// browser hulabaloo
	header("Content-Security-Policy: default-src *; style-src * 'self' 'unsafe-inline' 'unsafe-eval' data:; style-src-elem * 'self' 'unsafe-inline' 'unsafe-eval' data:; script-src * 'self' 'unsafe-inline' 'unsafe-eval'; img-src * data: blob:; font-src * data:;");

} else if (strpos($host_target_addr, "127.0.0.1/")) {
	// for local server
	$host_test = "default";
	$host_addr = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . "://127.0.0.1/$host_appfname/";
	$host_faddr = $host_addr;

} else if (strpos($host_target_addr, "10.0.2.2/")) {
	// for local server, usually to support sdk api testing for mobile on android emulators
	$host_test = "default";
	$host_addr = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . "://10.0.2.2/$host_appfname/";
	$host_faddr = $host_addr;

} else if (strpos($host_target_addr, "localhost.")) {

	// for local server but with custom host name
	$host_test = "default";
	$host_addr = "https://localhost/$host_appfname/";
	$host_faddr = $host_addr;

} else if (strpos($host_target_addr, "ngrok.io")) {
	// for tunneled local server via ngrok 
	$host_test = "ngrok2";
	$host_addr = $host_target_addr !== "http://" && $host_target_addr !== "https://" ?
	$host_target_addr . "$host_appfname/" : "$host_directaddr";
	// force https where possible
	$host_target_addr = str_replace("http://", "https://", $host_target_addr);
	$host_addr = $host_target_addr . "$host_appfname/";
	$host_faddr = $host_addr;

	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: *");
	header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
	header("Vary: Origin");

	header("Content-Security-Policy: default-src *; style-src * 'self' 'unsafe-inline' 'unsafe-eval'; script-src * 'self' 'unsafe-inline' 'unsafe-eval'; img-src * data: blob:; font-src * data:;");
	$host_env = "online";
	$host_ngrok = true;

	// specify the start-url value for pwa deployment
	$host_starturl = "/$host_appfname/?utm_source=app";
} else {

	// This section only runs if non of the others above are executed, this in
	// turn means that this is  most likely a production environment


	// Set Headers and cache control
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: *");
	header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
	header("Vary: Origin");
	header("Cache-Control: max-age=31536000");
	header("Accept-Encoding: gzip, compress, br");

	$host_env = "online";
	$host_test = "";

	$host_addr = $_SERVER['HTTP_HOST'] !== "" && isset($_SERVER['HTTP_HOST']) ? $host_target_addr : "$host_directaddr";

	$host_faddr = $host_addr;

}




//////////////////////
// Define Constants //
//////////////////////

// Applciation folder root
define('APPROOT',dirname(dirname(__FILE__)));

// platform folder url root
define('URLROOT',$host_addr);

// platform files section url (if files are hosted on different environment)
define('FILEURLROOT',$host_faddr);

// Define application or site name constant
define('SITENAME',$host_website_name);

// define composer vendor root
define('VENDORROOT',APPROOT."modules/vendor/");
