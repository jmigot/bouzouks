<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Projet      : Bouzouks
 * Description : Librairie de gestion et de control des items type téléporteur de Vlux 3D
 *
 * Auteur      : Hikingyo
 * Date        : Novembre 2014
 * Revision    : Avril 2015
 *
 * Copyright (C) 201-2015 Hikingyo - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */


class Teleport_factory {
private $CI;
private $manager;

const Teleport_type_bloque = 117;
const Teleport_type_double_sens = 118;
const Teleport_type_sens_unique = 119;
const Teleport_type_restreint = 120;

const Etat_Lock = 0;
const Etat_Blacklist = 1;
const Etat_Whitelist = 2;
const Etat_Open = 3;

const Mode_bloquee = -1;
const Mode_Sens_Unique = 0;
const Mode_Double_Sens = 1;
const Mode_Restreint = 2;
const Mode_Invisible = 3;

	public function __construct(){
		$this->CI =& get_instance();
		$this->manager = $this->get_manager();
	}

	public function get_manager(){
		$this->CI->load->model('teleport_manager');
		return $this->CI->teleport_manager;
	}

	public function get_teleport($id){
		$teleport = $this->manager->get($id);
		return $teleport;
	}

	public function get_teleport_by_coord($map_id, $teleport_x, $teleport_y){
		$teleport = $this->manager->get_by_coord($map_id, $teleport_x, $teleport_y);
		return $teleport;
	}
	
	/**
	 * Cette function permet de déterminer si un bouzouk a le droit de passage
	 * 
	 * @param int [$id] id du bouzouk
	 * @return bool 
	 * @todo ajout du rang modérateur map
	 **/

	public function is_granted($bouzouk_id, $bouzouk_rang, $teleport){
		// On filtre en premier en fonction de l'état de la porte
		switch ($teleport->etat) {
			// Porte fermée, uniquement admin
			case self::Etat_Lock :
				$this->CI->bouzouk->is_admin(null, $bouzouk_rang)?$auth=TRUE : $auth = FALSE;
				break;
			//Porte filtrée par blacklist. Les admins ne peuvent être blacklistés
			case self::Etat_Blacklist :
				if(in_array($bouzouk_id, $teleport->blacklist) && !$this->CI->bouzouk->is_admin(null, $bouzouk_rang)){
					$auth = FALSE;
				}
				break;
			// Porte filtrées par liste blanche
			case self::Etat_Whitelist :
				if(in_array($bouzouk_id, $teleport->whitelist) || $this->CI->bouzouk->is_admin(null, $bouzouk_rang)) {
					$auth = TRUE;
				}
				break;
			// Porte ouvert
			case self::Etat_Open :
				$auth = TRUE;
				break;
			// Par défault, on ferme la porte, sauf au admin
			default:
				$this->CI->bouzouk->is_admin(null, $bouzouk_rang)?$auth=TRUE : $auth = FALSE;
				break;
		}
		return $auth;
	}

	public function create_teleport($teleport){

			switch ($teleport['type']) {
				// Bloquée
				case 'bloque':
					$etat = 0;
					$mode = -1;
					break;
				// Sens unique, paramètrage des teleport en fonction du sens
				case 'sens_unique':
					$etat = 3;
					$mode = 0;
					break;
				case 'double_sens':
					$etat = 3;
					$mode = 1;
					break;
				case 'invisible':
					$etat = 3;
					$mode = 3;
					break;
				case 'restreint':
					$etat = 3;
					$mode = 2;
					break;
				default:
					show_error("Type de teleport incorrect : $teleport", 500);
					break;
			}
			// Configuration etat et mode du teleport
			$teleport['etat'] = $etat;
			$teleport['mode'] = $mode;
			
			if(isset($teleport['destination'])){
				$teleport['destination'] = array($teleport['destination']);
			}
			
			// Mise en en bdd
			$this->CI->load->library('vlux/teleport');
			$teleport = new $this->CI->teleport($teleport);
			$id = $this->manager->create($teleport);

			// Ajout du teleport dans la liste des objet
			$map = $this->CI->map_factory->get_map($teleport->map_id);
			$decor = $map->decor;
			$g = array(
				'x'		=>$teleport->x,
				'y'		=>$teleport->y,
				'z'		=>0,
				'vid'	=>constant('self::Teleport_type_'.$teleport->type)
				);	
			$map->decor[]= $g;
			// Maj du décor
			$this->CI->map_factory->update_map($map);
			return $id;
	}

	public function update_teleport($teleport){
		$this->manager->update($teleport);
	}

	public function finalize_teleport($id, $id_dest){
		$this->manager->add_dest($id, $id_dest);
	}

	public function supression_teleport($teleport){
		// Supression du téléport de la liste de destinations des téléport qui lui sont liés
		foreach ($teleport->destination as $destination_index => $destination_id) {
			$destination = $this->get_teleport($destination_id);
			//Si le téléport à suprimer est le seul de la liste, on suprrime celui-ci
			if(count($destination->destination)== 1){
				$this->suprimer_teleport($destination);
			}
			//Sinon, on retire le téléport à suprimer de la liste de destination
			else{
				$key = array_search($teleport->id, $destination->destination);
				array_splice($destination->destination, $key, 1);
				$this->update_teleport($destination);
			}
		}
		// On suprime le téléport lui-même
		$this->suprimer_teleport($teleport);
	}

	private function suprimer_teleport($teleport){

		$this->manager->delete($teleport->id);
		//Supression du décor de la map
		$this->CI->map_factory->suprimer_decor($teleport->map_id, $teleport->x, $teleport->y, constant('self::Teleport_type_'.$teleport->type));
	}

	public function get_map_destinations($map_id){
		$map_list = array();
		//Liste des téléports de la map
		$teleports = $this->manager->get_map_teleport($map_id);
		//Teste des destinations
		foreach ($teleports as $teleport){
			$dest = $this->get_teleport($teleport['id']);
			$dest = $dest->destination;
			foreach ($dest as $gate) {
				$gate = $this->get_teleport($gate);
				if($this->is_dest($gate, $map_id)){
					$map_list[] = $gate->map_id;
				}
			}
		}
		return $map_list;
	}

	public function is_dest($teleport, $map_id){
		$destinations = $teleport->destination;
		foreach ($destinations as $teleport_dest) {
			$dest = $this->get_teleport($teleport_dest);
			if($map_id == $dest->map_id){
				return TRUE;
			}
		}
		return FALSE;
	}

	public function get_one_map_teleport($map_id){
		return $this->manager->get_one_map_teleport($map_id);
	}

}