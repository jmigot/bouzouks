<?php $this->layout->set_title('Mendier'); ?>

<div id="mendiants-miserabilisme">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Misérabilisme</h4>
		<div class="bloc_bleu">
			<p class="margin padding">
				<?= $nom_clan ?> a aménagé la ruelle des mendiants au profit d'une donation pour son organisation<br>
				Tu peux donc faire un don de <span class="pourpre">10 struls</span> maximum pour les aider :)<br><br><br>
			</p>
			<p class="centre"><img src="<?= img_url('clans/demande_de_don.png') ?>" alt="Misérabilisme" width="200"></p>
				<?= $this->lib_parser->remplace_bbcode(nl2br(form_prep($texte))) ?>
			<p>
				<?= form_open('mendiants/donner_miserabilisme', array('class' => 'centre')) ?>
				Montant : <input type="text" name="montant" size="3" maxlength="2" class="centre"> struls<br>
				<input type="submit" value="Donner">
			</p>
				</form>
			<p class="clearfloat"></p>
		</div>
	</div>
</div>
