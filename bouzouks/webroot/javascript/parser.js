$(document).ready(function()
{
	/* BBCode */
	function remplace_bbcode(element_id, balise_debut, balise_fin)
	{
		var textarea     = $('#'+element_id);
		var top          = textarea.scrollTop();
		var taille       = textarea.val().length;
		var debut        = textarea[0].selectionStart;
		var fin          = textarea[0].selectionEnd;
		var selection    = textarea.val().substring(debut, fin);
		var remplacement = balise_debut + selection + balise_fin;
		
		// On remplace le bbcode
		textarea.val(textarea.val().substring(0, debut) + remplacement + textarea.val().substring(fin, taille));
		
		// On re-scroll là où on était
		textarea.scrollTop(top);
	}
	
	$('input[class*=bbcode_]').click(function()
	{
		var texte = $('span[id='+$(this).attr('class')+']').text();
		var tmp = texte.split('|');
		return remplace_bbcode(tmp[0], tmp[1], tmp[2]);
	}); 
});
