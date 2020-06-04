<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : petites annonces pour trouver un job
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

class Anpe extends MY_Controller
{
	public function index()
	{
		return $this->rechercher();
	}
	
	public function rechercher()
	{
		// On va chercher la liste des jobs possibles pour le joueur
		$query = $this->db->select('id, nom')
						  ->from('jobs')
						  ->where('experience <=', $this->session->userdata('experience'))
						  ->get();
		$jobs = $query->result();
		
		// On va chercher la liste des entreprises existantes (qui ont au moins une annonce)
		$query = $this->db->select('e.id, e.nom, j.pseudo')
						  ->from('entreprises e')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->join('petites_annonces p_a', 'p_a.entreprise_id = e.id')
						  ->group_by('e.id')
						  ->order_by('e.nom')
						  ->get();
		$entreprises = $query->result();

		$vars = array(
			'jobs'        => $jobs,
			'entreprises' => $entreprises
		);
		$this->layout->view('anpe/rechercher', $vars);
	}

	public function lister($offset = '0')
	{
		// Si de nouvelles données POST sont là, le joueur a modifié les filtres
		if ($this->input->isPost())
		{
			// Règles de validation
			$this->load->library('form_validation');
			$this->form_validation->set_rules('job_id', 'Le job', 'is_natural_no_zero');
			$this->form_validation->set_rules('entreprise_id', "L'entreprise", 'is_natural_no_zero');
			$this->form_validation->set_rules('salaire_minimum', 'Le salaire', 'is_natural|less_than_or_equal['.$this->bouzouk->config('entreprises_salaire_max_employe').']');

			if ( ! $this->form_validation->run())
			{
				return $this->rechercher();
			}
		
			// On vérifie que le job existe et est valide pour le joueur
			if ($this->input->post('job_id') !== false AND entier_naturel_positif($this->input->post('job_id')))
			{
				$job_existe = $this->db->where('id', $this->input->post('job_id'))
									->where('experience <=', $this->session->userdata('experience'))
									->count_all_results('jobs');

				if ($job_existe == '0')
				{
					$this->echec('Le job est invalide');
					return $this->rechercher();
				}

				$this->session->set_userdata('petites_annonces_job_id', $this->input->post('job_id'));
			}

			else
				$this->session->set_userdata('petites_annonces_job_id', 0);

			// On vérifie que l'entreprise existe
			if ($this->input->post('entreprise_id') !== false AND entier_naturel_positif($this->input->post('entreprise_id')))
			{
				$entreprise_existe = $this->db->where('id', $this->input->post('entreprise_id'))
											->count_all_results('entreprises');

				if ($entreprise_existe == '0')
				{
					$this->echec("Cette entreprise n'existe pas");
					return $this->rechercher();
				}

				$this->session->set_userdata('petites_annonces_entreprise_id', $this->input->post('entreprise_id'));
			}

			else
				$this->session->set_userdata('petites_annonces_entreprise_id', 0);

			if (entier_naturel_positif($this->input->post('salaire_minimum')))
				$this->session->set_userdata('petites_annonces_salaire', $this->input->post('salaire_minimum'));
				
			else
				$this->session->set_userdata('petites_annonces_salaire', 0);
		}

		$job_id        = $this->session->userdata('petites_annonces_job_id');
		$entreprise_id = $this->session->userdata('petites_annonces_entreprise_id');
		$salaire       = $this->session->userdata('petites_annonces_salaire');

		// On va chercher toutes les petites annonces correspondantes aux critères
		$this->db->select('e.nom AS entreprise, e.syndicats_autorises, j.nom AS job, p_a.id, p_a.salaire, p_a.prime_depart')
				 ->from('petites_annonces p_a')
				 ->join('entreprises e', 'e.id = p_a.entreprise_id')
				 ->join('jobs j', 'j.id = p_a.job_id')
				 ->where('p_a.type', Bouzouk::PetitesAnnonces_Patron)
				 ->where('p_a.joueur_id IS NULL');

		// Filtre sur le job ?
		if ($job_id > 0)
			$this->db->where('p_a.job_id', $job_id);

		else
			$this->db->where('j.experience <=', $this->session->userdata('experience'));

		// Filtre sur l'entreprise ?
		if ($entreprise_id)
			$this->db->where('p_a.entreprise_id', $entreprise_id);

		// Filtre sur le salaire minimum ?
		if ($salaire > 0)
			$this->db->where('p_a.salaire >=', $salaire);

		$query = $this->db->order_by('j.experience', 'desc')
						  ->order_by('p_a.salaire', 'desc')
						  ->order_by('p_a.id', 'random')
						  ->get();
  		$nb_annonces = $query->num_rows();
		$annonces_completes = $query->result();

		// Pagination
		$pagination = creer_pagination('anpe/lister', $nb_annonces, 30, $offset);

		// On ne récupère que les résultats qui nous intéressent
 		$annonces = array();
 		for ($i = $offset ; $i <= $offset + $pagination['par_page'] ; $i++)
 		{
			if (isset($annonces_completes[$i]))
				$annonces[] = $annonces_completes[$i];
		}
 		
		// On affiche les annonces
		$vars = array(
			'annonces'   => $annonces,
			'pagination' => $pagination['liens']
		);
		return $this->layout->view('anpe/lister', $vars);
	}

