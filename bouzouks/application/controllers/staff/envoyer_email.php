
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

class Envoyer_email extends MY_Controller
{
	public function index()
	{
		return $this->layout->view('staff/envoyer_email');
	}

	public function ecrire()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('de', "Le nom d'expéditeur", 'required');
		$this->form_validation->set_rules('email_expediteur', "L'email expéditeur", 'required|valid_email');
		$this->form_validation->set_rules('email_destinataire', "L'email destinataire", 'required|valid_email');
		$this->form_validation->set_rules('objet', "L'objet", 'required');
		$this->form_validation->set_rules('message', 'Le message', 'required');

		if ( ! $this->form_validation->run())
		{
			return $this->index();
		}
		
		// On envoit l'email
		$this->load->library('email');
		$this->email->from($this->input->post('email_expediteur'), $this->input->post('de'))
					->to($this->input->post('email_destinataire'))
					->subject($this->input->post('objet'))
					->message($this->input->post('message'));

		// On envoit l'email
		if ( ! $this->email->send())
		{
			$this->echec("Echec de l'envoi de l'email");
			return $this->index();
		}
		
		$this->succes("L'email a bien été envoyé");
		redirect('staff/accueil');
	}
}
