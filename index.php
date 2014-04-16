<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
	canvas{
		position: absolute;
		margin: 50px auto;
		border: 1px solid #000;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
	}
	</style>
</head>
<body>
	<canvas id="snake" width="500" height="500"></canvas>

	<script type="text/javascript">

	var Component = {};
		Component.canvas = document.getElementById("snake");
		Component.ctx = Component.canvas.getContext("2d");
		Component.w = Component.canvas.width;
		Component.h = Component.canvas.height;
		Component.tile = 20;
		Component.r = Component.w / Component.tile;
		Component.c = Component.h / Component.tile;

	var Thread = {};
		Thread.interval = 0;
		Thread.target = null;
		Thread.init = function(target){
			Thread.target = target;
		}
		Thread.start = function(){
			if(Thread.interval == 0){
				Thread.interval = setInterval(Thread.target.run, 20);
			}
		}
		Thread.stop = function(){
			if(Thread.interval != 0){
				clearInterval(Thread.interval);
				Thread.interval = 0;
			}
		}
		Thread.status = function(){
			return Thread.interval != 0;
		}

	var Direction = {};
		Direction.UP = 38;
		Direction.DOWN = 40;
		Direction.LEFT = 37;
		Direction.RIGHT = 39;

	var Listener = {};
		Listener.listen = function(){
			window.onkeydown = function(e){
				if(e.keyCode == Direction.UP && Snake.dir != Direction.DOWN){
					Snake.dir = Direction.UP;
				}else if(e.keyCode == Direction.DOWN && Snake.dir != Direction.UP){
					Snake.dir = Direction.DOWN;
				}else if(e.keyCode == Direction.LEFT && Snake.dir != Direction.RIGHT){
					Snake.dir = Direction.LEFT;
				}else if(e.keyCode == Direction.RIGHT && Snake.dir != Direction.LEFT){
					Snake.dir = Direction.RIGHT;
				}else if(e.keyCode == 80){
					if(Game.status()){
						Game.pause();
						Screen.Pause.render();
					}else{
						Game.contiune();
					}
				}
			}
		}

	var Grid = {};
		Grid.w = 0;
		Grid.h = 0;
		Grid.pixels = [];
		Grid.init = function(w, h){
			Grid.w = w;
			Grid.h = h;

			for(var x = 0; x < w; x++){
				Grid.pixels[x] = [];
				for(var y = 0; y < h; y++){
					Grid.pixels[x][y] = 0;
				}
			}
		}
		Grid.set = function(x, y, value){
			Grid.pixels[x][y] = value;
		}
		Grid.get = function(x, y){
			return Grid.pixels[x][y];
		}
		Grid.render = function(){
			for(var x = 0; x < Grid.w; x++){
				for(var y = 0; y < Grid.h; y++){
					if(Grid.get(x, y) === Game.EMPTY){
						Component.ctx.fillStyle = "white";
					}else if(Grid.get(x, y) === Game.SNAKE){
						Component.ctx.fillStyle = "blue";
					}else if(Grid.get(x, y) === Game.FOOD){
						Component.ctx.fillStyle = "red";
					}

					Component.ctx.fillRect(x * Component.tile, y * Component.tile, Component.tile, Component.tile);
				}
			}
		}

	/*	
	 * Snake object.
	 */
	var Snake = {};
		Snake.dir = 0;
		Snake.head = null;
		Snake.body = [];
		Snake.init = function(dir, x, y){
			Snake.dir = dir;

			Snake.body = [];
			Grid.set(x, y, Game.SNAKE);
			Snake.grow(x, y);
		}
		Snake.tick = function(){
			var hx = Snake.head.x,
				hy = Snake.head.y;

			// controll the snake
			if(Snake.dir == Direction.UP){
				hy--;
			}else if(Snake.dir == Direction.DOWN ){
				hy++;
			}else if(Snake.dir == Direction.LEFT){
				hx--;
			}else if(Snake.dir == Direction.RIGHT){
				hx++;
			}

			// reset the game if snake is outside the border.
			if(hx < 0 || hx >= Component.r || hy < 0 || hy > Component.c || Grid.get(hx, hy) === Game.SNAKE){
				return Game.reset();
			}

			// add score to scoreboard and grow the snake when snake eat the food.
			if(Grid.get(hx, hy) == Game.FOOD){
				Scoreboard.score++;
				Food.init();
			}else{
				var tail = Snake.remove();
				Grid.set(tail.x, tail.y, Game.EMPTY);
			}

			Grid.set(hx, hy, Game.SNAKE);
			Snake.grow(hx, hy);
		}
		Snake.grow = function(x, y){
			Snake.body.unshift({"x": x, "y": y});
			Snake.head = Snake.body[0];
		}
		Snake.remove = function(){
			return Snake.body.pop();
		}

	var Scoreboard = {};
		Scoreboard.score = 0;
		Scoreboard.init = function(){
			Scoreboard.score = 0;
		}
		Scoreboard.render = function(){
			Component.ctx.fillStyle = "black";
			Component.ctx.font = "13px Helvetica";
			Component.ctx.fillText("SCORE: " + Scoreboard.score, 10, Component.h - 10);
		}

	var Food = {};
		Food.init = function(){
			var empty = [];
			for(var x = 0; x < Component.r; x++){
				for(var y = 0; y < Component.c; y++){
					empty.push({"x": x, "y": y});
				}
			}

			var pos = empty[Math.floor(Math.random() * empty.length)];	
			
			Grid.set(pos.x, pos.y, Game.FOOD);
		}

	var Screen = {};
		Screen.Pause = {};
			Screen.Pause.render = function(){
				Component.ctx.globalAlpha = 0.5;
				Component.ctx.fillStyle = "black";
				Component.ctx.fillRect(0, 0, Component.w, Component.h);
				Component.ctx.globalAlpha = 1;
				Component.ctx.fillStyle = "white";
				Component.ctx.font = "30px Helvetica";
				Component.ctx.fillText("Paused", Component.w / 2 - 50, Component.h / 2 + 10);
			}

	var Game = {};
		Game.EMPTY = 0;
		Game.SNAKE = 1;
		Game.FOOD = 2;
		Game.frames = 0;
		Game.score = 0;
		Game.init = function(){
			Thread.init(Game);
			Game.start();
			Listener.listen();
		}
		Game.start = function(){
			Grid.init(Component.r, Component.c);
			Snake.init(Direction.UP, Math.floor(Component.r / 2) - 1, Component.c - 2);
			Food.init();
			Scoreboard.init();
			Thread.start();
		}
		Game.contiune = function(){
			Thread.start();
		}
		Game.pause = function(){
			Thread.stop();
		}
		Game.tick = function(){
			Snake.tick();
		}
		Game.render = function(){
			Component.ctx.clearRect(0, 0, Component.w, Component.h);

			Grid.render();
			Scoreboard.render();
		}
		Game.run = function(){
			Game.frames++;

			if(Game.frames % 4 == 0){
				Game.tick();
			}
			Game.render();
		}
		Game.reset = function(){
			Thread.stop();
			Game.start();
		}
		Game.status = function(){
			return Thread.status();
		}

	window.addEventListener('load', Game.init);

	</script>
</body>
</html>