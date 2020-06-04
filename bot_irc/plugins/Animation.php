<?php

class Animation extends Plugin
{
	private $liste_attente;
	private $liste_bonjours;
	private $liste_bonjours_persos;
	private $liste_highlights;
	private $liste_questions;
	
	private $derniere_rumeur;
	private $frequence_rumeurs;
	private $dernier_topic_id;
	private $dernier_plouk_id;
	private $dernier_check_topic;
	private $dernier_check_plouk;
	private $hosts_kickes;
	private $chut_bans;
	private $derniere_fortune;
	private $derniere_invite;
	private $lignes;
	private $autovoice;
	
	public function __construct($bot)
	{
		parent::__construct($bot);

		$this->derniere_rumeur       = time();
		$this->frequence_rumeurs     = 27; // minutes
		$this->file_attente          = array();
		$this->dernier_topic_id      = 0;
		$this->dernier_plouk_id      = 0;
		$this->dernier_check_topic   = time();
		$this->hosts_kickes          = array();
		$this->chut_bans             = array();
		$this->derniere_fortune      = array();
		$this->derniere_invite       = array();
		$this->lignes                = array();
		$this->liste_attente         = array();
		$this->liste_bonjours        = array();
		$this->liste_bonjours_persos = array();
		$this->liste_highlights      = array();
		$this->liste_questions       = array();
		$this->autovoice             = false;
	
		$this->lire_config();
	}

	private function lire_config()
	{
		if ( ! $this->connect_bdd())
			return;

		$requete = mysql_query('SELECT cle, valeur '.
							   'FROM bot_irc '.
							   'WHERE module = "animation"');

		while ($config = mysql_fetch_object($requete))
		{
			if ($config->cle == 'liste_bonjours')
				$this->liste_bonjours = explode("\n", $config->valeur);

			if ($config->cle == 'liste_bonjours_persos')
				$this->liste_bonjours_persos = explode("\n", $config->valeur);
				
			if ($config->cle == 'liste_highlights')
				$this->liste_highlights = explode("\n", $config->valeur);

			if ($config->cle == 'liste_questions')
				$this->liste_questions = explode("\n", $config->valeur);
		}			
		
		$bonjours_persos = $this->liste_bonjours_persos;
		$this->liste_bonjours_persos = array();
		
		foreach ($bonjours_persos as $infos)
		{
			if (trim($infos) == '')
				continue;
				
			$infos = explode(' ', $infos);
			
			$this->liste_bonjours_persos[] = array(
				'nick'     => array_shift($infos),
				'secondes' => array_shift($infos),
				'texte'    => implode(' ', $infos)
			);
		}
	}

	public function run()
	{
		// Si il y a des actions dans la liste d'attente
		if (count($this->liste_attente) > 0)
			$this->analyser_liste_attente();

		// Envoyer une rumeur de temps en temps
		if ($this->enabled)
			$this->lancer_rumeur();

		// Vérifier les derniers sujets du tobozon
		if ($this->enabled)
			$this->verifier_tobozon();

		// Vérifier les dernières parties de plouk
		if ($this->enabled)
			$this->verifier_plouk();

		// On regarde si il faut recharger la config
		if (file_exists('update_config'))
		{
			$this->lire_config();
			@unlink('update_config');
		}
			
		// On enlève les chut ban qui ont fait leur temps
		$this->supprimer_chut_bans();
		
		if ($this->buffer() === false)
			return;
			
		switch ($this->buffer('event'))
		{
			// On dit bonjour aux nouveaux arrivants
			case 'JOIN':
				if ($this->buffer('text') == $this->bot->config('chan') && $this->enabled)
				{
					if ($this->autovoice)
						$this->command('MODE '.$this->bot->config('chan').' +v '.$this->buffer('nick'));

					$this->ajouter_bonjour();
					$this->lignes[$this->buffer('nick')] = '';
				}
				break;

			// On réponds aux messages
			case 'PRIVMSG':
				// Message privé
				$params = $this->buffer('params');
				
				if ($params[0] == $this->bot->config('nick'))
					$this->analyse_prive();

				// On analyse les messages publiques
				else if ($params[0] == $this->bot->config('chan'))
				{
					if ($this->enabled)
						$this->analyse_publique();

					$this->lignes[$this->buffer('nick')] = $this->buffer('text');
					$this->analyse_admin();
				}
					
				break;

			// Notices
			case 'NOTICE':
				$params = $this->buffer('params');

				if ($params[0] == $this->bot->config('nick'))
					$this->analyse_admin();
				break;
				
			case 'KICK':
				// On rejoint le chan si le bot se fait kicker
				$params = $this->buffer('params');
				
				if ($params[0] == $this->bot->config('chan') && $params[1] == $this->bot->config('nick'))
				{
					$this->bot->join_chan();
					$this->ajouter_liste_attente('Merci beaucoup '.$this->buffer('nick').'...', 3);
				}
				break;
		}
	}

