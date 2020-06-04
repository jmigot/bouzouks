 <?php $this->layout->set_title('Admin - Gestion des news'); ?>

<div id="staff-gerer_news-rediger">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Rédiger une news</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/gerer_news') ?>">Retour</a></p>

			<?= form_open_multipart('staff/gerer_news/modifier') ?>
				<div class="centre margin">
					<input type="hidden" name="news_id" value="<?= $news_id ?>">

					<!-- Titre -->
					<p class="margin">
						Titre : <input type="text" name="titre" size="40" maxlength="80" class="compte_caracteres" value="<?= $news->modification ? set_value('titre') : form_prep($news->titre) ?>"><br>
						<span id="titre_nb_caracteres_restants" class="centre transparent">&nbsp;</span>
					</p>

					<!-- Texte -->
					<p>
						Texte (<span class="pourpre">HTML autorisé</span>)<br>
						<textarea name="texte" id="texte" maxlength="4000" class="compte_caracteres" rows="20" cols="80"><?= $news->modification ? set_value('texte') : form_prep($news->texte) ?></textarea>
					</p>
					<p id="texte_nb_caracteres_restants" class="centre transparent">&nbsp;</p>
					<?= $table_smileys; ?>
				</div>

				<!-- Admin -->
				<p class="highlight">Options</p>
				<table>
					<tr>
						<td>Auteur : </td>
						<td><?= $select_auteurs ?></td>
					</tr>

					<tr>
						<td>Date de l'article : </td>
						<td>
							<input type="text" name="date_jour" id="date_jour" value="<?= mb_substr($news->date, 8, 2) ?>" size="2" maxlength="2"> /
							<input type="text" name="date_mois" value="<?= mb_substr($news->date, 5, 2) ?>" size="2" maxlength="2"> /
							<input type="text" name="date_annee" value="<?= mb_substr($news->date, 0, 4) ?>" size="4" maxlength="4">&nbsp;&nbsp;à&nbsp;&nbsp;
							<input type="text" name="date_heures" value="<?= mb_substr($news->date, 11, 2) ?>" size="2" maxlength="2"> h
							<input type="text" name="date_minutes" value="<?= mb_substr($news->date, 14, 2) ?>" size="2" maxlength="2"> min
						</td>
					</tr>

					<tr>
						<td><label for="statut">Statut : </label></td>
						<td>
							<select name="statut" id="statut">
								<option value="0"<?= $news->en_ligne ? '' : ' selected' ?>>Brouillon</option>
								<option value="1"<?= $news->en_ligne ? ' selected' : '' ?>>Publié</option>
							</select>
						</td>
					</tr>
				</table>

				<!-- Valider -->
				<p class="centre margin">
					<input type="submit" name="modifier" value="<?= $news_id != '0' ? "Modifier la news" : "Créer la news" ?>" class="confirmation">
				</p>
			</form>
		</div>
	</div>
</div>