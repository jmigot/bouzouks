<?php $this->layout->set_title('FAQ - Missives'); ?>

<div>
	<div class="cellule_bleu_type1 marge_haut">
		<h4>FAQ - Missives</h4>
		<div class="bloc_bleu">
			<!-- Retour à la FAQ -->
			<p class="highlight centre"><a href="<?= site_url('site/faq') ?>">Retour à la FAQ</a></p>
			<p class="margin">
				La boîte à missives te permet de communiquer librement avec les autres bouzouks et de faire des rencontres par le biais de missives. Cette messagerie est également l'endroit
				où tu reçois tes factures et les réponses aux questions que tu as pu poser aux administrateurs.
			</p>

			<p class="highlight">Comment supprimer mes missives ?</p>
			<p class="margin">
				Il te suffit de cocher les cases à droite et de cliquer sur <span class="pourpre">Supprimer</span> en bas de la page.
			</p>

			<p class="highlight">Est-ce que je suis limité dans l'envoi des missives ?</p>
			<p class="margin">
				Tu es limité non seulement pour l'envoi mais aussi pour la réception : tu as le droit à un total de <span class="pourpre"><?= $this->bouzouk->config('missives_limite') ?> missives</span> dans toute ta boîte.
				Au delà, tu ne pourras plus utiliser ta boîte à missives : tu devras alors faire le ménage. Tu peux suivre la taille de ta boîte grâce à la jauge en bas à droite.<br><br>

				<span class="pourpre">Si ta boîte est pleine, tu recevras toujours les factures du jeu, les lettres d'embauche/démission, etc...<br>
				Tu peux également écrire/recevoir des missives des administrateurs même si ta boîte est pleine.</span>
			</p>

			<p class="highlight">Puis-je personnaliser mes messages ?</p>
			<p class="margin">
				Voilà un intérêt des missives : tu peux personnaliser par exemple en choisissant le timbre lors de la rédaction du message, tu peux aussi choisir ton adresse (fictive) dans
				la rubrique <a href="<?= site_url('mon_compte') ?>">Mon compte</a>.
			</p>

			<p class="highlight">Combien coûte l'envoi d'une missive ?</p>
			<p class="margin">
				Le prix minimum pour envoyer une missive est de <span class="pourpre">0,1 strul</span>, mais selon le timbre que tu choisis, le prix varie de <span class="pourpre">0,1 strul</span>
				à <span class="pourpre">0,5 strul</span>.
			</p>

			<p class="hr"></p>

			<p class="margin rouge">
				<b>ATTENTION :</b> un administrateur ou un modérateur ne te demandera JAMAIS ton mot de passe par missive !<br>
				<i>Ne réponds surtout pas</i> ! Les membres de la TeamBouzouk n'ont pas besoin de ton mot de passe pour accéder à ton compte. Il s'agit de quelqu'un voulant s'approprier ton compte.
			</p>

			<p class="margin">
				Dans ce cas, contacte au plus vite un administrateur en précisant la date du message.<br>
				La personne concernée sera bannie du site et risque des poursuites judiciaires, ce délit étant passible de <span class="pourpre">2 ans d'emprisonnement</span> et de
				<span class="pourpre">30 000 €</span> d'amende.
				Si, si c'est pas des bêtises ! Voir <a href="http://www.legifrance.gouv.fr/affichCode.do?idSectionTA=LEGISCTA000006149839&amp;cidTexte=LEGITEXT000006070719">la loi en vigueur</a>.
			</p>
		</div>
	</div>
</div>