	private function analyse_admin()
	{
		if ($this->buffer('text')[0] != '!')
			return;

		$fail = false;
		
		// Admin fonctions
		if (preg_match('#^!autovoice$#iU', $this->buffer('text'), $matches))
		{
			if ($this->is_op())
			{
				$this->autovoice = ! $this->autovoice;

				if ($this->autovoice)
					$this->send_notice($this->buffer('nick'), 'Autovoice activé');

				else
					$this->send_notice($this->buffer('nick'), 'Autovoice désactivé');
			}
		}

		else if (preg_match('#^!chut (.+)( (\d+))?$#iU', $this->buffer('text'), $matches))
		{
			if ($this->is_hop())
			{
				$nick = trim($matches[1]);
				$minutes = isset($matches[3]) ? (int) $matches[3] : 5;

				if ( ! isset($this->chut_bans[$nick]))
					$this->send_chan("Et hop, un peu de silence pour ".$this->pluriel($minutes, 'minute').", on va avoir des vacances :)");
					
				$this->chut_ban($nick, $minutes);
			}

			else
				$fail = true;
		}

		else if (preg_match('#^!invite all$#i', $this->buffer('text')))
		{
			if ($this->is_op())
			{
				if ($this->connect_bdd() === false)
					return;
				
				// On récupère tous les connectés
				$requete = mysql_query('SELECT id, pseudo FROM joueurs WHERE connecte > (NOW() - INTERVAL 2 MINUTE) AND statut IN (4, 5) ORDER BY pseudo');

				// On envoit la notification
				while ($joueur = mysql_fetch_object($requete))
					mysql_query("INSERT INTO historique VALUES('', ".$joueur->id.", 215, NULL, '".serialize(array('J.F Sébastien'))."', '".date('Y-m-d H:i:s')."', 1, 0)");

				$this->close_bdd();
				$this->ajouter_liste_attente('Tous les bouzouks connectés ont été invités à venir sur le tchat !', 1);
			}
		}

		else if (preg_match('#^!kick (.+)$#i', $this->buffer('text'), $matches))
		{
			if ($this->is_hop())
			{
				$nick = $matches[1];
				$reason = 'Allez hop du vent';

				if (strpos($nick, ' ') !== false)
				{
					$tmp = explode(' ', $nick);
					$nick = $tmp[0];
					$reason = $tmp[1];
				}

				if ($this->is_op(trim($nick)))
					$this->send_chan("T'y as cru ou quoi ".$this->buffer('nick').' ?');

				else
				{
					if ($nick == 'all' && $this->is_op())
					{
						$chan = $this->bot->config('chan');

						foreach ($this->liste_nicks() as $pseudo => $mode)
						{
							if ( ! preg_match('#(~|@|&)#', $mode))
								$this->bot->write("KICK $chan $pseudo :Le pouvoir du Kick All !!");
						}
					}

					else
						$this->kick($nick, $reason);
				}
			}
		}

		else if (preg_match('#^!ban (.+)$#i', $this->buffer('text'), $matches))
		{
			if ($this->is_hop())
			{
				$nick = $matches[1];
				$reason = 'Allez hop du vent';

				if (strpos($nick, ' ') !== false)
				{
					$tmp = explode(' ', $nick);
					$nick = $tmp[0];
					$reason = $tmp[1];
				}

				if ($this->is_op(trim($nick)))
					$this->send_chan("T'y as cru ou quoi ".$this->buffer('nick').' ?');
				
				else
				{
					$this->ban($nick);
					$this->kick($nick, $reason);
				}
			}
		}

		else if (preg_match('#^!unban (.+)$#i', $this->buffer('text'), $matches))
		{
			if ($this->is_hop())
			{
				$this->unban($matches[1]);
				$this->send_notice($matches[1], "Je t'ai débanni de ".$this->bot->config('chan')."...ouais je sais je suis trop cool !");
			}
		}

		else if (preg_match('#^!dechut (.+)$#i', $this->buffer('text'), $matches))
		{
			if ($this->is_hop())
			{
				$nick = trim($matches[1]);
				
				if (isset($this->chut_bans[$nick]))
					$this->send_chan("Bon ça va je suis cool je te redonne la parole mais fais gaffe...");

				$this->supprimer_chut_ban($nick);
			}

			else
				$fail = true;
		}

		else if (preg_match('#^!rumeur$#i', $this->buffer('text')))
		{
			if ($this->is_hop())
			{
				$this->lancer_rumeur(true);
			}
		}
		
		else if (preg_match('#^!stop$#i', $this->buffer('text'), $matches))
		{
			if ($this->is_hop())
			{
				$this->disable();
				$this->send_notice($this->buffer('nick'), 'Bot désactivé');
			}

			else
				$fail = true;
		}

		else if (preg_match('#^!start$#i', $this->buffer('text'), $matches))
		{
			if ($this->is_hop())
			{
				$this->enable();
				$this->send_notice($this->buffer('nick'), 'Bot activé');
			}

			else
				$fail = true;
		}

		else if (preg_match('#^!say (.+)$#i', $this->buffer('text'), $matches))
		{
			if ($this->is_op())
				$this->send_chan($matches[1]);

			else
				$fail = true;
		}

		else if (preg_match('#^!couleur (.+)$#i', $this->buffer('text'), $matches))
		{
			if ($this->is_hop())
			{
				if ($this->set_color($matches[1]))
					$this->send_notice($this->buffer('nick'), 'Le bot écrit maintenant en '.$matches[1]);
				
				else
				{
					$couleurs = '';
					foreach ($this->colors as $couleur => $code)
						$couleurs .= $couleur.', ';
					$couleurs = substr($couleurs, 0, mb_strlen($couleurs) - 2);

					$this->send_notice($this->buffer('nick'), "Cette couleur n'est pas disponible ($couleurs)");
				}
			}

			else
				$fail = true;
		}

		else if (preg_match('#^!gras$#i', $this->buffer('text'), $matches))
		{
			if ($this->is_hop())
			{
				if ($this->toggle_bold())
					$this->send_notice($this->buffer('nick'), 'Le bot écrit maintenant en gras');

				else
					$this->send_notice($this->buffer('nick'), "Le bot n'écrit plus en gras");
			}

			else
				$fail = true;
		}

		if ($fail)
		{
			$action = mt_rand(0, 2);
			
			if ($action == 0)
			{
				$texte = $this->buffer('nick').' je vais te défoncer la trompe !!';
				$this->ajouter_liste_attente($texte, 1);
			}

			else if ($action == 1)
				$this->kick($this->buffer('nick'), 'Petit con va...');

			else
			{
				$texte = 'Non mais tu te prends pour qui '.$this->buffer('nick').' sérieux ?';
				$secondes = mt_rand(2, 5);
				$this->ajouter_liste_attente($texte, $secondes);
			}
		}
	}

