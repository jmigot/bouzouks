<?php if ( ! defined('BASEPATH') && ! defined('CACHE_CLI')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : classe pour les registres du cache cli
 *
 * @author      : Hikingyo (hikingyo@hotmail.fr)
 * Date        : février 2015
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
class Lib_cache_register
{
	private $content;

	public $key;
	public $filename;
	public $create_date;
	public $timeout;
	public $value;



	function __construct($key){
		if(!is_file(FCPATH.Lib_cache_cli::CACHE_PATH.$key['key'].Lib_cache_cli::CACHE_EXTENSION)){	
			$now = now();
			$this->key = $now.'_'.$key['key'];
			$this->create_date = $now;
		}
		else{
			$this->key = $key['key'];
		}
		$this->filename = Lib_cache_cli::CACHE_PATH.$this->key.Lib_cache_cli::CACHE_EXTENSION;
	}

/**
* Méthode de lecture du fichier
* 
* @return FALSE en cas d'échec.
**/
	public function read(){
			if(!is_file($this->filename)){
			echo  "Le fichier demandé ( $this->filename) n'existe pas !";
			return FALSE;
		}
		// On met à jour le registre
		else{
			$this->content = read_file($this->filename);
			$this->content = json_decode($this->content, true);
			foreach ($this->content as $att => $val) {
				$this->$att = $val;
			}
		}
		// Si le registre est trop vieux, on supprime le fichier
		// timeout = 0, registre à ttl infinie
		if($this->timeout <= now() && $this->timeout!= $this->create_date){
			$this->delete();
		}
	}

/**
 * Méthode  d'écriture d'un registre du cache
 * 
 * @return bool
 **/
	public function write($value){
		//Préparation du contenu
		$this->content = json_encode(array(
			'key'			=> $this->key,
			'create_date'	=> $this->create_date,
			'timeout'		=> $this->timeout,
			'value'			=> $value
			));
		if(! write_file($this->filename, $this->content)){
			log_message('error', 'file not writable. Please set model to 755 to the '.Lib_cache_cli::CACHE_PATH.' dir.'.PHP_EOL);
			return FALSE;
		}
	}

/**
 * méthode de suppression d'un registre
 * 
 * @return bool
 **/
	public function delete(){

		if(!unlink(FCPATH.$this->filename)){
			return FALSE;
		}
		else{
			return TRUE;
		}

	}

}