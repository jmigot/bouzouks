<?php
	$this->layout->set_title('Les piges');
?>

<?php if ($this->bouzouk->is_journaliste()): ?>
	<!-- Menu -->
	<?php $this->load->view('piges/menu', array('lien' => 1)) ?>
<?php endif; ?>

<div id="piges-gerer">
	<div class="cellule_gris_type1 marge_haut">
		<h4>La rédaction de Vlurxtrznbnaxl</h4>
		<div class="bloc_gris piges padd_vertical">
			<!-- Anciens piges -->
			<p class="mini_bloc"><?= $pagination ?></p>
			<table>
				<tr>
					<?php if ($this->bouzouk->is_journaliste()): ?>
						<th>Auteur</th>
					<?php endif; ?>
					<th>Date</th>
					<th>Texte</th>
					<?php if ($this->bouzouk->is_journaliste()): ?>
						<th>Publié</th>
						<th></th>
					<?php endif; ?>
				</tr>

				<?php foreach ($piges as $pige): ?>
					<?php if ($this->bouzouk->is_journaliste() || $pige->en_ligne == Bouzouk::Piges_Active): ?>
						<tr>
							<?php if ($this->bouzouk->is_journaliste()): ?>
								<td><?= profil($pige->auteur_id, $pige->pseudo, $pige->rang) ?></td>
							<?php endif; ?>
							<td><p class="rond_blanc"><?= bouzouk_datetime($pige->date, 'court') ?></p></td>
							<td><?= $this->lib_parser->remplace_bbcode(form_prep($pige->texte)) ?><?= $pige->lien != '' ? '<a href="'.form_prep($pige->lien).'">En savoir plus...</a>' : '' ?></td>
							<?php if ($this->bouzouk->is_journaliste()): ?>
								
									<?php if ($pige->en_ligne == Bouzouk::Piges_Active): ?>
										<td class="centre"><p><img src="<?= img_url('succes.png') ?>" title="Active" alt="Active"></p></td>
									<?php elseif ($pige->en_ligne == Bouzouk::Piges_Desactive): ?>
										<td class="centre"><p><img src="<?= img_url('echec.png') ?>" title="Non active" alt="Non active"></p></td>
									<?php endif; ?>
									<?php if ($pige->auteur_id == $this->session->userdata('id') || $this->bouzouk->is_journaliste(Bouzouk::Rang_JournalisteChef)): ?>
									<?= form_open('piges/rediger/'.$pige->id) ?>
										<td class="centre"><p><input type="submit" value="Modifier"></p></td>
									</form>
									<?php endif; ?>
							<?php endif; ?>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
	<div class="entier clearfloat">&nbsp;</div>

	<?php if ($this->bouzouk->is_journaliste()): ?>
		<!-- Le tchat des journalistes -->
		<?php
			$vars = array(
				'titre'           => 'Le tchat des journalistes',
				'url_rafraichir'  => 'webservices/rafraichir_tchat_journalistes',
				'url_poster'      => 'webservices/poster_tchat_journalistes',
				'nb_messages_max' => $this->bouzouk->config('maintenance_tchats_messages')
			);
			$this->load->view('machine_a_cafe', $vars);
		?>

		<!-- Liste des journalistes -->
		<?= $this->load->view('gazette/liste_journalistes'); ?>
	<?php endif; ?>
</div>