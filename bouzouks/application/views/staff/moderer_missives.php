<?php $this->layout->set_title('Administration - Missives'); ?>

<div id="staff-moderer_missives">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Lire les missives</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<?= form_open('staff/moderer_missives', array('class' => 'margin centre')) ?>
				<p>
					Entre :
					<?= $select_joueurs1 ?>

					Et :
					<?= $select_joueurs2 ?>

					<input type="submit" value="Modifier les filtres">
				</p>
			</form>

			<p class="centre margin"><?= $pagination ?></p>

			<?= form_open('staff/moderer_missives/supprimer') ?>
				<!-- Supprimer -->
				<p class="gauche">
					<input type="submit" value="Supprimer" class="confirmation">
				</p>

				<table>
					<tr>
						<th>Infos</th>
						<th>Message</th>
					</tr>

					<tr>
						<td colspan="2"><p class="hr"></p></td>
					</tr>

					<?php foreach ($missives as $missive): ?>
						<tr>
							<td>
								<p class="highlight">
									<?= bouzouk_datetime($missive->date_envoi, 'court', false, true) ?><br>
									De&nbsp;&nbsp; <?= profil($missive->expediteur_id, $missive->expediteur_pseudo) ?><br>
									Pour <?= profil($missive->destinataire_id, $missive->destinataire_pseudo) ?><br>
									<input type="checkbox" id="supprimer_<?= $missive->id ?>" name="ids[]" value="<?= $missive->id ?>"><label for="supprimer_<?= $missive->id ?>">supprimer</label>
								</p>
							</td>
							<td>
								<?php
									echo couleur(form_prep($missive->objet), 'pourpre').'<br><br>';

									$texte = $missive->message;

									// On enlève les formules de politesse
									if (preg_match('#^[^\n]*\n\n(.*)\t[^\t]*$#Uis', $texte, $matches))
										$texte = $matches[1];

									echo $this->lib_parser->remplace_bbcode(nl2br(remplace_smileys(form_prep($texte))))
								?>
							</td>
						</tr>
						<tr>
							<td colspan="2"><p class="hr"></p></td>
						</tr>
					<?php endforeach; ?>
				</table>

				<!-- Supprimer -->
				<p class="droite">
					<input type="submit" value="Supprimer" class="confirmation">
				</p>
			</form>

			<p class="centre margin"><?= $pagination ?></p>
		</div>
	</div>
</div>
