/*
File: JavaScript functions file to be used with the WPNG Calendar plugin for Wordpress
Plugin URI: http://code.google.com/p/wpng-calendar/

----------------------------------------------------------------------------
LICENSE
----------------------------------------------------------------------------
Copyright 2008  - L1 Jockeys  (email : l1jockeys@gmail.com)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
----------------------------------------------------------------------------
*/

$j=jQuery.noConflict();

/* Loads the Google data API */
google.load("gdata", "1");

/* Loads the Google map API */
//google.load("maps", "2");

/* Set global variables to store the dates being processed */
var firstDate = null;
var lastDate = null;
var geocoder = null;
var entryMap = null;

/**
 * Init the Google data JS client library with an error handler 
 */
function init() {
  /* initialize the error handler */
  google.gdata.client.init(handleGDError);
  /* initialize the Geocoder */
  //geocoder = new GClientGeocoder();
}

/**
 * Setup the Google Calendar service
 */
function setupCalendarService() {
  var calService = new google.gdata.calendar.CalendarService('wpng-calendar-plugin-1');
  return calService;
}

/**
 * Wrapper to pull more entries from the calendar for later dates.
 */
 function getLaterEntries() { 
  /* increment the date by one day before incrementing by the weeks interval */
  lastDate.add(1).days();
  var start = lastDate.clone();
  var end   = lastDate.clone().add(weeks).weeks();
  /* call the loadCalendar function with the new dates) */
  loadCalendar(start,end);
 }
 
/**
 * Wrapper to pull more entries from the calendar for older dates.
 */
 function getOlderEntries() { 
  /* decrement the date by one day before decrementing by the weeks interval */
  firstDate.add(-1).days();
  var end   = firstDate.clone();
  var start = firstDate.clone().add(-1 * weeks).weeks();
  /* call the loadCalendar function with the new dates) */
  loadCalendar(start,end);
 }

/**
 * Wrapper to query the calendar for dates from current through a number of weeks
 */  
function loadCalendarByWeeks() {
  /* default to current date */
  var start = Date.today();
  var end   = Date.today().add(weeks).weeks();
  loadCalendar(start, end);
}

/**
 * Uses Google data JS client library to retrieve a calendar entry from the specified
 * URI.
 *
 * @param {string} entryURI is the URI for the specific entry
 */  
function loadCalendarEntry(entryURI) {
  var service = setupCalendarService();
  service.getEventsEntry(entryURI, listEntry, handleGDError);
}
 
/**
 * Uses Google data JS client library to retrieve a calendar feed from the specified
 * URL.  The feed is controlled by several query parameters and a callback 
 * function is called to process the feed results.
 *
 * @param {start}  start date (JavaScript)
 *        {end}    end date   (JavaScript)
 */  
function loadCalendar(start, end) {
  var service = setupCalendarService();
  var query = new google.gdata.calendar.CalendarEventQuery(calendarURL);
  /* general query settings */
  query.setOrderBy('starttime');
  query.setSortOrder('ascending');
  query.setSingleEvents(true);
  /* pageMaxResults is set dynamically from wpng-calendar.php -> addWPNGSettings */
  query.setMaxResults(pageMaxResults);
  /* convert JS dates to Google GData DateTime */
  var startDateTime = new google.gdata.DateTime(start);
  var endDateTime = new google.gdata.DateTime(end);
  query.setMinimumStartTime(startDateTime);
  query.setMaximumStartTime(endDateTime);

  service.getEventsFeed(query, listEvents, handleGDError);
}

/**
 * Uses Google data JS client library to retrieve a calendar feed from the specified
 * URL.  The feed is controlled by several query parameters and a callback 
 * function is called to process the feed results.
 *
 * @param {size}  # of events to list (JavaScript)
 */  
function loadCalendarWidget() {
  var service = setupCalendarService();
  var query = new google.gdata.calendar.CalendarEventQuery(calendarURL);
  /* general query settings */
  query.setOrderBy('starttime');
  query.setSortOrder('ascending');
  query.setSingleEvents(true);
  query.setMaxResults(widgetListSize);
  /* convert JS dates to Google GData DateTime */
  var startDateTime = new google.gdata.DateTime(Date.today());
  var endDateTime = new google.gdata.DateTime(Date.today().add(6).months());
  query.setMinimumStartTime(startDateTime);
  query.setMaximumStartTime(endDateTime);

  service.getEventsFeed(query, listWidgetEvents, handleGDError);
}

