<div class="login">

    <div class="loginTitle">Hello there!</div>

    <div class="loginMessage">Who's knocking?</div>

    <form id="loginForm">
        <input class="form-input" type="email" name="email" placeholder="Email Address" spellcheck="false">
        <input class="form-input" type="password" name="password" placeholder="Password" spellcheck="false">
        <br>
        <input class="form-button button clickable" type="button" value="Log in" onclick="submitForm()">
    </form>

    <div class="loginMessage" style="font-size:12px; color:grey">We use cookies to log you in - by pressing the button above we understand that you agree. <br><br> We do <b><u>not</u></b> use cookies to track your activity in any way.</b></div>

</div>

<script src="./libraries/crypto/encryption.js"></script>

<script>

    function submitForm() {

        var form = $('#loginForm');

        var email = form.find('input[name="email"]').val();
        var password = form.find('input[name="password"]').val();

        logInUser(email, password);

    }

    function logInUser(email, password) {

        $.post("./backend/log_in.php", {email: email, password: password}).done(function(response) {

            var json = jQuery.parseJSON(response);

            if (json.status == "success") {

                window.location.replace("./dashboard.php");

            } else {
                json.error.forEach(function(it){
                    alert(it);
                });
            }

        });

    }

</script>