	public function voir($annonce_id)
	{
		if ( ! entier_naturel_positif($annonce_id))
		{
			show_404();
		}

		// On va chercher les infos de l'annonce
		$query = $this->db->select('p_a.id AS annonce_id, p_a.salaire, p_a.prime_depart, p_a.message, jobs.nom AS job, e.id AS entreprise_id, e.nom, e.date_creation, e.syndicats_autorises, j.id AS chef_id, j.pseudo AS chef, o.nom AS objet, o.image_url AS objet_image_url')
						  ->from('petites_annonces p_a')
						  ->join('jobs', 'jobs.id = p_a.job_id')
						  ->join('entreprises e', 'e.id = p_a.entreprise_id')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->join('objets o', 'o.id = e.objet_id')
 						  ->where('p_a.type', Bouzouk::PetitesAnnonces_Patron)
						  ->where('p_a.id', $annonce_id)
						  ->get();

		// Si l'annonce n'existe pas
		if ($query->num_rows() == 0)
		{
			$this->echec("Cette annonce n'existe pas");
			return $this->rechercher();
		}

		$job = $query->row();

		// On va chercher le nombre d'employes
		$nb_employes = $this->db->from('employes')
								->where('entreprise_id', $job->entreprise_id)
								->count_all_results();

		$job->nb_employes = $nb_employes;

		$vars= array(
			'job' => $job
		);
		return $this->layout->view('anpe/voir', $vars);
	}

	public function proposer()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('annonce_id', "L'annonce", 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->rechercher();
		}

