<!DOCTYPE html>

<style>
    #user-info {
        height: 100%;
        width: 50%;
        margin: auto;
    }

    .form-container {
        position: fixed;
        height: 80%;
        top: 50%;
        left: 50%;
        -webkit-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
    }

    #user-info input,
    #user-info select {
        background-color: rgb(0, 0, 0, 0.05);
        color: #111;
        font-size: 0.7em;
    }

    #user-info input::placeholder{
        color:#555;
    }

    form button {
        width: 100%;
        color: white;
        opacity: 0.9;
        margin-top: 1em;
    }

    .title{
        font-size: 1.2em;
        font-weight: bold;
        color:#444;
        font-family: Verdana, Geneva, Tahoma, sans-serif;
        margin-bottom: 0.5em;
    }
</style>

<script>
    $(async function() {
        await $.get('functions.php?f=getUserInfo',
            function(response) {
                if (response.type === "success") {
                    $("#user-info").html("<div class='title'>Your Informations</div>"+register + '<button style="background-color:#13a2ce;" type="submit">Save</button></form> <button id="delete-account-button" style="background-color:firebrick;">Delete Account</button>');
                    $("#user-info input[name=password], #user-info input[name=confirmpassword]").remove();
                    $("#delete-account-button").on("click", function(e) {
                        e.preventDefault();
                        displayPopup("<p>Please enter your password below to confirm your account deletion</p><form id='confirm-deletion' style='display:flex; width:100%; flex-direction:row; justify-content:center; align-items:center;'><input type='password'  name='confirm-password' placeholder='Enter your password' required><input type='submit' style='flex:1; margin-left:1em;' value='Confirm'></form>", {
                            header: "Delete Account"
                        });
                        $("#confirm-deletion").on("submit", function(e) {
                            e.preventDefault();
                            $.ajax({
                                type: "POST",
                                url: "functions.php?f=deleteAccount",
                                data: $(this).serialize(),
                                dataType: "json",
                                success: function(r) {
                                    if ("error" in r) {
                                        displayPopup(r.error, {
                                            header: "Error"
                                        });
                                    } else {
                                        location.reload();
                                    }
                                },
                                fail: function() {
                                    displayPopup("An Unknown Error Occured. Please Try Again Later.", {
                                        header: "Error"
                                    });
                                }
                            });
                        });
                    });
                    loadCountries().then(async function() {
                        for (var k in response.data) {
                            let v = response.data[k];
                            $(`#${k}`).val(v);
                            if (k === "country_id") {
                                await countryChanged(v);
                            } else if (k === "city_name") {
                                await cityChanged(v);
                            }
                        }
                        $("#user-info").on("submit", function(e) {
                            e.preventDefault();
                            showLoadingIndicator();
                            let formData = {};
                            $(this).find('[name]:not([type=submit])').each(function() {
                                if (response.data[this.name] != this.value) {
                                    formData[this.name] = this.value;
                                }
                            })
                            $.ajax({
                                type: "POST",
                                url: "functions.php?f=updateAccount",
                                data: {
                                    data: formData
                                },
                                dataType: "json",
                                success: function(rsp) {
                                    if ("error" in rsp) {
                                        if (rsp.error === 1) {
                                            displayPopup("No Value Were Modified", {
                                                header: "Error"
                                            });
                                        } else if (rsp.error === 2) {
                                            displayPopup("An Unknown Error Occurred. Please Try Again Later.", {
                                                header: "Error"
                                            });
                                        } else {
                                            rsp.error.forEach(e => {
                                                let d = document.getElementById(e);
                                                d.setCustomValidity("Already Used");
                                                d.reportValidity();
                                            });
                                        }
                                    }
                                    if ("data" in rsp) {
                                        displayPopup("Informations Saved Successfully", {});
                                        $.each(rsp.data, function(k) {
                                            response.data[k] = rsp.data[k];
                                        });
                                    }
                                    removeLoadingIndicator();
                                },
                                fail: function() {
                                    removeLoadingIndicator();
                                }
                            });
                        });
                    });
                } else {
                    $("#user-info").html(`<div style='position: fixed;top: 50%;left: 50%;-webkit-transform: translate(-50%, -50%);transform: translate(-50%, -50%); text-align:center;'> <h1>${response.data}</h1> <a href='#'>Try again</a></div>`)
                }
            });
        removeLoadingIndicator();
    });
</script>

<body>
    <div class='default-margin' style="height:100%; overflow-y: scroll;">
        <div class="form-container">
            <form id="user-info"></form>
        </div>
    </div>

</body>

</html>