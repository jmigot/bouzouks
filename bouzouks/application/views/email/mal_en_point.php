<?php $this->load->view('email/en_tete'); ?>


Aïe, aïe, aïe ! Avec <?= $faim ?>% de faim, <?= $sante ?>% de santé et <?= $stress ?>% de stress, tu es en très mauvaise posture et risques de perdre ta partie d'un moment à l'autre.
Rendez-vous le plus tôt possible sur {unwrap}<?= site_url() ?>{/unwrap} pour remédier à cela.

<?php $this->load->view('email/signature'); ?>
