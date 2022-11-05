var locationResources = [];
var register = `<input type="text" name="username" title="Letters and numbers only, no punctuation or special characters" id="username" pattern="[A-Za-z0-9]+" onchange="removeErrorMessage(this)" placeholder="Enter your Username" required>
<input type="password" name="password" onchange="removeErrorMessage(this)" passwordrules="required: upper; required: lower; required:digit; minlength: 6; allowed: [-().&@?'#,/&quot;+];" style="flex: 1;" placeholder="Enter a password" required>
<input type="password" name="confirmpassword" onchange="removeErrorMessage(this)" placeholder="Re-enter your password" style="flex: 1;" required>
<input type="tel" name="phone" pattern="(^\+[0-9]{2}|^\+[0-9]{2}\(0\)|^\(\+[0-9]{2}\)\(0\)|^00[0-9]{2}|^0)([0-9]{9}$|[0-9\-\s]{10}$)" id="phone" onchange="removeErrorMessage(this)" placeholder="Enter your Phone Number" required>
<input type="email" name="email" id="email" onchange="removeErrorMessage(this)" placeholder="Enter your E-mail Address" required>
<input type="text" name="name" id="name" onchange="removeErrorMessage(this)" title="Your First Name (letters, apostrophe or hyphen only)" pattern="^([A-Za-z]+[,.]?[ ]?|[A-Za-z]+['-]?)+$" placeholder="Enter your First Name" required>
<input type="text" name="surname" id="surname" onchange="removeErrorMessage(this)" title="Your Last Name (letters, apostrophe or hyphen only)" pattern="^([A-Za-z]+[,.]?[ ]?|[A-Za-z]+['-]?)+$" placeholder="Enter your Last Name" required>
<select name="country_id" id="country_id" onchange="countryChanged(this.value)" required></select>
<select name="city_name" id="city_name" onchange="cityChanged(this.value)" required></select>
<select name="city_postal_code" id="city_postal_code" required></select>`;

$(function () {
    loadPageContent(window.location.search);
    window.onpopstate = function (e) {
        let p = history.state;
        if (p != null) {
            loadPageContent(p);
        } else {
            history.back();
        }
    };

    $('a.nav-link').on("click", function (e) {
        e.preventDefault();
        $(".popup").remove();
        $("#sidebar").hide();
        $(".overlay").remove();
        history.pushState(null, null, $(this).attr('href'));
        loadPageContent($(this).attr('href'));
    });

    $("#sidebar-open-button").click(function () {
        let sideBar = $("#sidebar").show();
        let overlay = showOverlay({
            onClick: function () {
                sideBar.hide();
            }
        });
        $("#sidebar-close-button").on("click", function () {
            sideBar.hide();
            overlay.remove();
        });
    });

    $("#register-button").on("click", function () {
        loadCountries();
        displayPopup('<form id="register-form">' + register + '<input type="submit" name="signup" value="Sign Up"></form>', {
            header: "Register"
        });
        $("#register-form").on("submit", function (e) {
            e.preventDefault();
            let confirmPassword = $("#register input[name='confirmpassword']");
            if (confirmPassword.val() !== $("#register input[name='password']").val()) {
                confirmPassword.get(0).setCustomValidity("The passwords dont match");
                confirmPassword.get(0).reportValidity();
            } else {
                showLoadingIndicator();
                let data = $(this).find(':not(input[name=confirmpassword])').serializeArray();
                $.ajax({
                    url: 'functions.php?f=register',
                    type: 'POST',
                    data: $.param(data),
                    dataType: "json"
                }).done(function (response) {
                    if (response.type === "success") {
                        location.reload();
                    } else if (response.type === "error") {
                        for (let v in response.data) {
                            let e = document.getElementById(v.split(" ")[0].toLowerCase());
                            e.setCustomValidity(response.data[v]);
                            e.reportValidity();
                        }

                    }
                    removeLoadingIndicator();
                }).fail(function (xhr, textStatus, errorThrown) {
                    displayPopup("Connection with the database impossible", {
                        header: "Error"
                    });
                    removeLoadingIndicator();
                });
            }
        });
    });

    $("#login-button").on("click", function () {
        displayPopup(`<form id="login-form">
        <input type="hidden" name="loginType" id="loginType" value="username">
        <input type="text" name="username" id="identifier" placeholder="Enter your Username" required="">
        <input type="password" name="password" placeholder="Enter your Password" required="">
        <input type="submit" name="log-in" value="Log-In">
        </form>` + `<br>
        <div style="display:flex;"> <button class="link-button selected-grey login-type-option" onclick="changeLoginType(this)" value="username">Username</button><button class="link-button login-type-option" onclick="changeLoginType(this)" value="email">Email</button><button class="link-button login-type-option" onclick="changeLoginType(this)" value="phone">Phone</button></div>`, {
            header: "Log-In"
        });
        $("#login-form").on("submit", function (e) {
            showLoadingIndicator();
            e.preventDefault();
            $.ajax({
                url: 'functions.php?f=login',
                type: 'POST',
                dataType: "json",
                data: $(this).serializeArray()
            }).done(function (response) {
                if (response.type === "success") {
                    location.reload();
                } else if (response.type === "error") {
                    displayPopup(response.data, {
                        header: "Error"
                    });
                }
                removeLoadingIndicator();
            }).fail(function (xhr, textStatus, errorThrown) {
                displayPopup("Connection with the database impossible", {
                    header: "Error"
                });
                removeLoadingIndicator();
            });
            return false;
        })
    });

    $("#login-popup input[name='loginType']").on("click", function (e) {
        e.preventDefault();
        changeLoginType($(this));
    })

    $("#logout-button").on("click", function (e) {
        $.get("functions.php?f=logout",
            function () {
                location.reload();
            }
        );
    })

});

