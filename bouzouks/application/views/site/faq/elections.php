<?php $this->layout->set_title('FAQ - Elections'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Elections</h4>
		<div class="bloc_bleu">
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>
			<p class="margin">
			<?php
				$duree = $this->bouzouk->config('elections_duree_candidatures') +
						 $this->bouzouk->config('elections_duree_tour_1') +
						 $this->bouzouk->config('elections_duree_tour_2') +
						 $this->bouzouk->config('elections_duree_tour_3');
			?>
				Les élections permettent d'élire un nouveau maire dans le jeu tous les <span class="pourpre"><?= $duree ?> jours</span>. Elles se déroulent en 4 phases :
			</p>

			<ul>
				<li>Candidatures : <span class="pourpre"><?= $this->bouzouk->config('elections_duree_candidatures') ?> jours</span></li>
				<li>1er tour : <span class="pourpre"><?= $this->bouzouk->config('elections_duree_tour_1') ?> jours</span></li>
				<li>2ème tour : <span class="pourpre"><?= $this->bouzouk->config('elections_duree_tour_2') ?> jours</span></li>
				<li>3ème tour : <span class="pourpre"><?= $this->bouzouk->config('elections_duree_tour_3') ?> jours</span></li>
			</ul>

			<p class="margin">
				À noter que l'ordre d'apparition des candidats est aléatoire à chaque rechargement de page, mais une fois que l'on a voté, les candidats sont classés de celui ayant le plus de
				voix à celui en ayant le moins. Le nombre de votes et le pourcentage par rapport au nombre de votes total sont affichés pour chaque candidat.<br><br>
				Il est possible de voter une fois par tour et par bouzouk.<br><br>
				Une fois les élections terminées, le classement des élections permet de revoir les scores de chaque candidat, avec le nombre de votes et le poucentage à chaque tour, ainsi que
				le classement final.
			</p>

			<p class="highlight pourpre">Candidatures</p>
			<p class="margin">
				Pour candidater, le bouzouk doit avoir au moins <span class="pourpre"><?= $this->bouzouk->config('elections_xp_candidater') ?> xp</span> et le coût d'une candidature est de <?= struls($this->bouzouk->config('elections_prix_candidater')) ?>. Candidater rapporte <span class="pourpre">+<?= $this->bouzouk->config('elections_gain_xp_candidater') ?> xp</span>. Une fois le texte validé,
				il est toujours possible de le modifier jusqu'au 1er tour.<br>
				Le nombre maximum de candidatures est limité à <span class="pourpre"><?= $this->bouzouk->config('elections_places_disponibles') ?> places</span> par élection.
			</p>

			<p class="highlight pourpre">1er tour</p>
			<p class="margin">
				Le 1er tour montre tous les bouzouks ayant candidaté aux élections.
			</p>

			<p class="highlight pourpre">2eme tour</p>
			<p class="margin">
				Le 2eme tour accepte les 6 meilleurs candidats du 1er tour. Ces 6 candidats gagnent <span class="pourpre">+<?= $this->bouzouk->config('elections_gain_xp_tour2') ?> xp</span>. Tous les autres n'ayant pas atteint le 2ème tour perdent <span class="pourpre">-<?= $this->bouzouk->config('elections_perte_xp_tour2') ?> xp</span>.
			</p>

			<p class="highlight pourpre">3e tour</p>
			<p class="margin">
				Le 3ème tour, ou duel, accepte les 2 meilleurs candidats du 2ème tour. Ces 2 candidats gagnent <span class="pourpre">+<?= $this->bouzouk->config('elections_gain_xp_tour3') ?> xp</span>. Les autres bouzouks n'ayant pas atteint le 3ème tour ne perdent pas d'xp.<br><br>
				Particularité : les suffrages du 3ème tour sont cachés jusqu'à la fin (jusqu'à l'élection du maire).
			</p>

			<p class="highlight">Le forum Propagande sur le Tobozon</p>
			<p class="margin">
				Pendant les élections tu as la possibilité de créér un sujet pour compléter ta campagne électorale, qui sera automatiquement ajouté sur la page <span class="pourpre">Elections</span>
				pendant la nuit. Les sujets du forum propagande sont effacés à chaque fin d'élections. Tu as le droit de recréer un autre sujet par tour, il remplacera l'ancien si tu décides de
				changer de stratégie.<br><br>

				<span class="pourpre">Attention ! Tu dois attendre la veille du 1er tour pour poster ton topic de propagande, sinon
				celui-ci sera supprimé par un modérateur.</span>
			</p>
		</div>
	</div>
</div>


