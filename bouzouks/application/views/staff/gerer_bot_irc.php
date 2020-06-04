<?php $this->layout->set_title('Administration - Gestion du bot IRC'); ?>

<div id="staff-gerer_bot_irc">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Gestion du bot IRC</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<!-- Etat du bot -->
			<div class="margin centre">
				Etat du bot IRC : <?= $etat ?><br>

				<!-- Démarrer/Arrêter -->
				<?= form_open('staff/gerer_bot_irc/demarrer', array('class' => 'inline-block')) ?>
					<p><input type="submit" value="Démarrer" class="confirmation"></p>
				</form>

				<?= form_open('staff/gerer_bot_irc/arreter', array('class' => 'inline-block')) ?>
					<p><input type="submit" value="Arrêter" class="confirmation"></p>
				</form>
			</div>

			<p class="highlight centre pourpre">Modifier la configuration du bot</p>
			
			<?= form_open('staff/gerer_bot_irc/modifier_config') ?>
				<p class="margin centre"><input type="submit" value="Modifier la config" class="confirmation"></p>

				<?php foreach ($configs as $config): ?>
					<p class="centre margin">
						<span class="pourpre gras"><?= $config->description ?></span><br>

						<?php if (is_numeric($config->valeur)): ?>
							<input type="text" name="<?= $config->cle ?>" value="<?= $config->valeur ?>" size="4"> <span class="pourpre"><?= $config->unite ?></span>
						<?php elseif (mb_strlen($config->valeur) <= 50): ?>
							<input type="text" name="<?= $config->cle ?>" value="<?= $config->valeur ?>" size="30" class="gauche">
						<?php else: ?>
							<textarea name="<?= $config->cle ?>" cols="80" rows="15"><?= $config->valeur ?></textarea>
						<?php endif; ?>
					</p>

					<p class="hr"></p>
				<?php endforeach; ?>

				<p class="margin centre"><input type="submit" value="Modifier la config" class="confirmation"></p>
			</form>
		</div>
	</div>
</div>


 
