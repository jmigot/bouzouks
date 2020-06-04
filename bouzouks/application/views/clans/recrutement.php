<?php $this->layout->set_title('Recrutement'); ?>

<div id="clans-recrutement">
	<!-- Menu -->
	<?php $this->load->view('clans/menu', array('lien' => 2)) ?>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Demandes de recrutement en attente</h4>
		<div class="bloc_bleu padd_vertical">
			<table class="tab_separ">
				<tr>
					<th>Bouzouk</th>
					<th>Date</th>
					<th>Invisible</th>
					<th>Accepter</th>
					<th>Refuser</th>
				</tr>

				<?php foreach ($joueurs_attente as $joueur): ?>
					<tr class="frameborder_bleu">
						<td><p class="tab_espace"><?= profil($joueur->id, $joueur->pseudo, $joueur->rang) ?></p></td>
						<td><?= bouzouk_datetime($joueur->date, 'court') ?></td>
						<td><p class="tab_espace"><?= $joueur->invisible ? 'oui' : 'non' ?></p></td>
						<td>
							<?= form_open('clans/accepter') ?>
								<p>
									<input type="hidden" name="type" value="<?= $clan->type ?>">
									<input type="hidden" name="joueur_id" value="<?= $joueur->id ?>">
									<input type="submit" value="Accepter">
								</p>
							</form>
						</td>
						<td>
							<?= form_open('clans/refuser') ?>
								<p>
									<input type="hidden" name="type" value="<?= $clan->type ?>">
									<input type="hidden" name="joueur_id" value="<?= $joueur->id ?>">
									<input type="submit" value="Refuser">
								</p>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

	<div class="cellule_gris_type2 marge_haut">
		<h4>Liste noire des bouzouks ne pouvant plus postuler</h4>
		<div class="bloc_gris padd_vertical">
			<table class="tab_separ">
				<tr>
					<th>Bouzouk</th>
					<th>Date</th>
					<th>Supprimer de la liste</th>
				</tr>

				<?php foreach ($joueurs_refuses as $joueur): ?>
					<tr class="frameborder_bleu">
						<td><p class="tab_espace"><?= profil($joueur->id, $joueur->pseudo, $joueur->rang) ?></p></td>
						<td><?= bouzouk_datetime($joueur->date, 'court') ?></td>
						<td>
							<?= form_open('clans/supprimer_liste_noire') ?>
								<p>
									<input type="hidden" name="type" value="<?= $clan->type ?>">
									<input type="hidden" name="joueur_id" value="<?= $joueur->id ?>">
									<input type="submit" value="Supprimer de la liste noire">
								</p>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Ajouter un bouzouk à la liste noire</h4>
		<div class="bloc_bleu padd_vertical">
			<?= form_open('clans/ajouter_liste_noire', array('class' => 'centre')) ?>
				<p class="frameborder_bleu">
					<input type="hidden" name="type" value="<?= $clan->type ?>">
					Pseudo : <input type="text" name="pseudo" maxlength="20" class="margin-mini"><br>
					<input type="submit" value="Ajouter" class="margin-mini">
				</p>
			</form>
		</div>
	</div>
</div>