	private function analyse_prive()
	{
		$upper_texte = mb_strtoupper($this->buffer('text'));
		
		if (in_array($upper_texte, array('VERSION', chr(1).'VERSION'.chr(1))))
			$this->send_notice($this->buffer('nick'), 'Turbouzoukoréactor 1.0 - http://www.bouzouks.net');

		else if (in_array($upper_texte, array('TIME', chr(1).'TIME'.chr(1))))
			$this->send_notice($this->buffer('nick'), "Tu veux l'heure ? Hum hé bien d'après ma montre à résine de pioupiouk, il est 25h81 :)");

		else if (in_array($upper_texte, array('PING', chr(1).'PING'.chr(1))))
			$this->send_notice($this->buffer('nick'), 'ping-pong ? Sinon on joue au billard tsé, ou au flipper. Je peux même te prendre au baby-foot mon gars/ma meuf !');

		else if ($this->enabled)
		{
			if (preg_match('#^!invite (.*)$#i', $this->buffer('text'), $matches))
				$this->invite($this->buffer('nick'), trim($matches[1]), true);

			else
				$this->analyse_admin();
		}

		else
			$this->analyse_admin();
	}
	
	private function ajouter_liste_attente($texte, $secondes, $bot = null)
	{
		$this->liste_attente[] = array(
			'texte' => $texte,
			'time'  => time() + $secondes,
			'bot'   => $bot
		);
	}

