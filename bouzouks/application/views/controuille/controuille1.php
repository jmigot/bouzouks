<?php $this->layout->set_title('Examen n°1'); ?>

<div id="controuille_controuille">
	<?= form_open('controuille/controuille1') ?>

	<div class="feuille">
		<div class="antiseche1 postit_note rotate-4">
			<p>Antisèche<br>
				Le résultat du controuïlle va influencer le début du jeu.<p>
		</div>
		<div class="antiseche2 postit_note rotate4">
			<p>&nbsp;Tu peux toujours choisir<br>de rater ta vie et répondre <br>au hazar tronche de bloubz !<p>
		</div>
		<div class="antiseche4 postit_note">
			<p><br><br>Vas lire le <a href="<?= site_url('site/lexique') ?>" title="Lexique">lexique</a>.<p>
		</div>
		<div class="antiseche3 postit_note rotate-4">
			<p>Antisèche<br>Certains mots sont spécifique<br> au language bouzouk.
				<p>
		</div>
		<div class="tete">
		</div>


		<!-- En-tête -->
		<div class="en-tete">
			<p class="fl-droite"><?= bouzouk_date() ?></p>
			<p class="titre">Controuïlle n°1</p>
		</div>

		<!-- Questions -->
		<div class="questions">
			<!-- Question 1 -->
			<p><u><b>Question 1 : </b>donne la réponse exacte à ces calculs bouzouk.</u><br>
			Rappel : la numérotation bouzouk comporte 10 chiffres<br>
			<em>GNEE=0 KAH=1 ZIG=2 STO=3 BLAZ=4 DRU=5 GOZ=6 POO=7 BNZ=8 GLAP=9</em></p>
			<p class="reponses">
				ZIG + DRU - GOZ =
				<input type="radio" name="question1a" id="question1a_1" value="1"><label for="question1a_1">KAH</label>
				<input type="radio" name="question1a" id="question1a_2" value="2"><label for="question1a_2">BNZ</label>
				<input type="radio" name="question1a" id="question1a_3" value="3"><label for="question1a_3">GLAP</label>
				<input type="radio" name="question1a" id="question1a_4" value="4"><label for="question1a_4">KAHKAH</label>
			</p>
			<p class="reponses">
				Si POOPOO=77 alors KAHKAHPOOPOO =
				<input type="radio" name="question1b" id="question1b_1" value="1"><label for="question1b_1">88</label>
				<input type="radio" name="question1b" id="question1b_2" value="2"><label for="question1b_2">1177</label>
				<input type="radio" name="question1b" id="question1b_3" value="3"><label for="question1b_3">17</label>
			</p>

			<!-- Question 2 -->
			<p><u><b>Question 2 : </b>quel est le nom de la ville ?</u></p>
			<p class="reponses bloc">
				<input type="radio" name="question2" id="question2_1" value="1"><label for="question2_1">Vurxtrznobouz</label><br>
				<input type="radio" name="question2" id="question2_2" value="2"><label for="question2_2">Vlurxtrznbnaxl</label>
			</p>
			<p class="reponses bloc">
				<input type="radio" name="question2" id="question2_3" value="3"><label for="question2_3">Simcity</label><br>
				<input type="radio" name="question2" id="question2_4" value="4"><label for="question2_4">Touxboomblog</label>
			</p>

			<!-- Question 3 -->
			<p><u><b>Question 3 : </b>quel est le terme bouzouk indiquant une situation anormale ?</u></p>
			<p class="reponses bloc">
				<input type="radio" name="question3" id="question3_1" value="1"><label for="question3_1">Bouzouter</label><br>
				<input type="radio" name="question3" id="question3_2" value="2"><label for="question3_2">Stroumpfer</label>
			</p>
			<p class="reponses bloc">
				<input type="radio" name="question3" id="question3_3" value="3"><label for="question3_3">Zloter</label><br>
				<input type="radio" name="question3" id="question3_4" value="4"><label for="question3_4">Zeferchier</label>
			</p>

			<!-- Question 4 -->
			<p><u><b>Question 4 : </b>que se passe-t-il si un bouzouk est trop stressé ?</u></p>
			<p class="reponses">
				<input type="radio" name="question4" id="question4_1" value="1"><label for="question4_1">Il se transforme en pioupiouk garou !</label><br>
				<input type="radio" name="question4" id="question4_2" value="2"><label for="question4_2">Il part en quête du Schnibble.</label><br>
				<input type="radio" name="question4" id="question4_3" value="3"><label for="question4_3">Il est pris de folie et est envoyé à l'asile psychiatrique.</label><br>
				<input type="radio" name="question4" id="question4_4" value="4"><label for="question4_4">Il perd sa trompe crânienne.</label>
			</p>

			<!-- Question 5 -->
			<p><u><b>Question 5 : </b>traduis cette phrase bouzouk</u><br>
			<em>&laquo; Quelque chose zlote, les struls de la mairie ont disparu ! Ça, c'est encore un coup du MLB ! Je vais me renseigner en consultant mon tobozon. &raquo;</em></p>
			<p class="reponses">
				<input type="radio" name="question5" id="question5_1" value="1"><label for="question5_1">Quelque chose s'est assombri, les lampes de la mairie ont disparu ! Ça, c'est encore un coup du Club des Bonnes Moeurs ! Je vais me renseigner sur le t'chat.</label><br>
				<input type="radio" name="question5" id="question5_2" value="2"><label for="question5_2">Quelque chose ne va pas, l'argent de la mairie a disparu ! Ça, c'est encore un coup du Mouvement Libérateur Bouzouk ! Je vais me renseigner sur le forum.</label><br>
				<input type="radio" name="question5" id="question5_3" value="3"><label for="question5_3">Quelque chose se complote, les membres de la mairie ont disparu ! Ça, c'est encore un coup du Mouvement Libérateur Bouzouk ! Je vais me renseigner sur le forum de discussion. </label>
			</p>
		</div>
	</div>

		<!-- Envoyer -->
		<p class="centre clearfloat margin"><input type="submit" value="Rendre la copie à la prof" class="bouton_rouge surbrillance"></p>
	</form>
</div>