/**
 * Callback function for the Google data JS client library to call with a feed 
 * of events retrieved.
 *
 * Creates a list of events in a human-readable form.  This list of
 * events is added into a div called 'wpng-cal-events'.  
 *
 * @param {json} feedRoot is the root of the feed, containing all entries 
 */ 
function listEvents(feedRoot) {
  var entries = feedRoot.feed.getEntries();
  var eventDiv = document.getElementById('wpng-cal-events');
  /* clear out anything in the current events DIV */
  while (eventDiv.firstChild) {
	  eventDiv.removeChild(eventDiv.firstChild);
  }
  /* loop through the events in the feed and output to the DIV */
  var prevDateString = null;
  var len = entries.length;
  /* the list is displayed in a table, let's create it */
  var table = document.createElement('table');
  var tableBody = document.createElement('tbody');
  table.setAttribute('className','wpng-page-list-table');
  table.setAttribute('class','wpng-page-list-table');
  for (var i = 0; i < len; i++) {
	  var entry = entries[i];
	  var times = entry.getTimes();
	  var startTime = times[0].getStartTime();
	  /* note: using cool functions from DateJS for formatting */
	  var displayTime = startTime.getDate().clone();
	  var dateString = null;
	  if (displayTime.clearTime().equals(Date.today())) {
		  dateString = 'Today';
	  }
	  else if (displayTime.clearTime().equals(Date.today().add(1).days())) {
		  dateString = 'Tomorrow';
	  }
	  else {
		  dateString = displayTime.toString('dddd, MMMM d, yyyy');
	  }
	  /* if the date has changed then output a new header row in the table */
	  if (dateString != prevDateString) {
		  var trHead = document.createElement('tr');
		  var tdHead = document.createElement('td');
		  tdHead.setAttribute('className','wpng-page-list-head');
		  tdHead.setAttribute('class','wpng-page-list-head');
		  tdHead.setAttribute('colSpan','2');
		  tdHead.setAttribute('colspan','2');
		  tdHead.appendChild(document.createTextNode(dateString));
		  trHead.appendChild(tdHead);
		  tableBody.appendChild(trHead);
		  prevDateString = dateString;
	  }
	  /* now display the event itself */
	  var timeString = 'All Day Event';
	  /* if the event has a time, override the default text */
	  if (!startTime.isDateOnly()) {
		  timeString = startTime.getDate().toString("h:mm tt")
	  }
	  /* create an anchor to the ThickBox remote call for the title */
	  var title = entry.getTitle().getText();
	  var uri = entry.getSelfLink().getHref();
	  var anchorTitle = document.createElement('a');
	  anchorTitle.setAttribute('href','javascript:loadCalendarEntry("' + uri + '")');
	  anchorTitle.appendChild(document.createTextNode(title));
	  
	  /* add the event time and title to the table */
	  var trEntry = document.createElement('tr');
	  var tdEntryTime = document.createElement('td');	  
	  var tdEntryTitle = document.createElement('td');	  
	  tdEntryTime.setAttribute('className','wpng-page-list-time');
	  tdEntryTime.setAttribute('class','wpng-page-list-time');
	  tdEntryTitle.setAttribute('className','wpng-page-list-title');
	  tdEntryTitle.setAttribute('class','wpng-page-list-title');
	  tdEntryTime.appendChild(document.createTextNode(timeString));
	  tdEntryTitle.appendChild(anchorTitle);
	  trEntry.appendChild(tdEntryTime);
	  trEntry.appendChild(tdEntryTitle);
	  tableBody.appendChild(trEntry);
	  /* get the date from the first / last entry */
	  if (i == 0) {
		  firstDate = displayTime;
	  }
	  else if (i == (len - 1)) {
	          lastDate = displayTime;
	  }
  }
  /* Append the table body to the table */
  table.appendChild(tableBody);
  
  /* if there were some events, add the table */
  if (len != 0) {
	  /* add the table to the event DIV */
	  eventDiv.appendChild(table);
  }
  else {
	  /* show a default message */
	  eventDiv.appendChild(document.createTextNode('No events to show.'));
  }
  	  
  /* at the end of the list, show the navigation links */
  if (showNav) {
	  eventDiv.appendChild(document.createElement('br'));
	  var navTable = document.createElement('table');
	  var navTableBody = document.createElement('tbody');
	  navTable.setAttribute('className','wpng-page-list-table');
	  navTable.setAttribute('class','wpng-page-list-table');
	  var row = document.createElement('tr');
	  
	  var tdOlder = document.createElement('td');
	  var anchorOlder = document.createElement('a');
	  anchorOlder.setAttribute('href','javascript:getOlderEntries()');
	  anchorOlder.appendChild(document.createTextNode('< Show older events'));
	  tdOlder.appendChild(anchorOlder);
	  row.appendChild(tdOlder);
	  
	  var tdLater = document.createElement('td');
	  tdLater.setAttribute('align','right');
	  var anchorLater = document.createElement('a');
	  anchorLater.setAttribute('href','javascript:getLaterEntries()');
	  anchorLater.appendChild(document.createTextNode('Show later events >'));
	  tdLater.appendChild(anchorLater);
	  row.appendChild(tdLater);
	  
	  navTableBody.appendChild(row);
	  navTable.appendChild(navTableBody);
	  eventDiv.appendChild(navTable);
  }
  
  /* Hide the loading image */
  $j("#wpng-cal-load-page").fadeOut("fast");
  
  /* Animate the display of the list */
  $j("#wpng-cal-events").slideDown("slow");
}

