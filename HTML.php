<?php

/**
 * Class used to build up a basic Web Page with HTML tags
 *
 * Functions available:
 * header(), footer(), startBody(), endBody()
 *
 * @author Florian Sabate
 * @copyright Free To Use
 * @version 1.0
 */

class HTML
{
    public $lang, $charset, $title, $description, $viewport, $author;

    /**
     * HTML constructor used to instantiate variables to configure our HTML page
     * Variables are optionals, if not declared default value is used
     *
     * @param string $lang HTML lang attribute (default to "en")
     * @param string $charset HTML meta charset attribute (default to "utf-8")
     * @param string $title HTML 'title' tag (default to "Squary Bird")
     * @param string $description HTML description meta (default to "Flappy Bird look-alike game")
     * @param string $viewport HTML meta viewport (default to "width=device-width, initial-scale=1")
     * @param string $author HTML author meta (default to "Florian Sabate")
     */
    public function __construct(string $lang = "en", string $charset = "utf-8", string $title = "Squary Bird", string $description = "Flappy Bird look-alike game", string $viewport = "width=device-width, initial-scale=1", string $author = "Florian Sabate")
    {
        $this->lang = $lang;
        $this->charset = $charset;
        $this->title = $title;
        $this->description = $description;
        $this->viewport = $viewport;
        $this->author = $author;
    }

    /**
     * Render the header of the page
     */
    public function header()
    {
        echo "<!doctype html>\n";
        echo "<html lang= \"" . $this->lang . "\">\n";
        echo "<head>\n";
        echo "<meta charset=\"" . $this->charset . "\">\n";
        echo "<meta name=\"viewport\" content=\"" . $this->viewport . "\">\n";
        echo "<title>" . $this->title . "</title>\n";
        echo "<meta name=\"description\" content=\"" . $this->description . "\">\n";
        echo "<meta name=\"author\" content=\"" . $this->author . "\">\n";
        echo "</head>\n";
    }

    public function navigationBar(bool $signedIn, array $items)
    {
        echo "<div id='top-navbar'>";
        echo '<button id="sidebar-open-button">&#9776;</button>';
        foreach ($items as $key => $value) {
            echo "<a class='nav-link' href= \"?content=" . $key . "\">" . $value . "</a>";
        }
        echo "<div style='float:right;'>" . ($signedIn ? "<a href='?content=my-account' class='nav-link'>My Account</a><button id='logout-button'>Sign Out</button>" : "<button id='login-button'>Login</button><button id='register-button'>Register</button>");
        echo "</div></div>";
    }

    public function sideBar()
    {
        echo '<div id="sidebar">
        <div id="sidebar-header">
            <h2>Game</h2>
            <button id="sidebar-close-button"> &times;</button>
        </div>
        <a href="?content=game" class="nav-link">New Game</a>
        <a href="?content=leader-board" class="nav-link">Leader Board</a>
        <a href="?content=rules" class="nav-link">Rules</a>
    </div>';
    }

    /**
     * 
     * @param mixed $content Content of the dialog
     * @param string $title Title of the dialog
     * @param string|null $id Id of the dialog
     * @param bool $automaticallyVisible Whether the dialog is automatically visible or needs to be displayed using CSS3 transition or JavaScript
     * @param bool $dismissible Whether the dialog is dimissible or not
     */

    public function dialog($content, string $title, string $id, bool $automaticallyVisible = false, bool $dismissible = true)
    {
        echo "<div class='popup' id=\"" . $id . "-popup\"><div class='header'><div class='dialog-title'>" . $title . "</div>" . ($dismissible ? "<a class='close-dialog'>&times;</a>" : "") . "</div><div class='popup-content'>";
        if (is_string($content)) {
            echo $content;
        } else if (is_array($content)) {
            foreach ($content as $item) {
                if (is_string($content)) {
                    echo $item;
                } else if ($item instanceof Form) {
                    $item->render();
                }
            }
        } else if ($content instanceof Form) {
            $content->render();
        }
        echo "</div></div></div>";
    }

    public function noScript()
    {
        echo "<noscript> ";
        $this->dialog("This website requires JavaScript to works properly. Please enable it to continue or change your web browser.", "&#9888; Error", "error", true, false);
        echo " </noscript>";
    }


