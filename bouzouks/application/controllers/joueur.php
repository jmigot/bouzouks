<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : accueil du joueur en fonction de son état (étudiant, actif, en pause, game over...)
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Joueur extends MY_Controller
{
	private $citations = array(
		'faim' => array(
			'<br>Moi manger...<br><br>Miam miam viiittte...<br><br>Argg !!',
			"<br>J'ai la<br>dalle, moi...<br><br>Un beurkeur c'est<br>trop demandé ?!",
		),
		'sante' => array(
			'<br>Je suis<br>malaaaaade !<br><br>Je vais vom... Beuaaaark !!',
			'<br>Ca va pas fort...<br>Soigne moi au lieu<br>de rester planté<br>derrière ton écran!'
		),
		'stress' => array(
			"<br>J'pete les plombs!<br><br>Je suis un petit pioupiouk !<br><br>Mouahaha!!!",
			"<br>J'ai pas trop<br>le moral...<br><br>Mais ça tu t'en<br>fiche, hein !"
		),
		'global' => array(
			"<br>Raaaaah !<br>Vi...te...<br>L'hôpi... L'hôpi...<br><br>L'hôpital...",
			"<br>Même si<br>mon visage ne<br>laisse rien transparaître,<br>c'est pas la forme...",
			"<br>Ca pourrait<br>aller mieux si<br>tu étais moins<br>radin !",
			"<br>Je vais<br>pas trop mal<br>mais ça va peut<br>être pas durer<br>...",
			"<br>Aujourd'hui<br>je suis en forme !<br><br>J'ai la kahuète !",
			'<br>La vie est belle !<br>Les pioupiouks chantent !<br><br>Cuicuicui !',
			"Tu te la<br>pètes parce que<br>tu as mis 100% à<br>toutes mes stats ?<br><br>Pfff..."
		),
		'bouf_tete' => "Les bébêtes<br>jaunes sont nos maîtres.<br><br>Gloire aux bébêtes<br>jaunes !!",
		'mlbobz' => "<br/>Etre une zoukette,<br/> c'est trop la classe !<br> Les zoukettes au pouvoir !!"
	);

	public function index()
	{
		$this->accueil();
	}
	
	// --------- Event RP Zombies ------------
	public function mordre()
	{
		// Règles de validation
 		$this->load->library('form_validation');
		$this->form_validation->set_rules('proie_id', 'Ta proie', 'required|is_natural_no_zero');

		if ( ! $this->form_validation->run())
			redirect();
		
		// Est-ce qu'il lui reste des morsures
		if ($this->session->userdata('event_zomies_morsures') <= 0)
		{
			$this->echec("Tu n'a plus de salive, ça n'a aucun effet.");
			redirect();
		}
		
		// Est-ce que le joueur est toujours connecté
		if ( ! $this->bouzouk->est_connecte($this->input->post('proie_id')))
		{
			$this->echec("Tu l'as manqué de peu mais il court vite, tant pis.");
			redirect();
		}
		
		// Est-ce que le joueur n'est pas déjà zombie
		if ($this->bouzouk->est_zombie($this->input->post('proie_id')))
		{
			$this->echec("Beurk ! C'est un zombie que tu as essayé de mordre, ça a mauvais gout, tu te sens mal.");
			redirect();
		}
		
		// On le transforme en zombie
		$data_event_joueurs_zombies = array(
			'joueur_id'  => $this->input->post('proie_id'),
			'nb_morsure' => 2,
		);
		$this->db->insert('event_joueurs_zombies', $data_event_joueurs_zombies);
		
		// On enleve une morsure
		$this->db->set('nb_morsure', 'nb_morsure - 1', false)
				 ->where('joueur_id', $this->session->userdata('id'))
				 ->update('event_joueurs_zombies');
		
		// On met à jour les sessions
		$this->bouzouk->augmente_version_session();
		$this->bouzouk->augmente_version_session($this->input->post('proie_id'));
		
		// On lui envoit une notif
		$this->bouzouk->historique(245, 246, array(profil(-1, '', $this->session->userdata('rang'))), $this->input->post('proie_id'), Bouzouk::Historique_Full);
		
		$this->succes("Scrounch scrouch ! C'est pas si mauvais le bouzouk. Tu as bien envie d'en regoûter un autre.");
		
		redirect();
	}
	// --------- Event RP Zombies ------------

	// --------- Event Bouf'Tête ------------

	/**
	 * implantation d'un nouveau bouf'tête
	 * Chaque bouf'tête à trois petit max par jour
	 **/
	public function infecter(){
		$this->load->library('form_validation');
		//Régle de validatio ndu formulaire
		$rules = array(
			array(
				'field' => 'infection',
				'label'	=> 'Id du joueur à infecter',
				'rules'	=> 'is_natural|required'
				)
			);
		$this->form_validation->set_rules($rules);
		// Traitement du formulaire
		if($this->form_validation->run() != FALSE){
			// On implante le joueur
			$id_joueur = $this->form_validation->set_value('infection');
			$implantation = $this->bouzouk->infecter($id_joueur, true);
			if($implantation == 'insuffisant'){
				$this->succes("Tu n'as plus de bestiole à implanter !");
			}
			elseif(!$implantation){
				$this->echec("Tu ne peux pas implanter de bestiole jaune.");
			}
			else{
				if($implantation == 0){
					$message = "Il ne te restes plus de bestiole à implanter !";
				}
				else{
					$message = "Il te restes $implantation bestiole(s) à implanter !";
				}
				$this->succes("Tu as réussi l'implentation. Gloire aux bestioles jaunes ! $message");
			}
		}
		redirect();
	}

	// Event Mblbobz
	public function maudire_mlbobz(){
		$this->load->library('form_validation');
		$rules = array(
			array(
				'field'	=>'malediction_mlbobz',
				'label'	=> 'choix de la victime',
				'rules'	=> 'is_natural|required|callback_malediction_bobz_check'
				)
			);
		$this->form_validation->set_rules($rules);

		if($this->form_validation->run()){
			$id_joueur = $this->input->post('malediction_mlbobz');
			$malediction = $this->bouzouk->maudire_mlbobz($id_joueur, true);
			if($malediction == 'insuffisant'){
				$this->succes("Tu ne peut plus faire de bisoux au smutrz pour aujourd'hui.");
			}
			elseif(!$malediction){
				$this->echec("Punis de bisou !!");
			}
			else{
				if($malediction == 0){
					$message = "Tu as suffisamment fait de bisoux pour aujourd'hui, coquine !";
				}
				else{
					$message = "Il te reste $malediction bisou(x) à disposition.";
				}
				$this->succes("Tu as réussi ton bisou au smurtz. Zoukette Powa ! $message");
			}
		}
		redirect();
	}

	public function malediction_bobz_check(){
		if($this->bouzouk->est_maudit_mlbobz($this->input->post('malediction_mlbobz'))){
			$this->form_validation->set_message('malediction_bobz_check', "Le joueurs choisi est invalide.");
			return FALSE;
		}
		else{
			return TRUE;
		}
	}

	public function accueil($offset = '0')
	{
		// Pagination
		$pagination = creer_pagination('joueur/accueil', $this->db->count_all('news'), 3, $offset);

		$vars = array(
			'pagination' => $pagination['liens'],
			'news_only'  => true
		);
		
		if ($offset == '0')
		{
			// Citation
			$moyenne = ($this->session->userdata('faim') + $this->session->userdata('sante') + (100 - $this->session->userdata('stress'))) / 3;
			$citation = '';

			if ($this->session->userdata('faim') < 8 && $this->session->userdata('sante') < 8 && $this->session->userdata('stress') > 92)
				$citation = $this->citations['global'][0];

			else if ($this->session->userdata('faim') < 15)
				$citation = $this->citations['faim'][0];

			else if ($this->session->userdata('sante') < 15)
				$citation = $this->citations['sante'][0];

			else if ($this->session->userdata('stress') > 85)
				$citation = $this->citations['stress'][0];

			else if ($this->session->userdata('faim') < 30)
				$citation = $this->citations['faim'][1];

			else if ($this->session->userdata('sante') < 30)
				$citation = $this->citations['sante'][1];

			else if ($this->session->userdata('stress') > 70)
				$citation = $this->citations['stress'][1];

			else if ($moyenne < 45)
				$citation = $this->citations['global'][1];

			else if ($moyenne < 55)
				$citation = $this->citations['global'][2];

			else if ($moyenne < 70)
				$citation = $this->citations['global'][3];

			else if ($moyenne < 90)
				$citation = $this->citations['global'][4];

			else if ($moyenne < 100)
				$citation = $this->citations['global'][5];

			else
				$citation = $this->citations['global'][6];

			// Event Bouf'tête
			if($this->session->userdata('bouf_tete') == 1){
				$citation = $this->citations['bouf_tete'];
			}
			if($this->session->userdata('mlbobz') == 1){
				$citation = $this->citations['mlbobz'];
			}

			// On récupère les dernières missives
			$query = $this->db->select('m.id, j.pseudo AS expediteur, j.id AS expediteur_id, j.rang AS expediteur_rang, m.date_envoi, m.objet, m.lue')
							->from('missives m')
							->join('joueurs j', 'j.id = m.expediteur_id')
							->where('m.destinataire_id', $this->session->userdata('id'))
							->where('m.destinataire_supprime', 0)
							->order_by('m.date_envoi desc')
							->limit(7)
							->get();
			$missives = $query->result();

			// On récupère les derniers sujets du tobozon (requête à la main à cause de l'id automatiquement échappé sur la jointure)
			$is_admin = $this->bouzouk->is_admin(Bouzouk::Rang_Admin) ? '1' : '0';
			$query = $this->db->query('SELECT p.posted AS date, p.poster AS pseudo, p.poster_id AS joueur_id, p.id, t.subject AS sujet
									FROM tobozon_topics t
									JOIN tobozon_posts p ON p.id = t.last_post_id
									JOIN tobozon_users u ON u.id = '.$this->session->userdata('id').'
									JOIN tobozon_forums f ON f.id = t.forum_id
									LEFT JOIN tobozon_forum_perms f_p ON f_p.forum_id = t.forum_id AND f_p.group_id = u.group_id
									LEFT JOIN politiciens po ON po.clan_id = f.clan_id
									LEFT JOIN clans c ON c.id = f.clan_id
									WHERE (f_p.read_forum IS NULL OR f_p.read_forum = 1) AND (f.clan_mode != 3 OR (po.joueur_id = '.$this->session->userdata('id').' OR c.chef_id = '.$this->session->userdata('id').' OR '.$is_admin.'))
									GROUP BY t.id
									ORDER BY t.last_post DESC
									LIMIT 7');
			$tobozon = $query->result();

			// Si le joueur est modérateur on affiche le nombre de signalements
			$nb_signalements = false;

			if ($this->session->userdata('moderateur_tobozon') || $this->bouzouk->is_admin())
			{
				$nb_signalements = $this->db->where('zapped', null)
											->count_all_results('tobozon_reports');
			}

			// On récupère la dernière gazette
			$query = $this->db->select('g.titre, g.resume, g.auteur_id, j.pseudo, tt.num_replies AS nb_commentaires, g.topic_id')
							->from('gazettes g')
							->join('joueurs j', 'j.id = g.auteur_id')
							->join('tobozon_topics tt', 'tt.id = g.topic_id', 'left')
							->where('g.type', Bouzouk::Gazette_Article)
							->where('g.en_ligne', 1)
							->order_by('g.date', 'desc')
							->limit(1)
							->get();
			$gazette = $query->row();

			// Affichage
			$fortune = $this->bouzouk->fortune_totale();
		
			// Variables
			$vars['citation']        = $citation;
			$vars['fortune']         = $fortune['total'];
			$vars['missives']        = $missives;
			$vars['tobozon']         = $tobozon;
			$vars['nb_signalements'] = $nb_signalements;
			$vars['gazette']         = $gazette;
			$vars['news_only']       = false;
		}

		// On récupère les dernières news
		$query = $this->db->select('n.titre, n.texte, n.date, n.auteur_id, j.pseudo AS auteur, j.rang AS auteur_rang')
						  ->from('news n')
						  ->join('joueurs j', 'j.id = n.auteur_id', 'left')
						  ->where('n.en_ligne', Bouzouk::News_Publie)
						  ->order_by('n.date', 'desc')
						  ->limit($pagination['par_page'], $pagination['offset'])
						  ->get();
		$news = $query->result();

		$this->load->library('lib_parser');
		
		// On tire une image aléatoire parmis les actions
		if ($this->session->userdata('points_action') >= $this->bouzouk->config('joueur_points_action_max'))
		{
			$images_actions = glob(FCPATH.'webroot/images/clans/actions/*.png');
			shuffle($images_actions);
		}

		else
			$images_actions = array(FCPATH.'webroot/images/clans/actions/vide.gif');
		
		// On récupère les clans du joueur
		$clans = array();
		$query = $this->db->select('type, nom')
						  ->from('clans')
						  ->where_in('id', $this->session->userdata('clan_id'))
						  ->get();
		
		foreach ($query->result() as $tmp)
			$clans[$tmp->type] = $tmp->nom;

		// On récupère le job du joueur
		$query = $this->db->select('j.nom')
						  ->from('employes e')
						  ->join('jobs j', 'j.id = e.job_id')
						  ->where('e.entreprise_id', $this->session->userdata('entreprise_id'))
						  ->where('e.joueur_id', $this->session->userdata('id'))
						  ->get();
		$nom_job = $query->num_rows() == 0 ? '' : $query->row()->nom;

		// On affiche
		$vars['news']         = $news;
		$vars['clans']        = $clans;
		$vars['nom_job']      = $nom_job;
		$vars['image_action'] = basename($images_actions[0]);
		return $this->layout->view('joueur/accueil', $vars);
	}

	public function _sexe_check($sexe)
	{
		if ( ! in_array($sexe, array('male', 'femelle'), true))
		{
			$this->form_validation->set_message('_sexe_check', "Nous n'acceptons pas ce genre de sexe à Vlurxtrznblax");
			return false;
		}

		return true;
	}

	public function choix_perso()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('sexe', 'Le sexe de ton bouzouk', 'required|callback__sexe_check');
		$this->form_validation->set_rules('jour', 'Le jour', 'required|greater_than_or_equal[1]|less_than_or_equal[31]');
		$this->form_validation->set_rules('mois', 'Le mois', 'required|greater_than_or_equal[1]|less_than_or_equal[12]');
		$this->form_validation->set_rules('annee', "L'année", 'required|greater_than[1900]|less_than['.date('Y').']');
		$this->form_validation->set_rules('commentaire', 'Le commentaire', 'max_length[150]');

		// On récupère la date de naissance
		$vars['dn'] = explode('-', $this->session->userdata('date_de_naissance'));
		if ( ! $this->form_validation->run())
		{
			return $this->layout->view('joueur/choix_perso', $vars);
		}

		// On vérifie que le bouzouk choisi existe bien
		$sexe = $this->input->post('sexe');
		$persos = $this->bouzouk->get_persos();

		if ( ! array_key_exists($this->input->post('perso_'.$sexe), $persos[$sexe]))
		{
			$this->echec("Ce bouzouk n'existe pas");
			return $this->layout->view('joueur/choix_perso');
		}

		// On vérifie que la date de naissance est correcte
		if ( ! checkdate($this->input->post('mois'), $this->input->post('jour'), $this->input->post('annee')))
		{
			$this->echec('La date de naissance est invalide');
			return $this->layout->view('joueur/choix_perso');
		}

		// On enregistre le perso en base et on passe le joueur en actif
		$data_joueur = array(
			'date_de_naissance' => $this->input->post('annee').'-'.$this->input->post('mois').'-'.$this->input->post('jour'),
			'commentaire'       => $this->input->post('commentaire'),
			'sexe'              => $this->input->post('sexe'),
			'perso'             => $this->input->post('perso_'.$sexe),
			'statut'            => Bouzouk::Joueur_Actif,
			'date_statut'       => bdd_datetime()
		);
		$this->db->where('id', $this->session->userdata('id'))
				 ->update('joueurs', $data_joueur);
		$this->session->set_userdata($data_joueur);

		// On donne quelques objets aléatoires au joueurs pour démarrer
		// Faim
		$query = $this->db->select('id')
						  ->from('objets')
						  ->where('type', 'faim')
						  ->where('disponibilite', 'entreprise')
						  ->order_by('id', 'random')
						  ->limit(1)
						  ->get();
		$objet_faim = $query->row();

		// Santé
		$query = $this->db->select('id')
						  ->from('objets')
						  ->where('type', 'sante')
						  ->where('disponibilite', 'entreprise')
						  ->order_by('id', 'random')
						  ->limit(1)
						  ->get();
		$objet_sante = $query->row();

		// Stress
		$query = $this->db->select('id')
						  ->from('objets')
						  ->where('type', 'stress')
						  ->where('disponibilite', 'entreprise')
						  ->order_by('id', 'random')
						  ->limit(1)
						  ->get();
		$objet_stress = $query->row();

		$data_maisons = array(
			array(
				'joueur_id'  => $this->session->userdata('id'),
				'objet_id'   => $objet_faim->id,
				'quantite'   => mt_rand(1, 2),
				'peremption' => mt_rand(2,5)
			),
			array(
				'joueur_id'  => $this->session->userdata('id'),
				'objet_id'   => $objet_sante->id,
				'quantite'   => mt_rand(1, 2),
				'peremption' => mt_rand(2,5)
			),
			array(
				'joueur_id'  => $this->session->userdata('id'),
				'objet_id'   => $objet_stress->id,
				'quantite'   => mt_rand(1, 2),
				'peremption' => mt_rand(2,5)
			),
		);
		$this->db->insert_batch('maisons', $data_maisons);

		// Si un parrain est déclaré
		$query = $this->db->select('parrain_id')
						  ->from('joueurs')
						  ->where('id', $this->session->userdata('id'))
						  ->where('parrain_id IS NOT NULL')
						  ->get();

		if ($query->num_rows() == 1)
		{
			$parrain_id = $query->row()->parrain_id;

			// On prévient le parrain que son filleul est dans la ville
			$this->bouzouk->notification(227, array(profil()), $parrain_id);

			// On ajoute le parrain et le filleul comme amis
			$data_amis = array(
				'joueur_id' => $this->session->userdata('id'),
				'ami_id'    => $parrain_id,
				'date'      => bdd_datetime(),
				'etat'      => Bouzouk::Amis_Accepte
			);
			$this->db->insert('amis', $data_amis);

			$data_amis = array(
				'joueur_id' => $parrain_id,
				'ami_id'    => $this->session->userdata('id'),
				'date'      => bdd_datetime(),
				'etat'      => Bouzouk::Amis_Accepte
			);
			$this->db->insert('amis', $data_amis);
		}
		// On affiche un message
		$message = '';
        $message.="Hey, salut l'nouveau ! T'es perdu ? Tu veux en savoir plus sur l'univers qui te tend les bras ?\n";
        $message.="Laisse-moi t'expliquer deux-trois trucs dans ce cas...\n\n";
        $message.="[b]Bienvenue à Vlurxtrznbnaxl ![/b]\n";
        $message.="Une petite ville où il fait bon vivre !\n\n";
        $message.="Si tu veux survivre dans ce monde de dingues tu dois nourrir régulièrement ton Bouzouk pour ne pas être envoyé à <a href='".site_url('site/faq/asile')."'>l'asile</a> ou partir en quête du schnibble (en <a href='http://www.bouzouks.net/site/faq/game_over'>game over</a>).\n";
        $message.="Tu peux acheter de quoi te nourrir au <a href='".site_url('magasins/bouffzouk')."'>bouffzouk</a>, à <a href='".site_url('magasins/indispenzouk')."'>l'indispenzouk</a> et au <a href='".site_url('magasins/luxezouk')."'>luxezouk</a>. Si un magasin est fermé, fais un tour au <a href='".site_url('marche_noir')."'>marché noir</a> pour voir si d'autres joueurs revendent les produits que tu cherches.\n\n";
        $message.="Afin de pouvoir acheter tout ceci tu dois trouver un boulot et commencer à bosser dès aujourd'hui en cherchant parmi les <a href='".site_url('anpe')."'>annonces de recrutement</a> des patrons ou en postant <a href='".site_url('anpe/mes_annonces')."'>ta propre annonce</a>. Tu recevras ta paye chaque jour à la maintenance.\n\n";
        $message.="Si tu veux gagner beaucoup plus de struls et/ou devenir une personne influente sur le jeu, intéresse toi à la vie politique, rejoins <a href='".site_url('clans/lister')."'>un ou plusieurs clans</a> (parti politique ou organisation) qui partagent les mêmes idéaux que toi et fait élire un des membres comme maire de la ville <a href='".site_url('elections')."'>aux élections</a>, tous les 15 jours.\n\n";
        $message.="Si tu t'ennuies tu peux tenter ta chance au <a href='".site_url('jeux/bonneteau')."'>bonneteau</a> (jeu de hasard), au <a href='".site_url('jeux/lohtoh')."'>lohtoh</a> (tirage une fois par jour) et au <a href='".site_url('plouk')."'>plouk</a> (jeu de cartes multi-joueurs).\n\n";
        $message.="Si tu veux développer tes idées politiques ou participer au <a href='".site_url('tobozon/viewtopic.php?id=437')."'>role-play (RP)</a> général, rend toi sur le <a href='".site_url('tobozon/')."'>Toboz</a> et commence à rédiger tes proses. Tu peux aussi venir régulièrement sur le <a href='".site_url('site/tchat')."'>T'chat IRC</a> pour délirer <a href='".site_url('tobozon/viewtopic.php?id=437')."'>RP ou HRP (Hors-Role Play)</a> avec les autres joueurs et l'équipe du jeu.\n\n";
        $message.="Le <a href='".site_url('site/lexique')."'>vocabulaire bouzouk</a> n'est pas forcement simple à comprendre au tout début, le <a href='".site_url('site/tchat')."'>T'chat</a> est un bon moyen de trouver de l'aide en cas de soucis sur le jeu.\n\n";
        $message.="Le contact avec les autres joueurs est essentiel pour jouer ici alors n’hésites pas à rencontrer les autres membres de la communauté. Bon jeu ! ;-)\n";

		$this->load->library('lib_missive');
        $idjoueur= $this->session->userdata('id');
		$this->lib_missive->envoyer_missive(13, $this->session->userdata('id'), "Besoin d'aide pour commencer à jouer ?", $message);
		// On affiche un message
		$this->succes("Félicitations ! Tu rentres maintenant dans la vie active, bonne chance :)<br>
					  Tu as gagné quelques objets dans ta maison pour survivre, mais pour la suite tu vas devoir travailler pour en acheter.<br>
					  Nous t'invitons à aller lire la <a href='".site_url('site/faq')."' title='Lire la FAQ'>FAQ du jeu</a> si tu as besoin d'aide.");
		redirect('joueur/accueil');
	}

	public function asile()
	{
		$this->load->helper('date');

		// On va chercher la liste des joueurs à l'asile
		$query = $this->db->select('id, pseudo, rang')
						  ->from('joueurs')
						  ->where('statut', Bouzouk::Joueur_Asile)
						  ->order_by('pseudo')
						  ->get();
		$alienes = $query->result();

		$profil_moderateur = '';
		
		// ---------- Hook clans ----------
		// Recrutement d'aliéné (SDS)
		$query = $this->db->select('parametres')
						  ->from('clans_actions_lancees')
						  ->where('statut', Bouzouk::Clans_ActionEnCours)
						  ->where('action_id', 41)
						  ->get();
		
		$recrute = false;

		if ($query->num_rows() > 0)
		{
			$actions_en_cours = $query->result();
			
			foreach ($actions_en_cours as $action_en_cours)
			{
				$action_en_cours->parametres = unserialize($action_en_cours->parametres);
				
				if ($action_en_cours->parametres['joueur_id'] == $this->session->userdata('id'))
					$recrute = true;
			}
		}
		
		// On va chercher le profil du modérateur
		if ($this->session->userdata('raison_statut') != '')
		{
			$query = $this->db->select('j2.id, j2.pseudo')
							  ->from('joueurs j1')
							  ->join('joueurs j2', 'j2.id = j1.statut_staff_id')
							  ->where('j1.id', $this->session->userdata('id'))
							  ->get();

			if ($query->num_rows() > 0)
			{
				$moderateur = $query->row();
				$profil_moderateur = profil($moderateur->id, $moderateur->pseudo);
			}
		}

		// On affiche le résultat
		$vars = array(
			'alienes'            => $alienes,
			'a_fait_son_temps'   => strtotime($this->session->userdata('date_statut').'+'.$this->session->userdata('duree_asile').' HOUR') <= strtotime(bdd_datetime()),
			'date_incarceration' => timespan(strtotime($this->session->userdata('date_statut'))),
			'profil_moderateur'  => $profil_moderateur,
			'table_smileys'      => creer_table_smileys('message'),
			'recrute'			 => $recrute,
		);
		return $this->layout->view('joueur/asile', $vars);
	}

	/**
	 * Lorsque le joueur répond au recrutement d'alliéné de la SdS
	 */
	public function recrute()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('rejoindre_secte', "L'appui sur le bouton \"Rejoindre la Secte du Schnibble\"", 'required');

		if ( ! $this->form_validation->run())
			return $this->asile();
		
		// On vérifie qu'une action de recrutement est lancée contre lui
		$query = $this->db->select('parametres')
						  ->from('clans_actions_lancees')
						  ->where('statut', Bouzouk::Clans_ActionEnCours)
						  ->where('action_id', 41)
						  ->get();
		
		$recrute = false;

		if ($query->num_rows() > 0)
		{
			$actions_en_cours = $query->result();
			
			foreach ($actions_en_cours as $action_en_cours)
			{
				$action_en_cours->parametres = unserialize($action_en_cours->parametres);
				
				if ($action_en_cours->parametres['joueur_id'] == $this->session->userdata('id'))
					$recrute = true;
			}
		}
		
		if ( ! $recrute)
			redirect('joueur/asile');

		// On change son statut sur le tobozon
		$this->db->set('title', '')
				 ->where('id', $this->session->userdata('id'))
				 ->update('tobozon_users');
					 
		// On annule l'asile
		$data_joueur = array(
			'statut'          => Bouzouk::Joueur_Actif,
			'date_statut'     => bdd_datetime(),
			'duree_asile'     => 0,
			'raison_statut'   => '',
			'statut_staff_id' => null
		);

		// Si la mise à l'asile n'était pas dûe à une infraction de la charte, on met 25% de stress
		if ($this->session->userdata('raison_statut') == '')
			$data_joueur['stress'] = 25;

		$this->db->where('id', $this->session->userdata('id'))
				 ->update('joueurs', $data_joueur);
		$this->session->set_userdata($data_joueur);	
		
		$this->load->library('lib_clans');
		
		// On vérifie si il a une orga et on la quitte
		if (isset($this->session->userdata('clan_grade')[Bouzouk::Clans_TypeOrganisation]))
		{
			// Si c'est le chef, on legue le clan
			if ($this->session->userdata('clan_grade')[Bouzouk::Clans_TypeOrganisation] == Bouzouk::Clans_GradeChef)
				$this->lib_clans->leguer_clan($this->session->userdata('clan_id')[Bouzouk::Clans_TypeOrganisation]);
			
			// Si il n'est que membre
			else
				$this->lib_clans->quitter_clan($this->session->userdata('clan_id')[Bouzouk::Clans_TypeOrganisation], $this->session->userdata('id'));
		}
		
		// On le fait rejoindre l'orga SDS
		$this->lib_clans->rejoindre_clan($this->session->userdata('id'), 13, Bouzouk::Clans_TypeOrganisation, false, true);
		
		// On passe l'action en fini
		$this->db->set('statut', Bouzouk::Clans_ActionTerminee)
				 ->where('action_id', 41)
				 ->update('clans_actions_lancees');
		
		// On ajoute à l'historique
		$this->bouzouk->historique(71, 72, array(heures_ecoulees($this->session->userdata('date_statut'))));
		
		$this->succes("Tu fais maintenant parti de la Secte du Schnibble");
		return redirect('clans/gerer/organisation');
	}

	public function en_pause()
	{
		$vars = array(
			'date' => $this->session->userdata('date_statut')
		);
		return $this->layout->view('joueur/en_pause', $vars);
	}

	public function game_over()
	{
		$vars = array(
			'date' => $this->session->userdata('date_statut')
		);
		return $this->layout->view('joueur/game_over', $vars);
	}

	public function reprendre_asile()
	{
		// On vérifie que le joueur est à l'asile (les modos/admin ont accès)
		if ($this->session->userdata('statut') != Bouzouk::Joueur_Asile)
		{
			$this->echec("T'es même pas à l'asile pour de vrai, tsss");
			return $this->asile();
		}

		// On vérifie que l'asile dure depuis assez longtemps
		if (strtotime(bdd_datetime()) < strtotime($this->session->userdata('date_statut').'+'.$this->session->userdata('duree_asile').' HOUR'))
		{
			$this->echec("Tu es à l'asile depuis moins de ".$this->session->userdata('duree_asile')." heures. Tu dois attendre ce délai pour pouvoir en sortir.");
			return $this->asile();
		}

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('reprendre_asile', "L'appui sur le bouton \"Sortir de l'asile\"", 'required');

		if ( ! $this->form_validation->run())
			return $this->asile();

		// On ajoute à l'historique
		$this->bouzouk->historique(71, 72, array(heures_ecoulees($this->session->userdata('date_statut'))));

		// On change son statut sur le tobozon
		$this->db->set('title', '')
				 ->where('id', $this->session->userdata('id'))
				 ->update('tobozon_users');
					 
		// On annule l'asile
		$data_joueur = array(
			'statut'          => Bouzouk::Joueur_Actif,
			'date_statut'     => bdd_datetime(),
			'duree_asile'     => 0,
			'raison_statut'   => '',
			'statut_staff_id' => null
		);

		// Si la mise à l'asile n'était pas du à une infraction de la charte, on met 75% de stress
		if ($this->session->userdata('raison_statut') == '')
			$data_joueur['stress'] = 75;

		$this->db->where('id', $this->session->userdata('id'))
				 ->update('joueurs', $data_joueur);
		$this->session->set_userdata($data_joueur);

		// On affiche un message de succès
		$this->succes("Tu es sorti de l'asile, fais en sorte de ne jamais y remettre la trompe ! ;)");
		return $this->index();
	}

	public function reprendre_pause()
	{
		// On vérifie que la pause dure depuis au moins 2 jours
		if (strtotime(bdd_datetime()) < strtotime($this->session->userdata('date_statut').'+2 DAY'))
		{
			$this->echec('Ta partie est en pause depuis moins de <span class="pourpre">2 jours</span>. Tu dois attendre <span class="pourpre">2 jours</span> pour pouvoir reprendre ta partie.');
			return $this->en_pause();
		}

		// On ajoute à l'historique
		$this->bouzouk->historique(73, 74, array(jours_ecoules($this->session->userdata('date_statut'))));

		// On change son statut sur le tobozon
		$this->db->set('title', '')
				 ->where('id', $this->session->userdata('id'))
				 ->update('tobozon_users');
					 
		// On annule la pause
		$data_joueur = array(
			'statut'          => Bouzouk::Joueur_Actif,
			'date_statut'     => bdd_datetime(),
			'raison_statut'   => '',
			'statut_staff_id' => null
		);
		$this->db->where('id', $this->session->userdata('id'))
				 ->update('joueurs', $data_joueur);
		$this->session->set_userdata($data_joueur);

		// On affiche un message de succès
		$this->succes('Tu as repris ta partie, bonne route ;)');
		return $this->index();
	}

	public function recommencer_partie()
	{
		// On ajoute à l'historique
		$this->bouzouk->historique(75, null, array(jours_ecoules($this->session->userdata('date_statut'))));

		// On recommence une partie
		$data_joueur = array(
			'statut'             => Bouzouk::Joueur_Etudiant,
			'date_statut'        => bdd_datetime(),
		);
		$this->db->where('id', $this->session->userdata('id'))
				 ->update('joueurs', $data_joueur);
		$this->session->set_userdata($data_joueur);

		// On change son statut sur le tobozon
		$this->db->set('title', '')
				 ->where('id', $this->session->userdata('id'))
				 ->update('tobozon_users');
					 
		// On affiche un message de succès
		$this->succes('Tu as recommencé ta partie, bonne chance ;)');
		redirect('controuille');
	}

	public function distribuer_points_action()
	{
		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('force', 'La force', 'required|is_natural');
		$this->form_validation->set_rules('charisme', 'Le charisme', 'required|is_natural');
		$this->form_validation->set_rules('intelligence', "L'intelligence", 'required|is_natural');

		if ( ! $this->form_validation->run())
		{
			return $this->accueil();
		}

		// On vérifie que le total ne dépasse pas la quantité du joueur
		if ($this->input->post('force') + $this->input->post('charisme') + $this->input->post('intelligence') > $this->session->userdata('points_action'))
		{
			$this->echec("Tu devrais apprendre à compter... Le total de ta distribution dépasse le nombre de tes points d'action !");
			return $this->accueil();
		}

		// On ajoute à l'historique
		$this->bouzouk->historique(76, null, array($this->session->userdata('points_action'), $this->input->post('force'), $this->input->post('charisme'), $this->input->post('intelligence')));

		// On distribue les points
		$this->db->set('`force`', '`force`+'.$this->input->post('force'), false)
				 ->set('charisme', 'charisme+'.$this->input->post('charisme'), false)
				 ->set('intelligence', 'intelligence+'.$this->input->post('intelligence'), false)
				 ->set('points_action', 0)
				 ->where('id', $this->session->userdata('id'))
				 ->update('joueurs');


		// On affiche un message de confirmation
		$this->succes("Tu as bien distribué tes points d'action");
		redirect('joueur/accueil');
	}

	public function deconnexion()
	{
		// On enlève les mutex sur le site
		$this->load->library('lib_gazette');
		$this->lib_gazette->liberer_mutex_joueur($this->session->userdata('id'));

		// Déconnexion du tobozon
		$this->load->library('lib_tobozon');
		$this->lib_tobozon->deconnecter();

		// On détruit des variables du cookie
		$this->input->set_cookie('joueur_id', '', '');
		
		// On détruit la session (tout est géré automatiquement par CodeIgniter, destruction du cookie et suppression des données de la base)
		$this->session->sess_destroy();

		// On redirige vers l'accueil du site
		redirect();
	}
}
