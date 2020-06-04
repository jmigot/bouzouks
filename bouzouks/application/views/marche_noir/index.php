<?php $this->layout->set_title('Le Marché Noir'); ?>

<div id="marche_noir-index">
	<div class="image_vendeur">
	</div>

	<?php 
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste()) != null)
			$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));
	?>

	<?php
		// ---------- Hook clans ----------
		// Corruption à agent (Struleone)
		if ($this->bouzouk->clans_corruption_a_agent()):
	?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Corruption à agent</h4>
			<div class="bloc_bleu">
				<p class="margin noir centre">
					Grâce à la <span class="pourpre">Corruption à agent</span>, tu as 50% de chances en moins de te faire attraper par la bouzopolice<br>
					Si tu te fais attraper, tu perdras <span class="pourpre">-<?= 3 * $this->bouzouk->config('marche_noir_perte_xp_achat_police') ?> xp</span> au lieu de <span class="pourpre">-<?= $this->bouzouk->config('marche_noir_perte_xp_achat_police') ?> xp</span> 
				</p>
			</div>
		</div>
	<?php endif; ?>
	<?php
		// ---------- Hook clans ----------
		// Concurrence gênante (Organsation)
		if ($this->bouzouk->clans_concurrence_genante()):
	?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Concurrence gênante</h4>
			<div class="bloc_bleu">
				<p class="margin noir centre">
					A cause d'une <span class="pourpre">Concurrence gênante</span> lancée sur toi, tous tes objets en vente ne le sont plus pendant <span class="pourpre">24h</span>.<br>
					Tous les autres bouzouks ne voient donc plus tes ventes. Elles seront remise automatiquement au bout de ces 24h.
				</p>
			</div>
		</div>
	<?php endif; ?>
	
	<!-- Liste des objets pubs -->
	<?php foreach ($objets_pub as $objet_pub): ?>
	<div class="objet_pub">
		<p class="nom_objet"><?= $objet_pub->quantite ?> <?= $objet_pub->nom ?><p>
		<div class="detail_obj bloc_bleu">
			<p class="bleu">Péremption : <?= $objet_pub->peremption ?></p>
			<p>Prix officiel: <?= $objet_pub->prix_normal ?> $</p>
		</div>
		<p class="prix_objet"><?= $objet_pub->prix ?> $<p>
		<?php if ($this->bouzouk->est_infecte($objet_pub->joueur_id)) $objet_pub->perso = 'zombi/'.$objet_pub->perso ; // --------- Event RP Zombies ------------ ?>
		<p class="image_joueur"><img src="<?= img_url(avatar($objet_pub->faim, $objet_pub->sante, $objet_pub->stress, $objet_pub->perso)) ?>" height="65" alt="Image perso"></p>
		<div class="infos_vente">
			<p class="pseudo"><?= profil($objet_pub->joueur_id, $objet_pub->vendeur) ?><p>
			<p class="image"><img src="<?= img_url($objet_pub->image_url) ?>" alt="<?= $objet_pub->nom ?>"><p>
		</div>
		<div class="formulaire">
			<?= form_open('marche_noir/acheter/'.$objet_pub->objet_id, array('class' => 'lien')) ?>
				<p>Quantité  
					<input type="hidden" name="vente_id" value="<?= $objet_pub->id_vente ?>">
					<select name="quantite">
						<?php for ($i = 1; $i <= min(9, $objet_pub->quantite); $i++): ?>
							<option value="<?= $i ?>"><?= $i ?></option>
						<?php endfor; ?>
					</select>
					<input class="bouton_violet" type="submit" value="Acheter">
				</p>
			</form>
		</div>

	</div>
	<?php endforeach; ?>

	<!-- Liste des objets -->

		<?php foreach ($objets as $objet): ?>
		<div class="objet">
			<a href="<?= site_url('marche_noir/acheter/'.$objet->id) ?>" class="objet_<?= $objet->type ?>">
				<p class="image">
					<img src="<?= img_url($objet->image_url) ?>" alt="<?= $objet->nom ?>">
				</p>
				<p class="titre <?= $objet->type ?>"><b><?= $objet->nom ?></b></p>
				<div class="detail bloc_gris">
					<p class="prix mini_bloc">Prix officiel : <?= struls($objet->prix) ?> -  <b>Le moins cher : <?= struls($objet->prix_minimum) ?></b></p>
					<table>
						<tr>
							<td>
								<p>
								<?php if ($objet->type == 'boost'): ?>
									<?php if ($objet->id == 44): ?>
										<?= $objet->points_action ?> points d'action à un ami
									<?php elseif ($objet->id == 55): ?>
										Fragment mystique permettant de terraformer la ville
									<?php elseif ($objet->id == 54): ?>
										Soigne tout ou presque
									<?php elseif ($objet->experience != 0): ?>
										<?= $objet->experience ?> Expérience
									<?php elseif ($objet->force != '0' || $objet->charisme != '0' || $objet->intelligence != '0'): ?>
										<?= $objet->force ?> Force  /  <?= $objet->charisme ?> Charisme  /  <?= $objet->intelligence ?> Intelligence		
									<?php elseif ($objet->id == 56): ?>
										<?= $objet->stress ?> Stress / <?= $objet->intelligence ?> Intelligence
									<?php else: ?>
										<?php if ($objet->jours_peremption == -2): ?>
											Dépérime tous les objets périmés
										<?php elseif ($objet->jours_peremption == -1): ?>
											Péremption illimitée pour 1 objet
										<?php else: ?>
											<?= $objet->jours_peremption ?> jours de péremption pour toute la maison
										<?php endif; ?>
									<?php endif; ?>
								<?php else: ?>
									<?= $objet->faim ?> Faim  /  <?= $objet->sante ?> Santé  /  <?= $objet->stress ?> Stress
								<?php endif; ?>
								</p>
							</td>
							<td>
								<p >Quantité disponible : <?= $objet->quantite_totale ?></p>
							</td>
						</tr>
					</table>
				</div>
			</a>
		</div>
		<?php endforeach; ?>
</div>
