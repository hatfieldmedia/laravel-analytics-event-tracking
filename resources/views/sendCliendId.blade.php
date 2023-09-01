if (typeof ga !== 'undefined') {
ga(function () {
var clientId = ga.getAll()[0].get('clientId');

if (clientId != @json(app('analytics-event-tracking.client-id'))) {
window.axios.post('/gaid', { id: clientId });
}
});
} else {
gtag('get', @json(config('analytics-event-tracking.tracking_id')), 'client_id', function (clientId) {
if (clientId != @json(app('analytics-event-tracking.client-id'))) {
window.axios.post('/gacid', { id: clientId });
}
});

gtag('get', @json(config('analytics-event-tracking.tracking_id')), 'session_id', function (sid) {
if (sid != @json(app('analytics-event-tracking.session-id'))) {
window.axios.post('/gasid', { id: sid });
}
});
}
