<div>
	<p class="fl-gauche marge_droite"><img src="<?= img_url('clans/tag_mlb/mlb_tag_'.mt_rand(1,4).'.png') ?>" alt="Tag MLB" width="182"></p>
	<h4>Message du Mouvement Libérateur Bouzouk</h4>
	<p class="noir margin padding"><?= $this->lib_parser->remplace_bbcode(nl2br(form_prep($tag_mlbiste->parametres['texte']))) ?></p>
	<p class="clearfloat"></p>
</div>