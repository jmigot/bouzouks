<?php $this->layout->set_title('FAQ - Taxes et Impots'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Taxes et Impots</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>

			<p class="margin">
				La mairie a besoin des struls pour acheter aux entreprises leurs produits et financer divers avantages. Pour ce faire, des impôts et taxes sont envoyés aux bouzouks et aux
				entreprises.
			</p>

			<p class="highlight">Quelle est la différence entre une taxe et un impôt ?</p>
			<p class="margin">
				Pour l'impôt, il est automatiquement envoyé à chaque mandat du maire (tous les 15 jours). Le maire peut choisir le pourcentage de l'impôt à envoyer. Ce pourcentage est appliqué
				sur les struls que tu possèdes si tu es employé, et sur les struls de l'entreprise si tu es patron (dans ce cas l'impôt est prélevé directement).<br>
				Pour la taxe, le maire gère le taux et le jour d'envoi. Il peut envoyer une taxe toutes les <?= $this->bouzouk->config('mairie_intervalle_taxes') ?>h.
			</p>

			<ul>
				<li>Taxes employés : de <span class="pourpre"><?= $this->bouzouk->config('mairie_taxe_min') ?>%</span> à <span class="pourpre"><?= $this->bouzouk->config('mairie_taxe_max') ?>%</span> des struls de l'employé</li>
				<li>Impôt employés : de <span class="pourpre"><?= $this->bouzouk->config('mairie_impots_employes_min') ?>%</span> à <span class="pourpre"><?= $this->bouzouk->config('mairie_impots_employes_max') ?>%</span> des struls de l'employé</li>
				<li>Impôt entreprise : de <span class="pourpre"><?= $this->bouzouk->config('mairie_impots_entreprises_min') ?>%</span> à <span class="pourpre"><?= $this->bouzouk->config('mairie_impots_entreprises_max') ?>%</span> des struls de l'entreprise</li>
			</ul>

			<p class="highlight">Comment reçoit-on les taxes et les impôts ?</p>
			<p class="margin">
				Une <a href="<?= site_url('site/faq/missives') ?>">missive</a> te prévient de l'arrivée de la facture à payer. Même si ta boîte à missives est pleine, tu recevras toujours les
				factures.<br><br>

				Les employés reçoivent les taxes et impôts après avoir reçu leur salaire.<br>
				Les entreprises reçoivent les impôts après la rentrée d'argent et après avoir payé les employés.
			</p>

			<p class="highlight">Comment payer la facture ?</p>
			<p class="margin">
				Il te suffit d'aller dans ta boîte à missives puis de cliquer sur l'onglet <span class="pourpre">&laquo; Factures &raquo;</span>. Ensuite, tu peux cliquer sur le bouton
				<span class="pourpre">“payer”</span> situé à droite de la facture.<br>
				Même chose pour les patrons d'entreprise qui reçoivent la taxe de leur entreprise par missives.
			</p>

			<p class="highlight">Que se passe-t-il si je ne paye pas mes taxes/impôts ?</p>
			<p class="margin">
				Tu as <span class="pourpre"><?= $this->bouzouk->config('factures_delai_majoration') ?> jours</span> pour payer une facture. Passé ce délai ton bouzouk ne sera plus accepté dans aucun service ni aucune boutique de la ville de Vlurxtrznbnaxl nécessitant de
				l'argent et la dette seras majorée de <span class="pourpre"><?= $this->bouzouk->config('factures_pourcent_majoration') ?>%</span> tous les <span class="pourpre"><?= $this->bouzouk->config('factures_delai_majoration') ?> jours</span> jusqu'à un montant maximal de 
				1000 struls de majoration.<br><br>

				De plus, si tu as au moins <span class="pourpre"><?= $this->bouzouk->config('factures_nb_factures_perte_xp') ?> factures</span> majorées, tu perdras <span class="pourpre"><?= $this->bouzouk->config('factures_perte_xp') ?> xp</span> chaque jour.
			</p>
			
			<p class="highlight">Est-il possible de recevoir une taxe surprise le même jour que les impôts ?</p>
			<p class="margin">
				Oui. Il faudra alors payer deux impôts sur la même somme et donc devoir payer 160% des struls disponibles au moment de la distribution.
			</p>
		</div>
	</div>
</div>
