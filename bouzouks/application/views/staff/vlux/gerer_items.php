<?php 
	$this->layout->set_title($title);
	$this->load->view('staff/vlux/menu_admin', array('lien' => $lien));
 ?>

<div id="staff-gerer_serveurs">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Les objets dans Vlux</h4>
		<div class="bloc_bleu">
			<div class="margin">
				<p>
					<?php
						if(ENVIRONMENT =='development'){
							echo form_open('staff/gerer_items/backup_sql', array('class'=>'inline-block'));
							echo form_submit(array('name'=>'bck_db'),"Backup SQL");
							echo form_close()."<br/>";
						}
					?>
					</p>
				<h5>Liste des objets disponibles :</h5>
				<p>Cliquer sur une image pour accéder à l'objet.
					<?php 
						echo form_open('staff/gerer_items/item_editor/new_tuile', array('class' => 'inline-block'));
						echo form_submit(array('name'=>'new'),"Créer Tuile");
						echo form_close(); 
						echo form_open('staff/gerer_items/item_editor/new_item', array('class' => 'inline-block'));
						echo form_submit(array('name'=>'new'),"Créer Item");
						echo form_close()."<br />";
						foreach ($items as $categorie) :?>
				<table class="tableau">
					<caption><?= ucfirst($categorie[0]->type) ?></caption>
					<thead>
						<tr>
							<th>Nom</th>
							<th>Item</th>
							<th>Prix</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($categorie as $objet): 
							// L'image est plus large que haute
							if($objet->tx>$objet->ty){
								$dx = 70;
								$ratio = 70/$objet->tx;
								$dy = round($objet->ty*$ratio);
							}
							// L'image est plus haute que large
							elseif($objet->ty>$objet->tx){
								$dy = 70;
								$ratio = 70/$objet->ty;
								$dx = round($objet->tx*$ratio);
							}
							// L'image est carré
							else{
								$dx = 70;
								$dy = 70;
							}
						?>
						<tr>
							<td><?= $objet->nom ?></td>
							<td><a href="<?= site_url('staff/gerer_items/item_editor/'.$objet->id) ?>"><?=img_tag('map/objets/'.$objet->img.'.png',$objet->nom, $objet->nom, $dx, $dy);?></a></td>
							<td><?= $objet->prix.' '.$objet->monnaie ?></td>
						</tr>
						<?php endforeach ; ?>
					</tbody>
				</table>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>