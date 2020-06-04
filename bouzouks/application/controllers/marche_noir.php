<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : classe de gestion du marché noir pour la revente d'objets entre joueurs
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Marche_noir extends MY_Controller
{
	private $message_police;
	private $types_autorises = array('faim', 'sante', 'stress', 'boost');
	private $nom_typess = array('faim' => 'du bouffzouk', 'sante' => "de l'indispenzouk", 'stress' => 'du luxezouk', 'boost' => 'du boostzouk');
	private $prix_gemmes;
	

	public function __construct()
	{
		parent::__construct();
		$this->message_police = "
			<strong><em>&laquo; POLICE ! Vos Papiers ! &raquo;</em></strong><br><br>
			Aïe ! La bouzopolice t'es tombée dessus !<br>
			Les objets que tu viens d'acheter te sont confisqués...et tu perds <span class='pourpre'>-".$this->bouzouk->config('marche_noir_perte_xp_achat_police').' xp</span>';
	}

	public function index()
	{
		$joueurs_genants = $this->bouzouk->clans_concurrence_genante(-1);
		
		// On définit quel magazin aura ses objets les moins cher affichés
		$magazin_pub = $this->types_autorises[mt_rand(1, count($this->types_autorises))-1];
			
		// On va chercher les 3 objets les moins cher d'un des magazins
		$query = $this->db->select('o.id, (MIN(m_n.prix)/o.prix) AS ratio')
						  ->from('marche_noir m_n')
						  ->join('objets o', 'o.id = m_n.objet_id')
						  ->where_not_in('m_n.joueur_id', $joueurs_genants)
						  ->where('m_n.peremption >', 0)
						  ->where('o.type = ', $magazin_pub)
						  ->group_by('o.nom')
						  ->order_by('ratio')
						  ->limit(3)
						  ->get();
		
		$objets = $query->result();
		
		foreach ($objets as $objet)
		{				
			// On va chercher des infos sur le vendeur de cet objet
			$query = $this->db->select('m_n.prix, m_n.id AS id_vente, m_n.joueur_id, m_n.objet_id, m_n.quantite, m_n.peremption, j.pseudo as vendeur, o.nom, o.prix AS prix_normal, o.image_url, j.faim, j.sante, j.stress, j.perso')
							  ->from('marche_noir m_n')
							  ->join('objets o', 'o.id = m_n.objet_id')
							  ->join('joueurs j', 'j.id = m_n.joueur_id')
							  ->where('o.id', $objet->id)
							  ->where('m_n.peremption >', 0)
							  ->group_by('m_n.prix')
							  ->limit(1)
							  ->get();
			
			$objets_pub[] = $query->row();
		
		}
		
		// On va chercher tous les objets en vente en regroupant par objet
		$query = $this->db->select('SUM(m_n.quantite) AS quantite_totale, MIN(m_n.prix) AS prix_minimum, o.id, o.nom, o.type, o.image_url, o.prix, o.faim, o.sante, o.stress, o.jours_peremption, o.experience, o.force, o.charisme, o.intelligence, o.points_action')
						  ->from('marche_noir m_n')
						  ->join('objets o', 'o.id = m_n.objet_id')
						  ->where_not_in('m_n.joueur_id', $joueurs_genants)
						  ->order_by('o.type')
						  ->group_by('o.nom')
						  ->get();

		$vars = array(
			'objets'	 => $query->result(),
			'objets_pub' => $objets_pub, 
			'magazin_pub' => $this->nom_typess[$magazin_pub],
			'prix_gemmes'=> $this->prix_gemmes
		);
		return $this->layout->view('marche_noir/index', $vars);
	}

	private function liste_vendeurs($objet_id)
	{
		if ( ! entier_naturel_positif($objet_id))
			show_404();
		
		$joueurs_genants = $this->bouzouk->clans_concurrence_genante(-1);
		
		// On va chercher les infos de l'objet pour l'en-tête
		$query = $this->db->select('SUM(m_n.quantite) AS quantite_totale, MIN(m_n.prix) AS prix_minimum, o.id, o.nom, o.image_url, o.type, o.prix, o.faim, o.sante, o.stress, o.jours_peremption, o.experience, o.force, o.charisme, o.intelligence, o.points_action')
							->from('marche_noir m_n')
							->join('objets o', 'o.id = m_n.objet_id')
							->where('m_n.objet_id', $objet_id)
							->where_not_in('m_n.joueur_id', $joueurs_genants)
							->having('count(*) > 0')
							->limit(1)
							->get();

		// Si l'objet n'existe pas ou qu'aucun joueur n'en vend
		if ($query->num_rows() == 0)
			redirect('marche_noir');

		$objet = $query->row();
		
		// On va chercher tous les vendeurs de cet objet pour les afficher
		$query = $this->db->select('j.id AS vendeur_id, j.pseudo AS vendeur, m_n.id AS vente_id, m_n.prix, m_n.quantite, m_n.peremption')
						->from('marche_noir m_n')
						->join('objets o', 'o.id = m_n.objet_id')
						->join('joueurs j', 'j.id = m_n.joueur_id')
						->where('o.id', $objet_id)
						->where_not_in('m_n.joueur_id', $joueurs_genants)
						->order_by('m_n.prix')
						->order_by('peremption')
						->get();

		// On affiche
		$vars = array(
			'objet_id' => $objet_id,
			'objet'    => $objet,
			'objets'   => $query->result(),
			'prix_gemmes'=> $this->prix_gemmes
		);
		return $this->layout->view('marche_noir/acheter', $vars);
	}

	public function acheter($objet_id = null)
	{
		// objet_id doit être valide
		if ( ! isset($objet_id) OR ! entier_naturel_positif($objet_id))
			show_404();

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('vente_id', 'La vente', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('quantite', 'La quantité', 'required|is_natural_no_zero|greater_than[0]|less_than[10]');

		if ( ! $this->form_validation->run())
			return $this->liste_vendeurs($objet_id);

		$joueurs_genants = $this->bouzouk->clans_concurrence_genante(-1);
		
		// On vérifie que la vente existe au marché noir
		$query = $this->db->select('m_n.joueur_id, m_n.objet_id, m_n.quantite, m_n.prix, m_n.peremption, j.pseudo as vendeur, o.nom, o.prix AS prix_normal, o.rarete')
						  ->from('marche_noir m_n')
						  ->join('joueurs j', 'j.id = m_n.joueur_id')
						  ->join('objets o', 'o.id = m_n.objet_id')
						  ->where_not_in('m_n.joueur_id', $joueurs_genants)
						  ->where('m_n.id', $this->input->post('vente_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cette vente n'existe pas");
			return $this->liste_vendeurs($objet_id);
		}

		$objet = $query->row();
		
		// ---------- Hook clans ----------
		// Braquage (Organisation)
		// Fabrique de Gnoulze (Struleone)
 		$braquage = $this->bouzouk->clans_braquage();
 		$fabrique_de_gnoulze = isset($braquage) ? null : $this->bouzouk->clans_fabrique_de_gnoulze();

		if ((isset($braquage) && $braquage->parametres['objet_id'] == $objet_id && $braquage->chef_id == $this->session->userdata('id')) || (isset($fabrique_de_gnoulze) && $objet_id == 24 && $fabrique_de_gnoulze->parametres['joueur_id'] == $this->session->userdata('id')))
			$this->echec("Tu ne peux pas racheter un de ces objets, c'est toi le bénéficiaire des ventes !");

		// On vérifie que le joueur ne s'achète pas à lui-même
		else if ($this->session->userdata('id') == $objet->joueur_id)
			$this->echec("Tu ne peux pas racheter un de tes propres objets. Tu dois passer par ta maison si tu veux retirer cet objet du marché noir.");

		// On vérifie qu'il en existe au moins de cette quantité
		else if ($this->input->post('quantite') > $objet->quantite)
			$this->echec('Il ne reste pas assez de stock pour toi !');

		// On vérifie que le joueur a assez d'argent pour en acheter à cette quantite
		else if ($this->session->userdata('struls') < $this->input->post('quantite') * $objet->prix)
			$this->echec("Tu n'as pas assez d'argent pour en acheter autant !");

		if ($this->session->userdata('flash_echec') !== false)
			return $this->liste_vendeurs($objet_id);

		// On retire la valeur de la marchandise à l'acheteur
		$prix_total = $this->input->post('quantite') * $objet->prix;
 		$this->bouzouk->retirer_struls($prix_total);

 		// On ajoute la valeur de la marchandise au vendeur (si ce n'est pas un robot)
 		if ( ! in_array($objet->joueur_id, $this->bouzouk->get_robots()))
 		{
			$this->bouzouk->ajouter_struls($prix_total, $objet->joueur_id);

			// On ajoute à l'historique du vendeur
			$this->bouzouk->historique(114, null, array(profil(), $this->input->post('quantite'), $objet->nom, struls($prix_total)), $objet->joueur_id);

			// On envoit une notif au vendeur
			if ($this->bouzouk->est_connecte($objet->joueur_id))
				$this->bouzouk->notification(114, array(profil(), $this->input->post('quantite'), $objet->nom, struls($prix_total)), $objet->joueur_id);
		}

		// ---------- Hook clans ----------
		// Braquage (Organisation)
		// Fabrique de Gnoulze (Struleone)
		else
		{
			$hook_clans = false;

			// Braquage (Organisation)
			if (isset($braquage))
			{
				if ($braquage->parametres['objet_id'] == $objet_id)
				{
					$hook_clans = true;
					$joueur_id  = $braquage->chef_id;
					$nom_action = $braquage->nom_action;
				}
			}

			// Fabrique de Gnoulze (Struleone)
			else if (isset($fabrique_de_gnoulze))
			{
				if ($objet_id == 24)
				{
					$hook_clans = true;
					$joueur_id  = $fabrique_de_gnoulze->parametres['joueur_id'];
					$nom_action = $fabrique_de_gnoulze->nom_action;
				}
			}

			if ($hook_clans)
			{
				// On ajoute la valeur de la marchandise au joueur
				$this->bouzouk->ajouter_struls($prix_total, $joueur_id);

				// On ajoute à l'historique du joueur
				$this->bouzouk->historique(115, null, array(couleur('['.$nom_action.']'), profil(), $this->input->post('quantite'), $objet->nom, struls($prix_total)), $joueur_id, Bouzouk::Historique_Full);
			}
		}

		// On retire la quantité d'objets du marché noir
		// Suppression
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

		// Si le prix est bradé à 50% ou moins, on enregistre la transaction pour la détection multicomptes
		if ($objet->prix <= $objet->prix_normal / 2.0)
		{
			$data_mc_marche_noir = array(
				'vendeur_id'  => $objet->joueur_id,
				'acheteur_id' => $this->session->userdata('id'),
				'objet_id'    => $objet->objet_id,
				'quantite'    => $this->input->post('quantite'),
				'peremption'  => $objet->peremption,
				'prix'        => $objet->prix,
				'date'        => bdd_datetime(),
			);
			$this->db->insert('mc_marche_noir', $data_mc_marche_noir);
		}
		
		$message = '';
		
		// On ajoute cette quantité d'objets au joueur, sauf si la police le prend
		if ($this->bouzouk->bouzopolice($prix_total))
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
			
			// Si il a un objet pour empecher ça et que le truc acheté est rare ou très rare
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
				// On retire 1 d'expérience au joueur
				$perte_xp = $this->bouzouk->config('marche_noir_perte_xp_achat_police');

				// ---------- Hook clans ----------
				// Corruption à agent (Struleone)
				if ($this->bouzouk->clans_corruption_a_agent())
					$perte_xp *= 3;

				$this->bouzouk->retirer_experience($perte_xp);

				// On ajoute à l'historique
				$this->bouzouk->historique(116, null, array($this->input->post('quantite'), $objet->nom, $perte_xp));

				$vars = array(
					'titre_layout' => 'Marché noir - Acheter',
					'titre'        => 'Police !',
					'image_url'    => 'magasins/police.png',
					'message'      => $this->message_police
				);
				return $this->layout->view('blocage', $vars);
			}
		}

		$this->bouzouk->ajouter_objets($objet->objet_id, $this->input->post('quantite'), $objet->peremption);

		// Si le vendeur était un robot et l'objet un objet rare, on ajoute de l'expérience
		// ---------- Hook clans ----------
		// Braquage (Organisation)
		// Fabrique de Gnoulze (Struleone)
		if (in_array($objet->joueur_id, $this->bouzouk->get_robots()) && in_array($objet->rarete, array('rare', 'tres_rare')) && ! isset($braquage) && ! isset($fabrique_de_gnoulze))
		{
			$gain_xp = $this->bouzouk->config('marche_noir_gain_xp_objet_rare');
			$this->bouzouk->ajouter_experience($gain_xp);
			$message .= ", comme tu as acheté un objet rare, tu gagnes <span class='pourpre'>+$gain_xp xp</span> :)";
		}
		
		// On ajoute à l'historique
		$this->bouzouk->historique(117, null, array($this->input->post('quantite'), $objet->nom, profil($objet->joueur_id, $objet->vendeur), struls($prix_total), $message));

		// On affiche un message de confirmation
		$this->succes('Tu as acheté <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.'</span> à '.profil($objet->joueur_id, $objet->vendeur).' au marché noir pour '.struls($prix_total).$message);
		
		if ( ! isset($vars))
			return redirect('marche_noir/acheter/'.$objet_id);
		else
			return $this->layout->view('blocage', $vars);
	}
}