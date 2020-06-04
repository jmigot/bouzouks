<?php $this->layout->set_title('Partie en pause'); ?>

<div class="cellule_bleu_type1 marge_haut">
	<h4>Tu es en pause</h4>
	<div class="bloc_bleu">
		<p class="fl-gauche">
			<img src="<?= img_url('loading.gif') ?>" alt="En pause">
		</p>
		
		<p class="margin">		
			Ta partie est en pause depuis le <span class="pourpre"><?= bouzouk_datetime($date) ?></span><br>
			Cette pause doit durer au minimum <span class="pourpre">2 jours</span> et tu as déjà fait <span class="pourpre"><?= jours_ecoules($date) ?></span>.
		</p>

		<?php if (strtotime($this->session->userdata('date_statut').'+2 DAY') <= strtotime(bdd_datetime())): ?>
			<?= form_open('joueur/reprendre_pause') ?>
				<p>
					<input type="submit" value="Reprendre ma partie">
				</p>
			</form>
		<?php endif; ?>
		
		<p class="clearfloat"></p>
	</div>
</div>