/**
 * Callback function for the Google data JS client library to call with a
 * single entry retreived
 *
 * Displays an event in a human-readable form.  This event is ultimately
 * displayed in a Facebox DIV
 *
 * @param {json} retrievedEntryRoot is the root of the entry
 */ 
function listEntry(retrievedEntryRoot) {
  /* get the entry */
  var entry = retrievedEntryRoot.entry;
  
  /* build the entry display for the Thickbox */
  var entryDiv = document.createElement('div');
  entryDiv.setAttribute('id','wpng-tb');
  
  /* get the title */
  var title = document.createElement('h2');
  title.appendChild(document.createTextNode(entry.getTitle().getText()));
  entryDiv.appendChild(title);
  
  /* display the date/time */
  var dateString = 'All Day Event';
  var times = entry.getTimes();
  if (times.length) {
	  /* if the event has a date & time, override the default text */
	  var startTime = times[0].getStartTime();
	  var endTime = times[0].getEndTime();
	  if (!startTime.isDateOnly()) {
		  dateString = startTime.getDate().toString("ddd, MMM d, yyyy h:mm tt");
	  }
	  else {
		  dateString = startTime.getDate().toString("ddd, MMM d, yyyy");
	  }
	  dateString += '   -   ';
	  if (!endTime.isDateOnly()) {
		  dateString += endTime.getDate().toString("ddd, MMM d, yyyy h:mm tt")
	  }
	  else {
		  dateString += endTime.getDate().toString("ddd, MMM d, yyyy");
	  }
  }
  var dateRow = document.createElement('div');
  dateRow.setAttribute('className','wpng-entry-label-row');
  dateRow.setAttribute('class','wpng-entry-label-row');
  
  dateLabel = document.createElement('div');
  dateLabel.appendChild(document.createTextNode('When: '));
  dateLabel.setAttribute('className','wpng-entry-label');
  dateLabel.setAttribute('class','wpng-entry-label');
  dateRow.appendChild(dateLabel);
  
  dateDisplay = document.createElement('div');
  dateDisplay.appendChild(document.createTextNode(dateString));
  dateDisplay.setAttribute('className','wpng-entry-label-text');
  dateDisplay.setAttribute('class','wpng-entry-label-text');
  dateRow.appendChild(dateDisplay);
  
  entryDiv.appendChild(dateRow);
  
  entryDiv.appendChild(document.createElement('br'));
  entryDiv.setAttribute('className','wpng-entry-break');
  entryDiv.setAttribute('class','wpng-entry-break');
  
  /* display the location */
  var locString = 'No location information';
  var locations = entry.getLocations();
  var locTemp = locations[0].getValueString();
  if (locTemp != null) {
	  locString = locTemp
  }
  var locRow = document.createElement('div');
  locRow.setAttribute('className','wpng-entry-label-row');
  locRow.setAttribute('class','wpng-entry-label-row');
  
  locLabel = document.createElement('div');
  locLabel.appendChild(document.createTextNode('Where: '));
  locLabel.setAttribute('className','wpng-entry-label');
  locLabel.setAttribute('class','wpng-entry-label');
  locRow.appendChild(locLabel);
  
  locDisplay = document.createElement('div');
  locDisplay.appendChild(document.createTextNode(locString));
  locDisplay.setAttribute('className','wpng-entry-label-text');
  locDisplay.setAttribute('class','wpng-entry-label-text');
  locRow.appendChild(locDisplay);
  
  entryDiv.appendChild(locRow);
  
  /* add a link to Google map the location, if something was there */
  if (locTemp != null) {
	  /* use browser sniffing to determine if IE or Opera (ugly, but required) */
	  /* thanks to => http://webbugtrack.blogspot.com/2007/10/bug-245-setattribute-style-does-not.html */
	  var isOpera = false;
	  var isIE = false;
	  var agt=navigator.userAgent.toLowerCase();
	  var appVer = navigator.appVersion.toLowerCase();
	  var iePos  = appVer.indexOf('msie');
	  if(typeof(window.opera) != 'undefined'){isOpera = true;}
	  if(!isOpera && iePos !=-1){isIE = true;};
	  
	  var locMapAnchor = document.createElement('a');
	  locMapAnchor.setAttribute('id','wpng-map-link');
	  locMapAnchor.setAttribute('href','http://maps.google.com/maps?hl=en&q=' + locString);
	  locMapAnchor.setAttribute('target','_blank');
	  if(!isIE){
		  /* use the correct DOM Method */
		  locMapAnchor.setAttribute('style','float:right;');
	  } else {
		  /* use the .cssText hack */
		  locMapAnchor.style.setAttribute('cssText', 'float:right;');
	  }
	  locMapAnchor.appendChild(document.createTextNode('Map'));
	  entryDiv.appendChild(locMapAnchor);
  }
  
  entryDiv.appendChild(document.createElement('br'));
  
  /* display the description */
  var descString = 'No description';
  var tempString = entry.getContent().getText();
  if (tempString != null) {
	  descString = tempString;
  }
  var descDisplay = document.createElement('div');
  descDisplay.setAttribute('className','wpng-entry-desc');
  descDisplay.setAttribute('class','wpng-entry-desc');
  // Translate description wikitext into HTML if the option is enabled
  if (parseWiki) {
	  var descHTML = Wiky.toHtml(descString);
	  descDisplay.innerHTML = descHTML;
  }
  else {
	  descDisplay.appendChild(document.createTextNode(descString));
  }
  
  entryDiv.appendChild(descDisplay);
  
  /* add the map div (may get called upon later) */
  /* TODO: Get fancy with it, see showEntryMap below
  var mapDiv = document.createElement('div');
  mapDiv.setAttribute('id','wpng-entry-map');
  mapDiv.setAttribute('style','display:none;height:400px');
  entryDiv.appendChild(mapDiv);
  */
  
  /* add the div to my modified ThickBox function */
  tb_show_inner("",entryDiv.innerHTML,"height=500&width=500");
}

