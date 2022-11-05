var gameCore;
$(async function(){
    gameCore = await GameCore.instance();
});

class GameCore {
    static async instance(){
        const instance = new GameCore();
        instance.resources = await instance.initAssets();
        instance.highestScore = await instance.getHighScore() || 0;
        instance.status = "created";
        return instance;
    }

    create(canvas, hud){
        this.canvas = canvas;
        this.context = canvas.getContext('2d');
        this.x = this.y = 0;
        this.width = this.canvas.width = window.innerWidth; 
        this.height = this.canvas.height = window.innerHeight;
        this.hud = hud;
        $(window).resize(function () {
            this.pause();
        }.bind(this));
        $(document).on("visibilitychange", function (){ if(this.status === "running"){
            this.pause();
        }}.bind(this));
        $('a.nav-link').bindFirst("click.game", function(e) {
            e.preventDefault();
            if(this.status !== "ready" && this.status !== "stopped"){
                this.pause();
                e.stopImmediatePropagation();
                displayPopup(`<div>Do you really want to quit?</div><div style='float:right;'><button class='close-dialog'>No</button><button id="yes">Yes</button></div>` ,{header: "Exit"});
                $("#yes").on("click",function (){
                    this.stop();
                    e.target.dispatchEvent(e.originalEvent);
                }.bind(this));
            }else{
                this.dispose();
            }
        }.bind(this));
        $("#sidebar-open-button").on( "click.game", function () {
            if(this.status === "running"){
                this.pause();
            }
        }.bind(this));
        $(this.hud).find("#hud-pause-button").on("click", function () {
                if (this.status === "running") {
                    this.pause();
                } else if (this.status === "paused") {
                    this.resume();
                }if(this.status === "ready"){
                    this.resume();
                    this.bird.fly();
                    this.removeComponent(this.instruction);
                } else if (this.status === "stopped") {
                    this.init();
                }
            
        }.bind(this));
        $(document).keydown(function (e) {
            e.preventDefault();
            if(!popupInterrupt()){
                switch (e.code) {
                    case "Space":
                        if (this.status === "running") {
                            this.bird.fly();
                        } else {
                                if (this.status === "paused") {
                                    this.resume();
                                }else if(this.status === "ready"){
                                    this.resume();
                                    this.bird.fly();
                                    this.removeComponent(this.instruction);
                                }
                            
                        }
                        break;
                    case "ArrowUp":
                        this.bird.fly();
                        break;
                    case "Escape":
                        this.pause();
                        break;
                }
            }else{
                if (e.code === "Space" || e.code === "Escape") {
                    if (this.status === "stopped") {
                        $('.popup').remove();
                        $(".overlay").remove();
                        this.init();
                    }
                }
            }
        }.bind(this));
        this.status = "initialized";
        this.init();
    }

    async initAssets() {
        let resources = [];
        for (const k of ['cloud', 'ground', 'tree-1', 'tree-2', 'tree-3', 'tree-4', 'bird', 'brick']) {
            let image = new Image();
            image.src = `resources/sprites/${k}.png`;
            await image.decode();
            resources[k] = image;
        }
        return resources;
    }

    async getHighScore() {
        let score = 0;
        await $.get('functions.php?f=highScore',
            function (response) {
                if (response.type === "success") {
                    score = response.data.score;
                }
            }, 'json');
        return score;
    }

    // function called before the game engine is started
    async init() {
        this.context.clearRect(this.x,this.y, this.width, this.height);
        this.components = [];
        $(this.hud).find($("#hud-pause-button")).css('background-image', "url('resources/imgs/play.png'");
        this.lastVelocityIncrease = this.ranking =  this.lastObstacleSpawn = this.lastCloudSpawn = this.lastTreeSpawn = this.distanceCovered = this.currentScore = 0;
        this.gravity = this.height * 2;
        this.velocityX = this.width * 0.2;
        $(this.hud).find("#score").html(this.currentScore);
        $('.popup').remove();
        $(".overlay").remove();
        this.t = Date.now();
        await this.addComponent(this.bird = new Bird(this.resources.bird, 0.1 * this.width, this.height * 0.5 - this.height * 0.12, this.height * 0.08), 1);
        await this.addComponent(this.instruction = new TextComponent(this.bird.x+this.bird.width, this.height / 2, `${this.height*0.03}px Mitr`, "#00000017", "Press Space Bar to start!"));
        await this.addComponent(this.ground = new SpriteComponent(this.resources.ground, 0, this.height * 0.9, this.width, this.height * 0.1, 1, true), this.components.length);
        this.draw(this.context);
        await this.addComponent(new TextComponent(this.width / 2, this.height / 2 - this.height * 0.075, `bold ${this.height*0.1}px Mitr`, "#00000007", "Squary Bird"));
        await this.addComponent(new TextComponent(this.width / 2, this.height / 2 + this.height * 0.075, `bold ${this.height*0.04}px Mitr`, "#00000007", "Florian Sabate"));
        this.pausedText = new TextComponent(this.width/2, this.height/2, `bold ${this.height*0.2}px Mitr`, "#00000007", "PAUSED");
        this.status = "ready";
                return true;
    }

    // function in charge of looping 
    loop = () => {
        if (this?.status === "running") {
            let currentTime = Date.now();
            let dt = currentTime - this.t;
            if (dt > 1) {
                this?.update(dt / 1000);
                this?.draw(this.context);
                this.t = currentTime;
            }
            requestAnimationFrame(this?.loop);
        }
    }

