<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : mendiants dans le jeu
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */
class Mendiants extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		
		// ---------- Hook clans ----------
		// Malediction du Schnibble (SDS)
		if ($this->session->userdata('maudit') && ! $this->bouzouk->is_admin())
			redirect('clans/maudit');
	}
	
	private function ruelle_fermee()
	{
		// ---------- Hook clans ----------
		// Censure des mendiants (CdBM)
		$query = $this->db->select('caa.parametres, c.nom AS nom_clan, c.mode_recrutement')
						  ->from('clans_actions_lancees caa')
						  ->join('clans c', 'c.id = caa.clan_id')
						  ->where('caa.action_id', 24)
						  ->where('caa.statut', Bouzouk::Clans_ActionEnCours)
						  ->get();
		$censure_mendiants = ($query->num_rows() == 1) ? $query->row() : null;

		if (isset($censure_mendiants))
		{
			$censure_mendiants->parametres = unserialize($censure_mendiants->parametres);
			$nom_clan = ($censure_mendiants->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('Un clan') : couleur(form_prep($censure_mendiants->nom_clan));
			$this->load->library('lib_parser');

			// On affiche
			$vars = array(
				'nom_clan' => $nom_clan,
				'texte'    => $censure_mendiants->parametres['texte']
			);
			return $this->layout->view('mendiants/ruelle_fermee', $vars);
		}

		return false;
	}

	private function miserabilisme()
	{
		// ---------- Hook clans ----------
		// Miserabilisme (Organisation)
		$miserabilisme = $this->bouzouk->clans_miserabilisme();

		if (isset($miserabilisme))
		{
			$nom_clan = ($miserabilisme->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('Un clan') : couleur(form_prep($miserabilisme->nom_clan));
			$this->load->library('lib_parser');
			
			// On affiche
			$vars = array(
				'nom_clan' => $miserabilisme->nom_clan,
				'texte'	   => $miserabilisme->parametres['texte']
			);
			return $this->layout->view('mendiants/miserabilisme', $vars);
		}

		return false;
	}

	private function racket_aux_pochtrons()
	{
		// ---------- Hook clans ----------
		// Racket aux pochtrons (Organisation)
		$query = $this->db->select('caa.parametres, c.nom AS nom_clan, c.mode_recrutement')
						  ->from('clans_actions_lancees caa')
						  ->join('clans c', 'c.id = caa.clan_id')
						  ->where('caa.action_id', 20)
						  ->where('caa.statut', Bouzouk::Clans_ActionEnCours)
						  ->get();
		return ($query->num_rows() == 1) ? $query->row() : null;
	}

	public function index()
	{
		$this->liste();
	}

	public function mendier()
	{
		// ---------- Hook clans ----------
		// Censure des mendiants (CdBM)
		if (($retour = $this->ruelle_fermee()) !== false)
			return $retour;

		// ---------- Hook clans ----------
		// Misérabilisme (Organisation)
		if (($retour = $this->miserabilisme()) !== false)
			return $retour;
			
		// On vérifie que le joueur n'est pas déjà en train de mendier
		$deja_mendiant = $this->db->where('joueur_id', $this->session->userdata('id'))
								  ->count_all_results('mendiants');

		if ($deja_mendiant > 0)
		{
			if ($this->racket_aux_pochtrons() != null)
				$message = "Les mendiants sont en train de se faire racketter par une organisation, tu n'as rien récolté.";

			else
			{
				// On récupère les sommes totales perçues
				$query = $this->db->select('SUM(montant) AS montant_total')
								  ->from('donations')
								  ->where('type', Bouzouk::Donation_Mendiant)
								  ->where('joueur_id', $this->session->userdata('id'))
								  ->get();
				$mendiant = $query->row();

				if ($mendiant->montant_total == '')
					$mendiant->montant_total = 0;

				$message = "Tu es déjà en train de mendier et tu as récolté : ".struls($mendiant->montant_total).'.';
			}
			
			// On va chercher la liste des mendiants
			$query = $this->db->select('j.id, j.pseudo, j.rang')
							  ->from('mendiants m')
							  ->join('joueurs j', 'j.id = m.joueur_id')
							  ->order_by('pseudo')
							  ->get();
			$mendiants = $query->result();

			$vars = array(
				'message'		=> $message,
				'mendiants'		=> $mendiants,
				'table_smileys' => creer_table_smileys('message')
			);
			return $this->layout->view('mendiants/en_train_mendier', $vars);
		}

		// ---------- Hook clans ----------
		// Escroquerie à la mendicité (Organisation)
		$query = $this->db->select('ca.nom AS nom_action, c.nom AS nom_clan')
						  ->from('clans_actions_lancees caa')
						  ->join('clans_actions ca', 'ca.id = caa.action_id')
						  ->join('clans c', 'c.id = caa.clan_id')
						  ->where('caa.clan_id', $this->session->userdata('clan_id')[Bouzouk::Clans_TypeOrganisation])
						  ->where('caa.action_id', 21)
						  ->where('caa.statut', Bouzouk::Clans_ActionEnCours)
						  ->get();
		$escroquerie_mendicite = ($query->num_rows() == 1) ? $query->row() : null;

		if ( ! isset($escroquerie_mendicite))
		{
			// On regarde la fortune totale du joueur
			$fortune = $this->bouzouk->fortune_totale();

			// On vérifie que la fortune totale est inférieure à 50 struls ou supérieure à 5000
			if ($fortune['total'] > $this->bouzouk->config('mendiants_fortune_max_mendier') AND $fortune['total'] < $this->bouzouk->config('mendiants_fortune_min_mendier'))
			{
				$vars = array(
					'titre_layout' => 'Mendier',
					'titre'        => 'Tu ne peux pas mendier',
					'image_url'    => 'mendiants/aucun_mendiant.gif',
					'message'      => 'Ta fortune totale est estimée à '.struls($fortune['total']).' :<br>'.
									  '<ul style="margin-left: 250px">'.
									  '<li>Struls : '.struls($fortune['struls']).'</li>'.
									  '<li>Objets maison : '.struls($fortune['maison']).'</li>'.
									  '<li>Objets marché noir : '.struls($fortune['marche_noir']).'</li>'.
									  '</ul>'.
									  "Tu n'es ni assez pauvre ni assez riche pour aller mendier"
				);
				return $this->layout->view('blocage', $vars);
			}
		}

		$vars = array(
			'escroquerie_mendicite' => $escroquerie_mendicite
		);

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('message', 'Le message', 'required|min_length[15]|max_length[250]');

		if ( ! $this->form_validation->run())
		{
			return $this->layout->view('mendiants/mendier', $vars);
		}

		// On insère le joueur chez les mendiants
		$data_mendiants = array(
			'joueur_id' => $this->session->userdata('id'),
			'argument'  => $this->input->post('message'),
			'date'      => bdd_datetime(),
			'riche'     => isset($escroquerie_mendicite) ? true : ($fortune['total'] >= $this->bouzouk->config('mendiants_fortune_min_mendier'))
		);
		$this->db->insert('mendiants', $data_mendiants);

		// On ajoute à l'historique
		$this->bouzouk->historique(118, null, array());

		// On met à jour la session
		$this->bouzouk->augmente_version_session();
		
		// On affiche une confirmation
		$this->succes('Félicitations, tu es maintenant en train de mendier !');
		return $this->mendier();
	}
	
	/* Pour la modération de la machine à café */
	public function machine_a_cafe()
	{
		// On va chercher la liste des mendiants
		$query = $this->db->select('j.id, j.pseudo, j.rang')
						  ->from('mendiants m')
						  ->join('joueurs j', 'j.id = m.joueur_id')
						  ->order_by('pseudo')
						  ->get();
		$mendiants = $query->result();
		
		$vars = array(
			'message'		=> false,
			'mendiants'		=> $mendiants,
			'table_smileys' => creer_table_smileys('message')
		);
		return $this->layout->view('mendiants/en_train_mendier', $vars);
	}

	public function liste()
	{
		// ---------- Hook clans ----------
		// Censure des mendiants (CdBM)
		if (($retour = $this->ruelle_fermee()) !== false)
			return $retour;

		// ---------- Hook clans ----------
		// Misérabilisme (Organisation)
		if (($retour = $this->miserabilisme()) !== false)
			return $retour;
			
		// On va chercher quelques mendiants aléatoirement à qui le joueur n'a pas donné
		$query = $this->db->select('m.argument, j.id, j.pseudo, j.faim, j.sante, j.stress, j.perso')
						  ->from('mendiants m')
						  ->join('joueurs j', 'j.id = m.joueur_id')
						  ->where('m.joueur_id NOT IN (SELECT joueur_id FROM donations WHERE type = '.Bouzouk::Donation_Mendiant.' AND donateur_id = '.$this->session->userdata('id').')')
						  ->order_by('m.id', 'random')
						  ->limit(15)
						  ->get();
		$mendiants = $query->result();

		// On va chercher les mendiants à qui le joueur a déjà donné
		$query = $this->db->select('m.argument, j.id, j.pseudo, j.faim, j.sante, j.stress, j.perso, d.montant')
						  ->from('donations d')
						  ->join('mendiants m', 'm.joueur_id = d.joueur_id')
						  ->join('joueurs j', 'j.id = m.joueur_id')
						  ->where('d.donateur_id', $this->session->userdata('id'))
						  ->where('d.type', Bouzouk::Donation_Mendiant)
						  ->order_by('d.id', 'desc')
						  ->get();
		$mendiants_donne = $query->result();

		// On compte le nombre de mendiants total
		$nb_mendiants = $this->db->count_all('mendiants');

		$vars = array(
			'mendiants'       => $mendiants,
			'mendiants_donne' => $mendiants_donne,
			'nb_mendiants'    => $nb_mendiants
		);
		return $this->layout->view('mendiants/liste', $vars);
	}

	public function donner()
	{
		// ---------- Hook clans ----------
		// Censure des mendiants (CdBM)
		if (($retour = $this->ruelle_fermee()) !== false)
			return $retour;

		// ---------- Hook clans ----------
		// Misérabilisme (Organisation)
		if (($retour = $this->miserabilisme()) !== false)
			return $retour;
		
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('joueur_id', 'Le joueur', 'required|is_natural_no_zero');
		$this->form_validation->set_rules('montant', 'Le montant', 'required|is_natural_no_zero|less_than_or_equal['.$this->bouzouk->config('mendiants_don_max').']');

		if ( ! $this->form_validation->run())
			return $this->liste();

		$_POST['montant'] = round($_POST['montant'], 1);

		// On vérifie que le joueur ne veut pas se donner à lui-même
		if ($this->input->post('joueur_id') == $this->session->userdata('id'))
		{
			$this->echec('Tu ne peux pas te donner à toi-même');
			return $this->liste();
		}

		// On vérifie que le joueur a assez de struls pour donner
		if ($this->session->userdata('struls') < $this->input->post('montant'))
		{
			$this->echec("Tu n'as pas assez de struls pour faire une donation de ".struls($this->input->post('montant')));
			return $this->liste();
		}

		// On regarde si le mendiant existe
		$query = $this->db->select('j.id, j.pseudo, j.sexe, m.riche')
						  ->from('mendiants m')
						  ->join('joueurs j', 'j.id = m.joueur_id')
						  ->where('m.joueur_id', $this->input->post('joueur_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Ce mendiant n'existe pas");
			return $this->liste();
		}

		$mendiant = $query->row();

		// On regarde si le joueur a déjà donné à ce mendiant
		$deja_donne = $this->db->where('donateur_id', $this->session->userdata('id'))
							   ->where('joueur_id', $this->input->post('joueur_id'))
							   ->where('type', Bouzouk::Donation_Mendiant)
							   ->count_all_results('donations');

		if ($deja_donne > 0)
		{
			$this->echec('Tu as déjà donné à ce mendiant, choisis-en un autre');
			return $this->liste();
		}

		// On retire la somme du joueur
		$this->bouzouk->retirer_struls($this->input->post('montant'));

		// Si c'est le premier don de la journée qui peut rapporter de l'xp
		$nb_dons = $this->db->where('donateur_id', $this->session->userdata('id'))
							->where('type', Bouzouk::Donation_Mendiant)
							->where('montant >= '.$this->bouzouk->config('mendiants_don_min_xp'))
							->count_all_results('donations');

		// On enregistre la donation
		$data_donations = array(
			'donateur_id' => $this->session->userdata('id'),
			'joueur_id'   => $this->input->post('joueur_id'),
			'montant'     => $this->input->post('montant'),
			'date'        => bdd_datetime(),
			'type'        => Bouzouk::Donation_Mendiant
		);
		$this->db->insert('donations', $data_donations);

		// ---------- Hook clans ----------
		// Racket aux pochtrons (Organisation)
		$racket_aux_pochtrons = $this->racket_aux_pochtrons();

		$this->load->library('lib_notifications');
		
		if (isset($racket_aux_pochtrons))
		{
			$racket_aux_pochtrons->parametres = unserialize($racket_aux_pochtrons->parametres);
			$nom_clan = ($racket_aux_pochtrons->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('Un clan') : couleur(form_prep($racket_aux_pochtrons->nom_clan));

			// On ajoute la somme au membre du clan
			$this->bouzouk->ajouter_struls($this->input->post('montant'), $racket_aux_pochtrons->parametres['joueur_id']);

			// On envoit une notification au membre du clan
			if ($this->lib_notifications->notifier(Bouzouk::Notification_DonMendiant, $mendiant->id))
				$this->bouzouk->notification(221, array(struls($this->input->post('montant')), profil($mendiant->id, $mendiant->pseudo)), $racket_aux_pochtrons->parametres['joueur_id']);
			
			// On ajoute à l'historique du membre du clan
			$this->bouzouk->historique(221, null, array(struls($this->input->post('montant')), profil($mendiant->id, $mendiant->pseudo)), $racket_aux_pochtrons->parametres['joueur_id']);

			// Le joueur perd de l'xp
			$perte_xp = $this->bouzouk->config('mendiants_perte_xp_riche');
			$this->bouzouk->retirer_experience($perte_xp);

			// On ajoute à l'historique du donateur
			$message = 'Tu as fait un don de '.struls($this->input->post('montant')).' à '.profil($mendiant->id, $mendiant->pseudo)." mais tu t'es fait racketter la somme par $nom_clan";
			$this->bouzouk->historique(119, null, array(struls($this->input->post('montant')), profil($mendiant->id, $mendiant->pseudo), $nom_clan));

			// On affiche une confirmation
			$this->succes($nom_clan." est en train de racketter les mendiants, le don que tu as fait est parti dans la poche d'un de ses membres, de plus tu perds <span class='pourpre'>-$perte_xp xp</span>");
			return $this->liste();		
		}

		// On ajoute la somme au mendiant
		$this->bouzouk->ajouter_struls($this->input->post('montant'), $this->input->post('joueur_id'));

		$merci = ($mendiant->sexe == 'male') ? ', merci pour lui ;)' : ', merci pour elle ;)';
		
		if ($nb_dons == 0 AND $this->input->post('montant') >= $this->bouzouk->config('mendiants_don_min_xp'))
		{
			// Donation à un riche
			if ($mendiant->riche)
			{
				$perte_xp = $this->bouzouk->config('mendiants_perte_xp_riche');
				$this->bouzouk->retirer_experience($perte_xp);

				// On ajoute à l'historique du donateur
				$this->bouzouk->historique(120, null, array(struls($this->input->post('montant')), profil($mendiant->id, $mendiant->pseudo), $perte_xp));

				// On affiche un message de confirmation
				$this->session->set_userdata('flash_succes', 'Tu as fais don de '.struls($this->input->post('montant')).' à '.profil($mendiant->id, $mendiant->pseudo).
																"...ah ben c'est malin tu viens de donner à un riche ! Avec tous ces mendiants qui sont dans le besoin tu devrais
																avoir honte ! Tu viens de perdre <span class='pourpre'>-$perte_xp xp</span>");
			}

			// Donation à un pauvre
			else
			{
				$gain_xp = $this->bouzouk->config('mendiants_gain_xp_pauvre');
				$this->bouzouk->ajouter_experience($gain_xp);

				// On ajoute à l'historique du donateur
				$message = 'Tu as fait un don de '.struls($this->input->post('montant')).' à '.profil($mendiant->id, $mendiant->pseudo).', tu gagnes <span class="pourpre">+'.$gain_xp.' xp</span>';
				$this->bouzouk->historique(121, null, array(struls($this->input->post('montant')), profil($mendiant->id, $mendiant->pseudo), $gain_xp));

				// On affiche un message de confirmation
				$this->succes($message.$merci);
			}

			// On envoit une notification au mendiant
			if ($this->lib_notifications->notifier(Bouzouk::Notification_DonMendiant, $mendiant->id))
				$this->bouzouk->notification(122, array(profil(), struls($this->input->post('montant'))), $mendiant->id);
			
			// On ajoute à l'historique du mendiant
			$this->bouzouk->historique(122, null, array(profil(), struls($this->input->post('montant'))), $mendiant->id);
			
			return $this->liste();
		}

			// On envoit une notification au mendiant
			if ($this->lib_notifications->notifier(Bouzouk::Notification_DonMendiant, $mendiant->id))
				$this->bouzouk->notification(122, array(profil(), struls($this->input->post('montant'))), $mendiant->id);
			
			// On ajoute à l'historique du mendiant
			$this->bouzouk->historique(122, null, array(profil(), struls($this->input->post('montant'))), $mendiant->id);

		// On ajoute à l'historique du donateur
		$message = 'Tu as fait un don de '.struls($this->input->post('montant')).' à '.profil($mendiant->id, $mendiant->pseudo);
		$this->bouzouk->historique(220, null, array(struls($this->input->post('montant')), profil($mendiant->id, $mendiant->pseudo)));

		// On affiche un message de confirmation
		$this->succes($message.$merci);
		return $this->liste();
	}

	public function donner_miserabilisme()
	{
		// ---------- Hook clans ----------
		// Misérabilisme (Organisation)
		$trou_du_culte = $this->bouzouk->clans_miserabilisme();

		if ( ! isset($trou_du_culte))
			show_404();

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('montant', 'Le montant', 'required|is_natural_no_zero|less_than_or_equal['.$this->bouzouk->config('mendiants_don_max').']');

		if ( ! $this->form_validation->run())
			return $this->liste();

		// On vérifie que le joueur ne se donne pas à lui-même
		if ($this->session->userdata('id') == $trou_du_culte->parametres['joueur_id'])
		{
			$this->echec("Tu veux te donner à toi-même ? C'est pas comme ça que tu vas aider cette organisation...");
			return $this->liste();
		}

		// On vérifie que le joueur a assez de struls pour donner
		if ($this->session->userdata('struls') < $this->input->post('montant'))
		{
			$this->echec("Tu n'as pas assez de struls pour faire une donation de ".struls($this->input->post('montant')));
			return $this->liste();
		}

		// On retire les struls au joueur
		$this->bouzouk->retirer_struls($this->input->post('montant'));

		// Si c'est le premier don de la journée qui peut rapporter de l'xp
		$nb_dons = $this->db->where('donateur_id', $this->session->userdata('id'))
							->where('type', Bouzouk::Donation_Mendiant)
							->count_all_results('donations');

		if ($nb_dons > 0)
		{
			$this->echec("Tu as déjà donné à ce clan aujourd'hui");
			return $this->liste();
		}

		// On enregistre la donation
		$data_donations = array(
			'donateur_id' => $this->session->userdata('id'),
			'joueur_id'   => $trou_du_culte->parametres['joueur_id'],
			'montant'     => $this->input->post('montant'),
			'date'        => bdd_datetime(),
			'type'        => Bouzouk::Donation_Mendiant
		);
		$this->db->insert('donations', $data_donations);

		// Le joueur gagne entre -5 et +5 xp
		$xp = mt_rand(-5, 5);
		
		if ($xp < 0)
		{
			$this->bouzouk->retirer_experience($xp);
			$historique_xp = ' et tu perds <span class="pourpre">'.$xp.' xp</span>';
		}

		else
		{
			$this->bouzouk->ajouter_experience($xp);
			$historique_xp = ' et tu gagnes <span class="pourpre">+'.$xp.' xp</span>';
		}
	
		// On ajoute à l'historique du joueur
		$message = 'Tu as fait un don de '.struls($this->input->post('montant')).' au clan '.$trou_du_culte->nom_clan.$historique_xp;
		$this->bouzouk->historique(123, null, array($this->input->post('montant'), $trou_du_culte->nom_clan, $historique_xp));

		// On envoit le don au membre
		$this->bouzouk->ajouter_struls($this->input->post('montant'), $trou_du_culte->parametres['joueur_id']);

		$this->load->library('lib_notifications');
		// On envoit une notification/historique au membre
		if ($this->lib_notifications->notifier(Bouzouk::Notification_DonMendiant, $trou_du_culte->parametres['joueur_id']))
			$this->bouzouk->notification(124, array(profil(), struls($this->input->post('montant'))), $trou_du_culte->parametres['joueur_id']);
		
		$this->bouzouk->historique(124, null, array(profil(), struls($this->input->post('montant'))), $trou_du_culte->parametres['joueur_id']);

		// On affiche une confirmation
		$this->succes($message);
		return $this->liste();
	}
}