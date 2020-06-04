<?php $this->layout->set_title('FAQ - Marché noir'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Marché noir</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>

			<p class="margin">
				Le marché noir est l'endroit où les bouzouks peuvent vendre et acheter leurs objets. Malheureusement, ceci étant une activité illégale...la bouzopolice veille.
			</p>

			<p class="highlight">A quel prix puis-je vendre mes objets ?</p>
			<p class="margin">
				Le prix minimum pour vendre un objet au marché noir est de <span class="pourpre">1 strul</span> et le prix maximum est de <span class="pourpre"><?= $this->bouzouk->config('maison_coefficient_max_vente') ?> fois le prix de l'objet</span>
				en magasin officiel.
			</p>

			<p class="highlight">Je veux retirer un objet du marché noir, combien cela va-t-il me couter ?</p>
			<p class="margin">
				La taxe de retrait des marchandises est actuellement de <span class="pourpre"><?= $this->bouzouk->config('maison_pourcentage_taxe_retrait') ?>%</span> du prix de l'objet fixé par le vendeur.<br><br>

				Ainsi, si tu mets en vente un objet à <span class="pourpre">100 struls</span>, tu devras payer <?= struls($this->bouzouk->config('maison_pourcentage_taxe_retrait')) ?> pour le retirer du marché noir.<br>
				<span class="pourpre">Le retrait d'un objet périmé est gratuit.</span>
			</p>
			
			<p class="highlight">Je suis tombé sur la Bouzopolice lors de l'achat d'un objet. Que faire ?</p>
			<p class="margin">
				Tu n'as plus rien à faire : le vendeur est payé, les objets que tu as essayé d'acheter sont confisqués et tu n'es pas remboursé. Bref, tu t'es fait avoir et c'est tant pis
				pour toi !
			</p>

			<p class="highlight">Quand est-ce que la bouzopolice fait sa ronde ?</p>
			<p class="margin">
				Personne ne le sait malheureusement...Il semblerait toutefois que seuls les plus chanceux peuvent être tranquilles.
			</p>
		</div>
	</div>
</div>
