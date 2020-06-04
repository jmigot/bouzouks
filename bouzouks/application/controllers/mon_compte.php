<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions de gestion du compte du joueur pour permettre le changement de mot de passe,
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

class Mon_compte extends MY_Controller
{
	private $robots;
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');

		$this->robots = array();
		
		// Certains bouzouks peuvent se connecter sur un autre compte du tobozon
		if ($this->bouzouk->is_admin()) // admins
		{
			$query = $this->db->select('id, pseudo, mot_de_passe')
							  ->from('joueurs')
							  ->where_in('id', $this->bouzouk->get_robots())
							  ->or_where_in('id', $this->bouzouk->get_clans())
							  ->or_where('id', $this->session->userdata('id'))
							  ->order_by('pseudo')
							  ->get();
			$this->robots = $query->result();
		}

		else if ($this->session->userdata('tobozon_ids') != '')
		{
			$query = $this->db->select('id, pseudo, mot_de_passe')
							  ->from('joueurs')
							  ->where_in('id', explode(',', $this->session->userdata('tobozon_ids')))
							  ->get();

			if ($query->num_rows() > 0)
				$this->robots = $query->result();
		}
	}

	public function index()
	{
		// On va chercher le total des dons allopass
		$query = $this->db->select('SUM(montant) AS total_euros, SUM(struls) AS total_struls, COUNT(id) AS nb_dons')
						  ->from('plus_de_struls')
						  ->where('joueur_id', $this->session->userdata('id'))
						  ->get();
		$dons_allopass = $query->row();

		// On va chercher le total des dons paypal
		$query = $this->db->select('SUM(montant) AS total_euros, COUNT(id) AS nb_dons')
						  ->from('dons_paypal')
						  ->where('joueur_id', $this->session->userdata('id'))
						  ->get();
		$dons_paypal = $query->row();

		// On va chercher le mail fb
		$query = $this->db->select('fb_mail')->where('id', $this->session->userdata('id'))->get('joueurs');
		if($query->num_rows > 0){
			$query = $query->row();
			$mail_fb = $query->fb_mail;
		}
		else{
			$mail_fb = '';
		}
		// On affiche
		$vars = array(
			'dons_allopass' => $dons_allopass,
			'dons_paypal'   => $dons_paypal,
			'robots'        => $this->robots,
			'mail_fb'		=> $mail_fb
		);
		return $this->layout->view('mon_compte/index', $vars);
	}
	
	public function bouzouk()
	{
		$this->load->library('lib_parser');
		
		// On regarde si le joueur utilise son avatar toboz
		$query = $this->db->select('utiliser_avatar_toboz')
						  ->from('joueurs')
						  ->where('id', $this->session->userdata('id'))
						  ->get();
		$utiliser_avatar_toboz = $query->row()->utiliser_avatar_toboz;

		// On affiche
		$vars = array(
			'utiliser_avatar_toboz' => $utiliser_avatar_toboz
		);
		return $this->layout->view('mon_compte/bouzouk', $vars);
	}
	
	public function notifications()
	{
		$this->load->library('lib_notifications');
		
		$vars = array(
			'notifications' => $this->lib_notifications->liste_notification(),
		);
		
		return $this->layout->view('mon_compte/notifications', $vars);
	}
	
	public function changer_notifications()
	{
		// Règles de validation
		$this->form_validation->set_rules('notifs', 'Les notifications', 'required');
		
		if ( ! $this->form_validation->run())
			return $this->index();
		
		$this->load->library('lib_notifications');
		$notifications = $this->lib_notifications->liste_notification();
		
		// On vérifie que la notification existe et on la modifie
		foreach ($this->input->post('notifs') as $notif => $value)
			if (array_key_exists($notif, $notifications))
				$this->lib_notifications->modifier_notification((int)$notif, (int)$value);
		
		$this->succes('Tes notifications ont bien été changées');
		return $this->notifications();
	}

	public function changer_mot_de_passe()
	{
		// Règles de validation
		$this->form_validation->set_rules('nouveau_mot_de_passe', 'Le nouveau mot de passe', 'required|min_length[6]|max_length[30]');
		$this->form_validation->set_rules('ancien_mot_de_passe', 'Le mot de passe actuel', 'required|min_length[6]|max_length[30]');

		if ( ! $this->form_validation->run())
			return $this->index();

		$query = $this->db->select('mot_de_passe')
						  ->from('joueurs')
						  ->where('id', $this->session->userdata('id'))
						  ->get();
		$joueur = $query->row();

		// Le mot de passe actuel doit être bon
		if (sha1($this->input->post('ancien_mot_de_passe')) != $joueur->mot_de_passe)
		{
			$this->echec('Le mot de passe actuel est incorrect');
			return $this->index();
		}

		// Le nouveau mot de passe doit être différent de l'ancien
		if ($this->input->post('nouveau_mot_de_passe') == $this->input->post('ancien_mot_de_passe'))
		{
			$this->echec("Le nouveau mot de passe est le même que l'ancien...");
			return $this->index();
		}

		$mot_de_passe = sha1($this->input->post('nouveau_mot_de_passe'));

		// On met à jour le mot de passe en base
		$data_joueur = array(
			'mot_de_passe' => $mot_de_passe
		);
		$this->db->where('id', $this->session->userdata('id'))
					->update('joueurs', $data_joueur);

		// On met à jour le mot de passe sur me tobozon
		$data_users = array(
			'password' => $mot_de_passe
		);
		$this->db->where('id', $this->session->userdata('id'))
					->update('tobozon_users', $data_users);

		// On ajoute à l'historique
		$this->bouzouk->historique(126);

		// On affiche une confirmation
		$this->succes('Ton mot de passe a bien été changé');
		return $this->index();
	}

	public function _email_check($email)
	{
		if ( ! $this->lib_email->email_valide($email))
		{
			$this->form_validation->set_message('_email_check', '%s est invalide (emails autorisés : '.$this->lib_email->emails_autorises().')');
			return false;
		}

		if ($this->lib_email->email_existe($email))
		{
			$this->form_validation->set_message('_email_check', '%s existe déjà dans la ville de Vlurxtrznblax !');
			return false;
		}

		return true;
	}
	
	public function changer_email()
	{
		$this->load->library('lib_email');
		
		// Règles de validation
		$this->form_validation->set_rules('email', 'L\'email', 'required|valid_email|callback__email_check');
		$this->form_validation->set_rules('mot_de_passe', 'Le mot de passe', 'required|min_length[6]|max_length[30]');

		if ( ! $this->form_validation->run())
			return $this->index();

		// On va chercher le mot de passe du joueur
		$query = $this->db->select('mot_de_passe')
						  ->from('joueurs')
						  ->where('id', $this->session->userdata('id'))
						  ->get();
		$joueur = $query->row();

		// Le mot de passe actuel doit être bon
		if (sha1($this->input->post('mot_de_passe')) != $joueur->mot_de_passe)
		{
			$this->echec('Le mot de passe est incorrect');
			return $this->index();
		}

		// La nouvelle adresse email doit être différente de l'ancienne
		if ($this->lib_email->nettoyer_email($this->input->post('email')) == $this->session->userdata('email'))
		{
			$this->echec("La nouvelle adresse email est la même que l'ancienne...");
			return $this->index();
		}

		// On vérifie s'il n'y a pas déjà une demande de changement d'email pour ce compte
		$nb_codes = $this->db->where('joueur_id', $this->session->userdata('id'))
							 ->where('type', Bouzouk::Code_ChangerEmail)
							 ->count_all_results('codes_aleatoires');

		if ($nb_codes > 0)
		{
			$this->echec("Il y a déjà une demande de changement d'adresse email en cours pour ce compte, vérifie ta boîte mail. Sinon attends que ta demande soit supprimée automatiquement (dans 24h maximum).");
			return $this->index();
		}

		// On insère un code aléatoire en base
		$this->load->helper('string');
		$this->load->helper('date');

		$data_code_aleatoire = array(
			'joueur_id' => $this->session->userdata('id'),
			'code'      => random_string('alnum', 8),
			'type'      => Bouzouk::Code_ChangerEmail,
			'date'      => bdd_datetime(),
			'valeur'    => $this->lib_email->nettoyer_email($this->input->post('email'))
		);
		$this->db->insert('codes_aleatoires', $data_code_aleatoire);

		// On prépare l'email
		$vars = array(
			'pseudo'         => $this->session->userdata('pseudo'),
			'date'           => mdate('%d/%m/%Y à %H:%i', strtotime($data_code_aleatoire['date'])),
			'code_aleatoire' => $data_code_aleatoire['code'],
		);
		$email = $this->load->view('email/changer_email', $vars, true);

		$this->load->library('email');
		$this->email->from($this->bouzouk->config('email_from'), 'Bouzouks')
					->to($this->input->post('email'))
					->subject("[Bouzouks.net] Changement d'adresse email")
					->message($email);

		// On ajoute à l'historique
		$this->bouzouk->historique(127, null, array($this->session->userdata('email'), $this->lib_email->nettoyer_email($this->input->post('email'))));

		// On envoit l'email
		if ( ! $this->email->send())
			$this->echec("Une erreur est survenue lors de l'envoi de l'email");

		else
			$this->succes('Un mail a été envoyé sur ton adresse, tu dois cliquer sur le lien donné pour valider ton adresse');

		return $this->index();
	}

	public function changer_email_confirmation()
	{
		// On transforme GET en POST, car on peut utiliser GET depuis un email
		if ($this->input->get('code') !== false)
			$_POST['code'] = $this->input->get('code');

		// Règles de validation
		$this->form_validation->set_rules('code', 'Le code', 'required|alpha_numeric|exact_length[8]');

		if ( ! $this->form_validation->run())
			return $this->layout->view('mon_compte/changer_email_confirmation');

		// On va chercher les infos du joueur
		$query = $this->db->select('j.id, j.pseudo, j.statut, c.code, c.type, c.valeur')
						  ->from('joueurs j')
						  ->join('codes_aleatoires c', 'c.joueur_id = j.id AND c.type = '.Bouzouk::Code_ChangerEmail)
						  ->where('j.id', $this->session->userdata('id'))
						  ->get();

		// On vérifie qu'il y a bien un code
		if ($query->num_rows() == 0)
		{
			$this->echec("Il n'y a aucune demande de changement d'email pour ton compte");
			return $this->layout->view('mon_compte/changer_email_confirmation');
		}

		$joueur = $query->row();

		// On vérifie que le code d'activation est bon
		if ($joueur->code != $this->input->post('code'))
		{
			$this->echec("Le code n'est pas bon");
			return $this->layout->view('mon_compte/changer_email_confirmation');
		}

		// On ajoute à l'historique
		$this->bouzouk->historique(128, null, array($this->session->userdata('email'), $joueur->valeur));

		// On change l'email dans le jeu et sur le forum
		$this->db->set('email', $joueur->valeur)
				 ->where('id', $joueur->id)
				 ->update('joueurs');
		$this->db->set('email', $joueur->valeur)
				 ->where('id', $joueur->id)
				 ->update('tobozon_users');
		$this->session->set_userdata('email', $joueur->valeur);

		// On supprime le code aléatoire
		$this->db->where('joueur_id', $joueur->id)
				 ->where('type', Bouzouk::Code_ChangerEmail)
				 ->delete('codes_aleatoires');

		// On affiche une confirmation
		$this->succes('Ton email a bien été changé');
		return $this->layout->view('message', array('titre' => "Changement d'email"));
	}

	public function _sexe_check($sexe)
	{
		if ( ! in_array($sexe, array('male', 'femelle'), true))
		{
			$this->form_validation->set_message('_sexe_check', 'Nous n\'acceptons pas ce genre de sexe à Vlurxtrznblax');
			return false;
		}

		return true;
	}

	public function changer_bouzouk()
	{
		// Règles de validation
		$this->form_validation->set_rules('sexe', 'Le sexe de ton bouzouk', 'required|callback__sexe_check');
		$this->form_validation->set_rules('jour', 'Le jour', 'required|is_natural_no_zero|greater_than[0]|less_than[32]');
		$this->form_validation->set_rules('mois', 'Le mois', 'required|is_natural_no_zero|greater_than[0]|less_than[13]');
		$this->form_validation->set_rules('annee', "L'année", 'required|is_natural_no_zero|greater_than[1900]|less_than['.date('Y').']');
		$this->form_validation->set_rules('commentaire', 'Le commentaire', 'max_length[1000]');
		$this->form_validation->set_rules('adresse', "L'adresse", 'required|min_length[15]|max_length[50]|regex_match[#^[a-zA-Z0-9éèàâêôîù ,\'-]+$#]');

		if ( ! $this->form_validation->run())
			return $this->index();

		$utiliser_avatar_toboz = $this->input->post('utiliser_avatar_toboz') != false ? '1' : '0';

		// On vérifie que le bouzouk choisi existe bien
		$sexe = $this->input->post('sexe');
		$persos = $this->bouzouk->get_persos();
		if ( ! array_key_exists($this->input->post('perso_'.$sexe), $persos[$sexe]))
		{
			$this->echec("Ce bouzouk n'existe pas");
			return $this->bouzouk();
		}

		// On vérifie que la date de naissance est correcte
		if ( ! checkdate($this->input->post('mois'), $this->input->post('jour'), $this->input->post('annee')))
		{
			$this->echec('La date de naissance est invalide');
			return $this->bouzouk();
		}

		// Si utiliser avatar perso est coché
		if ($utiliser_avatar_toboz == '1')
		{
			// on vérifie que le joueur n'est pas interdit
			if ($this->session->userdata('interdit_avatar'))
			{
				$this->echec('La date de naissance est invalide');
				return $this->bouzouk();
			}

			// On vérifie que l'avatar existe
			$avatars = glob(FCPATH.'tobozon/img/avatars/'.$this->session->userdata('id').'.*');
			
			if (count($avatars) != 1)
			{
				$this->echec("Tu n'as pas défini d'avatar sur le Toboz, tu dois en uploader un pour pouvoir l'utiliser sur le site");
				return $this->bouzouk();
			}
		}

		$cout_total = 0;

		$prix_sexe        = $this->bouzouk->config('mon_compte_struls_changer_sexe');
		$prix_perso       = $this->bouzouk->config('mon_compte_struls_changer_perso');
		$prix_naissance   = $this->bouzouk->config('mon_compte_struls_changer_naissance');
		$prix_commentaire = $this->bouzouk->config('mon_compte_struls_changer_commentaire');
		$prix_adresse     = $this->bouzouk->config('mon_compte_struls_changer_adresse');

		// ---- Event Bouf'tête --------
		if($this->bouzouk->est_infecte($this->session->userdata('id'))){
			$perso = str_replace('zombi/', '', $this->session->userdata('perso')); 
		}
		else{
			$perso = $this->session->userdata('perso');
		}

		// Si le joueur veut changer de sexe
		if ($sexe != $this->session->userdata('sexe'))
			$cout_total += $prix_sexe;

		// Sinon si il veut juste changer de bouzouk

		else if ($this->input->post('perso_'.$sexe) != $perso)
			$cout_total += $prix_perso;

		// Si il veut changer de date de naissance
		if ($this->input->post('annee').'-'.$this->input->post('mois').'-'.$this->input->post('jour') != $this->session->userdata('date_de_naissance'))
			$cout_total += $prix_naissance;

		// Si il veut changer son commentaire
		if ($this->input->post('commentaire') != $this->session->userdata('commentaire'))
			$cout_total += $prix_commentaire;

		// Si le joueur veut changer d'adresse
		if ($this->input->post('adresse') != $this->session->userdata('adresse'))
			$cout_total += $prix_adresse;

		// Si le joueur n'a pas assez d'argent pour payer tous les changements
		if ($this->session->userdata('struls') < $cout_total)
		{
			$this->echec('Il te faut au moins '.struls($cout_total).' pour faire tous ces changements');
			return $this->bouzouk();
		}

		// On retire la somme total au joueur
		if ($cout_total > 0)
			$this->bouzouk->retirer_struls($cout_total);

		// On met à jour le sexe et le perso du joueur
		$data_joueur = array(
			'date_de_naissance'     => $this->input->post('annee').'-'.$this->input->post('mois').'-'.$this->input->post('jour'),
			'commentaire'           => $this->input->post('commentaire'),
			'sexe'                  => $this->input->post('sexe'),
			'perso'                 => $this->input->post('perso_'.$sexe),
			'adresse'               => $this->input->post('adresse'),
			'utiliser_avatar_toboz' => $utiliser_avatar_toboz
		);
		$this->db->where('id', $this->session->userdata('id'))
				 ->update('joueurs', $data_joueur);

		// On ajoute à l'historique
		if ($sexe != $this->session->userdata('sexe'))
			$this->bouzouk->historique(129, 130, array(struls($prix_sexe, false)));

		if ($this->input->post('perso_'.$sexe) != $this->session->userdata('perso'))
			$this->bouzouk->historique(222, 223, array(struls($prix_perso, false)));

		if ($this->input->post('annee').'-'.$this->input->post('mois').'-'.$this->input->post('jour') != $this->session->userdata('date_de_naissance'))
			$this->bouzouk->historique(131, null, array(struls($prix_naissance, false)));

		if ($this->input->post('commentaire') != $this->session->userdata('commentaire'))
			$this->bouzouk->historique(132, null, array(struls($prix_commentaire, false)));

		if ($this->input->post('adresse') != $this->session->userdata('adresse'))
			$this->bouzouk->historique(134, 133, array(struls($prix_adresse, false)));
		// ------ Event Bouf'tête ----------
		if($this->bouzouk->est_infecte($this->session->userdata('id'))){
			$data_joueur['perso'] = 'zombi/'.$data_joueur['perso'];
		}
		// ------- Event Mlbobz --------
		if($this->bouzouk->est_maudit_mlbobz($this->session->userdata('id'))){
			$data_joueur['perso'] = 'rp_zoukette/'.$data_joueur['perso'];
		}
		// Message de confirmation
		$this->session->set_userdata($data_joueur);
		$this->succes('Tes informations ont bien été changées; tu as été débité de <span class="pourpre">-'.struls($cout_total, false).'</span>. Voir mon profil : '.profil(-1));

		return $this->bouzouk();
	}

	public function mettre_en_pause()
	{
		// Règles de validation
		$this->form_validation->set_rules('mettre_en_pause', "L'appui sur le bouton \"Mettre en pause\"", 'required');

		if ( ! $this->form_validation->run())
			return $this->index();

		$payer_taxes = $this->input->post('payer_taxes') !== false;

		// On met le compte en pause
		$this->load->library('lib_joueur');
		$this->lib_joueur->mettre_pause($this->session->userdata('id'), $payer_taxes);

		// On redirige vers la page de pause
		redirect('joueur/en_pause');
	}

	public function connexion_tobozon()
	{
		// Règles de validation
		$this->form_validation->set_rules('robot_id', 'Le robot', 'required|is_natural_no_zero');
		
		if ( ! $this->form_validation->run())
			return $this->index();

		// On vérifie que le joueur est autorisé à se connecter sur ce robot
		$autorisation = false;
		
		foreach ($this->robots as $robot)
		{
			if ($robot->id == $this->input->post('robot_id'))
			{
				$autorisation = true;
				break;
			}
		}

		if ( ! $autorisation)
		{
			$this->echec("Tu n'as pas l'autorisation de te conneter sur ce compte");
			return $this->index();
		}

		// On connecte au tobozon
		$this->load->library('lib_tobozon');
		$this->lib_tobozon->connecter($robot);

		// On affiche
		$this->succes("Tu es bien connecté sur le compte Tobozon de <span class='pourpre'>".$robot->pseudo.'</span>');
		return $this->index();
	}

	public function parrainer()
	{
		$this->load->library('lib_email');

		// Règles de validation
		$this->form_validation->set_rules('email', "L'email", 'required|valid_email|callback__email_check');
		
		if ( ! $this->form_validation->run())
			return $this->index();

		// On regarde si le joueur a déjà reçu une demande de la part de ce joueur
		$deja_demande = $this->db->where('joueur_id', $this->session->userdata('id'))
								 ->where('email', $this->input->post('email'))
								 ->count_all_results('parrainages_demandes');

		if ($deja_demande)
		{
			$this->echec("Tu as déjà envoyé une invitation à cette adresse email !");
			return $this->index();
		}

		// On enregistre la demande
		$data_parrainages_demandes = array(
			'joueur_id' => $this->session->userdata('id'),
			'email'     => $this->input->post('email'),
			'date'      => bdd_datetime()
		);
		$this->db->insert('parrainages_demandes', $data_parrainages_demandes);

		// On envoit l'email
		$vars = array(
			'pseudo' => $this->session->userdata('pseudo'),
		);
		$email = $this->load->view('email/parrainer', $vars, true);

		$this->load->library('email');
		$this->email->from($this->bouzouk->config('email_from'), 'Bouzouks')
					->to($this->input->post('email'))
					->subject("[Bouzouks.net] Invitation à jouer de la part de ".$this->session->userdata('pseudo'))
					->message($email);

		// On envoit l'email
		if ( ! $this->email->send())
			$this->echec("Une erreur est survenue lors de l'envoi de l'email");
		
		else
			$this->succes("Un mail d'invitation a bien été envoyé à l'adresse ".couleur(form_prep($this->input->post('email'))).". Ton ami pourra cliquer sur le lien, ton pseudo sera automatiquement renseigné comme parrain :)");

		return $this->index();
	}

	public function param_map(){
		// Titre de la page
		$vars['title'] = "Paramètres Map";

		// On récupère les paramètres du joueurs
		$this->load->library('vlux/vlux_param_joueur');
		$vars['params'] = $this->vlux_param_joueur->get_param($this->session->userdata('id'));
		$this->load->library('vlux/map_factory');
		$list_maps = $this->map_factory->list_own_maps($this->session->userdata('id'));
		foreach ($list_maps as $map) {
			$vars['list_maps'][$map['id']] = $map['nom'];
		}
		return $this->layout->view('mon_compte/param_map', $vars);
	}

	public function modifier_option_map(){
		$this->form_validation->set_rules(array(
			array(
				'field'		=> 'zoom_defaut',
				'label'		=> 'zomm par défaut',
				'rules'		=> 'required|callback_zoom_defaut_check'),
			array(
				'fiel'		=> 'son_notif',
				'label'		=> 'notif sonore',
				'rules'		=> 'is_boolean|required'),
			array(
				'field'		=> 'chan_defaut',
				'label'		=> 'onglet par défaut',
				'rules'		=> 'required|callback_chan_defaut_check'),
			array(
				'field'		=> 'affichage_pseudo',
				'label'		=> 'affichage du pseudo',
				'rules'		=> 'required|is_boolean'),
			array(
				'field'		=> 'res_principale',
				'label'		=> 'résidence principale',
				'rules'		=> 'required|callback_res_principale_check')
			)
		);

		if(!$this->form_validation->run()){
			return $this-> param_map();
		}
		else{
			$params = array(
				'zoom_defaut'		=> (float)$this->input->post('zoom_defaut'),
				'son_notif'			=> (int)$this->input->post('son_notif'),
				'chan_defaut'		=> $this->input->post('chan_defaut'),
				'affichage_pseudo'	=> (int)$this->input->post('affichage_pseudo'),
				'res_principale'		=> (int)$this->input->post('res_principale')
				);
			$this->load->library('vlux/vlux_param_joueur');
			$this->vlux_param_joueur->set_param($this->session->userdata('id'), $params);
			$this->succes('Tes préférences pour la map ont bien été changées');
			return $this->param_map();
		}
	}

	public function zoom_defaut_check(){
		$zoom_choices = array('0.4','0.8','1.2','1.6');
		if(!in_array(($this->input->post('zoom_defaut')), $zoom_choices)){
			$this->form_validation->set_message('zoom_defaut_check', 'Le niveau de zoom choisi est incorrect !');
			var_dump($this->input->post('zoom_defaut'));
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function chan_defaut_check(){
		$chans = array('map', 'global');
		if(!in_array($this->input->post('chan_defaut'), $chans)){
			$this->form_validation->set_message('chan_defaut_check', "La valeur pour %s est incorrect.");
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function res_principale_check(){
		$id_maps = array();
		// On récupère la liste des maps dont le joueur est proprio
		$this->load->library('vlux/map_factory');
		$list_maps = $this->map_factory->list_own_maps($this->session->userdata('id'));
		foreach($list_maps as $map){
			$id_maps[] = $map['id'];
		}
		if(!in_array($this->input->post('res_principale'), $id_maps)){
			$this->form_validation->set_message('res_principale_check', "La map choisie n'est pas correcte.");
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function assoc_fb(){
		$this->load->library('fb/fb_api');
		// On vérifie que le joueur n'ait pas encore d'id fb
		if(!$this->fb_api->is_registered()){
			// On se connect à l'api fb
			$redirect_url = $this->fb_api->get_login_url(base_url('mon_compte/assoc_fb_callback'));
			redirect($redirect_url);	
		}
		// Les comptes sont déjà associés
		else{
			redirect('/mon_compte');
		}
	}

	public function assoc_fb_callback(){
		$this->load->library('fb/fb_api');
		$this->load->library('lib_joueur');
		if($this->fb_api->connexion()){
			// On enregistre l'id fb du joueur
			$fb_id = $this->fb_api->get_id();
			$fb_mail = $this->fb_api->get_email();
			// Si 'lid fb n'est pas déjà enregistrée sur le site
			if(!$this->fb_api->is_exist_id($fb_id)){
				$this->lib_joueur->assoc_compte_fb($this->session->userdata('id'), $fb_id, $fb_mail);
				// On met à jour la session
				$this->session->set_userdata('fb_id', $fb_id);
			}
			// Sinon TODO alerte admin
			
		}
		redirect('mon_compte');
	}
}