	private function remplace_sexe($texte, $nick)
	{
		$pattern = '#{sexe:(.+)\|(.+)}#U';

		if (preg_match($pattern, $texte))
		{
			// On récupère le sexe du pseudo
			$sexe = 'male';

			if ($this->connect_bdd() !== false)
			{
				$requete = mysql_query('SELECT sexe FROM joueurs WHERE pseudo = "'.mysql_real_escape_string($nick).'"');

				if (mysql_num_rows($requete) == 1)
				{
					$joueur = mysql_fetch_object($requete);
					$sexe = $joueur->sexe;
				}

				$this->close_bdd();
			}

			// On remplace
			if ($sexe == 'male')
				$texte = preg_replace($pattern, '$1', $texte);

			else
				$texte = preg_replace($pattern, '$2', $texte);
		}

		return $texte;
	}

	private function chut_ban($nick, $minutes = 5)
	{
		$this->command('MODE '.$this->bot->config('chan').' +bb ~q:'.$nick.' ~n:'.$nick);
		$this->chut_bans[$nick] = time() + $minutes*60;
	}

	private function supprimer_chut_ban($nick)
	{
		$this->command('MODE '.$this->bot->config('chan').' -bb ~q:'.$nick.' ~n:'.$nick);
		unset($this->chut_bans[$nick]);
	}

	private function ajouter_bonjour()
	{
		// Si le nick qui a rejoint n'est pas le bot
		if ($this->buffer('nick') != $this->bot->config('nick'))
		{
			// Bonjours personnalisés
			foreach ($this->liste_bonjours_persos as $infos)
			{
				if (mb_strtolower($this->buffer('nick')) == mb_strtolower($infos['nick']))
				{
					$texte = $infos['texte'];
					$secondes = $infos['secondes'];
				}
			}
			
			if ( ! isset($texte, $secondes))
			{
				$texte = $this->liste_bonjours[mt_rand(0, count($this->liste_bonjours) - 1)];
				$texte = str_replace('{nick}', $this->buffer('nick'), $texte);
				$texte = $this->remplace_sexe($texte, $this->buffer('nick'));
				$secondes = mt_rand(5, 11);
			}

			$this->ajouter_liste_attente($texte, $secondes);
		}
	}

	private function analyser_liste_attente()
	{
		$time = time();

		// On parcourt tout ce qu'il reste à dire dans la file
		foreach ($this->liste_attente as $cle => $infos)
		{
			if ($infos['time'] <= $time)
			{
				$this->send_chan($infos['texte'], array(), $infos['bot']);
				unset($this->liste_attente[$cle]);
			}
		}
	}
	
