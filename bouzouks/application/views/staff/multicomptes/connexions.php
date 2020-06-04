<?php $this->layout->set_title('Administration - Multicomptes'); ?>

<div id="staff-multicomptes">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Multicomptes - Connexions</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			
			<p class="margin">Les joueurs qui se sont connectés sur la même adresse IP ces 15 derniers jours</p>
			
			<?= form_open('staff/multicomptes/exceptions_bans') ?>
				<p class="margin">
					<input type="submit" name="exception" value="Exceptions">
					<input type="submit" name="bannir" value="Bannir">
					<input type="hidden" name="redirect" value="staff/multicomptes/connexions">
				</p>
				
				<table>
					<tr>
						<th>Pseudo</th>
						<th>IP</th>
						<th>Mot de passe</th>
					</tr>

					<?php $derniere_ip = ''; ?>
					
					<?php foreach ($joueurs as $joueur): ?>
						<?php
						if ($joueur->ip != $derniere_ip):
							$derniere_ip = $joueur->ip; ?>
							<tr>
								<td colspan="3"><p class="hr"></p></td>
							</tr>
						<?php endif; ?>
						<tr>
							<td>
								<input type="checkbox" name="joueurs_ids[]" value="<?= $joueur->id ?>">
								<?= profil($joueur->id, $joueur->pseudo) ?>
							</td>
							<td class="highlight"><?= $joueur->ip ?></td>
							<td><?= $joueur->mot_de_passe ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</form>
		</div>
	</div>
</div>
