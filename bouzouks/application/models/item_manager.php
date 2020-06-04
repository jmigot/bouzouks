<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//  Objet représentant la connexion au sgbd pour les données du serveur vlux
class Item_manager extends CI_Model {

const TABLE ='vlux_items';

	public function get_list(){
		$items = $this->db->order_by('nom desc, type desc')->get(self::TABLE);
		if($items->num_rows() > 0){
			foreach ($items->result() as $item) {
					$liste[$item->type][] = $item;
				}
				return $liste;
		}
		else{
			return FALSE;
		}
	}

	public function get_item($id){
		$query = $this->db->where('id', $id)->get(self::TABLE);
		$item = $query->row();
		return $item;
	}

	public function get_items($type, $rang){
		$query= $this->db->where(array('type'=> $type, 'auth_level <='=> $rang))->or_where('type', 'sols')->or_where('type', 'utilitaires')->get(self::TABLE);
		$items = $query->result_array();
		return $items;
	}

	public function get_tuiles(){
		$query = $this->db->where('type', 'sols')->select('img, infranchissable')->get(self::TABLE);
		$tuiles = $query->result_array();
		return $tuiles;
	}

	public function update_item($item){
		$query = $this->db->where('id', $item->id)->set($item)->update(self::TABLE);
	}

	public function create($item){
		$query = $this->db->set($item)->insert(self::TABLE);
		$id = $this->db->insert_id();
		return $id;
	}

	public function delete($id){
		$query = $this->db->where('id', $id)->delete(self::TABLE);
	}

	public function list_check_tuiles(){
		$list_tuiles = $this->db->select('img')->where('type','sols')->get('vlux_items');
		$list_tuiles = $list_tuiles->result_array();
		foreach ($list_tuiles as $key=>$tuile) {
			$list_tuiles[$key] = $tuile['img'];
		}
		return $list_tuiles;
	}

	public function list_check_decor($map_type){
		$list_items = $this->db->select('id, auth_level')->where('type',$map_type)->or_where('cat', 'portes')->get('vlux_items');
		$list_items = $list_items->result_array();
		foreach ($list_items as $key=>$item) {
			$list_items[$item['id']] = $item;
		}
		return $list_items;
	}
}