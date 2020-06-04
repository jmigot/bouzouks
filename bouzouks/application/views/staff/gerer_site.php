<?php $this->layout->set_title('Administration - Gestion du site'); ?>

<div id="staff-gerer_site">
	<!-- Vérifications SQL -->
	<?php if (isset($verifications)): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Résultats de la vérification</h4>
			<div class="bloc_bleu">
				<table>
					<?php foreach ($verifications as $verification): ?>
						<tr>
							<td><?= form_prep($verification['texte']) ?></td>
							<td><?= ($verification['resultat']) ? '<span class="vert">[OK]</span>' : '<span class="rouge">[ERREUR]</span>' ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</div>
	<?php endif; ?>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Gestion du site</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<table>
				<!-- Mettre à jour topics élections -->
				<?= form_open('staff/gerer_site/mettre_a_jour_elections') ?>
					<tr>
						<td class="pourpre">Ajouter les topics dans les élections</td>
						<td><input type="submit" value="Mettre à jour élections"></td>
					</tr>
				</form>

				<!-- Trouver les erreurs -->
				<?= form_open('staff/gerer_site/trouver_erreurs') ?>
					<tr>
						<td class="pourpre">Chercher les erreurs SQL</td>
						<td><input type="submit" value="Chercher erreurs SQL"></td>
					</tr>
				</form>

				<!-- Cache APC utilisateur -->
				<?= form_open('staff/gerer_site/clean_apc_user_cache') ?>
					<tr>
						<td class="pourpre">Vider le cache APC utilisateurs</td>
						<td><input type="submit" value="Vider cache utilisateurs" class="confirmation"></td>
					</tr>
				</form>
				
				<!-- Cache APC PHP-->
				<?= form_open('staff/gerer_site/clean_apc_cache') ?>
					<tr>
						<td class="pourpre">Vider le cache APC</td>
						<td><input type="submit" value="Vider cache PHP" class="confirmation"></td>
					</tr>
				</form>
				
				<!-- Déconnecter tous les joueurs -->
				<?= form_open('staff/gerer_site/deconnecter_joueurs') ?>
					<tr>
						<td class="pourpre">Déconnecter tous les joueurs</td>
						<td><input type="submit" value="Déconnecter joueurs" class="confirmation"></td>
					</tr>
				</form>

				<!-- Mettre en maintenance -->
				<?= form_open('staff/gerer_site/mettre_maintenance') ?>
					<tr>
						<td class="pourpre">Mettre le site en maintenance</td>
						<td><input type="submit" value="Mettre en maintenance" class="confirmation"></td>
					</tr>
				</form>
			</table>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Envoyer une notification à tous les joueurs</h4>
		<div class="bloc_bleu">
			<?= form_open('staff/gerer_site/envoyer_notification', array('class' => 'centre margin')) ?>
				<p>Message <span class="pourpre">[HTML uniquement]</span> :</p>
				<textarea name="texte" id="texte" class="compte_caracteres" cols="60" rows="5" maxlength="500"></textarea>
				<p id="texte_nb_caracteres_restants" class="transparent centre">&nbsp;</p>

				<p><input type="submit" value="Envoyer" class="confirmation"></p>
			</form>
		</div>
	</div>
</div>

