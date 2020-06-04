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
 
class Gerer_config extends MY_Controller
{
	private $categories = array(
		1  => 'Jeu',
		2  => 'Boulot',
		3  => 'Communaute',
		4  => 'Controuilles',
		5  => 'Elections',
		6  => 'Entreprise',
		7  => 'Factures',
		8  => 'Historique',
		9  => 'Jeux',
		10 => 'Joueur',
		11 => 'Magasins',
		12 => 'Maintenance',
		13 => 'Maison',
		14 => 'Marche noir',
		15 => 'Mairie',
		16 => 'Mendiants',
		17 => 'Missives',
		18 => 'Mon compte',
		19 => 'Petite annonces',
		20 => 'Plus de struls',
		21 => 'Recrutement',
		22 => 'Plouk',
		23 => 'Clans',
		24 => 'Tobozon',
	);

	public function index()
	{
		// On va chercher toutes les clés/valeurs de configuration
		$query = $this->db->select('cle, valeur, unite, description, categorie')
						  ->from('config')
						  ->order_by('categorie, cle')
						  ->get();
		$configs = $query->result();

		// On affiche
		$vars = array(
			'configs'    => $configs,
			'categories' => $this->categories
		);
		return $this->layout->view('staff/gerer_config.php', $vars);
	}

	public function modifier()
	{
		// On va chercher toutes les clés/valeurs de configuration
		$query = $this->db->select('cle, valeur, unite, description, categorie')
						  ->from('config')
						  ->get();
		$configs = $query->result();

		// Règles de validation
		$this->load->library('form_validation');

		foreach ($configs as $config)
		{
			if ( ! in_array($config->cle, array('jeu_message_header_visiteur', 'jeu_message_header_connecte'))){
				if($config->cle == 'sup_achat'){
					$this->form_validation->set_rules($config->cle, 'supplément achat mairie', 'required|is_natural');
				}
				else{
					$this->form_validation->set_rules($config->cle, $config->cle, 'required');
				}

			}
		}

		if ( ! $this->form_validation->run())
			return $this->index();

		// On effectue certains traitements selon la config choisie
		// Changement du nombre de combinaisons du lohtoh : on rembourse tous les joueurs
		if ($this->input->post('jeux_nb_numeros_a_jouer') != $this->bouzouk->config('jeux_nb_numeros_a_jouer'))
		{
			// On va chercher les numéros déjà joués
			$query = $this->db->select('joueur_id, montant')
							  ->from('loterie')
							  ->get();
			$tickets = $query->result();

			// On rembourse chaque joueur
			foreach ($tickets as $ticket)
				$this->bouzouk->ajouter_struls($ticket->montant, $ticket->joueur_id);

			// On vide la table
			$this->db->truncate('loterie');
		}

		// On enregistre la nouvelle config
		foreach ($configs as $config)
		{
			if ($config->valeur != $this->input->post($config->cle))
			{
				$this->db->set('valeur', $this->input->post($config->cle))
						 ->where('cle', $config->cle)
						 ->update('config');
			}
		}

		// On ajoute à l'historique modération
		$this->bouzouk->historique_moderation(profil().' a modifié la configuration du jeu');
		
		// On met le cache à jour
		$this->lib_cache->config(true);
		
		// On affiche un message de confirmation
		$this->succes('La configuration du jeu a bien été modifiée. La FAQ a été mise à jour.');
		redirect('staff/gerer_config');
	}
}