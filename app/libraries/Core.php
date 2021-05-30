<?php
/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2012 Olagoke Okebukola <gokuzimaki@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category   Framework
 * @package    Homonculus MVC
 * @subpackage Core Library
 * @author     Olagoke Okebukola <gokuzimaki@gmail.com>
 * @copyright  2012 Olagoke Okebukola.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    1.0
 * @link       https://dreambench.io
 */

/**
 * Retrieves Url and loads controller with method and parameters from it.
 *
 * FORMAT - /controller/method/params
 * 			(
 * 				Controller - What app sector to access e.g Pages, Users etc
 * 							 these are class files found in the ROOT/app/controller
 * 							 folder
 * 				
 * 				Method - What operation or method to access in the application sector 
 * 						 controller, e.g create update retrieval and display or delete ops
 * 						 
 * 				Parameters - url arguments to feed the controller method or make available
 * 							 in the application
 *
 * 				Qualified Url Example
 * 				
 * 				https://mysite.com/about
 *
 * 				https://mysite.com/users/edit/1 controller = Users , method = edit, params = 1
 *     			
 * 			)
 * 			
 */
class Core{
	protected $currentController = 'Pages';

	protected $currentMethod = 'index';

	protected $currentParams = [];

	protected $currentUrl = '';

	public function __construct(){


		$url = $this->getUrl();

		///////////////////////////
		// Step 1 Get Controller //
		///////////////////////////

		// get the target controller for the current request
		$controller = ucwords($url[0] ?? $this->currentController);

		// define the path for the controller. This path is 
		// relative to the ROOT/public folder and should be
		// defined as such
		$controlPath  = "../app/controllers/$controller.php" ;

		// echo $controlPath;

		if(file_exists($controlPath)){
			$this->currentController = $controller;
			if(isset($url[0])){
				unset($url[0]);
			}
		}else{
			$controlPath  = "../app/controllers/{$this->currentController}.php";
		}

		// require the current controller
		require_once $controlPath;

		// instantiate controller
		$this->currentController = new $this->currentController;

		// uncomment this to debug and see what the 
		// url parameters are directly
		// print_r($this->getUrl());

		///////////////////////
		// Step 2 Get Method //
		///////////////////////


		// check for method value in 1 index of url array
		if(isset($url[1])){

			if(method_exists($this->currentController, $url[1])){
				// set the current method to the url 1 index value
				$this->currentMethod = $url[1];

				// unset url 1 index value
				unset($url[1]);
			}
		}


		///////////////////////////
		// Step 3 Set Parameters //
		///////////////////////////
		$this->currentParams = $url ? array_values($url) : [];


		///////////////////////////////////
		// Step 4 Call Controller Method //
		///////////////////////////////////
		call_user_func_array([$this->currentController, $this->currentMethod], $this->currentParams);
	}

	public function getUrl(){

		$url = $_GET['url'] ?? "";

		if($url !== ""){

			// remove any trailing slashes in the url
			$url = rtrim($url,"/");

			// set the current url parmeter data to the currentUrl
			// property
			$this->currentUrl = $url;

			$url = filter_var($url, FILTER_SANITIZE_URL);

			$url = explode("/",$url);

		}
		return $url;
	}
}
?>