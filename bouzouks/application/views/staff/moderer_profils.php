<?php $this->layout->set_title('Modération - Profils'); ?>

<div id="staff-moderer_profils">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Modérer les profils</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<?= form_open('staff/moderer_profils/voir') ?>
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
			<h4><?= profil($joueur->id, $joueur->pseudo, $joueur->rang) ?></h4>
			<div class="bloc_bleu">
				<?= form_open('staff/moderer_profils/modifier', array('class' => 'centre')) ?>
					<input type="hidden" name="joueur_id" value="<?= $joueur->id ?>">

					<table>
						<!-- Adresse -->
						<tr>
							<td><label for="adresse" class="highlight">Adresse</label></td>
							<td><input type="text" name="adresse" id="adresse" value="<?= form_prep($joueur->adresse) ?>" size="50" maxlength="50"></td>
						</tr>

						<!-- Commentaire -->
						<tr>
							<td><label for="commentaire" class="highlight">Commentaire</label></td>
							<td><textarea name="commentaire" id="commentaire" cols="50" rows="5" maxlength="150"><?= form_prep($joueur->commentaire) ?></textarea></td>
						</tr>

						<!-- Statut -->
						<tr>
							<td><label for="statut" class="highlight">Statut</label></td>
							<td>
								<select name="statut" id="statut">
									<option value="<?= Bouzouk::Joueur_Actif ?>"<?= $joueur->statut == Bouzouk::Joueur_Actif ? ' selected' : '' ?>>Joueur actif</option>
									<option value="<?= Bouzouk::Joueur_Asile ?>"<?= $joueur->statut == Bouzouk::Joueur_Asile ? ' selected' : '' ?>>A l'asile</option>
								</select>
								Durée asile : <input type="text" name="duree_asile" size="3" maxlength="3" value="<?= $joueur->duree_asile ?>"> heures
							</td>
						</tr>

						<!-- Raison statut -->
						<tr>
							<td><label for="raison_statut" class="highlight">Raison statut<br><span class="pourpre">HTML autorisé</span></label></td>
							<td><textarea name="raison_statut" id="raison_statut" cols="50" rows="3" maxlength="250"><?= form_prep($joueur->raison_statut) ?></textarea></td>
						</tr>

						<!-- Admin statut -->
						<tr>
							<td><label class="highlight">Responsable statut</label></td>
							<td><?= isset($joueur->statut_staff_id) ? profil($joueur->statut_staff_id, $joueur->statut_staff_pseudo, $joueur->statut_staff_rang) : '<span class="pourpre"><i>aucun</i></span>' ?></td>
						</tr>

						<!-- Interdictions -->
						<tr>
							<td><label class="highlight">Interdictions</label></td>
							<td>
								Nombre d'envois à l'asile : <?= $joueur->nb_asile == 0 ? '<span class="pourpre">0 fois</span>' : '<span class="rouge gras">'.$joueur->nb_asile.' fois</span>' ?><br>
								<input type="checkbox" name="interdit_missives" id="interdit_missives"<?= $joueur->interdit_missives ? ' checked' : '' ?>><label for="interdit_missives">Interdit de missives</label><br>
								<input type="checkbox" name="interdit_tchat" id="interdit_tchat"<?= $joueur->interdit_tchat ? ' checked' : '' ?>><label for="interdit_tchat">Interdit de tchat</label><br>
								<input type="checkbox" name="interdit_plouk" id="interdit_plouk"<?= $joueur->interdit_plouk ? ' checked' : '' ?>><label for="interdit_plouk">Interdit de Plouk</label><br>
								<input type="checkbox" name="interdit_avatar" id="interdit_avatar"<?= $joueur->interdit_avatar ? ' checked' : '' ?>><label for="interdit_avatar">Interdit d'avatar perso</label>
							</td>
						</tr>
					</table>

					<p class="margin"><input type="submit" value="Modifier" class="confirmation"></p>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>




