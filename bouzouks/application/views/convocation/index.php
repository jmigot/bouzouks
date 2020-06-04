<?php
$this->layout->set_title('Convocation');
?>
<div id="convocation">
	<!-- Le tchat -->
	<?php
		$vars = array(
			'titre'           => 'Convocation de '.$convocation->moderateur_pseudo,
			'url_rafraichir'  => 'webservices/rafraichir_tchat_convocation/'.$convocation->id,
			'url_poster'      => 'webservices/poster_tchat_convocation/'.$convocation->id,
			'nb_messages_max' => $this->bouzouk->config('maintenance_tchats_messages'),
			'table_smileys'   => creer_table_smileys('message')
		);
		$this->load->view('machine_a_cafe', $vars);
	?>
</div>

<div class="cellule_bleu_type1 marge_haut">
	<h4>Convocation</h4>
	<div class="bloc_bleu convocation">
		<p class="margin-petit">Vous avez été convoqué par un membre de la modération de <span class="pourpre">Bouzouks.net</span>. Vous ne pouvez plus jouer tant que la convocation n'est pas finie.</p>
		<p class="margin-petit">Rappelez vous que la personne que vous avez en face est un membre de la modération de <span class="pourpre">Bouzouks.net</span>, et non pas son bouzouk que vous avez pu rencontrer sur le tobozon.</p>
		
		<?php if ($this->bouzouk->is_moderateur() && $convocation->etat): ?>
		<p>
			<?= form_open('staff/convoquer_joueur/fin_convocation/'.$convocation->id) ?>
				<input type="submit" name="fermer" value="Fermer la convocation">
			</form>
		</p>
		<?php endif; ?>
	</div>
</div>