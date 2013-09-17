// Solar Irradiance JSON Chart by Bryan Smith
function setup_solar() {
		// Create the chart
		$('#solarirradiance').highcharts('StockChart', {
         plotOptions : {
            line : {
               marker : {
                  enabled : false
               }
            }
         },
         chart: {
            width : 500,
            height : 300
         },
			rangeSelector : {
            selected : 0,
            buttons: [
               {
                  type: 'month',
                  count: 1,
                  text: '1m'
               }, {
                  type: 'month',
                  count: 3,
                  text: '3m'
               }, {
                  type: 'ytd',
                  text: 'YTD'
               }, {
                  type: 'year',
                  count: 1,
                  text: '1y'
               }, {
                  type: 'all',
                  text: 'All'
               }
            ]
         },
			title : {
				text : 'Solar Irradiance'
			},
         xAxis : {
            type: 'datetime'
         },
         yAxis : {
            title: {
               text: 'W/m^2'
            }
         },
			series : [{
				name : 'Irradiance',
            data : [[]],
				tooltip: {
					valueDecimals: 2,
               valueSuffix: ' W/m^2',
				}
			}]
		});
      solarIrradiance = $('#solarirradiance').highcharts();
}
