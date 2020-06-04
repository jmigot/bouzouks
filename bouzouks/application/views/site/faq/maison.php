<?php $this->layout->set_title('FAQ - Maison'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Maison</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>
			<p class="margin">
				La maison est le lieu où se trouvent tous les objets que tu as pu acheter. Tu peux à partir de cette page les utiliser, les revendre ou les jeter. Cette page se décompose
				en 2 principales parties.
			</p>

			<p class="highlight">Tes objets</p>
			<p class="margin">
				Tous les objets que tu as acheté, que ce soit dans les shops ou au marché noir, se trouvent ici. Pour les utiliser, il te suffit de sélectionner le nombre d'objets que tu veux
				utiliser en même temps et de cliquer sur le bouton <span class="pourpre">Utiliser</span>. La technique est la même pour les vendre.
			</p>

			<p class="highlight">Tes objets en vente</p>
			<p class="margin">
				Cette partie recense les objets que tu as mis en vente et qui sont en attente d'un acheteur. Pour éviter que trop d'objets ne circulent, la récupération des objets t'es taxée
				d'un montant qui dépend du prix de vente que tu auras choisi.
			</p>

			<p class="highlight">Péremption des objets</p>
			<p class="margin">
				Chaque objet acheté possède un décompte de jours à partir duquel il deviendra périmé. Utiliser un objet périmé
				a des conséquences aléatoires, généralement les effets de l'objet sont inversés.<br><br>

				Il est possible d'augmenter la péremption de tous les objets de la maison ou même de rendre un objet en particulier
				impérissable, et tout ceci grâce à des objets spéciaux que l'on peut trouver au <span class="pourpre">Boostzouk</span>.<br>
				Attention les objets du BoostZouk pour la péremption ne fonctionnent pas sur les objets périmés.<br><br>

				<span class="pourpre">La péremption d'un objet est fixée à <?= $this->bouzouk->config('maison_peremption_max') ?> jours maximum.</span>
			</p>

			<p class="highlight">Comment les objets périment-ils ?</p>
			<p class="margin">
				Chaque nuit les objets perdent un jour de péremption, que ce soit les objets de la maison ou ceux qui sont en vente au marché noir. Si tu es à l'asile, ils périment aussi.
				Si tu es en pause la perte de pérempion est stoppée jusqu'à ce que tu reprennes ta partie.
			</p>

			<p class="highlight">Je suis tombé sur la Bouzopolice lors de la mise en vente de mes objets au marché noir. Que faire ?</p>
			<p class="margin">
				Tu n'as plus rien à faire : les objets que tu as essayé de vendre sont confisqués et tu n'es pas remboursé.
			</p>
		</div>
	</div>
</div>
