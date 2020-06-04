<?php $this->layout->set_title('Mendier'); ?>

<div id="mendiants-ruelle_fermee">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Ruelle des mendiants fermée...</h4>
		<div class="bloc_bleu">
			<p class="fl-gauche margin"><img src="<?= img_url('mendiants/aucun_mendiant.gif') ?>" alt="Illustration"></p>
			<p class="margin padding">
				<?= $nom_clan ?> (organisation) a censuré la ruelle des mendiants pour la raison suivante :<br><br>
				<?= $this->lib_parser->remplace_bbcode(nl2br(form_prep($texte))) ?>
			</p>
			<p class="clearfloat"></p>
		</div>
	</div>
</div>
