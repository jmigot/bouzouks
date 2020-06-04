<?php

class Quizz extends Plugin
{
	private $questions;
	private $config;
	private $question_running;
	private $num_question;
	private $last_time;
	private $moderation;
	private $paused;
	private $scores;
	private $teams;
	private $teams_on;
	private $paused_admin;
	private $bad_answers;
	private $chut_bans;
	
	public function __construct($bot)
	{
		parent::__construct($bot);	
		$this->disable();

		$this->config = array(
			'pause_duration'          => 5, // min
			'nb_questions_pause'      => 15,
			'question_duration'       => 90, // seconds
			'delay_between_questions' => 30, // secondes
			'delay_between_rules'     => 4, // seconds
			'delay_between_scores'    => 2, // seconds
		);
		$this->paused_admin = false;
		$this->bad_answers = array();
		$this->chut_bans = array();
		$this->moderation = true;
		$this->teams = array();
		$this->teams_on = false;
	}

	public function run()
	{
		if ($this->buffer() !== false)
		{
			$this->analyse_admin();
			
			//  Teams managment
			if ($this->teams_on)
				$this->analyse_teams();
		}

		// Quizz disabled
		if ( ! $this->enabled)
			return;

		// Verify chut bans
		$this->verify_chut_bans();
		
		$time = time();

		// Question is in progress
		if ($this->question_running)
		{
			// Out of time
			if ($time >= $this->last_time + $this->config['question_duration'])
			{
				$question = $this->questions[$this->num_question];
				$this->send_chan("[Quizz] Personne n'a donné de bonne réponse, bande de bons à rien...la réponse était : ".chr(2).$question->answer);
				$this->next_question();
			}

			// Public message
			else if ($this->buffer() !== false && $this->buffer('event') == 'PRIVMSG')
			{
				$params = $this->buffer('params');

				// Messages publiques
				if (mb_strtolower($params[0]) == mb_strtolower($this->bot->config('chan')) && mb_substr($this->buffer('text'), 0, 7) != '!quizz ')
				{
					// Good answer
					if ($this->good_answer($this->buffer('text'), $this->questions[$this->num_question]))
					{
						$question = $this->questions[$this->num_question];
						$this->add_points($question->points, $this->buffer('nick'));
						$this->send_chan('[Quizz] '.chr(2).'Bravo '.$this->buffer('nick').chr(2).', la réponse était bien : '.chr(2).$question->answer.chr(2).', tu gagnes '.$this->pluriel($question->points, 'point'));
						$this->next_question();
					}

					// Bad answer
					else if ($this->moderation)
					{
						if ( ! isset($this->bad_answers[$this->buffer('nick')]))
							$this->bad_answers[$this->buffer('nick')] = 1;

						else
							$this->bad_answers[$this->buffer('nick')]++;

						if ($this->bad_answers[$this->buffer('nick')] >= 3)
							$this->chut($this->buffer('nick'));
					}
				}
			}
		}

		else if ($this->paused)
		{
			// Stop pause
			if (time() >= $this->last_time + $this->config['pause_duration'] * 60)
			{
				$this->paused = false;
				$this->last_time = time();
				$this->send_chan('[Quizz] Reprise du quizz dans '.$this->config['delay_between_questions'].' secondes !', array('color' => 'pourpre', 'bold' => true));
			}
		}

		// No pause and question not running, next question is soon
		else if ($time >= $this->last_time + $this->config['delay_between_questions'])
			$this->send_question();
	}

