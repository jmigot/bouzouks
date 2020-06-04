$(document).ready(function()
{
	$('.joueur form[class=supprimer]').submit(function()
	{
		return confirm("Veux-tu vraiment supprimer ce bouzouk de ta liste d'amis ?");
	});
});