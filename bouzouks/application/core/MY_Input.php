<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Input extends CI_Input {
	public function isPost()
	{
		return ($_SERVER['REQUEST_METHOD'] === 'POST');
	}
}