$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Objets                                  */
	/*--------------------------------------------------*/
	$('#staff-objets input[name=faim], #staff-objets input[name=sante], #staff-objets input[name=stress], #staff-objets input[name=prix]').bind('keypress keydown keyup mouseover', function()
	{
		var objet_id = $(this).attr('class');
		var faim     = $('#staff-objets input[name=faim][class='+objet_id+']').val();
		var sante    = $('#staff-objets input[name=sante][class='+objet_id+']').val();
		var stress   = $('#staff-objets input[name=stress][class='+objet_id+']').val();
		var prix     = $('#staff-objets input[name=prix][class='+objet_id+']').val();

		if (faim == '' || sante == '' || stress == '' || prix == '' || isNaN(faim) || isNaN(sante) || isNaN(stress) || isNaN(prix))
		{
			$('#staff-objets span[class='+objet_id+']').html('&nbsp;');
			return;
		}

		faim   = parseInt(faim);
		sante  = parseInt(sante);
		stress = parseInt(stress);
		prix   = parseFloat(prix);

		if (prix == 0)
		{
			$('#staff-objets span[class='+objet_id+']').html('&nbsp;');
			return;
		}
		
		var rentabilite = Math.round((faim + sante - stress) / prix * 100.0) / 100;
		$('#staff-objets span[class='+objet_id+']').html(rentabilite);
	});
});


