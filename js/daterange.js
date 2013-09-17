// Date Selection by Timothy Pace

function reload_date_range() {
   showLoader (true);
   var start = $( "#start-picker" ).datepicker( "getDate" ).toISOString();
   var end = $( "#end-picker" ).datepicker( "getDate" ).toISOString();
   var getURL = "http://re-dev.soe.ucsc.edu/sandbox/data/history.php?";
   if (start) getURL += "&start="+start;
   if (end) getURL += "&end="+end;
   $.getJSON(getURL, function(data) {
      updateCharts(data, false);
      updateWindRose(data);
      showLoader (false);
   });
}

function initial_date_range(start, end) {
   var getURL = "http://re-dev.soe.ucsc.edu/sandbox/data/history.php?";
   if (start) getURL += "&start="+start;
   if (end) getURL += "&end="+end;
   makeCharts();
   showLoader (true);
   $.getJSON(getURL, function(data) {
      updateCharts(data, true);
      setup_winddirection(data);
      showLoader (false);
   });
}

