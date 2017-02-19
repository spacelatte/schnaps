<?php
	$data = file_get_contents('php://input');
	if(!empty($data)) {
		//print_r($data);
		$json = json_decode($data, true);
		$name = md5($json["name"]);
		$data = $json["data"];
		switch($json["type"]) {
			case "up":
				file_put_contents($name, $data);
				echo json_encode(
					array(
						"status" => "ok",
						"image" => $data,
					)
				);
				break;
			case "dl":
				if(!file_exists($name)) {
					echo json_encode(
						array(
							"status" => "fail",
						)
					);
					break;
				}
				$data = file_get_contents($name);
				echo json_encode(
					array(
						"status" => "ok",
						"image" => $data,
					)
				);
				break;
			default:
				echo json_encode(
					array(
						"status" => "fail",
					)
				);
				break;
		}
		exit(0);
	}
?>
<html>
	<head>
		<title>Smart Notebook</title>
		<meta name="author" content="Mert Akengin" >
		<meta name="description" content="Smart notebook main page" >
		<meta name="keywords" content="">
		<meta name="viewport" content="initial-scale=0.8, maximum-scale=1.2, width=device-width, user-scalable=no" />
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="theme-color" content="navy">
		<meta charset="utf-8" />
		<style>
			html {
				background-color: navy;
			}
			body {
				background-color: white;
			}
			#upload, #download, #viewer {
				display: inline-block;
				width: 49%;
				height: 100%;
				padding: 3%;
				border: 0px solid black;
				font-size: 0px;
				background-size: contain;
				background-position: center center;
				background-repeat: no-repeat;
				background-origin: content-box;
				box-sizing: border-box;
			}
			#main { background-color: white; }
			#main, #viewer, #reader, VIDEO, CANVAS {
				position: fixed;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				width: auto;
				height: auto;
			}
			#upload {
				background-image:url(up.ico);
				float: left;
			}
			#download {
				background-image:url(down.ico);
				float: right;
			}
			VIDEO, _CANVAS {
				z-index: 99;
				width: 100%;
				height: 100%;
			}
			#viewer, #reader, VIDEO, CANVAS {
				background-color: black;
				background-image: url("./loader.gif");
				background-position: center center;
				background-repeat: no-repeat;
				background-size: 50%;
			}
		</style>
	</head>
	<body>
		<div id=reader ></div>
		<div id=viewer ></div>
		<div id=main >
			<span id=upload >yukle</span>
			<span id=download >getir</span>
		</div>
	</body>