    /**
     * Render the footer of the page (close the HTML tag)
     */

    public function footer()
    {
        echo "</html>\n";
    }


    /**
     * Open the body tag
     */
    public function startBody()
    {
        echo "<body>\n";
    }


    /**
     * Close the body tag
     */
    public function endBody()
    {
        echo "</body>\n";
    }
}



/**
 * Class Input
 *
 * This class use a constructor to instantiated an "input" HTML element
 *
 * Basically made of most used Input attributes (name, type, value and required)
 * Title variable used to display a text before the input
 *
 * @author Florian Sabate
 * @version 1.0
 * @copyright Free To Use
 */
class Input
{
    public string $name, $type;
    public ?string $label, $value, $className, $others, $title, $id;
    public bool $isRequired;

    /**
     * Create a new input element
     * @param string $name name attribute of the input HTML element
     * @param string $type type attribute of the input HTML element
     * @param string|null $label text displayed before the input element (default to null)
     * @param string|null $title title attribute of the input HTML element
     * @param string|null $className class attribute to style the element (default to null)
     * @param string|null $value value attribute of the input HTML element (default to null)
     * @param bool $isRequired whether the input element is required or not (default to false)
     * @param string|null $others additional attributes not available in the Input class
     */
    public function __construct(string $name, string $type, ?string $className = null, ?string $label = null, ?string $value = null, bool $isRequired = false, ?string $others = null, ?string $title = null, ?string $id = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->className = $className;
        $this->label = $label;
        $this->value = $value;
        $this->isRequired = $isRequired;
        $this->others = $others;
        $this->title = $title;
        $this->id = $id;
    }

    /**
     * @return Input Default Username Input instantiated as follows:
     *
     *  <Input name= "username", type="text" required>
     */
    public static function defaultUsernameInput(?string $className = null, ?string $label = null, ?string $name = null, ?string $id = null, bool $showTitle = true): Input
    {
        return new Input($name ?? "username", "text", className: $className, label: $label, isRequired: true, title: $showTitle ? "letters and numbers only, no  punctuation or special characters" : "", others: 'pattern="[A-Za-z0-9]+" placeholder="Enter your Username"', id: $id ?? "username-input");
    }

    /**
     * @return Input Default Email Input instantiated as follows:
     *
     *  <Input name= "email", type="email" required>
     */
    public static function defaultEmailInput(?string $className = null, ?string $label = null, ?string $name = null, ?string $id = null, bool $showTitle = true): Input
    {
        return new Input($name ?? "email", "email", className: $className, label: $label, title: $showTitle ? "Email in the following format: local-part@domain,\n example: example@example.com" : "", isRequired: true, others: 'pattern ="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" placeholder="Enter your E-mail Address"', id: $id ?? "email-input");
    }

    /**
     * @return Input Default Phone Input instantiated as follows:
     *
     *  <Input name= "phone", type="tel" required>
     */
    public static function defaultPhoneInput(?string $className = null, ?string $label = null, ?string $name = null, ?string $id = null, bool $showTitle = true): Input
    {
        return new Input($name ?? "phone", "tel", className: $className, label: $label, isRequired: true, title: $showTitle ? "Example: +66835674432" : "", others: "pattern='\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*\d\W*(\d{1,2})$' placeholder='Enter your Phone Number'", id: $id ?? "phone-input");
    }

    /**
     * @return Input Default Password Input instantiated as follows:
     *
     *  Input("password", InputType::Password, isRequired: true)
     */
    public static function defaultLoginPasswordInput(?string $className = null, ?string $label = null, ?string $name = null, ?string $others = null): Input
    {
        return new Input($name ?? "password", "password", className: $className, label: $label, isRequired: true, others: $others ?? "placeholder='Enter your Password'");
    }

    /**
     * @return Input Default Password Input instantiated as follows:
     *
     *  Input("password", InputType::Password, isRequired: true)
     */
    public static function defaultRegisterPasswordInput(?string $className = null, ?string $label = null, ?string $name = null): Input
    {
        return new Input($name ?? "password", "password", className: $className, label: $label, isRequired: true, others: 'passwordrules="required: upper; required: lower; required:digit; minlength: 6; allowed: [-().&@?\'#,/&quot;+];" style= "flex: 1;" placeholder="Enter a password"');
    }


