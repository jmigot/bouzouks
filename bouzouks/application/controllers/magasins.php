<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Projet      : Bouzouks
 * Description : affichage des magasins pour acheter des objets; les magasins peuvent fermer à certains moments pour diverses raisons
 *
 * Auteur      : Jean-Luc Migot (jluc.migot@gmail.com)
 * Date        : septembre 2012
 *
 * Copyright (C) 2012-2013 Jean-Luc Migot - Tous droits réservés
 *
 * Ce fichier fait partie du projet Bouzouks.
 * Toute distribution, modification, reproduction ou réutilisation est interdite sans l'accord de tous les auteurs.
 */

class Magasins extends MY_Controller
{
	private $types_autorises = array('faim', 'sante', 'stress', 'boost');
	private $message_fermeture_conseil = "
		<br><br>
		<em>En attendant sa réouverture, tu peux te rendre au marché noir ;)</em>";
	private $messages_fermeture = array(
		array(
			'image'   => 'magasins/vendeurs.gif',
			'message' => 'les vendeurs se sont mis en grève et exigent que JF Sébastien sorte du shop.'
		),
		array(
			'image'   => 'magasins/complot.png',
			'message' => 'un braquage a eu lieu et les malfaiteurs se sont enfuis avec 3 struls. La police est en train de chercher des indices au bistro des pochtrons.'
		),
		array(
			'image'   => 'magasins/maire.gif',
			'message' => 'le maire est actuellement dans le shop en train de faire sa campagne de pub pour la prochaine élection...Fuyez ! Vite ! Il arrive !'
		),
		array(
			'image'   => 'magasins/complot.png',
			'message' => 'les vendeurs ont été pris en otage par le MLB, reviens plus tard.'
		),
		array(
			'image'   => 'magasins/vendeurs.gif',
			'message' => 'le club des Bonnes Moeurs a fait un barrage pour manifester contre la qualité des TV à rayons gamma.'
		),
		array(
			'image'   => 'magasins/secte.gif',
			'message' => "la secte du Schnible est en train d'essayer de recruter de nouveaux adeptes !"
		),
		array(
			'image'   => 'magasins/bouzouiolis.gif',
			'message' => 'les Spaggiouili sont tombés par terre et le shop est devenu une vraie patinoire.'
		),
		array(
			'image'   => 'magasins/police.png',
			'message' => 'la police est actuellement en train de rechercher de la drogue.'
		)
	);

	private $messages_fermeture_etat = array(
		'faim'   => "les vendeurs refusent de te laisser entrer car tu ne peux pas t'empêcher de manger tout ce qui est à ta vue.<br><br>(Pour le zloteur qui n'aurait pas compris, tu dois consommer des objets pour faire miamiam !)",
		'sante'  => "les vendeurs refusent de te laisser entrer car tes microbes sont indésirables dans le shop<br><br>(Pour le zloteur qui n'aurait pas compris, tu dois consommer des objets pour redonner vitalité à ce corps tout flasque !)",
		'stress' => "les vendeurs refusent de te laisser entrer car ton stress fait fuir les clients<br><br>(Pour le zloteur qui n'aurait pas compris, tu dois consommer des objets pour te calmer !)",
	);

	private $messages_vendeur = array(
		'faim'   => 'Vente de<br>plats variés et avariés<br> déjà prêts à être mangés.<br>Sur place ou à emporter ?',
		'sante'  => "Problème<br>gastrique ? Verrue sur la<br>trompe nazale? Ici j'ai<br>ce qu'il te faut.",
		'stress' => "Bonzour !<br>Izi, tu trouveras plein<br>d'obzets inzolites qui<br>réduiront ton stress.",
		'boost'  => "Héé ! Psst !<br> J'ai trouvé des soluces<br> pour tricher à ce jeu débile !<br> Pour quelques struls je<br>te les vends..."
	);

	public function __construct()
	{
		parent::__construct();
		$this->bouzouk->verifier_factures();
		
		// ---------- Hook clans ----------
		// Malediction du Schnibble (SDS)
		if ($this->session->userdata('maudit') && ! $this->bouzouk->is_admin())
			redirect('clans/maudit');
	}

	private function verifier_fermeture_magasin($type)
	{
		if ( ! in_array($type, $this->types_autorises))
			show_404();

		// ---------- Hook clans ----------
		// Propagande (Parti Politique)
		$query = $this->db->select('caa.parametres, c.nom AS nom_clan, c.mode_recrutement')
						  ->from('clans_actions_lancees caa')
						  ->join('clans c', 'c.id = caa.clan_id')
						  ->where('caa.action_id', 11)
						  ->where('caa.statut', Bouzouk::Clans_ActionEnCours)
						  ->get();
		$propagande = ($query->num_rows() == 1) ? $query->row() : null;

		if (isset($propagande))
		{
			$this->load->library('lib_parser');
			$propagande->parametres = unserialize($propagande->parametres);
			$nom_clan = ($propagande->mode_recrutement == Bouzouk::Clans_RecrutementInvisible) ? couleur('Un clan') : couleur(form_prep($propagande->nom_clan));

			// Si le shop est bloqué par un clan, on affiche
			if ($propagande->parametres['shop'] == $type)
			{
				$vars = array(
					'titre_layout' => 'Magasins',
					'titre'        => 'Le magasin est fermé',
					'image_url'    => 'magasins/vendeurs.gif',
					'message'      => $nom_clan.' (parti politique) a fermé ce magasin pour la journée :<br><br>'.$this->lib_parser->remplace_bbcode(nl2br(form_prep($propagande->parametres['texte'])))
				);
				$this->layout->view('blocage', $vars);				
				return true;
			}
		}

		$message   = '';
		$image_url = '';
		$heure     = date('H');
		$minute    = date('i');

		// Faim
		if ($type == 'faim')
		{
			$debut_message = '<strong>Le bouffzouk est fermé :</strong> ';

			// On vérifie l'état du joueur
			if ($this->session->userdata('sante') < $this->bouzouk->config('magasins_sante_bouffzouk'))
				$message = $debut_message.$this->messages_fermeture_etat['sante'];

			else if ($this->session->userdata('stress') > $this->bouzouk->config('magasins_stress_bouffzouk'))
				$message = $debut_message.$this->messages_fermeture_etat['stress'];

			else
			{
				// On vérifie les horaires du magasin
				$horaires_fermeture = array(
					'00' => array('00', '30'),
					'06' => array('00', '30'),
					'12' => array('00', '30'),
					'18' => array('00', '30')
				);

				if (array_key_exists($heure, $horaires_fermeture) AND $minute >= $horaires_fermeture[$heure][0] AND $minute <= $horaires_fermeture[$heure][1])
				{
					// Le jour de l'année détermine le message aléatoire
					$index = date('z') % count($this->messages_fermeture);
					$image_url = $this->messages_fermeture[$index]['image'];
					$message = $debut_message.$this->messages_fermeture[$index]['message'].$this->message_fermeture_conseil;
				}
			}
		}

		// Santé
		else if ($type == 'sante')
		{
			$debut_message = "<strong>L'indispenzouk est fermé : </strong> ";

			// On vérifie l'état du joueur
			if ($this->session->userdata('faim') < $this->bouzouk->config('magasins_faim_indispenzouk'))
				$message = $debut_message.$this->messages_fermeture_etat['faim'];

			else if ($this->session->userdata('stress') > $this->bouzouk->config('magasins_stress_indispenzouk'))
				$message = $debut_message.$this->messages_fermeture_etat['stress'];

			else
			{
				// On vérifie les horaires du magasin
				$horaires_fermeture = array(
					'01' => array('15', '45'),
					'07' => array('15', '45'),
					'13' => array('15', '45'),
					'19' => array('15', '45')
				);

				if (array_key_exists($heure, $horaires_fermeture) AND $minute >= $horaires_fermeture[$heure][0] AND $minute <= $horaires_fermeture[$heure][1])
				{
					// Le jour de l'année détermine le message aléatoire (un décalage permet d'avoir un message différent pour chaque magasin)
					$index = (date('z') + 1) % count($this->messages_fermeture);
					$image_url = $this->messages_fermeture[$index]['image'];
					$message = $debut_message.$this->messages_fermeture[$index]['message'].$this->message_fermeture_conseil;
				}
			}
		}

		// Stress
		else if ($type == 'stress')
		{
			$debut_message = '<strong>Le luxezouk est fermé :</strong> ';

			// On vérifie l'état du joueur
			if ($this->session->userdata('faim') < $this->bouzouk->config('magasins_faim_luxezouk'))
				$message = $debut_message.$this->messages_fermeture_etat['faim'];

			else if ($this->session->userdata('sante') < $this->bouzouk->config('magasins_sante_luxezouk'))
				$message = $debut_message.$this->messages_fermeture_etat['sante'];

			else
			{
				// On vérifie les horaires du magasin
				$horaires_fermeture = array(
					'03' => array('29', '59'),
					'09' => array('29', '59'),
					'15' => array('29', '59'),
					'21' => array('29', '59')
				);

				if (array_key_exists($heure, $horaires_fermeture) AND $minute >= $horaires_fermeture[$heure][0] AND $minute <= $horaires_fermeture[$heure][1])
				{
					// Le jour de l'année détermine le message aléatoire (un décalage permet d'avoir un message différent pour chaque magasin)
					$index = (date('z') + 2) % count($this->messages_fermeture);
					$image_url = $this->messages_fermeture[$index]['image'];
					$message = $debut_message.$this->messages_fermeture[$index]['message'].$this->message_fermeture_conseil;
				}
			}
		}

		// Boost
		else if ($type == 'boost')
		{
			$debut_message = '<strong>Le boostzouk est fermé :</strong> ';

			// On vérifie l'état du joueur
			if ($this->session->userdata('faim') < $this->bouzouk->config('magasins_faim_boostzouk'))
				$message = $debut_message.$this->messages_fermeture_etat['faim'];

			else if ($this->session->userdata('sante') < $this->bouzouk->config('magasins_sante_boostzouk'))
				$message = $debut_message.$this->messages_fermeture_etat['sante'];

			else if ($this->session->userdata('stress') > $this->bouzouk->config('magasins_stress_boostzouk'))
				$message = $debut_message.$this->messages_fermeture_etat['stress'];
			
			else
			{
				// On vérifie les horaires du magasin
				$horaires_fermeture = array(
					'02' => array('15', '45'),
					'08' => array('15', '45'),
					'14' => array('15', '45'),
					'20' => array('15', '45')
				);

				if (array_key_exists($heure, $horaires_fermeture) AND $minute >= $horaires_fermeture[$heure][0] AND $minute <= $horaires_fermeture[$heure][1])
				{
					// Le jour de l'année détermine le message aléatoire (un décalage permet d'avoir un message différent pour chaque magasin)
					$index = (date('z') + 2) % count($this->messages_fermeture);
					$image_url = $this->messages_fermeture[$index]['image'];
					$message = $debut_message.$this->messages_fermeture[$index]['message'].$this->message_fermeture_conseil;
				}
			}
		}

		if ($message != '')
		{
			if ($image_url == '')
				$image_url = 'trop_faible.png';

			$vars = array(
				'titre_layout' => 'Magasins',
				'titre'        => 'Le magasin est fermé',
				'image_url'    => $image_url,
				'message'      => $message
			);
			$this->layout->view('blocage', $vars);

			return true;
		}

		return false;
	}

	private function magasin($type)
	{
		if ( ! in_array($type, $this->types_autorises))
			show_404();

		if ($this->verifier_fermeture_magasin($type))
			return;

		// On va chercher les objets de ce magasin
		$query = $this->db->select('m.quantite, o.id, o.nom, o.type, o.image_url, o.faim, o.sante, o.stress, o.jours_peremption, o.experience, o.force, o.charisme, o.intelligence, o.prix')
						  ->from('magasins m')
						  ->join('objets o', 'o.id = m.objet_id')
						  ->where('o.type', $type)
						  ->get();
		$objets = $query->result();

		// ---------- Hook clans ----------
		// Braquage (Organisation)
		$braquage = $this->bouzouk->clans_braquage();

		// ---------- Hook clans ----------
		// Saint Brigade (SdS)
		$sainte_brigade = null;

		if ($type == 'boost')
			$sainte_brigade = $this->bouzouk->clans_sainte_brigade();

		// On regarde si une promotion est en cours
		$query = $this->db->select('promotion_objet_id AS objet_id')
						  ->from('mairie')
						  ->where('promotion_objet_id IS NOT NULL')
						  ->get();
		$promotion = $query->num_rows() == 1 ? $query->row() : null;

		// On affiche
		$vars = array(
			'type_magasin'    => $type,
			'message_vendeur' => $this->messages_vendeur[$type],
			'objets'          => $objets,
			'braquage'        => $braquage,
			'sainte_brigade'  => $sainte_brigade,
			'promotion'       => $promotion
		);
		$this->layout->view('magasins/magasin', $vars);
	}

	public function bouffzouk()
	{
		$this->magasin('faim');
	}

	public function indispenzouk()
	{
		$this->magasin('sante');
	}

	public function luxezouk()
	{
		$this->magasin('stress');
	}

	public function boostzouk()
	{
		$this->magasin('boost');
	}

	public function acheter()
	{
		// Le type de magasin doit être valide
		if ( ! $this->input->post('type') OR ! in_array($this->input->post('type'), $this->types_autorises))
			show_404();

		if ($this->verifier_fermeture_magasin($this->input->post('type')))
			return;

		// Règles de validation
		$this->load->library('form_validation');
		$this->form_validation->set_rules('objet_id', "L'objet", 'required|is_natural_no_zero');
		$this->form_validation->set_rules('quantite', 'La quantité', 'required|is_natural_no_zero|greater_than[0]|less_than[10]');

		if ( ! $this->form_validation->run())
			return $this->magasin($this->input->post('type'));

		// ---------- Hook clans ----------
		// Saint Brigade (SdS)
		if ($this->input->post('type') == 'boost' && ($sainte_brigade = $this->bouzouk->clans_sainte_brigade()) != null)
		{
			if (($sainte_brigade->parametres['bibles'] && $this->input->post('objet_id') == 19) || ($sainte_brigade->parametres['schnibbles'] && $this->input->post('objet_id') == 18))
			{
				$this->echec('Cet objet a été censuré par '.$sainte_brigade->nom_clan);
				return $this->magasin($this->input->post('type'));
			}
		}
		
		// ---------- Hook clans ----------
		// Pillage compulsif (Organisation)
		if ($this->bouzouk->clans_pillage_compulsif($this->input->post('type')))
		{
			$this->echec('Tu ne peux rien acheter dans ce magasin');
				return $this->magasin($this->input->post('type'));
		}

		// On vérifie que l'objet existe en magasin
		$query = $this->db->select('o.id, m.quantite, o.prix, o.nom, o.peremption')
						  ->from('magasins m')
						  ->join('objets o', 'o.id = m.objet_id')
						  ->where('objet_id', $this->input->post('objet_id'))
						  ->get();

		if ($query->num_rows() == 0)
		{
			$this->echec("Cet objet n'existe pas");
			return $this->magasin($this->input->post('type'));
		}

		$objet = $query->row();

		// On regarde si une promotion est en cours
		$query = $this->db->select('promotion_objet_id AS objet_id')
						  ->from('mairie')
						  ->where('promotion_objet_id', $objet->id)
						  ->get();
		$promotion = $query->num_rows() == 1 ? $query->row() : null;

		if (isset($promotion))
			$objet->prix = round($objet->prix / 2.0, 1);
		
		// On vérifie qu'il en existe au moins de cette quantité
		if ($objet->quantite <= 0)
			$this->echec("Il n'y a plus de <span class='pourpre'>$objet->nom</span> disponible.<br>Nous te conseillons d'aller voir au marché noir s'il en reste... ;)");

		else if ($this->input->post('quantite') > $objet->quantite)
			$this->echec("Il ne reste pas assez de stock pour en acheter autant !");

		// On vérifie que le joueur a assez d'argent pour en acheter à cette quantite
		else if ($this->session->userdata('struls') < $this->input->post('quantite') * $objet->prix)
			$this->echec("Tu n'as pas assez d'argent pour en acheter autant !");

		if ($this->session->userdata('flash_echec') !== false)
			return $this->magasin($this->input->post('type'));

		// On retire la valeur de la marchandise au joueur
		$prix_total = $this->input->post('quantite') * $objet->prix;
 		$this->bouzouk->retirer_struls($prix_total);

 		// On rajoute l'argent à la mairie
 		if ($this->input->post('type') == 'boost')
 		{
			$perte_cagnotte = $prix_total * (100.0 - $this->bouzouk->config('maintenance_pourcentage_prix_boostzouk')) / 100.0;
			$prix_mairie = $prix_total * $this->bouzouk->config('maintenance_pourcentage_prix_boostzouk') / 100.0; // le boostzouk est à xx% pour la mairie

			$this->db->set('struls', 'struls+'.$prix_mairie, false)
					 ->update('mairie');
		}

		else
		{
			$this->db->set('struls', 'struls+'.$prix_total, false)
					 ->update('mairie');
		}

		// On retire la quantité d'objets du magasin
		$this->db->set('quantite', 'quantite - '.$this->input->post('quantite'), false)
				 ->where('objet_id', $this->input->post('objet_id'))
				 ->update('magasins');

		// On ajoute cette quantité d'objets au joueur
		$this->bouzouk->ajouter_objets($this->input->post('objet_id'), $this->input->post('quantite'), $objet->peremption);
		
		$this->bouzouk->historique(77, null, array($this->input->post('quantite'), $objet->nom, struls($prix_total)));

		// On affiche une confirmation
		$this->succes('Tu as acheté <span class="pourpre">'.$this->input->post('quantite').' '.$objet->nom.'</span> au shop pour '.struls($prix_total));
		return $this->magasin($this->input->post('type'));
	}
}
