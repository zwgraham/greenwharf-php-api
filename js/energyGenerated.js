// Energy Generated JSON Stacked Line Chart by Bryan Smith
function setup_energygenerated() {
      $('#energygenerated').highcharts('StockChart', {
         colors: ['#00B2EE', '#33FF33'],
         chart: {
            type : 'area',
            width : 500,
            height : 374
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
				text : 'Energy Generated'
			},
         yAxis : {
            title: {
               text : "Watts"
            }
         },
			legend : {
            enabled : true
         },
			series : [{
				name : 'Solar',
				tooltip: {
					valueDecimals: 2,
               valueSuffix: ' watts'
				}
			}, {
				name : 'Turbine',
            data : [[]],
				tooltip: {
					valueDecimals: 2,
               valueSuffix: ' watts'
				}
			}],
         plotOptions : {
            series : {
               stacking : "normal"
            },
         }
		});
      energyGenerated = $('#energygenerated').highcharts();
}
    
