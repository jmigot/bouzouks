<?php $this->layout->set_title('Administration - Configuration du jeu'); ?>

<div id="staff-gerer_config">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Configuration du jeu</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<p class="margin">Aller directement à :</p>

			<ul>
				<?php foreach ($categories as $cle => $valeur): ?>
					<li><a href="#categorie_<?= $cle ?>"><?= $valeur ?></a></li>
				<?php endforeach; ?>
			</ul>

			<?= form_open('staff/gerer_config/modifier') ?>
				<p class="margin centre"><input type="submit" value="Modifier la config" class="confirmation"></p>

				<?php $derniere_categorie = 0; ?>

				<?php foreach ($configs as $config): ?>
					<?php if ($config->categorie > $derniere_categorie): ?>
						<?php if ($derniere_categorie > 0): ?>
							</table>
						<?php endif; ?>

						<p class="highlight" id="categorie_<?= $config->categorie ?>"><?= $categories[$config->categorie] ?></p>

						<table>

						<?php $derniere_categorie = $config->categorie; ?>
					<?php endif; ?>

					<tr>
						<td><?= $config->description ?></td>
						<td>
							<?php if (is_numeric($config->valeur)): ?>
								<input type="text" name="<?= $config->cle ?>" value="<?= $config->valeur ?>" size="4"> <span class="pourpre"><?= $config->unite ?></span>
							<?php elseif (mb_strlen($config->valeur) <= 50): ?>
								<input type="text" name="<?= $config->cle ?>" value="<?= $config->valeur ?>" size="30" class="gauche">
							<?php else: ?>
								<textarea name="<?= $config->cle ?>" cols="50" rows="5"><?= $config->valeur ?></textarea>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<td colspan="2"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>

				</table>

				<p class="margin centre"><input type="submit" value="Modifier la config" class="confirmation"></p>
			</form>
		</div>
	</div>
</div>

