<?php $this->layout->set_title('Administration - Multicomptes'); ?>

<div id="staff-multicomptes-inscriptions">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Multicomptes - Inscriptions</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			
			<p class="margin">Les joueurs qui se sont inscrits sur la même adresse IP</p>
			
			<?= form_open('staff/multicomptes/exceptions_bans') ?>
				<p class="margin">
					<input type="submit" name="exception" value="Exceptions">
					<input type="submit" name="bannir" value="Bannir">
					<input type="hidden" name="redirect" value="staff/multicomptes/inscriptions">
				</p>
				
				<table>
					<tr>
						<th>Pseudo</th>
						<th>IP</th>
						<th>Date inscription / Mot de passe</th>
					</tr>

					<?php $derniere_ip = '' ?>

					<?php foreach ($joueurs as $joueur): ?>
						<?php if ($joueur->ip != $derniere_ip): ?>
							<?php $derniere_ip = $joueur->ip; ?>
							<tr>
								<td colspan="3"><p class="hr"></p></td>
							</tr>
						<?php endif; ?>
						<tr>
							<td>
								<input type="checkbox" name="joueurs_ids[]" value="<?= $joueur->id ?>">
								<?= profil($joueur->id, $joueur->pseudo) ?><br>
								<?= $joueur->email ?>
							</td>
							<td class="highlight"><?= $joueur->ip ?></td>
							<td>
								<span class="pourpre"><?= bouzouk_datetime($joueur->date_inscription, 'court', false) ?></span><br>
								<?= $joueur->mot_de_passe ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			</form>
		</div>
	</div>
</div>
