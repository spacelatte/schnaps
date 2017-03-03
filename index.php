<?php
	const BASEDIR = "./data/";
	if(!empty($_GET)) {
		if(isset($_GET["list"])) {
			$cont = scandir(BASEDIR);
			?>
				<style>
					img {
						display:block;
						width:90%;
						height:auto;
						margin:4px auto;
						box-sizing:border-box;
					}
				</style>
				<a href="./">back</a>
			<?php
			for($i=2; $i<sizeof($cont); $i++) {
				echo "<img src='".file_get_contents(BASEDIR.$cont[$i])."'></img>\n";
			}
			exit(0);
		}
		if(strlen($_SERVER["QUERY_STRING"])) {
			echo file_get_contents(BASEDIR.md5($_SERVER["QUERY_STRING"]));
		}
		exit(0);
	}
	$data = file_get_contents('php://input');
	if(!empty($data)) {
		//print_r($data);
		$json = json_decode($data, true);
		$name = BASEDIR.md5($json["name"]);
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
				file_put_contents($name, $data);
				echo json_encode(
					array(
						"status" => "ok",
						"image" => "data:image/png;base64,".base64_encode(file_get_contents("https://chart.googleapis.com/chart?cht=qr&chs=512x512&chl="
							.urlencode($json["type"]))),
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
		<link rel="icon" type="image/png" href="./nb.png" />
		<meta charset="utf-8" />
		<style>
			html, body {
				margin:0;
				padding:0;
			}
			html {
				background-color: navy;
			}
			body {
				background-color: white;
			}
			#viewer {
				display: inline-block;
				width: 49%;
				height: 100%;
				padding: 3%;
				border: 0px solid black;
				font-size: 0px;
			}
			.btn {
				display:inline-block;
				width:240px;
				height:240px;
				font-size:0;
				background-size: contain;
				background-position: center center;
				background-repeat: no-repeat;
				background-origin: content-box;
				box-sizing: border-box;
				margin:8px;
				_transform:translateY(33%);
			}
			#main {
				overflow:auto;
				position:relative;
				background-color: white;
				text-align:center;
				min-height:100vh;
				_min-width:100vw;
				padding-bottom:12px;
			}
			#viewer, #reader, VIDEO, CANVAS {
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
			}
			#download {
				background-image:url(down.ico);
			}
			#local {
				background-image:url(disk.png);
			}
			#code {
				background-image:url(qr.png);
			}
			#trash {
				background-image:url(trash.svg);
			}
			.img {
				display:block;
				width:80vw;
				height:80vw;
				margin:12px auto;
				font-size:0;
				background-repeat:no-repeat;
				background-size:contain;
				background-color:black;
				background-position:center;
			}
			VIDEO, _CANVAS {
				z-index: 99;
				width: 100%;
				height: 100%;
			}
			#viewer, #reader, VIDEO, CANVAS {
				background-color: rgb(43,40,40);
				background-image: url("./loop.gif");
				background-position: center center;
				background-repeat: no-repeat;
				background-size: contain;
			}
		</style>
	</head>
	<body>
		<div id=reader ></div>
		<div id=viewer ></div>
		<div id=main >
			<span class=btn id=upload >yukle</span>
			<span class=btn id=download >getir</span>
			<span class=btn id=local >bak</span>
			<span class=btn id=trash >temizle</span>
			<span class=btn id=code >yarat</span>
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
			if( window.localStorage.getItem(md5(data)) ) {
				viewer.style.backgroundSize = "contain";
				document.getElementById("main").style.visibility = "visible";
				viewer.style.backgroundImage = "url("
					+ window.localStorage.getItem(md5(data)) + ")";
				viewer.style.zIndex = "99";
				proc = false;
				kill();
				return;
			}
			xhr.onloadend = function(e) {
				proc = false;
				viewer.style.backgroundSize = "contain";
				document.getElementById("main").style.visibility = "visible";
				if(this.status !== 200) {
					return;
				}
				viewer.style.zIndex = "99";
				if(!this.response.image) {
					viewer.style.backgroundImage = "url(//i.ytimg.com/vi/lFjWA5w74nY/maxresdefault.jpg)";
					return;
				}
				viewer.style.backgroundImage = "url(" + this.response.image + ")";
				switch(type) {
					case "up":
						break;
					case "dl":
						break;
					default:
						break;
				}
				type = null;
				window.localStorage.setItem(md5(data), this.response.image);
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
			document.getElementById("main").style.visibility = "hidden";
			//window.localStorage.setItem(md5(data), image);
			//alert(md5(data) + " " + data.substring(0,99));
			kill();
			return;
		}
		function camera(elem, manual) {
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
				
				setTimeout(function(e) {
					canvas.width = video.videoWidth;
					canvas.height = video.videoHeight;
					return;
				}, 999);
				
				if(manual !== undefined) {
					video.id = manual;
					type = manual;
					return;
				}
				window.int = setInterval(function(e) {
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
		function view(image) {
			viewer.style.zIndex = "99";
			viewer.style.backgroundImage = "url(" + image + ")";
			viewer.style.backgroundSize = "contain";
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
				if(type !== undefined && type.length > 3) {
					let canvas = document.getElementById("qr-canvas");
					let video = document.getElementsByTagName("video")[0];
					let key = ""
						+ window.location.protocol
						+ "//"
						+ window.location.hostname
						+ ":"
						+ window.location.port
						+ window.location.pathname
						+ "?"
						+ type;
					let tmp = type;
					type = key;
					canvas
						.getContext("2d")
						.drawImage(video, 0, 0, canvas.width, canvas.height);
				//	setTimeout(function(e) {
						process(tmp);
				//		return;
				//	}, 99);
				}
				kill();
				return;
			});
			document.getElementById("viewer").addEventListener("click", function(e) {
				this.style.backgroundImage = "";
				this.style.backgroundSize = "";
				this.style.zIndex = "";
				return;
			});
			document.getElementById("local").addEventListener("click", function(e) {
				let stor = window.localStorage;
				let main = document.getElementById("main");
				let orig = main.innerHTML;
				window.orig = orig;
				main.style.visibility = "hidden";
				main.innerHTML = "<a href='#' class=img style='width:72px;height:72px;background-color:transparent;background-image:url(back.png);' onclick='javascript:document.getElementById(\"main\").innerHTML = window.orig; window.onload(null); ' ></a>";
			//	main.getElementsByTagName("A")[0].addEventListener("click", function(e) {
			//		document.getElementById("main").innerHTML = orig;
			//		return;
			//	});
				let buffer = "";
				for(let i=0; i<stor.length; i++) {
					let key = stor.key(i);
					let obj = stor.getItem(key);
					let elem = "<a href='"
						+ obj
						+ "' style='background-image:url("
						+ obj
						+ ");' "
						+ "class='img' "
						+ ">"
						+ key
						+ "</a>";
					buffer += elem;
					continue;
				}
				main.innerHTML += buffer;
				main.style.visibility = "visible";
				return;
			});
			document.getElementById("code").addEventListener("click", function(e) {
				camera(document.getElementById("reader"), (Date.now() + ":" + Math.random()));
				return;
			});
			document.getElementById("trash").addEventListener("click", function(e) {
				if(confirm("are you sure?" + "\n" + "this will clear local cache only"))
					window.localStorage.clear();
				return;
			});
			if(window.location.search.length > 0) {
				type = "dl";
				main.style.visibility = "hidden";
				process(window.location.search.substring(1));
			}
			select();
			qrcode.callback = process;
			console.log("init done");
			return;
		};
		function getPerm() {
			navigator.mediaDevices.getUserMedia({video:true}).then(function(stream) {
				setTimeout(function(e) {
					stream.getTracks().forEach(function(track) {
						track.stop();
						return;
					});
					return;
				}, 333);
			}).catch(function(e) {
				document.getElementById("main").style.visibility = "visible";
				console.log(e);
				return;
			});
			return;
		}
		getPerm();
	</script>
</html>
