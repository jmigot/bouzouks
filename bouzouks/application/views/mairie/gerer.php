<?php
$this->layout->set_title('Gestion mairie');
$this->layout->ajouter_javascript('mairie.js');
?>

<div id="mairie-gerer">
	<?php 
		$this->load->view('mairie/menu_gestion', array('lien' => $lien));
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste()) != null)
			$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));
	?>

	<div class="cellule_bleu_type1">
		<h4>Gestion de la mairie</h4>
		<div class="bloc_bleu gestion padd_vertical">
			<?= form_open('mairie/changer_gestion') ?>
				<table class="entier tab_separ">
					<!-- Fonds de la mairie -->
					<tr>
						<td><p class="tab_espace">Fonds de la mairie</p></td>
						<td><?= struls($mairie->struls) ?></td>
					</tr>

					<!-- Salaire du maire -->
					<tr>
						<td><label for="salaire_maire" class="tab_espace">Salaire du maire</label></td>
						<td><input type="text" name="salaire_maire" id="salaire_maire" size="4" maxlength="5" value="<?= $mairie->salaire_maire ?>"> struls [<?= struls($this->bouzouk->config('mairie_salaire_max_maire')) ?> max]</td>
					</tr>

					<!-- Aide à la création d'entreprise -->
					<tr>
						<td><label for="aide_entreprise" class="tab_espace">Aide à la création d'entreprise</label></td>
						<td><input type="text" name="aide_entreprise" id="aide_entreprise" size="4" maxlength="4" value="<?= $mairie->aide_entreprise ?>"> struls [<?= struls($this->bouzouk->config('mairie_aide_entreprise_min')) ?> - <?= struls($this->bouzouk->config('mairie_aide_entreprise_max')) ?>]</td>
					</tr>

					<tr>
						<td></td>
						<td>Reste à la charge des patrons : <?= struls($this->bouzouk->config('entreprises_prix_entreprise') - $mairie->aide_entreprise) ?></td>
					</tr>
					<tr>
						<td></td>
						<td>Reste à la charge des patrons : <?= struls($this->bouzouk->config('entreprises_prix_entreprise') - $mairie->aide_entreprise) ?></td>
					</tr>
					
					<!-- Application des bonus/malus aux entreprises -->
					<tr>
						<td><label for="bonus_entreprise" class="tab_espace">Bonus rentré argent Entreprise</label></td>
						<td><input type="text" name="bonus_entreprise" id="bonus_entreprise" size="4" maxlength="2" value="<?= $mairie->bonus_entreprise ?>"> % entre [<?= ($this->bouzouk->config('entreprises_pourcent_min_bonus_rentre_argent')) ?>% min][<?= ($this->bouzouk->config('entreprises_pourcent_max_bonus_rentre_argent')) ?>% max]</td>
					</tr>
					<tr>
						<td><label for="malus_entreprise" class="tab_espace">Malus rentré argent Entreprise</label></td>
						<td><input type="text" name="malus_entreprise" id="malus_entreprise" size="4" maxlength="2" value="<?= $mairie->malus_entreprise ?>"> % entre [<?= ($this->bouzouk->config('entreprises_pourcent_min_malus_rentre_argent')) ?>% min][<?= ($this->bouzouk->config('entreprises_pourcent_max_malus_rentre_argent')) ?>% max]</td>
					</tr>

					<!-- Aide au chômage -->
					<tr>
						<td><label for="aide_chomage" class="tab_espace">Aide au chômage</label></td>
						<td><input type="text" name="aide_chomage" id="aide_chomage" size="4" maxlength="3" value="<?= $mairie->aide_chomage ?>"> struls [<?= struls($this->bouzouk->config('mairie_aide_chomage_max')) ?> max]</td>
					</tr>

					<!-- Taux de chômage -->
					<tr>
						<td><p class="tab_espace">Taux de chômage</p></td>
						<td class="pourpre"><?= $taux_chomage ?>% (<?= pluriel($nb_chomeurs, 'chômeur') ?>)</td>
					</tr>
					
					<!-- Economie -->
					<tr>
						<td><p class="tab_espace">Moyenne fortunes</p></td>
						<td class="pourpre"><?= struls($economie['moyenne']) ?></td>
					</tr>
					
					<tr>
						<td><p class="tab_espace">Médiane fortunes</p></td>
						<td class="pourpre"><?= struls($economie['mediane']) ?></td>
					</tr>	

					<tr>
						<td><p class="tab_espace">Moyenne sous la médiane</p></td>
						<td class="pourpre"><?= struls($economie['moyenne_sous_mediane']) ?></td>
					</tr>	

					<tr>
						<td><p class="tab_espace">Moyenne sur la médiane</p></td>
						<td class="pourpre"><?= struls($economie['moyenne_sur_mediane']) ?></td>
					</tr>

					<!-- Tricher aux élections -->
					<tr>
						<td><label for="tricher_elections" class="tab_espace">Tricher aux élections</label></td>
						<td><input type="checkbox" name="tricher_elections" id="tricher_elections"<?php if ($mairie->tricher_elections) echo ' checked'?>></td>
					</tr>

					<!-- Cacher salaire -->
					<tr>
						<td><label for="cacher_salaire" class="tab_espace">Cacher mon salaire</label></td>
						<td><input type="checkbox" name="cacher_salaire" id="cacher_salaire"<?php if ($mairie->cacher_salaire) echo ' checked'?>></td>
					</tr>
				</table>

				<p class="centre"><input type="submit" value="Changer la gestion"></p>
			</form>
		</div>
	</div>

	<div class="cellule_gris_type2 marge_haut">
		<h4>Gestion des shops</h4>
		<div class="bloc_gris shops padd_vertical">
			<p class="margin noir centre">
				Tu peux choisir la répartition des achats dans les shops en distribuant <span class="pourpre">18 points</span> aux magasins. Avec <span class="pourpre">6 points</span>
				à chaque magasin, les quantités seront équilibrées, avec <span class="pourpre">18 points</span> sur un seul magasin et <span class="pourpre">0 point</span> aux autres,
				ceux-ci n'auront que des quantités achetées très faibles.
			</p>

			<?= form_open('mairie/changer_shops') ?>
				<table class="tab_separ">
					<!-- Faim -->
					<tr>
						<td><p class="tab_espace">Bouffzouk : </p></td>
						<td>
							<select name="coefficient_faim">
								<?php for ($i = 0 ; $i <= 18 ; $i++): ?>
									<option value="<?= $i ?>"<?= $mairie->coefficients_achats[0] == $i ? ' selected' : '' ?>><?= pluriel($i, 'point') ?></option>
								<?php endfor; ?>
							</select><br>
						</td>
					</tr>

					<!-- Santé -->
					<tr>
						<td><p class="tab_espace">Indispenzouk : </p></td>
						<td>
							<select name="coefficient_sante">
								<?php for ($i = 0 ; $i <= 18 ; $i++): ?>
									<option value="<?= $i ?>"<?= $mairie->coefficients_achats[1] == $i ? ' selected' : '' ?>><?= pluriel($i, 'point') ?></option>
								<?php endfor; ?>
							</select><br>
						</td>
					</tr>

					<!-- Stress -->
					<tr>
						<td><p class="tab_espace">Luxezouk : </p></td>
						<td>
							<select name="coefficient_stress">
								<?php for ($i = 0 ; $i <= 18 ; $i++): ?>
									<option value="<?= $i ?>"<?= $mairie->coefficients_achats[2] == $i ? ' selected' : '' ?>><?= pluriel($i, 'point') ?></option>
								<?php endfor; ?>
							</select>
						</td>
					</tr>
				</table>

				<p class="centre"><input type="submit" value="Valider la distribution"></p>
			</form>

			<p class="hr"></p>

			<p class="margin noir centre">
				Tu peux mettre un objet de la liste suivante en promotion à <span class="pourpre">-50%</span> pour la journée<br>
				Tous les bouzouks seront avertis de la promotion.
			</p>

			<?= form_open('mairie/changer_promotion', array('class' => 'centre frameborder_bleu')) ?>
				Objet en promotion : 
				<select name="objet_id">
					<option value="">---------------</option>

					<?php foreach ($objets_promotion as $objet): ?>
						<option value="<?= $objet->id ?>"<?= $objet->id == $mairie->promotion_objet_id ? ' selected' : '' ?>><?= $objet->nom ?></option>
					<?php endforeach; ?>
				</select><br>
				<p class="pourpre centre">Attention, les entreprises produisant cet objet auront une perte de bénéfice sur la journée</p>

				<input type="submit" value="Mettre en promotion">
			</form>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Donations</h4>
		<div class="bloc_bleu donations">
			<p class="margin centre">
				Le nombre de bouzouks touchés par le don ainsi que le montant total va se rafraîchir automatiquement.<br>
				Tu as déjà donné <?= struls($total_dons) ?> dans les dernières <span class="pourpre"><?= $this->bouzouk->config('mairie_intervalle_max_dons') ?> heures</span> et la limite est de <?= struls($this->bouzouk->config('mairie_max_dons')) ?>.
			</p>

			<!-- Donner à un bouzouk -->
			<div class="frameborder_bleu moitie inline-block margin-mini">
					<p class="margin-petit">Donner à un bouzouk [<?= struls($this->bouzouk->config('mairie_don_max_bouzouk')) ?> max]</p>
					<?= form_open('mairie/donner_bouzouk') ?>
						<table>
							<tr>
								<td>Pseudo</td>
								<td>
									<?= $select_joueurs ?>
								</td>
							</tr>

							<tr>
								<td>Donner</td>
								<td><input type="text" name="montant" size="5" maxlength="5"> struls</td>
							</tr>
						</table>

						<p class="centre">
							<br><br>
							<input type="submit" value="Donner à ce bouzouk">
						</p>
					</form>
			</div>

			<!-- Donner à une catégorie de bouzouks -->
			<div class="frameborder_bleu moitie inline-block margin-mini">
					<p class="margin-petit">Donner à certains bouzouks [<?= struls($this->bouzouk->config('mairie_don_max_intervalle')) ?> max]</p>
					<?= form_open('mairie/donner_bouzouks') ?>
						<table>
							<tr>
								<td>
									Entre <input type="text" name="min_struls" id="min_struls_don_tranche" size="5" maxlength="8" value="<?= set_value('min_struls') ?>"> et
									<input type="text" name="max_struls" id="max_struls_don_tranche" size="5" maxlength="8" value="<?= set_value('max_struls') ?>"> struls<br>
									<p class="centre"><input type="checkbox" name="par_fortune" id="par_fortune"><label for="par_fortune">Par fortune (au lieu des struls)</label></p>
								</td>
							</tr>

							<tr>
								<td>Leur donner <input type="text" name="montant" id="montant_don_tranche" size="5" maxlength="5"> struls chacun</td>
							</tr>
						</table>
						<p class="centre">
							<span class="pourpre italique invisible" id="resultat_don_tranche">&nbsp;</span><br>
							<input type="submit" value="Donner à ces bouzouks">
						</p>

					</form>
			</div>

			<!-- Donner aux mendiants -->
			<div class="frameborder_bleu moitie inline-block margin-mini">
					<p class="margin-petit">Donner <?= $nb_mendiants <= 1 ? 'à' : 'aux' ?> <span id="nb_mendiants"><?= $nb_mendiants ?></span> <?= $nb_mendiants <= 1 ? 'mendiant' : 'mendiants' ?> [<?= struls($this->bouzouk->config('mairie_don_max_mendiant')) ?> max]</p>

					<?= form_open('mairie/donner_mendiants') ?>
						<p class="centre">
							Donner <input type="text" name="montant" id="montant_don_mendiants" size="5" maxlength="5"> struls chacun<br><br>
							<span class="pourpre italique invisible" id="resultat_don_mendiants">&nbsp;</span><br>
							<input type="submit" value="Donner aux mendiants">
						</p>
					</form>
			</div>

			<!-- Donner à tous les bouzouks -->
			<div class="frameborder_bleu moitie inline-block margin-mini">
					<p class="margin-petit">Donner aux <span id="nb_bouzouks"><?= $nb_joueurs ?></span> bouzouks actifs [<?= struls($this->bouzouk->config('mairie_don_max_tous')) ?> max]</p>

					<?= form_open('mairie/donner_tous') ?>
						<p class="centre">
							Donner <input type="text" name="montant" id="montant_don_bouzouks" size="5" maxlength="5"> struls chacun<br><br>
							<span class="pourpre italique invisible" id="resultat_don_bouzouks">&nbsp;</span><br>
							<input type="submit" value="Donner à tous les bouzouks">
						</p>
					</form>
			</div>
		</div>
	</div>

	<div class="cellule_gris_type2 marge_haut">
		<h4>Impôts</h4>
		<div class="bloc_gris gestion">
			<p class="mini_bloc">Les impôts sont distribués automatiquement une fois par mandat.</p>
			<p class="centre margin">
				Les impôts employés concernent ceux qui ont un job dans une entreprise.<br>
				Les 3 autres impôts seront distribués aux patrons d'entreprise en fonction du type de production.</p>

			<?= form_open('mairie/changer_impots') ?>
				<table class="entier tab_separ">
					<?php
						$impots_entreprises_min = $this->bouzouk->config('mairie_impots_entreprises_min');
						$impots_entreprises_max = $this->bouzouk->config('mairie_impots_entreprises_max');
					?>

					<!-- Impôts employés -->
					<tr>
						<td><label for="impots_employes" class="tab_espace">Impôts employés</label></td>
						<td>
							<select name="impots_employes" id="impots_employes">
							<?php for ($i = $this->bouzouk->config('mairie_impots_employes_min'); $i <= $this->bouzouk->config('mairie_impots_employes_max'); $i++): ?>
								<option value="<?= $i ?>"<?php if ($mairie->impots_employes == $i) echo ' selected'; ?>><?= $i ?> %</option>
							<?php endfor; ?>
							</select> du salaire le jour des impôts
						</td>
					</tr>

					<!-- Impôts faim -->
					<tr>
						<td><label for="impots_faim" class="tab_espace">Impôts entreprises Bouffzouk</label></td>
						<td>
							<select name="impots_faim" id="impots_faim">
							<?php for ($i = $impots_entreprises_min; $i <= $impots_entreprises_max; $i++): ?>
								<option value="<?= $i ?>"<?php if ($mairie->impots_faim == $i) echo ' selected'; ?>><?= $i ?> %</option>
							<?php endfor; ?>
							</select> des struls de l'entreprise
						</td>
					</tr>

					<!-- Impôts santé -->
					<tr>
						<td><label for="impots_sante" class="tab_espace">Impôts entreprises Indispenzouk</label></td>
						<td>
							<select name="impots_sante" id="impots_sante">
							<?php for ($i = $impots_entreprises_min; $i <= $impots_entreprises_max; $i++): ?>
								<option value="<?= $i ?>"<?php if ($mairie->impots_sante == $i) echo ' selected'; ?>><?= $i ?> %</option>
							<?php endfor; ?>
							</select> des struls de l'entreprise
						</td>
					</tr>

					<!-- Impôts stress -->
					<tr>
						<td><label for="impots_stress" class="tab_espace">Impôts entreprises Luxezouk</label></td>
						<td>
							<select name="impots_stress" id="impots_stress">
							<?php for ($i = $impots_entreprises_min; $i <= $impots_entreprises_max; $i++): ?>
								<option value="<?= $i ?>"<?php if ($mairie->impots_stress == $i) echo ' selected'; ?>><?= $i ?> %</option>
							<?php endfor; ?>
							</select> des struls de l'entreprise
						</td>
					</tr>

					<!-- Impôts lohtoh -->
					<tr>
						<td><label for="impots_lohtoh" class="tab_espace">Impôts Lohtoh</label></td>
						<td>
							<select name="impots_lohtoh" id="impots_lohtoh">
							<?php for ($i = $this->bouzouk->config('mairie_impots_lohtoh_min'); $i <= $this->bouzouk->config('mairie_impots_lohtoh_max'); $i++): ?>
								<option value="<?= $i ?>"<?php if ($mairie->impots_lohtoh == $i) echo ' selected'; ?>><?= $i ?> %</option>
							<?php endfor; ?>
							</select> de la cagnotte du Lohtoh
						</td>
					</tr>
				</table>

				<p class="centre"><input type="submit" value="Changer les impôts"></p>
			</form>
		</div>
	</div>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Taxe surprise</h4>
		<div class="bloc_bleu centre taxe">
			<p class="centre margin">
				Tu peux envoyer une taxe surprise toutes les <span class="pourpre"><?= $this->bouzouk->config('mairie_intervalle_taxes') ?>h</span><br>
				Tu pourras toujours modifier le taux et le texte jusqu'à l'envoi de la taxe (à la maintenance).
			</p>

			<?= form_open('mairie/taxe_surprise') ?>
				<p class="frameborder_bleu">
					<input type="hidden" name="taxe_id" value="<?= $taxe->id ?>">
					<label for="taux" class="margin">Taux :</label>

					<select name="taux" id="taux">
						<?php for ($i = $this->bouzouk->config('mairie_taxe_min'); $i <= $this->bouzouk->config('mairie_taxe_max'); $i++): ?>
							<option value="<?= $i ?>"<?= $taxe->modification ? set_select('taux', $i) : $taxe->taux == $i ? ' selected' : '' ?>><?= $i ?> %</option>
						<?php endfor; ?>
					</select> des struls
				</p>

				<p class="margin frameborder_bleu padd_vertical">
					Raison de la taxe: <span id="raison_nb_caracteres_restants" class="centre rond_blanc transparent">&nbsp;</span> <br>
					<textarea name="raison" maxlength="300" class="compte_caracteres" rows="7" cols="50"><?= $taxe->modification ? set_value('raison') : $taxe->raison ?></textarea>
				</p>
				<p><input type="submit" value="Envoyer la taxe"></p>
			</form>
		</div>
	</div>
</div>