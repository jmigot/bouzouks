<?php
$this->layout->set_title('Ma maison');
$this->layout->ajouter_javascript('maison.js');
?>

<div id="maison-index">
	<?php if (count($objets_maison) == 0): ?>
		<!-- Maison vide -->
		<div class="cellule_bleu_type1">
			<h4>Objets de la maison</h4>
			<div class="bloc_bleu">
				<p class="margin">Tu ne possèdes aucun objet...tu es tellement pauvre que même les mendiants se sentent riches à côté !</p>
			</div>
		</div>
	<?php endif; ?>
	
	<!-- Objets de la maison -->
	<?php $changement = false; foreach ($objets_maison as $objet): ?>
		<?php if ( ! $changement && $objet->rarete != 'normal'): ?>
			<!-- Séparation objets rares -->
			<?php $changement = true; ?>

			<div class="margin">
				<p class="margin clearfloat highlight centre gras">Objets <span class="pourpre">[Rare]</span> et <span class="pourpre">[Très Rare]</span></p>
			</div>
		<?php endif; ?>

		<div class="titre_double objet">
			<h4><?= $objet->quantite.' '.$objet->nom ?></h4>
			<p class="image">
				<img src="<?= img_url($objet->image_url) ?>" alt="<?= $objet->nom ?>">
			</p>

			<div class="bloc_gris">
				<!-- Jauge péremption -->
				<div class="mini_bloc">
					<p class="peremption barre inline-block">
						<!-- Illimité -->
						<?php if ($objet->peremption == -1): ?>
							<img src="<?= img_url('jauge_bleue_1.png') ?>" width="1" alt="Jauge 1">
							<img src="<?= img_url('jauge_bleue_2.png') ?>" width="100" alt="Jauge 2">
							<img src="<?= img_url('jauge_bleue_1.png') ?>" width="1" alt="Jauge 1">
						<!-- Périmé -->
						<?php elseif ($objet->peremption == 0): ?>
							<img src="<?= img_url('jauge_rouge_1.png') ?>" width="1" alt="Jauge 1">
							<img src="<?= img_url('jauge_rouge_3.png') ?>" width="100" alt="Jauge 3">
							<img src="<?= img_url('jauge_rouge_1.png') ?>" width="1" alt="Jauge 1">
						<!-- Normal -->
						<?php else: ?>
							<?php $barre_visible = min(100, round($objet->peremption * 100.0 / 7.0)); ?>
							<img src="<?= img_url('jauge_grise_1.png') ?>" width="1" alt="Jauge 1">
							<img src="<?= img_url('jauge_grise_2.png') ?>" width="<?= $barre_visible ?>" alt="Jauge 2">
							<img src="<?= img_url('jauge_grise_3.png') ?>" width="<?= 100 - $barre_visible ?>" alt="Jauge 3">
							<img src="<?= img_url('jauge_grise_1.png') ?>" width="1" alt="Jauge 1">
						<?php endif; ?>
					</p>
					<p class="inline-block">
						<?= $objet->peremption == -1 ? '<span class="pourpre gras">illimité</span>' : ($objet->peremption == 0 ? '<span class="rouge gras">périmé</span>' : pluriel($objet->peremption, 'jour')) ?>
					</p>
				</div>

				<table>
  					<tr>
  						<!-- Description de l'objet -->
					    <td rowspan="2">
					    	<?php if($objet->type == 'boost'): ?>
					    		<!-- Objet du boostzouk -->
								<?php if ($objet->id == 44): ?>
									<!-- Robot Stroustzup [Rare] -->
									<p><?= $objet->points_action ?> points d'action à un ami</p>
								<?php elseif ($objet->id == 55): ?>
									<!-- Fragment de Schnibble Bleuté -->
									<p>Fragment mystique permettant<br>de terraformer la ville</p>
								<?php elseif ($objet->id == 54): ?>
									<!-- Antidote  [Rare] -->
									<p>
										Guérit et immunise<br/>
										pour la journée<br/>
										contre divers contagions.<br/>
									</p>
								<?php elseif ($objet->experience != 0): ?>
									<p>Expérience : <span class="pourpre"><?= $objet->experience ?></span></p>
								<?php elseif ($objet->force != '0' || $objet->charisme != '0' || $objet->intelligence != '0'): ?>
									<p>Force : <span class="pourpre"><?= $objet->force ?></span></p>
									<p>Charisme : <span class="pourpre"><?= $objet->charisme ?></span></p>
									<p>Intelligence : <span class="pourpre"><?= $objet->intelligence ?></span></p>
								<?php else: ?>
									<?php if ($objet->jours_peremption == -2): ?>
										<p>Péremption : <span class="pourpre">dépérime tous les</span></p>
										<p><span class="pourpre">objets périmés</span></p>
									<?php elseif ($objet->jours_peremption == -1): ?>
										<p>Péremption : <span class="pourpre">1 objet illimité</span></p>
									<?php else: ?>
										<p>Péremption : <span class="pourpre"><?= $objet->jours_peremption ?> jours maison</span></p>
									<?php endif; ?>
								<?php endif; ?>
							<?php elseif ($objet->id == 48 && $objet->peremption > 0): ?>
								<!-- Trépan [Rare] -->
								<p>Stress : <span class="pourpre"><?= $objet->stress ?></span></p>
								<p class="pourpre">Empêche la bouzopolice de saisir</p>
								<p class="pourpre">les objets rares et très rares</p>
							<?php elseif ($objet->id == 56): ?>
							<!-- Smurtz [Rare] -->
								<p> Stress : <span class="pourpre"><?= $objet->stress ?></span></p>
								<p> Intelligence : <span class="pourpre"><?= $objet->intelligence ?></span></p>
							<?php else: ?>
								<p>Faim : <span class="pourpre"><?= $objet->faim ?></span></p>
								<p>Santé : <span class="pourpre"><?= $objet->sante ?></span></p>
								<p>Stress : <span class="pourpre"><?= $objet->stress ?></span></p>
							<?php endif; ?>
						</td>

						<!-- Consommer un objet -->
					    <td class="actions formulaire<?= in_array($objet->rarete, array('rare', 'tres_rare')) ? ' confirmer' : '' ?> frameborder_bleu">
							<?php if ($objet->id == 48 && $objet->peremption > 0): ?>
								<!-- Trépan [Rare] -->
								<p class="pourpre">S'active automatiquement au marché noir</p>
							<?php elseif ($objet->id == 55): ?>
								<!-- Fragment de Schnibble Bleuté -->
								&nbsp;
							<?php elseif ($objet->id == 49 && ($malediction_bloque || ! $amis_connectes)): ?>
								<!-- Malédiction du Schnibble, le joueur ne peut pas le transmettre si une action malédiction est en cours sur lui -->
								<p class="pourpre margin-petit">Qu'est-ce que c'est ce truc ?</p>
							<?php elseif ($objet->id == 50): ?>
								<?= form_open('maison/lire') ?>
									<p>
										<input type="hidden" name="objet_id" value="<?= $objet->id ?>">
										<input type="submit" value="Lire"> Attention, s'auto-détruit après lecture
									</p>
								</form>
							<?php else: ?>
								<?= form_open('maison/consommer') ?>
									<p>
										<input type="hidden" name="objet_id" value="<?= $objet->id ?>">
										<input type="hidden" name="peremption" value="<?= $objet->peremption ?>">
										<input type="submit" value="Utiliser">
										
										<?php if ($objet->type == 'boost' && $objet->jours_peremption == -1): ?>
											<!-- Baril de beurk -->
											<input type="hidden" name="quantite" value="1">

											<select name="maison_id" class="illimite">
												<?php foreach ($objets_maison as $objet_choix): ?>
													<?php if ($objet_choix->peremption > 0 && $objet_choix->jours_peremption != -1): // les objets périmés ne peuvent pas reçevoir l'illimité, ni les objets déjà illimités ?>
														<option value="<?= $objet_choix->maison_id ?>"><?= $objet_choix->nom ?> (<?= pluriel($objet_choix->peremption, 'jour') ?>)</option>
													<?php endif; ?>
												<?php endforeach; ?>
											</select>
										<?php elseif ($objet->id == 44): ?>
											<!-- Robot Stroustzup [Rare] -->
											<input type="hidden" name="quantite" value="1">

											<select name="ami_id" class="illimite">
												<?php foreach ($amis as $ami): ?>
													<option value="<?= $ami->id ?>"><?= form_prep($ami->pseudo) ?></option>
												<?php endforeach; ?>
											</select>
										<?php elseif ($objet->id == 49): ?>
											<input type="hidden" name="quantite" value="1">

											<select name="ami_id" class="illimite">
												<?php foreach ($amis_connectes as $ami_connecte): ?>
													<option value="<?= $ami_connecte->id ?>"><?= form_prep($ami_connecte->pseudo) ?></option>
												<?php endforeach; ?>
											</select>
										<?php else: ?>
											<select name="quantite">
												<?php for ($i = 1; $i <= min(9, $objet->quantite); $i++): ?>
													<option value="<?= $i ?>"><?= $i ?></option>
												<?php endfor; ?>
											</select>
											objets à la fois
										<?php endif; ?>
									</p>
								</form>
							<?php endif; ?>		    	
					    </td>

					    <!-- Supprimer un objet -->
					    <td rowspan="2" class="jeter frameborder_bleu centre">
					    	<?php if ($objet->id != 49 && $objet->id != 50): ?>
						    	<?= form_open('maison/supprimer') ?>
									<p>
										<input type="hidden" name="objet_id" value="<?= $objet->id ?>">
										<input type="hidden" name="peremption" value="<?= $objet->peremption ?>">
										<select name="quantite">
											<?php for ($i = 1; $i <= min(9, $objet->quantite); $i++): ?>
												<option value="<?= $i ?>"><?= $i ?></option>
											<?php endfor; ?>
										</select>
									</p>
									<input type="submit" value="Jeter">
								</form>
							<?php endif; ?>
					    </td>
					</tr>

					<!-- Vendre un objet -->
					<tr>
					    <td class="frameborder_bleu">
					    	<?php if ($objet->id == 49): ?>
								<p class="pourpre margin-petit">Pourquoi me regarde t'il bizarrement ?</p>
							<?php elseif ($objet->id == 50): ?>
								<p class="margin-petit">et se détruit à la fin de la journée</p>
							<?php else: ?>

						    	<?= form_open('maison/vendre') ?>
									<p>
										<input type="hidden" name="objet_id" value="<?= $objet->id ?>">
										<input type="hidden" name="peremption" value="<?= $objet->peremption ?>">
					
										<input type="submit" value="Vendre">
										<select name="quantite">
											<?php for ($i = 1; $i <= min(9, $objet->quantite); $i++): ?>
												<option value="<?= $i ?>"><?= $i ?></option>
											<?php endfor; ?>
										</select>
										objets à
										<input type="text" name="prix" size="6" value="<?= $objet->prix ?>">
										struls
				                    </p>
								</form>
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?php endforeach; ?>

	<!-- Objets en vente au marché noir -->
	<?php if (count($objets_marche_noir) > 0): ?>
		<!-- Marché noir -->
		<div class="marche_noir cellule_bleu_type1">
			<h4>Objets mis en vente au marché noir</h4>
			<div class="bloc_bleu">
				<p class="mini_bloc">
					La taxe de retrait est actuellement de <span class="pourpre"><?= $this->bouzouk->config('maison_pourcentage_taxe_retrait') ?>%</span> du prix de vente
				</p>
				<table>
				<tr>
					<th>Quantité</th>
					<th>Nom</th>
					<th>Péremption</th>
					<th>Ton prix</th>
					<th>Coût retrait</th>
					<th>Récupérer</th>
				</tr>

				<?php foreach ($objets_marche_noir as $objet): ?>

					<tr>
						<td><p class="quantite"><?= $objet->quantite ?></p></td>
						<td><?= $objet->nom ?></td>
						<td><?= $objet->peremption == -1 ? '<span class="pourpre gras">illimité</span>' : ($objet->peremption == 0 ? '<span class="rouge gras">périmé</span>' : couleur(pluriel($objet->peremption, 'jour'), 'pourpre')) ?></td>
						<td><p class="prix"><?= struls($objet->prix, false) ?></p></td>
						<td><p class="prix"><?= $objet->peremption == 0 ? struls(0, false) : struls(floor($objet->prix * $this->bouzouk->config('maison_pourcentage_taxe_retrait') / 100), false) ?></p></td>
						<td>
							<?= form_open('maison/retirer') ?>
								<p>
									<input type="hidden" name="vente_id" value="<?= $objet->id ?>">
									
									<select name="quantite">
										<?php for ($i = 1; $i <= min(9, $objet->quantite); $i++): ?>
											<option value="<?= $i ?>"><?= $i ?></option>
										<?php endfor; ?>
									</select>
									<input type="submit" value="Retirer">
								</p>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
				</table>
			</div>
		</div>
	<?php endif; ?>
</div>