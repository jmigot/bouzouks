<?php

class Bot
{
	private $config;
	private $socket;
	private $buffer;
	private $plugins;

	public function __construct($config)
	{
		// Save the config
		$this->config = $config;
		$this->buffer = '';
		
		// Load plugins
		foreach ($this->config('plugins') as $plugin)
			$this->load_plugin($plugin);
		
		// Connect the bot
		$this->connect();
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	public function load_plugin($plugin)
	{
		$file = "plugins/$plugin.php";
		
		if ( ! is_file($file))
			error("$file is not readable");

		// Add plugin to plugins list
		require_once $file;
		$this->plugins[$plugin] = new $plugin($this);
	}
	
	public function config($key)
	{
		if ( ! isset($this->config[$key]))
			error(__FILE__, __LINE__, "'$key' is not a valid key");

		return $this->config[$key];
	}

	public function buffer($key)
	{
		if ( ! isset($key))
			return $this->buffer;
			
		if ( ! isset($this->buffer[$key]))
			error(__FILE__, __LINE__, "'$key' is not a valid key");

		return $this->buffer[$key];
	}

	public function plugin($key)
	{
		if ( ! isset($key))
			return null;

		if ( ! isset($this->plugins[$key]))
			error(__FILE__, __LINE__, "'$key' is not a valid key");

		return $this->plugins[$key];
	}
	
	public function connect()
	{
		// Connexion to IRC
		$this->socket = fsockopen($this->config('server'), $this->config('port'), $errno, $errstr, $this->config('socket_timeout'));

		if ( ! $this->socket)
			error(__FILE__, __LINE__, "fsockopen() error: $errstr. ($errno)");

		// Set non-blocking mode
		if ( ! stream_set_blocking($this->socket, 0))
			error(__FILE__, __LINE__, 'stream_set_blocking() error');
		
		// NICK, USER
		$this->write('NICK '.$this->config('nick'));
		$this->write('USER '.$this->config('nick').' host server :'.$this->config('realname'));

		// PROTOCTL NAMESX will make NAME return user modes in front of their nicks
		$this->write('PROTOCTL NAMESX');

		// We wait for Message Of The Day (synonym of connected)
		while (true)
		{
			$this->buffer = $this->parse($this->read());

			if ($this->buffer !== false)
			{
				// PING
				if ($this->buffer('special') == 'PING')
					$this->write('PONG '.$this->buffer('text'));
					
				// End of MOTD
				else if ($this->buffer('event') == 376)
				{
					// Identify the nick
					if ($this->config('password') != '')
						$this->write('NICKSERV IDENTIFY '.$this->config('password'));
					$this->write('MODE '.$this->config('nick').' +x');

					// Join chan
					$this->join_chan();

					// Personnal user command at connect
					$this->write($this->config('at_connect'));
					break;
				}
			}

			usleep($this->config('usleep'));
		}
	}

	public function read()
	{
		// We set the socket timeout
		stream_set_timeout($this->socket, $this->config('socket_timeout'));

		// We read data from the socket
		$buffer = fgets($this->socket);

		// We check for 'timed_out' or 'eof'
		$meta_data = stream_get_meta_data($this->socket);

		if ($meta_data['timed_out'] || $meta_data['eof'])
		{
			$this->disconnect();
			$this->connect();
		}

		if ($buffer != '')
			$this->log("<-- $buffer");
			
		return rtrim($buffer, "\r\n");
	}

	public function write($buffer)
	{
		// TODO : gérer les 510 caractères max par buffer
		
		$this->log("--> $buffer\n");

		// We write data into the socket
		if (fwrite($this->socket, $buffer."\r\n") === false)
			error(__FILE__, __LINE__, 'fwrite() error');

		// We flush buffer into socket
		fflush($this->socket);
	}

	public function disconnect()
	{
		if (is_resource($this->socket))
		{
			$this->write('QUIT :'.$this->config('quit_message'));
			
			if ( ! fclose($this->socket))
				error(__FILE__, __LINE__, 'fclose() error');
		}
	}
	
	public function run()
	{
		while (true)
		{
			$this->buffer = $this->parse($this->read());

			if ($this->buffer !== false)
			{
				// We treat some special commands
				if ($this->buffer('special') == 'PING')
					$this->write('PONG '.$this->buffer('text'));

				else if($this->buffer('special') == 'ERROR')
					error(__FILE__, __LINE__, $this->buffer('full'));
			}

			// We run plugins
			foreach ($this->plugins as $plugin)
			{
				if (isset($plugin))
					$plugin->run();
			}
			
			usleep($this->config('usleep'));
		}
	}

	public function parse($buffer)
	{
		if ($buffer == '')
			return false;

		//          1                            23    4    5        6            7               8
		$regex = '^(NOTICE AUTH|PING|ERROR)?(?:^:((.*)!(.*)@(.*)|.*) (\d*|\S*)(?: ([^:]*))?)?(?: :(.*?))?$';
		
		if (preg_match('#'.$regex.'#Ui', $buffer, $matches))
		{
			return array(
				'full'       => isset($matches[0]) ? $matches[0] : '',
				'special'    => isset($matches[1]) ? $matches[1] : '',
				'ident'      => isset($matches[2]) ? $matches[2] : '',
				'nick'       => isset($matches[3]) ? $matches[3] : '',
				'realname'   => isset($matches[4]) ? $matches[4] : '',
				'host'       => isset($matches[5]) ? $matches[5] : '',
				'event'      => isset($matches[6]) ? strtoupper($matches[6]) : '',
				'params'     => isset($matches[7]) ? explode(' ', $matches[7]) : '',
				'text'       => isset($matches[8]) ? $matches[8] : ''
			);
		}

		return false;
	}

	public function join_chan()
	{
		$this->write('JOIN '.$this->config('chan'));
	}

	public function log($text)
	{
		// UTF-8 encode
		if (mb_detect_encoding($text, 'UTF-8', true) === false)
			$text = utf8_encode($text);
			
		if ($this->config('log') === false)
			return;

		if ($this->config('log') == 'output')
		{
			echo $text;
		}

		else if ($this->config('log') == 'file')
		{
			$file = fopen('logs/'.date('Y-m-d'), 'a');

			if ($file === false)
				return;

			if (fwrite($file, '['.date('H\hi').'] '.$text) === false)
				return;

			if ( ! fclose($file) === false)
				return;
		}
	}
}