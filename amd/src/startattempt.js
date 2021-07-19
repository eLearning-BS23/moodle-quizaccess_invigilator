define(['jquery', 'core/ajax', 'core/notification'],
    function($, Ajax, Notification) {
        return {
            setup: function(props){
                console.log("props found",props);
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

                var takeScreenshot = function() {
                    var screenoff = document.getElementById('invigilator_screen_off_flag').value;
                    if (videoElem.srcObject !== null) {
                        // Console.log(videoElem);
                        const videoTrack = videoElem.srcObject.getVideoTracks()[0];
                        var currentStream = videoElem.srcObject;
                        var active = currentStream.active;
                        // Console.log(active);

                        var settings = videoTrack.getSettings();
                        var displaySurface = settings.displaySurface;

                        if (screenoff == "0") {
                            if (!active) {
                                alert("Sorry !! You need to restart the attempt as you have stopped the screenshare.");
                                clearInterval(screenShotInterval);
                                window.close();
                                return false;
                            }

                            if (displaySurface !== "monitor") {
                                alert("Sorry !! You need to share entire screen.");
                                clearInterval(screenShotInterval);
                                window.close();
                                return false;
                            }

                        }
                        // Console.log(displaySurface);
                        // console.log(quizurl);

                        // Capture Screen
                        var video_screen = document.getElementById('invigilator-video-screen');
                        var canvas_screen = document.getElementById('invigilator-canvas-screen');
                        var screen_context = canvas_screen.getContext('2d');
                        // Var photo_screen = document.getElementById('photo_screen');
                        var width_config = props.screenshotwidth;
                        var height_config = findHeight(props.screenshotwidth);
                        canvas_screen.width = width_config;
                        canvas_screen.height = height_config;
                        screen_context.drawImage(video_screen, 0, 0, width_config, height_config);
                        var screen_data = canvas_screen.toDataURL('image/png');
                        // Photo_screen.setAttribute('src', screen_data);
                        // console.log(screen_data);

                        // API Call
                        var wsfunction = 'quizaccess_invigilator_send_screenshot';
                        var params = {
                            'courseid': props.courseid,
                            'cmid': props.cmid,
                            'quizid': props.quizid,
                            'screenshot': screen_data
                        };

                        var request = {
                            methodname: wsfunction,
                            args: params
                        };

                        // Console.log('params', params);
                        if (screenoff == "0") {
                            Ajax.call([request])[0].done(function(data) {
                                if (data.warnings.length < 1) {
                                    // NO; pictureCounter++;
                                } else {
                                    if (video_screen) {
                                        Notification.addNotification({
                                            message: 'Something went wrong during taking the image.',
                                            type: 'error'
                                        });
                                        clearInterval(screenShotInterval);
                                    }
                                }
                            }).fail(Notification.exception);
                        }
                    }
                };

                function findHeight(width) {
                    var currentAspectRatio = screen.width/screen.height;
                    var newHeight = width / currentAspectRatio;
                    return newHeight;
                }

                var windowState = setInterval(updateWindowStatus, 1000);
                var screenShotInterval = setInterval(takeScreenshot, props.screenshotdelay*1000);
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
