<?php
$this->layout->set_title('Rédiger un article');
$this->layout->ajouter_javascript('gazette.js');
?>

<div id="gazette-rediger">
	<!-- Menu -->
	<?php $this->load->view('gazette/menu', array('lien' => 2, 'article_id' => $article_id)) ?>

	<div class="cellule_bleu_type1">
		<h4>Rédiger un article pour la gazette</h4>
		<div class="bloc_bleu padd_vertical">
			<?php if ($modification): ?>
				<div class="centre margin">
					<?= form_open('', array('class' => 'mutex')) ?>
						<p class="texte">
							<?php if (isset($article->mutex_auteur_id)): ?>
								<?= $article->mutex_auteur_id != $this->session->userdata('id') ? '<span class="rouge gras">' : '' ?>
									Cet article est en cours d'édition par <?= profil($article->mutex_auteur_id, $article->mutex_pseudo, $article->mutex_rang) ?>
								<?= $article->mutex_auteur_id != $this->session->userdata('id') ? '</span>' : '' ?>
							<?php else: ?>
								Personne ne rédige cet article en ce moment
							<?php endif; ?>
						</p>
						<p><input type="submit" name="mutex" value="<?= isset($article->mutex_auteur_id) && $article->mutex_auteur_id == $this->session->userdata('id') ? "Déverrouiller l'article" : "Verrouiller l'article" ?>"></p>
					</form>
				</div>

				<p class="hr"></p>
			<?php endif; ?>

			<?php $classe = ( ! $modification || ($article->mutex_auteur_id == $this->session->userdata('id'))) ? '' : ' invisible'; ?>
			<?= form_open_multipart('gazette/modifier', array('class' => 'article'.$classe)) ?>
				<div class="centre">
					<input type="hidden" name="article_id" value="<?= $article_id ?>">

					<!-- Titre -->
					<p class="frameborder_bleu padd_vertical marge_bas">
						Titre : <input type="text" name="titre" size="40" maxlength="80" class="compte_caracteres" value="<?= $article->modification ? set_value('titre') : form_prep($article->titre) ?>"><br>
						<span id="titre_nb_caracteres_restants" class="centre transparent">&nbsp;</span>
					</p>

					<!-- Résumé -->
					<p class="frameborder_bleu padd_vertical marge_bas">
						Résumé<br>
						<textarea name="resume" id="resume" maxlength="250" class="compte_caracteres" rows="5" cols="80"><?= $article->modification ? set_value('resume') : form_prep($article->resume) ?></textarea>
					</p>

					<!-- Texte -->
					<p class="frameborder_bleu padd_vertical">
						Texte<br>
						<textarea name="texte" id="texte" maxlength="5000" class="compte_caracteres" rows="40" cols="80"><?= $article->modification ? set_value('texte') : form_prep($article->texte) ?></textarea>
					</p>
					<p id="texte_nb_caracteres_restants" class="centre transparent">&nbsp;</p>

					<div class="margin">
						<p class="clearfloat entier highlight">Options de BBCode</p>
						<table>
							<tr>
								<td><p><input type="button" name="image" value="Image"><?= $this->lib_parser->bbcode('texte') ?></p></td>
							</tr>
						</table>
						<p class="clearfloat entier highlight">Ajouter le tag d'un pseudo</p>
						<div class="frameborder_bleu">
						<table>
							<tr>
								<td><p><?= $select_ajout ?>
							<input type="button" class="ajouter_pseudo" value="Ajouter"></p></td>
							</tr>
						</table>
						</div>
					</div>				
				</div>

				<?php if ($this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef)): ?>
					<!-- Admin -->
					<div class="margin">
						<p class="highlight centre">Options du rédacteur en chef</p>
						<table class="tab_separ marge_haut">
							<tr class="frameborder_bleu">
								<td>Auteur : </td>
								<td><?= $select_auteurs ?></td>
							</tr>

							<tr class="frameborder_bleu">
								<?php if ($article->xp_distribuee == 0 || $this->bouzouk->is_admin()): ?>
									<td><label for="experience_publication">Donner les <span class="color">+<?= $this->bouzouk->config('gain_xp_publication_gazette') ?> d'xp</span> à l'auteur</label></td>
									<td><input type="checkbox" name="experience_publication" id="experience_publication" value="0" <?= set_checkbox('experience_publication', '0') ?>><?= $article->xp_distribuee > 0 ? '<span class="pourpre">(déjà donnée '.$article->xp_distribuee.' fois)' : '' ?></span></td>
								<?php endif; ?>
							</tr>

							<tr class="frameborder_bleu">
								<?php if ($this->bouzouk->is_admin()): ?>
									<td><label for="prevenir_joueurs">Prévenir les joueurs cités par missive : </label></td>
									<td><input type="checkbox" name="prevenir_joueurs" id="prevenir_joueurs" value="0" <?= set_checkbox('prevenir_joueurs', '0') ?>></td>
								<?php endif; ?>
							</tr>

							<tr class="frameborder_bleu">
								<td>Date de l'article : </td>
								<td>
									<input type="text" name="date_jour" id="date_jour" value="<?= mb_substr($article->date, 8, 2) ?>" size="2" maxlength="2"> /
									<input type="text" name="date_mois" value="<?= mb_substr($article->date, 5, 2) ?>" size="2" maxlength="2"> /
									<input type="text" name="date_annee" value="<?= mb_substr($article->date, 0, 4) ?>" size="4" maxlength="4">&nbsp;&nbsp;à&nbsp;&nbsp;
									<input type="text" name="date_heures" value="<?= mb_substr($article->date, 11, 2) ?>" size="2" maxlength="2"> h
									<input type="text" name="date_minutes" value="<?= mb_substr($article->date, 14, 2) ?>" size="2" maxlength="2"> min
								</td>
							</tr>

							<tr class="frameborder_bleu">
								<td><label for="statut">Statut : </label></td>
								<td>
									<select name="statut" id="statut">
										<option value="<?= Bouzouk::Gazette_Brouillon ?>"<?= $article->en_ligne == Bouzouk::Gazette_Brouillon ? ' selected' : '' ?>>Brouillon</option>
										<option value="<?= Bouzouk::Gazette_Publie ?>"<?= $article->en_ligne == Bouzouk::Gazette_Publie ? ' selected' : '' ?>>Publié</option>
										<option value="<?= Bouzouk::Gazette_Refuse ?>"<?= $article->en_ligne == Bouzouk::Gazette_Refuse ? ' selected' : '' ?>>Refusé</option>
									</select>
								</td>
							</tr>
						</table>
					</div>
				<?php endif; ?>

				<!-- Valider -->
				<div class="margin">
					<p class="highlight centre">Validation</p>
					<p class="frameborder_bleu centre">
						<?php if ($article_id != '0'): ?>
							<label for="commentaire">Commentaire de modification <span class="pourpre">[5 caractères min.]</span>: </label><br>
							<textarea name="commentaire" id="commentaire" maxlength="300" rows="4" cols="50" class="message compte_caracteres"><?= set_value('commentaire') ?></textarea><br>
							<span id="commentaire_nb_caracteres_restants" class="transparent centre">&nbsp;</span><br>
						<?php endif; ?>
						<input type="submit" name="modifier" value="<?= $article_id != '0' ? "Modifier l'article" : "Créer l'article" ?>">
					</p>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Prévisualisation -->
<div id="popup" class="invisible">
</div>
