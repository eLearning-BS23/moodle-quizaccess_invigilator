define(['jquery', 'core/ajax', 'core/notification'],
    function($, Ajax, Notification) {
        return {
            setup: function(props) {
                console.log('attemptpage/setup called');
                return true;
            },
            init: function(props) {
                console.log('attemptpage/init called');
                return true;
            }
        };
    });
