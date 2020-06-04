<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration du site
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : août 2013
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */
 
class Moderer_map_tchats extends MY_Controller
{
	public function __construct(){
		parent::__construct();
		$this->load->library('vlux/tchat_factory');
	}

	public function index()
	{
		// On va chercher la list des tchats
		$chans = $this->tchat_factory->get_chans();

		// On affiche les résultats
		$vars = array(
			'title' => "Modération Tchats Map",
			'lien' => 1,
			'chans' => $chans
		);
		return $this->layout->view('staff/gerer_map_tchat/moderer_map_tchats', $vars);
	}

	public function signalements()
	{
		// On va chercher les signalements
		$signalements_a_traiter = $this->tchat_factory->get_signalements_a_traiter();
		$signalements_traites = $this->tchat_factory->get_signalements_traites();
		// On affiche les résultats
		$vars = array(
			'title' => "Modération - Signalements Map",
			'signalements_a_traiter' => $signalements_a_traiter,
			'signalements_traites'	 => $signalements_traites,
			'lien' => 2
		);
		return $this->layout->view('staff/gerer_map_tchat/signalements', $vars);
	}

	public function traiter_signalement($id){
		if(!ctype_digit($id)){
			log_message('error', __FILE__.' '.__LINE__.' id de signalement demandé par'.$this->userdata('pseudo').' invalide.');
		}
		if($this->tchat_factory->set_statut_signalement($id, $this->session->userdata('id'))){
			$this->succes("Le signalement a été validé.");
			return $this->signalements();
		}
		else{
			$this->echec("Ce signalement a déjà été valider");
			return $this->signalements();
		}
	}
}