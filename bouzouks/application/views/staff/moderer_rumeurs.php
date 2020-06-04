<?php $this->layout->set_title('Modération - Rumeurs'); ?>

<div id="staff-moderer_rumeurs">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Modérer les rumeurs</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			
			<p class="margin">
				Une rumeur refusée sera supprimée <span class="pourpre"><?= $this->bouzouk->config('communaute_delai_rumeurs_refusees') ?> jours</span> après sa date d'envoi. Une rumeur désactivée sera gardée en mémoire mais jamais affichée sur le téléscripteur.
			</p>

			<p class="centre margin"><?= $pagination ?></p>

			<table>
				<tr>
					<th>Auteur</th>
					<th>Date</th>
					<th>Statut</th>
					<th>Modifier</th>
					<th>Publiée ?</th>
				</tr>

				<tr>
					<td colspan="5"><p class="hr"></p></td>
				</tr>

				<?php foreach ($rumeurs as $rumeur): ?>
					<?= form_open('staff/moderer_rumeurs/modifier/'.$offsetpost) ?>
						<!-- Infos -->
						<tr>
							<td><?= profil($rumeur->auteur_id, $rumeur->auteur_pseudo) ?></td>
							<td><p class="highlight"><?= bouzouk_date($rumeur->date, 'court', false) ?></p></td>
							<td>
								<input type="hidden" name="rumeur_id" value="<?= $rumeur->id ?>">
								<select name="statut" >
									<option value="<?= Bouzouk::Rumeur_EnAttente ?>"<?php if ($rumeur->statut == Bouzouk::Rumeur_EnAttente) echo ' selected' ?>>En attente</option>
									<option value="<?= Bouzouk::Rumeur_Refusee ?>"<?php if ($rumeur->statut == Bouzouk::Rumeur_Refusee) echo ' selected' ?>>Refusée</option>
									<option value="<?= Bouzouk::Rumeur_Validee ?>"<?php if ($rumeur->statut == Bouzouk::Rumeur_Validee) echo ' selected' ?>>Validée</option>
									<option value="<?= Bouzouk::Rumeur_Desactivee ?>"<?php if ($rumeur->statut == Bouzouk::Rumeur_Desactivee) echo ' selected' ?>>Désactivée</option>
								</select>
							</td>
							<td><input type="submit" value="Modifier"></td>
							<td>
								<p>
									<?php if ($rumeur->statut == Bouzouk::Rumeur_EnAttente): ?>
										<img src="<?= img_url('attention.png') ?>" alt="En attente">
									<?php elseif ($rumeur->statut == Bouzouk::Rumeur_Validee): ?>
										<img src="<?= img_url('succes.png') ?>" alt="Valide">
									<?php else: ?>
										<img src="<?= img_url('echec.png') ?>" alt="Invalide">
									<?php endif; ?>
								</p>
							</td>
						</tr>

						<!-- Texte -->
						<tr>
							<td colspan="5"><input type="text" name="texte" maxlength="100" value="<?= form_prep($rumeur->texte) ?>"></td>
						</tr>
					</form>

					<tr>
						<td colspan="5"><p class="hr"></p></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<p class="centre margin"><?= $pagination ?></p>
		</div>
	</div>
</div>



