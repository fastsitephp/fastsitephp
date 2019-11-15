<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="X-CSRF-Token" content="<?php echo $csrf_token ?>">
    <title>CSRF Testing and Demo</title>
    <style>
        input { width:800px; margin:10px; padding:4px 8px; }
        button { margin:10px; padding:4px 8px; }
    </style>
  </head>
  <body>
    <h1>CSRF Testing and Demo</h1>
    <form method="POST">
        <b>Field:</b> <input name="test" value="test">
        <br>
        <b>CSRF:</b> <input name="X-CSRF-Token" value="<?php echo $csrf_token ?>">
        <br>
        <button type="submit">Submit</button>
        <br>
    </form>
    <button type="button" id="js-submit">JS Submit</button>
    <br>
    <br>
    <div id="expire-time"></div>    
    <script>
        (function() {
            var token = document.querySelector("meta[name='X-CSRF-Token']").getAttribute('content');
            var hasExpireTime = (token.indexOf(".") !== -1);
            if (hasExpireTime) {
                var expireEl = document.getElementById("expire-time");

                var expireTime = token.split(".")[0];
                expireTime = parseInt(expireTime, 10);
                expireTime = new Date(expireTime);
                
                expireEl.textContent = "Expires at: " + expireTime.toLocaleTimeString();
                var interval = window.setInterval(function() {
                    if (Date.now() > expireTime) {
                        window.clearInterval(interval);
                        expireEl.style.color = "red";
                    }
                }, 1000);
            }
            
            document.getElementById('js-submit').onclick = function() {
                var formData = new FormData();
                formData.append('test', 'js-test');

                fetch(document.URL, {
                    method: 'POST',
                    cache: 'no-cache',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-Token': token,
                    },
                    body: formData
                })
                .then(response => response.text())
                .then(text => console.log(text));       
            }
        })();
    </script>
  </body>
</html>