<?php $this->layout->set_title('Poster une rumeur'); ?>

<div id="communaute-rumeurs">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Poster une rumeur</h4>
		<div class="bloc_bleu">
			<!-- Règles -->
			<p>Voici les règles pour poster une rumeur</p>

			<ul>
				<li>Poster une rumeur te coûtera <?= struls($this->bouzouk->config('communaute_prix_rumeur')) ?></li>
				<li>Elle doit être validée par un modérateur/administrateur pour apparaître sur le téléscripteur</li>
				<li>Tu ne seras pas remboursé si elle est refusée</li>
			</ul>

			<p class="pourpre margin">
				Pour avoir une chance d'être validée, une rumeur doit être générale et comique et ne pas viser un bouzouk ou une entité (entreprise, clan, etc.) en particulier.
			</p>

			<!-- Formulaire -->
			<?= form_open('communaute/rumeurs') ?>
				<p class="centre">
					<label for="rumeur">Rumeur</label>
					<input type="text" name="rumeur" id="rumeur" class="compte_caracteres" maxlength="100" size="50" value="<?= set_value('rumeur') ?>">
					<input type="submit" value="Valider"><br>
					<span id="rumeur_nb_caracteres_restants" class="transparent">&nbsp;</span>
				</p>
			</form>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Mes rumeurs</h4>
		<div class="bloc_bleu rumeurs">
			<table>
				<tr>
					<th>Date</th>
					<th>Texte</th>
					<th>Statut</th>
					<th></th>
				</tr>

				<tr>
					<td colspan="4"><p class="hr"></p></td>
				</tr>
					
				<?php foreach ($rumeurs as $rumeur): ?>
					<tr>
						<td><p class="highlight"><?= bouzouk_date($rumeur->date) ?></p></td>
						<td><?= $rumeur->texte ?></td>
						<td><p class="highlight"><?= $statuts[$rumeur->statut] ?></p></td>
						<td>
							<?php if ($rumeur->statut == Bouzouk::Rumeur_Validee): ?>
								<img src="<?= img_url('succes.png') ?>" alt="Validée">
							<?php elseif ($rumeur->statut == Bouzouk::Rumeur_EnAttente): ?>
								<img src="<?= img_url('attention.png') ?>" alt="En attente">
							<?php else: ?>
								<img src="<?= img_url('echec.png') ?>" alt="Désactivée/Refusée">
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
 
