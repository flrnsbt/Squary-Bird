<!DOCTYPE html>

<style>
    body {
        color: #555;
    }

    .row {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        white-space: nowrap;
        align-items: center;
    }

    /* .row> *{
        flex: 1 1 0;
        width: 0;
    } */

    .footer a>* {
        height: 100%;
    }

    .footer {
        height: 5vh;
        position: fixed;
        bottom: 4vh;
    }

    .row>* {
        height: 100%;
        margin-left: 1em;
        margin-right: 1em;
    }

    #home-play-button {
        height: 0;
        width: 0;
        display: flex;
        padding: 40px;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        cursor: pointer;
        color: lightblue;
        background-color: #13a2ce;
    }
</style>


<script>
    $(function() {
        $("#home-play-button").on("click", function() {
            loadPageContent("?content=game");
        });
        removeLoadingIndicator();
    });
</script>
<header>
    <script src="https://unpkg.com/ionicons@latest/dist/ionicons.js"></script>
</header>

<body>
    <div class="center" style="width:50%; height:30%;">
        <div class="row" >
            <img src="resources/imgs/bird.gif">
            <div style="display: flex;justify-content: center;flex-direction:column;">
                <div style="font-size:2em; line-height:1em;">Welcome to</div>Squary Bird
            </div>
            <div id="home-play-button">&#9658;</div>
        </div>
    </div>
    <div class="row footer">
        <a href="https://github.com/flrnsbt/Squary-Bird" >
            <ion-icon size="large" style='color: #13a2ce; float: left; margin-right: 5px;' name="logo-github"></ion-icon>
            <p>Github</p>
        </a>
    </div>
</body>

</html>