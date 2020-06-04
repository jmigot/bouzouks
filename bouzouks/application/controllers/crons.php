<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : crons du jeu toute la journée
 *
 * Auteur      : Fabien Foixet (fabien@foixet.com)
 * Date        : février 2014
 *
 * Copyright (C) 2012-2014 Fabien Foixet - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Crons extends CI_Controller
{
	private $date               = '';
	private $datetime           = '';
	
	public function __construct()
	{
		parent::__construct();

		// Ce controller ne peut être appelé qu'en console
		if ( ! $this->input->is_cli_request())
			show_404();

		$this->date     = bdd_date();
		$this->datetime = bdd_datetime();

		$this->output->set_header('Content-Type: text/html; charset=utf-8');
	}
	
	public function marche_noir_ajouter_rares()
	{
		$robots = $this->bouzouk->get_robots();

		// On vérifie qu'il y a des objets à mettre en vente
		$query = $this->db->select('id, objet_id')
						  ->from('objets_rares_attente')
						  ->where('date_vente <', $this->datetime)
						  ->get();
		
		// On met en vente les objets
		foreach ($query->result() as $objet_rare)
		{
			// On va chercher les infos de l'objet
			$query = $this->db->select('id, prix, peremption')
							  ->from('objets')
							  ->where('id', $objet_rare->objet_id)
							  ->get();

			// Si il existe
			if ($query->num_rows() == 1)
			{
				$objet = $query->row();

				// On prend un vendeur au hasard parmis les robots
				$vendeur = $robots[mt_rand(0, count($robots) - 1)];

				// On ajoute au marché noir
				$data_marche_noir = array(
					'objet_id'   => $objet->id,
					'joueur_id'  => $vendeur,
					'quantite'   => 1,
					'prix'       => $objet->prix,
					'peremption' => $objet->peremption
				);
				$this->db->insert('marche_noir', $data_marche_noir);
			}
			
			//On supprime l'entrée de vendeurs_rare
			$this->db->where('id', $objet_rare->id)
					 ->delete('objets_rares_attente');
		}
	}
	
	public function tobozon_notification_pseudo_prononce()
	{
		$this->load->library('lib_notifications');
		
		// On regarde la dernière fois qu'on a vérifié
		$query = $this->db->select('derniere_verif_posts_tobozon')
						  ->from('mairie')
						  ->get();
		$mairie = $query->row();
		
		// On met à jour la derniere fois qu'on a vérifié
		$this->db->set('derniere_verif_posts_tobozon', time())
				 ->update('mairie');
		
		// On va chercher tous les joueurs actifs
		$query = $this->db->select('j.id, j.pseudo, tu.group_id')
						  ->from('joueurs j')
						  ->join('tobozon_users tu', 'tu.id = j.id')
						  ->where_in('statut', array(Bouzouk::Joueur_Actif, Bouzouk::Joueur_Asile))
						  ->get();
		$joueurs = $query->result();
		
		// On va chercher tous les derniers posts
		$query = $this->db->select('id, poster, poster_id, message, topic_id')
						  ->from('tobozon_posts')
						  ->where('posted >', $mairie->derniere_verif_posts_tobozon)
						  ->get();
		
		foreach ($query->result() as $post)
		{
			foreach ($joueurs as $joueur)
			{
				$notifie = false;
				
				// Si le pseudo du joueur s'y trouve mais que c'est pas un post de lui même
				if (stripos($post->message, $joueur->pseudo) !== false && $joueur->id != $post->poster_id)
				{
					// On vérifie qu'il a le droit de lecture
					$query = $this->db->query('SELECT f.clan_id, f.clan_mode '.
								  'FROM tobozon_topics t '.
								  'JOIN tobozon_forums f ON f.id = t.forum_id '.
								  'LEFT JOIN tobozon_forum_perms fp ON (fp.forum_id = f.id AND fp.group_id = '.$joueur->group_id.') '.
								  'WHERE (fp.read_forum IS NULL OR fp.read_forum = 1) AND t.id='.$post->topic_id.' AND t.moved_to IS NULL');
					
					$forum = $query->row();
					
					if ($query->num_rows() > 0)
					{
						// On vérifie si c'est pas un forum de clan
						if($forum->clan_id == null || $forum->clan_mode != 3)
							$notifie = true;
						else
						{
							// On va chercher les membres de ce clan
							$est_politicien = $this->db->select('joueur_id')
											  ->from('politiciens')
											  ->where('clan_id', $forum->clan_id)
											  ->where('joueur_id', $joueur->id)
											  ->get()
											  ->num_rows();
							
							// On vérifie que c'est un membre de ce clan
							if ($est_politicien > 0)
								$notifie = true;
							else
							{
								// On va chercher le chef de ce clan
								$est_chef = $this->db->select('chef_id')
											  ->from('clans')
											  ->where('id', $forum->clan_id)
											  ->where('chef_id', $joueur->id)
											  ->get()
											  ->num_rows();
								
								// On vérifie que c'est le chef de ce clan
								if ($est_chef > 0)
									$notifie = true;
							}
						}
						
						if ($notifie && $this->lib_notifications->notifier(Bouzouk::Notification_PseudoPrononceTobozon, $joueur->id))
							$this->bouzouk->notification(237, array(profil($post->poster_id, $post->poster), site_url('tobozon/viewtopic.php?pid='.$post->id.'#p'.$post->id)), $joueur->id);
					}
				}
			}
		}
	}
}