	private function analyse_publique()
	{
		if (preg_match('#^lol$#i', $this->buffer('text')))
			$this->send_chan('...ita');

		else if (preg_match('#!bol (1|2|3|KAH|ZIG|STO)$#i', $this->buffer('text'), $match))
		{
			$bol = mt_rand(1, 3);
			$texte = '/ \\ / \\ / \\';

			// On remplit le bon bol
			if ($bol == 1)
				$texte[1] = 'o';

			else if ($bol == 2)
				$texte[5] = 'o';

			else
				$texte[9] = 'o';

			// Gagné
			$nombres_bouzouks = array('KAH' => 1, 'ZIG' => 2, 'STO' => 3);
			
			if ($bol == intval($match[1]) || (in_array(strtoupper($match[1]), array_keys($nombres_bouzouks)) && $bol == $nombres_bouzouks[strtoupper($match[1])]))
				$texte .= ' Bravo '.$this->buffer('nick').', tu as gagné !';

			// Perdu
			else
				$texte .= ' Perdu '.$this->buffer('nick').', tu es vraiment trop mauvais...';

			$this->ajouter_liste_attente($texte, 1, 'JF`Sebastien');
		}

		else if (preg_match('#connard|ta mere|ta mère|enfoiré|salope|batard| pute|enculé|putain|encule|fuck|baise | bite#i', $this->buffer('text')))
		{
			if ($this->buffer('host') == 'bouzouks.net')
				return;
				
			// On augmente le nombre de kicks de la personne
			if (isset($this->hosts_kickes[$this->buffer('host')]))
				$this->hosts_kickes[$this->buffer('host')]['nb_kicks']++;
			else
				$this->hosts_kickes[$this->buffer('host')] = array('nb_kicks' => 1);

			// Trop de kicks = chut ban
			if ($this->hosts_kickes[$this->buffer('host')]['nb_kicks'] >= 3)
			{
				$this->chut_ban($this->buffer('host'));
				$this->hosts_kickes[$this->buffer('host')]['nb_kicks'] = 0;
			}

			// On kick la personne
			$this->kick($this->buffer('nick'), 'Merci de parler correctement');
		}

		else if (preg_match('#('.$this->bot->config('nick').'|JF`Sebastien) ?\?#', $this->buffer('text'), $match))
		{
			$texte = str_replace('{nick}', $this->buffer('nick'), $this->liste_questions[mt_rand(0, count($this->liste_questions) - 1)]);
			$texte = $this->remplace_sexe($texte, $this->buffer('nick'));
			$secondes = mt_rand(2, 5);
			$this->ajouter_liste_attente($texte, $secondes, $match[1]);
		}

		else if (preg_match('#('.$this->bot->config('nick').'|JF`Sebastien)#', $this->buffer('text'), $match))
		{
			$texte = str_replace('{nick}', $this->buffer('nick'), $this->liste_highlights[mt_rand(0, count($this->liste_highlights) - 1)]);
			$texte = $this->remplace_sexe($texte, $this->buffer('nick'));
			$secondes = mt_rand(2, 5);
			$this->ajouter_liste_attente($texte, $secondes, $match[1]);
		}

		else if (preg_match('#^!fortune (.*)$#i', $this->buffer('text'), $matches))
		{
			if ( ! isset($this->lignes[$this->buffer('nick')]) || $this->lignes[$this->buffer('nick')] == '')
				$this->send_chan('Hey '.$this->buffer('nick').', ça te tuerait de dire bonjour avant de me causer ? Sale pochtron va...');

			else
				$this->fortune($matches[1]);
		}

		else if (preg_match('#^!invite (.*)$#i', $this->buffer('text'), $matches))
		{
			$this->invite($this->buffer('nick'), trim($matches[1]));
		}
		
		else
		{
			if (mt_rand(1, 50) == 1)
			{
				$texte = str_replace('{nick}', $this->buffer('nick'), $this->liste_highlights[mt_rand(0, count($this->liste_highlights) - 1)]);
				$texte = $this->remplace_sexe($texte, $this->buffer('nick'));
				$secondes = mt_rand(2, 5);
				$this->ajouter_liste_attente($texte, $secondes);
			}

		}
	}