    dispose(){
        $('a.nav-link').off("click.game");
        $("#sidebar-open-button").off("click.game");
        $(this.hud).find($("#hud-pause-button")).off();
        $(document).off("keydown");
        $(window).off("resize");
        this.status = "instantiated";
    }

    //pause the game engine (update and draw functions are not triggered)
    pause() {
        if(this.status === "running"){
            $(this.hud).find($("#hud-pause-button")).css('background-image', "url('resources/imgs/play.png')");
            this.status = "paused";
            this.pausedText.init().then( v => v.draw(this.context));
        }
    }

    // resume the game engine 
    resume() {
        this.pausedText.clear();
        this.t = Date.now()-1;
        this.status = "running";
        $(this.hud).find($("#hud-pause-button")).css('background-image', "url('resources/imgs/pause.png'");
        requestAnimationFrame(this?.loop);
    }

    stop() {
        $(this.hud).find($("#hud-pause-button")).css('background-image', "url('resources/imgs/play.png')");
        this.status = "stopped";
    }


    async gameOver() {
        this.stop();
        let rank = await $.ajax({
            url: 'functions.php?f=getRank',
            type: 'POST',
            data: {
                "score": this.currentScore
            },
            dataType: "json"
        });
        if (rank.type === "success") {
            this.ranking = rank.data;
        }
        if (this.currentScore > this.highestScore) {
            this.highestScore = this.currentScore;
            $.ajax({
                url: 'functions.php?f=addScore',
                type: 'POST',
                data: {
                    "score": this.currentScore
                },
            }).done(function (response) {
                this.showGameOverOverlay();
                if (response.data === "Not logged in") {
                    $("#ranking").after("<div class='warning'><p><b>You are currently logged out!</b><br>If you want to save your score, please consider logging-in or creating an account</p></div>");
                }
            }.bind(this));
        }else{
            this.showGameOverOverlay();
        }
    }

    // function called before each frame is drawn
    update(dt) {
        this.distanceCovered += dt * this.velocityX; 
        if(this.distanceCovered > this.lastVelocityIncrease + this.distanceCovered){
            this.distanceCovered = this.lastVelocityIncrease;
            this.velocityX *= this.gravity *= 1 + this.distanceCovered / (this.distanceCovered+1) ;
        }
        if (this.distanceCovered - this.lastObstacleSpawn > 30* this.velocityX * Math.random() / (1 + this.currentScore * 0.1) + 1.1*this.velocityX) {
            this.lastObstacleSpawn = this.distanceCovered;
            this.addComponent(new Brick(this.resources.brick, this.width, 0, this.bird.width, this.ground.y));
        }
        if (this.distanceCovered - this.lastCloudSpawn > Math.random() * 150* this.velocityX + 2*this.velocityX) {
            this.lastCloudSpawn = this.distanceCovered;
            let r = Math.random();
            let s = this.height * 0.05 + this.height * 0.15 * r;
            this.addComponent(new SpriteComponent(this.resources.cloud, this.width, (1 - r) * (this.height * 0.75 - s), null, s, 0.2 + 0.2 * r), 0);
        }
        if (this.distanceCovered - this.lastTreeSpawn > Math.random() * 100* this.velocityX + 2*this.velocityX) {
            this.lastTreeSpawn = this.distanceCovered;
            this.addComponent(new SpriteComponent(this.resources[`tree-${~~(Math.random()*3) +1}`], this.width, this.ground.y - this.height * 0.15, this.height * 0.15, this.height * 0.15, 0.95), 1);
        }
        this.components.forEach((g) =>
            g.update(dt));
    }

    increaseScore() {
        this.currentScore++;
        $(this.hud).find($("#score")).html(~~this.currentScore);
    }

    // draw the frame on the canvas
    draw(canvas) {
        this.components.forEach((g) =>
            g.draw(canvas));
    }

    async addComponent(gameComponent, index) {
        await gameComponent.init();
        if (index !== undefined) {
            this.components.splice(index, 0, gameComponent);
        } else {
            this.components.splice(this.components.length - 1, 0, gameComponent);
        }
    }

    removeComponent(gameComponent) {
        gameComponent.clear();
        this.components.splice(this.components.indexOf(gameComponent), 1);
    }

    showGameOverOverlay() {
        displayPopup(`<h1 style='color: #e64c3a; margin:0; font-size: 3em;'>GAME OVER</h1><b>Current Score: ${this.currentScore} High Score: ${this.highestScore}</b><br>Your Current Ranking is<h2 style='margin:0 auto;' id='ranking'>#${this.ranking}</h2><br><div style='display:table; width: 100%; justify-content:space-evenly; align-items: center;'><button id="play-again-button" class="game-button"></button><button id='leaderboard-button' class="game-button"></button><button id='exit-button' class="game-button"></button></div>`,{onDismiss: function(){
            this.init();
        }.bind(this)});
        $("#leaderboard-button").on("click", function () {
            $("#sidebar > a:nth-child(3)").click();
        });
        $("#exit-button").on("click", function () {
            $("#top-navbar > a:nth-child(2)").click();
        });
        $("#play-again-button").on("click", function () {
            this.init();
        }.bind(this));
    }
}