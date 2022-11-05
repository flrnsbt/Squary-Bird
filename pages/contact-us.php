<!DOCTYPE html>

<style>
    body {
        font-family: Verdana, Geneva, Tahoma, sans-serif !important;
        font-size: 1em;
        overflow-y:scroll;
    }

    #contact-us-form input,
    #contact-us-form textarea {
        font-family: Verdana, Geneva, Tahoma, sans-serif !important;
        background-color: #13A2CE1F;
    }

    .section {
        width: 30%;
        margin: 2em;
    }

    #contact-us-form input[type="submit"] {
        background-color: #13a2ce;
    }

    .tip {
        padding: 1em;
        background-color: #13a2ce;
        color: white;
        border-radius: 1em;
        display: flex;
        align-items: center;
    }
</style>

<script>
    $(function() {
        $("#contact-us-form").on("submit", function(e) {
            e.preventDefault();
            $.ajax({
                url: 'functions.php?f=sendEmail',
                type: 'POST',
                data: $(this).serializeArray(),
                dataType: "json"
            }).done(function(response) {
                if (response.type === "success") {
                    displayPopup("E-mail sent successfully", {});
                } else {
                    displayPopup(response.data, {});
                }
            });
        });
        removeLoadingIndicator();
    });
</script>

<body>
    <div class="default-margin">
        <div style="display: flex; height:100%; justify-content:center;  align-items: center;">
            <div class='section'>
                <h2>Contact Us</h2>
                <form id="contact-us-form" method="post" action="">
                    <div style="display: flex;">
                        <input name="email" type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" placeholder="Enter your email address" required>
                        <div style="width:10%"></div>
                        <input name="subject" maxlength="100" type="text" placeholder="Enter subject...">
                    </div>
                    <textarea name="message" rows="15" maxlength="1000" placeholder="Your Message here..." required></textarea>
                    <input type="submit" name="submit" value="Submit">
                </form>
            </div>
            <div class="section">
                <div style="display: flex;  justify-content: space-evenly; flex-direction:column; align-items:center; overflow:hidden">
                    <img src="resources/imgs/profile.jpg" style="object-fit:contain; width:75%; height:50%;">
                        <p>Florian Sabate<br>1810220005</p>
                        <p>&#9993; 1810220005@students.stamford.edu<br>&#9993; contact@floriansabate.com</p>
                </div>
            </div>
        </div>
    </div>


</body>