<input type="hidden" id="currentDate" value="" />
<div id="calendar">
	<table>
		<thead>
			<tr>
				<th colspan="2"></th>
				<th class="prev"><a href="#" onclick="prev();"><</a></th>
				<th id="month"></th>
				<th class="next"><a href="#" onclick="next();">></a></th>
				<th colspan="2"></th>
			</tr>
			<tr>
				<th class="week">Sun</th>
				<th class="week">Mon</th>
				<th class="week">Tue</th>
				<th class="week">Wed</th>
				<th class="week">Thu</th>
				<th class="week">Fri</th>
				<th class="week">Sat</th>
			</tr>
		</thead>
		<tbody id="weeks">
		</tbody>
	</table>
</div>
<script>
	//initialize calendar with current month
	var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
	var now = new Date();
	var firstDayOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
	$('#currentDate').val(firstDayOfMonth);
	$('#month').html(months[firstDayOfMonth.getMonth()] + '<br/>' + firstDayOfMonth.getFullYear());
	makeCalendar();

	//get selected date from the calendar
	function selectedDate(obj, day) {
		var firstDate = new Date($('#currentDate').val());
		var date = new Date(firstDate.getFullYear(), firstDate.getMonth(), day);
		if(obj.attr('class') == "day old") {
			var past = firstDate.setMonth(firstDate.getMonth() - 1, 1);
			var newDate = new Date(past);
			date = new Date(newDate.getFullYear(), newDate.getMonth(), day);
		} else if(obj.attr('class') == "day new") {
			var future = firstDate.setMonth(firstDate.getMonth() + 1, 1);
			var newDate = new Date(future);
			date = new Date(newDate.getFullYear(), newDate.getMonth(), day);
		}
		return date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate();
	}
	
	//ajax request here to edit event list on this day	
	function editEvents(obj, day) {
		location.href = base + 'events/view_by_day/' + selectedDate(obj, day);
	}
	
	//make calendar
	function makeCalendar() {	
		var firstDate = new Date($('#currentDate').val());
		var lastDateOfOldMonth = new Date(firstDate.getFullYear(), firstDate.getMonth(), 0);
		var lastDate = new Date(firstDate.getFullYear(), firstDate.getMonth() + 1, 0);
		var weeks = '';
		var noOfWeek = 1;
		//old days rows
		var count = 0;
		var dateStart = '';
		for(var i=6-lastDateOfOldMonth.getDay(); i <= 6; i++) {
			if (count == 0) {
				weeks += '<tr>';
			}
			count++;
			var day = lastDateOfOldMonth.getDate()+i-6;
			if(dateStart == '') {
				dateStart = lastDateOfOldMonth.getFullYear() + '-' + (lastDateOfOldMonth.getMonth()+1) + '-' + day;
			}
			weeks += '<td class="day old" data-tip="No Events">' + day + '</td>';
			if (count == 7) {
				weeks += '</tr>';
				count = 0;
				noOfWeek++;
			}			
		}
		//current days rows
		for(var i=1; i <= lastDate.getDate(); i++) {
			if (count == 0) {
				weeks += '<tr>';
			}
			count++;
			var day = firstDate.getDate()+i-1;
			var date = firstDate.getFullYear() + '-' + (firstDate.getMonth()+1) + '-' + day;
			if(dateStart == '') {
				dateStart = firstDate.getFullYear() + '-' + (firstDate.getMonth()+1) + '-' + day;
			}			
			var active = now.getFullYear() == firstDate.getFullYear()
				&& now.getMonth() == firstDate.getMonth() 
				&& now.getDate() == day ? ' active' : '';
			weeks += '<td id="' + date + '" class="day' + active + '" data-tip="No Events">' + day + '</td>';
			if (count == 7) {
				weeks += '</tr>';
				count = 0;
				noOfWeek++;
			}
		}
		//new days rows
		var newDaysLeft = 6-lastDate.getDay();
		var nextMonth = firstDate.setMonth(firstDate.getMonth() + 1, 1);
		var nextMonthDate = new Date(nextMonth);
		var dateEnd = '';
		if(noOfWeek == 5 || (noOfWeek == 6 && newDaysLeft == 0)) {
			newDaysLeft += 7;
		}
		for(var i=1;i <= newDaysLeft; i++) {
			if (count == 0) {
				weeks += '<tr>';
			}
			count++;
			var day = i;
			dateEnd = nextMonthDate.getFullYear() + '-' + (nextMonthDate.getMonth()+1) + '-' + day;
			weeks += '<td class="day new" data-tip="No Events">' + day + '</td>';
			if (count == 7) {
				weeks += '</tr>';
				count = 0;
			}			
		}
		$('#weeks').html(weeks);
		$.ajax({
			url: base + 'events/view_by_day/',
			dataType: "json",
			data: {start: dateStart, end: dateEnd},
			success: function(data) {
				if(data) {
					$.each(data, function(date, events){
						var eventsString = '';
						$.each(events, function(i, event) {
							var linebreak = (eventsString == '') ? '' : '\n';
							eventsString += linebreak + '* ' + event.Event.name;// + '\nFrom: ' + new Date(event.Event.event_from) + '\nTo: ' +  new Date(event.Event.event_to);
						});
						$('#' + date).addClass('event');
						$('#' + date).attr('data-tip', eventsString);
					});
				}
			},
			error: function(data) {		
			},
		});		
		
	}
	//show prev month calendar
	function prev() {
		var currentDate = new Date($('#currentDate').val());
		var past = currentDate.setMonth(currentDate.getMonth() - 1, 1);
		var newDate = new Date(past);
		$('#currentDate').val(newDate);
		$('#month').html(months[newDate.getMonth()] + '<br/>' + newDate.getFullYear());
		makeCalendar();
	}
	//show next month calendar
	function next() {
		var currentDate = new Date($('#currentDate').val());
		var future = currentDate.setMonth(currentDate.getMonth() + 1, 1);	
		var newDate = new Date(future);
		$('#currentDate').val(newDate);
		$('#month').html(months[newDate.getMonth()] + '<br/>' + newDate.getFullYear());
		makeCalendar();
	}
	
	$(document).ready(function () {
		$(document).on('click', '.day', function(){
			editEvents($(this), $(this).html());
		});		
	});
		
</script>