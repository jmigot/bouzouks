<?php $this->layout->set_title('Admin - Gestion des joueurs'); ?>

<div id="staff-gerer_joueurs">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Gérer les joueurs</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<?= form_open('staff/gerer_joueurs/voir') ?>
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
				<?= form_open('staff/gerer_joueurs/modifier', array('class' => 'centre')) ?>
					<p><input type="hidden" name="joueur_id" value="<?= $joueur->id ?>"></p>

					<table>
						<!-- Pseudo -->
						<tr>
							<td><label for="pseudo" class="highlight">Pseudo</label></td>
							<td><input type="text" name="pseudo" id="pseudo" value="<?= $joueur->pseudo ?>" maxlength="15"></td>
						</tr>

						<!-- Date de naissance -->
						<tr>
							<td><label for="date_de_naissance_jour" class="highlight">Date de naissance</label></td>
							<td><input type="text" name="date_de_naissance_jour" id="date_de_naissance_jour" value="<?= mb_substr($joueur->date_de_naissance, 8, 2) ?>" size="2" maxlength="2"> / <input type="text" name="date_de_naissance_mois" value="<?= mb_substr($joueur->date_de_naissance, 5, 2) ?>" size="2" maxlength="2"> / <input type="text" name="date_de_naissance_annee" value="<?= mb_substr($joueur->date_de_naissance, 0, 4) ?>" size="4"  maxlength="4"></td>
						</tr>

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

						<tr>
							<td colspan="2"><p class="hr"></p></td>
						</tr>

						<!-- Statut -->
						<tr>
							<td><label for="statut" class="highlight">Statut</label></td>
							<td>
								<select name="statut" id="statut">
									<option value="<?= Bouzouk::Joueur_Inactif ?>"<?= $joueur->statut == Bouzouk::Joueur_Inactif ? ' selected' : '' ?>>Inactif</option>
									<option value="<?= Bouzouk::Joueur_Etudiant ?>"<?= $joueur->statut == Bouzouk::Joueur_Etudiant ? ' selected' : '' ?>>Etudiant</option>
									<option value="<?= Bouzouk::Joueur_Actif ?>"<?= $joueur->statut == Bouzouk::Joueur_Actif ? ' selected' : '' ?>>Joueur actif</option>
									<option value="<?= Bouzouk::Joueur_Asile ?>"<?= $joueur->statut == Bouzouk::Joueur_Asile ? ' selected' : '' ?>>A l'asile</option>
									<option value="<?= Bouzouk::Joueur_Pause ?>"<?= $joueur->statut == Bouzouk::Joueur_Pause ? ' selected' : '' ?>>En pause</option>
									<option value="<?= Bouzouk::Joueur_GameOver ?>"<?= $joueur->statut == Bouzouk::Joueur_GameOver ? ' selected' : '' ?>>Game over</option>
									<option value="<?= Bouzouk::Joueur_Banni ?>"<?= $joueur->statut == Bouzouk::Joueur_Banni ? ' selected' : '' ?>>Banni</option>
								</select>
								Durée asile : <input type="text" name="duree_asile" size="3" maxlength="3" value="<?= $joueur->duree_asile ?>"> heures
							</td>
						</tr>

						<!-- Date statut -->
						<tr>
							<td><label for="date_statut_jour" class="highlight">Date statut</label></td>
							<td>
								<input type="text" name="date_statut_jour" id="date_statut_jour" value="<?= mb_substr($joueur->date_statut, 8, 2) ?>" size="2" maxlength="2"> / <input type="text" name="date_statut_mois" value="<?= mb_substr($joueur->date_statut, 5, 2) ?>" size="2" maxlength="2"> / <input type="text" name="date_statut_annee" value="<?= mb_substr($joueur->date_statut, 0, 4) ?>" size="4" maxlength="4">
								&nbsp;&nbsp;à&nbsp;&nbsp; <input type="text" name="date_statut_heures" value="<?= mb_substr($joueur->date_statut, 11, 2) ?>" size="2" maxlength="2"> h <input type="text" name="date_statut_minutes" value="<?= mb_substr($joueur->date_statut, 14, 2) ?>" size="2" maxlength="2"> min <input type="text" name="date_statut_secondes" value="<?= mb_substr($joueur->date_statut, 17, 2) ?>" size="2" maxlength="2"> s
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
							<td><?= isset($joueur->statut_staff_id) ? profil($joueur->statut_staff_id, $joueur->statut_staff_pseudo) : '<span class="pourpre"><i>aucun</i></span>' ?></td>
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

						<tr>
							<td colspan="2"><p class="hr"></p></td>
						</tr>

						<!-- Rang -->
						<tr>
							<td><label for="rang" class="highlight">Rang</label></td>
							<td>
								<?php foreach ($this->bouzouk->get_droits() as $masque => $titre): ?>
									<?php if ($masque == Bouzouk::Rang_Admin && ! $this->bouzouk->is_admin(Bouzouk::Rang_Admin)) continue; ?>
									<input type="checkbox" id="masque_<?= $masque ?>" name="rang[]" value="<?= $masque ?>"<?= (($joueur->rang & $masque) > 0) ? 'checked' : '' ?>><label for="masque_<?= $masque ?>"><?= $titre ?></label><br>
								<?php endforeach; ?>
							</td>
						</tr>

						<!-- Groupe tobozon -->
						<tr>
							<td><label class="highlight">Groupe tobozon</label></td>
							<td>
								<select name="groupe_tobozon">
									<?php foreach ($groupes_tobozon as $groupe): ?>
										<option value="<?= $groupe->g_id?>"<?= ($joueur->group_id == $groupe->g_id) ? ' selected' : '' ?>><?= $groupe->g_title ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						
						<!-- Rang description -->
						<tr>
							<td><label for="rang_description" class="highlight">Titre du rang</label></td>
							<td><input type="text" name="rang_description" id="rang_description" value="<?= form_prep($joueur->rang_description) ?>" maxlength="50"></td>
						</tr>

						<tr>
							<td colspan="2"><p class="hr"></p></td>
						</tr>

						<!-- Infos -->
						<tr>
							<td><label for="struls" class="highlight">Infos</label></td>
							<td><span class="pourpre">Struls</span> : <input type="text" name="struls" id="struls" value="<?= $joueur->struls ?>" size="8" maxlength="10" class="centre"> struls - <span class="pourpre">Expérience</span> : <input type="text" name="experience" value="<?= $joueur->experience ?>" size="5" maxlength="8" class="centre"> xp - <span class="pourpre">Points action</span> : <input type="text" name="points_action" value="<?= $joueur->points_action ?>" size="5" maxlength="3" class="centre"> pts</td>
						</tr>

						<!-- Stats -->
						<tr>
							<td><label for="faim" class="highlight">Stats</label></td>
							<td><span class="pourpre">Faim</span> : <input type="text" name="faim" id="faim" value="<?= $joueur->faim ?>" size="3" maxlength="3" class="centre">% - <span class="pourpre">Santé</span> : <input type="text" name="sante" value="<?= $joueur->sante ?>" size="3" maxlength="3" class="centre">% - <span class="pourpre">Stress</span> : <input type="text" name="stress" value="<?= $joueur->stress ?>" size="3" maxlength="3" class="centre">%</td>
						</tr>

						<!-- Stats clan -->
						<tr>
							<td><label for="force" class="highlight">Stats clans</label></td>
							<td><span class="pourpre">Force</span> : <input type="text" name="force" id="force" value="<?= $joueur->force ?>" size="3" maxlength="6" class="centre"> pts - <span class="pourpre">Charisme</span> : <input type="text" name="charisme" value="<?= $joueur->charisme ?>" size="3" maxlength="6" class="centre"> pts - <span class="pourpre">Intelligence</span> : <input type="text" name="intelligence" value="<?= $joueur->intelligence ?>" size="3" maxlength="6" class="centre"> pts</td>
						</tr>
					</table>

					<p class="rouge margin">Ils sont beaux les néons rouges ?</p>
					<p class="margin"><input type="submit" value="Modifier" class="confirmation"></p>
				</form>
			</div>
		</div>

		<div class="cellule_bleu_type1 marge_haut">
			<h4>Envoyer une fiente de pioupiouk à <?= profil($joueur->id, $joueur->pseudo, $joueur->rang) ?></h4>
			<div class="bloc_bleu">
				<p class="margin">Envoyer une fiente de pioupiouk dans la maison du joueur pour lui faire perdre de l'xp. La perte sera indiquée dans son historique.</p>

				<?= form_open('staff/gerer_joueurs/fiente_pioupiouk', array('class' => 'centre margin')) ?>
					<p>
						<input type="hidden" name="joueur_id" value="<?= $joueur->id ?>">
						Perte d'xp : <input type="text" name="perte_xp" size="3"> xp<br>
						<input type="submit" value="Envoyer" class="confirmation">
					</p>
				</form>
			</div>
		</div>

		<div class="cellule_bleu_type1 marge_haut">
			<h4>Ajouter des objets à <?= profil($joueur->id, $joueur->pseudo, $joueur->rang) ?></h4>
			<div class="bloc_bleu ajouter_objet">
				<?= form_open('staff/gerer_joueurs/donner_objet', array('class' => 'centre margin')) ?>
					<p><input type="hidden" name="joueur_id" value="<?= $joueur->id ?>"></p>

					<table>
						<!-- Objet -->
						<tr>
							<td><p class="highlight">Objet :</p></td>
							<td>
								<select name="objet_id">
									<option value="" selected>------------------------------</option>
									<?php foreach ($objets as $objet): ?>
										<option value="<?= $objet->id ?>"><?= $objet->nom ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>

						<!-- Quantité -->
						<tr>
							<td><p class="highlight">Quantité : </p></td>
							<td><input type="text" name="quantite" size="3"></td>
						</tr>

						<!-- Péremption -->
						<tr>
							<td><p class="highlight">Péremption (-1 pour illimité) : </p></td>
							<td><input type="text" name="peremption" size="3"> jours</td>
						</tr>
					</table>

					<p><input type="submit" value="Envoyer" class="confirmation"></p>
				</form>
			</div>
		</div>

		<div class="cellule_bleu_type1 marge_haut">
			<h4>Envoyer une notification à <?= profil($joueur->id, $joueur->pseudo, $joueur->rang) ?></h4>
			<div class="bloc_bleu">
				<?= form_open('staff/gerer_joueurs/envoyer_notification', array('class' => 'centre margin')) ?>
					<p><input type="hidden" name="joueur_id" value="<?= $joueur->id ?>"></p>

					<p>Message <span class="pourpre">[HTML uniquement]</span> :</p>
					<textarea name="texte" id="texte" class="compte_caracteres" cols="60" rows="5" maxlength="500"></textarea>
					<p id="texte_nb_caracteres_restants" class="transparent centre">&nbsp;</p>
												
					<p><input type="submit" value="Envoyer" class="confirmation"></p>
				</form>
			</div>
		</div>

		<div class="cellule_bleu_type1 marge_haut">
			<h4>Supprimer le compte <?= profil($joueur->id, $joueur->pseudo, $joueur->rang) ?></h4>
			<div class="bloc_bleu">
				<?= form_open('staff/gerer_joueurs/supprimer_compte', array('class' => 'centre margin')) ?>
					<p><input type="hidden" name="joueur_id" value="<?= $joueur->id ?>"></p>

					<p class="rouge">Attention ! Supprimer le compte va le mettre en game over puis effacer toute trace de ce joueur de la base
						de données. Cette opération est irréversible !</p>
												
					<p><input type="submit" value="Supprimer ce compte" class="confirmation"></p>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>
