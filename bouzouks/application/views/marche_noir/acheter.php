<?php $this->layout->set_title('Marché noir'); ?>

<div id="marche_noir-acheter">
		<p class="retour"><a href="<?= site_url('marche_noir') ?>">Retour au marché noir</a></p>
	<?php 
		// ---------- Hook clans ----------
		// Tag MLBiste (MLB)
		if (($tag_mlbiste = $this->bouzouk->clans_tag_mlbiste()) != null)
			$this->load->view('clans/tag_mlb', array('tag_mlbiste' => $tag_mlbiste));
	?>

	<!-- En-tete de l'objet -->
	<div class="en-tete">
		<p class="infos_produit">Hé ! Viens voir...<br><?= $objet->nom ?>,<br>
			<?php if ($objet->type == 'boost'): ?>
				<?php if ($objet->id == 44): ?>
					donne <?= $objet->points_action ?> points d'action à un ami.<br>Génial pour les geeks !
				<?php elseif ($objet->id == 55): ?>
					fragment mystique permettant de terraformer la ville
				<?php elseif ($objet->id == 54): ?>
					Soigne la maladie Zombifiante
				<?php elseif ($objet->experience != 0): ?>
					ça t'augmente ton experience<br>de <?= $objet->experience ?> xp. Pas mal hein ?!
				<?php elseif ($objet->force != '0' || $objet->charisme != '0' || $objet->intelligence != '0'): ?>
					augmente ton charisme de <?= $objet->charisme ?> pts,<br>ton intelligence de <?= $objet->intelligence ?> pts et<br>ta force de <?= $objet->force ?> pts.
				<?php else: ?>
					<?php if ($objet->jours_peremption == -2): ?>
						cette merveille dépérime tous les<br>objets périmés dans ta maison!<br> Incroyable, non ?!</span>
					<?php elseif ($objet->jours_peremption == -1): ?>
						te permet d'avoir une durée de<br>péremption illimitée pour 1 objet<br>de ta maison.</span>
					<?php else: ?>
						permet un délai de péremption de<br><?= $objet->jours_peremption ?> jours pour tous les objets<br>de la maison !</span>
					<?php endif; ?>
				<?php endif; ?>
			<?php else: ?>			
				qui donne <?= $objet->faim ?> pts de faim, <?= $objet->sante ?> pts de<br>santé et <?= $objet->stress ?> pts de stress.<br>Ça te tente ?!
			<?php endif; ?>
		</p>
		<p class="texte_pnj">Décide toi<br>vite avant que les<br>bouzoflics rappliquent<br>dans le coin sinon<br>tu peux dire adieu à<br>tes achats !</p>
		<p class="image"><img src="<?= img_url($objet->image_url) ?>" alt="<?= $objet->nom ?>"></p>
		<p class="infos_mn">
			<u>Quantité disponible :</u> <?= $objet->quantite_totale ?><br>
			<u>Prix officiel :</u> <?= struls($objet->prix) ?><br><br>
			<u>Le moins cher<br> du m.n.:</u> <?= struls($objet->prix_minimum) ?>
		</p>
	</div>

	<!-- Liste des vendeur -->
	<div class="cellule_bleu_type1">
		<h4>Liste des vendeurs pour cet objet</h4>
		<div class="bloc_bleu">
			<?php foreach ($objets as $objet): ?>
				<div class="objet">
					<p class="titre"><?= struls($objet->prix) ?> l'unité</p>
					<div class="infos">
						<p class="quantite">Quantité : <span class="pourpre"><?= $objet->quantite ?></span></p>
						<p class="vendeur">Vendeur : <?= profil($objet->vendeur_id, $objet->vendeur) ?></p>
						<p class="peremption">Péremption : <?= $objet->peremption == -1 ? '<span class="pourpre gras">illimité</span>' : ($objet->peremption == 0 ? '<span class="rouge">périmé</span>' : couleur(pluriel($objet->peremption, 'jour'), 'pourpre')) ?></p>
					</div>
					<div class="formulaire">
					<?= form_open('marche_noir/acheter/'.$objet_id, array('class' => 'lien')) ?>
							<p>
								<input type="hidden" name="vente_id" value="<?= $objet->vente_id ?>">
								<select name="quantite">
									<?php for ($i = 1; $i <= min(9, $objet->quantite); $i++): ?>
										<option value="<?= $i ?>"><?= $i ?></option>
									<?php endfor; ?>
								</select>
								<input type="submit" value="Acheter">
							</p>
						</form>
					</div>
				</div>
			<?php endforeach; ?>
			<p class="clearfloat"></p>
		</div>
	</div>
</div>
