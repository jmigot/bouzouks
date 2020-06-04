<?php $this->layout->set_title('Mendier'); ?>
<div id="blocage">
	<?php if ($message): ?>
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Tu es mendiant</h4>
			<div class="bloc_bleu">
				<p class="fl-gauche"><img src="<?= img_url('mendiants/aucun_mendiant.gif') ?>" alt="Illustration" class="image"></p>
				<div class="message"><?= $message ?></div>
				<p class="clearfloat"></p>
			</div>
		</div>
	<?php endif; ?>
	<!-- Le tchat des mendiants -->
	<?php
		$vars = array(
			'titre'           => 'Machine à jus de chausette',
			'url_rafraichir'  => 'webservices/rafraichir_tchat_mendiant',
			'url_poster'      => 'webservices/poster_tchat_mendiant',
			'nb_messages_max' => $this->bouzouk->config('maintenance_tchats_messages')
		);

		if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats))
		{
			$vars['moderation']    = true;
			$vars['url_supprimer'] = 'webservices/supprimer_tchat_mendiants';
		}

		$this->load->view('machine_a_cafe', $vars);
	?>
	
	<?php if ($this->bouzouk->is_moderateur(Bouzouk::Rang_ModerateurTchats)): ?>
		<p>
			<input type="button" name="machine_a_cafe_supprimer" value="Supprimer messages">
		</p>
	<?php endif; ?>

	<!-- Liste des mendiants -->
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Liste des mendiants</h4>
		<div class="bloc_bleu">
			<p class="margin italique pourpre">Il y a en tout <?= pluriel(count($mendiants), 'mendiant') ?> dans la ruelle.</p>

			<table class="liste_bouzouks">
				<tr>
					<?php $i = 0; ?>
					<?php foreach ($mendiants as $mendiant): ?>
						<td><?= profil($mendiant->id, $mendiant->pseudo, $mendiant->rang) ?></td>
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
