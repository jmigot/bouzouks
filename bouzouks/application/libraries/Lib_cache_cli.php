<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Empêche l'accès direct à la librairie lib_cache_register
define('CACHE_CLI', 'bouzouks_cache_cli');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion des caches cli
 *
 * @author      : Hikingyo (hikingyo@hotmail.fr)
 * @date        : février 2015 
 *
 * Copyright (C) 2015 Hikingyo - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

/**
 * Librairie pour un systeme de mise en cache de données via le systeme de fichier
 * 
 * Le dossier application/cache/cache_cli doit avoir les droits en écriture pour l'application
 * Un registre est stocké sous forme de fichier .ccf (cache cli file) contenant un tableau au format json sous la forme
 * 
 * {
 * 	'creat_date' : [time_stamp de création de la variable]
 * 	'timeout'	 : [ durée de vie du registre]
 * 	'values'	 : [ contenu du registre]
 * }
 **/
class Lib_cache_cli
{
	private $CI;
	public $register = FALSE;

	const CACHE_PATH = 'application/cache/cache_cli/';
	const CACHE_EXTENSION = '.ccf';

	public function __construct(){
		$this->CI =& get_instance();
		$this->CI->load->helper(array('date','file'));
	}

	public  function store($key, $value, $ttl = 60){
		// Instenciation du registre
		$this->register = $this->get_register($key);
		$this->register->timeout = $ttl + $this->register->create_date;
		$this->register->write($value);
		return $this->register->key;
	}

	public function fetch($key){

		// Instenciation du registre
		$this->register = $this->get_register($key);
		$this->register->read();
		return $this->register->value;
	}

	public function clear_register(){

		if($this->register && $this->register->delete()){
			$this->register = FALSE ;
			return TRUE;
		}
		else{
			echo "Le registre n'existe pas!";
			return FALSE;
		}
	}

	public function clear_all(){

		$cache = $this->dump();
		if(!empty($cache)){
			return delete_files(self::CACHE_PATH);
		}
		else{
			log_message('info', "Le cache est déjà vide");
			return FALSE;
		}
	}

	public function dump(){
		$dump = get_filenames(self::CACHE_PATH);
		return $dump;
	}

	private function get_register($key){
		$key = array('key'=>$key);
		$this->CI->load->library('lib_cache_register', $key);
		$r= $this->CI->lib_cache_register;
		return $r;
	}
}