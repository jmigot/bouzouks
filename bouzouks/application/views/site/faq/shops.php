<?php $this->layout->set_title('FAQ - Magasins'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Magasins</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>
			<p class="margin">
				Les shops (ou magasins) permettent de maintenir un niveau de vie suffisant. Chaque shop correspond à chaque stat : bouffzouk pour la faim, indispenzouk pour la santé, luxezouk pour le stress
				et boostzouk pour les objets spéciaux.
			</p>

			<p class="highlight">Il n'y a plus l'objet que je souhaite !</p>
			<p class="margin">
				Pas de chance ! Si vraiment tu as besoin de tel ou tel objet et qu'il est en rupture de stock, nous t'invitons à aller voir au marché noir s'il ne s'y trouve pas.
				Dans le cas contraire, les shops sont réapprovisionnés <span class="pourpre">tous les 3 jours</span> : il te suffira d'attendre un peu...
			</p>

			<p class="highlight">J'ai acheté un objet mais mes stats n'augmentent pas.</p>
			<p class="margin">
				Lorsque tu achètes un objet, celui-ci est ajouté dans ta maison. Tu dois t'y rendre pour l'utiliser.
			</p>

			<p class="highlight">Pourquoi on me refuse l'accès au shop ?</p>
			<p class="margin">
				Il y a 3 possibilités :
			</p>

			<ul>
				<li>tes stats sont trop faibles pour accéder au shop, va au marché noir et achète des objets pour y remédier</li>
				<li>un évènement particulier est survenu, le shop est inaccessible pour tous pendant une courte durée</li>
				<li>tu as des factures impayées depuis plus de <span class="pourpre"><?= $this->bouzouk->config('factures_delai_majoration') ?> jours</span>, rends toi sur la <a href="<?= site_url('factures') ?>">page des factures</a> et régle tes dettes</li>
			</ul>

			<p class="highlight">Comment achète-t-on les objets rares ?</p>
			<p class="margin">
				Certains objets marqués <span class="pourpre">[Rare]</span> ou <span class="pourpre">[Très rare]</span> n'apparaissent qu'au marché noir, de temps en temps. Ils sont soit vendus par des personnes mystiques, soit par des joueurs
				qui ont réussi à en acheter et qui les revendent.
			</p>
		</div>
	</div>
</div>
