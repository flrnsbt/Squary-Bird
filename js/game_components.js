class GameComponent {
    constructor(x, y, width, height, velocityXFactor = 1, isPermanent = false) {
        this.originalX = x;
        this.originalY = y;
        this.width = width;
        this.height = height;
        this.ratio = this.width / this.height;
        this.isPermanent = isPermanent;
        this.velocityXFactor = velocityXFactor;
    }

    async init() {
        this.x = this.originalX;
        this.y = this.originalY;
        return this;
    }

    update(dt) {
        if (!this.isPermanent) {
            this.clear();
            this.x -= gameCore.velocityX * this.velocityXFactor * dt;
            if (this.x + this.width < 0 || this.y > gameCore.height) {
                this.remove();
            }
        }
    }

    clear() {
        gameCore.context.clearRect(this.x - 1, this.y - 1, this.width + 2, this.height + 2);
    }

    remove() {
        gameCore.removeComponent(this);
    }

    draw(canvas) {}

}

class TextComponent extends GameComponent {
    constructor(x, y, font, color, text) {
        super(x, y);
        this.font = font;
        this.text = text;
        this.color = color;
    }

    async init() {
        super.init();
        gameCore.context.fillStyle = this.color;
        gameCore.context.font = this.font;
        let metrics = gameCore.context.measureText(this.text);
        this.width = metrics.width;
        this.height = metrics.actualBoundingBoxAscent + metrics.actualBoundingBoxDescent;
        this.x -= this.width/2;
        return this;
    }

    draw(canvas) {
        canvas.beginPath();
        canvas.font = this.font;
        canvas.fillText(this.text, this.x, this.y);
    }

    clear() {
        gameCore.context.clearRect(this.x - 1, this.y - this.height, this.width*1.1, this.height * 1.3);
    }


}

class SpriteComponent extends GameComponent {
    constructor(spriteSheet, x, y, width, height, velocityXFactor, isPermanent = false) {
        super(x, y, width, height, velocityXFactor, isPermanent);
        this.spriteSheet = spriteSheet;
        this.originY = this.originX = 0;
    }

    async init() {
        super.init();
        if (this.width === null || this.width === undefined) {
            this.width = this.height * (this.spriteSheet.width / this.spriteSheet.height) / (this.numberOfFrames || 1);
        }
        if (this.height === null || this.height === undefined) {
            this.height = this.width * (this.spriteSheet.height / this.spriteSheet.width) * (this.numberOfFrames || 1);
        }
        return this;
    }

    update(dt) {
        super.update(dt);
    }

    draw(canvas) {
        super.draw(canvas);
        canvas.drawImage(this.spriteSheet,
            this.originX,
            this.originY,
            this.spriteSheet.width / (this.numberOfFrames || 1),
            this.spriteSheet.height,
            this.x,
            this.y,
            this.width,
            this.height);
    }
}

class AnimatedSpriteComponent extends SpriteComponent {
    constructor(spriteSheet, numberOfFrames, x, y, width, height, velocityXFactor, isPermanent = false) {
        super(spriteSheet, x, y, width, height, velocityXFactor, isPermanent);
        this.numberOfFrames = numberOfFrames || 1;
        this.frameIndex = this.fStart = 0;
        this.fEnd = this.numberOfFrames;
        this.dRefresh = 0;
    }

    update(dt) {
        super.update(dt);
        this.dRefresh += dt * gameCore.velocityX;
        if (this.dRefresh > 20) {
            this.dRefresh = 0;
            this.frameIndex++;
            if (this.frameIndex >= this.fEnd) {
                this.frameIndex = this.fStart;
            }
        }
    }

    draw(canvas) {
        this.originX = this.frameIndex * this.spriteSheet.width / this.numberOfFrames;
        super.draw(canvas);
    }
}

class Bird extends AnimatedSpriteComponent {
    velocityY = 0;
    constructor(spriteSheet, x, y, size) {
        super(spriteSheet, 4, x, y, size, null, 1, false);
    }

    async init() {
        super.init();
        this.isFlying = false;
        this.tilt = 1;
        return this;
    }

    update(dt) {
        super.update(dt);
        this.velocityY -= gameCore.gravity * dt;
        this.y -= this.velocityY * dt;
        this.x += gameCore.velocityX * dt;
        this.tilt -= this.tilt * dt;
        if (this.tilt < 0.5) {
            this.fStart = 0;
            this.fEnd = 1;
        }
        if (this.y < 0) {
            this.y = 0;
        }
        if (this.y + this.height * 1.05 >= gameCore.ground.y) {
            gameCore.gameOver();
        }
    }

    fly() {
        if (!this.isFlying) {
            this.tilt = 1;
            this.isFlying = true;
            this.fStart = 0;
            this.fEnd = this.numberOfFrames;
            this.velocityY = gameCore.gravity * 0.3;
            setTimeout(() => {
                this.isFlying = false;
            }, 10);
        }
    }
}

class Brick extends SpriteComponent {
    constructor(spriteSheet, x, y, width, height) {
        super(spriteSheet, x, y, width, height);
    }

    async init() {
        super.init();
        this.numberOfBricks = ~~this.height / this.spriteSheet.height;
        this.spacingSize =  ~~((this.numberOfBricks - gameCore.bird.height*2.3/this.spriteSheet.height -2) /(1+gameCore.currentScore*0.1) * Math.random() + gameCore.bird.height*2.3/this.spriteSheet.height);
        this.spacingStart = ~~(Math.random()*(this.numberOfBricks-this.spacingSize));
        return this;
   }

    draw(canvas) {
        for(let i =0; i< this.numberOfBricks; i++){
            if(this.spacingStart === i){
                i += this.spacingSize;
            }else{
                canvas.drawImage(this.spriteSheet,
                    this.x,
                    this.spriteSheet.height*i,
                    this.width,
                    this.spriteSheet.height);
            }
            
        }
    }

    remove() {
        super.remove();
        gameCore.increaseScore();
    }

    update(dt) {
        super.update(dt);
        if(this.x < gameCore.bird.x + gameCore.bird.width && this.x + this.width > gameCore.bird.x && (gameCore.bird.y + gameCore.bird.height > this.spriteSheet.height*(1+this.spacingSize+this.spacingStart) || gameCore.bird.y < this.spriteSheet.height * this.spacingStart)){
            gameCore.gameOver();
        }
    }

}