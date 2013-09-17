var global_data;
Highcharts.setOptions({
   global: {
      useUTC: false
   }
});
$(document).ready(function() {
   $.getJSON('http://re-dev.soe.ucsc.edu/sandbox/data/history.php?end=2013-8-19', function(data) {
      start_WharfWindSpeed(data);
      start_WharfSolarIrradiance(data);
      start_WharfEnergyGenerated(data);
   });
});