<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation
{
	protected $CI;

	public function __construct(){
		parent::__construct();
		$this->CI =& get_instance();
	}

	public function greater_than_or_equal($str, $min)
	{
		if ( ! is_numeric($str))
		{
			return FALSE;
		}
		return $str >= $min;
	}

	public function less_than_or_equal($str, $max)
	{
		if ( ! is_numeric($str))
		{
			return FALSE;
		}
		return $str <= $max;
	}

	public function is_boolean($str){
		$bool= array(TRUE, 'TRUE', 'true', 1, FALSE, 'FALSE', 'false', 0);
		if(!in_array($str, $bool)){
			$this->CI->form_validation->set_message('is_boolean', 'Le champs %s doit être un booléen.');
			return FALSE;
		}
		else{
			return TRUE;
		}
	}
}