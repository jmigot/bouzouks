<?php
$this->layout->set_title('Modération - Tchats');
$this->layout->ajouter_javascript('tchat.js');
?>

<div id="staff-moderer_tchats">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Modérer les tchats clans</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>

			<p class="margin centre">
				Ce bouton va supprimer tous les messages de tchats sélectionnés sur cette page<br>
				[<span class="pourpre italique">fonctionne pour toutes les entreprises à la fois</span>]<br>
				<input type="button" name="machine_a_cafe_supprimer" value="Supprimer">
			</p>
		</div>
	</div>

	<?php
	$types = array(
		Bouzouk::Clans_TypeSyndicat       => 'Syndicats',
		Bouzouk::Clans_TypePartiPolitique => 'Partis politiques',
		Bouzouk::Clans_TypeOrganisation   => 'Organisations'
	);
	foreach ($types as $type => $nom): ?>
		<p class="padding highlight centre noir test"><?= $nom ?></p>

		<?php foreach ($clans as $clan): ?>
			<?php if ($clan->type != $type) continue; ?>

			<div class="bloc_bleu infos">
				<table>
					<tr>
						<td><p class="highlight">[ <span class="pourpre"><?= form_prep($clan->nom) ?></span> ] <i>dirigé par</i> <?= profil($clan->chef_id, $clan->chef_pseudo) ?></p></td>
						<td><p class="inline-block"><input type="button" name="afficher_tchat_<?= $clan->id ?>" value="Machine à café"></td>
					</tr>
				</table>
			</div>

			<?php
				$vars = array(
					'url_rafraichir'        => 'webservices/rafraichir_tchat_clan/'.$clan->id,
					'url_poster'            => 'webservices/poster_tchat_clan/'.$clan->id,
					'url_supprimer'         => 'webservices/supprimer_tchat_clan',
					'nb_messages_max'       => $this->bouzouk->config('maintenance_tchats_messages_clan'),
					'tchat_id'              => $clan->id,
					'titre'                 => form_prep($clan->nom),
					'no_javascript_include' => true,
					'moderation'            => true,
					'table_smileys'         => creer_table_smileys('message_'.$clan->id),
					'actif'                 => false
				);
				$this->load->view('machine_a_cafe', $vars);
			?>
		<?php endforeach; ?>
	<?php endforeach; ?>
</div>