/**
 * Callback function for the Google data JS client library to call with a feed 
 * of events retrieved.
 *
 * Creates a list of events in a human-readable form. This list of
 * events is intended to be shown in a Wordpress sidebar widget
 *
 * @param {json} feedRoot is the root of the feed, containing all entries 
 */ 
function listWidgetEvents(feedRoot) {
  var entries = feedRoot.feed.getEntries();
  var eventDiv = document.getElementById('wpng-cal-widget-events');
  /* clear out anything in the current events DIV */
  while (eventDiv.firstChild) {
	  eventDiv.removeChild(eventDiv.firstChild);
  }
  /* loop through the events in the feed and output to the DIV */
  var prevDateString = null;
  var len = entries.length;
  var ulist = null;
  for (var i = 0; i < len; i++) {
	  var entry = entries[i];
	  var times = entry.getTimes();
	  var startTime = times[0].getStartTime();
	  /* note: using cool functions from DateJS for formatting */
	  var displayTime = startTime.getDate().clone();
	  var dateString = null;
	  if (displayTime.clearTime().equals(Date.today())) {
		  dateString = 'Today';
	  }
	  else if (displayTime.clearTime().equals(Date.today().add(1).days())) {
		  dateString = 'Tomorrow';
	  }
	  else {
		  dateString = displayTime.toString('MMM dd');
	  }
	  /* if the date has changed then output a new title and start a new list */
	  if (dateString != prevDateString) {
		  /* append the list to the event DIV, unless this is the first one */
		  if (ulist != null) {
			  eventDiv.appendChild(ulist);
		  }
		  var titleDiv = document.createElement('div');
		  titleDiv.setAttribute('className','wpng-widget-date-title');
		  titleDiv.setAttribute('class','wpng-widget-date-title');
		  titleDiv.appendChild(document.createTextNode(dateString));
		  eventDiv.appendChild(titleDiv);
		  prevDateString = dateString;
		  /* the events are displayed in an undefined list, let's create it */
		  ulist = document.createElement('ul');
	  }
	  /* now display the event itself as an item in the list */
	  
	  /* create an anchor to the Facebox remote call for the title */
	  var title = entry.getTitle().getText();
	  var uri = entry.getSelfLink().getHref();
	  var anchorTitle = document.createElement('a');
	  anchorTitle.setAttribute('className','thickbox');
	  anchorTitle.setAttribute('class','thickbox');
	  anchorTitle.setAttribute('href','javascript:loadCalendarEntry("' + uri + '")');
	  anchorTitle.appendChild(document.createTextNode(title));
	  
	  /* add the event to the list */
	  var item = document.createElement('li');
	  item.appendChild(anchorTitle);
	  ulist.appendChild(item);
  }
  
  /* append the last list to the event DIV */
  if (ulist != null) {
	  eventDiv.appendChild(ulist);
  }
  
  /* if there were not any events, display a default message */
  if (len == 0) {
	  /* show a default message */
	  eventDiv.appendChild(document.createTextNode('No events to show.'));
  }
  
  /* Hide the loading image */
  $j("#wpng-cal-load-widget").fadeOut("fast");
  
  /* Animate the display of the list */
  $j("#wpng-cal-widget-events").slideDown("slow");
  
}