		// On vérifie que l'annonce existe bien
		$query = $this->db->select('e.chef_id, e.nom, j.pseudo AS chef, p_a.entreprise_id, p_a.salaire, p_a.prime_depart')
						  ->from('petites_annonces p_a')
						  ->join('entreprises e', 'e.id = p_a.entreprise_id')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->where('type', Bouzouk::PetitesAnnonces_Patron)
						  ->where('p_a.id', $this->input->post('annonce_id'))
						  ->where('p_a.joueur_id IS NULL')
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cette annonce n'existe pas");
			return $this->rechercher();
		}

		$annonce = $query->row();

		// On regarde si le joueur n'a pas déjà accepté une annonce de ce patron
		$deja_accepte = $this->db->where('type', Bouzouk::PetitesAnnonces_Patron)
								 ->where('entreprise_id', $annonce->entreprise_id)
								 ->where('joueur_id', $this->session->userdata('id'))
								 ->count_all_results('petites_annonces');

		if ($deja_accepte > 0)
		{
			$this->echec('Tu as déjà candidaté pour une annonce dans cette entreprise, tu dois attendre la décision du patron pour repostuler chez eux');
			return $this->rechercher();
		}

		// On regarde si le joueur n'a pas trop d'annonces acceptées
		$nb_annonces = $this->db->where('type', Bouzouk::PetitesAnnonces_Patron)
								->where('joueur_id', $this->session->userdata('id'))
								->count_all_results('petites_annonces');

		if ($nb_annonces >= $this->bouzouk->config('petites_annonces_max_acceptees'))
		{
			$this->echec("Tu as déjà candidaté pour <span class='pourpre'>".pluriel($nb_annonces, 'annonce')."</span>, tu dois attendre qu'un patron t'accepte ou te refuse pour faire d'autres demandes");
			return $this->rechercher();
		}

		// On marque l'annonce en attente
		$this->db->set('joueur_id', $this->session->userdata('id'))
				 ->where('id', $this->input->post('annonce_id'))
				 ->update('petites_annonces');
				 
		// On ajoute à l'historique du patron
		$this->bouzouk->historique(10, null, array(profil(), struls($annonce->salaire), struls($annonce->prime_depart)), $annonce->chef_id);
		$this->bouzouk->notification(15, array(profil()), $annonce->chef_id);
		
		// On ajoute à l'historique du chômeur
		$this->bouzouk->historique(11, null, array($annonce->nom, struls($annonce->salaire), struls($annonce->prime_depart)));

		// On affiche un message de confirmation
		$this->succes('Tu as candidaté pour ce job, tu dois attendre que ton patron accepte ou refuse ta candidature. Si il ne se décide pas, tu peux toujours rejoindre une autre entreprise.');
		return $this->mes_annonces();
	}

	public function accepter()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('annonce_id', "L'annonce", 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->mes_annonces();
		}
		
		// On vérifie que l'annonce est valide
		$query = $this->db->select('j.id AS chef_id, j.pseudo AS chef_pseudo, e.nom AS entreprise, pa.entreprise_id, pa.job_id, pa.salaire, pa.prime_depart, pa.joueur_id')
						  ->from('petites_annonces pa')
						  ->join('entreprises e', 'e.id = pa.entreprise_id')
						  ->join('joueurs j', 'j.id = e.chef_id')
  						  ->where('pa.type', Bouzouk::PetitesAnnonces_Chomeur)
						  ->where('pa.id', $this->input->post('annonce_id'))
						  ->where('pa.joueur_id', $this->session->userdata('id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cette annonce n'existe pas");
			return $this->mes_annonces();
		}

		$annonce = $query->row();
		
		// On supprime l'annonce du chômeur
		$this->load->library('lib_entreprise');
		$this->lib_entreprise->liberer_annonces($this->session->userdata('id'));
		
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
		$this->bouzouk->augmente_version_session();

		// On ajoute à l'historique de l'employé
		$this->bouzouk->historique(12, 13, array(profil($annonce->chef_id, $annonce->chef_pseudo), $annonce->entreprise));

		// On ajoute à l'historique du patron
		$this->bouzouk->historique(14, null, array(profil()), $annonce->chef_id);
		
		$this->load->library('lib_notifications');
		// On envoit une notification au patron
		if ($this->lib_notifications->notifier(Bouzouk::Notification_NouvelEmploye, $annonce->chef_id))
			$this->bouzouk->notification(16, array(profil()), $annonce->chef_id);
		
		// On va chercher la liste des employés
		$query = $this->db->select('joueur_id')
						 ->from('employes e')
						 ->where('entreprise_id', $annonce->entreprise_id)
						 ->get();
		$employes = $query->result();
		foreach ($employes as $employe)
		{
			if ($this->lib_notifications->notifier(Bouzouk::Notification_NouvelEmploye, $employe->joueur_id) && $employe->joueur_id != $this->session->userdata('id'))
				$this->bouzouk->notification(238, array(profil()), $employe->joueur_id);;
		}
		
		// On affiche une confirmation
		$this->succes('Tu as bien été embauché');
		redirect('boulot/gerer');
	}
	
	public function refuser()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('annonce_id', "L'annonce", 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
		{
			return $this->rechercher();
		}

		// On vérifie que l'annonce est valide
		$query = $this->db->select('e.chef_id')
						 ->from('petites_annonces pa')
						 ->join('entreprises e', 'e.id = pa.entreprise_id')
						 ->where('pa.type', Bouzouk::PetitesAnnonces_Chomeur)
						 ->where('pa.id', $this->input->post('annonce_id'))
						 ->where('pa.joueur_id', $this->session->userdata('id'))
						 ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cette annonce n'existe pas");
			return $this->mes_annonces();
		}

		$annonce = $query->row();
		
		// On supprime l'annonce
		$this->db->where('id', $this->input->post('annonce_id'))
				 ->delete('petites_annonces');

		// On envoit une notif au patron
		$this->bouzouk->notification(17, array(profil()), $annonce->chef_id);
		
		// On affiche un message de confirmation
		$this->succes("Tu as refusé cette proposition");
		return $this->mes_annonces();
	}
	
	public function mes_annonces()
	{
		// On regarde si le joueur a une annonce en cours
		$query = $this->db->select('message')
						  ->from('chomeurs')
						  ->where('joueur_id', $this->session->userdata('id'))
						  ->get();
		$mon_annonce = ($query->num_rows() == 0) ? null : $query->row();
		
		// On va chercher les petites annonces proposées par des patrons
		$query = $this->db->select('pa.id, e.nom AS entreprise, j.id AS patron_id, j.pseudo AS patron_pseudo, jobs.nom AS job, pa.salaire, pa.prime_depart')
						  ->from('petites_annonces pa')
						  ->join('entreprises e', 'e.id = pa.entreprise_id')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->join('jobs', 'jobs.id = pa.job_id')
						  ->where('pa.type', Bouzouk::PetitesAnnonces_Chomeur)
						  ->where('pa.joueur_id', $this->session->userdata('id'))
						  ->order_by('pa.date_annonce', 'desc')
						  ->get();
		$annonces_proposees = $query->result();

		// On va chercher les petites annonces acceptées par le chômeur
		$query = $this->db->select('e.nom AS entreprise, j.id AS patron_id, j.pseudo AS patron_pseudo, jobs.nom AS job, pa.salaire, pa.prime_depart')
						  ->from('petites_annonces pa')
						  ->join('entreprises e', 'e.id = pa.entreprise_id')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->join('jobs', 'jobs.id = pa.job_id')
						  ->where('pa.type', Bouzouk::PetitesAnnonces_Patron)
						  ->where('pa.joueur_id', $this->session->userdata('id'))
						  ->order_by('pa.date_annonce', 'desc')
						  ->get();
		$annonces_acceptees = $query->result();
		
		// On affiche
		$vars = array(
			'mon_annonce'        => $mon_annonce,
			'annonces_proposees' => $annonces_proposees,
			'annonces_acceptees' => $annonces_acceptees
		);
		return $this->layout->view('anpe/mes_annonces', $vars);
	}

	public function poster()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('message', 'Le message', 'required|min_length[5]|max_length[250]');

		if ( ! $this->form_validation->run())
		{
			return $this->mes_annonces();
		}

		// On regarde si l'annonce existe
		$existe = $this->db->where('joueur_id', $this->session->userdata('id'))
						   ->count_all_results('chomeurs');

		// Nouvelle annonce
		if ( ! $existe)
		{
			$data_chomeurs = array(
				'joueur_id' => $this->session->userdata('id'),
				'message'   => $this->input->post('message'),
				'date'      => bdd_datetime()
			);
			$this->db->insert('chomeurs', $data_chomeurs);

			// On récupère tous les patrons
			$query = $this->db->select('chef_id')
							->from('entreprises')
							->get();
			$patrons = $query->result();
			
			$this->load->library('lib_notifications');
			
			// On prévient chaque patron
			foreach ($patrons as $patron)
			{
				if ($this->lib_notifications->notifier(Bouzouk::Notification_AnnonceANPC, $patron->chef_id))
					$this->bouzouk->notification(18, array(profil()), $patron->chef_id);
			}

			// Message de confirmation
			$this->succes('Ton annonce a bien été postée, tu seras prévenu quand un patron te fera une proposition');
		}

		// Modification
		else
		{
			$this->db->set('message', $this->input->post('message'))
					 ->where('joueur_id', $this->session->userdata('id'))
					 ->update('chomeurs');

			// Message de confirmation
			$this->succes('Ton annonce a bien été modifée');
		}
		
		// On affiche un message de confirmation
		return $this->mes_annonces();
	}

	public function supprimer()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('supprimer', 'Le bouton supprimer', 'required');

		if ( ! $this->form_validation->run())
		{
			return $this->mes_annonces();
		}
		
		// On supprime l'annonce du chômeur
		$this->db->where('joueur_id', $this->session->userdata('id'))
				 ->delete('chomeurs');

		// On récupère les patrons qui ont proposé à ce chômeur
		$query = $this->db->select('e.chef_id')
						  ->from('petites_annonces pa')
						  ->join('entreprises e', 'e.id = pa.entreprise_id')
						  ->where('pa.type', Bouzouk::PetitesAnnonces_Chomeur)
						  ->where('pa.joueur_id', $this->session->userdata('id'))
						  ->get();
		$patrons = $query->result();

		// On supprime toutes les propositions des entreprises
		$this->db->where('type', Bouzouk::PetitesAnnonces_Chomeur)
				 ->where('joueur_id', $this->session->userdata('id'))
				 ->delete('petites_annonces');

		// On envoit une notif aux patron
		foreach ($patrons as $patron)
			$this->bouzouk->notification(17, array(profil()), $patron->chef_id);
			
		// On affiche un message
		$this->succes('Ton annonce a bien été supprimée, ainsi que toutes les propositions des patrons');
		return $this->mes_annonces();
	}
	
	public function machine_a_cafe()
	{
		// On va chercher tous les chômeurs
		$query = $this->db->select('j.id, j.pseudo, j.rang')
						  ->from('joueurs j')
						  ->join('employes em', 'em.joueur_id = j.id', 'left')
						  ->join('entreprises en', 'en.chef_id = j.id', 'left')
						  ->where_in('j.statut', array(Bouzouk::Joueur_Actif))
						  ->where('em.joueur_id IS NULL')
						  ->where('en.chef_id IS NULL')
						  ->order_by('j.pseudo')
						  ->get();
		$chomeurs = $query->result();

		// On affiche		
		$vars = array(
			'chomeurs'      => $chomeurs,
			'table_smileys' => creer_table_smileys('message')
		);		
		return $this->layout->view('anpe/machine_a_cafe', $vars);
	}
}
