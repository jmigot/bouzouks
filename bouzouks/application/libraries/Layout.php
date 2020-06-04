<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Layout
{
	private $CI;
	private $title;
	private $css;
	private $javascripts;

	public function __construct()
	{
		$this->CI          =& get_instance();
		$this->title       = 'Jeu de simulation politique gratuit en ligne';
		$this->javascripts = '';
		$this->css         = '';
	}

	public function view($view, $data = null, $return = false, $layout = null)
	{
		// Si le layout n'est pas renseigné, on prend le layout du site par défaut
		if ( ! isset($layout))
			$layout = $this->CI->session->userdata('connecte') ? 'site' : 'site_visiteur';

		if($layout == 'site_visiteur'){
			$this->CI->load->library('fb/fb_api');
			$loadedData['fb_connexion'] = $this->CI->fb_api->get_login_url();
			$data['fb_connexion'] = $loadedData['fb_connexion'];
		}

		$loadedData = array(
			'content_for_layout'     => $this->CI->load->view($view, $data, true),
			'title_for_layout'       => $this->title,
			'javascripts_for_layout' => $this->javascripts,
			'css_for_layout'         => $this->css
		);
		
		if ($layout == 'site')
			$this->CI->load->library('lib_parser');
		
		if ($return)
			return $this->CI->load->view('layouts/'.$layout, $loadedData, true);

		$this->CI->load->view('layouts/'.$layout, $loadedData, false);
	}

	public function set_title($title)
	{
		$this->title = $title;
	}

	public function ajouter_javascript($fichier)
	{
		$src = javascript_url($fichier);
		$this->javascripts .= "<script src='$src'></script>";
	}

	public function ajouter_javascript_externe($fichier)
	{
		$this->javascripts .= "<script src='$fichier'></script>";
	}

	public function ajouter_javascript_script($script){
		$this->javascripts .= "<script>".$script."</script>";
	}

	public function ajouter_css($fichier)
	{
		$src = css_url($fichier);
		$this->css .= "<link rel='stylesheet' media='screen' type='text/css' href='$src'>";
	}
}