<?php
if ($form) :
?>
<div class="event_mlbobz">
	<?= form_open('joueur/maudire_mlbobz') ?>
		<?= $text ?>
		<br>
		<input type="submit" value="Smouax !!" class="bouton_rose">
	</form>
</div>
<?php
else: 
?>
<p class="event_mlbobz">
	<span class="blanc"><?= $text ?></span>
</p>
<?php
endif;