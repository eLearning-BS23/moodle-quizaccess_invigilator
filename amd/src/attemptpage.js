define(['jquery', 'core/ajax', 'core/notification'],
    function($) {
        return {
            setup: function(props) {
                var quizurl = props.quizurl;

                /**
                 * Closes attempt page upon parent window close.
                 */
                function CloseOnParentClose() {
                    if (typeof window.opener != 'undefined' && window.opener !== null) {
                        if (window.opener.closed) {
                            window.close();
                        }
                    } else {
                        window.close();
                    }

                    var parentWindowURL = window.opener.location.href;

                    if (parentWindowURL !== quizurl) {
                        window.close();
                    }

                    var shareState = window.opener.invigilatorShareState;
                    var windowSurface = window.opener.invigilatorWindowSurface;

                    if (shareState.value !== "true") {
                        window.close();
                    }

                    if (windowSurface.value !== 'monitor') {
                        window.close();
                    }
                }
                $(window).ready(function() {
                    setInterval(CloseOnParentClose, 1000);
                });
                return true;
            },
            init: function() {
                return true;
            }
        };
    });
