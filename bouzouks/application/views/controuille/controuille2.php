<?php $this->layout->set_title('Examen n°2'); ?>

<div id="controuille_controuille">
	<?= form_open('controuille/controuille2') ?>

	<div class="feuille">
		<div class="antiseche1 postit_note rotate-4">
			<p>Antisèche<br>
				Si tu rate ce controuïlle tu<br>peux pointer au chômage...<p>
		</div>
		<div class="antiseche2 postit_note rotate4">
			<p>&nbsp;Désolé mais ne je peux plus<br>rien pour toi là... T'es trop mauvais.<p>
		</div>
		<div class="antiseche3 postit_note rotate-4">
			<p>Trop dur ? Rappelle toi<br>qu'un jour le grand chêne a<br>été un gland comme toi...<p>
		</div>
		<div class="tete">
		</div>


		<!-- En-tête -->
		<div class="en-tete">
			<p class="fl-droite"><?= bouzouk_date() ?></p>
			<p class="titre">Controuïlle n°2</p>
		</div>

			<!-- Questions -->
			<div class="questions">
				<!-- Question 1 -->
				<p><u><b>Question 1 : </b>donne la réponse à ce calcul bouzouk.</u><br>
				(ZIGZIG + DRU - DRUGOZ + KAHSTO) x GNEE + GLAP = ?
				<p class="reponses">
					<input type="radio" name="question1" id="question1_1" value="1"><label for="question1_1">BLAZ</label>
					<input type="radio" name="question1" id="question1_2" value="2"><label for="question1_2">BNZ</label>
					<input type="radio" name="question1" id="question1_3" value="3"><label for="question1_3">GLAP</label>
					<input type="radio" name="question1" id="question1_4" value="4"><label for="question1_4">KAHKAH</label>
				</p>

				<!-- Question 2 -->
				<p><u><b>Question 2 : </b>quel est le plat préféré des bouzouks ?</u></p>
				<p class="reponses">
					<input type="radio" name="question2" id="question2_1" value="1"><label for="question2_1">Le Spaggiouili.</label><br>
					<input type="radio" name="question2" id="question2_2" value="2"><label for="question2_2">L'Excrément de Kramouth.</label><br>
					<input type="radio" name="question2" id="question2_3" value="3"><label for="question2_3">Le Beurkeur.</label><br>
					<input type="radio" name="question2" id="question2_4" value="4"><label for="question2_4">Le Chnoubznok avec de l'alcool à 90°.</label>
				</p>

				<!-- Question 3 -->
				<p><u><b>Question 3 : </b>comment fait un bouzouk pour se reproduire ?</u></p>
				<p class="reponses">
					<input type="radio" name="question3" id="question3_1" value="1"><label for="question3_1">Mystère et boule de gomme...</label><br>
					<input type="radio" name="question3" id="question3_2" value="2"><label for="question3_2">Demande à ta mère !</label><br>
					<input type="radio" name="question3" id="question3_3" value="3"><label for="question3_3">Par la trompe.</label><br>
					<input type="radio" name="question3" id="question3_4" value="4"><label for="question3_4">Il ne se reproduit pas et tant mieux !</label>
				</p>

				<!-- Question 4 -->
				<p><u><b>Question 4 : </b>quel est l'horrible destin des pioupiouks ?</u></p>
				<p class="reponses">
					<input type="radio" name="question4" id="question4_1" value="1"><label for="question4_1">Finir cuit dans un plat pour se faire manger par un bouzouk.</label><br>
					<input type="radio" name="question4" id="question4_2" value="2"><label for="question4_2">Se faire dévorer par un gros minet !</label><br>
					<input type="radio" name="question4" id="question4_3" value="3"><label for="question4_3">Qu'est-ce qu'on en a à faire des pioupiouks ?!</label><br>
					<input type="radio" name="question4" id="question4_4" value="4"><label for="question4_4">Partir se reproduire dans des contrées lointaines... Horrible !!</label>
				</p>

				<!-- Question 5 -->
				<p><u><b>Question 5 : </b>qu'est-ce que le Schnibble ?</u></p>
				<p class="reponses">
					<input type="radio" name="question5" id="question5_1" value="1"><label for="question5_1">Un animal.</label><br>
					<input type="radio" name="question5" id="question5_2" value="2"><label for="question5_2">Le bruit que fait un bouzouk en éternuant.</label><br>
					<input type="radio" name="question5" id="question5_3" value="3"><label for="question5_3">Un objet de culte.</label><br>
					<input type="radio" name="question5" id="question5_4" value="4"><label for="question5_4">J'en sais rien moi ! Je suis là que depuis 10 minutes !</label>
				</p>

				<!-- Question 6 -->
				<p><u><b>Question 6 : </b>penses-tu réellement que tu vas réussir ce devoir ?</u></p>
				<p class="reponses">
					<input type="radio" name="question6" id="question6_1" value="1"><label for="question6_1">Oui mais je suis un peu naïf...</label><br>
					<input type="radio" name="question6" id="question6_2" value="2"><label for="question6_2">Non, aucune chance avec les réponses débiles que vous proposez !</label><br>
					<input type="radio" name="question6" id="question6_3" value="3"><label for="question6_3">Heuuu.... Oui ?</label><br>
					<input type="radio" name="question6" id="question6_4" value="4"><label for="question5_4">Désolé, je manque de lucidité pour répondre.</label>
				</p>
			</div>
		</div>

		<!-- Envoyer -->
		<p class="centre clearfloat margin"><input type="submit" value="Rendre la copie à la prof" class="bouton_rouge surbrillance"></p>
	</form>
</div> 
