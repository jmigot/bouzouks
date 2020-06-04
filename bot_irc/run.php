<?php

// Max execution time is unlimited
set_time_limit(0);

// Encoding is UTF-8
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

require_once 'Bot.php';
require_once 'plugins/Plugin.php';

// Output errors
function error($file, $line, $text)
{
	echo "[ERROR] $text [$file:$line]\n";
	exit;
}

// Bot config
$config = array(
	'server'         => 'irc.powanet.org',
	'port'           => '6667',
	'chan'           => '#bouzouks',
	'nick'           => 'Bouzouk',
	'realname'       => 'Bouzouks.net IRC Bot',
	'password'       => 'xxx',
	'socket_timeout' => 30,
	'quit_message'   => 'je reviendrais',
	'usleep'         => 50000,
	'plugins'        => array(0 => 'Logs', 1 => 'Animation', 2 => 'Quizz'),
	'at_connect'     => 'PART #powanet',
	'log'            => false // 'file', 'output' or false
);

// Write pid in file
file_put_contents('mypid', getmypid());

$Bot = new Bot($config);
$Bot->run();
