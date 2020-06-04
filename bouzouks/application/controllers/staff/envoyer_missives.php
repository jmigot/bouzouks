
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

class Envoyer_missives extends MY_Controller
{
	private $destinataires;

	public function __construct()
	{
		parent::__construct();
		$this->load->library('lib_missive');
		$this->load->library('lib_parser');
		
		$this->destinataires = array(
			0 => '---------------',
			1 => 'Tous',
			2 => 'Patrons',
			3 => 'Employés',
			4 => 'Bêta-Testeurs',
			5 => 'Journalistes',
		);
	}

	public function index()
	{
		// Expéditeurs possibles
		$query = $this->db->select('id, pseudo')
						  ->from('joueurs')
						  ->where_in('id', array_merge($this->bouzouk->get_inactifs(), $this->bouzouk->get_robots()))
						  ->or_where('(rang & '.$this->bouzouk->get_masque(Bouzouk::Masque_Admin).' > 0)')
						  ->order_by('statut')
						  ->order_by('pseudo')
						  ->get();
		$robots = $query->result();

		$select_expediteurs = '<select name="expediteur_id"><option value="">---------------</option>';

		foreach ($robots as $robot)
		{
			$selected = ($this->input->post('expediteur_id') == $robot->id) ? ' selected' : '';
			$select_expediteurs .= '<option value="'.$robot->id.'"'.$selected.'>'.$robot->pseudo.'</option>';
		}
		
		$select_expediteurs .= '</select>';
		
		// Destinataires possibles
		$select_destinataires = '<select name="destinataire">';

		foreach ($this->destinataires as $cle => $destinataire)
		{
			$selected = ($this->input->post('destinataire') == $cle) ? ' selected' : '';
			$select_destinataires .= '<option value="'.$cle.'"'.$selected.'>'.$destinataire.'</option>';
		}

		$select_destinataires .= '</select>';

		// On affiche
		$vars = array(
			'select_expediteurs'   => $select_expediteurs,
			'select_destinataires' => $select_destinataires,
			'timbres'              => $this->lib_missive->timbres(),
			'intros'               => $this->lib_missive->intros(),
			'politesses'           => $this->lib_missive->politesses(),
			'adresse'              => $this->session->userdata('adresse'),
			'table_smileys'        => creer_table_smileys('message'),
		);
		return $this->layout->view('staff/envoyer_missives', $vars);
	}

	public function _intro_check($intro)
	{
		if ( ! array_key_exists($intro, $this->lib_missive->intros()))
		{
			$this->form_validation->set_message('_intro_check', "%s n'existe pas");
			return false;
		}

		return true;
	}

	public function _politesse_check($politesse)
	{
		if ( ! array_key_exists($politesse, $this->lib_missive->politesses()))
		{
			$this->form_validation->set_message('_politesse_check', "%s n'existe pas");
			return false;
		}

		return true;
	}

	public function ecrire()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('expediteur_id', "L'expéditeur", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('destinataire', 'Le destinataire', 'required|is_natural');
		$this->form_validation->set_rules('timbre', 'Le timbre', 'required');
		$this->form_validation->set_rules('intro', "L'introduction", 'required|callback__intro_check');
		$this->form_validation->set_rules('objet', "L'objet", 'required|min_length[3]|max_length[60]');
		$this->form_validation->set_rules('message', 'Le message', 'required|min_length[1]|max_length[5000]');
		$this->form_validation->set_rules('politesse', 'La formule de politesse', 'required|callback__politesse_check');

		if ( ! $this->form_validation->run())
		{
			return $this->index();
		}
		// L'expéditeur doit être valide
		$query = $this->db->select('id, pseudo, rang')
						  ->from('joueurs')
						  ->where('id', $this->input->post('expediteur_id'))
						  ->where_in('(id', array_merge($this->bouzouk->get_inactifs(), $this->bouzouk->get_robots()))
						  ->or_where('(rang & '.$this->bouzouk->get_masque(Bouzouk::Masque_Admin).' > 0))')
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("L'expéditeur est invalide");
			return $this->index();
		}
		$expediteur = $query->row();
		// Le destinataire doit exister
		if ( ! in_array($this->input->post('destinataire'), array_keys($this->destinataires)))
		{
			$this->echec('Le destinataire est invalide');
			return $this->index();
		}

		// Le timbre doit exister
		if ( ! array_key_exists($this->input->post('timbre'), $this->lib_missive->timbres()))
		{
			$this->echec("Ce timbre n'existe pas");
			return $this->index();
		}

		// Destinataire = Tous
		if ($this->input->post('destinataire') == 1)
		{
			$query = $this->db->select('id')
							  ->from('joueurs')
							  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile, Bouzouk::Joueur_Pause))
							  ->get();
			$joueurs = $query->result();
		}

		// Destinataire = Patrons
		else if ($this->input->post('destinataire') == 2)
		{
			$query = $this->db->select('chef_id AS id')
							  ->from('entreprises')
							  ->get();
			$joueurs = $query->result();
		}

		// Destinataire = employés
		else if ($this->input->post('destinataire') == 3)
		{
			$query = $this->db->select('joueur_id AS id')
							  ->from('employes')
							  ->get();
			$joueurs = $query->result();
		}

		// Destinataire = bêta-testeurs
		else if ($this->input->post('destinataire') == 4)
		{
			$rang = $this->bouzouk->get_masque(Bouzouk::Masque_Moderateur | Bouzouk::Masque_Admin) | Bouzouk::Rang_BetaTesteur;
			
			$query = $this->db->select('id')
							  ->from('joueurs')
							  ->where('rang & '.$rang.' > 0')
							  ->get();
			$joueurs = $query->result();
		}

		// Destinataire = journalistes
		else if ($this->input->post('destinataire') == 5)
		{
			$rang = $this->bouzouk->get_masque(Bouzouk::Masque_Admin | Bouzouk::Masque_Journaliste);

			$query = $this->db->select('id')
							  ->from('joueurs')
							  ->where('rang & '.$rang.' > 0')
							  ->get();
			$joueurs = $query->result();
		}

		else
		{
			$this->echec('Le destinataire est invalide');
			return $this->index();
		}

		$data_missives = array();
		$data_historique = array();
		$time = bdd_datetime();
		$notification = array(profil($expediteur->id, $expediteur->pseudo, $expediteur->rang));

		foreach ($joueurs as $joueur)
		{
			// On enregistre la lettre
			$data_missives[] = array(
				'expediteur_id'         => $expediteur->id,
				'destinataire_id'       => $joueur->id,
				'date_envoi'            => $time,
				'timbre'                => $this->input->post('timbre'),
				'objet'                 => $this->input->post('objet'),
				'message'               => "\t".$this->lib_missive->intros($this->input->post('intro')).",\n\n".$this->input->post('message')."\n\n\t".$this->lib_missive->politesses($this->input->post('politesse')),
				'lue'                   => 0,
				'expediteur_supprime'   => 1,
				'destinataire_supprime' => 0
			);

			// Si le destinataire est connecté, on envoit une notif
			if ($this->bouzouk->est_connecte($joueur->id))
			{
				$data_historique[] = array(
					'joueur_id'    		=> $joueur->id,
					'texte_id_private'  => 276,
					'donnees'           => serialize($notification),
					'notification' 		=> Bouzouk::Historique_Notification,
					'date'         		=> $time
				);
			}
		}
		$this->db->insert_batch('missives', $data_missives);
		$this->db->insert_batch('historique', $data_historique);

		// On affiche un message de confirmation
		$this->succes('Missive envoyée ;)');
		return $this->index();
	}
}
