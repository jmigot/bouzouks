<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : modération/administration du site
 *
 * Auteur      : Fabien Foixet (fabien@foixet.com)
 * Date        : mars 2014
 *
 * Copyright (C) 2012-2014 Fabien Foixet - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Convoquer_joueur extends MY_Controller
{
	public function index()
	{
		// On va chercher toutes les convocations
		$query = $this->db->select('c.id, c.date, c.etat, jc.id AS convoque_id, jc.pseudo AS convoque_pseudo, jc.rang AS convoque_rang, jm.id AS moderateur_id, jm.pseudo AS moderateur_pseudo, jm.rang AS moderateur_rang')
						  ->from('convocations_moderation c')
						  ->join('joueurs jc', 'jc.id = c.convoque_id')
						  ->join('joueurs jm', 'jm.id = c.moderateur_id')
						  ->order_by('c.etat', 'DESC')
						  ->order_by('c.date', 'DESC')
						  ->limit(20)
						  ->get();
		$convocations = $query->result();

		// On affiche
		$vars = array(
			'select_joueurs'  => $this->bouzouk->select_joueurs(array('status_not_in' => array(Bouzouk::Joueur_Etudiant, Bouzouk::Joueur_ChoixPerso, Bouzouk::Joueur_GameOver, Bouzouk::Joueur_Robot, Bouzouk::Joueur_Banni), 'champ_texte' => true)),
			'convocations'    => $convocations,
		);
		return $this->layout->view('staff/convoquer_joueur', $vars);
	}
	
	public function convoquer()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'is_natural_no_zero');
		$this->form_validation->set_rules('joueur_id_pseudo', 'Le pseudo', 'min_length[3]|max_length[20]');

		if ( ! $this->form_validation->run())
			return $this->index();
		
		// On va chercher les infos du joueur
		$this->db->select('id, pseudo')
				 ->from('joueurs');

		if ($this->input->post('joueur_id_pseudo') != false)
			$this->db->where('pseudo', $this->input->post('joueur_id_pseudo'));
		else
			$this->db->where('id', $this->input->post('joueur_id'));

		$query = $this->db->where_not_in('statut', array(Bouzouk::Joueur_Robot))
						  ->get();

		// On vérifie que le compte existe
		if ($query->num_rows() == 0)
		{
			$this->echec("Ce joueur n'existe pas");
			return $this->index();
		}

		$joueur = $query->row();
		
		// On le convoque
		$data_convoque = array(
			'convoque_id'		=> $joueur->id,
			'moderateur_id' => $this->session->userdata('id'),
			'date'			=> bdd_datetime(),
			'etat'			=> 1
		);
		$this->db->insert('convocations_moderation', $data_convoque);
		
		$convoque_id = $this->db->insert_id();
		
		// On met à jour sa session
		$this->bouzouk->augmente_version_session($joueur->id);
	
		// On lui envoit une notif
		$this->bouzouk->historique(239, null, array(profil(-1, '', $this->session->userdata('rang'))), $joueur->id, Bouzouk::Historique_Full);
		
		// Historique modération
		$this->bouzouk->historique_moderation(profil()." a convoqué ".get_profil($joueur->id));
		
		// On redirige vers la convoque
		return redirect('convocation/index/'.$convoque_id);
	}
	
	public function fin_convocation($convocation_id)
	{
		// On va chercher des information sur cette convocation
		$query = $this->db->select('c.id, c.date, c.etat, jc.id AS convoque_id, jc.pseudo AS convoque_pseudo, jc.rang AS convoque_rang, jm.id AS moderateur_id, jm.pseudo AS moderateur_pseudo, jm.rang AS moderateur_rang')
						  ->from('convocations_moderation c')
						  ->join('joueurs jc', 'jc.id = c.convoque_id')
						  ->join('joueurs jm', 'jm.id = c.moderateur_id')
						  ->where('c.id', (int)$convocation_id)
						  ->get();
		
		if($query->num_rows() == 0)
			redirect('staff/convoquer_joueur');
		
		$convocation = $query->row();
		
		$this->db->set('etat', 0)
					 ->where('id', $convocation_id)
					 ->update('convocations_moderation');
		
		// On envoit une notif au convoqué
		$this->bouzouk->notification(240, array(), $convocation->convoque_id);
		
		// On met à jour sa session
		$this->bouzouk->augmente_version_session($convocation->convoque_id);
		
		$this->succes('La convocation avec '.$convocation->convoque_pseudo.' est terminée');
		return redirect('staff/convoquer_joueur');
	}
}