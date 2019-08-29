'use strict';

console.group('SSE');
var sseJSON = true;
var container = document.getElementById('update');
var stop = false;
var run = '';
var index = 0;
var index_max = 1000;
var pid, old_pid;
var directory = 'php-sse';
if (sseJSON === true) {
	var sse = new EventSource(directory + "/run.sse.json.php");
}
else {
	var sse = new EventSource(directory + "/run.sse.php");
}
console.info(sse);
if (container) {
	sse.onmessage=function(event) {
		console.info('Update.', event);
		if (stop === false) {
			if (sseJSON === true) {
				// Parse received data
				run = JSON.parse(event.data);

				// Show initial header
				if (index === 0) {
					container.innerHTML = 'Running: ' + run.command + '<br>';
				}

				// Avoid command execution loop...
				old_pid = run.status.pid;
				if (typeof pid !== 'undefined') {
					if (pid !== old_pid) {
						stop = true;
						run.command = '';
					}
				}

				// Display command execution
				console.info('Parsed:', run);
				console.info('Status:', run.status);
				pid = run.status.pid;
				container.innerHTML += run.output;
				// container.innerHTML += 'Index: ' + index + '<br>';
			}
			else {
				// Show initial header
				if (index === 0) {
					container.innerHTML = 'Running...' + '<br>';
				}

				// Display command execution
				container.innerHTML += event.data;
			}

			index++; // Increment counter for both modes
			container.scrollTop = container.scrollHeight; // Autoscroll till new content added
		}
		if (stop === true) {
			// container.innerHTML += 'Stopped.' + '<br>';
			sse.close();
		}
	};
}
console.groupEnd();