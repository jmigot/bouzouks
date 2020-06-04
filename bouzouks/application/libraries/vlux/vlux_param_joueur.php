<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vlux_param_joueur {

	private $CI;
	private $manager;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->manager = $this->get_manager();
	}

	private function get_manager(){
		$this->CI->load->model('vlux_param_joueur_manager');
		return $this->CI->vlux_param_joueur_manager;
	}

	public function get_param($id){
		return $this->manager->get($id);
	}

	public function set_param($id, $params){
		return $this->manager->set($id, $params);
	}

	public function set_res_principale($map_id, $id){
		$this->manager->set_res_principale($map_id, $id);
	}

	public function nouveau_joueur($id){
		$params = array(
			'id'				=> $id,
			'zoom_defaut'		=> 0.8,
			'son_notif'			=> 1,
			'chan_defaut'		=> 'map',
			'affichage_pseudo'	=> 1,
			'res_principale'	=> 2
			);
		$this->manager->create($params);
	}

	public function supprimer_joueur($id){
		$this->manager->delete($id);
	}
}