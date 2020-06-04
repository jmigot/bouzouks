<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Projet      : Bouzouks
 * Description : Librairie de gestion et de controls des items type décor de Vlux 3D
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


class Item_factory {

private $CI;
private $manager;

	public function __construct(){
		$this->CI =& get_instance();
		$this->manager = $this->get_manager();
	}

	private function get_manager(){
		$this->CI->load->model('item_manager');
		return $this->CI->item_manager;
	}

	public function get_ressources($map_type, $rang){

		$items = $this->manager->get_items($map_type, $rang);
		foreach ($items as $item) {
			$type = $item['type'];
			$cat = $item['cat'];
			$ressources[$type][$cat][] = $item;
		}
		return $ressources;
	}

	public function list_items($map_type, $rang){
		$objets = $this->manager->get_items($map_type, $rang);
		return $objets;
	}

	public function get_list(){
		$objets = $this->manager->get_list();
		return $objets;
	}

	public function get_item($id){
		$item = $this->manager->get_item($id);
		return $item;
	}

	public function for_client($map_type){
		$items = $this->list_items($map_type, Bouzouk::Rang_Admin);
		if(!$items){
			return FALSE;
		}
		else{
			foreach ($items as $item) {
					$id = $item['id'];
					$items[$id]= $item;
				}
			return $items;
		}
	}

	public function update_item($item){
		$this->manager->update_item($item);
	}

	public function new_item(){
		$data = array(
			'id'	=> 'tmp',
			'nom'	=> 'truc',
			'img'	=>'vide',
			'auth_level' => Bouzouk::Rang_Admin
			);
		$this->CI->load->library('vlux/item', $data);
		// on retourne un objet Item par défaut.
		$item =$this->CI->item;
		return $item;
	}

	public function create_item($item){
		$id = $this->manager->create($item);
		return $id;
	}

	public function delete_item($id){
		$item = $this->get_item($id);
		$filename = './webroot/images/map/objets/'.$item->img.'.png';
		if(file_exists($filename)){
			unlink($filename);
			$this->manager->delete($id);
			return true;
		}
		
		else{
			show_error("L'image $filename.png n'existe pas !", 500);
		}
	}

	public function check_decor($decor, $map_size, $map_type, $rang){
		if($decor!=0){
			$item_check_list = $this->manager->list_check_decor($map_type);
			$items_data = explode('-', $decor);
			foreach($items_data as $data){
				$data = explode('_', $data);
				if($data[0]>0 && $data[0]<=$map_size && $data[1]>0 && $data[1]<=$map_size && array_key_exists($data[3], $item_check_list) && $rang >= $item_check_list[$data[3]]['auth_level']){
					$items[]=array('x'=>$data[0], 'y'=>$data[1], 'z'=>$data[2], 'vid'=>$data[3]);
				}
				else{
					return FALSE;
				}
			}
		}
		else{
			$items= array();
		}

		return ($items);
	}

	public function check_tuiles($tuiles, $map_size){
		$list_tuiles = $this->manager->list_check_tuiles();
		$tuiles = str_replace(array("-","_"), array(":",","), $tuiles );
		$tuiles_array = explode(':', $tuiles);
		if(count($tuiles_array) != (intval($map_size))){
			return FALSE;
		}
		foreach ($tuiles_array as $tuiles_row) {
			$tuiles_row = explode(',', $tuiles_row);
			if(count($tuiles_row)!= (intval($map_size))){
				return FALSE;
			}
			foreach ($tuiles_row as $tuile) {
				if(!in_array($tuile, $list_tuiles)){
					return FALSE;
				}
			}
		}
		return $tuiles;
	}

	public function get_tuiles(){
		$tuiles = $this->manager->get_tuiles();
		return $tuiles;
	}

}