/**
 * Show a map using the description information using the Google Map API
 * TODO: Add a AJAX grabbed map to the Facebox using this function
 */
 
/*
function showEntryMap(address) { 
  entryMap = new google.maps.Map2(document.getElementById("wpng-entry-map"));
  geocoder.getLatLng(
    address,
    function(point) {
      if (!point) {
        alert(address + " not found");
      } else {
        entryMap.setCenter(point, 13);
        var marker = new GMarker(point);
        entryMap.addOverlay(marker);
        marker.openInfoWindowHtml(address);
      }
    }
  );
  var test = jQuery('#wpng-entry-map').slideDown();
}
*/

/**
 * Callback function for the Google data JS client library to call when an error
 * occurs during the retrieval of the feed.  Details available depend partly
 * on the web browser, but this shows a few basic examples. In the case of
 * a privileged environment using ClientLogin authentication, there may also
 * be an e.type attribute in some cases.
 *
 * @param {Error} e is an instance of an Error 
 */
function handleGDError(e) {
  document.getElementById('jsSourceFinal').setAttribute('style', 
      'display:none');
  if (e instanceof Error) {
    /* alert with the error line number, file and message */
    alert('Error at line ' + e.lineNumber +
          ' in ' + e.fileName + '\n' +
          'Message: ' + e.message);
    /* if available, output HTTP error code and status text */
    if (e.cause) {
      var status = e.cause.status;
      var statusText = e.cause.statusText;
      alert('Root cause: HTTP error ' + status + ' with status text of: ' + 
            statusText);
    }
  } else {
    alert(e.toString());
  }
}

/* initialize the Google error handler */
google.setOnLoadCallback(init);
