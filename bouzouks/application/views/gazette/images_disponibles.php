<div id="gazette-images_disponibles">
	<input type="button" value="Fermer" name="fermer" class="fl-droite">
	
	<h2 class="centre">Images disponibles pour la gazette</h2>
	<p>
		<?php foreach ($images as $image): ?>
			<img src="<?= img_url('uploads/gazette/'.$image) ?>" alt="<?= $image ?>">
		<?php endforeach; ?>
	</p>
</div>
