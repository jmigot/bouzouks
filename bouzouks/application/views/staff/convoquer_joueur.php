<?php $this->layout->set_title('Administration - Convoquer un joueur'); ?>

<div id="staff-gerer_joueurs">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Convoquer un joueur</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<?= form_open('staff/convoquer_joueur/convoquer') ?>
				<p class="centre">
					Pseudo :
					<?= $select_joueurs ?>
					<input type="submit" name="convoquer" value="Convoquer">
				</p>
			</form>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Convocations</h4>
		<div class="bloc_bleu">
			<table>
				<tr>
					<th>Date</th>
					<th>Joueur</th>
					<th>Modérateur</th>
					<th>État</th>
				</tr>

				<tr>
					<td colspan="4"><p class="hr"></p></td>
				</tr>
				
				<?php foreach ($convocations as $convocation): ?>
					<tr>
						<td class="noir"><?= bouzouk_datetime($convocation->date) ?></td>
						<td><p class="highlight centre"><?= profil($convocation->convoque_id, $convocation->convoque_pseudo, $convocation->convoque_rang) ?></p></td>
						<td><p class="highlight centre"><?= profil($convocation->moderateur_id, $convocation->moderateur_pseudo, $convocation->moderateur_rang) ?></p></td>
						<td class="centre">
							<?php if ($convocation->etat == 1): ?>
								<?= form_open('convocation/index/'.$convocation->id) ?>
									<input type="submit" name="rejoindre" value="Rejoindre">
								</form>
							<?php elseif ($convocation->etat == 0): ?>
								<img src="<?= img_url('echec.png') ?>" title="Non active" alt="Non active">
								<?= form_open('convocation/index/'.$convocation->id) ?>
									<input type="submit" name="rejoindre" value="Voir">
								</form>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<td colspan="4"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>