	public function analyse_teams()
	{
		if ($this->buffer('event') != 'PRIVMSG')
			return;

		if (preg_match('#^!quizz join (.+)(?:/(.+))?$#Ui', $this->buffer('text'), $matches))
		{
			$team_name = $matches[1];
			$password = isset($matches[2]) ? trim($matches[2]) : NULL;

			// On vérifie le nom d'équipe
			if ( ! preg_match('#^[^/]{3,30}$#', $team_name))
				return $this->send_notice($this->buffer('nick'), "[Quizz - équipe] Le nom d'équipe doit faire entre 3 et 30 caractères, le caractère '/' délimite le mot de passe");
				
			// On vérifie le mot de passe
			if (isset($password) && ! preg_match('#^.{3,15}$#', $password))
				return $this->send_notice($this->buffer('nick'), "[Quizz - équipe] Le mot de passe doit faire entre 3 et 15 caractères");

			// On regarde si le joueur a déjà une équipe
			foreach ($this->teams as $team)
			{
				if (in_array($this->buffer('nick'), $team['nicks']))
					return $this->send_notice($this->buffer('nick'), "[Quizz - équipe] Tu es déjà dans l'équipe ".$this->bold($team['name']).', tape '.$this->bold('!quizz leave').' pour la quitter');
			}

			// On vérifie que le nom d'équipe n'existe pas déjà
			foreach ($this->teams as $team_key => $team)
			{
				if ($team['name'] == $team_name)
				{
					// Mot de passe incorrect ?
					if ($password != $team['password'])
						return $this->send_notice($this->buffer('nick'), '[Quizz - équipe] Mot de passe incorrect');

					// Plus assez de place ?
					if (count($team['nicks']) >= 4)
						return $this->send_notice($this->buffer('nick'), "[Quizz - équipe] Cette équipe est déjà complète");

					// On rejoint l'équipe
					$this->teams[$team_key]['nicks'][] = $this->buffer('nick');
					return $this->send_notice($this->buffer('nick'), "[Quizz - équipe] Tu as bien rejoint l'équipe ".$this->bold($team_name).', tape '.$this->bold('!quizz leave').' pour la quitter');
				}
			}

			// L'équipe n'existe pas encore, on l'ajoute
			$this->teams[] = array(
				'name'     => $team_name,
				'password' => $password,
				'nicks'    => array($this->buffer('nick'))
			);

			// On prévient le membre
			$password = isset($password) ? 'mot de passe : '.$this->bold($password) : 'pas de mot de passe';
			$this->send_notice($this->buffer('nick'), "[Quizz - équipe] Tu as bien créé et rejoint l'équipe ".$this->bold($team_name)." ($password), tape ".$this->bold('!quizz leave').' pour la quitter');
		}

		else if (preg_match('#^!quizz leave$#i', $this->buffer('text')))
			$this->leave_team($this->buffer('nick'));
	}

