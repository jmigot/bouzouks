<?php $this->layout->set_title('FAQ - Bonneteau'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Bonneteau</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>
			<p class="margin">
				Jeu de hasard et d'argent consistant à deviner sous lequel des trois bols retournés se trouve un oeil en morphoplastoc.
			</p>

			<p class="highlight">Comment jouer ?</p>
			<p class="margin">
				Il suffit de parier un certain nombre de struls puis de choisir un des trois bols. Si tu trouves le bol qui cache un oeil en
				morphoplastoc, tu gagnes le double de ta mise. Dans le cas contraire, tu perds la mise...
			</p>

			<p class="highlight">Combien de struls peut-on parier ?</p>
			<p class="margin">
				On peut parier autant de struls que l'on veut, à partir de <?= struls($this->bouzouk->config('jeux_min_prix_bonneteau')) ?>, si l'on possède la somme misée.
			</p>

			<p class="margin pourpre">Astuce : si tu gagnes <?= $this->bouzouk->config('jeux_nb_parties_gain_xp') ?> fois d'affilée au bonneteau, tu gagnes +<?= $this->bouzouk->config('jeux_gain_xp_bonneteau') ?> xp</p>

			<p class="margin italique">Si tu es comme <?= profil(47, 'Nuko') ?> et que tu es accro au jeu, tu risques de perdre tous tes struls, alors attention !</p>
		</div>
	</div>
</div>
