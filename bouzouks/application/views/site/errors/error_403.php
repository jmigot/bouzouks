<?php

$this->layout->set_title("Mais que fais-tu ici ??");

$textes = array(
	"Tu t'es perdu ?",
	"Tu est tombé du coté obscur du site web jeune padawan. Retourner sur la page d'accueil du site, tu dois.",
	"Qu'est ce que tu fais ici espece d'enstrulé ?!!",
	"Ah Ah ! Je t'y prend petit garnement !",
	"Fiche le camp ! Tu vois pas que je suis occupé à sacrifier un pioupiouk là ?!",
	"WTF ?!",
	"Il est beau le design du site hein ? T'aimerais bien savoir comment faire pareil, hein ? hein ?!",
	"&laquo; Le changement de page, c'est maintenant !&raquo; Holladmin.",
	"Ho !! HO !! Tu vas où comment ça gamin ?! Cette zone est interdite aux moins de 75 ans !!! Fiche le camp !",
	"Non mais t'es fou ?!",
	"WWWWOUUHOUUUU !!!!",
	"BOOUUUUUHHH!!! Tu as eu peur hein ?",
	"Tu cherche le Père Nozouk ? Il est pas là...",
	"La droguerie est fermé !",
	"Qu'est ce que tu fiches ici tronche de bloubz ?!!",
	"Je suis trop vieux pour ces conneries...",
	"&laquo; Moi, président de la République, je ferai en sorte qu'il y ai quelque chose d'utile sur cette page web.&raquo; Hollandouille.",
	"LOL t'y as cru ou quoi ?",
	"Vas voir ailleurs si j'y suis.",
	"Allo, non mais allô quoi, le type il est bloqué sur une page bidon, allo, allo, j'sais pas vous m'recevez ? T'es un hacker tu tombes sur une page bidon, c'est comme si j'te dit t'es un hacker t'as pas de clavier.",
	"J'ai cru voir un vampirezouk. Vas prendre le soleil dehors plutôt que perdre ton temps ici...",
	"Tu t'es perdu ? Je te conseil de tester Google Maps...",
	"Espèce de Plouk !!",
	"Interdit à ceux qui ne sont pas habillés en rose !",
	"CASSE TOI DE LÀ FACE DE BLOUBZ !!!   o(&gt;&lt;)O"
);

?>

<div class="cellule_bleu_type1 marge_haut">
	<h4>Erreur 403</h4>
	<div class="bloc_bleu">
		<p><img src="<?= img_url('loading.gif') ?>" alt="Image" width="150" height="150" class="fl-droite"></p>

		<p class="centre margin">
			<?= $textes[mt_rand(0, count($textes) - 1)] ?>
		</p>
	</div>
</div>
