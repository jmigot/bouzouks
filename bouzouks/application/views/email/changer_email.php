<?php $this->load->view('email/en_tete'); ?>


Tu reçois ce mail suite à ta demande de changement d'adresse email le <?= $date ?>.

Si tu n'as fait aucune demande, ignore ce mail, ton adresse email actuelle sera toujours valide.

Dans le cas contraire, clique sur ce lien ou copie-colle le dans la barre d'adresse de ton navigateur :
{unwrap}<?= site_url('mon_compte/changer_email_confirmation').'?code='.$code_aleatoire ?>{/unwrap}

Si le lien ci-dessus ne fonctionne pas, rends-toi sur {unwrap}<?= site_url() ?>{/unwrap}, connecte toi puis, dans le menu de gauche, clique sur "Mon compte",
puis sur "Changer d'email avec un code reçu".
Entre ton code de validation : "<?= $code_aleatoire ?>".

<?php $this->load->view('email/signature'); ?>