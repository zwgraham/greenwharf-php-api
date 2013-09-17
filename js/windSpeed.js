// Windspeed JSON Chart by Bryan Smith

function setup_windspeed() {
		// Create the chart
		$('#windspeed').highcharts('StockChart', {
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
				text : 'Wind Speed'
			},
         xAxis : {
            type: 'datetime',
         },
         yAxis: {
            title: {
               text: 'Wind speed (mph)'
            },
            min: 0,
            minorGridLineWidth: 0,
            gridLineWidth: 0,
            alternateGridColor: null,
            plotBands: [{ // Light air
               from: 0,
               to: 3,
               color: 'rgba(68, 170, 213, 0.1)',
               label: {
                  text: 'Light air',
                  style: {
                     color: '#000000'
                  }
               }
            }, { // Light breeze
                  from: 3,
                  to: 7,
                  color: 'rgba(0, 0, 0, 0)',
                  label: {
                     text: 'Light breeze',
                     style: {
                        color: '#000000'
                     }
                  }
            }, { // Gentle breeze
                  from: 7,
                  to: 12,
                  color: 'rgba(68, 170, 213, 0.1)',
                  label: {
                     text: 'Gengle breeze',
                     style: {
                        color: '#000000'
                     }
                  }
            }, { // Moderate breeze
                  from: 12,
                  to: 17,
                  color: 'rgba(0, 0, 0, 0)',
                  label: {
                     text: 'Moderate breeze',
                     style: {
                        color: '#000000'
                     }
                  }
            }, { // Fresh breeze
                  from: 17,
                  to: 24,
                  color: 'rgba(68, 170, 213, 0.1)',
                  label: {
                        text: 'Fresh breeze',
                     style: {
                        color: '#000000'
                     }
                  }
            }, { // Strong breeze
                  from: 24,
                  to: 30,
                  color: 'rgba(0, 0, 0, 0)',
                  label: {
                     text: 'Strong breeze',
                     style: {
                        color: '#000000'
                     }
                  }
               }, { // High wind
                  from: 30,
                  to: 100,
                  color: 'rgba(68, 170, 213, 0.1)',
                  label: {
                     text: 'High wind',
                     style: {
                        color: '#000000'
                     }
                  }
               }]
         },
			series : [{
				name : 'Wind',
            data : [[]],
				tooltip: {
					valueDecimals: 2,
               valueSuffix: ' mph'
				}
			}]
		});
      windSpeed = $('#windspeed').highcharts();
}
