<?php
$this->layout->set_title('Modération - Clans');
$this->layout->ajouter_javascript('staff/moderer_clans.js');
?>

<div id="staff-moderer_clans-index">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Modérer les clans</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<p class="margin">
				Les enchères du jour par types de clans. l'enchère la plus en haut est celle qui va remporter les enchères si personne ne surenchérit.<br><br>

				A. (Annulée) : <img src="<?= img_url('valide.png') ?>" alt="Valide" width="15"> Enchère valide | <img src="<?= img_url('invalide.png') ?>" alt="Invalide" width="15"> Enchère annulée<br>
				M. (Modérée) : <img src="<?= img_url('valide.png') ?>" alt="Valide" width="15"> Enchère modérée | <img src="<?= img_url('invalide.png') ?>" alt="Invalide" width="15"> Enchère non-modérée
				(<span class="pourpre">cliquer sur l'icône pour la modérer</span>)
			</p>

			<?php foreach (array(Bouzouk::Clans_TypeSyndicat, Bouzouk::Clans_TypePartiPolitique, Bouzouk::Clans_TypeOrganisation) as $type): ?>
				<p class="padding highlight pourpre gras">
					<?= $type == Bouzouk::Clans_TypeSyndicat ? 'Syndicats' : ($type == Bouzouk::Clans_TypePartiPolitique ? 'Partis politiques' : 'Organisations') ?>
				</p>

				<table class="margin">
					<tr>
						<th>Date</th>
						<th>Clan</th>
						<th>Action</th>
						<th>Montant</th>
						<th>A.</th>
						<th>M.</th>
					</tr>

					<?php foreach ($encheres[$type] as $enchere): ?>
						<!-- Les MDJ ne voient que les enchères à modérer -->
						<?php if ( ! $this->bouzouk->is_admin() && ! isset($enchere->parametres['texte']) && ! isset($enchere->parametres['titre'])) continue; ?>

						<tr><td colspan="6"><p class="hr"></p></td></tr>

						<tr>
							<td><p class="highlight"><?= bouzouk_datetime($enchere->date, 'court', false) ?></p></td>
							<td><?= form_prep($enchere->nom_clan) ?></td>
							<td class="pourpre"><?= $enchere->nom_action ?> <?= $this->lib_clans->parametres($enchere->parametres, true) ?></td>
							<td><p class="highlight pourpre"><?= $enchere->montant_enchere ?> p.a</p></td>
							<td><img src="<?= $enchere->annulee ? img_url('invalide.png') : img_url('valide.png') ?>" alt="Annulée" width="15"></td>
							<td><img src="<?= $enchere->moderee ? img_url('valide.png') : img_url('invalide.png') ?>" alt="Annulée" width="15" class="enchere_<?= $enchere->id ?>"></td>
						</tr>

						<?php if (isset($enchere->parametres['texte']) || isset($enchere->parametres['titre'])): ?>
							<tr class="enchere_<?= $enchere->id ?> invisible">
								<td colspan="6">
									<?= form_open('staff/moderer_clans/modifier', array('class' => 'centre')) ?>
										<p><input type="hidden" name="enchere_id" value="<?= $enchere->id ?>"></p>

										<?php if (isset($enchere->parametres['titre'])): ?>
											Titre<br>
											<input type="text" name="titre" value="<?= form_prep($enchere->parametres['titre']) ?>" size="70"><br>
										<?php endif; ?>

										<?php if (isset($enchere->parametres['texte'])): ?>
											Texte<br>
											<textarea name="texte" cols="70" rows="10"><?= form_prep($enchere->parametres['texte']) ?></textarea>
										<?php endif; ?>

										<p class="centre"><input type="submit" value="Modifier"></p>
									</form>
								</td>
							</tr>
						<?php endif; ?>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- Liste des actions lancées -->
	<?php if ($this->bouzouk->is_admin()): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Liste des 50 dernières actions lancées</h4>
			<div class="bloc_bleu">
				<table>
					<tr>
						<th>Date début</th>
						<th>Clan</th>
						<th></th>
						<th>Action</th>
						<th>Coût action</th>
						<th>Effet</th>
						<th>Actif</th>
					</tr>

					<?php foreach ($actions_lancees as $action): ?>
						<tr><td colspan="7"><p class="hr"></p></td></tr>

						<tr>
							<td><p class="highlight"><?= bouzouk_datetime($action->date_debut, 'court') ?></p></td>
							<td><?= form_prep($action->nom_clan) ?></td>
							<td><img src="<?= img_url('clans/actions/'.$action->image) ?>" alt="Image" width="45"></td>
							<td class="pourpre"><?= $action->nom ?> <?= $this->lib_clans->parametres(unserialize($action->parametres), true) ?></td>
							<td><p class="highlight"><?= $action->cout ?> p.a</p></td>
							<td><?= $action->effet == Bouzouk::Clans_EffetDirect ? 'Direct' : 'Différé' ?></td>
							<td><img src="<?= $action->statut == Bouzouk::Clans_ActionEnCours ? img_url('succes.png') : img_url('echec.png') ?>" alt="En cours" width="20"></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</div>
	<?php endif; ?>
</div>
