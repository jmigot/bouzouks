<?php
	$this->layout->set_title($title);
	$this->load->view('facebook/pixel', array('pixel_id'=> $pixel->id_fb));
?>
<div>
	<div class="celulle_bleu_type1 marge_haut">
		<h4>test pixel <?= $pixel->nom ?></h4>
		<div class="bloc_bleu">
			<p class="margin">
				Une requête a été envoyer afin de tester l'état du pixel<br/>
				A vérifier sur <a href="https://www.facebook.com/ads/manager/pixel/conversion_pixel/?act=368759042&pid=p1">FaceBook</a>
			</p>
		</div>
	</div>
</div>
