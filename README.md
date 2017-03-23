# Gigya Webhooks Demo

A PHP Script that demos and benchmarks the Gigya Webhooks. This demo allows you to view how an action can trigger a web hook in real time and provide you with the information plotted on a graph.

## Prerequisites

* PHP
* Pusher.com credentials

## Setup

### Webhooks Setup

You will need to create a Webhook via the Gigya Console - the following credentials need to be set:

* Notification URL: https://DOMAIN.COM/webhooks/listener.php (This must be a https secure URL)
* Sign notifications using: Partner Secret Key (The script has not been set up to work with a User Secret Key)
* Events: Check all events

### Config changes

The script uses the `pusher.com` web sockets service - you will need to register for an account and create a new app, as an example it can be called `webhooks`.

Once you have the pusher credentials you will need to modify `listener.php` and `index.php`.

### listener.php

Line 6 - Add your Gigya Partner Key
``` PHP
$PARTNERSECRET = "PARTNERSECRETHERE";
```

Line 65 - Add your Pusher credentials
``` PHP
  $pusher = new Pusher(
    '', //key
    '', // secret
    '', // app_id
    $options
  );
  ```
  
### index.php

Line 47 - Add your Gigya API Key

``` HTML
<script src='https://cdns.gigya.com/JS/gigya.js?apiKey=APIKEY' type='text/javascript'></script>
```

Line 117 - Enter your Gigya Screenset ID

``` Javascript
gigya.accounts.showScreenSet({
        screenSet: 'Defaut-RegistrationLogin'
});
```

Line 285 - Enter your Pusher Key

``` Javascript
var pusher = new Pusher('PUSHERKEY', {
    encrypted: true
});
```

Once the credentials have been set you will just need to access the application via your web browser and use the script to receive notifications.

## Demo

A demo can be found at https://sandeep.gigya-cs.com/webhooks