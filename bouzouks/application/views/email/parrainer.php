<?php

$heure = date('H');

if ($heure >= 5 AND $heure <= 17)
	echo 'Bonjour, ';

else
	echo 'Bonsoir, ';
?>

<?= $pseudo ?> t'invite à venir jouer avec lui et toute la communauté de Bouzouks.net !
Viens rejoindre un monde délirant où tu pourras devenir patron, être endetté par les taxes et jeux d'argent, exploiter malhonnêtement d'autres bouzouks, être à la tête d'un groupe d'illuminés ou encore devenir le maire de la ville !

Pour t'inscrire, clique sur ce lien (ou copie/colle le dans la barre d'adresse) :
{unwrap}<?= site_url('visiteur/inscription/'.urlencode($pseudo)) ?>{/unwrap}

Ce lien va renseigner <?= $pseudo ?> comme étant ton parrain, ce qui lui fera gagner un objet rare exclusif une fois que tu auras commencé à jouer :)

<?php $this->load->view('email/signature'); ?>
