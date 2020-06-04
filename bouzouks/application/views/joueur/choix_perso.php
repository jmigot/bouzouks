<?php
$this->layout->set_title("Choix du bouzouk");
$this->layout->ajouter_javascript('joueur.js');
?>

<div id="joueur-choix_perso">


		<div class="pts-actions msg-attention">
		<h2>Il faut être présentable avant de chercher un job...</h2>
		<p class="margin">Choisis le sexe et le style de ton bouzouk. Remplis également ta date de naissance (pour avoir un cadeau à ton anniversaire), ainsi qu'un commentaire optionnel qui apparaîtra sur ton profil.<br><br>
			<em></em><p>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Création du personnage</h4>
		<div class="bloc_bleu">
			<p class="mini_bloc">Tu pourras toujours changer ces informations plus tard.</p>
			<?= form_open('joueur/choix_perso') ?>
				<div class="inline-block moitie">
					<div class="frameborder_bleu padding marge_droite">
						<p class="highlight">Choisis ton bouzouk</p>

						<!-- Sexe du bouzouk -->
						<p class="centre margin">
							<input type="radio" name="sexe" id="sexe_male" value="male" <?= set_radio('sexe', 'male', true) ?>><label for="sexe_male">Mâle</label>
							<input type="radio" name="sexe" id="sexe_femelle" value="femelle" <?= set_radio('sexe', 'femelle') ?>><label for="sexe_femelle">Femelle</label>
						</p>

						<!-- Choix perso -->
						<p><?= select_bouzouk($this->input->post('sexe'), $this->input->post('perso_male'), $this->input->post('perso_femelle')) ?></p>
						<p class="clearfloat"></p>
					</div>
				</div>

				<div class="inline-block moitie">
					<!-- Date de naissance -->
					<div class="frameborder_bleu padding">
						<p class="highlight">Date de naissance</p>
						<p class="centre">
							<input type="text" name="jour" value="<?= set_value('jour', $dn[2]) ?>" class="centre" size="3" maxlength="2" placeholder="jour"> /
							<input type="text" name="mois" value="<?= set_value('mois', $dn[1]) ?>" class="centre" size="3" maxlength="2" placeholder="mois"> /
							<input type="text" name="annee" value="<?= set_value('annee', $dn[0]) ?>" class="centre" size="6" maxlength="4" placeholder="année">
						</p>
						<p class="margin">
							<em>Cette information est gardée confidentielle, conformément à la loi sur la protection des données</em>
						</p>
					</div>

					<!-- Commentaire -->
					<div class="frameborder_bleu padding marge_haut">
						<p class="highlight">Commentaire</p>
						<p class="centre"><textarea name="commentaire" id="commentaire" class="compte_caracteres" rows="7" maxlength="150" placeholder="Optionnel"><?= set_value('commentaire') ?></textarea></p>
						<p id="commentaire_nb_caracteres_restants" class="centre transparent">&nbsp;</p>
						<p class="margin">Ce commentaire sera visible sur ton profil bouzouk</p>
					</div>
				</div>

				<!-- Valider -->
				<p class="centre clearfloat margin">
					<input type="submit" value="Valider la création du machin qui me sert de personnage" class="bouton_rouge surbrillance">
				</p>
			</form>
		</div>
	</div>
</div>