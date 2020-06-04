<?php $this->layout->set_title('Mes annonces'); ?>

<div id="anpe-mes_annonces">
	<!-- Menu -->
	<?php $this->load->view('anpe/menu', array('lien' => 2)) ?>

	<?php if (count($annonces_proposees) > 0): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Annonces proposées par un patron</h4>
			<div class="bloc_bleu">
				<table>
					<tr>
						<th>Entreprise</th>
						<th>Patron</th>
						<th>Salaire</th>
						<th>Prime</th>
						<th>Job</th>
						<th>Accepter</th>
					</tr>

					<tr>
						<td colspan="6"><p class="hr"></p></td>
					</tr>

					<?php foreach ($annonces_proposees as $annonce): ?>
						<tr>
							<td class="pourpre"><?= $annonce->entreprise ?></td>
							<td><?= profil($annonce->patron_id, $annonce->patron_pseudo) ?></td>
							<td><p class="highlight"><?= struls($annonce->salaire) ?></p></td>
							<td><p class="highlight"><?= struls($annonce->prime_depart) ?></p></td>
							<td class="pourpre"><?= $annonce->job ?></td>
							<td>
								<?= form_open('anpe/accepter', array('class' => 'inline-block')) ?>
									<p>
										<input type="hidden" name="annonce_id" value="<?= $annonce->id ?>">
										<input type="submit" value="Accepter">
									</p>
								</form>

								<?= form_open('anpe/refuser', array('class' => 'inline-block')) ?>
									<p>
										<input type="hidden" name="annonce_id" value="<?= $annonce->id ?>">
										<input type="submit" value="Refuser">
									</p>
								</form>
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

	<?php if (count($annonces_acceptees) > 0): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Annonces acceptées en attente</h4>
			<div class="bloc_bleu">
				<table>
					<tr>
						<th>Entreprise</th>
						<th>Patron</th>
						<th>Salaire</th>
						<th>Prime</th>
						<th>Job</th>
					</tr>

					<tr>
						<td colspan="5"><p class="hr"></p></td>
					</tr>

					<?php foreach ($annonces_acceptees as $annonce): ?>
						<tr>
							<td class="pourpre"><?= $annonce->entreprise ?></td>
							<td><?= profil($annonce->patron_id, $annonce->patron_pseudo) ?></td>
							<td><p class="highlight"><?= struls($annonce->salaire) ?></p></td>
							<td><p class="highlight"><?= struls($annonce->prime_depart) ?></p></td>
							<td class="pourpre"><?= $annonce->job ?></td>
						</tr>

						<tr>
							<td colspan="5"><p class="hr"></p></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</div>
	<?php endif; ?>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Poster une annonce</h4>
		<div class="bloc_bleu">
			<?php if (isset($mon_annonce)): ?>
				<div class="centre margin">
					<!-- Annonce en cours -->
					<p class="pourpre italique">Mon annonce en cours :</p>
					<p class="margin"><?= form_prep($mon_annonce->message) ?></p>

					<?= form_open('anpe/supprimer') ?>
						<p><input type="submit" name="supprimer" value="Supprimer"></p>
					</form>
				</div>
			<?php else: ?>
				<!-- Nouvelle annonce -->
				<?= form_open('anpe/poster', array('class' => 'centre margin')) ?>
					<p>
						Message<br>
						<textarea name="message" id="message" class="compte_caracteres" rows="5" cols="60" maxlength="250"><?= set_value('message') ?></textarea><br>
						<span id="message_nb_caracteres_restants" class="transparent">&nbsp;</span>
					</p>

					<p><input type="submit" value="Poster"></p>
				</form>
			<?php endif; ?>
		</div>
	</div>
</div>
