<?php $this->layout->set_title('FAQ - Le Game Over'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Le Game Over</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>
			<p class="margin">
				Si ta faim et ta santé tombent toutes les deux à <span class="pourpre">0%</span>, c'est la fin de la partie...Le Game Over !
			</p>

	        <p class="margin">
				Avoir <span class="pourpre">0%</span> en faim et en santé est la seule raison pour un Game Over. Mais il est facile d'oublier de s'occuper de son bouzouk. Si tu es proche du Game Over, un mail te sera
				envoyé pour t'en avertir.
	        </p>

			<p class="highlight">Que se passe-t-il en cas de Game Over ?</p>
			<p class="margin">
				Ta partie est finie, toute ta progression dans le jeu sera effacée. Tu auras la possibilité de recommencer le jeu mais tu repartiras du début avec <span class="pourpre">50% de l'expérience que tu avais</span>
				Si tu as fait des dons au site tu peux repartir avec <span class="pourpre">100% de ton ancienne expérience</span> (contacter un administrateur).
			</p>

			<p class="margin italique pourpre">
				Les comptes en game over depuis <?= $this->bouzouk->config('maintenance_delai_suppression_game_over') ?> jours seront supprimés totalement du site.
			</p>
		</div>
	</div>
</div>
