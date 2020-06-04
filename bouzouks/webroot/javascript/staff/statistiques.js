$(document).ready(function()
{
	/*--------------------------------------------------*/
	/*          Inscriptions                            */
	/*--------------------------------------------------*/
	if ($('#staff-statistiques-inscriptions').size())
	{
		$('#graphique').css('height', '500px');
		var container = document.getElementById('graphique');
		var graph;
		var donnees = JSON.parse($('#donnees').text());
		var data = [];

		$.each(donnees, function(date, nombre) {
			data.push([parseInt(date * 1000), nombre]);
		});

		options = {
			grid: {
				tickColor: '#BBBBBB',
			},
			xaxis: {
				mode: 'time',
				title: "Jour d'inscription",
				labelsAngle: 45,
				noTicks: 35,
			},
			yaxis: {
				title: "Nombre d'inscrits",
				noTicks: 20,
				tickDecimals: 0,
				min: 0,
 				max: 80,
			},
			selection: {
				mode: "x"
			},
			HtmlText: false,
			title: "Inscriptions par jour"
		};

		function drawGraph(opts) {
			o = Flotr._.extend(Flotr._.clone(options), opts || {});
			return Flotr.draw(container, [data], o);
		}

		graph = drawGraph();

		Flotr.EventAdapter.observe(container, "flotr:select", function(area) {
			graph = drawGraph({
				xaxis: {
					min: area.x1,
					max: area.x2,
					mode: "time",
					labelsAngle: 45
				},
				yaxis: {
					min: area.y1,
					max: area.y2
				}
			});
		});

		Flotr.EventAdapter.observe(container, "flotr:click", function() {
			graph = drawGraph();
		});
	}

	/*--------------------------------------------------*/
	/*          Plus de struls                          */
	/*--------------------------------------------------*/
	else if ($('#staff-statistiques-plus_de_struls').size())
	{
		$('#graphique').css('height', '500px');
		var container = document.getElementById('graphique');
		var graph;
		var donnees = JSON.parse($('#donnees').text());
		var data = [];
		
		$.each(donnees, function(date, nombre) {
			data.push([parseInt(date * 1000), nombre]);
		});
		
		options = {
			grid: {
				tickColor: '#BBBBBB',
			},
			xaxis: {
				mode: 'time',
				title: "Jour",
				labelsAngle: 45,
				noTicks: 35,
			},
			yaxis: {
				title: "Nombre d'Allopass",
				min: 0,
				max: 150,
				noTicks: 20,
				tickDecimals: 0,
			},
			selection: {
				mode: "x"
			},
			HtmlText: false,
			title: "Allopass par jour"
		};
		
		function drawGraph(opts) {
			o = Flotr._.extend(Flotr._.clone(options), opts || {});
			return Flotr.draw(container, [data], o);
		}
		
		graph = drawGraph();
		
		Flotr.EventAdapter.observe(container, "flotr:select", function(area) {
			graph = drawGraph({
				xaxis: {
					min: area.x1,
					max: area.x2,
					mode: "time",
					labelsAngle: 45
				},
				yaxis: {
					min: area.y1,
					max: area.y2
				}
			});
		});
		
		Flotr.EventAdapter.observe(container, "flotr:click", function() {
			graph = drawGraph();
		});
	}
});