/**
 * Highlight a navbar item by adding the selected class to it, and removing it from all other navbar links
 * 
 * @param {*} item Navbar item to be selected
 */
function selectNavItem(item) {
    if (!item.includes("?content")) item = "?content=home";
    $('a.nav-link').removeClass("selected");
    $('a.nav-link[href="' + item + '"]').addClass("selected");
}

/**
 * Load page content dynamically with AJAX
 * 
 * @param {String} pageURL Page to be loaded
 */
async function loadPageContent(pageURL) {
    let overlay = showLoadingIndicator();
    selectNavItem(pageURL);
    return $.ajax({
        type: "GET",
        url: "functions.php?f=loadPage&" + pageURL.substring(1),
        dataType: "html",
        error: function (xhr, textStatus, error) {
            $('#pageContent').html("<div style='position: fixed;top: 50%;left: 50%;-webkit-transform: translate(-50%, -50%);transform: translate(-50%, -50%); text-align:center;'> <h1>An unknown error occured</h1> <a href='.'>Try again</a></div>");
            overlay.remove();
        },
        success: function (data) {
            window.setTimeout(function () {
                $('#pageContent').html(data);
            }, 500);
            history.replaceState(window.location.search, null, pageURL);
        },
        timeout: 10000
    });
}

function changeLoginType(type){
    $("#loginType").val(type.value);
    $(".login-type-option").removeClass("selected-grey");
    $(type).addClass("selected-grey");
    let input;
    switch(type.value){
        case "username": input = `<input type="text" id="identifier" name="username" onchange="removeErrorMessage(this)" placeholder="Enter your Username" required>`; break;
        case "email": input = `<input type="email" id="identifier" name="email" id="email" onchange="removeErrorMessage(this)" placeholder="Enter your E-mail Address" required>`; break;
        case "phone": input = `<input type="tel" id="identifier" name="phone" id="phone" onchange="removeErrorMessage(this)" placeholder="Enter your Phone Number" required>`; break;
    }
    $("input#identifier").replaceWith(input);
}

function removeErrorMessage(i) {
    i.setCustomValidity("");
}

/**
 * Function used to display a popup
 * 
 * @param {String} content HTML content to be displayed in the popup
 */
