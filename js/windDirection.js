// Wind Direction JSON Rose Chart
// Bryan Smith

function setup_winddirection(data) {
tabulateWindRose(data.windDir);
// Parse the data from an inline table using the Highcharts Data plugin
$('#winddirection').highcharts({
   data: {
      table: 'freq',
      startRow: 1,
      endRow: 17,
      endColumn: 7
   },
   chart: {
      polar: true,
      type: 'column',
      width: 500,
      height: 300,
   },
   colors: ['#CCFFFF', '#99FFCC', '#99FF99', '#99FF66', '#99FF00', '#CCFF00', '#FFFF00'],
   title: {
      text: 'Wind Rose'
   },
   pane: {
      size: '85%'
   },

   legend: {
      reversed: true,
      align: 'right',
      verticalAlign: 'top',
      y: 100,
      layout: 'vertical'
   },

   xAxis: {
      tickmarkPlacement: 'on'
   },

   yAxis: {
      min: 0,
      endOnTick: false,
      showLastLabel: true,
      title: {
         text: 'Frequency (%)'
      },
      labels: {
         formatter: function () {
            return this.value + '%';
         }
      }
   },
   
   tooltip: {
   backgroundColor: 'rgba(255, 255, 255, 1)',
   valueSuffix: '%',
   valueDecimals: 2,
   },

   plotOptions: {
      series: {
         stacking: 'normal',
         shadow: false,
         groupPadding: 0,
         pointPlacement: 'on'
      }
   },
});
   windDirection = $('#winddirection').highcharts();
}


// Generate table for wind rose chart
// Timothy Pace
function tabulateWindRose(windDir) {
   legend = ["&lt; 3 mph", "3-7 mph", "7-12 mph", "12-17 mph", "17-24 mph", "24-30 mph", "&gt; 30 mph", "Total"];
   directions = ["N","NNE","NE","ENE","E","ESE", "SE", "SSE","S","SSW","SW","WSW","W","WNW","NW","NNW"];
   var table = document.createElement('table');
   table.id = "freq";
   table.border = 0;
   table.cellSpacing = 0;
   table.cellPadding = 0;
   var tr = document.createElement('tr');
   tr.setAttribute("noWrap",true)
   tr.bgColor = "#CCCCFF";
   var th = document.createElement('th');
   th.colSpan = 9;
   th.className = "hdr";
   th.innerHTML = "Table of Frequencies (percent)";
   tr.appendChild(th);
   table.appendChild(tr);
   tr = document.createElement('tr');
   tr.setAttribute("noWrap",true)
   tr.bgColor = "#CCCCFF";
   th = document.createElement('th');
   th.className = "freq";
   th.innerHTML = "Direction";
   tr.appendChild(th);
   for (var i = 0; i < legend.length-1; i++) {
      th = document.createElement('th');
      th.className = "freq";
      th.innerHTML = legend[i];
      tr.appendChild(th);
   }
   table.appendChild(tr);
   for (var i = 0; i < directions.length; i++) {
      tr = document.createElement('tr');
      tr.setAttribute("noWrap",true)
      if (i % 2 != 0) tr.bgColor = "#DDDDDD";
      var td = document.createElement('td');
      td.className = "dir";
      td.innerHTML = directions[i];
      tr.appendChild(td);
      for (var j = 0; j < legend.length-1; j++) {
         td = document.createElement('td');
         td.className = "data";
         td.innerHTML = windDir[directions[i]][j];
         tr.appendChild(td);
      }
      table.appendChild(tr);
   }
   document.getElementById('wind-rose').appendChild(table);
}