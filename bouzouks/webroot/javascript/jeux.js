$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Bonneteau                               */
	/*--------------------------------------------------*/
	var bol_1 = false;
	var bol_2 = false;
	var bol_3 = false;

	// On regarde quels bols sont baissés
	$("#jeux-bonneteau .bols button img").each(function()
	{
		// Si le bol est levé
		if ($(this).attr('src') != img_url + 'jeux/bonneteau_1.png' &&
			$(this).attr('src') != img_url + 'jeux/bonneteau_2.png' &&
			$(this).attr('src') != img_url + 'jeux/bonneteau_3.png')
		{
			// On récupère le numéro du bol
			var numero_bol = $(this).parent().val();

			// On le baisse après un petit temps
			setTimeout(function() { $("#jeux-bonneteau .bols button[value="+numero_bol+"] img").attr('src', img_url+'jeux/bonneteau_'+numero_bol+'.png'); }, 2000);
		}
	});
}); 