    /**
     * @return Input Default Submit Input instantiated as follows:
     *
     *  Input($name, InputType::Submit, value:  $name)
     */
    public static function defaultSubmitInput(string $name, ?string $label = null, ?string $className = null): Input
    {
        return new Input(str_replace(' ', '', strtolower($name)), "submit", className: $className, value: $name, label: $label);
    }


    /**
     * Render the current Input HTML element using previously instantiated variables
     */
    public function render()
    {
        if ($this->type == "select") {
            $input = "<select name = '$this->name' id='$this->id' " . ($this->others ?? " ") . " required></select>";
        } else {
            $input = "<input type=\"" . $this->type . "\" name=\"" . $this->name . "\" " . ($this->title != null ? "title=\"" . $this->title . "\" " : "") . ($this->id != null ?  "id=\"" . $this->id . "\" " : "") .
                ($this->className ? "class=\"" . $this->className . "\" " : "") . ($this->value != null ? "value=\"" . $this->value . "\"" : "")
                . $this->others . ($this->isRequired ? " required >" : ">");
        }

        echo ($this->label ? "<label for='$this->name'>$this->label" . ($this->isRequired ? '* ' : ' ') . "</label> " : "")
            . $input . "\n";
    }
}


/**
 * Class Form
 *
 * Responsible for rendering a custom HTML Form using a set of variables and functions
 *
 * Form Action is defined in $formAction, and the rest of the form input elements
 * are configured using Input class and set in $formInputs variable
 *
 * @author Florian Sabate
 * @version 1.0
 * @copyright Free To Use
 */
class Form
{
    public ?string $formTitle, $id, $formAction;
    public $formInputs;

    /**
     * Form constructor
     * @param string|null $formHandler corresponds to the action field of the form
     * @param string|null $formTitle corresponds to the title displayed before the form, if null nothing is displayed
     * @param Input|Input[]|string $formInputs corresponds to all the input elements of the form, can be instantiated with a single Input or an array of Input Objects
     */
    public function __construct(array|Input|string $formInputs, ?string $formTitle = null, string $id = null, string $formHandler = null)
    {
        $this->formAction = $formHandler;
        $this->formTitle = $formTitle;
        $this->formInputs = $formInputs;
        $this->id = $id;
    }

    /**
     * Static function used to easily instantiate the default Login Form
     * @return Form an instance of Form with default Login Form settings (
     * $formInputs = [Input::defaultUsernameInput(), Input::defaultPasswordInput(), Input::defaultSubmitInput("Log-In")]
     * $formTitle = "Log-In")
     */
    public static function defaultLoginForm(): Form
    {
        return new Form([new Input(type: "hidden", name: "loginType", id: "loginType", value: "username"), Input::defaultUsernameInput(id: "identifier", showTitle: false), Input::defaultLoginPasswordInput(), Input::defaultSubmitInput("Log-In")], id: "login", formHandler: "functions.php?f=login");
    }



    /**
     * Render the form from the input set in $formInputs and the button from $formSubmitButton
     */

    public function render()
    {
        if ($this->formTitle != null) echo "<h3>$this->formTitle</h3>\n";
        $this->startForm();
        $this->renderInputs();
        $this->endForm();
    }

    /**
     * Render inputs elements from Input object(s) set in $formInputs variable
     */

    public function renderInputs()
    {
        //if formInputs is an array of Input object
        if (gettype($this->formInputs) == 'array') {
            foreach ($this->formInputs as $input) {
                $input->render();
            }
        } else if (is_string($this->formInputs)) {
            echo $this->formInputs;
        } //if formInputs is a single Input object
        else {
            $this->formInputs->render();
        }
    }

    /**
     * Open the form tag with $formHandler as action handler
     */

    public function startForm()
    {
        echo "<form method='post' " . ($this->formAction ? "action='$this->formAction'" : "") .
            ($this->id ? "id= '$this->id'" : "") . ">\n";
    }

    /**
     * Close the form tag
     */

    public function endForm()
    {
        echo "</form>\n";
    }
}
