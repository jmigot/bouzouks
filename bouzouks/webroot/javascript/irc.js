$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Ouverture des tchats en popup           */
	/*--------------------------------------------------*/
	$('#site-tchat form').submit(function(event)
	{
		// On récupère le pseudo
		var pseudo = $('#site-tchat input[name=pseudo]').val();

		// On ouvre le tchat dans une nouvelle fenêtre
		window.open($(this).attr('action')+'&nick='+pseudo,"tchat","width=800,height=400,location=no,toolbar=no,menubar=no");
		
		// On empêche le formulaire d'être validé
		event.preventDefault();
	});

	$('#site-tchat .au_secours').click(function(event)
	{
		$('#site-tchat .invisible').slideToggle();
		event.preventDefault();
	});
}); 
