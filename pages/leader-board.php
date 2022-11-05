<!DOCTYPE html>

<style>
    .leaderboard-item{
        padding-top: 1em;
        padding-bottom: 1em;
        padding-left: 2em;
        padding-right: 2em;
        border-radius: 1vw;
        background-color: rgb(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        color: #333;
        margin-top: 1em;
        margin-bottom: 1em;
    }

    .leaderboard-score{
        font-weight: bold;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        font-size: 2em;
        color: #8B8B8B;
    }
    .leaderboard-ranking{
        font-size: 2em;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        font-weight: bold;
        color: #707070;
    }
    .leaderboard-username{
        font-size: 1em;
        margin-left: 2em;
        color: #747474;
    }

    #leaderboard-content{
        height: 100%;
        overflow-y: scroll;
    }
</style>
<script> $(function () {
    soapRequest("user_score", "r.score, r2.username", 50).done(function (response) {
        let content = $("#leaderboard-content");
        let i =1;
        $(response).find('user_score').each(function () {
            content.append(`<div class="leaderboard-item" id="leaderboard-item-${i}"><div class="leaderboard-ranking">#${i}</div><div class="leaderboard-username">${$(this).attr("username")}</div><div style="flex:1;"></div><div class="leaderboard-score">${$(this).attr("score")}</div></div>\n`);
            i++;
        });
        content.append("<div style='height: 20%;'></div>");
        (() => {
            $.get('functions.php?f=highScore',
            function (response) {
                if(response.type === "success"  && response.data != null){
                    $.ajax({
                        url: 'functions.php?f=getRank',
                        type: 'POST',
                        data: {"score": response.data.score},
                        dataType: "json"
                    }).done(function (response){
                        if(response.type === "success"){
                            $("#score-user .leaderboard-ranking").text("#"+response.data);
                        }else{
                            $("#score-user").remove();
                        }
                    }).fail(function (xhr, textStatus, errorThrown) {
                        $("#score-user").remove();
                    });
                    $("#score-user .leaderboard-score").text(response.data.score);
                }else{
                    $("#score-user").remove();
                }
            }.bind(this),'json');
        })();
    });
    removeLoadingIndicator();
});</script>
<body>
    <div class='default-margin' style="display:flex; flex-flow: column;height: 100%;">
        <div style="color: #707070; font-size:2em;">Leaderboard</div>
        <div class="leaderboard-item" id="score-user" style="background-color: rgb(255,255,255,0.4);"><div class="leaderboard-ranking"></div><div class="leaderboard-username"><?php echo read("username");?><p style="font-size:0.75em; line-height:0;">(Your best score)</p></div><div style="flex:1;"></div><div class="leaderboard-score"></div></div>
        <div id="leaderboard-content">
        </div>
    </div>

</body>

</html>