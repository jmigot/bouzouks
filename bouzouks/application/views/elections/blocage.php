<?php $this->layout->set_title('Elections - Candidater'); ?>

<div id="blocage">
	<?php $this->load->view('elections/avancement') ?>

	<div class="cellule_bleu_type1 marge_haut">
		<h4>Tu ne peux pas candidater</h4>
		<div class="bloc_bleu">
			<p class="fl-gauche"><img src="<?= img_url('mairie.gif') ?>" alt="Illustration" class="image"></p>
			<p class="message"><?= $message ?></p>
			<p class="clearfloat"></p>
		</div>
	</div>
</div>







 
