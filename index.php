<!DOCTYPE html>
<html>

<head>
    <title>Gigya Web Hooks</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/paper/bootstrap.min.css" rel="stylesheet" integrity="sha384-awusxf8AUojygHf2+joICySzB780jVvQaVCAt1clU3QsyAitLGul28Qxb2r1e5g+" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/epoch.min.css">
    <style type="text/css">
    body {
        /*background-color: #FCFCFC;*/
    }
    
    .spinner {
        width: 28px;
        height: 28px;
        /* margin: 100px auto;*/
        background-color: #333333;
        display: inline-block;
        border-radius: 100%;
        -webkit-animation: sk-scaleout 1.0s infinite ease-in-out;
        animation: sk-scaleout 1.0s infinite ease-in-out;
    }
    
    @-webkit-keyframes sk-scaleout {
        0% {
            -webkit-transform: scale(0)
        }
        100% {
            -webkit-transform: scale(1.0);
            opacity: 0;
        }
    }
    
    @keyframes sk-scaleout {
        0% {
            -webkit-transform: scale(0);
            transform: scale(0);
        }
        100% {
            -webkit-transform: scale(1.0);
            transform: scale(1.0);
            opacity: 0;
        }
    }
    </style>
    <script src='https://cdns.gigya.com/JS/gigya.js?apiKey=APIKEY' type='text/javascript'></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src="js/d3.min.js"></script>
    <script src="js/epoch.min.js"></script>
    <script type="text/javascript">
    var webHooksData = [];
    var realtimeChart;
    var ignore = false;

    function StartTimer() {

        if (ignore) return;
        var curTime = Math.round(new Date().getTime() / 1000.0);
        if (realtimeChart) {
            realtimeChart.push([

                {
                    time: curTime,
                    y: 0
                }, {
                    time: curTime,
                    y: 0
                }, {
                    time: curTime,
                    y: 0
                }, {
                    time: curTime,
                    y: 0
                }



            ]);
        }

        setTimeout(StartTimer, 1000);

    }

    function GigyaLogin(data) {
        if (data) {
            if (data.status == "FAIL") return;
            $('#loginNav').hide();
            $('#profileNav').show();
            $('#profileName').html(data.profile.firstName + ' ' + data.profile.lastName);
            $('#subtitle').html("<div class='spinner'></div>");
        }
    }

    function GigyaLogout(data) {
        $('#loginNav').show();
        $('#profileNav').hide();
    }

    $(document).ready(function() {



        gigya.accounts.addEventHandlers({
            onLogin: GigyaLogin,
            onLogout: GigyaLogout
        });

        gigya.accounts.getAccountInfo({
            callback: GigyaLogin
        });

        $('.login').click(function(e) {
            gigya.accounts.showScreenSet({
                screenSet: 'Defaut-RegistrationLogin'
            });
            e.preventDefault();
        });

        $('.logout').click(function(e) {
            gigya.accounts.logout();
            e.preventDefault();
        });

        $('.profileUpdate').click(function(e) {
            gigya.accounts.showScreenSet({
                screenSet: 'v2-ProfileUpdate'
            });
            e.preventDefault();
        });

        var barChartData = [{
            label: "accountUpdated",
            values: []
        }, {
            label: "accountCreated",
            values: []
        }, {
            label: "accountRegistered",
            values: []
        }, {
            label: "accountDeleted",
            values: []
        }];
        realtimeChart = $('#area').epoch({
            type: 'time.bar',
            data: barChartData,
            axes: ['left', 'bottom'],
            fps: 50
        });
        StartTimer();

    });
    </script>
    <script src="https://js.pusher.com/3.2/pusher.min.js"></script>
    <script>
    function ProcessEvent(event, data) {
        ignore = true;
        var now = new Date();
        var pingAccountUpdated = 0;
        var pingAccountCreated = 0;
        var pingAccountRegistered = 0;
        var pingAccountDeleted = 0;


        if (event.length > 1) {
            for (var i = 0; i < event.length; i++) {
                var multievent = event[i];

                var id = multievent.id;
                var eventType = multievent.type;
                var uid = multievent.data.uid;
                var time = multievent.timestamp;
                var date = new Date(0);
                date.setUTCSeconds(time);
                time = date.toUTCString();

                var cTime = Math.floor(now.getTime() / 1000.0);
                var difference = ((now - date) / 1000.0).toFixed(2);
                var healthClass = "text-muted";

                if (((now - date) / 1000.0) > 20) healthClass = "text-danger";
                if (((now - date) / 1000.0) <= 20) healthClass = "text-warning";
                if (((now - date) / 1000.0) <= 10) healthClass = "text-primary";
                if (((now - date) / 1000.0) <= 5) healthClass = "text-success";

                $('.activeRecord').remove();
                var html = '<tr id="row-' + id + '" class="notification-row" data-id="' + id + '"><td><span class="activeRecord glyphicon glyphicon-chevron-right" aria-hidden="true"></span></td><td>' + time + '</td><td>' + eventType + '</td><td>' + uid + '</td><td><span class="' + healthClass + '">' + difference + ' seconds</span></td></tr>';
                /*<tr style="display:none" id="verbose-' + id + '"><td colspan="5"><textarea class="form-control">' + JSON.stringify(data) + '</textarea></td></tr>*/
                $('#events').prepend($(html).hide().fadeIn());


                if (eventType == "accountUpdated") pingAccountUpdated++;
                if (eventType == "accountCreated") pingAccountCreated++;
                if (eventType == "accountRegistered") pingAccountRegistered++;
                if (eventType == "accountDeleted") pingAccountDeleted++;



            }

            if (realtimeChart) {
                cTime = Math.floor(new Date().getTime() / 1000.0);
                realtimeChart.push([

                    {
                        time: cTime,
                        y: pingAccountUpdated
                    }, {
                        time: cTime,
                        y: pingAccountCreated
                    }, {
                        time: cTime,
                        y: pingAccountRegistered
                    }, {
                        time: cTime,
                        y: pingAccountDeleted
                    }
                ]);
                ignore = false;
                return;
            }
        }
        var id = event.id;
        var eventType = event.type;
        var uid = event.data.uid;
        var time = event.timestamp;
        var date = new Date(0);
        date.setUTCSeconds(time);
        time = date.toUTCString();

        var cTime = Math.floor(now.getTime() / 1000.0);
        var difference = ((now - date) / 1000.0).toFixed(2);
        var healthClass = "text-muted";

        if (((now - date) / 1000.0) > 20) healthClass = "text-danger";
        if (((now - date) / 1000.0) <= 20) healthClass = "text-warning";
        if (((now - date) / 1000.0) <= 10) healthClass = "text-primary";
        if (((now - date) / 1000.0) <= 5) healthClass = "text-success";

        $('.activeRecord').remove();
        var html = '<tr id="row-' + id + '" class="notification-row" data-id="' + id + '"><td><span class="activeRecord glyphicon glyphicon-chevron-right" aria-hidden="true"></span></td><td>' + time + '</td><td>' + eventType + '</td><td>' + uid + '</td><td><span class="' + healthClass + '">' + difference + ' seconds</span></td></tr>';
        /*<tr style="display:none" id="verbose-' + id + '"><td colspan="5"><textarea class="form-control">' + JSON.stringify(data) + '</textarea></td></tr>*/
        $('#events').prepend($(html).hide().fadeIn());
        /*$('#row-' + id).click(function(e) {
            var id = $(this).data('id');
            console.log(id);
            $('#verbose-' + id).toggle();
            e.preventDefault();
        });*/
        if (eventType == "accountUpdated") pingAccountUpdated = 1;
        if (eventType == "accountCreated") pingAccountCreated = 1;
        if (eventType == "accountRegistered") pingAccountRegistered = 1;
        if (eventType == "accountDeleted") pingAccountDeleted = 1;


        if (realtimeChart) {
            realtimeChart.push([

                {
                    time: cTime,
                    y: pingAccountUpdated
                }, {
                    time: cTime,
                    y: pingAccountCreated
                }, {
                    time: cTime,
                    y: pingAccountRegistered
                }, {
                    time: cTime,
                    y: pingAccountDeleted
                }



            ]);
        }

        ignore = false;
    }

    Pusher.logToConsole = false;
    var pusher = new Pusher('PUSHERKEY', {
        encrypted: true
    });

    var channel = pusher.subscribe('webhooks');
    channel.bind('accountUpdated', function(data) {
        data = $.parseJSON(window.atob(data));
        if (data.payload) {
            data.payload = $.parseJSON(data.payload);
            var event = data.payload.events[0];
            ProcessEvent(event, data);
        }
    });

    channel.bind('accountCreated', function(data) {
        data = $.parseJSON(window.atob(data));
        if (data.payload) {
            data.payload = $.parseJSON(data.payload);
            var event = data.payload.events[0];
            ProcessEvent(event, data);
        }
    });

    channel.bind('accountRegistered', function(data) {
        data = $.parseJSON(window.atob(data));
        if (data.payload) {
            data.payload = $.parseJSON(data.payload);
            var event = data.payload.events[0];
            ProcessEvent(event, data);
        }
    });

    channel.bind('accountDeleted', function(data) {
        data = $.parseJSON(window.atob(data));
        if (data.payload) {
            data.payload = $.parseJSON(data.payload);
            var event = data.payload.events[0];
            ProcessEvent(event, data);
        }
    });

    channel.bind('multiple', function(data) {
        data = $.parseJSON(window.atob(data));
        if (data.payload) {
            data.payload = $.parseJSON(data.payload);
            if (data.payload.events.length > 1) {
                ProcessEvent(data.payload.events, data);
            }
        }
    });
    </script>
</head>

<body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="#">Web Hooks Monitor</a>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li id="loginNav"><a class="login" href="#">Login</a></li>
                <li class="dropdown" id="profileNav" style="display:none">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span id="profileName">Profile</span> <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="#" class="profileUpdate">Update Profile</a></li>
                        <li class="divider"></li>
                        <li><a href="#" class="logout">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <div class="page-header">
            <div class="row">
                <div class="col-lg-8 col-md-7 col-sm-6">
                    <h1>Real time notifications</h1>
                    <p class="lead" id="subtitle">Test by first <a href="#" class="login">logging in or registering</a></p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div id="area" class="epoch category10" style="height: 300px;"></div>
            </div>
        </div>
        <div class="row" style="padding-top: 50px;">
            <table class="table table-striped table-hover">
                <thead>
                    <th></th>
                    <th>Time</th>
                    <th>Event</th>
                    <th>UID</th>
                    <th>Speed</th>
                </thead>
                <tbody id="events">
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