	private function invite($emetteur, $destinataire, $prive = false)
	{
		if ($destinataire == 'all')
			return;

		// On regarde si le host a déjà fait trop de demandes d'invite (1 invite toutes les 5min)
		if (isset($this->derniere_invite[$this->buffer('host')]) && $this->derniere_invite[$this->buffer('host')] >= time() - 60*5)
		{
			$texte = "T'as pas un peu l'impression d'abuser là $emetteur ?!";
			
			if ($prive)
				$this->send_prive($emetteur, $texte);
			else
				$this->ajouter_liste_attente($texte, 2);

			return;
		}

		if ($emetteur == $destinataire)
		{
			$texte = "$emetteur tu serais pas un peu concon ?";
			
			if ($prive)
				$this->send_prive($emetteur, $texte);
			else
				$this->ajouter_liste_attente($texte, 2);

			return;
		}
		
		if ($this->connect_bdd() === false)
			return;

		// On regarde si l'emetteur existe
		$query = mysql_query('SELECT id FROM joueurs WHERE pseudo="'.mysql_real_escape_string($emetteur).'" AND statut IN (4, 5)');

		if (mysql_num_rows($query) == 0)
		{
			$this->close_bdd();
			$texte = "$emetteur je ne te connais pas sur Bouzouks.";

			if ($prive)
				$this->send_prive($emetteur, $texte);
			else
				$this->ajouter_liste_attente($texte, 2);

			return;
		}
		
		// On regarde si le destinataire existe
		$query = mysql_query('SELECT id, connecte FROM joueurs WHERE pseudo="'.mysql_real_escape_string($destinataire).'" AND statut IN (4, 5)');

		if (mysql_num_rows($query) == 0)
		{
			$this->close_bdd();
			$texte = "Ce bouzouk n'existe pas ou alors il est trop loin pour moi...";
			
			if ($prive)
				$this->send_prive($emetteur, $texte);
			else
				$this->ajouter_liste_attente($texte, 2);

			return;
		}

		$joueur = mysql_fetch_object($query);

		// On regarde si le joueur est connecté
		if (strtotime($joueur->connecte) < strtotime('-2 MINUTE'))
		{
			$this->close_bdd();
			$texte = "Ce bouzouk n'est plus connecté, mets tes lunettes $emetteur...";

			if ($prive)
				$this->send_prive($emetteur, $texte);
			else
				$this->ajouter_liste_attente($texte, 1);
				
			return;
		}

		// On ajoute la demande
		if ( ! $this->is_hop())
			$this->derniere_invite[$this->buffer('host')] = time();

		// On envoit la notification
		mysql_query("INSERT INTO historique VALUES('', ".$joueur->id.", 215, NULL, '".serialize(array(mysql_real_escape_string($emetteur)))."', '".date('Y-m-d H:i:s')."', 1, 0)");
		$this->close_bdd();

		// On affiche une confirmation
		$texte = "$destinataire a été invité par $emetteur à venir sur le tchat";

		if ($prive)
			$this->send_prive($emetteur, $texte);
		else
			$this->ajouter_liste_attente($texte, 1);
	}

	private function lancer_rumeur($force = false)
	{
		if ($force || ($this->derniere_rumeur <= time() - ($this->frequence_rumeurs * 60)))
		{
			if ($this->connect_bdd() === false)
				return;

			$requete = mysql_query('SELECT texte '.
								   'FROM rumeurs '.
								   'WHERE statut=3 '.
								   'ORDER BY RAND() '.
								   'LIMIT 1');
			$rumeur = mysql_fetch_object($requete);
			$this->close_bdd();

			$this->send_chan('«'.$rumeur->texte.'»', array(), 'JF`Sebastien');
			$this->derniere_rumeur = time();
		}
	}
	
