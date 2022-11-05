<!DOCTYPE html>
<style>
    #hud {
        position: fixed;
        left: 50%;
        width:100px;
        -webkit-transform: translate(-50%, 0);
        display: flex;
        align-items: center;
        justify-content: space-evenly;
        transform: translate(-50%, 0);
        background-color: rgb(0, 0, 0, 0.1);
        border-radius: 2vw;
        margin-top: 10px;
        padding: 5px;
        color: lightcyan;
    }

    #score {
        font-size: 15pt;
        font-weight: 900;
    }

    #life {
        font-size: 2vw;
    }


    #top-navbar button,
    #top-navbar a {
        background-color: rgb(0, 0, 0, 0.1);
        color: white;
    }

    .game-button {
        background-color: #e64c3a;
        background-position: center;
        background-repeat: no-repeat;
        background-size: 20%;
        padding: 15px;
        margin: 0;
    }

    #play-again-button {
        background-image: url('resources/imgs/play.png');
    }

    #exit-button {
        background-image: url('resources/imgs/exit.png');
    }

    #leaderboard-button {
        background-image: url('resources/imgs/leaderboard.png');
    }

</style>

<script>
    $(function(){
        gameCore.create(document.getElementById("game-canvas"), document.getElementById("hud"));
        removeLoadingIndicator();
    }); 
</script>

<div id="hud">
    <div id="score">0</div>
    <button id="hud-pause-button" class="game-button"></button>
</div>
<canvas id="game-canvas"></canvas>