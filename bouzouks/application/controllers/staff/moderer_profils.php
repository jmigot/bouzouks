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
 
class Moderer_profils extends MY_Controller
{
	public function index($offset = '0')
	{
		$select_joueurs = $this->bouzouk->select_joueurs();

		$vars = array(
			'select_joueurs' => $this->bouzouk->select_joueurs(array('status_not_in' => array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_Pause, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Robot, Bouzouk::Joueur_Banni), 'champ_texte' => true))
		);
		return $this->layout->view('staff/moderer_profils', $vars);
	}

	public function voir($joueur_id = null)
	{
		if ( ! isset($joueur_id))
		{
			// Règles de validation
			$this->load->library('form_validation');
			$this->form_validation->set_rules('joueur_id', 'Le joueur', 'is_natural_no_zero');
			$this->form_validation->set_rules('joueur_id_pseudo', 'Le pseudo', 'min_length[3]|max_length[20]');

			if ( ! $this->form_validation->run())
			{
				return $this->index();
			}
		}

		// On va chercher les infos du joueur
		$this->db->select('j1.id, j1.pseudo, j1.adresse, j1.commentaire, j1.statut, j1.raison_statut, j1.rang, j1.statut_staff_id, j2.pseudo AS statut_staff_pseudo,
						   j2.rang AS statut_staff_rang, j1.nb_asile, j1.interdit_missives, j1.interdit_tchat, j1.interdit_plouk, j1.interdit_avatar, j1.duree_asile')
				 ->from('joueurs j1')
				 ->join('joueurs j2', 'j2.id = j1.statut_staff_id', 'left');

		if ($this->input->post('joueur_id_pseudo') != false)
			$this->db->where('j1.pseudo', $this->input->post('joueur_id_pseudo'));

		else
			$this->db->where('j1.id', $this->input->post('joueur_id'));
			
		$query = $this->db->where_not_in('j1.statut', array(Bouzouk::Joueur_Inactif, Bouzouk::Joueur_Robot))
						  ->get();

		// On vérifie que le compte existe
		if ($query->num_rows() == 0)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->index();
		}

		$joueur = $query->row();
		$select_joueurs = $this->bouzouk->select_joueurs(array('status_not_in' => array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_Pause, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Robot, Bouzouk::Joueur_Banni), 'champ_texte' => true));

		$vars = array(
			'select_joueurs' => $select_joueurs,
			'joueur'         => $joueur
		);
		return $this->layout->view('staff/moderer_profils', $vars);
	}

	public function modifier()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('adresse', "L'adresse", 'required|min_length[15]|max_length[50]|regex_match[#^[a-zA-Z0-9éèàâêôîù ,\'-]+$#]');
		$this->form_validation->set_rules('commentaire', 'Le commentaire', 'max_length[150]');
		$this->form_validation->set_rules('duree_asile', "La durée à l'asile", 'required|is_natural|less_than_or_equal[48]');
		$this->form_validation->set_rules('statut', 'Le statut', 'required|is_natural');
		$this->form_validation->set_rules('raison_statut', 'La raison du statut', 'max_length[250]');

		if ( ! $this->form_validation->run())
		{
			return $this->voir();
		}

		// La mise à l'asile ou le ban doit être accompagnée d'une raison
		if (in_array($this->input->post('statut'), array(Bouzouk::Joueur_Asile)) && $this->input->post('raison_statut') == '')
		{
			$this->echec("Il faut donner une raison pour un statut asile");
			return $this->voir();
		}
		
		// Le joueur ne peut mettre que actif ou asile
		if ( ! in_array($this->input->post('statut'), array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile)))
		{
			$this->echec("Tu ne peux mettre un joueur qu'en actif ou à l'asile");
			return $this->voir();
		}
		
		// On va chercher les infos du joueur
		$query = $this->db->select('id, pseudo, statut, rang, duree_asile, interdit_plouk, interdit_tchat, interdit_missives, interdit_avatar, adresse, commentaire')
						  ->from('joueurs')
						  ->where('id', $this->input->post('joueur_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->voir();
		}

		$joueur = $query->row();

		// La durée de l'asile > 0 doit être accompagnée d'une mise à l'asile
		if ($joueur->statut != Bouzouk::Joueur_Asile && $this->input->post('duree_asile') > 0 && $this->input->post('statut') != Bouzouk::Joueur_Asile)
		{
			$this->echec("Tu dois mettre le joueur à l'asile pour pouvoir indiquer une durée");
			return $this->voir();
		}
		
		// On construit l'historique des modifications
		$historique = '';
		
		foreach (array('duree_asile' => 'h') as $cle => $unite)
		{
			if ($joueur->{$cle} != $this->input->post($cle))
				$historique .= '- '.str_replace('_', ' ', $cle).' : '.$joueur->{$cle}.$unite.' -&gt; '.$this->input->post($cle).$unite.'<br>';
		}

		foreach (array('interdit_missives', 'interdit_plouk', 'interdit_tchat', 'interdit_avatar') as $cle)
		{
			$avant = $joueur->{$cle} ? 'oui' : 'non';
			$apres = $this->input->post($cle) ? 'oui' : 'non';

			if ($joueur->{$cle} != $this->input->post($cle))
				$historique .= '- '.str_replace('_', ' ', $cle)." : $avant -&gt; $apres<br>";
		}

		foreach (array('adresse', 'commentaire') as $cle)
		{
			if ($joueur->{$cle} != $this->input->post($cle))
				$historique .= '- '.$cle.' : modifié<br>';
		}

		// On effectue les modifications
		$data_joueurs = array(
			'adresse'           => $this->input->post('adresse'),
			'commentaire'       => $this->input->post('commentaire'),
			'statut'            => $this->input->post('statut'),
			'raison_statut'     => $this->input->post('raison_statut'),
			'duree_asile'       => $this->input->post('duree_asile'),
			'interdit_missives' => $this->input->post('interdit_missives') != false ? 1 : 0,
			'interdit_tchat'    => $this->input->post('interdit_tchat') != false ? 1 : 0,
			'interdit_plouk'    => $this->input->post('interdit_plouk') != false ? 1 : 0,
			'interdit_avatar'   => $this->input->post('interdit_avatar') != false ? 1 : 0,
		);
		$this->db->where('id', $this->input->post('joueur_id'))
				 ->update('joueurs', $data_joueurs);

		// On applique des règles selon le statut
		$this->load->library('lib_joueur');
		$statut = $this->input->post('statut');

		if ($statut == Bouzouk::Joueur_Actif && $joueur->statut != Bouzouk::Joueur_Actif)
		{
			$this->db->set('raison_statut', '')
					 ->set('duree_asile', 0)
					 ->where('id', $this->input->post('joueur_id'))
					 ->update('joueurs');

			// On change son statut sur le tobozon
			$this->db->set('title', '')
					 ->where('id', $this->input->post('joueur_id'))
					 ->update('tobozon_users');
		}

		else if ($statut == Bouzouk::Joueur_Asile && $joueur->statut != Bouzouk::Joueur_Asile)
		{
			$this->lib_joueur->mettre_asile($this->input->post('joueur_id'), $this->input->post('raison_statut'), $this->input->post('duree_asile'), $this->session->userdata('id'));

			// On augmente le nombre de fois où le joueur a été envoyé à l'asile
			$this->db->set('nb_asile', 'nb_asile+1', false)
					 ->where('id', $this->input->post('joueur_id'))
					 ->update('joueurs');
		}

		// On ajoute à l'historique modération
		$this->bouzouk->historique_moderation(profil().' a modifié le profil de '.profil($joueur->id, $joueur->pseudo).' :<br>'.$historique);

		// On ajoute à l'historique du joueur
		if ($historique != '')
			$this->bouzouk->historique(34, null, array(profil(-1, '', $this->session->userdata('rang')), $historique), $joueur->id, Bouzouk::Historique_Full);
			
		$this->bouzouk->augmente_version_session($joueur->id);

		// On affiche un message de confirmation
		$this->succes(profil($joueur->id, $joueur->pseudo, $joueur->rang).' a bien été modifié');
		return $this->voir();
	}
}

