<?php 

	class Pages extends Controller{

		public function __construct(){
			// echo 'Pages Loaded';
		}
		

		public function index(){
			$data = [
				"title" => "Hello World",
			];


			$this->view('pages/index',$data);
		}

		public function about(){
			
			$this->view('pages/about');

		}
	}


?>