<?php $this->layout->set_title('Machine à café'); ?>

<div id="anpe-machine_a_cafe">
	<!-- Menu -->
	<?php $this->load->view('anpe/menu', array('lien' => 3)) ?>

	<!-- Le tchat de l'anpe -->
	<?php
		$vars = array(
			'titre'           => 'La machine à café',
			'url_rafraichir'  => 'webservices/rafraichir_tchat_chomeur',
			'url_poster'      => 'webservices/poster_tchat_chomeur',
			'nb_messages_max' => $this->bouzouk->config('maintenance_tchats_messages')
		);

		if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats))
		{
			$vars['moderation']    = true;
			$vars['url_supprimer'] = 'webservices/supprimer_tchat_chomeurs';
		}

		$this->load->view('machine_a_cafe', $vars);
	?>
	
	<?php if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats)): ?>
		<p>
			<input type="button" name="machine_a_cafe_supprimer" value="Supprimer messages">
		</p>
	<?php endif; ?>

	<!-- Liste des chômeurs -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Liste des chômeurs</h4>
		<div class="bloc_bleu">
			<p class="margin italique pourpre">Il y a en tout <?= pluriel(count($chomeurs), 'chômeur') ?> à l'ANPC.</p>

			<table class="liste_bouzouks">
				<tr>
					<?php $i = 0; ?>
					<?php foreach ($chomeurs as $chomeur): ?>
						<td><?= profil($chomeur->id, $chomeur->pseudo, $chomeur->rang) ?></td>
						<?php if (++$i % 4 == 0): ?>
							</tr>
							<tr>
						<?php endif; ?>
					<?php endforeach; ?>
				</tr>
			</table>
		</div>
	</div>
</div>
 
