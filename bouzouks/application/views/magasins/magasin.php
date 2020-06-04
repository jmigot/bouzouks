<?php $this->layout->set_title('Magasin'); ?>

<div id="magasin-magasin">
	<?php 
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste()) != null)
			$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));
	?>
	
	<?php
		// ---------- Hook clans ----------
		// Pillage compulsif (Organsation)
		if ($pillage_compulsif = $this->bouzouk->clans_pillage_compulsif($type_magasin)):
	?>
		<div class="cellule_violet_type3">
			<h4>Pillage compulsif</h4>
			<div class="bloc_violet">
				<p class="margin noir centre">
					A cause d'un <span class="pourpre">Pillage compulsif</span> lancée sur toi, tu ne peux plus faire d'achat dans ce shop pendant <span class="pourpre">24h</span>. 
				</p>
			</div>
		</div>
	<?php endif; ?>

	<?php $n = 0; ?>

	<div id="magasin">
	<?php foreach ($objets as $objet): ?>
		<?php if($n == 1): ?>
			<div id="<?= $type_magasin ?>">
				<p><?= $message_vendeur ?></p>
			</div>
		<?php endif; ?>

		<?php if($n == 2):?>
			</div>
		<?php endif; ?>

		<div class="objet">
			<p class="titre pourpre"><?= $objet->nom ?></p>
			<?= isset($promotion) && $promotion->objet_id == $objet->id ? '<p class="promo"><img src="'.img_url('./magasins/reduction.png').'" alt="Promotion -50%" width="56px" height="58px"></p>' : '' ?>
			<p class="image"><img src="<?= img_url($objet->image_url) ?>" alt="<?= $objet->nom ?>"></p>
			<p class="prix">
				<?php
					if (isset($promotion) && $promotion->objet_id == $objet->id)
						echo couleur(struls(round($objet->prix / 2.0, 1), false), 'blanc').'<br><span class="ancien">'.$objet->prix.' struls</span>';

					else
						echo struls($objet->prix, false);
				?>
			</p>
			<div class="achat">
				

				<?php
					// ---------- Hook clans ----------
					// Sainte brigade (SdS)
				 	if (isset($sainte_brigade) && (($sainte_brigade->parametres['bibles'] && $objet->id == 19) || ($sainte_brigade->parametres['schnibbles'] && $objet->id == 18))):
				 ?>
					<p class="penurie gras italique vert_fonce">Censurés</p>
				<?php
					// ---------- Hook clans ----------
					// Braquage (Organisation)
					elseif (isset($braquage) && $braquage->parametres['objet_id'] == $objet->id):
				?>
					<p class="penurie gras italique pourpre">Volés</p>
				<?php 
					// ---------- Hook clans ----------
					// Pillage compulsif (Organisation)
				elseif ($pillage_compulsif): ?>
					<p class="penurie gras italique pourpre">Vente bloquée !</p>
				<?php elseif ($objet->quantite == 0): ?>
					<p class="penurie gras italique rouge">Pénurie de stock !</p>
				<?php else: ?>
					<?= form_open('magasins/acheter', array('class' => 'formulaire')) ?>
						<p>
							<input type="hidden" name="type" value="<?= $objet->type ?>">
							<input type="hidden" name="objet_id" value="<?= $objet->id ?>">
							<label for="quantite_<?= $objet->id ?>"></label>
							<select name="quantite" id="quantite_<?= $objet->id ?>">
								<?php for ($i = 1; $i <= min(9, $objet->quantite); $i++): ?>
									<option value="<?= $i ?>"><?= $i ?></option>
								<?php endfor; ?>
							</select>
						</p>
						<p class="quantite">Quantité en stock : <b><?= $objet->quantite ?></b></p>
						<input type="submit" value="Acheter" class="bouton_rouge">
						
					</form>
				<?php endif; ?>
			</div>

			<div class="stat_objet bloc_bleu">
				<?php if ($objet->type == 'boost'): ?>
					<?php if ($objet->experience != 0): ?>
						<p class="frameborder_bleu">Expérience</p>
						<p><span class="pourpre"><?= $objet->experience ?> xp</span></p>
					<?php elseif ($objet->force != '0' || $objet->charisme != '0' || $objet->intelligence != '0'): ?>
						<p class="frameborder_bleu">Force : &nbsp;&nbsp;<span class="pourpre"><?= $objet->force ?></span></p>
						<p>Charisme : &nbsp;<span class="pourpre"><?= $objet->charisme ?></span></p>
						<p class="frameborder_bleu">Intelligence : <span class="pourpre"><?= $objet->intelligence ?></span></p>
					<?php else: ?>
						<p class="frameborder_bleu">Péremption</p>

						<?php if ($objet->jours_peremption == -1): ?>
							<p><span class="pourpre">Durée illimitée pour</span></p>
							<p><span class="pourpre">1 objet de la maison</span></p>
						<?php else: ?>
							<p><span class="pourpre"><?= $objet->jours_peremption ?> jours pour tous les</span></p>
							<p><span class="pourpre">objets de la maison</span></p>
						<?php endif; ?>					
					<?php endif; ?>
				<?php else: ?>
					<p class="frameborder_bleu">Faim : &nbsp;&nbsp;<span class="pourpre"><?= $objet->faim ?></span></p>
					<p>Santé : &nbsp;<span class="pourpre"><?= $objet->sante ?></span></p>
					<p class="frameborder_bleu">Stress : <span class="pourpre"><?= $objet->stress ?></span></p>
				<?php endif; ?>
			</div>
		</div>

		<?php $n++; ?>
	<?php endforeach;?>
</div> 
