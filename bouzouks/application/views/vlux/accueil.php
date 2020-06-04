<?php
$this->layout->set_title($title);
$this->load->view('map/menu', array('lien' => $lien));
?>

<section class="cellule_bleu_type1 marge_bas">
	<h4>Bonjour et bienvue dans Vlux 3D !</h4>
	<article class="bloc_bleu">
		<p class="margin">
			Amis béta-testeurs, bienvenue dans cette nouvelle phase de test de la map Vlux 3D.<br/>
			Pour cette mise-à-jour, quelques nouveautés :
			<ul>
				<li>Refonte de la page d'accueil.</li>
				<li>Le lien "Vlux Pad" vous amène directement sur la dernière map où vous étiez</li>
				<li> Ajout des téléporteurs. Chaque type est représenté par un code couleur :
					<ul>
						<li>Vert : téléporteur à double sens.</li>
						<li>Bleu : téléporteur à sens unique.</li>
						<li>Rouge : téléporteur bloquée. C'est la contrepartie du bleu.</li>
						<li>Blanc : invisible. Ce type permet de créer des passages secrets.</li>
					</ul>
				</li>
			</ul>
			Vous ne pouvez choisir une destination que parmis les map dont le propriétaire vous a donné l'accès, ou dont vous-même êtes le propriétaire.<br/>

	</article>
</section>