<?php
$this->layout->set_title($title);
$this->load->view('staff/vlux/menu_admin', array('lien' => $lien));
?>
	<!-- Liste des maps -->
		<div class="cellule_bleu_type1 marge_haut">
			<h4>Les maps</h4>
				<article class=" bloc_bleu">
				<p class="margin">
					L'Editor permet de modifier la configuration d'une map. Le Creator permet de modifier le décor de la map.<br/>
					Le statut défini le niveau d'accès requis pour modifier la map et l'état dans lequel elle se trouve. Le passage en mode "création" aura pour effet sortir les bouzouks présent afin d'éviter les conflits.
				</p>
				<table class="tableau">
					<caption>Liste des maps</caption>
					<tfoot><tr><th>
					<?php echo(form_open('staff/gerer_maps/map_editor/new', array('class' => 'inline-block'))) ?>
					<p><input type="submit" value="Créer une nouvelle map" class="confirmation"></p>
					</form></th></tr></tfoot>
					<thead>
						<tr>
							<th>Map Editor</th>
							<th>Statut</th>
							<th>Propiétaire</th>
							<th>Map Creator</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($maps as $map):?>
						<tr>
							<td>
								<?php if($map['type'] != 'meta') : ?>
								<a href="<?= site_url('staff/gerer_maps/map_editor/'.$map['id']) ?>">
								<?php endif; ?>
									<?= $map['nom']?>
								<?php if($map['type']!='meta') : ?>
								</a>
								<?php endif; ?>
							</td>
							<td><?= $map['etat']?></td>
							<td><?= profil($map['proprio_id'], $map['pseudo'] ,$map['rang']) ?></td>
							<td><a href="<?= site_url('staff/gerer_maps/map_creator/'.$map['id']) ?>"><?= img_tag('map/interface/creator_staff.png', "Map Creator", 'Go Creator !') ?></a></td>
						</tr>
					<?php endforeach ; ?>
					</tbody>
				</table>
			</article>
		</div>
