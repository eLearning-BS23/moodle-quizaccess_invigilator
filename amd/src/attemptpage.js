define(['jquery', 'core/ajax', 'core/notification'],
    function($, Ajax, Notification) {
        return {
            setup: function(props) {
                console.log('attemptpage/setup called');
                var quizurl = props.quizurl;
                function CloseOnParentClose() {
                    if (typeof window.opener != 'undefined' && window.opener !== null) {
                        if (window.opener.closed) {
                            window.close();
                        }
                    } else {
                        window.close();
                    }

                    var parentWindowURL = window.opener.location.href;
                    console.log("parenturl", parentWindowURL);
                    console.log("quizurl", quizurl);

                    if (parentWindowURL !== quizurl) {
                        window.close();
                    }

                    var share_state = window.opener.invigilator_share_state;
                    var window_surface = window.opener.invigilator_window_surface;
                    // Console.log('parent ss', share_state);
                    // console.log('parent ws', window_surface);

                    if (share_state.value !== "true") {
                        // Window.close();
                        // console.log('close window now');
                        window.close();
                    }

                    if (window_surface.value !== 'monitor') {
                        // Console.log('close window now');
                        window.close();
                    }
                }
                $(window).ready(function() {
                    setInterval(CloseOnParentClose, 1000);
                });
                return true;
            },
            init: function(props) {
                console.log('attemptpage/init called');
                return true;
            }
        };
    });
