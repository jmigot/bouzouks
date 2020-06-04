<?php
$this->layout->set_title($title);
$this->load->view('vlux/menu', array('lien' => $lien));
?>

<section class="cellule_bleu_type1 marge_bas">
	<h4>Bonjour et bienvue dans Vlux 3D !</h4>
	<article class="bloc_bleu">
		<p class="margin">
			Amis béta-testeurs, bienvenue dans cette nouvelle phase de test de la map Vlux 3D.<br/>
			Vous êtes ici au porte du mode création de Vlurx 3D.<br/>
			Cette page représente la liste des maps dont vous êtes propriétaires. Elle vous permettra de gérer les différents éléments paramétrables qu'elles contiennent.
		</p>
	</article>
</section>
<?php foreach ($maps as $map): 
		if($map['monnaie']=='strul'){
			$img_monnaie = 'design/icones/struls.png';
		} 
		elseif($map['monnaie']=='fragment'){ 
			$img_monnaie = 'design/icones/fragments.png';
		} 
		else{
			$img_monnaie = null;
		} 
?>
<section class="cellule_gris_type2">
	<h4><a href="<?= site_url()?>vlux/creation/<?= $map['id']?>"><?= $map['nom'] ?></a></h4>
	<article class="proposees bloc_bleu">
		<table class="entier tab_separ">
			<tr>
				<td>Etat de la map : <?= $map['etat'] ?></td>
				<td>Prix de vente : <?= $map['prix']?><?php img_tag($img_monnaie, ucfirst($map['monnaie']), '');?></td>
			</tr>
			<tr>
				<td>Liste des téléporteurs</td>
				<td></td>
			</tr>
		</table>
	</article>
</section>
<?php endforeach; ?>