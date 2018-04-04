<div class="login">

    <div class="loginTitle">Nice to meet you!</div>

    <div class="loginMessage">Be one click away from your encrypted data anytime, wherever you are.</div>

    <form id="registrationForm">

        <input class="form-input" type="text" name="name" placeholder="Name" spellcheck="false">
        <input class="form-input" type="email" name="email" placeholder="Email Address" spellcheck="false">
        <input class="form-input" type="password" name="password" placeholder="Password" spellcheck="false">
        <br>
        <input class="form-button button clickable" type="button" value="Create free account" onclick="submitForm()">

    </form>

    <div class="loginMessage" style="font-size:12px; color:grey">By registering you agree to our Terms & Conditions</div>

</div>

<script>

    function submitForm() {

        var form = $('#registrationForm');

        var name = form.find('input[name="name"]').val();
        var email = form.find('input[name="email"]').val();
        var password = form.find('input[name="password"]').val();

        registerUser(name, email, password);

    }

    function registerUser(name, email, password) {

        $.post("./backend/create_user.php", {name: name, email: email, password: password}).done(function(response) {

            var json = jQuery.parseJSON(response);

            if (json.status == "success") {
                logInUser(email, password);
            } else {
                json.error.forEach(function(it){
                   alert(it);
                });
            }

        });

    }

    function logInUser(email, password) {

        $.post("./backend/log_in.php", {email: email, password: password}).done(function(response) {

            var json = jQuery.parseJSON(response);

            if (json.status == "success") {
                alert("Success");
            } else {
                json.error.forEach(function(it){
                    alert(it);
                });
            }

        });

    }

</script>