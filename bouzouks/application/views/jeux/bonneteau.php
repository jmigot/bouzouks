<?php
$this->layout->set_title('Le bonneteau');
$this->layout->ajouter_javascript('jeux.js');
?>

<div id="jeux-bonneteau">
	<div class="decor">
		<?= form_open('jeux/bonneteau') ?>
			<p class="texte_pnj">
				Si tu<br>trouves un oeil en<br>morphoplastoc sous<br>ce bol, tu gagnes le<br>double de la mise.<br>Bonne chance !
			</p>

			<p class="bols">
				<button type="submit" name="bol" value="1"><img src="<?= img_url('jeux/'.$images_bols['1']) ?>" width="86" height="150" alt="Bol 1"></button>
				<button type="submit" name="bol" value="2"><img src="<?= img_url('jeux/'.$images_bols['2']) ?>" width="86" height="150" alt="Bol 2"></button>
				<button type="submit" name="bol" value="3"><img src="<?= img_url('jeux/'.$images_bols['3']) ?>" width="86" height="150" alt="Bol 3"></button>
			</p>

			<p class="mise pourpre gras">
				Mise une somme et choisis un des trois bols.<br>
				Quelle somme veux-tu miser ?
				<input type="text" name="mise" size="4" value="<?= set_value('mise') ?>" class="centre"> struls
			</p>
		</form>
	</div>
</div>