	public function analyse_admin()
	{
		if ($this->buffer('event') != 'PRIVMSG' || ! $this->is_op())
			return;

		if (preg_match('#^!quizz start#i', $this->buffer('text')))
		{
			if ($this->enabled || $this->paused_admin)
				$this->send_notice($this->buffer('nick'), 'Le quizz est déjà en cours, abruti');

			else
			{
				if ($this->start_quizz())
					$this->send_notice($this->buffer('nick'), 'Le quizz est maintenant activé');
			}
		}

		else if (preg_match('#^!quizz stop#i', $this->buffer('text')))
		{
			if ( ! $this->enabled && ! $this->paused_admin)
				$this->send_notice($this->buffer('nick'), 'Le quizz est même pas encore lancé, crétin');

			else
			{
				$this->stop_quizz(false);
				$this->send_notice($this->buffer('nick'), 'Le quizz est maintenant désactivé');
			}
		}

		else if (preg_match('#^!quizz regles#i', $this->buffer('text')))
			$this->send_rules();

		else if (preg_match('#^!quizz moderation#i', $this->buffer('text')))
		{
			$this->moderation = ! $this->moderation;

			if ($this->moderation)
				$this->send_chan('[Quizz] Le bot modère maintenant les mauvaises réponses');
			
			else
			{
				// On supprime tous les bans
				foreach ($this->chut_bans as $nick => $ban_time)
				{
					$this->command('MODE '.$this->bot->config('chan').' -b ~q:'.$nick);
					$this->command('MODE '.$this->bot->config('chan').' -b ~n:'.$nick);
				}

				$this->chut_bans = array();

				// On vide les mauvaises réponses
				$this->bad_answers = array();

				$this->send_chan('[Quizz] Le bot ne modère plus les mauvaises réponses');
			}
		}

		else if (preg_match('#^!quizz pause#', $this->buffer('text')))
		{
			if ( ! $this->enabled)
				$this->send_notice($this->buffer('nick'), 'Le quizz est même pas lancé, trou du cul');

			else
			{
				$this->disable();
				$this->paused_admin = true;
				$this->send_chan('[Quizz] Le quizz est maintenant en pause', array('color' => 'rouge', 'bold' => true));
			}
		}

		else if (preg_match('#^!quizz reprise#', $this->buffer('text')))
		{
			if ( ! $this->paused_admin)
				$this->send_notice($this->buffer('nick'), 'Le quizz est même pas en pause, petit con');

			else
			{
				$this->enable();
				$this->paused_admin = false;
				$this->last_time = time();
				$this->send_chan('[Quizz] Le quizz a maintenant repris', array('color' => 'rouge', 'bold' => true));
			}
		}

		else if (preg_match('#^!quizz scores#i', $this->buffer('text')))
			$this->send_scores();

		else if (preg_match('#^!quizz teams reset#i', $this->buffer('text')))
		{
			$this->teams = array();
			$this->send_notice($this->buffer('nick'), '[Quizz] Les équipes ont été réinitialisées');
		}

		else if (preg_match('#^!quizz teams on#i', $this->buffer('text')))
		{
			$this->teams_on = true;
			$this->send_chan('[Quizz] Les équipes sont ouvertes ! Tapez "!quizz join Mon équipe" ou "!quizz join Mon équipe/mot_de_passe" en privé à Bouzouk pour en créer/rejoindre une (4 max. par équipe, mot de passe facultatif)', array('color' => 'rouge', 'bold' => true));
		}

		else if (preg_match('#^!quizz teams off#i', $this->buffer('text')))
		{
			$this->teams_on = false;
			$this->send_chan('[Quizz] Les équipes sont maintenance fermées', array('color' => 'rouge', 'bold' => true));
		}

		else if (preg_match('#^!quizz teams$#i', $this->buffer('text')))
		{
			$this->send_teams();
		}

		else if (preg_match('#^!quizz leave (.+)$#i', $this->buffer('text'), $matches))
			$this->leave_team(trim($matches[1]), true);
	}

	public function leave_team($nick, $forced = false)
	{
		foreach ($this->teams as $team_key => $team)
		{
			// Joueur trouvé
			if (in_array($nick, $team['nicks']))
			{
				// On supprime le joueur de l'équipe
				foreach ($team['nicks'] as $nick_key => $nick_member)
				{
					if ($nick == $nick_member)
					{
						unset($this->teams[$team_key]['nicks'][$nick_key]);

						if ( ! $forced)
							$this->send_notice($nick, "[Quizz - équipe] Tu as bien quitté l'équipe ".$this->bold($team['name']));

						else
						{
							$this->send_notice($nick, "[Quizz - équipe] Tu as été retiré de l'équipe ".$this->bold($team['name']).' par un administrateur');
							$this->send_notice($this->buffer('nick'), '[Quizz - équipes] Tu as bien retiré '.$this->bold($nick)." de l'équipe ".$this->bold($team['name']));
						}

						// On détruit l'équipe si plus personne
						if (count($this->teams[$team_key]['nicks']) == 0)
							unset($this->teams[$team_key]);
					}
				}
			}

		}
	}

	public function read_questions()
	{
		if ( ! $this->connect_bdd())
			return 'connexion bdd';

		$requete = mysql_query('SELECT valeur '.
							   'FROM bot_irc '.
							   'WHERE module = "quizz" AND cle="questions"');
		$config = mysql_fetch_object($requete);
		
		$this->questions = array();
		$file = explode("\n", $config->valeur);
		$i = 0;

		while (isset($file[$i], $file[$i + 1], $file[$i + 2]))
		{
			// Verify that 3rd line contains points
			if ( ! is_numeric($file[$i + 2]))
			{
				$this->questions = array();
				return "ligne $i";
			}
			
			$question           = new StdClass;
			$question->question = trim($file[$i]);
			$question->answer   = trim($file[$i + 1]);
			$question->points   = (int)$file[$i + 2];

			$this->questions[] = $question;
			$i += 4;
		}

		return true;
	}
	
