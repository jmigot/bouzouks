<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//  Objet représentant la connexion au sgbd pour les données du serveur vlux
class Vlux_manager extends CI_Model {

	const TABLE = 'vlux_config';
	
	function __construct() {
	}
	
	public function get_config() {
		$this->db->select('*')->from(self::TABLE);
		$query = $this->db->get();
		return $query->row();
	}
	
	public function update($game) {
		$query = $this->db->update(self::TABLE, $game);
	}

	public function update_server($etat){
		$query = $this->db->set('server_etat', $etat);
		$query->update(self::TABLE);
	}

	public function get_auth_default($type)
	{
		$query = $this->db->select('id');
		switch ($type) {
			case 'creator':
				$query = $this->db->where('rang >=', Bouzouk::Rang_Admin);
				break;
			
			case 'bouzouk':
				$query = $query->where('rang >=', Bouzouk::Rang_Admin);
				break;
			
			default:
				show_error("le type spécifié est invalide : $type", 500);
				break;
		}
		$query = $query->get('joueurs');
		$query = $query->result_array();
		foreach($query as $joueurs){
			$auth_default[] = $joueurs['id'];
		}
		return $auth_default;
	}

	public function deco_joueur($set, $where){
		$query = $this->db->set($set);
		if($where){
			$query = $this->db->where($where);
		}
		$query = $this->db->update('joueurs');
	}
}
