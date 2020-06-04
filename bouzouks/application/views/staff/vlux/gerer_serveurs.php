<?php 
	$this->layout->set_title($title);
	$this->load->view('staff/vlux/menu_admin', array('lien' => $lien));
 ?>

<div id="staff-gerer_serveurs">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Serveur Node.js</h4>
		<div class="bloc_bleu">
			<div class="margin">
				<?php img_tag($etat_serveur['img'],$etat_serveur['title'], $etat_serveur['alt']); ?>
				<p>Bientôt :
					<ul>
						<li>envoie message de masse, par map ou joueur</li>
						<li>modération et gestion du serveur (socket, room)</li>
					</ul></p>
			</div>
			<div class="margin">
				<p>Contrôles du serveur :</p>
				<!-- Démarrer/Arrêter/ Mettre en ligne-->
					<?php
					if (site_url()!='localhost') : 
						if($etat !=0) : echo(form_open('staff/gerer_serveurs/switch_serveur/0', array('class' => 'inline-block'))) ?>
						<p><input type="submit" value="Arrêter" class="confirmation"></p>
						</form>
						<?php endif;
						if ($etat !=1): echo(form_open('staff/gerer_serveurs/switch_serveur/1', array('class' => 'inline-block'))) ?>
						<p><input type="submit" value="Mode Admin" class="confirmation"></p>
						</form>
						<?php endif;
						if ($etat!=2) : echo(form_open('staff/gerer_serveurs/switch_serveur/2', array('class' => 'inline-block'))) ?>
						<p><input type="submit" value="Mode Béta" class="confirmation"></p>
						</form>
						<?php endif;
						if ($etat !=3 )  : echo(form_open('staff/gerer_serveurs/switch_serveur/3', array('class' => 'inline-block')))?>
						<p><input type="submit" value="Mode Ouvert" class="confirmation"></p>
						</form>
						<?php endif; 
					endif; ?>
					
			</div>
		</div>
	</div>
</div>