<script type="text/javascript" src="./qr/src/grid.js"></script>
<script type="text/javascript" src="./qr/src/version.js"></script>
<script type="text/javascript" src="./qr/src/detector.js"></script>
<script type="text/javascript" src="./qr/src/formatinf.js"></script>
<script type="text/javascript" src="./qr/src/errorlevel.js"></script>
<script type="text/javascript" src="./qr/src/bitmat.js"></script>
<script type="text/javascript" src="./qr/src/datablock.js"></script>
<script type="text/javascript" src="./qr/src/bmparser.js"></script>
<script type="text/javascript" src="./qr/src/datamask.js"></script>
<script type="text/javascript" src="./qr/src/rsdecoder.js"></script>
<script type="text/javascript" src="./qr/src/gf256poly.js"></script>
<script type="text/javascript" src="./qr/src/gf256.js"></script>
<script type="text/javascript" src="./qr/src/decoder.js"></script>
<script type="text/javascript" src="./qr/src/qrcode.js"></script>
<script type="text/javascript" src="./qr/src/findpat.js"></script>
<script type="text/javascript" src="./qr/src/alignpat.js"></script>
<script type="text/javascript" src="./qr/src/databr.js"></script>
	<script src="./md5/js/md5.min.js" ></script>
	<script type="text/javascript" >
		let proc = false;
		let type = null;
		function process(data) {
			if(proc) {
				return;
			}
			proc = true;
			//if(!confirm(data)) return;
			let image = document.getElementById("qr-canvas").toDataURL();
			let viewer = document.getElementById("viewer");
			let xhr = new XMLHttpRequest();
			xhr.onloadend = function(e) {
				proc = false;
				document.getElementById("main").style.opacity = "1";
				if(this.status !== 200) {
					return;
				}
				document.getElementById("viewer").style.zIndex = "99";
				if(!this.response.image) {
					viewer.style.backgroundImage = "url(//i.ytimg.com/vi/lFjWA5w74nY/maxresdefault.jpg)";
					return;
				}
				viewer.style.backgroundImage = "url(" + this.response.image + ")";
				viewer.style.backgroundSize = "100%";
				switch(type) {
					case "up":
						break;
					case "dl":
						break;
					default:
						console.log("invalid type:", type);
						break;
				}
				return;
			};
			xhr.open("POST", window.location, true);
			xhr.withCredentials = true;
			xhr.responseType = "json";
			xhr.setRequestHeader("content-type", "text/plain");
			xhr.send(
				JSON.stringify({
					"name": md5(data),
					"type": type,
					"data": image,
				}, null, 4)
			);
			document.getElementById("main").style.opacity = "0";
			kill();
			return;
		}
		function camera(elem) {
			elem.innerHTML = "";
			navigator.mediaDevices.getUserMedia(window.opts).then(function(stream) {
				window.str = stream;
				let video = document.createElement("video");
				let canvas = document.createElement("canvas");
				video.src = window.URL.createObjectURL(stream);
				canvas.id = "qr-canvas";
				canvas.style.zIndex = "111";
				canvas.style.display = "none";
				elem.appendChild(video);
				elem.appendChild(canvas);
				window.int = setInterval(function(e) {
					canvas.width = video.videoWidth;
					canvas.height = video.videoHeight;
					canvas.getContext("2d").drawImage(video, 0, 0, canvas.width, canvas.height);
					try {
						qrcode.decode();
					} catch(e) {
					//	console.log(e);
					}
					return;
				}, 999);
				return;
			}).catch(function(error) {
				console.log(error);
				return;
			});
			document.getElementById("reader").style.zIndex = "99";
			return;
		}
		function kill() {
			document.getElementById("reader").style.zIndex = "0";
			window.str && window.str.getTracks().forEach(function(track) {
				track.stop();
				return;
			});
			window.int && clearInterval(window.int);
			return;
		}
		function select() {
			if(!window.opts) {
				window.opts = {
					audio: false,
					video: {
						facingMode: "environment",
					},
				};
			}
			navigator.mediaDevices.enumerateDevices().then(function(devices) {
				for(let i=0; i<devices.length; i++) {
					let dev = devices[i];
					if(dev.kind === "videoinput") {
						if(dev.label.indexOf("back") >= 0) {
							window.opts.video.deviceId = dev.deviceId;
						}
					}
				}
				return;
			});
			return;
		}
		window.onload = function() {
			document.getElementById("upload").addEventListener("click", function(e) {
				type = "up";
				camera(document.getElementById("reader"));
				return;
			});
			document.getElementById("download").addEventListener("click", function(e) {
				type = "dl";
				camera(document.getElementById("reader"));
				return;
			});
			document.getElementById("reader").addEventListener("click", function(e) {
				kill();
				return;
			});
			document.getElementById("viewer").addEventListener("click", function(e) {
				this.style.backgroundImage = "";
				this.style.backgroundSize = "";
				this.style.zIndex = 0;
				return;
			});
			getPerm();
			select();
			qrcode.callback = process;
			console.log("init done");
			return;
		};
		function getPerm() {
			navigator.mediaDevices.getUserMedia({video:true}).then(function(stream) {
				stream.getTracks().forEach(function(track) {
					track.stop();
					return;
				});
			}).catch(function(e) {
				document.getElementById("main").style.opacity = "0";
				console.log(e);
				return;
			});
			return;
		}
	</script>
</html>
