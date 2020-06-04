<?php $this->load->view('email/en_tete'); ?>


Tu reçois ce mail suite à ta demande de changement de mot de passe <?= $date ?>.

Si tu n'as fait aucune demande, ignore ce mail, tu pourras toujours utiliser ton mot de passe actuel.

Dans le cas contraire, clique sur ce lien ou copie-colle le dans la barre d'adresse de ton navigateur :
{unwrap}<?= site_url('visiteur/pass_perdu_confirmation').'?pseudo='.$pseudo.'&code='.$code_aleatoire ?>{/unwrap}

Si le lien ci-dessus ne fonctionne pas, rends-toi sur {unwrap}<?= site_url() ?>{/unwrap} puis, dans le menu de gauche, clique sur "Mot de passe perdu",
puis sur "Changer de mot de passe avec un code reçu".
Entre ton pseudo : "<?= $pseudo ?>", ton code de validation : "<?= $code_aleatoire ?>" et choisis ton nouveau mot de passe.

<?php $this->load->view('email/signature'); ?>