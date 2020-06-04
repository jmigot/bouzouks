<?php $this->layout->set_title('Modération - Elections'); ?>

<div id="staff-moderer_elections">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Modérer les élections</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<!-- Liste des candidats -->
			<?php foreach ($candidats as $candidat): ?>
				<?= form_open('staff/moderer_elections/modifier', array('class' => 'candidat')) ?>
					<input type="hidden" name="candidat_id" value="<?= $candidat->id ?>">

					<!--Avatar -->
					<p class="perso fl-gauche centre">
						<img src="<?= img_url(avatar($candidat->faim, $candidat->sante, $candidat->stress, $candidat->perso)) ?>" alt="Bouzouk"><br>
						<?= profil($candidat->id, $candidat->pseudo) ?>
					</p>

					<!-- Slogan de campagne -->
					<div>
						<input maxlength="60" type="text" name="slogan" value="<?= form_prep($candidat->slogan) ?>" size="60">
					</div>
					<!-- Texte de campagne -->
					<div>
						<textarea name="texte" cols="65" rows="8"><?= form_prep($candidat->texte) ?></textarea>
					</div>

					<p class="clearfloat droite">
						<input type="submit" value="Modifier">
					</p>
				</form>
				<div class="hr"></div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
