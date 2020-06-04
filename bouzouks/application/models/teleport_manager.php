<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Classe définissant la connexion au sgbd et la manipulation de la table vlux_gates
 * 
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 * 
 * @author       Hikingyo <hikingyo@outlook.com>
 * @copyright    Team Bouzouk 2015 (C) 2015 Hikingyo - Tous droits réservés
 * @package      bouzouks\vlux\libs
 * @see          /application/libraries/jeu/vlux_factory
 * 
 */
class teleport_manager extends CI_Model {

	const TABLE = 'vlux_teleports';

	public function get($id){
		if(is_numeric($id)){
			$teleport = $this->db->where('id', $id)->get(self::TABLE);
			$teleport = $teleport->row();
			$teleport->destination = unserialize($teleport->destination);
			return $teleport;
		}
		else{
			if(ENVIRONMENT =='development'){show_error("'tit soucis avec le teleport $id, chef!", 500);}
		}
	}

/**
 * Recherche d'un télételeportur à partir de ses coorddonnées
 * 
 * @param int $map_id identifiant de la map de départ
 * @param int $gate_x coordonnée en x du téléport de départ
 * @param int gate_y coordonnée en y du téléport d départ
 * 
 * @return array contenant le résultat de la requête
 **/
	public function get_by_coord($map_id, $teleport_x, $teleport_y){
		$teleport = $this->db->where(array('map_id'=>$map_id,'x'=>$teleport_x, 'y'=>$teleport_y))
				 ->get(self::TABLE);
		$teleport = $teleport->row();
		$teleport->destination = unserialize($teleport->destination);
		return $teleport;
	}

/**
 * Ajout d'un téléport en bdd
 * @param object [$teleport] l'objet a persister
 * @return int [$id] l'id de l'enregistrement
 * 											**/
	public function create($teleport){
		$teleport->destination = serialize($teleport->destination);
		$this->db->set($teleport)->insert(self::TABLE);
		$id = $this->db->insert_id();
		return $id;
	}

/**
 * Mise à jour d'un téléport en bdd
 * 
 * @param obj $teleport L'instance de la classe du téléport à mettre à jour
 * 
 * @return void
 **/
	public function update($teleport){
		$teleport->destination = serialize($teleport->destination);
$this->db->where('id', $teleport->id)
				 ->update(self::TABLE, $teleport);
	}

/**
 * Recherche des destination d'un téléporteur
 * 
 * @param int $map_id identifiant de la map de départ
 * @param int $gate_x coordonnée en x du téléport de départ
 * @param int gate_y coordonnée en y du téléport d départ
 * 
 * @return mixed la liste des identifiants des destinations
 **/
	public function get_dest($map_id, $teleport_x, $teleport_y){
		$query = $this->db->select('destination')
				 ->where(array('map_id'=>$map_id,'x'=>$teleport_x, 'y'=>$teleport_y))
				 ->get(self::TABLE);
		$dest = $query->row_array();
		return $dest;
	}

/**
 * Ajout d'une destination à un téléport
 * 
 * @param int $id l'identifiant du téléport
 * @param int $dest l'identifiant du téléport de destination à ajouter
 * 
 * @return void
 **/
	public function add_dest($id, $dest){
		$dest = array($dest);
		$dest = serialize($dest);
		$query = $this->db->where('id', $id)->update(self::TABLE, array('destination'=>$dest));
	}

/**
 * Supression d'un téléport en bdd
 * 
 * @param int $id identifiant du téléport à suprimer
 * 
 *  @return void
 **/
	public function delete($id){
		$this->db->delete(self::TABLE, array('id'=>$id));
	}

/**
 * Liste des téléports d'une map
 **/
	public function get_map_teleport($map_id){
		$query = $this->db->where('map_id', $map_id)->select('id')->get(self::TABLE);
		$teleports = $query->result_array();
		return $teleports;
	}

	public function get_one_map_teleport($map_id){
		$query = $this->db->select('id')->where('map_id', $map_id)->get(self::TABLE);
		if($query->num_rows()>0){
			$query = $query->row();
			return $query->id;
		}
		else{
			return FALSE;
		}
		
	}

}