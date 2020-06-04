<?php $this->layout->set_title('Administration - Multicomptes'); ?>

<div id="staff-multicomptes">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Multicomptes - Mots de passes</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			
			<p class="margin">Les joueurs qui ont le même mot de passe</p>
		
			<?= form_open('staff/multicomptes/exceptions_bans') ?>
				<p class="margin">
					<input type="submit" name="exception" value="Exceptions">
					<input type="submit" name="bannir" value="Bannir">
					<input type="hidden" name="redirect" value="staff/multicomptes/mots_de_passe">
				</p>
			
				<table>
					<tr>
						<th>Pseudo</th>
						<th>Mot de passe</th>
					</tr>

					<?php $dernier_mot_de_passe = '' ?>
					
					<?php foreach ($joueurs as $joueur): ?>
						<?php if ($joueur->mot_de_passe != $dernier_mot_de_passe): ?>
							<?php $dernier_mot_de_passe = $joueur->mot_de_passe; ?>
							<tr>
								<td colspan="2"><p class="hr"></p></td>
							</tr>
						<?php endif; ?>
						<tr>
							<td>
								<input type="checkbox" name="joueurs_ids[]" value="<?= $joueur->id ?>">
								<?= profil($joueur->id, $joueur->pseudo) ?>
							</td>
							<td class="highlight"><?= $joueur->mot_de_passe ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</form>
		</div>
	</div>
</div>