	private function verifier_tobozon()
	{
		$time = time();
		
		if ($time - 30 < $this->dernier_check_topic)
			return;

		$this->dernier_check_topic = $time;
		
		if ($this->connect_bdd() === false)
			return;
			
		// On récupère les derniers topics visibles en mode visiteur
		$requete = mysql_query('SELECT tt.id, tt.poster, tt.subject, tf.forum_name '.
							   'FROM tobozon_topics tt '.
							   'JOIN tobozon_forums tf ON tf.id = tt.forum_id '.
							   'LEFT JOIN tobozon_forum_perms fp ON fp.forum_id = tt.forum_id AND fp.group_id = 3 '.
							   'WHERE (fp.read_forum IS NULL OR fp.read_forum = 1) AND tf.clan_mode != 3 '.
							   'ORDER BY tt.posted DESC LIMIT 1');
		$topic = mysql_fetch_object($requete);
		$this->close_bdd();

		if ($topic->id > $this->dernier_topic_id)
		{
			$dernier_id = $this->dernier_topic_id;
			$this->dernier_topic_id = $topic->id;

			if ($dernier_id > 0)
			{
				$texte = 'Nouveau topic sur le tobozon : ['.$topic->subject.'] par '.$topic->poster.' dans ['.$topic->forum_name.'] : http://www.bouzouks.net/tobozon/viewtopic.php?id='.$topic->id;
				$this->ajouter_liste_attente($texte, 5);
			}
		}
	}

	private function verifier_plouk()
	{
		$time = time();
		
		if ($time - 10 < $this->dernier_check_plouk)
			return;

		$this->dernier_check_plouk = $time;
		
		if ($this->connect_bdd() === false)
			return;
			
		// On récupère la dernière partie de plouk
		$requete = mysql_query('SELECT mcp.id, mcp.createur_id, mcp.adversaire_id, j1.pseudo AS createur_pseudo, j2.pseudo AS adversaire_pseudo, mcp.gagnant_id '.
							   'FROM mc_plouk mcp '.
							   'JOIN joueurs j1 ON j1.id = mcp.createur_id '.
							   'JOIN joueurs j2 ON j2.id = mcp.adversaire_id '.
							   'WHERE mcp.id > '.$this->dernier_plouk_id.' '.
							   'ORDER BY mcp.id DESC LIMIT 5');
		
		$dernier_id = $this->dernier_plouk_id;

		while ($partie = mysql_fetch_object($requete))
		{
			if ($partie->id > $this->dernier_plouk_id)
				$this->dernier_plouk_id = $partie->id;

			if ($dernier_id > 0)
			{
				$gagnant_pseudo = ($partie->gagnant_id == $partie->createur_id) ? $partie->createur_pseudo : $partie->adversaire_pseudo;
				$perdant_pseudo = ($partie->gagnant_id == $partie->createur_id) ? $partie->adversaire_pseudo : $partie->createur_pseudo;

				$texte = $gagnant_pseudo.' vient de gagner au plouk contre '.$perdant_pseudo;
				$this->ajouter_liste_attente($texte, 2, 'JF`Sebastien');
			}
		}

		$this->close_bdd();
	}

