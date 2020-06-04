<?php $this->layout->set_title('Administration - Multicomptes'); ?>

<div id="staff-multicomptes">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Multicomptes - Le Pioupiouk Chercheur</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			
			<p class="margin">Le <span class="gras pourpre">Pioupiouk Chercheur</span></p>
			
			<?= form_open('staff/multicomptes/pioupiouk_chercheur') ?>
				<p class="margin">
					<input type="checkbox" name="ip_inscription" id="ip_inscription" value="0" <?= set_checkbox('ip_inscription', '0') ?>><label for="ip_inscription">Même IP d'inscription</label><br>
					<!-- <input type="checkbox" name="ip_connexion" id="ip_connexion"><label for="ip_connexion">Même IP de connexion</label><br> -->
					<input type="checkbox" name="mot_de_passe" id="mot_de_passe" value="0" <?= set_checkbox('mot_de_passe', '0') ?>><label for="mot_de_passe">Même mot de passe</label><br>
					<input type="checkbox" name="date_de_naissance" id="date_de_naissance" value="0" <?= set_checkbox('date_de_naissance', '0') ?>><label for="date_de_naissance">Même date de naissance</label><br><br>

					<input type="checkbox" name="exceptions" id="exceptions" value="0" <?= set_checkbox('exceptions', '0') ?>><label for="exceptions">Inclure les exceptions</label><br><br>

					<input type="submit" value="Chercher">
				</p>
			</form>
		</div>
	</div>

	<?php if (count($joueurs) > 0): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Multicomptes - <?= pluriel(count($joueurs), 'résultat') ?></h4>
			<div class="bloc_bleu">
				<?= form_open('staff/multicomptes/exceptions_bans') ?>
					<p class="margin">
						<input type="submit" name="exception" value="Exceptions">
						<input type="submit" name="bannir" value="Bannir">
						<input type="hidden" name="redirect" value="staff/multicomptes/pioupiouk_chercheur">
					</p>
					
					<table>
						<tr>
							<th>Pseudo</th>
							<th>IP inscription</th>
							<th>Date de naissance</th>
							<th>Mot de passe</th>
						</tr>

						<?php $derniers = array('ip_inscription' => '', 'mot_de_passe' => '', 'date_de_naissance' => ''); ?>

						<?php foreach ($joueurs as $joueur): ?>
							<?php
							$changement = false;

							foreach (array_keys($derniers) as $cle)
							{
								if ($this->input->post($cle) !== false && $joueur->{$cle} != $derniers[$cle])
								{
									foreach (array_keys($derniers) as $cle)
										$derniers[$cle] = $joueur->{$cle};
									?>

									<tr>
										<td colspan="4"><p class="hr"></p></td>
									</tr>
							<?php
									break;
								}
							}
							?>
							
							<tr>
								<td>
									<input type="checkbox" name="joueurs_ids[]" value="<?= $joueur->id ?>">
									<?= profil($joueur->id, $joueur->pseudo) ?><br>
								</td>
								<td><p class="highlight"><?= $joueur->ip_inscription ?></p></td>
								<td><p class="highlight"><?= $joueur->date_de_naissance ?></p></td>
								<th><?= mb_substr($joueur->mot_de_passe, 0, 35) ?>...</td>
							</tr>
						<?php endforeach; ?>
					</table>
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>
