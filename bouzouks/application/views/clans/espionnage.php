<?php $this->layout->set_title('Espionnage'); ?>

<div id="clans-espionnage">
	<!-- Menu -->
	<?php $this->load->view('clans/menu', array('lien' => 4)) ?>

	<?php if ( ! $espionnage->valide): ?>
		<!-- Espionnage raté car entreprise ou clan n'existe plus -->
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Espionnage raté</h4>
			<div class="bloc_bleu">
				<p class="margin noir">L'action <span class="pourpre">Espionnage</span> a raté car l'entrepise ou le clan cible n'existe pas ou plus.</p>
			</div>
		</div>
	<?php elseif ($espionnage->parametres['entreprise_id'] > 0): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Espionnage entreprise</h4>
			<div class="bloc_bleu">
				<p class="margin noir centre">
					<span class="pourpre">Espionnage</span> en cours de l'entreprise <span class="pourpre"><?= form_prep($espionnage->entreprise->nom) ?></span>.<br>
					Tu ne peux pas poster de message, juste voir qui est connecté ainsi que les discussions.
				</p>
			</div>
		</div>

		<!-- Machine à café entreprise -->
		<?php
			$vars = array(
				'url_rafraichir'  => 'webservices/rafraichir_tchat_entreprise/'.$espionnage->parametres['entreprise_id'].'/'.$clan->id,
				'url_poster'      => 'webservices/poster_tchat_entreprise/'.$espionnage->parametres['entreprise_id'].'/'.$clan->id,
				'nb_messages_max' => $this->bouzouk->config('maintenance_tchats_messages_entreprise'),
				'table_smileys'   => creer_table_smileys('message')
			);
			$this->load->view('machine_a_cafe', $vars);
		?>
	<?php else: ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Espionnage clan adverse</h4>
			<div class="bloc_bleu">
				<p class="margin noir centre"><span class="pourpre">
					Espionnage</span> en cours du clan <span class="pourpre"><?= form_prep($espionnage->clan->nom) ?></span>.<br>
					Tu ne peux pas poster de message, juste voir qui est connecté ainsi que les discussions.
				</p>
			</div>
		</div>

		<!-- Machine à café clan adverse -->
		<?php
			$vars = array(
				'url_rafraichir'  => 'webservices/rafraichir_tchat_clan/'.$espionnage->parametres['clan_id'].'/'.$clan->id,
				'url_poster'      => 'webservices/poster_tchat_clan/'.$espionnage->parametres['clan_id'].'/'.$clan->id,
				'nb_messages_max' => $this->bouzouk->config('maintenance_tchats_messages_clan'),
				'table_smileys'   => creer_table_smileys('message')
			);
			$this->load->view('machine_a_cafe', $vars);
		?>
	<?php endif; ?>
</div>
