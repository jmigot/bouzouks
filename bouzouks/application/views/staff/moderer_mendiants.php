<?php $this->layout->set_title('Modération - Mendiants'); ?>

<div id="staff-moderer_mendiants">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Modérer les mendiants</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<?php foreach ($mendiants as $mendiant): ?>
				<?= form_open('staff/moderer_mendiants/modifier', array('class' => 'mendiant')) ?>
					<input type="hidden" name="joueur_id" value="<?= $mendiant->id ?>">

					<div class="joueur">
						<p class="fl-gauche"><img src="<?= img_url(avatar($mendiant->faim, $mendiant->sante, $mendiant->stress, $mendiant->perso)) ?>" height="65" alt="Image perso"></p>
						<p class="infos"><?= profil($mendiant->id, $mendiant->pseudo) ?></p>
					</div>

					<p class="argument">
						<textarea name="argument" rows="8"><?= form_prep($mendiant->argument) ?></textarea>
					</p>

					<p class="bulle_fin"></p>
					<p>
						<input type="submit" name="modifier" value="Modifier">
						<input type="submit" name="supprimer" value="Supprimer" class="confirmation">
					</p>
				</form>
			<?php endforeach; ?>
		</div>
	</div>
</div>




