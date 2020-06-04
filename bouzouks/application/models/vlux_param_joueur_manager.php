<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//  Objet représentant la connexion au sgbd pour les données du serveur vlux
class vlux_param_joueur_manager extends CI_Model {

	const TABLE ='vlux_param_joueur';

	public function get($id){
		$query = $this->db->where('id', $id)->get(self::TABLE);
		$query = $query->row();
		return $query;
	}

	public function set($id, $params){
		$query = $this->db->set($params)->where('id', $id)->update(self::TABLE);
	}

	public function set_res_principale($map_id, $id){
		$this->db->set('res_principale', $map_id)->where('id', $id)->update(self::TABLE);
	}

	public function create($params){
		$this->db->insert(self::TABLE, $params);
	}

	public function delete($id){
		$this->db->where('id', $id)->delete(self::TABLE);
	}
}