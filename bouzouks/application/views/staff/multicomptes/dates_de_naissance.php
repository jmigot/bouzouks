<?php $this->layout->set_title('Administration - Multicomptes'); ?>

<div id="staff-multicomptes">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Multicomptes - Dates de naissance</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<p class="margin">Les joueurs qui ont la même date de naissance</p>
			
			<?= form_open('staff/multicomptes/exceptions_bans') ?>
				<p class="margin">
					<input type="submit" name="exception" value="Exceptions">
					<input type="submit" name="bannir" value="Bannir">
					<input type="hidden" name="redirect" value="staff/multicomptes/dates_de_naissance">
				</p>
				
				<table>
					<tr>
						<th>Pseudo</th>
						<th>Date de naissance</th>
					</tr>

					<?php $derniere_date = '' ?>

					<?php foreach ($joueurs as $joueur): ?>
						<?php if ($joueur->date_de_naissance != $derniere_date): ?>
							<?php $derniere_date = $joueur->date_de_naissance; ?>
							<tr>
								<td colspan="2"><p class="hr"></p></td>
							</tr>
						<?php endif; ?>
						<tr>
							<td>
								<input type="checkbox" name="joueurs_ids[]" value="<?= $joueur->id ?>">
								<?= profil($joueur->id, $joueur->pseudo) ?>
							</td>
							<td class="highlight"><?= $joueur->date_de_naissance ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</form>
		</div>
	</div>
</div>
