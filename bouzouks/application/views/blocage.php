<?php $this->layout->set_title($titre_layout); ?>

<div id="blocage" class="cellule_bleu_type1">
	<h4><?= $titre ?></h4>
	<div class="bloc_bleu">
		<p class="fl-gauche"><img src="<?= img_url($image_url) ?>" alt="Illustration" class="image"></p>
		<div class="message"><?= $message ?></div>
		<p class="clearfloat"></p>
	</div>
</div>
