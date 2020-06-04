<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : fonctions d'inscription au jeu (inscription, mot de passe perdu, connexion...)
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Visiteur extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->library('lib_email');
		$this->load->library('encrypt');
		$this->load->library('user_agent');
	}

	public function index()
	{
		$this->inscription();
	}


	/* Fonctions de callback pour la validation de formulaire d'inscription */
	public function _pseudo_check($pseudo)
	{
		// Mots interdits
		$pseudos_interdits = array(
			'admin', 'bouzouk', 'modo', 'moderat', 'root', 'police', 'election', 'salope', 'pd', 'con', 'connard', 'mairie', 'maire', 'webmaster', 'strul', 'webmestre',
			'staff', 'equipe', 'team', 'journal', 'allopass', 'loto', 'redac', 'pute', 'putain', 'ministere', 'ministère', 'corrupteur', 'dealer', 'percepteur', 'J.F Sébastien',
			'censeur', 'pochtron', 'Pooh', 'Lett', 'secte', 'shnibble', 'Poohsmurgl'
		);

		// Les pseudos des modérateurs et des admins sont interdits
		$query = $this->db->select('pseudo')
						  ->from('joueurs')
						  ->where('(rang & '.$this->bouzouk->get_masque(Bouzouk::Masque_Moderateur | Bouzouk::Masque_Admin).') > 0')
						  ->get();

		foreach ($query->result() as $joueur)
		{
			$pseudos_interdits[] = $joueur->pseudo;
		}

		// On vérifie que le pseudo n'est pas interdit
		if (preg_match('#'.implode('|', $pseudos_interdits).'#i', $pseudo))
		{
			$this->form_validation->set_message('_pseudo_check', '%s contient un mot qui est interdit dans la ville de Vlurxtrznblax !');
			return false;
		}

		// On vérifie que le premier caractères du pseudo est une lettre
		if ( ! preg_match('#^[a-zA-Z]#i', $pseudo))
		{
			$this->form_validation->set_message('_pseudo_check', '%s doit commencer par une lettre');
			return false;
		}

		return true;
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

	public function inscription()
	{	
		// callback de validation du formulaire
		$vars['validation_callback'] = 'inscription';
		// Règles de validation
		$this->form_validation->set_rules('pseudo', 'Le pseudo', 'required|min_length[3]|max_length[12]|alpha_dash|is_unique[joueurs.pseudo]|callback__pseudo_check');
		$this->form_validation->set_rules('mot_de_passe', 'Le mot de passe', 'required|min_length[6]|max_length[30]');
		$this->form_validation->set_rules('email', "L'email", 'required|valid_email|callback__email_check');
		$this->form_validation->set_rules('age', 'La case "age"', 'required');
		$this->form_validation->set_rules('charte', 'La case "charte"', 'required');
		$this->form_validation->set_rules('derniere', 'La case "jamais 2 sans 3"', 'required');
		$this->form_validation->set_rules('parrain', 'Le pseudo du parrain', 'min_length[3]|max_length[12]|alpha_dash');

		if ( ! $this->form_validation->run()){
				return $this->layout->view('visiteur/inscription', $vars);
		}
			
		// Interdiction aux proxys
		if ($this->is_proxy())
		{
			$this->echec("Ta connexion internet n'est pas autorisée sur ce serveur");
			return $this->layout->view('visiteur/inscription', $vars);
		}
		$this->load->library('lib_joueur');
		$data = $this->input->post(null);
		$data['ip'] = $this->input->ip_address();
		$checked_info = $this->lib_joueur->check_info_inscription($data);
		//SI il y a une erreur dans les info fournies
		if(isset($checked_info['erreur']['type'])){
			$this->echec($checked_info['erreur']['message']);
			return $this->layout->view('visiteur/inscription', $vars);
		}
		// Sinon, on enregistre le joueur
		else{
			// Protection contre le rechargement (à cause du check proxy)
			$deja_inscrit = $this->db->where('pseudo', $this->input->post('pseudo'))
									 ->count_all_results('joueurs');
			//Enregistrement en bdd
			if(!$deja_inscrit){
				if($this->session->userdata('fb_id')){
					$fb_id = $this->session->userdata('fb_id');
				}
				else{
					$fb_id = null;
				}
				$checked_info['fb_id'] = $fb_id;
				$checked_info['email'] = $this->input->post('email');
				$inscription = $this->lib_joueur->inscription($checked_info);
				// On prépare un code aléatoire pour confirmer l'inscription
				$this->load->helper('string');
				$data_code_aleatoire = array(
					'joueur_id' => $inscription['joueur_id'],
					'code'      => random_string('alnum', 8),
					'type'      => Bouzouk::Code_Inscription,
					'date'      => $inscription['date']
				);
				$this->db->insert('codes_aleatoires', $data_code_aleatoire);

				// On prépare l'email
				$vars = array(
					'pseudo'         => $this->input->post('pseudo'),
					'code_aleatoire' => $data_code_aleatoire['code']
				);
				$email = $this->load->view('email/inscription', $vars, true);

				$this->load->library('email');
				$this->email->from($this->bouzouk->config('email_from'), 'Bouzouks')
							->to($this->input->post('email'))
							->subject('[Bouzouks.net] Inscription')
							->message($email);
			}
		}
		// On envoit l'email
		if ( ! $deja_inscrit && ! $this->email->send())
		{
			$this->attention("Tu es bien inscrit mais une erreur est survenue lors de l'envoi du mail.".$inscription['charte']);
			return $this->layout->view('message', array('titre' => 'Inscription'));
		}

		// On affiche un message de confirmation
		$this->succes('Tu es maintenant inscrit sur le jeu et sur le tobozon (le forum) et un mail de confirmation a été envoyé ;)<br>Clique sur le lien donné dans le mail pour activer ton compte.'.$inscription['charte']);
		return $this->layout->view('message', array('titre' => 'Inscription'));
	}

	public function mail_non_recu()
	{
		// Règles de validation
		$this->form_validation->set_rules('email', "L'email", 'required|valid_email');

		if ( ! $this->form_validation->run())
		{
			return $this->layout->view('visiteur/mail_non_recu');
		}

		// On va chercher les infos du joueur
		$query = $this->db->select('id, pseudo, statut, email')
							->from('joueurs')
							->where('email', $this->lib_email->nettoyer_email($this->input->post('email')))
							->get();

		// On vérifie que le compte existe
		if ($query->num_rows() == 0)
		{
			$this->echec('Cette adresse email ne correspond à aucun joueur');
			return $this->layout->view('visiteur/mail_non_recu');
		}

		$joueur = $query->row();

		// On vérifie que le compte n'est pas banni
		if ($joueur->statut == Bouzouk::Joueur_Banni)
		{
			$this->echec('Ce compte est banni');
			return $this->layout->view('visiteur/mail_non_recu');
		}

		// On vérifie que le compte n'est pas déjà actif
		if ($joueur->statut != Bouzouk::Joueur_Inactif)
		{
			$this->echec('Ce compte est déjà actif');
			return $this->layout->view('visiteur/mail_non_recu');
		}

		// On regarde si un code de validation existe
		$query = $this->db->select('code')
						  ->from('codes_aleatoires')
						  ->where('joueur_id', $joueur->id)
						  ->where('type', Bouzouk::Code_Inscription)
						  ->get();

		$code = '';

		// Si aucun code, on en créé un nouveau
		if ($query->num_rows() == 0)
		{
			$data_code_aleatoire = array(
				'joueur_id' => $joueur->id,
				'code'      => random_string('alnum', 8),
				'type'      => Bouzouk::Code_Inscription,
				'date'      => bdd_datetime()
			);
			$this->db->insert('codes_aleatoires', $data_code_aleatoire);
			$code = $data_code_aleatoire['code'];
		}

		else
		{
			$code_aleatoire = $query->row();
			$code = $code_aleatoire->code;
		}

		// On prépare l'email
		$vars = array(
			'pseudo'         => $joueur->pseudo,
			'code_aleatoire' => $code
		);
		$message = $this->load->view('email/inscription', $vars, true);

		$this->load->library('email');
		$this->email->from($this->bouzouk->config('email_from'), 'Bouzouks')
					->to($this->input->post('email'))
					->subject('[Bouzouks.net] Inscription')
					->message($message);

		// On envoit l'email
		if ( ! $this->email->send())
		{
			$this->echec("Une erreur est survenue lors de l'envoi du mail");
			return $this->layout->view('visiteur/mail_non_recu');
		}

		$this->succes('Un nouveau mail de confirmation a été envoyé ;)');
		return $this->layout->view('message', array('titre' => 'Email non reçu'));
	}

	public function inscription_confirmation()
	{
		// On transforme GET en POST, car on peut utiliser GET depuis un email
		if ($this->input->get('pseudo') !== false AND $this->input->get('code') !== false)
		{
			$_POST['pseudo'] = $this->input->get('pseudo');
			$_POST['code'] = $this->input->get('code');
		}

		// Règles de validation
		$this->form_validation->set_rules('pseudo', 'Le pseudo', 'required|min_length[3]|max_length[15]|alpha_dash');
		$this->form_validation->set_rules('code', 'Le code', 'required|alpha_numeric|exact_length[8]');

		if ( ! $this->form_validation->run())
		{
			return $this->layout->view('visiteur/inscription_confirmation');
		}
		$this->load->library('lib_joueur');
		// On va chercher les infos du joueur
		$query = $this->db->select('j.id, j.pseudo, j.statut, c.code, c.type')
						  ->from('joueurs j')
						  ->join('codes_aleatoires c', 'c.joueur_id = j.id AND c.type = '.Bouzouk::Code_Inscription, 'left')
						  ->where('j.pseudo', $this->input->post('pseudo'))
						  ->get();

		// On vérifie que le compte existe
		if ($query->num_rows() == 0)
		{
			$this->echec('Ce pseudo ne correspond à aucun joueur');
			return $this->layout->view('visiteur/inscription_confirmation');
		}

		$joueur = $query->row();

		// On vérifie que le compte n'est pas banni
		if ($joueur->statut == Bouzouk::Joueur_Banni)
		{
			$this->echec('Ce compte est banni');
			return $this->layout->view('visiteur/inscription_confirmation');
		}

		// On vérifie que le compte n'est pas déjà actif
		if ($joueur->statut != Bouzouk::Joueur_Inactif)
		{
			$this->echec('Ce compte est déjà actif');
			return $this->layout->view('visiteur/inscription_confirmation');
		}

		// On vérifie que le code d'activation est bon
		if ($joueur->code != $this->input->post('code'))
		{
			$this->echec("Le code d'activation n'est pas bon");
			return $this->layout->view('visiteur/inscription_confirmation');
		}

		// On active le compte
		$this->lib_joueur->activation_compte($joueur->id, $joueur->pseudo);
		// On supprime le code aléatoire
		$this->db->where('joueur_id', $joueur->id)
				 ->where('type', Bouzouk::Code_Inscription)
				 ->delete('codes_aleatoires');
		
		// On affiche un message de confirmation
		$this->succes('Ton compte a bien été validé, tu peux maintenant te connecter');
		return $this->layout->view('message', array('titre' => "Confirmation d'inscription"));
	}

	public function pass_perdu()
	{
		// Règles de validation
		$this->form_validation->set_rules('pseudo', 'Le pseudo', 'required|min_length[3]|max_length[15]|alpha_dash');
		$this->form_validation->set_rules('email', 'L\'e-mail', 'required|valid_email');

		if ( ! $this->form_validation->run())
		{
			return $this->layout->view('visiteur/pass_perdu');
		}

		// On va chercher les infos du joueur
		$query = $this->db->select('id, pseudo, email, statut')
						  ->from('joueurs')
						  ->where('pseudo', $this->input->post('pseudo'))
						  ->get();

		// On vérifie que le compte existe
		if ($query->num_rows() == 0)
		{
			$this->echec('Ce pseudo ne correspond à aucun joueur');
			return $this->layout->view('visiteur/pass_perdu');
		}

		$joueur = $query->row();

		// On vérifie l'email
		if ($joueur->email != $this->lib_email->nettoyer_email($this->input->post('email')))
		{
			$this->echec("Cette adresse email n'est pas la bonne");
			return $this->layout->view('visiteur/pass_perdu');
		}

		// On vérifie que le compte n'est pas banni
		if ($joueur->statut == Bouzouk::Joueur_Banni)
		{
			$this->echec('Ce compte est banni');
			return $this->layout->view('visiteur/pass_perdu');
		}

		// On vérifie que le compte n'est pas inactif
		if ($joueur->statut == Bouzouk::Joueur_Inactif)
		{
			$this->echec("Ce compte n'a pas été activé");
			return $this->layout->view('visiteur/pass_perdu');
		}

		$nb_codes = $this->db->where('joueur_id', $joueur->id)
							 ->where('type', Bouzouk::Code_PassPerdu)
							 ->count_all_results('codes_aleatoires');

		if ($nb_codes > 0)
		{
			$this->echec("Il y a déjà une demande de mot de passe en cours pour ce compte, vérifie ta boîte mail");
			return $this->layout->view('visiteur/pass_perdu');
		}

		// On insère un code aléatoire en base
		$this->load->helper('string');
		$this->load->helper('date');

		$data_code_aleatoire = array(
			'joueur_id' => $joueur->id,
			'code'      => random_string('alnum', 8),
			'type'      => Bouzouk::Code_PassPerdu,
			'date'      => bdd_datetime()
		);
		$this->db->insert('codes_aleatoires', $data_code_aleatoire);

		// On prépare l'email
		$vars = array(
			'pseudo'         => $joueur->pseudo,
			'date'           => bouzouk_datetime($data_code_aleatoire['date']),
			'code_aleatoire' => $data_code_aleatoire['code']
		);
		$email = $this->load->view('email/pass_perdu', $vars, true);

		$this->load->library('email');
		$this->email->from($this->bouzouk->config('email_from'), 'Bouzouks')
					->to($this->input->post('email'))
					->subject('[Bouzouks.net] Mot de passe perdu')
					->message($email);

		// On envoit l'email
		if ( ! $this->email->send())
		{
			$this->echec("Une erreur est survenue lors de l'envoi de l'email");
			return $this->layout->view('visiteur/pass_perdu');
		}

		$this->succes('Un mail a été envoyé sur ton adresse, tu dois cliquer sur le lien donné pour changer ton mot de passe');
		return $this->layout->view('message', array('titre' => 'Mot de passe perdu'));
	}

	public function pass_perdu_confirmation()
	{
		// On transforme GET en POST, car on peut utiliser GET depuis un email
		if ($this->input->get('pseudo') !== false AND $this->input->get('code') !== false)
		{
			$_POST['pseudo'] = $this->input->get('pseudo');
			$_POST['code'] = $this->input->get('code');
		}

		else
		{
			// Règle de validation du code
			$this->form_validation->set_rules('mot_de_passe', 'Le mot de passe', 'required|min_length[6]|max_length[30]');
		}

		// Règles de validation
		$this->form_validation->set_rules('pseudo', 'Le pseudo', 'required|min_length[3]|max_length[15]|alpha_dash');
		$this->form_validation->set_rules('code', 'Le code', 'required|alpha_numeric|exact_length[8]');

		if ( ! $this->form_validation->run() OR ($this->input->get('pseudo') !== false AND $this->input->get('code') !== false))
		{
			return $this->layout->view('visiteur/pass_perdu_confirmation');
		}

		// On va chercher les infos du joueur
		$query = $this->db->select('j.id, j.pseudo, j.statut, c.code, c.type')
						  ->from('joueurs j')
						  ->join('codes_aleatoires c', 'c.joueur_id = j.id AND c.type = '.Bouzouk::Code_PassPerdu, 'left')
						  ->where('pseudo', $this->input->post('pseudo'))
						  ->get();

		// On vérifie que le compte existe
		if ($query->num_rows() == 0)
		{
			$this->echec('Ce pseudo ne correspond à aucun joueur');
			return $this->layout->view('visiteur/pass_perdu_confirmation');
		}

		$joueur = $query->row();

		// On vérifie que le compte n'est pas banni
		if ($joueur->statut == Bouzouk::Joueur_Banni)
		{
			$this->echec('Ce compte est banni');
			return $this->layout->view('visiteur/pass_perdu_confirmation');
		}

		// On vérifie que le compte n'est pas inactif
		if ($joueur->statut == Bouzouk::Joueur_Inactif)
		{
			$this->echec("Ce compte n'a pas été activé");
			return $this->layout->view('visiteur/pass_perdu_confirmation');
		}

		// On vérifie que la demande de mot de passe existe
		if ($joueur->type == null)
		{
			$this->echec("Il n'y a aucune demande de mot de passe pour ce compte");
			return $this->layout->view('visiteur/pass_perdu_confirmation');
		}

		// On vérifie que le code d'activation est bon
		if ($joueur->code != $this->input->post('code'))
		{
			$this->echec("Le code d'activation n'est pas bon");
			return $this->layout->view('visiteur/pass_perdu_confirmation');
		}

		// On change le mot de passe
		$mdp = sha1($this->input->post('mot_de_passe'));

		$data_joueur = array(
			'mot_de_passe'   => $mdp,
		);
		$this->db->where('id', $joueur->id)
				 ->update('joueurs', $data_joueur);

		// On met à jour le mot de passe sur me tobozon
		$data_users = array(
			'password' => $mdp
		);
		$this->db->where('id', $joueur->id)
					->update('tobozon_users', $data_users);

		// On supprime le code aléatoire
		$this->db->where('joueur_id', $joueur->id)
				 ->where('type', Bouzouk::Code_PassPerdu)
				 ->delete('codes_aleatoires');

		$this->succes('Ton mot de passe a bien été changé, tu peux maintenant te connecter');
		// Campagne FaceBook
		$this->load->model('fb_pixel');
		$pixel = $this->fb_pixel->get('1');
		if($pixel->etat == 1){
			$this->load->view('facebook/pixel', array('pixel_id'=> $pixel->id_fb));
		}
		return $this->layout->view('message', array('titre' => 'Changer de mot de passe'));
	}

	public function connexion()
	{
		if ( ! $this->input->isPost())
			redirect();

		// Règles de validation
		$this->form_validation->set_rules('connexion_pseudo', 'Le pseudo', 'required|min_length[3]|max_length[15]|regex_match[#^[^<>]+$#]');
		$this->form_validation->set_rules('connexion_mot_de_passe', 'Le mot de passe', 'required|min_length[6]|max_length[30]');

		if ( ! $this->form_validation->run())
		{
			return $this->layout->view('message', array('titre' => 'Connexion'));
		}
		
		// On va chercher les infos du joueur
		$this->load->library('lib_joueur');
		$session = $this->lib_joueur->get_joueur_info(array('pseudo'=> $this->input->post('connexion_pseudo')));
		if(!$session){
			$this->echec('Ce pseudo ne correspond à aucun joueur');
			return $this->layout->view('message', array('titre' => 'Connexion'));
		}
		// En version test on ne va autoriser que certaines personnes
		if (ENVIRONMENT == 'testing')
		{
			if ( ! $this->bouzouk->is_admin(null, $session->rang))
			{
				$this->echec("Tu n'as pas l'autorisation de te connecter au site...désolé :)");
				return $this->layout->view('message', array('titre' => 'Connexion'));
			}
		}

		// On vérifie le mot de passe
		if ($session->mot_de_passe != sha1($this->input->post('connexion_mot_de_passe')))
		{
			$this->echec('Mot de passe incorrect');
			return $this->layout->view('message', array('titre' => 'Connexion'));
		}

		//if (file_exists(APPPATH.'third_party/salt_builder.php'))
			//require APPPATH.'third_party/salt_builder.php';
		
		// On vérifie que le compte n'est pas banni
		if ($session->statut == Bouzouk::Joueur_Banni)
		{
			$raison = $session->raison_statut;
			$this->echec('Tu as été banni de Vlurxtrznblax pour la raison suivante :<br><br>&laquo; <span class="pourpre">'.$raison." &raquo; </span><br><br>Si tu penses qu'il y a une erreur, <a href='".
						  site_url('site/team')."'>contacte un administrateur en privé</a>");
			return $this->layout->view('message', array('titre' => 'Connexion'));
		}

		// On vérifie que le compte n'est pas inactif
		if ($session->statut == Bouzouk::Joueur_Inactif)
		{
			$this->echec("Ce compte n'a pas été activé");
			return $this->layout->view('message', array('titre' => 'Connexion'));
		}
		$this->lib_joueur->connecter($session);

		// On redirige le joueur vers l'accueil des joueurs
		redirect('joueur/accueil');
	}

	public function fb_callback(){
		$this->load->library('fb/fb_api');
		$this->load->library('lib_joueur');

		// On récupère un token d'authentifiaction chez FB
		if(!$this->fb_api->connexion()){
			redirect();
		}
		$this->fb_api->connexion();

		// Récupération des infos via fb
		$fb_id = $this->fb_api->get_id();
		$fb_mail = $this->fb_api->get_email();
		$fb_birthday = $this->fb_api->get_birthday();

		// Info du joueur sur notre site
		$is_granted = $this->fb_api->is_granted($fb_id);
		$mail_enregistre = $this->lib_email->email_existe($fb_mail);
		$id_joueur = $this->lib_joueur->get_id_by_FB($fb_id);
		// On regarde si un code de validation existe
		$query = $this->db->select('code')
						  ->from('codes_aleatoires')
						  ->where('joueur_id', $id_joueur)
						  ->where('type', Bouzouk::Code_Inscription)
						  ->get();
		// SI on a un code
		if($query->num_rows>0){
			$mail_non_confirme = TRUE;
		}
		else{
			$mail_non_confirmer = FALSE;
		}

		// Si l'user a refuser de communiquer son mail et n'est pas inscrit
		if(!$fb_mail && !$id_joueur){
			//On enregistre l'id FB en session
			$this->session->set_userdata('fb_id', $fb_id);
			// On renvoie vers l'inscription classique
			return $this->inscription();
		}
		// Si les info ne correspondent à aucune entrée, on inscrit le joueur
		elseif(!$mail_enregistre && !$id_joueur){
			// On stocke l'id FB et le mail en session
			$this->session->set_userdata('fb_id', $fb_id);
			$this->session->set_userdata('email', $fb_mail);
			$this->session->set_userdata('birthday', $fb_birthday);
			return $this->inscription_fb($fb_mail);
		}
		// Si le joueur n'a  pas d'id fb mais que le mail est déjà enregistré
		elseif(!$is_granted && $mail_enregistre){
			// On récupère l'id du joueur via le mail du compte fb
			$id_joueur = $this->lib_joueur->get_id_by_email($fb_mail);
			$this->lib_joueur->assoc_compte_fb($id_joueur, $fb_id, $fb_mail);
		}

		// On connecte le joueur
		$session = $this->lib_joueur->get_joueur_info(array('id'=>$id_joueur));

		// En version test on ne va autoriser que certaines personnes
		if (ENVIRONMENT == 'testing')
		{
			if ( ! $this->bouzouk->is_admin(null, $session->rang))
			{
				$this->echec("Tu n'as pas l'autorisation de te connecter au site...désolé :)");
				return $this->layout->view('message', array('titre' => 'Connexion'));
			}
		}

		// On vérifie que le compte n'est pas banni
		elseif ($session->statut == Bouzouk::Joueur_Banni)
		{
			$raison = $session->raison_statut;
			$this->echec('Tu as été banni de Vlurxtrznblax pour la raison suivante :<br><br>&laquo; <span class="pourpre">'.$raison." &raquo; </span><br><br>Si tu penses qu'il y a une erreur, <a href='".
						  site_url('site/team')."'>contacte un administrateur en privé</a>");
			return $this->layout->view('message', array('titre' => 'Connexion'));
		}

		// On vérifie que le compte n'est pas inactif
		elseif ($session->statut == Bouzouk::Joueur_Inactif)
		{
			$this->echec("Ce compte n'a pas été activé");
			return $this->layout->view('message', array('titre' => 'Connexion'));
		}
		else
		{
			$this->lib_joueur->connecter($session);
		}
		// On redirige le joueur vers l'accueil des joueurs
		redirect('joueur/accueil');
	}

	public function inscription_fb($fb_mail){
		// Si le mail n'est pas autorisé
		if ( ! $this->lib_email->email_valide($fb_mail))
		{
			$this->echec( $fb_mail.' est invalide (emails autorisés : '.$this->lib_email->emails_autorises().')');
			$this->layout->view('message', array('titre' => 'Confirmation d\'inscription'));
		}
		else{
			// callback de validation du formulaire
			$vars['validation_callback'] = 'inscription_fb_callback';
			// On affiche le formulaire
			$this->layout->view('visiteur/inscription', $vars);
		}
		
	}

	public function inscription_fb_callback(){

		$vars['validation_callback'] = 'inscription_fb_callback';
		// Règles de validation
		$this->form_validation->set_rules('pseudo', 'Le pseudo', 'required|min_length[3]|max_length[12]|alpha_dash|is_unique[joueurs.pseudo]|callback__pseudo_check');
		$this->form_validation->set_rules('mot_de_passe', 'Le mot de passe', 'required|min_length[6]|max_length[30]');
		$this->form_validation->set_rules('age', 'La case "age"', 'required');
		$this->form_validation->set_rules('charte', 'La case "charte"', 'required');
		$this->form_validation->set_rules('derniere', 'La case "jamais 2 sans 3"', 'required');
		$this->form_validation->set_rules('parrain', 'Le pseudo du parrain', 'min_length[3]|max_length[12]|alpha_dash|callback_parrain_check');

		if ( ! $this->form_validation->run())
			return $this->layout->view('visiteur/inscription', $vars);

		// Interdiction aux proxys
		if ($this->is_proxy())
		{
			$this->echec("Ta connexion internet n'est pas autorisée sur ce serveur");
			return $this->layout->view('visiteur/inscription', $vars);
		}
		$this->load->library('lib_joueur');
		$data = $this->input->post(NULL);
		$data['ip'] = $this->input->ip_address();
		$checked_info = $this->lib_joueur->check_info_inscription($data);

		// S'il y a une erreur dans les informations fournies
		if(isset($checked_info['erreur']['type'])){
			$this->echec($checked_info['erreur']['message']);
			return $this->layout->view('visiteur/inscription', $vars);
		}
		else{
			// Protection contre le rechargement (à cause du check proxy)
			$deja_inscrit = $this->db->where('pseudo', $this->input->post('pseudo'))
									 ->count_all_results('joueurs');
			if(!$deja_inscrit){
				// Si tout est ok, on inscrit le joueur
				$checked_info['email'] = $this->session->userdata('email');
				$checked_info['fb_id'] = $this->session->userdata('fb_id');
				$checked_info['birthday'] = $this->session->userdata('birthday');
				$inscription = $this->lib_joueur->inscription($checked_info);
			}
		}
		//On active directement le compte
		$this->lib_joueur->activation_compte($inscription['joueur_id'], $data['pseudo']);
		// Campagne FaceBook
		$this->load->model('fb_pixel');
		$pixel = $this->fb_pixel->get('1');
		if($pixel->etat == 1){
			$this->load->view('facebook/pixel', array('pixel_id'=> $pixel->id_fb));
		}
		// On affiche un message de confirmation
		$this->succes('Tu es maintenant inscrit sur le jeu et sur le tobozon (le forum)'.$inscription['charte']);
		return $this->layout->view('message', array('titre' => 'Inscription'));
	}


	public function is_proxy()
	{
		$scan_headers = array(
			'HTTP_VIA',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_FORWARDED',
			'HTTP_CLIENT_IP',
			'HTTP_FORWARDED_FOR_IP',
			'VIA',
			'X_FORWARDED_FOR',
			'FORWARDED_FOR',
			'X_FORWARDED',
			'FORWARDED',
			'CLIENT_IP',
			'FORWARDED_FOR_IP',
			'HTTP_PROXY_CONNECTION'
		);

		foreach ($scan_headers as $i)
		{
			if (isset($_SERVER[$i]) && $_SERVER[$i])
				return true;
		}

		if (in_array($_SERVER['REMOTE_PORT'], array(8080,80,6588,8000,3128,553,554))/* || @fsockopen($_SERVER['REMOTE_ADDR'], 80, $errno, $errstr, 5)*/)
			return true;

		return false;
	}
}

