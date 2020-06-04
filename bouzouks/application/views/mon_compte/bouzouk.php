<?php
$this->layout->set_title('Gestion de mon bouzouk');
$this->layout->ajouter_javascript('mon_compte.js');
?>

<div id="mon_compte-index">
	<!-- Menu -->
	<?php $this->load->view('mon_compte/menu', array('lien' => 2)) ?>

	<?php if ($this->session->userdata('statut') == Bouzouk::Joueur_Actif): ?>
		<div class="cellule_gris_type1 marge_haut">
			<h4>Changer mon bouzouk</h4>
			<div class="bloc_gris padd_vertical">
				<div class="changer_bouzouk">
					<?= form_open('mon_compte/changer_bouzouk') ?>
						<div class="frameborder_bleu marge_bas inline-block moitie">
									<p class="rond_blanc margin-petit padd_vertical">
										Changer de sexe : <?= struls($this->bouzouk->config('mon_compte_struls_changer_sexe')) ?><br>
										Changer de bouzouk : <?= struls($this->bouzouk->config('mon_compte_struls_changer_perso')) ?>
									</p>

									<!-- Sexe du bouzouk -->
									<p class="centre margin">
										<?php
											$checked_male = ($this->session->userdata('sexe') == 'male') ? ' checked' : '';
											$checked_femelle = ($this->session->userdata('sexe') == 'femelle') ? ' checked' : '';
										?>
										<input type="radio" name="sexe" id="sexe_male" value="male"<?= $checked_male ?>><label for="sexe_male">Mâle</label>
										<input type="radio" name="sexe" id="sexe_femelle" value="femelle"<?= $checked_femelle ?>><label for="sexe_femelle">Femelle</label>
									</p>

									<!-- Choix du bouzouk -->
									<?php
									// ----- Event Bouf'tête ----------
									if($this->bouzouk->est_infecte($this->session->userdata('id'))) :
										$perso = str_replace('zombi/', '', $this->session->userdata('perso'));
									elseif($this->bouzouk->est_maudit_mlbobz($this->session->userdata('id'))):
										// Event RP Zoukette
										$perso = str_replace('rp_zoukette/', '', $this->session->userdata('perso'));
									else :
										$perso = $this->session->userdata('perso');
									endif;
									?>
									<?= select_bouzouk($this->session->userdata('sexe'), $perso, $perso) ?>
						</div>

						<div class="inline-block moitie">
							<!-- Date de naissance -->
									<p class="rond_blanc padd_vertical">Changer la date de naissance : <?= struls($this->bouzouk->config('mon_compte_struls_changer_naissance')) ?></p>
									<p class="frameborder_bleu centre padd_vertical">
										<?php
										$date = explode('-', $this->session->userdata('date_de_naissance'));
										$jour = $date[2];
										$mois = $date[1];
										$annee = $date[0];
										?>
										<input type="text" class="centre" name="jour" value="<?= $jour ?>" size="2" maxlength="2" placeholder="jour"> /
										<input type="text" class="centre" name="mois" value="<?= $mois ?>" size="2" maxlength="2" placeholder="mois"> /
										<input type="text" class="centre" name="annee" value="<?= $annee ?>" size="4" maxlength="4" placeholder="année">
									</p>
									<p class="frameborder_bleu marge_bas centre">
										<em>Cette information est gardée confidentielle, conformément à la loi sur la protection des données</em>
									</p>

							<!-- Adresse -->
									<p class="rond_blanc marge_haut padd_vertical">Changer l'adresse : <?= struls($this->bouzouk->config('mon_compte_struls_changer_adresse')) ?></p>
									<p class="frameborder_bleu centre padd_vertical"><input type="text" name="adresse" maxlength="50" size="30" value="<?= form_prep($this->session->userdata('adresse')) ?>"><p>
									<p class="frameborder_bleu marge_bas centre">Cette adresse sera visible sur les missives envoyées/reçues</p>

							<!-- Utiliser avatar tobozon -->
									<p class="rond_blanc padd_vertical">Avatar personnalisé : gratuit (si si)</p>

									<?php if ($this->session->userdata('interdit_avatar')): ?>
									<p class="frameborder_bleu centre padd_vertical rouge">Tu as été interdit d'avatar personnalisé par un modérateur ou un administrateur</p>
									<?php else: ?>
									<p class="frameborder_bleu centre padd_vertical"><input type="checkbox" id="utiliser_avatar_toboz" name="utiliser_avatar_toboz"<?= $utiliser_avatar_toboz ? ' checked' : '' ?>><label for="utiliser_avatar_toboz">Utiliser mon avatar Toboz sur le site</label></p>
									<?php endif; ?>
						</div>

						<!-- Commentaire -->
								<p class="margin-petit">Changer le commentaire de votre fiche profil: <?= struls($this->bouzouk->config('mon_compte_struls_changer_commentaire')) ?></p>
							<div class="frameborder_bleu">
								<div class="boutons margin-petit"><?= $this->lib_parser->bbcode('commentaire') ?></div>

								<textarea name="commentaire" id="commentaire" class="compte_caracteres margin" rows="15" maxlength="1000" placeholder="Optionnel"><?= form_prep($this->session->userdata('commentaire')) ?></textarea>
								<p id="commentaire_nb_caracteres_restants" class="centre transparent">&nbsp;</p>
								<p class="margin pourpre">Ce commentaire sera visible sur ton profil bouzouk</p>
							</div>
						
						<!-- Valider -->
						<div class="clearfloat margin">
							<p class="centre"><input type="submit" value="Valider la modification du profil"></p>
						</div>
					</form>
				</div>
			</div>

			<div class="previsualiser_commentaire invisible">
				<div class="cellule_bleu_type1 marge_haut">
					<h4>Prévisualisation commentaire</h4>
					<div class="bloc_bleu">
						<p class="margin centre"><input type="button" class="fermer_previsualisation" value="Fermer"></p>
						<p class="hr"></p>
						<div class="texte margin"></div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