	private function fortune($pseudo)
	{
		$pseudo = trim($pseudo);
		$secondes = 2;
		
		// On regarde si le host a déjà fait trop de demandes de fortune
		if (isset($this->derniere_fortune[$this->buffer('host')]) && $this->derniere_fortune[$this->buffer('host')]['timestamp'] >= time() - 60*30)
		{
			$texte = "Aaah toi ".$this->buffer('nick')." je te parle plus !";
			$this->ajouter_liste_attente($texte, $secondes);
			return;
		}
		
		if ($this->connect_bdd() === false)
			return;

		// On va chercher le nombre de struls du joueur
		$query = mysql_query('SELECT id, struls FROM joueurs WHERE pseudo="'.mysql_real_escape_string($pseudo).'" AND statut IN (4, 5, 6)');

		if (mysql_num_rows($query) == 0)
		{
			// On regarde si c'est une entreprise
			$query = mysql_query('SELECT struls FROM entreprises WHERE nom="'.mysql_real_escape_string($pseudo).'"');

			if (mysql_num_rows($query) == 0)
			{
				$this->close_bdd();
				$texte = "Désolé ".$this->buffer('nick')." mais ce bouzouk n'existe pas...revois tes sources";
				$this->ajouter_liste_attente($texte, $secondes);
				return;
			}

			else
			{
				$entreprise = mysql_fetch_object($query);
				$fortune = $entreprise->struls;

				// Une fois de temps en temps le nombre est faux
				if (mt_rand(1, 15) == 1)
					$fortune = (int)($fortune * mt_rand(15, 350) / 100);
					
				$texte = "La fortune de '$pseudo' s'élève à ".number_format($fortune, 1, '.', ' ').($fortune <= 1 ? ' strul' : ' struls');
			}
		}

		else
		{
			$joueur = mysql_fetch_object($query);

			// ---------- Hook clans ----------
			// Magouille fiscale (Struleone)
			// On regarde si une magouille fiscale est en cours sur ce joueur
			$query = mysql_query('SELECT p.joueur_id, c.chef_id FROM clans_actions_lancees cal JOIN politiciens p ON p.clan_id = cal.clan_id JOIN clans c ON c.id = cal.clan_id WHERE cal.action_id=27 AND cal.statut=1');
			$joueurs_ids = array();

			while ($magouille_fiscale = mysql_fetch_object($query))
			{
				if (isset($magouille_fiscale->joueur_id) && ! in_array($magouille_fiscale->joueur_id, $joueurs_ids))
					$joueurs_ids[] = $magouille_fiscale->joueur_id;

				if (isset($magouille_fiscale->chef_id) && ! in_array($magouille_fiscale->chef_id, $joueurs_ids))
					$joueurs_ids[] = $magouille_fiscale->chef_id;
			}

			if (in_array($joueur->id, $joueurs_ids))
				$fortune = 0;

			else
			{

				$fortune = $joueur->struls;

				// Valeur des objets de la maison
				$query = mysql_query('SELECT SUM(o.prix * m.quantite) AS prix_total FROM maisons m JOIN objets o ON o.id = m.objet_id WHERE m.joueur_id='.$joueur->id);

				if (mysql_num_rows($query) == 1)
				{
					$maison = mysql_fetch_object($query);
					$fortune += (int) $maison->prix_total;
				}

				// Valeur des objets en vente au marché noir
				$query = mysql_query('SELECT SUM(o.prix * m_n.quantite) AS prix_total FROM marche_noir m_n JOIN objets o ON o.id = m_n.objet_id WHERE m_n.joueur_id='.$joueur->id);

				if (mysql_num_rows($query) == 1)
				{
					$marche_noir = mysql_fetch_object($query);
					$fortune += (int) $marche_noir->prix_total;
				}

				// Une fois de temps en temps le nombre est faux
				if (mt_rand(1, 10) == 1)
					$fortune = (int)($fortune * mt_rand(15, 350) / 100);
			}

			$texte = "La fortune de $pseudo s'élève à ".number_format($fortune, 1, '.', ' ').($fortune <= 1 ? ' strul' : ' struls');
		}
		
		$this->close_bdd();
		$this->ajouter_liste_attente($texte, $secondes);

		if ( ! $this->is_op())
		{
			$host = $this->buffer('host');

			// On ajoute la demande
			if ( ! isset($this->derniere_fortune[$host]))
				$this->derniere_fortune[$host] = array('nb' => 1, 'timestamp' => 0);

			// On augmente le nombre de demandes
			else
			{
				$this->derniere_fortune[$host]['nb']++;

				// Au bout de 3 on reset et on bloque l'ip
				if ($this->derniere_fortune[$host]['nb'] >= 3)
					$this->derniere_fortune[$host] = array('nb' => 0, 'timestamp' => time());
			}
		}
	}
	
	private function supprimer_chut_bans()
	{
		$time_actuel = time();
		
		foreach ($this->chut_bans as $host => $time)
		{
			// Temps écoulé, on enlève le ban
			if ($time_actuel >= $time)
			{
				$this->send_chan("Bon ça va je suis cool je te redonne la parole mais fais gaffe...");
				$this->supprimer_chut_ban($host);
			}
		}
	}
}
