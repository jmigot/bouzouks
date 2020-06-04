<?php
if ($form) :
?>
<div class="event_zombie">
	<?= form_open('joueur/infecter') ?>
		<label for="infection"></label>
		<?= $text ?>
		<br>
		<input type="submit" value="Implanter ce bouzouk" class="bouton_rouge">
	</form>
</div>
<?php
else: 
?>
<p class="event_zombie">
	<span class="blanc"><?= $text ?></span>
</p>
<?php
endif;