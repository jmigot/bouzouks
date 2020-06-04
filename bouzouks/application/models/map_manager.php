<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//  Objet représentant la connexion au sgbd pour les données du serveur vlux
class map_manager extends CI_Model {
	
	const TABLE = 'vlux_maps';

	function __construct() {
	}
	
	public function get_map($id) {
		$query = $this->db->select('vlux_maps.*, joueurs.pseudo as proprio_pseudo')
		->where('vlux_maps.id', $id)
		->from(self::TABLE)
		->join('joueurs','joueurs.id = vlux_maps.proprio_id','inner');
		$query = $this->db->get();
		$query = $query->row_array();
		$query['decor'] = unserialize($query['decor']);
		$query['batiments'] = unserialize($query['batiments']);
		$query['auth_creator'] = unserialize($query['auth_creator']);
		return $query;
	}

	public function team_maps(){
		$maps_id = $this->db->select('id')->where('proprio_id', 13)->get(self::TABLE);
		$maps_id = $maps_id->result_array();
		foreach ($maps_id as $map_key => $map_id) {
			$maps_id[$map_key]= $map_id['id'];
		}
		array_shift($maps_id);
		return $maps_id;
	}

	public function mairie_maps(){
		$list_maps = $this->db->where('proprio_id', 2)->select('id, nom, type, size, prix, monnaie, statut_vente')->order_by('nom', 'desc')->get(self::TABLE);
		if($list_maps->num_rows()>0){
			$list_maps = $list_maps->result();
			foreach ($list_maps as $map) {
				$maps['type'][] = $map;
			}
			return $maps;
		}
		else{
			return FALSE;
		}
	}

	public function update ($map) {
		$query = $this->db->where('id', $map->id);
		unset($map->proprio_pseudo);
		$map->decor = serialize($map->decor);
		$map->batiments = serialize($map->batiments);
		$map->auth_creator = serialize($map->auth_creator);
		$query->update(self::TABLE, $map);
	}

	public function create($data){
		$data = array(
			'nom'		=>$data->nom,
			'type'		=> $data->type,
			'size'		=> $data->size,
			'prix'		=>$data->prix,
			'monnaie'	=>$data->monnaie,
			'tuiles'	=> $data->tuiles,
			'decor'		=> serialize($data->decor),
			'batiments'	=> serialize($data->batiments),
			'zone'		=> $data->zone,
			'etat'		=> $data->etat,
			'proprio_id'=> $data->proprio_id,
			'auth_creator'=> serialize($data->auth_creator),
			);
		$query = $this->db->set($data)->insert(self::TABLE);
		$query = $this->db->insert_id();
		return $query;
	}

	public function erase($id){
		if(is_numeric($id)){
			$this->db->where('id', $id)->delete(self::TABLE);
		}
		else{
			show_error("Id $id invalide", 500);
		}
	}
	
	public function count_maps() {
		return $this->db->count_all(self::TABLE);
	}
	
	public function count_maps_open() {
		$query = $this->db->where('etat >=', 2)->from(self::TABLE);
		return $query->count_all_results();
	}
	
	public function list_maps() {
		$this->db->select('vlux_maps.id, vlux_maps.nom, vlux_maps.etat, vlux_maps.auth_creator, vlux_maps.prix, vlux_maps.monnaie, vlux_maps.type,vlux_maps.proprio_id, joueurs.pseudo, joueurs.rang')->from(self::TABLE)->join('joueurs','joueurs.id = vlux_maps.proprio_id','inner');
		$maps = $this->db->get();
		$maps =$maps->result_array();
		foreach ($maps as $key=>$map) {
			$maps[$key]['auth_creator'] = unserialize($map['auth_creator']);
			$maps[$key]['etat'] = $this->convert_etat($maps[$key]['etat']);
			
		}
		return $maps;
	}

	public function list_own($bouzouk_id) {
		$this->db->where('proprio_id', $bouzouk_id)
					->select('vlux_maps.id, vlux_maps.nom, vlux_maps.etat, vlux_maps.auth_creator, vlux_maps.prix, vlux_maps.monnaie, vlux_maps.type,vlux_maps.proprio_id, vlux_maps.size')
					->from(self::TABLE);
		$maps = $this->db->get();
		$maps =$maps->result_array();
		foreach ($maps as $key=>$map) {
			$maps[$key]['auth_creator'] = unserialize($map['auth_creator']);
			$maps[$key]['etat'] = $this->convert_etat($maps[$key]['etat']);
		}
		return $maps;
	}

	public function get_possible_destination($list_destinations){
		$this->db->select('id, nom')->where('id !=', 1);
		if(!empty($list_destinations)){
			$this->db->where_not_in('id', $list_destinations);
		}
		$list = $this->db->get(self::TABLE);
		$list = $list->result_array();
		return $list;
	}

	public function get_connecte($id){
		$query = $this->db->where('map_id', $id)
						  ->where('map_connecte', 1)
						  ->select('id')
						  ->get('joueurs');
		$query = $query->result();
	}

	public function is_map($id){
		$query = $this->db->where('id', $id)->select('id')->get(self::TABLE);
		if($query->num_rows()>0){
			return $id;
		}
		else{
			return FALSE;
		}
	}

	private function convert_etat($map_etat){
		switch ($map_etat) {
				case 0 :
				$map_etat = 'Hors ligne';
				break;
				case 1 :
				$map_etat = 'Accès admin';
				break;
				case 2 :
				$map_etat = 'Mode créa';
				break;
				break;
				case 3 :
				$map_etat = 'Ouverte';
				break;
				default:
				if(ENVIRONMENT=='devlopment'){show_error("Problême : statut de carte érroné : ".$maps[$key]['nom'].' '.$maps[$key]['etat'], 500);}
				break;
			}
		return $map_etat;
	}

	public function reset_changement_nom(){
		$this->db->set('changement_nom', 0)->where('proprio_id', 2)->update(self::TABLE);
	}

	public function get_vente($proprio_id){
		$maps = $this->db->where('proprio_id', $proprio_id)
						 ->where('statut_vente', 1)
						 ->select('id, nom, size, prix')
						 ->get(self::TABLE);
		$maps = $maps->result();
		return $maps;
	}

	public function changement_proprio($map_id, $new_proprio_id){
		$this->db->set('proprio_id', $new_proprio_id)->set('statut_vente', 0)->where('id', $map_id)->update(self::TABLE);
	}
}