<?php $this->layout->set_title('FAQ - La Pause'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - La Pause</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>

			<p class="margin">
				Si tu ne peux pas t'occuper de ton bouzouk pendant un certain temps, mettre ton compte en pause permet de <span class="pourpre">&laquo; geler &raquo;</span> le jeu pendant une
				longue durée. Pour ce faire, tu dois cliquer sur le bouton <span class="pourpre">Mettre en pause</span> situé sur la page <a href="<?= site_url('mon_compte') ?>">Mon compte</a>.
			</p>

	        <p class="margin">
				La pause durera minimum <span class="pourpre">2 jours</span> et au maximum <span class="pourpre"><?= $this->bouzouk->config('maintenance_delai_pause_to_game_over') ?> jours</span> : passé ce délai, le compte passera en Game Over. En mode pause tu ne pourras pas jouer, les stats de ton bouzouk
				ne bougeront pas, tu ne toucheras pas de salaire, il sera impossible de voter ou d'envoyer des missive, etc. <span class="pourpre">La péremption des objets ne bouge pas pendant la pause.</span>
	        </p>

			<p class="margin">
				Par contre il n'est pas possible d'échapper aux factures qui seront dans ta boîte a missives à ton retour. Pour revenir dans le jeu il te suffira de te connecter et de cliquer
				sur <span class="pourpre">Reprendre ma partie</span>, après au moins <span class="pourpre">2 jours</span> d'attente.
			</p>

			<p class="highlight">Qu'est-ce qu'il se passe si je suis patron ?</p>
			<p class="margin">
				Ton entreprise continue a tourner avec les derniers paramètres que tu auras modifié mais tant que tu n'as pas repris ta partie tu ne pourras plus gérer ta boîte.
			</p>

			<p class="highlight">Qu'est-ce qu'il se passe si je suis maire ou candidat aux élections ?</p>
			<p class="margin">
				Si tu es maire, tu es destitué de tes fonctions (on ne veut pas d'un fou pour gérer la ville !) et c'est ton suppléant qui prend le relais.
				Si tu es candidat aux élections, tu es supprimé de la liste quelque que soit le nombre de concurrents.
			</p>
		</div>
	</div>
</div>
