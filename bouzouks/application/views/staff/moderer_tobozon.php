<?php $this->layout->set_title('Modération - Tobozon'); ?>

<div id="staff-moderer_tobozon">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Modérer le tobozon</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<?= form_open('staff/moderer_tobozon/voir') ?>
				<p class="centre">
					Pseudo :
					<?= $select_joueurs ?>
					<input type="submit" name="modifier" value="Modifier">
				</p>
			</form>
		</div>
	</div>

	<?php if (isset($joueur)): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4><?= profil($joueur->id, $joueur->pseudo) ?></h4>
			<div class="bloc_bleu">
				<?= form_open('staff/moderer_tobozon/modifier', array('class' => 'centre')) ?>
					<input type="hidden" name="joueur_id" value="<?= $joueur->id ?>">

					<!-- Groupe -->
					<p class="margin">
						<label for="group_id">Groupe</label><br>
						<select name="group_id" id="group_id">
							<option value="<?= Bouzouk::Tobozon_IdGroupeBouzouks ?>"<?= $joueur->group_id == Bouzouk::Tobozon_IdGroupeBouzouks ? ' selected' : '' ?>>Bouzouk</option>
							<option value="<?= Bouzouk::Tobozon_IdGroupeCensures ?>"<?= $joueur->group_id == Bouzouk::Tobozon_IdGroupeCensures ? ' selected' : '' ?>>Censuré</option>
						</select>
					</p>

					<!-- Raison -->
					<p class="margin">
						<label for="raison">Raison</label><br>
						<textarea name="raison" id="raison" cols="50" rows="3" maxlength="250"><?= set_value('raison') ?></textarea>
					</p>
					
					<p class="margin"><input type="submit" value="Modifier" class="confirmation"></p>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>