	public function start_quizz()
	{
		// Init vars
		if (($erreur = $this->read_questions()) !== true)
		{
			$this->send_chan("[Quizz] Erreur dans les questions ($erreur)");
			return false;
		}
		
		$this->question_running = false;
		$this->num_question     = 0;
		$this->scores           = array();
		$this->paused           = false;
		$this->paused_admin     = false;

		// Enable quizz
		$this->enable();
		$this->next_question();

		return true;
	}

	public function stop_quizz($send_scores = true)
	{
		// Disable quizz
		$this->disable();
		$this->question_running = false;
		$this->num_question     = 0;
		$this->paused           = false;
		$this->paused_admin     = false;
		$this->teams_on         = false;

		// On supprime tous les bans
		foreach ($this->chut_bans as $nick => $ban_time)
		{
			$this->command('MODE '.$this->bot->config('chan').' -b ~q:'.$nick);
			$this->command('MODE '.$this->bot->config('chan').' -b ~n:'.$nick);
		}

		$this->chut_bans = array();

		// On vide les mauvaises réponses
		$this->bad_answers = array();

		$this->moderate(false);
		
		if ($send_scores)
		{
			// Send scores
			$this->send_scores();
			$this->send_teams();
			$this->send_chan('[Quizz] Le quizz est terminé ! A la prochaine bande de pochtrons !', array('color' => 'pourpre', 'bold' => true));
		}

		$this->teams  = array();
	}
	
	public function next_question()
	{
		// If question is running, change question
		if ($this->question_running)
		{
			$this->num_question++;
			$this->question_running = false;
		}

		$this->last_time = time();
		
		// End of quizz, no more questions
		if ($this->num_question >= count($this->questions))
			$this->stop_quizz();

		// Pause time
		else if ($this->num_question > 0 && $this->num_question % ($this->config['nb_questions_pause']) == 0)
		{
			$this->paused = true;
			$this->send_chan('[Quizz] Pause de '.$this->pluriel($this->config['pause_duration'], 'minute').", qu'on m'apporte un café !", array('color' => 'pourpre', 'bold' => true));
			usleep(3000000);
			$this->send_scores();
			$this->send_teams();
		}

		// Next question in some seconds
		else
		{
			$nb = count($this->questions) - $this->num_question;
			$this->send_chan('[Quizz] Prochaine question dans '.$this->config['delay_between_questions'].' secondes (plus que '.$this->pluriel($nb, 'question').')', array('color' => 'pourpre', 'bold' => true));
			$this->moderate(true);
		}
	}

	public function moderate($moderate)
	{
		$m = $moderate ? '+m' : '-m';
		$this->command('MODE '.$this->bot->config('chan').' '.$m);
	}

	public function add_points($points, $nick)
	{
		if ($nick == $this->bot->config('nick'))
			return;
		
		// On regarde si le mec est dans une équipe
		foreach ($this->teams as $team)
		{
			if (in_array($nick, $team['nicks']))
			{
				$nick = $team['name'];
				break;
			}
		}

		if (isset($this->scores[$nick]))
			$this->scores[$nick] += $points;

		else
			$this->scores[$nick] = $points;

		// Scores sorting every time
		arsort($this->scores);
	}

	public function good_answer($text, $question)
	{
		// Numbers have to be exactly matched
		if (is_numeric($question->answer))
			return preg_match('#^(.* +)?'.preg_quote($question->answer).'( +.*)?$#', $text);

		return preg_match('#'.preg_quote($question->answer).'#i', $text);
	}
	
	public function send_question()
	{
		// Init vars
		$this->last_time = time();
		$this->question_running = true;

		// Send question
		$question = $this->questions[$this->num_question];
		$num_question = $this->num_question + 1;
		$this->moderate(false);
		$this->send_chan('[Question '.$num_question.'] '.$question->question.' ('.$this->pluriel($question->points, 'point').')', array('color' => 'bleu', 'bold' => true));
	}

