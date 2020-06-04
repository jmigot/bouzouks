<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration du site
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : février 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */
 
class Gerer_serveurs extends MY_Controller
{

public $vars = array();
private $_server;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('vlux/vlux_factory');
		$this->_server = $this->vlux_factory->get_server();
		$this->vars['etat'] =$this->_server->etat;
		$this->vars['title'] = "Gestion Serveur";
		$this->vars['lien'] = 2;
		$this->get_img_server();
	}

	public function index()
	{
		// On affiche
		return $this->layout->view('staff/vlux/gerer_serveurs', $this->vars);
	}

	public function switch_serveur($etat) {
		switch ($etat) {
			// Arrêt du serveur vlux
			case server::Etat_Offline : 
				$msg ="à l'arrêt.";
				break;
			// En mode admin seulement
			case server::Etat_Lock : 
				$msg = "en accès limité.";
				break;
			// Serveur en mode béta
			case server::Etat_Beta : 
				$msg=" en mode test.";
				break;
	 		// Serveur ouvert
			case server::Etat_Open :
				$msg=" en ligne.";
				break;
				
			default :
				break;
		}
		if (isset($msg) and !empty($msg)) {
			$this->server->switch_state($etat);
			$this->succes('Le serveur node.js a bien été mis '.$msg);
			redirect('staff/gerer_serveurs');
		}
		else {
			if(ENVIRONMENT == 'development'){
				show_error("Probleme de changement d'état : ".$etat." ".$msg,500);
			}
		}
		
	}
	
	public function get_img_server() {
			
		foreach ($this->server->get_img() as $attribut => $valeur) {
			$this->vars['etat_serveur'][$attribut]=$valeur;
		}
	}	
}
