<?php

class Logs extends Plugin
{
	private $log_html = true;
	private $text;
	private $html;
	
	public function __construct($bot)
	{
		parent::__construct($bot);

		$text = '----- Connexion du bot à '.$this->bot->config('chan').' -----';
		$html = '';
		$this->write_log($text);
		$this->write_log_html($html);
	}
	
	public function run()
	{
		if ($this->buffer() === false)
			return;

		$this->text = '';
		$this->html = '';
		
		switch ($this->buffer('event'))
		{
			case 'JOIN':
				$this->log_join($this->buffer('text'), $this->buffer('nick'), $this->buffer('ident'));
				break;
			
			case 'QUIT':
				$this->log_quit($this->buffer('nick'), $this->buffer('text'));
				break;

			case 'NICK':
				$this->log_nick($this->buffer('nick'), $this->buffer('text'));
				break;

			case 'PART':
				$params = $this->buffer('params');
				$this->log_part($params[0], $this->buffer('nick'), $this->buffer('text'));
				break;

			case 'PRIVMSG':
				$params = $this->buffer('params');
				$this->log_privmsg($params[0], $this->buffer('nick'), $this->buffer('text'));
				break;

			case 'KICK':
				$params = $this->buffer('params');
				$this->log_kick($params[0], $this->buffer('nick'), $params[1], $this->buffer('text'));
				break;

			case 'MODE':
				$params = $this->buffer('params');
				$chan = $params[0];
				array_shift($params);
				$params = implode(' ', $params);
				$this->log_mode($chan, $this->buffer('nick'), $params);
				break;
		}
	}

	public function log_join($chan, $nick, $ident)
	{
		if ($chan == $this->bot->config('chan'))
		{
			$this->text = "* $nick ($ident) a rejoint $chan";

			if ($this->log_html)
				$this->html = "<td class='join'>*</td><td class='join'><span class='gras'>$nick</span> ($ident) a rejoint $chan</td>";

			$this->write();
		}
	}

	public function log_quit($nick, $reason)
	{
		$this->text = "* $nick est parti ($reason)";

		if ($this->log_html)
			$this->html = "<td class='quit'>*</td><td class='quit'>$nick est parti ($reason)</td>";

		$this->write();
	}

	public function log_nick($nick, $new_nick)
	{
		$this->text = "* $nick s'appelle maintenant $new_nick";

		if ($this->log_html)
			$this->html = "<td class='nick'>*</td><td class='nick'>$nick s'appelle maintenant $new_nick</td>";

		$this->write();
	}

	public function log_part($chan, $nick, $reason)
	{
		if ($chan == $this->bot->config('chan'))
		{
			$this->text = "* $nick a quitté $chan ($reason)";

			if ($this->log_html)
				$this->html = "<td class='part'>*</td><td class='part'>$nick a quitté $chan ($reason)</td>";

			$this->write();
		}
	}

	public function log_privmsg($chan, $nick, $text)
	{
		if ($chan == $this->bot->config('chan'))
		{
			// /me managment
			if (preg_match('#'.chr(1).'ACTION (.*)'.chr(1).'#U', $text, $matches))
			{
				$text = $matches[1];
				
				$this->text = "* $nick $text";

				if ($this->log_html)
					$this->html = "<td class='me'>*</td><td class='me'><span class='me-nick'>$nick</span> $text</td>";
			}

			else
			{
				// Color chars
				$text = preg_replace('#'.chr(3).'(1[0-5]|0?[0-9])(,(1[0-5]|0?[0-9]))?#', '', $text);

				// Bold, underline, color, invert, normal text chars
				$text = preg_replace('#'.chr(2).'|'.chr(37).'|'.chr(3).'|'.chr(26).'|'.chr(15).'|'.chr(17).'#', '', $text);

				$this->text = "$nick $text";
				
				if ($this->log_html)
					$this->html = "<td class='privmsg'>$nick</td><td class='privmsg'>$text</td>";		
			}

			$this->write();
		}
	}

	public function log_kick($chan, $nick, $nick_kicked, $reason)
	{
		if ($chan == $this->bot->config('chan'))
		{
			$this->text = "* $nick a expulsé $nick_kicked de $chan ($reason)";

			if ($this->log_html)
				$this->html = "<td class='kick'>*</td><td class='kick'>$nick a expulsé $nick_kicked de $chan ($reason)</td>";

			$this->write();
		}
	}

	public function log_mode($chan, $nick, $params)
	{
		if ($chan == $this->bot->config('chan'))
		{
			$this->text = "* $nick active le mode $params $chan";

			if ($this->log_html)
				$this->html = "<td class='mode'>*</td><td class='mode'>$nick active le mode $params $chan</td>";

			$this->write();
		}
	}

	private function write()
	{
		// Write to log file
		if ($this->text != '')
			$this->write_log($this->text);

		if ($this->log_html && $this->html != '')
			$this->write_log_html($this->html);
	}
	
	private function write_log($text)
	{
		// UTF-8 encode
		if (mb_detect_encoding($text, 'UTF-8', true) === false)
			$text = utf8_encode($text);

		// Open log file
		$file = fopen('plugins/logs/'.date('Y-m-d'), 'a');

		if ($file === false)
			return;

		// Write log file
		if (fwrite($file, '['.date('H\hi').'] '.$text."\n") === false)
			return;

		// Close log file
		if ( ! fclose($file) === false)
			return;
	}

	private function write_log_html($html)
	{		
		// UTF-8 encode
		if (mb_detect_encoding($html, 'UTF-8', true) === false)
			$html = utf8_encode($html);

		// Open log file
		$file = fopen('plugins/logs/html/'.date('Y-m-d'), 'a');

		if ($file === false)
			return;

		// Write log file
		if (fwrite($file, '<tr><td>['.date('H\hi').']</td>'.$html."</tr>\n") === false)
			return;

		// Close log file
		if ( ! fclose($file) === false)
			return;
	}
}
