define(['jquery', 'core/ajax', 'core/notification'],
    function($, Ajax, Notification) {
        return {
            setup: function(props){
                console.log('startattempt/setup called');
                window.invigilator_share_state = document.getElementById('invigilator_share_state');
                window.invigilator_window_surface = document.getElementById('invigilator_window_surface');
                window.invigilator_screenoff = document.getElementById('invigilator_screen_off_flag');

                const videoElem = document.getElementById("invigilator-video-screen");
                const logElem = document.getElementById("invigilator-log-screen");

                var displayMediaOptions = {
                    video: {
                        cursor: "always"
                    },
                    audio: false
                };

                $("#invigilator-share-screen-btn").click(function() {
                    event.preventDefault();
                    console.log('screen sharing clicked');
                    startCapture();
                });

                async function startCapture() {
                    logElem.innerHTML = "";
                    try {
                        // Console.log("vid found success");
                        videoElem.srcObject = await navigator.mediaDevices.getDisplayMedia(displayMediaOptions);
                        $('#id_invigilator').css("display", 'block');
                        $("label[for='id_invigilator']").css("display", 'block');
                    } catch (err) {
                        // Console.log("Error: " + err.toString());
                        let errString = err.toString();
                        if (errString == "NotAllowedError: Permission denied") {
                            alert("Please share entire screen.");
                            return false;
                        }
                    }
                }

                var updateWindowStatus = function() {
                    if (videoElem.srcObject !== null) {
                        // Console.log(videoElem);
                        const videoTrack = videoElem.srcObject.getVideoTracks()[0];
                        var currentStream = videoElem.srcObject;
                        var active = currentStream.active;
                        var settings = videoTrack.getSettings();
                        var displaySurface = settings.displaySurface;
                        document.getElementById('invigilator_window_surface').value = displaySurface;
                        document.getElementById('invigilator_share_state').value = active;
                        var screenoff = document.getElementById('invigilator_screen_off_flag').value;
                        if (screenoff == "1") {
                            videoTrack.stop();
                            // Console.log('video stopped');
                            clearInterval(windowState);
                            location.reload();
                        }
                    }
                };
                var windowState = setInterval(updateWindowStatus, 1000);
            },
            init: function(props) {
                console.log('startattempt/init called');
                $('#id_submitbutton').prop("disabled", true);
                $('#id_invigilator').css("display", 'none');
                $("label[for='id_invigilator']").css("display", 'none');


                $('#id_invigilator').click(function() {
                    if (!$(this).is(':checked')) {
                        console.log("un Checked");
                        hideButtons();
                    }
                    else{
                        console.log("Checked");
                        var screensharestatus = document.getElementById('invigilator_share_state').value;
                        var screensharemode = document.getElementById('invigilator_window_surface').value;
                        console.log(screensharemode);
                        console.log(screensharestatus);
                        if((screensharemode == 'monitor') && (screensharestatus == "true")){
                            showButtons();
                        }
                        else{
                            alert('Please click share screen and choose entire monitor.');
                        }
                    }
                });

                /**
                 * HideButtons
                 */
                function hideButtons() {
                    $('#id_submitbutton').prop("disabled", true);
                }

                /**
                 * ShowButtons
                 */
                function showButtons() {
                    $('#id_submitbutton').prop("disabled", false);
                }
                return true;
            }
        };
    });
