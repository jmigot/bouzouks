<?php $this->layout->set_title('Plus de struls') ?>

<div id="plus_de_struls-index">
	<!-- Explications -->
	<div class="cellule_gris_type1">
		<h4>Plus de struls !</h4>
		<div class="bloc_gris centre">
			<p class="margin">
				Le système <span class="italique pourpre">Plus de struls !</span> te permet d'acheter des <span class="pourpre">struls</span> ou des <span class="pourpre">objets rares</span>. Ce service est payant et permet à la TeamBouzouk de payer
				le serveur de jeu tous les mois.<br><br>

				Nous proposons Paypal et Allopass comme plateformes de paiement, avec une large préférence pour Paypal :)
			</p>
		</div>
	</div>

	<!-- Paypal -->
	<div class="cellule_bleu_type2 marge_haut">
		<h4>Don Paypal : struls ou objets rares</h4>
		<div class="bloc_bleu">
			<p class="margin centre">
				<span class="pourpre">Carte Bancaire</span> ou <span class="pourpre">Paypal</span>. Délai possible avant de recevoir la récompense (validation manuelle).<br>
				<i>(les centimes ne sont pas pris en compte)</i>
			</p>

			<ul>
				<li><span class="bleu">&nbsp; 5.00€</span> : <span class="pourpre">60 Fragments de Schnibble Bleuté</span></li>
				<li><span class="bleu">&nbsp; 6.00€</span> à <span class="bleu">&nbsp; 9.00€</span> : <span class="pourpre">gain de struls</span> (1€ = 85 struls)</li>
				<li><span class="bleu">10.00€</span> à <span class="bleu">19.00€</span> : <span class="pourpre">un objet [Rare] aléatoire</span></li>
				<li><span class="bleu">20.00€</span> ou plus</span> : <span class="pourpre">un objet [Très Rare] aléatoire</span> et le respect de la TeamBouzouk</li>
			</ul>

			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="margin centre">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="BRQV5TV97XXSW">

				<!-- Pseudo -->
				<input type="hidden" name="on0" value="Pseudo">
				<input type="hidden" name="os0" value="<?= $this->session->userdata('pseudo').' ('.$this->session->userdata('id').')' ?>">

				<!-- Image -->
				<input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
				<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
	</div>

	<!-- Allopass -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Micro-paiement Allopass : struls</h4>
		<div class="bloc_bleu centre">
			<p class="margin">
				<span class="pourpre">Appel surtaxé</span>, <span class="pourpre">SMS surtaxé</span>, <span class="pourpre">Carte Bancaire</span>, <span class="pourpre">Internet+</span> ou <span class="pourpre">NeoSurf</span>. Instantané.
			</p>

			<ul class="gauche">
				<li><span class="bleu"><?= number_format($allopass['sms']['prix'], 2) ?> €</span> : <?= struls($allopass['sms']['struls']) ?></li>
				<li><span class="bleu"><?= number_format($allopass['appel']['prix'], 2) ?> €</span> : <?= struls($allopass['appel']['struls']) ?></li>
				<li><span class="bleu"><?= number_format($allopass['sms2']['prix'], 2) ?> €</span> : <?= struls($allopass['sms2']['struls']) ?></li>
			</ul>

			<div class="inline-block margin">
				<div class="frameborder2">
					<p class="highlight">Appel à <span class="noir"><?= number_format($allopass['appel']['prix'], 2) ?> €</span> : <b><?= struls($allopass['appel']['struls']) ?></b></p>
					
					<!-- Begin Allopass Checkout-Button Code -->
					<script type="text/javascript" src="https://payment.allopass.com/buy/checkout.apu?ids=48602&amp;idd=125996&amp;lang=fr"></script>
					<noscript>
						<a href="https://payment.allopass.com/buy/buy.apu?ids=48602&amp;idd=125996" style="border:0">
							<img src="https://payment.allopass.com/static/buy/button/fr/162x56.png" style="border:0" alt="Buy now!" />
						</a>
					</noscript>
					<!-- End Allopass Checkout-Button Code -->
				</div>
			</div>

			<div class="inline-block margin">
				<div class="frameborder2">
					<p class="highlight">SMS à <span class="noir"><?= number_format($allopass['sms']['prix'], 2) ?> €</span> : <b><?= struls($allopass['sms']['struls']) ?></b></p>
					
					<!-- Begin Allopass Checkout-Button Code -->
					<script type="text/javascript" src="https://payment.allopass.com/buy/checkout.apu?ids=48602&amp;idd=126004&amp;lang=fr"></script>
					<noscript>
						<a href="https://payment.allopass.com/buy/buy.apu?ids=48602&amp;idd=126004" style="border:0">
							<img src="https://payment.allopass.com/static/buy/button/fr/162x56.png" style="border:0" alt="Buy now!" />
						</a>
					</noscript>
					<!-- End Allopass Checkout-Button Code -->
				</div>
			</div>
			
			<div class="inline-block margin">
				<div class="frameborder2">
					<p class="highlight">SMS à <span class="noir"><?= number_format($allopass['sms2']['prix'], 2) ?> €</span> : <b><?= struls($allopass['sms2']['struls']) ?></b></p>
					
					<!-- Begin Allopass Checkout-Button Code -->
					<script type="text/javascript" src="https://payment.allopass.com/buy/checkout.apu?ids=48602&amp;idd=346503&amp;lang=fr"></script>
					<noscript>
						<a href="https://payment.allopass.com/buy/buy.apu?ids=48602&amp;idd=346503" style="border:0">
							<img src="https://payment.allopass.com/static/buy/button/fr/162x56.png" style="border:0" alt="Buy now!" />
						</a>
					</noscript>
					<!-- End Allopass Checkout-Button Code -->
				</div>
			</div>
		</div>
	</div>
</div>
