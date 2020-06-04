<?php
$this->layout->set_title('Map Creator');
$this->layout->ajouter_javascript('libs/jquery.pp3Diso.js');

$this->layout->ajouter_css('pp3diso.css');
$this->layout->ajouter_javascript('vlux/map_creator.js');
$this->layout->ajouter_javascript('vlux/vlux_functions.js');
$this->layout->ajouter_javascript_externe($io_url);
?>
<div id="map-index">
	<?php
	if($this->router->method=='creation'){
		$this->load->view('vlux/menu', array('lien'=> $lien));
	}
	else{
		$this->load->view('staff/vlux/menu_admin', array('lien' => $lien));
	} 
?>


	<div id="total">
		<div class="lumiere">
			<div id="carte">
				<div id="ppISO">
					<div id="pp3diso-fleche-se" class="pp3diso_users fleche"></div>
					<div id="pp3diso-fleche-ne" class="pp3diso_users fleche"></div>
					<div id="pp3diso-fleche-so" class="pp3diso_users fleche"></div>
					<div id="pp3diso-fleche-no" class="pp3diso_users fleche"></div>
					<div id="vlux_sub_menu" class="pp3diso_users">
						
						<?php foreach ($objets as $type => $cats) : ?>
						<ul class="type vlux_display_off menu_type" id="vlux_type_<?= $type ?>">
							<?php foreach($cats as $cat => $item) : ?>
								<li>
									<button onclick="display_cat('<?= $type ?>_<?= $cat ?>')"><?= ucfirst($cat) ?></button>
								</li>
							<?php endforeach ; ?>
						</ul>
						<?php endforeach; ?>
						<div class="deco">
						</div>
						<div id="display_item">
						<?php foreach ($objets as $type) : ?>
							<?php foreach($type as $cat) : ?>
							<div class="cat vlux_display_off" id="vlux_<?= $cat[0]['type'] ?>_<?= $cat[0]['cat'] ?>">
								<div class="vlux_slider">
									<ul class="saut"> 
										<?php 
										foreach ($cat as $item) :
										?>
										 <li>
										<a href="javascript:change_cursor(<?= $item['id']?>);"><?php img_tag("map/objets/".$item['img'].'.png', ucfirst($item['nom']),''); ?></a>
										 </li>
										<?php endforeach; ?>
									</ul>
								</div>
								<div class="suiv"></div>
								<div class="prec"></div>
							</div>
							<?php endforeach; ?>
						<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
			<div id="vlux_interface" >
				<ul id="vlux_zoom" class="pp3diso_users">
					<li id="loupe-plus" ><a href="javascript:zoom_plus();"></a></li>
					<li id="loupe-moins" ><a href="javascript:zoom_moins();"></a></li>
				</ul>
				<ul id="menu_annul" class="vlux_menu pp3diso_users hidden">
					<li id="annulation">
						<a href="javascript:abort()">Annuler</a>
					</li>
				</ul>
				<ul id="menu_std" class="vlux_menu pp3diso_users">
					<li class="marge_droite">
						<a href="javascript:enregistrer_map()">Sauvegarder</a>
					</li>
					<?php foreach ($objets as $type => $cat) : ?>
					<li>
						<button onclick="display_type('<?= $type ?>')"><?= ucfirst($type) ?></button>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>