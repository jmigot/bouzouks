<?php
$this->layout->set_title('Mes amis');
$this->layout->ajouter_javascript('amis.js');
?>

<div id="amis-index">
	<!-- Liste des amis actuels -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Mes amis</h4>
		<div class="bloc_bleu">
			<?php if (count($amis) > 0): ?>			
				<?php foreach ($amis as $ami): 
					if ($this->bouzouk->est_infecte($ami->id)){
						$ami->perso = 'zombi/'.$ami->perso;
					}  // --------- Event RP Zombies ------------ 
					if($this->bouzouk->est_maudit_mlbobz($ami->id)){
						$ami->perso = 'rp_zoukette/'.$ami->perso;
					}?>
					<div class="joueur">
						<p class="fl-gauche"><img src="<?= img_url(avatar($ami->faim, $ami->sante, $ami->stress, $ami->perso)) ?>" height="65" alt="Image perso"></p>
						<p class="infos"><?= profil($ami->id, $ami->pseudo, $ami->rang) ?></p>
						<p class=<?= !$ami->connect ? '"vert">Connecté' : '"rouge">Hors-ligne' ?></p>
						
						<?= form_open('amis/supprimer', array('class' => 'supprimer')) ?>
							<p>
								<input type="hidden" name="joueur_id" value="<?= $ami->id ?>">
								<input type="submit" value="Supprimer">
							</p>
						</form>
					</div>
				<?php endforeach; ?>
				
				<p class="clearfloat"></p>
			<?php else: ?>
				<p class="fl-gauche"><img src="<?= img_url('mendiants/aucun_mendiant.gif') ?>" alt="Illustration" class="image"></p>
				<p class="message margin">Tu n'as pas d'ami, c'est triste la vie hein ?</p>
				<p class="clearfloat"></p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Ajouter un ami -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Ajouter un ami</h4>
		<div class="bloc_bleu">
			<p class="margin">Besoin d'ami à Vlurxtrznbnaxl ? Viens sur Fazezouk ! Tu peux y ajouter tes amis et voir quand ils sont connectés.</p>
			
			<?= form_open('amis/ajouter', array('class' => 'margin centre')) ?>
				<label for="ami_id">Envoyer une demande d'amitié :</label>
				<?= $select_amis ?>
				<input type="submit" value="Envoyer">
			</form>
		</div>
	</div>

	<!-- Demandes des autres joueurs en attente -->
	<?php if (count($demandes_attente) > 0): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Les demandes des autres Bouzouks en attente</h4>
			<div class="bloc_bleu">
				<table>
					<tr>
						<th>Date</th>
						<th>Pseudo</th>
						<th>Accepter/Refuser</th>
					</tr>

					<tr>
						<td colspan="3"><p class="hr"></p></td>
					</tr>

					<?php foreach ($demandes_attente as $demande): ?>
						<tr>
							<td><?= bouzouk_datetime($demande->date, 'court') ?></td>
							<td><p class="highlight"><?= profil($demande->id, $demande->pseudo, $demande->rang) ?></p></td>
							<td>
								<?= form_open('amis/accepter') ?>
									<p>
										<input type="hidden" name="joueur_id" value="<?= $demande->id ?>">
										<input type="submit" value="Accepter">
									</p>
								</form>

								<?= form_open('amis/refuser') ?>
									<p>
										<input type="hidden" name="joueur_id" value="<?= $demande->id ?>">
										<input type="submit" value="Refuser">
									</p>
								</form>
							</td>
						</tr>
					
						<tr>
							<td colspan="3"><p class="hr"></p></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</div>
	<?php endif; ?>

	<!-- Demandes du joueur en attente -->
	<?php if (count($demandes_faites) > 0): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Mes demandes</h4>
			<div class="bloc_bleu">
				<table>
					<tr>
						<th>Date</th>
						<th>Pseudo</th>
						<th>En attente/Refusée</th>
					</tr>

					<tr>
						<td colspan="3"><p class="hr"></p></td>
					</tr>

					<?php foreach ($demandes_faites as $demande): ?>
						<tr>
							<td><?= bouzouk_datetime($demande->date, 'court') ?></td>
							<td><p class="highlight"><?= profil($demande->id, $demande->pseudo, $demande->rang) ?></p></td>
							<td>
								<?php if ($demande->etat == Bouzouk::Amis_Attente): ?>
									En attente <img src="<?= img_url('attention.png') ?>" alt="En attente" width="15px">
								<?php else: ?>
									Refusée <img src="<?= img_url('echec.png') ?>" alt="Refusée" width="15px">
								<?php endif; ?>
							</td>
						</tr>
					
						<tr>
							<td colspan="3"><p class="hr"></p></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</div>
	<?php endif; ?>

	<!-- Liste noire -->
	<?php if (count($demandes_liste_noire) > 0): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Ma liste noire de bouzouks refusés</h4>
			<div class="bloc_bleu">
				<table>
					<tr>
						<th>Date</th>
						<th>Pseudo</th>
						<th>Supprimer</th>
					</tr>

					<tr>
						<td colspan="3"><p class="hr"></p></td>
					</tr>

					<?php foreach ($demandes_liste_noire as $demande): ?>
						<tr>
							<td><?= bouzouk_datetime($demande->date, 'court') ?></td>
							<td><p class="highlight"><?= profil($demande->id, $demande->pseudo, $demande->rang) ?></p></td>
							<td>
								<?= form_open('amis/supprimer_liste_noire') ?>
									<p>
										<input type="hidden" name="joueur_id" value="<?= $demande->id ?>">
										<input type="submit" value="Supprimer de la liste noire">
									</p>
								</form>
							</td>
						</tr>
					
						<tr>
							<td colspan="3"><p class="hr"></p></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</div>
	<?php endif; ?>
</div>