	public function send_rules()
	{
		// On a besoin du nombre de questions
		if ( ! $this->read_questions())
		{
			$this->send_chan("[Quizz] Erreur dans les questions");
			return;
		}
		
		$this->moderate(true);
		$this->send_chan('---------- [ Bienvenue sur le Quizz Bouzouks ] ----------', array('color' => 'pourpre', 'bold' => true));

		$duree_quizz = (count($this->questions) * $this->config['delay_between_questions'] + (ceil(1.0 * count($this->questions) / $this->config['nb_questions_pause'])) * $this->config['pause_duration'] * 60) / 60;
		
		$rules = array(
			"Le chan est modéré (+m) et tu ne peux pas parler c'est normal",
			"Le Quizz comporte ".count($this->questions)." questions et a une durée d'environ $duree_quizz minutes",
			'Chaque question rapporte des points différents (indiqués à la fin de la question)',
			'Le premier qui trouve la réponse remporte les points',
			"Trop de réponses à la suite = kick, 3 mauvaises réponses = muté pendant 1min30",
			"Certaines réponses comportent des accents ou des tirets, bref, attention à l'orthographe",
			'Chaque question dure '.$this->pluriel($this->config['question_duration'], 'seconde'),
			'Il y aura une pause de '.$this->pluriel($this->config['pause_duration'], 'minute').' toutes les '.$this->pluriel($this->config['nb_questions_pause'], 'question'),
			'A chaque pause les scores sont affichés',
			"Toute tentative de tricherie ou d'entrave au quizz sera sanctionnée d'un ban",
			'Tape "!quizz join Mon équipe" ou "!quizz join Mon équipe/mot_de_passe" pour créer/rejoindre une équipe',
			'Tape "!quizz leave" pour quitter ton équipe',
			"Un administrateur peut supprimer un joueur d'une équipe",
		);

		$i = 1;
		
		foreach($rules as $rule)
		{
			usleep($this->config['delay_between_rules'] * 1000000);
			$this->send_chan($i.'. '.$rule, array('color' => 'pourpre'));
			$i++;
		}

		usleep($this->config['delay_between_rules'] * 1000000);
		$this->send_chan('---------- Fin des règles, lancement du quizz imminent...des questions ? ----------', array('color' => 'pourpre', 'bold' => true));
		$this->moderate(false);
	}

	public function send_scores()
	{
		$this->moderate(true);
		$this->send_chan('[Quizz] ----- Voici les scores actuels -----', array('color' => 'marron', 'bold' => true));

		$i = 1;
		
		foreach ($this->scores as $nick => $score)
		{
			foreach ($this->teams as $team)
			{
				if ($nick == $team['name'])
				{
					$nick = $this->color($nick, 'bleu', 'marron');
					break;
				}
			}

			$this->send_chan("      $i. ".$this->bold($nick)." : ".$this->bold($this->pluriel($score, 'point')), array('color' => 'marron'));
			$i++;
			usleep($this->config['delay_between_scores'] * 1000000);
		}

		$this->send_chan('[Quizz] ----- Fin des scores -----', array('color' => 'marron', 'bold' => true));
		$this->moderate(false);
	}

	public function send_teams()
	{
		$this->moderate(true);
		$this->send_chan('[Quizz] ----- Voici les équipes actuelles -----', array('color' => 'bleu', 'bold' => true));

		foreach ($this->teams as $team)
		{
			$nicks = implode(', ', $team['nicks']);
			$this->send_chan('[Quizz] '.$this->bold($team['name']).' : '.$nicks, array('color' => 'bleu'));
		}

		$this->send_chan('[Quizz] ----- Fin des équipes -----', array('color' => 'bleu', 'bold' => true));
		$this->moderate(false);
	}

	private function chut($nick)
	{
		if ( ! isset($this->chut_bans[$nick]))
		{
			$this->chut_bans[$nick] = time();
			unset($this->bad_answers[$nick]);
			$this->command('MODE '.$this->bot->config('chan').' +b ~q:'.$nick);
			$this->command('MODE '.$this->bot->config('chan').' +b ~n:'.$nick);
		}
	}

	private function verify_chut_bans()
	{
		$time = time();

		foreach ($this->chut_bans as $nick => $ban_time)
		{
			if ($time - $ban_time >= 90)
			{
				$this->command('MODE '.$this->bot->config('chan').' -b ~q:'.$nick);
				$this->command('MODE '.$this->bot->config('chan').' -b ~n:'.$nick);
				unset($this->chut_bans[$nick]);
			}
		}
	}
}
