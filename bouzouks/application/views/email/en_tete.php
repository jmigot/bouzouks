<?php

$heure = date('H');

if ($heure >= 5 AND $heure <= 17)
{
	echo 'Bonjour '.$pseudo.',';
}

else
{
	echo 'Bonsoir '.$pseudo.',';
}
