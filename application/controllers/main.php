<?php
class Main extends Controller
{
	public function __construct()
	{
	}

	public function index()
	{
		// Load the Main View
		$this->load_view('hello_world', $data);
	}
}
?>