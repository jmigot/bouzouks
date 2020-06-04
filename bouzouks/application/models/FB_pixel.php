<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//  Objet représentant la connexion au sgbd pour les données du serveur vlux
class FB_pixel extends CI_Model {

	const TABLE ='fb_pixels';

	public function get_all(){
		$query = $this->db->get(self::TABLE);
		if($query->num_rows() > 0){
			$pixels = $query->result();
			return $pixels;
		}
		else{
			return FALSE;
		}
	}

	public function get($id){
		if(!ctype_digit($id)){
			return FALSE;
		}
		$query = $this->db->where('id', $id)->get(self::TABLE);
		if($query->num_rows > 0){
			$pixel = $query->row();
			return $pixel;
		}
		else{
			return FALSE;
		}
	}

	public function update($pixel){
		$this->db->set($pixel)->where('id', $pixel->id)->update(self::TABLE);
	}

}