<?php $this->layout->set_title('Mail non reçu'); ?>

<div class="cellule_bleu_type1 marge_haut">
	<h4>Mail non reçu ?</h4>
	<div class="bloc_bleu" id="visiteur-mail_non_recu">
		<p class="margin-petit">
			Tu n'as pas reçu ton mail de confirmation ? Pas de panique ! <br/>
			Si tu as une adresse hotmail, live ou outlook, il y a une petite manip' à faire. Trois fois rien ...<br/>
			Pour ce faire, il faut que tu ailles dans les options de ton compte mail( le petit engrenage ).<br/>
			Ensuite, tu cliques sur "options" puis, au paragraphe "Prévention contre le courrier indésirable" , tu cliques sur "Expéditeurs autorisés".<br/>
			C'est alors que tu verras un champs de saisie accompagné d'une liste.<br/>
			Dans le champs de saisie, tu entre le nom du site, à savoir bouzouks.net, et tu valides en cliquant sur le bouton "ajouter".<br/>
			Tu pourras alors entrer ton adresse mail pour obtenir un nouveau lien d'activation.<br/><br/>
			Sinon te suffit d'entrer ton adresse email ci-dessous.
		</p>

		<!-- Formulaire -->
		<?= form_open('visiteur/mail_non_recu'); ?>
			<p class="centre frameborder_bleu">
				<label for="email">Ton email</label>
				<input type="email" name="email" id="email" maxlength="320" value="<?= set_value('email') ?>">
				<input type="submit" value="Valider">
			</p>
		</form>

		<p class="centre margin"><a href="<?= site_url('visiteur/inscription_confirmation') ?>">Confirmer mon email avec un code reçu</a></p>
	</div>
</div>
