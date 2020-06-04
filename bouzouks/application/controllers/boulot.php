<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : gestion du boulot côté employé
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Boulot extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		
		// ---------- Hook clans ----------
		// Malediction du Schnibble (SDS)
		if ($this->session->userdata('maudit') && ! $this->bouzouk->is_admin())
			redirect('clans/maudit');
	}
	
	public function gerer()
	{
		// On va chercher les infos de l'entreprise
		$query = $this->db->select('j.pseudo AS pseudo_chef, e.nom, e.date_creation, e.struls, e.chef_id, e.salaire_chef, e.message_1, e.message_2, e.historique_publique, e.syndicats_autorises, c_e.position, c_e.evolution, o.nom AS nom_objet, o.image_url')
						  ->from('entreprises e')
						  ->join('objets o', 'o.id = e.objet_id')
						  ->join('joueurs j', 'j.id = e.chef_id')
						  ->join('classement_entreprises c_e', 'c_e.entreprise_id = e.id', 'left')
						  ->where('e.id', $this->session->userdata('entreprise_id'))
						  ->get();
		$entreprise = $query->row();

		// On va chercher la liste des employés
		$query = $this->db->select('j.id, j.pseudo, j.rang, j.statut')
						 ->from('employes e')
						 ->join('joueurs j', 'j.id = e.joueur_id')
						 ->where('e.entreprise_id', $this->session->userdata('entreprise_id'))
						 ->order_by('j.pseudo')
						 ->get();
		$nb_employes = $query->num_rows();
		$employes = $query->result();

		// On va chercher les infos de l'employé
		$query = $this->db->select('j.nom, e.salaire, e.anciennete, e.prime_depart, e.dernier_salaire, e.payer, e.dernier_bonus')
					  ->from('employes e')
					  ->join('jobs j', 'j.id = e.job_id')
					  ->where('e.joueur_id', $this->session->userdata('id'))
					  ->where('e.entreprise_id', $this->session->userdata('entreprise_id'))
					  ->get();
		$job = $query->row();

		// On compte le nombre de syndicats
		$nb_syndicats = $this->db->where('type', Bouzouk::Clans_TypeSyndicat)
								 ->where('entreprise_id', $this->session->userdata('entreprise_id'))
								 ->count_all_results('clans');

		// On affiche
		$vars = array(
			'entreprise'    => $entreprise,
			'employes'      => $employes,
			'nb_employes'   => $nb_employes,
			'job'           => $job,
			'nb_syndicats'  => $nb_syndicats,
			'table_smileys' => creer_table_smileys('message')
		);
		return $this->layout->view('boulot/gerer', $vars);
	}

	public function historique()
	{
		// On récupère l'historique de l'entreprise
		$query = $this->db->select('date, nb_employes, impots, rentree_argent, salaires_employes, salaire_patron, pourcent_achats, struls')
						  ->from('historique_entreprises')
						  ->where('entreprise_id', $this->session->userdata('entreprise_id'))
						  ->order_by('date', 'desc')
						  ->get();
		$historiques = $query->result();

		// On va chercher quelques infos de l'entreprise
		$query = $this->db->select('historique_publique, syndicats_autorises')
						  ->from('entreprises')
						  ->where('id', $this->session->userdata('entreprise_id'))
						  ->get();
		$entreprise = $query->row();

		// On affiche
		$vars = array(
			'historiques' => $historiques,
			'entreprise'  => $entreprise
		);
		return $this->layout->view('boulot/historique', $vars);
	}
	
	public function demissionner()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('demissionner', "L'appui sur le bouton <span class='pourpre'>Démissionner</span>", 'required');

		if ( ! $this->form_validation->run())
		{
			return $this->gerer();
		}

		$this->load->library('lib_missive');

		// On va chercher les infos du joueur
		$query = $this->db->select('j.id AS patron_id, j.pseudo AS patron, em.date_embauche, em.anciennete, en.nom')
						  ->from('employes em')
						  ->join('entreprises en', 'en.id = em.entreprise_id')
						  ->join('joueurs j', 'j.id = en.chef_id')
						  ->where('em.joueur_id', $this->session->userdata('id'))
						  ->where('em.entreprise_id', $this->session->userdata('entreprise_id'))
						  ->get();
		$employe = $query->row();
		$attente = $this->bouzouk->config('boulot_attente_embauche');

		// On vérifie que le joueur a assez d'ancienneté
		if (strtotime(bdd_datetime()) < strtotime($employe->date_embauche."+$attente HOUR"))
		{
			$this->echec('Tu dois attendre <span class="pourpre">'.$attente.'h</span> après ton embauche pour démissionner');
			return $this->gerer();
		}
		
		// On retire l'employé de l'entreprise
		$this->db->where('joueur_id', $this->session->userdata('id'))
				 ->where('entreprise_id', $this->session->userdata('entreprise_id'))
				 ->delete('employes');

		// On quitte le syndicat
		$this->load->library('lib_clans');
		$this->lib_clans->quitter_syndicat($this->session->userdata('id'));
		
		// On supprime les demandes de recrutement en syndicat
		$query = $this->db->select('id')
						  ->from('clans')
						  ->where('entreprise_id', $this->session->userdata('entreprise_id'))
						  ->get();
		$syndicats = $query->result();

		foreach ($syndicats as $syndicat)
		{
			$this->db->where('clan_id', $syndicat->id)
					 ->where('joueur_id', $this->session->userdata('id'))
					 ->where('refuse', 0)
					 ->delete('clans_recrutement');
		}

		// On envoie une missive au patron
		$message  = "	Bonjour $employe->patron\n\n".
					"Nous avons le regret de t'informer que ton employé ".profil()." vient de démissionner.\n\n".
					"	Amicalement, le ministère des démissions de Vlurxtrznbnaxl.";
		$this->lib_missive->envoyer_missive(Bouzouk::Robot_Emploi, $employe->patron_id, "Démission d'un employé", $message);

		// On retire de l'expérience au joueur en fonction de son ancienneté et de son expérience
		$perte_xp = $this->bouzouk->config('boulot_perte_xp_demission');
		$this->bouzouk->retirer_experience($perte_xp);

		// La session doit être mise à jour
		$this->bouzouk->augmente_version_session();

		// On ajoute à l'historique du patron
		$this->bouzouk->historique(19, null, array(profil()), $employe->patron_id);

		// On envoit une notif au patron
		if ($this->bouzouk->est_connecte($employe->patron_id))
			$this->bouzouk->notification(22, array(), $employe->patron_id);

		// On ajoute à l'historique de l'employé
		$message = "Tu as démissionné de l'entreprise <span class='pourpre'>$employe->nom</span>, tu perds <span class='pourpre'>-$perte_xp xp</span>";
		$this->bouzouk->historique(20, 21, array($employe->nom, $perte_xp));
		
		// On affiche un message de confirmation
		$this->succes($message.' et tu es maintenant un valeureux chômeur :)');
		redirect('joueur');
	}
}
