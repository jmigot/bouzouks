<?php
$this->layout->set_title('Recrutement');
$this->layout->ajouter_javascript('recrutement.js');
?>

<div id="recrutement-lister">
	<!-- Menu -->
	<?php $this->load->view('entreprises/menu', array('lien' => 2)) ?>

	<?php if (count($annonces_proposees) > 0): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Annonces de recrutement</h4>
			<div class="bloc_bleu liste">
				<?= form_open('recrutement/retirer') ?>
					<table>
						<tr>
							<th>Job proposé</th>
							<th>Salaire</th>
							<th>Prime départ</th>
							<th>Message</th>
							<th><p><input type="checkbox" id="case_supprimer_tous"></p></th>
						</tr>

						<tr>
							<td colspan="5"><p class="hr"></p></td>
						</tr>

						<?php foreach ($annonces_proposees as $annonce): ?>
							<tr>
								<td><?= $annonce->job ?></td>
								<td><p class="highlight"><?= $annonce->salaire ?> struls</p></td>
								<td><p class="highlight"><?= pluriel($annonce->prime_depart, 'strul') ?></p></td>
								<td><a href="#" class="<?= $annonce->id ?>">Afficher</a></td>
								<td><p><input type="checkbox" name="annonces_ids[]" value="<?= $annonce->id ?>"></p></td>
							</tr>

							<tr>
								<td></td>
								<td colspan="3" class="message">
									<div class="invisible texte_<?= $annonce->id ?>">
										<p>&laquo; <?= nl2br(form_prep($annonce->message)) ?> &raquo;</p>
									</div>
								</td>
								<td></td>
							</tr>

							<tr>
								<td colspan="5"><p class="hr"></p></td>
							</tr>
						<?php endforeach; ?>
					</table>

					<p class="droite"><input type="submit" value="Retirer"></p>
				</form>
			</div>
		</div>
	<?php endif; ?>

	<?php if (count($annonces_acceptees) > 0): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Annonces acceptées par des chômeurs</h4>
			<div class="bloc_bleu annonces">
				<table>
					<tr>
						<th>Joueur</th>
						<th>Job proposé</th>
						<th>Salaire</th>
						<th>Prime</th>
						<th>Message</th>
						<th>Action</th>
					</tr>

					<tr>
						<td colspan="6"><p class="hr"></p></td>
					</tr>

					<?php foreach ($annonces_acceptees as $annonce): ?>
						<tr>
							<td><?= profil($annonce->joueur_id, $annonce->pseudo) ?></td>
							<td><?= $annonce->job ?></td>
							<td><p class="highlight"><?= struls($annonce->salaire) ?></p></td>
							<td><p class="highlight"><?= struls($annonce->prime_depart) ?></p></td>
							<td><a href="#" class="<?= $annonce->id ?>">Afficher</a></td>
							<td>
								<?= form_open('recrutement/accepter', array('class' => 'inline-block')) ?>
									<p>
										<input type="hidden" name="annonce_id" value="<?= $annonce->id ?>">
										<input type="submit" value="Accepter">
									</p>
								</form>

								<?= form_open('recrutement/refuser', array('class' => 'inline-block')) ?>
									<p>
										<input type="hidden" name="annonce_id" value="<?= $annonce->id ?>">
										<input type="submit" value="Refuser">
									</p>
								</form>
							</td>
						</tr>

						<tr>
							<td></td>
							<td></td>
							<td colspan="3" class="message">
								<div class="invisible texte_<?= $annonce->id ?>">
									<p>&laquo; <?= nl2br(form_prep($annonce->message)) ?> &raquo;</p>
								</div>
							</td>
						</tr>


						<tr>
							<td colspan="6"><p class="hr"></p></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</div>
	<?php endif; ?>

	<?php if (count($annonces_chomeurs) > 0): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Annonces proposées à des chômeurs</h4>
			<div class="bloc_bleu annonces liste">
				<table>
					<tr>
						<th>Joueur</th>
						<th>Job</th>
						<th>Salaire</th>
						<th>Prime</th>
					</tr>

					<tr>
						<td colspan="4"><p class="hr"></p></td>
					</tr>

					<?php foreach ($annonces_chomeurs as $annonce): ?>
						<tr>
							<td><?= profil($annonce->chomeur_id, $annonce->chomeur_pseudo) ?></td>
							<td><?= $annonce->job ?></td>
							<td><p class="highlight"><?= struls($annonce->salaire) ?></p></td>
							<td><p class="highlight"><?= struls($annonce->prime_depart) ?></p></td>
						</tr>

						<tr>
							<td colspan="4"><p class="hr"></p></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</div>
	<?php endif; ?>
	
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Poster une annonce</h4>
		<div class="bloc_bleu poster">
			<?= form_open('recrutement/poster') ?>
				<table>
					<tr>
						<td><label for="job_id" class="highlight">Job :</label></td>
						<td>
							<select name="job_id" id="job_id">
								<option value=""></option>
								<?php foreach ($jobs as $job): ?>
									<option value="<?= $job->id ?>"><?= $job->nom ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<td><label for="salaire" class="highlight">Salaire : </label></td>
						<td><input type="text" name="salaire" id="salaire" size="4" value="<?= set_value('salaire') ?>"> struls [<?= struls($this->bouzouk->config('entreprises_salaire_min_employe')) ?> - <?= struls($this->bouzouk->config('entreprises_salaire_max_employe')) ?>]</td>
					</tr>

					<tr>
						<td><label for="prime_depart" class="highlight">Prime d'incompétence :</label></td>
						<td><input type="text" name="prime_depart" id="prime_depart" size="4" value="<?= set_value('prime_depart') ?>"> struls [<?= struls($this->bouzouk->config('entreprises_prime_max')) ?> max]</td>
					</tr>

					<tr>
						<td><label for="message" class="highlight">Message :</label></td>
						<td>
							<textarea name="message" id="message" class="compte_caracteres" rows="5" cols="30" maxlength="250"><?= set_value('message') ?></textarea><br>
							<p id="message_nb_caracteres_restants" class="transparent">&nbsp;</p>
						</td>
					</tr>

					<tr>
						<td><label for="nombre" class="highlight">Nombre d'annonces : </label></td>
						<td>
							<select name="nombre" id="nombre">
								<option value="1" selected>1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
							</select>
						</td>
					</tr>
				</table>

				<p class="centre"><input type="submit" value="Poster"></p>
			</form>
		</div>
	</div>
</div>
