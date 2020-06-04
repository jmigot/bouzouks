<?php $this->load->view('email/en_tete'); ?>


Merci de t'être inscrit sur Bouzouks.net, le 1er jeu de simulation de vie bouzouk.

Pour confirmer ton inscription, clique sur ce lien ou copie-colle le dans la barre d'adresse de ton navigateur :
{unwrap}<?= site_url('visiteur/inscription_confirmation').'?pseudo='.$pseudo.'&code='.$code_aleatoire ?>{/unwrap}

Si le lien ci-dessus ne fonctionne pas, rends-toi sur {unwrap}<?= site_url() ?>{/unwrap} puis, dans le menu de gauche, clique sur "Confirmation".
Entre ton pseudo : "<?= $pseudo ?>" et ton code de validation : "<?= $code_aleatoire ?>"

Attention : dès que tu aura confirmé ton inscription, ta partie sera active et sera soumise aux règles du jeu.


<?php $this->load->view('email/signature'); ?>
