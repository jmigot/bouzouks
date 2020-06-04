<?php $this->layout->set_title('Game over') ; ?>

<div class="cellule_bleu_type1 marge_haut">
	<h4>Tu es en game over</h4>
	<div class="bloc_bleu">
		<p class="fl-gauche">
			<img src="<?= img_url('loading.gif') ?>" alt="En pause">
		</p>
		
		<p class="margin">
			Tu es en Game Over depuis le <span class="pourpre"><?= bouzouk_datetime($date) ?></span><br><br>
			Si tu restes en Game Over plus de <span class="pourpre"><?= $this->bouzouk->config('maintenance_delai_suppression_game_over') ?> jours</span> à partir de cette date, ton compte sera définitivement supprimé. Tu es en game over depuis déjà
			<span class="pourpre"><?= jours_ecoules($date) ?></span>.
		</p>

		<?= form_open('joueur/recommencer_partie') ?>
			<p>
				<input type="submit" value="Recommencer ma partie">
			</p>
		</form>
		
		<p class="clearfloat"></p>
	</div>
</div>