function displayPopup(content, {
    header,
    zIndex,
    onDismiss
}) {
    zIndex = 2 + $(".popup").length;
    let dialog = $(`<div class='popup' style='z-index:${zIndex +1}'>` + (header !== undefined ? `<div class='header'>${header}<a class='close-dialog popup-exit-button'>&times;</a></div>` : "") + `<div class='popup-content'>${content}</div>`).appendTo('body');
    let overlay = showOverlay({
        zIndex: zIndex || 2,
        link: dialog,
        onClick: onDismiss
    });
    dialog.find(".close-dialog").on("click", function () {
        if (onDismiss !== undefined) {
            onDismiss();
        }
        dialog.remove();
        overlay.fadeOut(500, function () {
            overlay.remove();
        });
    })
}

function showOverlay({
    zIndex,
    link,
    onClick,
    id
}) {
    let overlay = $(`<div class='overlay'` + (zIndex !== undefined ? `style='z-index: ${zIndex}'` : ``) + (id !== undefined ? `id= '${id}'` : ``) + `></div>`).appendTo("body").hide().fadeIn(500);
    overlay.on("click", function () {
        if (onClick !== undefined) {
            onClick();
        }
        overlay.off();
        if (link !== undefined) {
            link.remove();
        }
        overlay.fadeOut(500, function () {
            overlay.remove();
        });
    });
    return overlay;
}


function showLoadingIndicator() {
    return showOverlay({
        id: "loading-indicator"
    }).append("<div class='loader'></div>");
}

function removeLoadingIndicator() {
    $("#loading-indicator").remove();
}

/**
 * Function loading all countries from the database
 */
async function loadCountries() {
    if (locationResources.size == undefined) {
        let data = await soapRequest("country", "country_id, name");
        $(data).find('country').each(function () {
            locationResources[$(this).attr("country_id")] = {
                name: $(this).attr("name")
            };
        });
    }
    let html = ``;
    for (let k in locationResources) {
        html += `<option value="${k}">${locationResources[k].name}</option>\n`;
    }
    $('#country_id').html(html).val(173); // pre select thailand as a country for test
    await countryChanged(173);
}

async function countryChanged(c) {
    if (locationResources[c].provinces === undefined) {
        let data = await soapRequest("province", "name", c);
        locationResources[c].provinces = [];
        $(data).find('province').each(function () {
            locationResources[c].provinces.push($(this).attr("name"));
        });
    }
    let html = ``;
    locationResources[c].provinces.forEach((k) => {
        html += `<option value="${k}">${k}</option>\n`;
    });
    $('#city_name').html(html);
    if (html === '') {
        displayPopup("Country not yet supported", {});
    }
    await cityChanged($('#city_name').val());
}

async function cityChanged(p) {
    let c = $('#country_id').val();
    if (locationResources[c].provinces[p] === undefined) {
        let data = await soapRequest("city", "name, postal_code", p);
        locationResources[c].provinces[p] = [];
        $(data).find('city').each(function () {
            locationResources[c].provinces[p].push({
                postal_code: $(this).attr("postal_code"),
                name: $(this).attr("name")
            });
        });
    }
    let html = ``;
    locationResources[c].provinces[p].forEach((k) => {
        html += `<option value="${k.postal_code}">${k.name} - ${k.postal_code}</option>\n`;
    });
    await $('#city_postal_code').html(html).trigger('change');
}

function soapRequest(query_type, columns, args) {
    return $.ajax({
        url: 'soap.php',
        type: 'POST',
        dataType: "xml",
        data: {
            "query_type": query_type,
            "columns": columns,
            "args": (Array.isArray(args) ? args : [args])
        }
    }).fail(function () {
        displayPopupError("An error occured while retrieving data", {});
    });
}


function popupInterrupt() {
    return ($(".popup")[0] || $("#sidebar").is(":visible"));
}

$.fn.bindFirst = function (name, fn) {
    this.on(name, fn);
    this.each(function () {
        var handlers = $._data(this, 'events')[name.split('.')[0]];
        var handler = handlers.pop();
        handlers.splice(0, 0, handler);
    });
};