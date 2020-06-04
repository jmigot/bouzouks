<?php
$this->layout->set_title('Modération - Tchats');
$this->layout->ajouter_javascript('tchat.js');
?>

<div id="staff-moderer_tchats">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Modérer les tchats entreprise</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<p class="margin centre">
				Ce bouton va supprimer tous les messages de tchats sélectionnés sur cette page<br>
				[<span class="pourpre italique">fonctionne pour toutes les entreprises à la fois</span>]<br>
				<input type="button" name="machine_a_cafe_supprimer" value="Supprimer">
			</p>
		</div>
	</div>

	<?php foreach ($entreprises as $entreprise): ?>
		<div class="bloc_bleu infos">
			<table>
				<tr>
					<td><p class="highlight">[ <span class="pourpre"><?= form_prep($entreprise->nom) ?></span> ] <i>dirigée par</i> <?= profil($entreprise->chef_id, $entreprise->chef_pseudo) ?></p></td>
					<td><p class="inline-block"><input type="button" name="afficher_tchat_<?= $entreprise->id ?>" value="Machine à café"></td>
				</tr>
			</table>
		</div>

		<?php
			$vars = array(
				'url_rafraichir'        => 'webservices/rafraichir_tchat_entreprise/'.$entreprise->id,
				'url_poster'            => 'webservices/poster_tchat_entreprise/'.$entreprise->id,
				'url_supprimer'         => 'webservices/supprimer_tchat_entreprise',
				'nb_messages_max'       => $this->bouzouk->config('maintenance_tchats_messages_entreprise'),
				'tchat_id'              => $entreprise->id,
				'titre'                 => form_prep($entreprise->nom),
				'no_javascript_include' => true,
				'moderation'            => true,
				'table_smileys'         => creer_table_smileys('message_'.$entreprise->id),
				'actif'                 => false
			);
			$this->load->view('machine_a_cafe', $vars);
		?>
	<?php endforeach; ?>	
</div>
