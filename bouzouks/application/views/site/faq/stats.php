<?php $this->layout->set_title('FAQ - Statistiques'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Les statistiques (faim, santé, stress)</h4>
		<div class="bloc_bleu">
			<p class="margin">
				Lorsque le joueur commence l'aventure, ses statistiques sont de <span class="pourpre"><?= $this->bouzouk->config('joueur_faim_depart') ?>%</span> pour la faim,
				<span class="pourpre"><?= $this->bouzouk->config('joueur_sante_depart') ?>%</span> pour la santé et <span class="pourpre"><?= $this->bouzouk->config('joueur_stress_depart') ?>%</span>
				pour le stress. Le joueur doit veiller à maintenir ces statistiques afin d'avoir un niveau de vie correct et ne pas finir l'aventure prématurément.<br><br>

				Les jauges de faim et de santé baissent progressivement au cours des jours qui passent. La jauge de stress, quant à elle, augmente.
				Les jauges peuvent aussi être modifiées suivant les actions que tu réalises dans le jeu. Avoir un mauvais niveau de vie bloquera certaines de tes actions dans le jeu,
				il est donc conseillé de garder constamment un niveau de vie élevé.
			</p>

			<p class="highlight">Au bout de combien de temps mes statistiques tombent-elles à zéro ?</p>
			<p class="margin">
				Les statistiques de ton bouzouk peuvent tomber à zéro très rapidement si tu ne t'en occupes pas. En effet, l'espérance de vie maximale d'un bouzouk en pleine forme mais
				abandonné est estimée à environ <span class="pourpre">1 semaine</span>...
			</p>

			<p class="highlight">Comment remonter ses jauges (statistiques) ?</p>
			<p class="margin">
				Pour combler les besoins de ton bouzouk il faudra utiliser divers objets à acheter dans les <a href="<?= site_url('site/faq/shops') ?>">magasins</a>
				(Bouffzouk, Indispenzouk, Luxezouk) puis les récupérer dans ta maison en cliquant sur &laquo; <i>utiliser</i> &raquo; sur l'objet voulu.<br>
				Si ton niveau de vie est trop bas ou qu'un événement spécial se déroule dans le jeu, tu ne pourras pas accéder aux magasins. Pour te nourrir ou te soigner malgré tout,
				il faudra aller acheter ton objet sur <a href="<?= site_url('site/faq/marche_noir') ?>">le marché noir</a>.
			</p>

			<p class="highlight">Que se passe-t-il si les niveaux de faim, de santé ou de stress se retrouvent à 0% ?</p>
			<p class="margin">
				Dans le cas des jauges de faim et de santé : si les deux jauges se retrouvent à <span class="pourpre">0%</span> en même temps c'est le <a href="<?= site_url('site/faq/game_over') ?>">Game Over</a> !
				Tu n'as plus qu'à recommencer la partie :)<br><br>

				Dans le cas de la jauge de stress, au contraire, celle-ci doit rester proche de <span class="pourpre">0%</span>. Si elle atteint <span class="pourpre">100%</span>, ton bouzouk devient fou et est interné à l'asile psychiatrique
				de la ville pour une durée de <span class="pourpre">2 jours minimum</span>. Pendant cette période tu n'auras plus accès à rien à part une machine à café pour discuter avec les autres personnes internées et le <span class="pourpre">0%</span> en même temps c'est le <a href="<?= site_url('site/faq/plouk') ?>">Plouk</a>.
			</p>
		</div>
	</div>
</div>
