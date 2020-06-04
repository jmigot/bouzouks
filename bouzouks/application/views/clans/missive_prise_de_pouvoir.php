Bonjour <?= $maire->pseudo ?>,

Ceci est une missive officielle suite à l'action <span class="pourpre">Prise de pouvoir</span> du clan <?= $nom_clan ?> et ses alliés.
Cette action a pour but de te destituer de ton poste de maire afin de te remplacer par le chef du clan sus-mentionné.

Plusieurs choix s'offrent maintenant à toi et tu dois prendre une décision avant la prochaine maintenance faute de quoi l'action "Céder ma place" sera validée automatiquement.
Voici les différents choix dont tu disposes :

- [u]Céder ma place[/u] : l'action sera validée, tu seras destitué de ton poste et le chef de clan te remplacera
- [u]Sanction aléatoire[/u] : l'action sera annulée mais tu subiras une sanction aléatoire parmis les suivantes : "5 objets supprimés de la maison", "amende de 10 000 struls", "remise à zéro de tes points d'action", "envoi à l'asile" ou "cession de la mairie au chef de clan"
- [u]Envoyer la bouzopolice[/u] : l'action sera annulée mais cela te coutera 10% de points d'action en plus que le prix de l'enchère du clan pour cette action, soit <span class="pourpre"><?= $prix_bouzopolice ?> p.a de ta poche</span>; tant que tu n'auras pas assez de p.a, ce choix ne fonctionnera pas

<div class="centre"><?= form_open('clans/reponse_prise_de_pouvoir', array('class' => 'inline-block note_service')) ?><p><input type="hidden" name="decision" value="ceder"><input type="submit" value="Céder ma place"></p></form><?= form_open('clans/reponse_prise_de_pouvoir', array('class' => 'inline-block note_service')) ?><p><input type="hidden" name="decision" value="sanction"><input type="submit" value="Sanction aléatoire"></p></form><?= form_open('clans/reponse_prise_de_pouvoir', array('class' => 'inline-block note_service')) ?><p><input type="hidden" name="decision" value="bouzopolice"><input type="submit" value="Bouzopolice (<?= $prix_bouzopolice ?> p.a)"></p></form></div>

Merci donc de nous faire parvenir ton choix dans les plus brefs délais. Nous te conseillons de ne pas supprimer cette missive avant demain.<br>
Bon courage :)