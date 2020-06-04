<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : système de recrutement pour permettre aux patrons de poster des annonces et d'embaucher des employés
 *               d'email, de personnage ou mettre son compte en pause
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Recrutement extends MY_Controller
{
	public function index()
	{
		return $this->lister();
	}

	public function lister()
	{
		// On va chercher les annonces proposées du patron
		$query = $this->db->select('jobs.nom AS job, pa.id, pa.salaire, pa.prime_depart, pa.message')
						  ->from('petites_annonces pa')
						  ->join('jobs', 'jobs.id = pa.job_id')
						  ->where('type', Bouzouk::PetitesAnnonces_Patron)
						  ->where('pa.entreprise_id', $this->session->userdata('entreprise_id'))
						  ->where('pa.joueur_id IS NULL')
						  ->order_by('pa.date_annonce', 'desc')
						  ->get();
		$annonces_proposees = $query->result();
		
		// On va chercher les annonces acceptées du patron
		$query = $this->db->select('pa.joueur_id, j.pseudo, jobs.nom AS job, pa.id, pa.salaire, pa.prime_depart, pa.message')
						  ->from('petites_annonces pa')
						  ->join('jobs', 'jobs.id = pa.job_id')
						  ->join('joueurs j', 'j.id = pa.joueur_id')
						  ->where('type', Bouzouk::PetitesAnnonces_Patron)
						  ->where('pa.entreprise_id', $this->session->userdata('entreprise_id'))
						  ->where('pa.joueur_id IS NOT NULL')
						  ->order_by('pa.date_annonce', 'desc')
						  ->get();
		$annonces_acceptees = $query->result();

		// On va chercher les annonces des chômeurs acceptées par le patron
		$query = $this->db->select('pa.id, j.id AS chomeur_id, j.pseudo AS chomeur_pseudo, pa.salaire, pa.prime_depart, jobs.nom AS job')
						  ->from('petites_annonces pa')
						  ->join('jobs', 'jobs.id = pa.job_id')
						  ->join('joueurs j', 'j.id = pa.joueur_id')
						  ->where('type', Bouzouk::PetitesAnnonces_Chomeur)
						  ->where('pa.entreprise_id', $this->session->userdata('entreprise_id'))
						  ->order_by('pa.date_annonce', 'desc')
						  ->get();
		$annonces_chomeurs = $query->result();
		
		// On va chercher la liste des jobs pour poster une annonce
		$query = $this->db->select('id, nom')
						  ->from('jobs')
						  ->order_by('experience')
						  ->get();
		$jobs = $query->result();

		$vars = array(
			'annonces_proposees' => $annonces_proposees,
			'annonces_acceptees' => $annonces_acceptees,
			'annonces_chomeurs'  => $annonces_chomeurs,
			'jobs'               => $jobs
		);
		return $this->layout->view('recrutement/lister', $vars);
	}

	public function poster()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('job_id', 'Le job', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('salaire', 'Le salaire', 'required|is_natural_no_zero|greater_than_or_equal['.$this->bouzouk->config('entreprises_salaire_min_employe').']|less_than_or_equal['.$this->bouzouk->config('entreprises_salaire_max_employe').']');
		$this->form_validation->set_rules('prime_depart', "La prime d'incompétence", 'required|is_natural|less_than_or_equal['.$this->bouzouk->config('entreprises_prime_max').']');
		$this->form_validation->set_rules('message', 'Le message', 'required|max_length[250]');
		$this->form_validation->set_rules('nombre', "Le nombre d'annonces", 'required|is_natural_no_zero|less_than_or_equal[5]');

		if ( ! $this->form_validation->run())
		{
			return $this->lister();
		}

		// On vérifie que le nombre d'annonces maximum n'est pas atteint
		$nb_annonces = $this->db->where('type', Bouzouk::PetitesAnnonces_Patron)
								->where('entreprise_id', $this->session->userdata('entreprise_id'))
								->where('joueur_id', null)
								->count_all_results('petites_annonces');

		if ($nb_annonces + $this->input->post('nombre') > $this->bouzouk->config('recrutement_max_annonces'))
		{
			$this->echec("Tu as déjà posté <span class='pourpre'>".pluriel($nb_annonces, 'annonce').'</span> (limite : <span class="pourpre">'.$this->bouzouk->config('recrutement_max_annonces')." annonces</span>, tu ne peux pas en re-poster autant");
			return $this->lister();
		}

		// On vérifie que le job existe bien
		$job_existe = $this->db->where('id', $this->input->post('job_id'))
							   ->count_all_results('jobs');

		if ($job_existe == 0)
		{
			$this->echec("Ce job n'existe pas");
			return $this->lister();
		}

		// On poste l'annonce (x fois)
		$data_petites_annonces = array();
		$datetime = bdd_datetime();

		for ($i = 0 ; $i < $this->input->post('nombre') ; $i++)
		{
			$data_petites_annonces[] = array(
				'entreprise_id' => $this->session->userdata('entreprise_id'),
				'job_id'        => $this->input->post('job_id'),
				'message'       => $this->input->post('message'),
				'salaire'       => $this->input->post('salaire'),
				'prime_depart'  => $this->input->post('prime_depart'),
				'date_annonce'  => $datetime,
				'type'          => Bouzouk::PetitesAnnonces_Patron
			);
		}
		$this->db->insert_batch('petites_annonces', $data_petites_annonces);

		// On affiche un message de confirmation
		$this->succes('Ton annonce a bien été postée');
		return $this->lister();
	}

	public function accepter()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('annonce_id', "L'annonce", 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->lister();
		}

		// On vérifie que l'annonce existe
		$query = $this->db->select('j.pseudo, e.nom AS entreprise, p_a.entreprise_id, p_a.job_id, p_a.salaire, p_a.prime_depart, p_a.joueur_id')
						  ->from('petites_annonces p_a')
						  ->join('entreprises e', 'e.id = p_a.entreprise_id')
						  ->join('joueurs j', 'j.id = p_a.joueur_id')
						  ->where('type', Bouzouk::PetitesAnnonces_Patron)
						  ->where('p_a.id', $this->input->post('annonce_id'))
						  ->where('entreprise_id', $this->session->userdata('entreprise_id'))
						  ->where('joueur_id IS NOT NULL')
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cette annonce n'existe pas");
			return $this->lister();
		}

		$annonce = $query->row();

		// On vérifie que le joueur n'est pas déjà employé
		$employe = $this->db->where('joueur_id', $annonce->joueur_id)
							->count_all_results('employes');

		if ($employe)
		{
			$this->echec('Ce joueur a déjà un boulot');
			return $this->lister();
		}

		// On libère les annonces du chômeur
		$this->load->library('lib_entreprise');
		$this->lib_entreprise->liberer_annonces($annonce->joueur_id);
			 
		// On supprime l'annonce acceptée
		$this->db->where('id', $this->input->post('annonce_id'))
				 ->delete('petites_annonces');

		$date = bdd_datetime();

		// On embauche le bouzouk
		$data_employes = array(
			'entreprise_id' => $annonce->entreprise_id,
			'joueur_id'     => $annonce->joueur_id,
			'job_id'        => $annonce->job_id,
			'salaire'       => $annonce->salaire,
			'prime_depart'  => $annonce->prime_depart,
			'date_embauche' => $date,
		);
		$this->db->insert('employes', $data_employes);
		
		// La session doit être mise à jour
		$this->bouzouk->augmente_version_session($annonce->joueur_id);

		// On ajoute à l'historique de l'employé
		$this->bouzouk->historique(141, 142, array(profil(), $annonce->entreprise), $annonce->joueur_id, Bouzouk::Historique_Full);

		// On ajoute à l'historique du patron
		$this->bouzouk->historique(143, null, array(profil($annonce->joueur_id, $annonce->pseudo)));
		
		$this->load->library('lib_notifications');
		// On va chercher la liste des employés
		$query = $this->db->select('joueur_id')
						 ->from('employes e')
						 ->where('entreprise_id', $annonce->entreprise_id)
						 ->get();
		$employes = $query->result();
		foreach ($employes as $employe)
		{
			if ($this->lib_notifications->notifier(Bouzouk::Notification_NouvelEmploye, $employe->joueur_id) && $employe->joueur_id != $annonce->joueur_id)
				$this->bouzouk->notification(238, array(profil($annonce->joueur_id, $annonce->pseudo)), $employe->joueur_id);;
		}

		// On affiche un message de confirmation
		$this->succes('Tu as bien engagé ce bouzouk');
		return $this->lister();
	}

	public function refuser()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('annonce_id', "L'annonce", 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->lister();
		}

		// On vérifie que l'annonce existe
		$query = $this->db->select('p_a.entreprise_id, p_a.joueur_id, j.pseudo, e.nom AS entreprise')
						  ->from('petites_annonces p_a')
						  ->join('entreprises e', 'e.id = p_a.entreprise_id')
						  ->join('joueurs j', 'j.id = p_a.joueur_id')
						  ->where('type', Bouzouk::PetitesAnnonces_Patron)
						  ->where('p_a.id', $this->input->post('annonce_id'))
						  ->where('entreprise_id', $this->session->userdata('entreprise_id'))
						  ->where('joueur_id IS NOT NULL')
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cette annonce n'existe pas");
			return $this->lister();
		}

		$annonce = $query->row();

		// On remet l'annonce en jeu
		$this->db->set('joueur_id', null)
				 ->where('id', $this->input->post('annonce_id'))
				 ->update('petites_annonces');

		// On ajoute à l'historique du chômeur
		$this->bouzouk->historique(144, null, array(profil(), $annonce->entreprise), $annonce->joueur_id, Bouzouk::Historique_Full);
	
		// On ajoute à l'historique du patron
		$this->bouzouk->historique(145, null, array(profil($annonce->joueur_id, $annonce->pseudo)));

		// On affiche un message de confirmation
		$this->succes('Tu as bien refusé ce bouzouk');
		return $this->lister();
	}

	public function retirer()
	{
		// On supprime les annonces
		$this->db->where('type', Bouzouk::PetitesAnnonces_Patron)
				 ->where('entreprise_id', $this->session->userdata('entreprise_id'))
				 ->where('joueur_id IS NULL')
				 ->where_in('id', $this->input->post('annonces_ids'))
				 ->delete('petites_annonces');

		if ($this->db->affected_rows() == 0)
			$this->echec("Aucune annonce n'a été sélectionnée");
		
		else if ($this->db->affected_rows() == 1)
			$this->succes("L'annonce a bien été supprimée");

		else
			$this->succes('Les annonces ont bien été supprimées');
				
		// On affiche un message de confirmation
		return $this->lister();
	}

	public function lister_chomeurs()
	{
		// On va chercher toutes les annonces proposées par les chômeurs (sauf si le patron a déjà proposé aux chômeurs)
		$query = $this->db->query('SELECT c.message, j.id AS chomeur_id, j.pseudo AS chomeur_pseudo, j.experience AS chomeur_experience '.
								  'FROM chomeurs c '.
								  'JOIN joueurs j ON j.id = c.joueur_id '.
								  'WHERE c.joueur_id NOT IN ('.
									'SELECT joueur_id '.
									'FROM petites_annonces '.
									'WHERE type = '.Bouzouk::PetitesAnnonces_Chomeur.' AND entreprise_id = '.$this->session->userdata('entreprise_id').' '.
								  ') '.
								  'ORDER BY c.date desc');
		$annonces = $query->result();

		// On va chercher la liste des jobs pour proposer un poste
		$query = $this->db->select('id, nom, experience')
						  ->from('jobs')
						  ->order_by('experience')
						  ->get();
		$jobs = $query->result();
		
		// On affiche
		$vars = array(
			'annonces'      => $annonces,
			'jobs'          => $jobs,
			'table_smileys' => creer_table_smileys('message')
		);		
		return $this->layout->view('recrutement/lister_chomeurs', $vars);
	}

	public function proposer()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', "Le chômeur", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('job_id', 'Le job', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('salaire', 'Le salaire', 'required|is_natural_no_zero|greater_than_or_equal['.$this->bouzouk->config('entreprises_salaire_min_employe').']|less_than_or_equal['.$this->bouzouk->config('entreprises_salaire_max_employe').']');
		$this->form_validation->set_rules('prime_depart', "La prime d'incompétence", 'required|is_natural|less_than_or_equal['.$this->bouzouk->config('entreprises_prime_max').']');

		if ( ! $this->form_validation->run())
		{
			return $this->lister_chomeurs();
		}

		// On vérifie que le job existe bien
		$job_existe = $this->db->where('id', $this->input->post('job_id'))
							   ->count_all_results('jobs');

		if ($job_existe == 0)
		{
			$this->echec("Ce job n'existe pas");
			return $this->lister();
		}
		
		// On vérifie que ce chômeur existe
		$existe = $this->db->where('joueur_id', $this->input->post('joueur_id'))
						   ->count_all_results('chomeurs');

		if ( ! $existe)
		{
			$this->echec("Ce joueur n'est pas chômeur");
			return $this->lister_chomeurs();
		}

		// On vérifie que le patron n'a pas déjà proposé à ce chômeur
		$existe = $this->db->where('joueur_id', $this->input->post('joueur_id'))
						   ->where('entreprise_id', $this->session->userdata('entreprise_id'))
						   ->where('type', Bouzouk::PetitesAnnonces_Chomeur)
						   ->count_all_results('petites_annonces');
		
		if ($existe)
		{
			$this->echec('Tu as déjà envoyé une proposition à ce chômeur');
			return $this->lister_chomeurs();
		}

		// On regarde si le chômeur n'a pas déjà trop de propositions
		$nb_annonces = $this->db->where('joueur_id', $this->input->post('joueur_id'))
								->where('type', Bouzouk::PetitesAnnonces_Chomeur)
								->count_all_results('petites_annonces');

		if ($nb_annonces >= $this->bouzouk->config('petites_annonces_max_acceptees'))
		{
			$this->echec("Ce bouzouk a déjà reçu trop de propositions, tu dois attendre qu'il en refuse pour pouvoir lui faire une proposition d'embauche");
			return $this->lister_chomeurs();
		}
		
		// On envoie la proposition
		$data_petites_annonces = array(
			'entreprise_id' => $this->session->userdata('entreprise_id'),
			'joueur_id'     => $this->input->post('joueur_id'),
			'job_id'        => $this->input->post('job_id'),
			'salaire'       => $this->input->post('salaire'),
			'prime_depart'  => $this->input->post('prime_depart'),
			'date_annonce'  => bdd_datetime(),
			'type'          => Bouzouk::PetitesAnnonces_Chomeur
		);
		$this->db->insert('petites_annonces', $data_petites_annonces);

		// On envoit une notif au joueur
		$this->bouzouk->notification(146, array(profil()), $this->input->post('joueur_id'));
		
		// On affiche une confirmation
		$this->succes("Ta proposition a bien été envoyée au chômeur, tu seras prévenu si il l'accepte ou la refuse");
		return $this->lister_chomeurs();
	}
}
