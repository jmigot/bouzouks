<?php $this->layout->set_title('FAQ - L\'Asile'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - L'Asile</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>

			<p class="margin">
				Entre le boulot, les taxes et le MLB toujours à faire des graffitis sur ta trompe, difficile de ne pas être stressé ! Ce stress est représenté par un liquide rouge qui
				bouillonne et monte au fur et à mesure des journées; si ce liquide arrive à <span class="pourpre">100%</span> c'est la folie qui s'empare de ton pauvre bouzouk et celui-ci se
				retrouve à l'asile <span class="pourpre">La Maison du bonheur</span>.<br><br>

				Il est aussi possible d'arriver à l'asile suite à une décision d'un modérateur ou d'un administrateur.
			</p>

	        <p class="margin">
				Quand tu es en asile psychiatrique, tu n'as plus accès à aucune fonction du jeu, tu peux seulement discuter avec les autres aliénés de l'asile devant la machine a café
				(ben oui on est pas si cruel on offre quand même le café !) et ceci pour une durée de 2 jours.<br>
				Passés les 2 jours de rétention tu auras accès à un bouton <span class="pourpre">sortir de l'asile</span> qui te permettra de reprendre le mode de jeu normal.<br><br>

				<span class="pourpre">Attention : si tu ne sors pas de l'asile <?= $this->bouzouk->config('maintenance_delai_asile_to_game_over') ?> jours après y être entré, ton compte passera en game over. Tu peux toujours mettre ton compte en pause quand
				tu es à l'asile si tu ne peux plus t'occuper de ton bouzouk pendant un certain temps.</span>
	        </p>

			<p class="highlight">Est-ce que je perds de l'expérience en allant à l'asile ?</p>
			<p class="margin">
				Oui, aller à l'asile à cause de la jauge de stress fait perdre <span class="pourpre">-<?= $this->bouzouk->config('joueur_perte_xp_asile') ?> xp</span>, mais se faire envoyer à
				l'asile par un modérateur ou un administrateur fait perdre <span class="pourpre">-<?= $this->bouzouk->config('joueur_perte_xp_asile_moderation') ?> xp</span>.<br><br>

				De plus, être à l'asile bloque l'augmentation d'xp lors de la maintenance tous les jours.
			</p>
			
			<p class="highlight">Suis-je toujours payé quand je suis à l'asile ?</p>
			<p class="margin">
				Non, comme pour la <a href="<?= site_url('site/faq/pause') ?>">pause</a>, pendant toute la durée de détention tu n'es pas payé. Par contre tu continues à recevoir des factures.
			</p>

			<p class="highlight">Qu'est-ce qu'il se passe si je suis patron ?</p>
			<p class="margin">
				Ton entreprise continue a tourner avec les derniers paramètres que tu auras modifié mais tant que tu n'es pas sorti de l'asile tu ne pourras plus gérer ta boîte.
			</p>

			<p class="highlight">Qu'est-ce qu'il se passe si je suis maire ou candidat aux élections ?</p>
			<p class="margin">
				Si tu es maire, tu es destitué de tes fonctions (on ne veut pas d'un fou pour gérer la ville !) et c'est ton suppléant qui prend le relais.
				Si tu es candidat aux élections, tu es supprimé de la liste quelque que soit le nombre de concurrents.
			</p>
		</div>
	</div>
</div>
