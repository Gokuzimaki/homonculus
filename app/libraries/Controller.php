<?php 
	/**
	 * Base Controller Class for Homonculus MVC.
	 * 
	 * Every controller must have a method to instantiate models and views.
	 *
	 * All controllers must extend the base controller class.
	 */
	class Controller {

		public function model($model){

			if(file_exists("../app/models/$model.php")){
				// require the requested model for the current controller
				require_once "../app/models/$model.php";

				// instantiate the model
				return new $model();
			}else{
				die("Model '<b>$model</b>' was not found in the '<b>app/models</b>' directory.");

			}

		}

		public function view($view, $data = []){

			$viewPath = "../app/views/$view.php";

			if(file_exists($viewPath)){
				require_once $viewPath;
				
			} else {

				// redirect to 'view not found' error view display
				

				// or kill the app
				die("Could not find view at '<b>$viewPath</b>'");
			}
		}
	}