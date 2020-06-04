<?php $this->layout->set_title('La mairie'); ?>

<div id="mairie-index">
	<?php
		$this->load->view('mairie/menu', array('lien'=> $lien));
	?>
	<?php 
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste()) != null)
			$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));

		if ($this->bouzouk->clans_grosse_manif_syndicale() != null)
			$this->load->view('clans/grosse_manif_syndicale');
	?>

	<div class="tableau">
		<div class="note_secretaire">
		</div>

		<!-- Maire actuel -->
		<p class="pseudo_maire">
			<?= profil($mairie->maire_id, $mairie->maire_pseudo) ?>
		</p>
		<div class="perso_maire">
			<p class="portrait_maire">
				<img src="<?= img_url_avatar(100, 100, 0, $mairie->maire_perso, $mairie->utiliser_avatar_toboz, $mairie->maire_id) ?>" alt="Le Maire Actuel">
			</p>
		</div>

		<!-- Ancien maire n-3 -->
		<div class="img_ex_maire_1">
			<p class="portrait">
				<img src="<?= img_url_avatar(50, 50, 50, $maires[3]->perso, $maires[3]->utiliser_avatar_toboz, $maires[3]->id) ?>" alt="Ancien maire">
			</p>
		</div>
		<p class="pseudo_ex_maire_1">
			<?= profil($maires[3]->id, $maires[3]->pseudo) ?>
		</p>

		<!-- Ancien maire n-2 -->
		<div class="img_ex_maire_2">
			<p class="portrait">
				<img src="<?= img_url_avatar(50, 50, 50, $maires[2]->perso, $maires[2]->utiliser_avatar_toboz, $maires[2]->id) ?>" alt="Ancien maire">
			</p>
		</div>
		<p class="pseudo_ex_maire_2">
			<?= profil($maires[2]->id, $maires[2]->pseudo) ?>
		</p>

		<!-- Ancien maire n-1 -->
		<div class="img_ex_maire_3">
			<p class="portrait">
				<img src="<?= img_url_avatar(50, 50, 50, $maires[1]->perso, $maires[1]->utiliser_avatar_toboz, $maires[1]->id) ?>" alt="Ancien maire">
			</p>
		</div>
		<p class="pseudo_ex_maire_3">
			<?= profil($maires[1]->id, $maires[1]->pseudo) ?>
		</p>
	</div>


	<div class="infos">
		<p class="image_maire">
			<img src="<?= img_url('mairie/maire.png') ?>" width="250" alt="Maire">
		</p>
		<h4>Mandat du maire</h4>
		<div class="mandat bloc_bleu">
			<table>
				<tr>
					<td><p>Maire actuel : </p></td>
					<td><?= profil($mairie->maire_id, $mairie->maire_pseudo) ?></td>
				</tr>
				<tr>
					<td><p>Suppléant du maire : </p></td>
					<td><?= profil($mairie->maire_suppleant_id, $mairie->maire_suppleant_pseudo) ?></td>
				</tr>
				<tr>
					<td><p>Fin du mandat : </p></td>
					<?php
						$duree_mandat = $this->bouzouk->config('elections_duree_candidatures') + $this->bouzouk->config('elections_duree_tour_1') +
						$this->bouzouk->config('elections_duree_tour_2') + $this->bouzouk->config('elections_duree_tour_3');
					?>
					<td><?= bouzouk_date($mairie->date_debut_election.'+'.$duree_mandat.' DAY', 'court') ?></td>
				</tr>

				<!-- Struls -->
				<tr>
					<td><p><b>Fonds de la mairie : </b></p></td>
					<td><b><?= couleur(struls($mairie->struls, false), $mairie->struls < 0 ? 'rouge' : 'pourpre') ?></b></td>
				</tr>
				<tr>
					<td><p>Découvert autorisé : </p></td>
					<td><?= struls($this->bouzouk->config('mairie_decouvert_autorise'), false) ?></td>
				</tr>
				<tr>
					<td><p>Salaire du maire : </p></td>
					<td>
						<?php if ($mairie->cacher_salaire): ?>
							<i>info cachée</i>
						<?php else: ?>
							<?= struls($mairie->salaire_maire, false) ?>
						<?php endif; ?>
					</td>
				</tr>

				<?php if ($this->bouzouk->is_journaliste()): ?>
					<tr>
					<td><p>Triche aux élections : </p></td>
						<td>
							<?php if ($mairie->tricher_elections): ?>
								<span class="gras rouge">oui</span>
							<?php else: ?>
								non
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
			</table>
		</div>
	</div>

	<div class="demi_cellule_bleu_type1 gestion">
		<h4>Gestion de la mairie</h4>
		<div class="bloc_bleu">
			<table>
				<!-- Allocations -->
				<tr>
					<td><p>Aide à la création d'entreprise : </p></td>
					<td><?= struls($mairie->aide_entreprise, false) ?></td>
				</tr>
				<tr>
					<td><p>Allocations chômage : </p></td>
					<td><?= struls($mairie->aide_chomage, false) ?> / jour</td>
				</tr>

				<tr>
					<td><p>Taux de chômage</p></td>
					<td class="pourpre"><?= $taux_chomage ?>%</td>
				</tr>

				<tr>
					<td colspan="2"><p class="hr"></p></td>
				</tr>

				<!-- Coefficients achats -->
				<tr>
					<td><p>Répartition achats Bouffzouk : </p></td>
					<td><?= round($mairie->coefficients_achats[0] * 100.0 / 6.0, 0) ?>%</td>
				</tr>
				<tr>
					<td><p>Répartition achats Indispenzouk : </p></td>
					<td><?= round($mairie->coefficients_achats[1] * 100.0 / 6.0, 0) ?>%</td>
				</tr>
				<tr>
					<td><p>Répartition achats Luxezouk : </p></td>
					<td><?= round($mairie->coefficients_achats[2] * 100.0 / 6.0, 0) ?>%</td>
				</tr>

				<tr>
					<td colspan="2"><p class="hr"></p></td>
				</tr>

				<tr>
					<td><p>Date prochain achat entreprises : </p></td>
					<td><?= bouzouk_date($mairie->date_prochain_achat, 'court') ?></td>
				</tr>
			</table>
		</div>
	</div>

	<div class="demi_cellule_bleu_type1 gestion_taxes">
		<h4>Répartition des taxes</h4>
		<div class="bloc_bleu">
			<table>
				<!-- Impôts -->
				<tr>
					<td><p>Impôts employés : </p></td>
					<td><?= $mairie->impots_employes ?>%</td>
				</tr>
				<tr>
					<td><p>Impôts Bouffzouk : </p></td>
					<td><?= $mairie->impots_faim ?>%</td>
				</tr>
				<tr>
					<td><p>Impôts Indispenzouk : </p></td>
					<td><?= $mairie->impots_sante ?>%</td>
				</tr>
				<tr>
					<td><p>Impôts Luxezouk : </p></td>
					<td><?= $mairie->impots_stress ?>%</td>
				</tr>
				<tr>
					<td><p>Impôts Lohtoh : </p></td>
					<td><?= $mairie->impots_lohtoh ?>%</td>
				</tr>

				<tr>
					<td colspan="2"><p class="hr"></p></td>
				</tr>

				<tr>
					<td><p>Date prochains impôts : </p></td>
					<td><?= bouzouk_date($mairie->date_prochain_impot, 'court') ?></td>
				</tr>
			</table>
		</div>
	</div>


	<?php if ($this->bouzouk->is_journaliste(Bouzouk::Rang_Journaliste) || $this->bouzouk->is_maire()): ?>
	<div class="cellule_gris_type2 marge_haut">
		<h4>Historique de la mairie des <?= $this->bouzouk->config('historique_mairie_duree_retention') ?> derniers jours</h4>
		<div class="bloc_gris historique">
			<p class="mini_bloc">Section visible uniquement des journalistes et du maire</p>

			<p class="centre"><?= $pagination ?></p>

			<table>
				<tr>
					<th>Infos</th>
					<th></th>
					<th>Texte</th>
				</tr>

				<?php foreach ($historique as $ligne): ?>
					<tr>
						<td><?= profil($ligne->id, $ligne->pseudo) ?><br><?= bouzouk_datetime($ligne->date, 'court') ?></td>
						<td class="espace"></td>
						<td>
							<?= $ligne->texte ?>
							<?php if ( ! $ligne->visible_journalistes): ?>
								<br><span class="bleu italique">[Magouille : caché aux journalistes]</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>

			<p class="centre"><?= $pagination ?></p>
		</div>
	</div>
	<?php endif; ?>
	
	<div class="cellule_gris_type2 marge_haut">
		<h4>Les dernières taxes surprises distribuées</h4>
		<div class="bloc_gris taxes">
			<table>
				<tr>
					<th>Infos</th>
					<th>Montant</th>
					<th>Raison</th>
				</tr>

				<?php foreach ($taxes as $taxe): ?>
					<tr>
						<td>
							Le <?= bouzouk_date($taxe->date_taxe, 'court') ?><br>
							Par <?= profil($taxe->maire_id, $taxe->pseudo) ?>
						</td>
						<td class="pourpre"><?= $taxe->taux ?>%</td>
						<td>&laquo; <?= nl2br($taxe->raison) ?> &raquo; </td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
