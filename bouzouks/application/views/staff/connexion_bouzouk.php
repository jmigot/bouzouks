<?php $this->layout->set_title('Admin - Connexion autre pseudo'); ?>

<div id="staff-connexion_bouzouk">
	<div class="cellule_bleu_type1 marge_haut">
		<h4>Connexion sous un autre pseudo</h4>
		<div class="bloc_bleu">
			<p class="centre highlight"><a href="<?= site_url('staff/accueil') ?>">Retour accueil</a></p>
			
			<?= form_open('staff/connexion_bouzouk') ?>
				<p class="centre margin">
					Pseudo :
					<?= $select_joueurs ?>
					<input type="submit" value="Connexion"><br>
					<input type="checkbox" name="connexion_tobozon" id="connexion_tobozon"><label for="connexion_tobozon">Connecter également au tobozon</label>
				</p>
			</form>
		</div>
	</div>
</div>

 
