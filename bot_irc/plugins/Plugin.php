<?php

abstract class Plugin
{
	protected $bot;
	protected $enabled;
	private $bold;
	private $color;
	private $multicolor;
	protected $colors = array(
	    'blanc'   => '00',
	    'noir'    => '01',
	    'bleu'    => '02',
	    'vert'    => '03',
	    'rouge'   => '04',
	    'marron'  => '05',
	    'pourpre' => '06',
	    'orange'  => '07',
	    'jaune'   => '08',
	    'cyan'    => '10',
		'rose'    => '13',
		'gris'    => '14',
	);

	protected function __construct($bot)
	{
		$this->bot   =& $bot;
		$this->set_color('bleu');
		$this->bold = false;
		$this->enable();
	}

	abstract protected function run();

	protected function enable()
	{
		$this->enabled = true;
	}

	protected function disable()
	{
		$this->enabled = false;
	}

	protected function toggle_bold()
	{
		$this->bold = ! $this->bold;
		return $this->bold;
	}
	
	protected function color($text, $color, $old_color)
	{
		return chr(3).$this->colors[$color].$text.chr(3).$this->colors[$old_color];
	}

	protected function set_color($key)
	{
		$this->multicolor = ($key == 'arc-en-ciel');
					
		if (isset($this->colors[$key]))
		{
			$this->color = chr(3).$this->colors[$key];
			return true;
		}

		else if ($key == 'arc-en-ciel')
			return true;

		return false;
	}
	
	protected function bold($text)
	{
		return chr(2).$text.chr(2);
	}

	protected function buffer($key = null)
	{
		return $this->bot->buffer($key);
	}
	
	protected function send_chan($text, $options = array(), $bot = null)
	{
		if ( ! isset($bot))
			$bot = $this->bot->config('nick');

		// Options
		$options['color'] = isset($options['color']) ? chr(3).$this->colors[$options['color']] : $this->color;
		$options['bold'] = isset($options['bold']) || $this->bold ? chr(2) : '';

		// Chan
		$chan = $this->bot->config('chan');

		// /me
		if (mb_substr($text, 0, 4) == '/me ')
		{
			// J.F Sébastien parle
			if ($bot != $this->bot->config('nick'))
			{
				$text = str_replace('/me ', '', $text);
				$this->bot->write("BOTSERV ACT $chan $text");
			}

			// Bouzouk parle
			else
			{
				$text = str_replace('/me ', 'ACTION ', $text);
				$text = chr(1).$text.chr(1);
				$this->bot->write("PRIVMSG $chan :$text");
			}
		}

		// Normal text
		else
		{
			if ($this->multicolor)
			{
				$options['color'] = '';
				$colors = array_keys($this->colors);
				$nb_colors = count($colors);
				$text_tmp = '';

				for ($i = 0; $i < mb_strlen($text); $i++)
					$text_tmp .= chr(3).$this->colors[$colors[$i % ($nb_colors - 1) + 1]].mb_substr($text, $i, 1);

				$text = $text_tmp;
			}
			
			// Commandes
			if (preg_match('#^!(.+)#', $text))
				$this->bot->write("PRIVMSG $chan :$text");

			else
			{
				// J.F Sébastien parle
				if ($bot != $this->bot->config('nick'))
					$this->bot->write("BOTSERV SAY $chan ".$options['bold'].$options['color'].$text);
				
				else
					$this->bot->write("PRIVMSG $chan :".$options['bold'].$options['color'].$text);
			}
		}

		$Logs = $this->bot->plugin('Logs');
		$Logs->log_privmsg($chan, $this->bot->config('nick'), $text);
	}

	protected function send_notice($nick, $text)
	{
		$this->bot->write("NOTICE $nick :$text");
	}

	protected function send_prive($nick, $text)
	{
		$this->bot->write("PRIVMSG $nick :$text");
	}

	protected function kick($nick, $reason)
	{
		$chan = $this->bot->config('chan');
		$this->bot->write("KICK $chan $nick :$reason");
	}

	protected function ban($nick)
	{
		$chan = $this->bot->config('chan');
		$this->bot->write("MODE $chan +b $nick");
	}

	protected function unban($nick)
	{
		$chan = $this->bot->config('chan');
		$this->bot->write("MODE $chan -b $nick");
	}

	protected function command($command)
	{
		$this->bot->write($command);
	}

	protected function is_op($nick = null)
	{
		if ( ! isset($nick))
			$nick = $this->buffer('nick');
			
		return $this->is_mode('@', $nick);
	}

	protected function is_hop($nick = null)
	{
		if ( ! isset($nick))
			$nick = $this->buffer('nick');
			
		return $this->is_mode('%@', $nick);
	}
	
	protected function is_voice($nick = null)
	{
		if ( ! isset($nick))
			$nick = $this->buffer('nick');

		return $this->is_mode('+%@', $nick);
	}

	protected function is_mode($modes, $nick)
	{
		$liste_nicks = $this->liste_nicks();

		for ($i = 0 ; $i < mb_strlen($modes) ; $i++)
			if (strpos($liste_nicks[$nick], $modes[$i]) !== false)
				return true;

		return false;
	}
	
	protected function liste_nicks()
	{
		// On récupère la liste des connectés
		$connectes = file_get_contents('http://www.powanet.org/powanet.php?a=222');

		if ($connectes === false)
			return array();

		$connectes = explode('<br>', $connectes);

		$modes_possibles = array('&', '~', '@', '%', '+');
		$nicks = array();

		foreach ($connectes as $nick)
		{
			// On récupère les modes du nick
			$modes = '';

			while (in_array($nick[0], $modes_possibles))
			{
				$modes .= $nick[0];
				$nick = mb_substr($nick, 1);
			}

			$nicks[$nick] = $modes;
		}

		return $nicks;
	}

	protected function is_present($chain)
	{
		return preg_match('#'.$chain.'#i', $this->buffer('text'));
	}

	protected function is_required($chain)
	{
		return $chain == $this->buffer('text');
	}

	protected function give_required($chain, &$out)
	{
		return preg_match('#^'.$chain.'$#', $this->buffer('text'), $out);
	}
	
	protected function connect_bdd()
	{
		$return = @mysql_connect('localhost', 'bouzouks', 'htY1jVgY');

		if ($return !== false)
			$return = @mysql_select_db('bouzouks');

		@mysql_query("SET NAMES 'utf8'");
		return $return;
	}

	protected function close_bdd()
	{
		@mysql_close();
	}

	protected function pluriel($nb, $singulier)
	{
		$pluriel = $singulier.'s';

		if ($nb >= -1 && $nb <= 1)
			return $nb.' '.$singulier;

		return $nb.' '.$pluriel;
	}
}
