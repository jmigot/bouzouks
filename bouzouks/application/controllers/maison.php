<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : gestion de la maison du joueur (consommation/vente des objets)
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Maison extends MY_Controller
{
	private $message_police;
	private $messages_event;

	public function __construct()
	{
		parent::__construct();
		$this->message_police = "
			<strong><em>&laquo; POLICE ! Vos Papiers ! &raquo;</em></strong></p>
			<p>Aïe ! La bouzopolice t'es tombée dessus !<br>
			Les objets que tu voulais mettre en vente te sont confisqués...et tu perds <span class='pourpre'>-".$this->bouzouk->config('marche_noir_perte_xp_vente_police').' xp</span>';
	}

	public function index()
	{		
		// On va chercher les objets de la maison
		$query = $this->db->query('SELECT m.quantite, m.peremption, m.id AS maison_id, o.id, o.nom, o.rarete, o.type, o.image_url, o.prix, o.faim, o.sante, o.stress, o.jours_peremption, o.experience, o.force, o.charisme, o.intelligence, o.points_action '.
								  'FROM maisons m '.
								  'JOIN objets o ON o.id = m.objet_id '.
								  'WHERE m.joueur_id = '.$this->session->userdata('id').' '.
								  'ORDER BY o.rarete, IF(m.peremption = -1, 999999, m.peremption), o.type, o.nom');
		$maison = $query->result();

		// On va chercher les objets du marché noir
		$query = $this->db->query('SELECT m_n.id, m_n.quantite, m_n.prix, m_n.peremption, o.nom, o.image_url '.
								  'FROM marche_noir m_n '.
								  'JOIN objets o ON o.id = m_n.objet_id '.
								  'WHERE joueur_id = '.$this->session->userdata('id').' '.
								  'ORDER BY o.rarete, IF(m_n.peremption = -1, 999999, m_n.peremption), o.type, o.nom');
		$marche_noir = $query->result();

		// On récupère la liste des amis
		$query = $this->db->select()
						  ->from('amis a')
						  ->join('joueurs j', 'j.id = a.ami_id')
						  ->where('a.joueur_id', $this->session->userdata('id'))
        				  ->where('a.etat', Bouzouk::Amis_Accepte)
        				  ->where('j.statut', Bouzouk::Joueur_Actif)
        				  ->order_by('j.pseudo')
        				  ->get();
        $amis = $query->result();
		
		$malediction_bloque = false;
		$amis_connectes = false;
		
		// Si il est maudit
		if ($this->session->userdata('maudit'))
		{
			$query = $this->db->select('parametres')
							  ->from('clans_actions_lancees')
							  ->where('action_id', 40)
							  ->where('statut', Bouzouk::Clans_ActionEnCours)
							  ->where('date_debut >= (NOW() - INTERVAL duree HOUR)')
							  ->get();
			
			if ($query->num_rows() > 0)
			{
				$malediction = $query->row();
				$malediction->parametres = unserialize($malediction->parametres);
				
				if ($malediction->parametres['joueur_id'] == $this->session->userdata('id'))
					$malediction_bloque = true;
			}
			
			// On va chercher les amis connctés du joueur
			$query = $this->db->select('j.id, j.pseudo')
							  ->from('amis a')
							  ->join('joueurs j', 'a.ami_id = j.id')
							  ->where('a.joueur_id', $this->session->userdata('id'))
							  ->where('a.etat', Bouzouk::Amis_Accepte)
							  ->where('j.connecte > (NOW() - INTERVAL 5 MINUTE)')
							  ->order_by('j.pseudo')
							  ->get();
			
			if ($query->num_rows() > 0)
				$amis_connectes = $query->result();
		}

		// On affiche
		$vars = array(
			'objets_maison'      => $maison,
			'objets_marche_noir' => $marche_noir,
			'amis'               => $amis,
			'malediction_bloque' => $malediction_bloque,
			'amis_connectes'	 => $amis_connectes
		);
		$this->layout->view('maison/index', $vars);
	}
	
	public function consommer()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('objet_id', "L'objet", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('peremption', 'La péremption', 'required|integer');
		$this->form_validation->set_rules('quantite', 'La quantité', 'required|is_natural_no_zero|greater_than[0]|less_than[10]');
		$this->form_validation->set_rules('maison_id', "L'objet illimté", 'is_natural_no_zero');

		if ( ! $this->form_validation->run())
			return $this->index();
	
		// Le Fragment de Schnibble Bleuté ne peut pas se consommer
		if ($this->input->post('objet_id') == 55)
		{
			$this->echec('Ne consomme pas ça malheureux...tu risques une crise de zlotage sévère !');
			return $this->index();
		}

		// On supprime la quantité demandée de la maison du joueur
		$objet = $this->bouzouk->retirer_objets($this->input->post('objet_id'), $this->input->post('quantite'), $this->input->post('peremption'));

		// Si il y a une erreur
		if ( ! $objet || ($this->input->post('objet_id') == 48 && $objet->peremption > 0))
			return $this->index();

		// On applique les stats sur le joueur
		$quantite = $this->input->post('quantite');

		// Objets boost
		if ($objet->type == 'boost')
		{
			// Antidote
			if ($objet->id == 54)
			{
				// Périmé
				if ($objet->peremption == 0)
				{
					$sante = -10 * $quantite;
					$this->bouzouk->set_stats(0, $sante, 0);
					
					// On ajoute à l'historique
					$this->bouzouk->historique(109, 98, array($this->input->post('quantite'), $objet->nom, '-0', $sante, '-0'));
					
					// On affiche un message de confirmation
					$this->succes('Tu as consommé <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.'</span>...Oups, la date de péremption était passée...Santé : <span class="pourpre">'.$sante.'</span>');
				}

				// Non périmé
				else
				{
					// On immunise le joueur
					// Event Bouf'tête
					if($this->bouzouk->etat_bouf_tete() == 'start'){
						$this->db->where('id_joueur', $this->session->userdata('id'))
								->set('nb_petit', 0)
								->set('immun', 1)
								->set('candidat', 0)
								->update('event_bouf_tete');

						// On met a jour la session
						$this->session->set_userdata('bouf_tete', 0);
						$this->session->set_userdata('perso', str_replace('zombi/', '', $this->session->userdata('perso')));

					}
					// Event Mlboobz
					if($this->bouzouk->etat_event_mlbobz()){
						$this->db->where('id_joueur', $this->session->userdata('id'))
								 ->set('immun', 1)
								 ->set('nb_malediction', 0)
								 ->update('event_mlbobz');
						$this->session->set_userdata('mlbobz', 0);
						$this->session->set_userdata('perso', str_replace('rp_zoukette/', '', $this->session->userdata('perso')));

					}
					$message = 'Tu as consommé <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.'</span>, tu te sens mieux.';
					// On ajoute à l'historique
					$this->bouzouk->historique(36, null, array($message));

					// On affiche un message de confirmation
					$this->succes($message);

					// On met à jour la session du transplanteur
					$this->bouzouk->augmente_version_session();
				}
			}

			// Malédiction
			elseif ($objet->id == 49)
			{
				// On regarde si l'ami existe et est connecté
				$query = $this->db->select('j.id, j.pseudo, j.rang')
								   ->from('amis a')
								   ->join('joueurs j', 'j.id = a.ami_id')
								   ->where('a.joueur_id', $this->session->userdata('id'))
								   ->where('a.ami_id', $this->input->post('ami_id'))
								   ->where('a.etat', Bouzouk::Amis_Accepte)
								   ->where('j.statut', Bouzouk::Joueur_Actif)
								   ->where('j.connecte > (NOW() - INTERVAL 5 MINUTE)')
								   ->get();
							
        		if ($query->num_rows() == 0)
        		{
					$this->bouzouk->ajouter_objets($this->input->post('objet_id'), $this->input->post('quantite'), $this->input->post('peremption'));
					$this->echec("Le bouzouk ciblé n'est pas ton ami ou n'est pas connecté");
					return $this->index();
        		}
				
				$ami = $query->row();
				
				// On refile l'objet à l'ami
				$this->bouzouk->ajouter_objets($this->input->post('objet_id'), $this->input->post('quantite'), $this->input->post('peremption'), $ami->id);
				
				// On met a jour les 2 sessions
				$this->bouzouk->augmente_version_session($ami->id);
				$this->bouzouk->augmente_version_session();
				
				// On envoit un historique/notif à l'ami
				$this->bouzouk->historique(243, null, array(profil()), $ami->id, Bouzouk::Historique_Full);
				
				// Historique
				$this->bouzouk->historique(244, null, array(profil($ami->id, $ami->pseudo, $ami->rang)));
				
				// On affiche un message de confirmation
					$this->succes('Tu as envoyé <span class="pourpre">'.$objet->nom."</span> à ton ami ".profil($ami->id, $ami->pseudo, $ami->rang).'.');
			}

			// Robot
			else if ($objet->id == 44)
			{
				// On regarde si l'ami existe
				$query = $this->db->select('j.id, j.pseudo, j.rang')
								   ->from('amis a')
								   ->join('joueurs j', 'j.id = a.ami_id')
								   ->where('a.joueur_id', $this->session->userdata('id'))
								   ->where('a.ami_id', $this->input->post('ami_id'))
								   ->where('a.etat', Bouzouk::Amis_Accepte)
								   ->where('j.statut', Bouzouk::Joueur_Actif)
								   ->get();
							
        		if ($query->num_rows() == 0)
        		{
					$this->bouzouk->ajouter_objets($this->input->post('objet_id'), $this->input->post('quantite'), $this->input->post('peremption'));
					$this->echec("Le bouzouk ciblé n'est pas ton ami ou n'est plus actif");
					return $this->index();
        		}
        
				// Périmé
				if ($objet->peremption == 0)
				{			
					// Le joueur voit ses P.A remis à 0
					$this->db->set('points_action', '0')
							 ->where('id', $this->session->userdata('id'))
							 ->update('joueurs');
					$this->session->set_userdata('points_action', 0);

					// On ajoute à l'historique
					$this->bouzouk->historique(233, null, array(1, $objet->nom));

					// On affiche un message de confirmation
					$this->succes('Tu as consommé <span class="pourpre">1 '.$objet->nom."</span>...Oups, la date de péremption était passée...tu perds tous tes points d'action");
				}

				// Non périmé
				else
				{
					$ami = $query->row();

					// On ajoute les P.A à l'ami
					$this->db->set('points_action', 'points_action'.$objet->points_action, false)
							 ->where('id', $this->input->post('ami_id'))
							 ->update('joueurs');
					$this->bouzouk->augmente_version_session($this->input->post('ami_id'));
					
					// On envoit un historique/notif à l'ami
					$this->bouzouk->historique(234, null, array(profil(), $objet->nom, $objet->points_action), $this->input->post('ami_id'), Bouzouk::Historique_Full);

					// On ajoute à l'historique
					$this->bouzouk->historique(232, null, array(1, $objet->nom, profil($ami->id, $ami->pseudo, $ami->rang), $objet->points_action));

					// On affiche un message de confirmation
					$this->succes('Tu as consommé <span class="pourpre">1 '.$objet->nom.'</span>, ton ami '.profil($ami->id, $ami->pseudo, $ami->rang).' gagne <span class="pourpre">'.$objet->points_action." points d'action</span>");
				}
			}

			// Expérience
			else if ($objet->experience != 0)
			{
				// Périmé
				if ($objet->peremption == 0)
				{
					$perte_xp = mt_rand(1, $objet->experience) * $quantite;
					$this->bouzouk->retirer_experience($perte_xp);

					// On ajoute à l'historique
					$this->bouzouk->historique(97, 98, array($this->input->post('quantite'), $objet->nom, $perte_xp));

					// On affiche un message de confirmation
					$this->succes('Tu as consommé <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.'</span>...Oups, la date de péremption était passée..., tu perds <span class="pourpre">+'.$perte_xp.' xp</span>');
				}

				// Non périmé
				else
				{
					$gain_xp = $objet->experience * $quantite;
					$this->bouzouk->ajouter_experience($gain_xp);

					// On ajoute à l'historique
					$this->bouzouk->historique(99, null, array($this->input->post('quantite'), $objet->nom, $gain_xp));

					// On affiche un message de confirmation
					$this->succes('Tu as consommé <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.'</span>, tu gagnes <span class="pourpre">+'.$gain_xp.' xp</span>');
				}
			}

			// Objets force/charisme/intelligence
			else if ($objet->force != '0' || $objet->charisme != '0' || $objet->intelligence != '0')
			{
				// Périmé
				if ($objet->peremption == 0)
				{
					$force = ($objet->force != '0') ? mt_rand(-(int)$objet->force, -1) * $quantite : 0;
					$charisme = ($objet->charisme != '0') ? mt_rand(-(int)$objet->charisme, -1) * $quantite : 0;
					$intelligence = ($objet->intelligence != '0') ? mt_rand(-(int)$objet->intelligence, -1) * $quantite : 0;
					$this->bouzouk->set_stats_clans($force, $charisme, $intelligence);

					// On ajoute à l'historique
					$this->bouzouk->historique(100, 98, array($this->input->post('quantite'), $objet->nom, $force, $charisme, $intelligence));

					// On affiche un message de confirmation
					$this->succes('Tu as consommé <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.
								'</span>...Oups, la date de péremption était passée...Force : <span class="pourpre">'.$force.
								'</span> | Charisme : <span class="pourpre">'.$charisme.'</span> | Intelligence : <span class="pourpre">'.$intelligence.'</span>');
				}

				// Non périmé
				else
				{
					$force = $objet->force * $quantite;
					$charisme = $objet->charisme * $quantite;
					$intelligence = $objet->intelligence * $quantite;
					$this->bouzouk->set_stats_clans($force, $charisme, $intelligence);

					if ($force > 0)
						$force = '+'.$force;

					if ($charisme > 0)
						$charisme = '+'.$charisme;

					if ($intelligence > 0)
						$intelligence = '+'.$intelligence;

					// On ajoute à l'historique
					$this->bouzouk->historique(101, null, array($this->input->post('quantite'), $objet->nom, $force, $charisme, $intelligence));

					// On affiche un message de confirmation
					$this->succes('Tu as consommé <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.
								'</span> (force : <span class="pourpre">'.$force.'</span> | charisme : <span class="pourpre">'.$charisme.
								'</span> | intelligence : <span class="pourpre">'.$intelligence.'</span>)');
				}
			}

			// Péremption
			else
			{
				// Objet spécial qui redonne de la péremption à tous les objets de la maison
				if ($objet->jours_peremption == -2)
				{
					// Périmé
					if ($objet->peremption == 0)
					{
						// On va chercher tous les objets de la maison qui sont non périmés
						$query = $this->db->select('m.peremption, o.id, m.quantite')
										  ->from('maisons m')
										  ->join('objets o', 'o.id = m.objet_id')
										  ->where('m.joueur_id', $this->session->userdata('id'))
										  ->where('m.peremption !=', 0)
										  ->get();
						$objets = $query->result();

						// On périme chaque objet
						foreach ($objets as $objet_joueur)
						{
							$this->bouzouk->retirer_objets($objet_joueur->id, $objet_joueur->quantite, $objet_joueur->peremption);
							$this->bouzouk->ajouter_objets($objet_joueur->id, $objet_joueur->quantite, 0);
						}

						// On ajoute à l'historique
						$this->bouzouk->historique(102, 98, array(1, $objet->nom));
						
						// On affiche un message de confirmation
						$this->succes('Tu as consommé <span class="pourpre">1 '.$objet->nom."</span>...Oups, la date de péremption était passée...tous tes objets deviennent périmés");
					}

					// Non périmé
					else
					{
						// On va chercher tous les objets de la maison qui sont périmés
						$query = $this->db->select('o.peremption, o.id, m.quantite')
										  ->from('maisons m')
										  ->join('objets o', 'o.id = m.objet_id')
										  ->where('m.joueur_id', $this->session->userdata('id'))
										  ->where('m.peremption', 0)
										  ->get();
						$objets = $query->result();

						// On dépérime chaque objet
						foreach ($objets as $objet_joueur)
						{
							$this->bouzouk->retirer_objets($objet_joueur->id, $objet_joueur->quantite, 0);
							$this->bouzouk->ajouter_objets($objet_joueur->id, $objet_joueur->quantite, $objet_joueur->peremption);
						}

						// On ajoute à l'historique
						$this->bouzouk->historique(103, null, array(1, $objet->nom));
						
						// On affiche un message de confirmation
						$this->succes('Tu as consommé <span class="pourpre">1 '.$objet->nom.'</span>, tous tes objets périmés ont été remis à neuf');
					}
				}
				
				// Objet illimité
				else if ($objet->jours_peremption == -1)
				{
					// On va chercher les infos de l'objet ciblé
					$query = $this->db->select('m.objet_id, m.peremption, o.nom')
									  ->from('maisons m')
									  ->join('objets o', 'o.id = m.objet_id')
									  ->where('m.id', $this->input->post('maison_id'))
									  ->where('m.joueur_id', $this->session->userdata('id'))
									  ->where_not_in('m.objet_id', array(49)) // Pas la malédiction
									  ->where('m.peremption > 0')
									  ->get();

					// L'objet ciblé n'existe pas
					if ($query->num_rows() == 0)
					{
						$this->bouzouk->ajouter_objets($this->input->post('objet_id'), $this->input->post('quantite'), $this->input->post('peremption'));
						$this->echec("L'objet ciblé n'existe pas");
						return $this->index();
					}

					$objet_cible = $query->row();

					// On supprime l'objet ciblé de la maison du joueur
					$objet_illimite = $this->bouzouk->retirer_objets($objet_cible->objet_id, 1, $objet_cible->peremption);

					// Si il y a une erreur
					if ( ! $objet_illimite)
					{
						$this->bouzouk->ajouter_objets($this->input->post('objet_id'), $this->input->post('quantite'), $this->input->post('peremption'));
						$this->echec("L'objet ciblé n'a pas pu être retiré de la maison");
						return $this->index();
					}

					// Périmé
					if ($objet->peremption == 0)
					{
						// On ajoute un objet équivalent périmé
						$this->bouzouk->ajouter_objets($objet_cible->objet_id, 1, 0);

						// On ajoute à l'historique
						$this->bouzouk->historique(104, 98, array(1, $objet->nom, $objet_cible->nom));

						// On affiche un message de confirmation
						$this->succes('Tu as consommé <span class="pourpre">1 '.$objet->nom.'</span> sur <span class="pourpre">1 '.$objet_cible->nom."</span>...Oups, la date de péremption était passée...l'objet devient périmé");
					}

					// Non périmé
					else
					{
						// On ajoute un objet équivalent avec une péremption illimitée
						$this->bouzouk->ajouter_objets($objet_cible->objet_id, 1, -1);

						// On ajoute à l'historique
						$this->bouzouk->historique(105, null, array(1, $objet->nom, $objet_cible->nom));

						// On affiche un message de confirmation
						$this->succes('Tu as consommé <span class="pourpre">1 '.$objet->nom.'</span> sur <span class="pourpre">1 '.$objet_cible->nom.'</span> qui obtient une péremption illimitée');
					}
				}

				// Ajout d'un nombre de jours
				else
				{
					// Périmé
					if ($objet->peremption == 0)
					{
						$peremption = mt_rand(1, $objet->jours_peremption) * $quantite;

						// On périme ceux qui étaient en dessous du seuil
						$this->db->set('peremption', 0)
								 ->where('joueur_id', $this->session->userdata('id'))
								 ->where('peremption != -1')
								 ->where('peremption < '.$peremption)
								 ->where_not_in('objet_id', array(49)) // Pas la malédiction
								 ->update('maisons');

						// On enlève des jours de péremption
						$this->db->set('peremption', 'peremption-'.$peremption, false)
								 ->where('joueur_id', $this->session->userdata('id'))
								 ->where('peremption != -1')
								 ->where('peremption >= '.$peremption)
								 ->where_not_in('objet_id', array(49)) // Pas la malédiction
								 ->update('maisons');

						// On ajoute à l'historique
						$this->bouzouk->historique(106, 98, array($quantite, $objet->nom, $peremption));

						// On affiche un message de confirmation
						$this->succes('Tu as consommé <span class="pourpre">'.$quantite.' '.$objet->nom.'</span>...Oups, la date de péremption était passée..tu perds <span class="pourpre">-'.$peremption.' jours</span> de péremption sur tous les objets de ta maison');
					}

					// Non périmé
					else
					{
						$peremption = $objet->jours_peremption * $quantite;
						$peremption_max = $this->bouzouk->config('maison_peremption_max');

						// On met à fond les objets qui sont au dessus du seuil
						$this->db->set('peremption', $peremption_max)
								 ->where('joueur_id', $this->session->userdata('id'))
								 ->where('peremption > 0')
								 ->where('peremption + '.$peremption.' > '.$peremption_max)
								 ->where_not_in('objet_id', array(49)) // Pas la malédiction
								 ->update('maisons');

						// On ajoute les jours de péremption aux autres objets
						$this->db->set('peremption', 'peremption+'.$peremption, false)
								 ->where('joueur_id', $this->session->userdata('id'))
								 ->where('peremption > 0')
								 ->where('peremption + '.$peremption.' <= '.$peremption_max)
								 ->where_not_in('objet_id', array(49)) // Pas la malédiction
								 ->update('maisons');

						// On cherche tous les objets qui ont atteint la péremption max (certains ne seront pas fusionnés)
						$query = $this->db->select('objet_id, quantite')
								 ->from('maisons')
								 ->where('joueur_id', $this->session->userdata('id'))
								 ->where('peremption', $peremption_max)
								 ->get();
						$objets = $query->result();

						$nouveaux_objets = array();

						// On calcule les nouveaux objets
						foreach ($objets as $objet)
						{
							// On ajoute au tableau de fusion
							if ( ! isset($nouveaux_objets[$objet->objet_id]))
								$nouveaux_objets[$objet->objet_id] = $objet->quantite;

							else
								$nouveaux_objets[$objet->objet_id] += $objet->quantite;

						}

						// On supprime les objets de péremption max
						foreach ($objets as $objet)
						{
							// On retire les objets au joueur
							$this->CI->db->where('objet_id', $objet->objet_id)
						 				 ->where('joueur_id', $this->session->userdata('id'))
						 			     ->where('peremption', $peremption_max)
						 				 ->delete('maisons');
						}

						// On ajoute les objets de péremption max
						foreach ($nouveaux_objets as $objet_id => $quantite)
						{
							$this->bouzouk->ajouter_objets($objet_id, $quantite, $peremption_max);
						}
						
						// On ajoute à l'historique
						$this->bouzouk->historique(107, null, array($quantite, $objet->nom, $peremption));

						// On affiche un message de confirmation
						$this->succes('Tu as consommé <span class="pourpre">'.$quantite.' '.$objet->nom.'</span>, tu gagnes <span class="pourpre">+'.$peremption.' jours</span> de péremption sur tous les objets de ta maison');
					}
				}
			}
		}

		// Objets faim/santé/stress
		else
		{
			// Périmé
			if ($objet->peremption == 0)
			{
				// Smurtz [Rare]
				if($objet->id == 56)
				{
					$stress = '+40';
					$sante = 0;
				}
				else{
					$sante = mt_rand(-55, -20);
					$stress = '+'.mt_rand(30, 55);
				}
				$this->bouzouk->set_stats(0, $sante, $stress);

				// On ajoute à l'historique
				$this->bouzouk->historique(108, 98, array($this->input->post('quantite'), $objet->nom, $sante, $stress));

				// On affiche un message de confirmation
				$this->succes('Tu as consommé <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.
							'</span>...Oups, la date de péremption était passée...Santé : <span class="pourpre">'.$sante.
							'</span> | Stress : <span class="pourpre">'.$stress.'</span>');
			}

			// Non périmé
			else
			{
				$faim = $objet->faim * $quantite;
				$sante = $objet->sante * $quantite;
				$stress = $objet->stress * $quantite;
				$this->bouzouk->set_stats($faim, $sante, $stress);

				if ($faim > 0)
					$faim = '+'.$faim;

				if ($sante > 0)
					$sante = '+'.$sante;

				if ($stress > 0)
					$stress = '+'.$stress;

				// On ajoute à l'historique
				$this->bouzouk->historique(109, null, array($this->input->post('quantite'), $objet->nom, $faim, $sante, $stress));

				// On affiche un message de confirmation
				$this->succes('Tu as consommé <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.
							'</span> (faim : <span class="pourpre">'.$faim.'</span> | santé : <span class="pourpre">'.$sante.
							'</span> | stress : <span class="pourpre">'.$stress.'</span>)');
			}
		}
		
		// Si le stress est à 100%
		if ($this->session->userdata('stress') >= 100)
		{
			// On passe à l'asile
			$this->load->library('lib_joueur');
			$this->lib_joueur->mettre_asile($this->session->userdata('id'));

			// On redirige
			redirect('joueur/asile');
		}

		return $this->index();
	}

	public function lire()
	{
		$this->echec("Tu n'arrives pas à lire ce truc, essaye de te procurer des lunettes spéciales...");
		return $this->index();
	}

	public function _check_prix($prix)
	{
		$prix = round($prix, 1);

		// On vérifie que le prix est d'au moins 1 strul
		if ($this->input->post('prix') < 1)
		{
			$this->form_validation->set_message('_check_prix', '%s est trop petit (1 strul minimum)');
			return false;
		}

		// Si c'est pour le Fragment de Schnibble Bleuté, le prix max est quasi-illimité
		if ($this->input->post('objet_id') == 55)
		{
			if ($prix > 100000)
			{
				$this->form_validation->set_message('_check_prix', "Oui enfin bon à ce prix là je pense pas qu'on te l'achète, hein");
				return false;
			}

			return true;
		}

		// On vérifie que le prix n'excède pas x fois le prix officiel de l'objet
		$query = $this->db->select('prix')
						  ->from('objets')
						  ->where('id', $this->input->post('objet_id'))
						  ->get();
		$objet = $query->row();
		$prix_maximum = $this->bouzouk->config('maison_coefficient_max_vente') * $objet->prix;

		if ($prix > $prix_maximum)
		{
			$this->form_validation->set_message('_check_prix', '%s est trop grand (<span class="pourpre">'.$this->bouzouk->config('maison_coefficient_max_vente').' fois</span> le prix officiel maximum, soit '.struls($prix_maximum).')');
			return false;
		}

		return true;
	}

	public function vendre()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('objet_id', "L'objet", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('peremption', 'La péremption', 'required|integer');
		$this->form_validation->set_rules('quantite', 'La quantité', 'required|is_natural_no_zero|greater_than[0]|less_than[10]');
		$this->form_validation->set_rules('prix', 'Le prix', 'required|numeric|callback__check_prix');

		if ( ! $this->form_validation->run())
			return $this->index();
		
		// La malédiction du Schnibble est invendable
		if ($this->input->post('objet_id') == 49)
		{
			$this->echec('Tu ose vendre une relique sacrée ?? Honte sur toi...');
			return $this->index();
		}

		$_POST['prix'] = round($_POST['prix'], 1);

		// On vérifie que le joueur n'a pas trop d'objets en vente
		$nb_ventes = $this->db->where('joueur_id', $this->session->userdata('id'))
						  ->count_all_results('marche_noir');

		$nb_ventes_max = $this->bouzouk->config('marche_noir_max_ventes');

		if ($nb_ventes >= $nb_ventes_max)
		{
			$this->echec("Tu as déjà <span class='pourpre'>$nb_ventes ventes</span> au marché noir. Tu ne peux plus proposer de produits.");
			return $this->index();
		}

		// On supprime la quantité demandée de la maison du joueur
		$objet = $this->bouzouk->retirer_objets($this->input->post('objet_id'), $this->input->post('quantite'), $this->input->post('peremption'));

		// Si il y a une erreur
		if ( ! $objet)
			return $this->index();

		$message = '';
		
		// On ajoute l'objet au marché noir, sauf si la police les prend
		if ($this->bouzouk->bouzopolice($this->input->post('quantite') * $this->input->post('prix')))
		{
			// On regarde si le joueur a un trepan
			$query = $this->db->select('m.id, m.objet_id, m.peremption, o.nom, o.stress')
							  ->from('maisons m')
							  ->join('objets o', 'm.objet_id = o.id')
							  ->where('m.joueur_id', $this->session->userdata('id'))
							  ->where('m.objet_id', 48)
							  ->where('m.peremption >', 0)
							  ->order_by('m.peremption')
							  ->limit(1)
							  ->get();
			
			// Si il a un objet pour empecher ça et que le truc vendu est rare ou très rare
			if ($query->num_rows() == 1 && $objet->rarete != 'normal')
			{
				$objet_echape = $query->row();
				
				$this->bouzouk->set_stats(0, 0, $objet_echape->stress);
				
				$vars = array(
					'titre_layout' => 'Marché noir - Acheter',
					'titre'        => 'Police !',
					'image_url'    => 'magasins/police.png',
					'message'      => "
					<strong><em>&laquo; POLICE ! Vos Papiers ! &raquo;</em></strong><br><br>
					Aïe ! La bouzopolice t'es tombée dessus !<br>
					<br>
					Mais grace à ton <span class='pourpre'>".$objet_echape->nom."</span> tu réussis à discretement recupérer ton objet confisqué qu'il avait rangé dans son Képi, tu gagnes <span class='pourpre'>".$objet_echape->stress.' de Stress</span>'
				);
				
				$message .= ', mais tu as perdu <span class="pourpre">1 '.$objet_echape->nom.'</span>';
				
				$this->bouzouk->retirer_objets($objet_echape->objet_id, 1, $objet_echape->peremption);
			}

			else
			{
				// On retire de l'expérience au joueur
				$perte_xp = $this->bouzouk->config('marche_noir_perte_xp_vente_police');


				// ---------- Hook clans ----------
				// Corruption à agent (Struleone)
				if ($this->bouzouk->clans_corruption_a_agent())
					$perte_xp *= 3;
				
				$this->bouzouk->retirer_experience($perte_xp);

				// On ajoute à l'historique
				$this->bouzouk->historique(110, null, array($this->input->post('quantite'), $objet->nom, $perte_xp));

				$vars = array(
					'titre_layout' => 'Maison - Vendre',
					'titre'        => 'Police !',
					'image_url'    => 'marche_noir/police.gif',
					'message'      => $this->message_police
				);
				return $this->layout->view('blocage', $vars);
			}
		}

		$nb_objets = $this->db->where('joueur_id', $this->session->userdata('id'))
							  ->where('objet_id', $this->input->post('objet_id'))
							  ->where('prix', $this->input->post('prix'))
							  ->where('peremption', $this->input->post('peremption'))
							  ->count_all_results('marche_noir');

		// Update
		if ($nb_objets > 0)
		{
			$this->db->set('quantite', 'quantite + '.$this->input->post('quantite'), false)
					 ->where('objet_id', $this->input->post('objet_id'))
					 ->where('joueur_id', $this->session->userdata('id'))
					 ->where('prix', $this->input->post('prix'))
					 ->where('peremption', $this->input->post('peremption'))
					 ->update('marche_noir');
		}

		// Insert
		else
		{
			$data_marche_noir = array(
				'joueur_id'  => $this->session->userdata('id'),
				'objet_id'   => $this->input->post('objet_id'),
				'quantite'   => $this->input->post('quantite'),
				'prix'       => $this->input->post('prix'),
				'peremption' => $this->input->post('peremption')
			);
			$this->db->insert('marche_noir', $data_marche_noir);
		}

		// On ajoute à l'historique
		$this->bouzouk->historique(111, null, array($this->input->post('quantite'), $objet->nom, struls($this->input->post('prix'))));

		// On affiche un message de confirmation
		$this->succes('Tu as mis en vente <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.'</span> au marché noir à '.struls($this->input->post('prix')).' pièce'.$message);
		
		if ( ! isset($vars))
			return $this->index();
		else
			return $this->layout->view('blocage', $vars);
	}

	public function retirer()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('vente_id', "La vente", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('quantite', 'La quantité', 'required|is_natural_no_zero|greater_than[0]|less_than[10]');

		if ( ! $this->form_validation->run())
		{
			return $this->index();
		}

		// On vérifie que le joueur possède bien cet objet
		$query = $this->db->select('m_n.quantite, m_n.prix, m_n.peremption, o.nom, o.id')
						  ->from('marche_noir m_n')
						  ->join('objets o', 'o.id = m_n.objet_id')
						  ->where('m_n.joueur_id', $this->session->userdata('id'))
						  ->where('m_n.id', $this->input->post('vente_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Tu n'as pas d'objet de ce type en vente");
			return $this->index();
		}

		$objet = $query->row();

		// On vérifie que la quantité demandée est disponible
		if ($this->input->post('quantite') > $objet->quantite)
		{
			$this->echec("Tu n'as que <span class='pourpre'>".$objet->quantite." ".$objet->nom."</span> en vente, tu ne peux pas en retirer <span class='pourpre'>".$this->input->post('quantite').'</span>');
			return $this->index();
		}

		// Si l'objet n'est pas périmé, il peut y avoir une taxe
		if ($objet->peremption != 0 && floor($objet->prix * $this->bouzouk->config('maison_pourcentage_taxe_retrait') / 100) > 0)
		{
			// On calcule la taxe de reprise (xx% du total -> plus le joueur vend cher plus il payera de taxe pour reprendre ses invendus)
			$taxe = $this->input->post('quantite') * floor($objet->prix * $this->bouzouk->config('maison_pourcentage_taxe_retrait') / 100);

			// On vérifie que le joueur peut payer la taxe
			if ($taxe > 0 AND $this->session->userdata('struls') < $taxe)
			{
				$this->echec("Tu n'as pas assez de struls pour payer la taxe de reprise de ".struls($taxe));
				return $this->index();
			}

			// On retire la taxe au joueur
			if ($taxe > 0)
				$this->bouzouk->retirer_struls($taxe);
		}

		else
		{
			$taxe = 0;
		}

		// On retire les objets du marché noir
		// Delete
		if ($this->input->post('quantite') == $objet->quantite)
		{
			$this->db->where('id', $this->input->post('vente_id'))
					 ->delete('marche_noir');
		}

		// Update
		else
		{
			$this->db->set('quantite', 'quantite - '.$this->input->post('quantite'), false)
					 ->where('id', $this->input->post('vente_id'))
					 ->update('marche_noir');
		}

		// On ajoute les objets au joueur
		$this->bouzouk->ajouter_objets($objet->id, $this->input->post('quantite'), $objet->peremption);

		// On ajoute à l'historique
		$this->bouzouk->historique(112, null, array($this->input->post('quantite'), $objet->nom, struls($taxe)));

		// On affiche le résultat
		$this->succes('Tu as récupéré <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.'</span> et tu as payé '.struls($taxe).' de taxe.');
		return $this->index();
	}

	public function supprimer()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('objet_id', "L'objet", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('peremption', 'La péremption', 'required|integer');
		$this->form_validation->set_rules('quantite', 'La quantité', 'required|is_natural_no_zero|greater_than[0]|less_than[10]');

		if ( ! $this->form_validation->run())
			return $this->index();

		// La malédiction du Schnibble est pas supprimable
		if ($this->input->post('objet_id') == 49)
		{
			$this->echec('Mec, jeter une relique ça porte malheur...');
			return $this->index();
		}

		// On retire la quantité demandée de la maison du joueur
		$objet = $this->bouzouk->retirer_objets($this->input->post('objet_id'), $this->input->post('quantite'), $this->input->post('peremption'));

		// Si il y a une erreur
		if ( ! $objet)
		{
			return $this->index();
		}

		// On ajoute à l'historique
		$this->bouzouk->historique(113, null, array($this->input->post('quantite'), $objet->nom));

		// On affiche un message de confirmation
		$this->succes('Tu as jeté <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.'</span> de ta maison');
		return $this->index();
	}
}
