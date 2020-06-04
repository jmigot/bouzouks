<div id="missives-lire">
	<div class="lettre">
		<!-- Message -->
		<div class="feuille">
			<div class="tete">
			</div>

			<!-- Date -->
			<p class="date">Le <?= bouzouk_datetime($date_envoi, 'court') ?></p>
			<div class="message">
				<!-- Objet -->
				<span class="highlight pourpre"><?= form_prep($objet) ?></span><br><br><br>
				
				<!-- Message -->
				<?php
					// Si l'expéditeur n'est pas un robot, on protège
					if ( ! $expediteur_robot)
						$message = form_prep($message);

					echo remplace_smileys($this->lib_parser->remplace_bbcode(nl2br(tab2spaces($message))));
				?>
			</div>
		</div>

		<!-- Enveloppe -->
		<div class="enveloppe">
			<p class="timbre"><img src="<?= img_url('missives/timbres/'.$timbre) ?>" width="62" height="73" alt="Timbre"></p>
			<div class="effet_lumiere">
			</div>

			<div class="contact">
				<p class="expediteur">
					De la part de <?= profil() ?><br>
					<?= $this->session->userdata('adresse') ?><br>
					Vlurxtrznbnaxl
				</p>
				<p class="destinataire">
					A l'attention de <?= profil($destinataire->id, $destinataire->pseudo) ?><br>
					<?= $destinataire->adresse ?><br>
					Vlurxtrznbnaxl
				</p>
			</div>
		</div>
	</div>

	<p class="clearfloat centre margin">
		<input type="button" value="Fermer" class="fermer_previsualisation">
	</p>
</div>
