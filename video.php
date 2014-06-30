<?php

// the following global variables may need to be modified based on the server's configuration

// if a video is selected
$video_url = $_GET["video"];

// for getting the video list
// the directory on the server where the videos are stored
$video_dir = "/var/www/public/temp/downloads";
// the url that points to that directory
$video_dir_url = "http://vps.bismith.net/temp/downloads";

// the device the server stores the video files on
// (for Linux and other unix-like OS's)
$dev = "/dev/vda";

function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}

function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

// making this script compatible with operating systems other than Linux
// may require this function to be modified (especially for non-unix-like OS's)
function getServerFreeSpace()
{
	global $dev;
	$os = php_uname('s');
	if ($os === "Linux") {
		$split = explode(' ', exec("df -h | grep " . $dev));
		return $split[14] . "B";
	} else {
		return "unknown";
	}
}

function getVideoListAsTable($video_dir, $video_dir_url)
{
	global $video_url;
	$return = "\n<table border=0>";
	$array = scandir($video_dir);
	foreach ($array as $item) {
		// $return .= "<tr><td>" . ($video_dir_url . "/" . $item) . "</td></tr>";
		// $return .= "<tr><td>" . $video_url . "</td></tr>";
		if (($video_dir_url . "/" . $item) === ($video_url)) {
			$return .= "<tr><td>";
			$return .= $item . " (current video)";
			$return .= "</td></tr>\n";
		} else if (endsWith($item, ".mp4")) {
			$return .= "<tr>";
			$return .= "<td><a class=\"mp4link\" href=\"video.php?video=" . $video_dir_url . "/" . $item . "\">" . $item . "</a></td>";
			$return .= "</tr>\n";
		} else if (endsWith($item, ".mkv")) {
			$return .= "<tr>";
			$return .= "<td><a class=\"mkvlink\" href=\"" . $video_dir_url . "/" . $item . "\">" . $item . "</a></td>";
			$return .= "</tr>\n";
		} else if (endsWith($item, ".mp4.!ut") || endsWith($item, ".mkv.!ut")) {
			$return .= "<tr>";
			$return .= "<td><span class=\"orange\">" . $item . " (incomplete)</span></td>";
			$return .= "</tr>\n";
		} else if (is_dir($video_dir . "/" . $item) && !startsWith($item, '.')) {
			$within = scandir($video_dir . "/" . $item);
			foreach ($within as $thing) {
				if (($video_dir_url . "/" . $item . "/" . $thing) === ($video_url)) {
					$return .= "<tr><td>";
					$return .= $thing . " (current video)";
					$return .= "</td></tr>\n";
				} else if (endsWith($thing, ".mp4")) {
					$return .= "<tr>";
					$return .= "<td><a class=\"mp4link\" href=\"video.php?video=" . $video_dir_url . "/" . $item . "/" . $thing . "\">" . $thing . "</a></td>";
					$return .= "</tr>\n";
				} else if (endsWith($thing, ".mkv")) {
					$return .= "<tr>";
					$return .= "<td><a class=\"mkvlink\" href=\"" . $video_dir_url . "/" . $item . "/" . $thing . "\">" . $thing . "</a></td>";
					$return .= "</tr>\n";
				} else if (endsWith($thing, ".mp4.!ut") || endsWith($thing, ".mkv.!ut")) {
					$return .= "<tr>";
					$return .= "<td><span class=\"orange\">" . $thing . " (incomplete)</span></td>";
					$return .= "</tr>\n";
				}
			}
		}
	}
	$return .= "</table>\n";
	return $return;
}

// returns an html5 video player
function getVideoPlayer($video_url)
{
	$return = '<video width="100%" height="auto" controls>';
	$return .= '<source src="' . $video_url . '" type="video/mp4">';
	$return .= 'Your browser does not support the html5 video tag.';
	$return .= '</video>';
	return $return;
}

function getClientInfo()
{
	$return = $_SERVER['HTTP_USER_AGENT'];
	$return .= ", ";
	$return .= $_SERVER['REMOTE_ADDR'];
	$return .= " ";
	$return .= "(" . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ")";
	return $return;
}

?>

<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">

	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<title>Video</title>
		<style type="text/css">

			/* quick tool to allow colored text n' stuff */
			span.red { color: red; }
			span.darkred { color: #cc2222; }
			span.orange { color: orange; }
			span.yellow { color: yellow; }
			span.green { color: green; }
			span.blue { color: blue; }
			span.purple { color: purple; }
			span.brown { color: #a52a2a; }
			span.black { color: black; }
			span.white { color: white; }
			span.teal { color: #00ccdd; } /* this is my teal, not their teal... dang W3C */

			body {
				margin: 0;
				border: 0;
				padding: 0;
				background-color: #000000;
				font-family: "Lucida Console", Monaco, monospace;
				font-size: 14px;
				/* overflow: hidden; */
			}

			#content {
				width: 70%;
				margin: 0 auto;
				color: #aaaaaa;
			}

			#content a.genlink:link { color: #5555ff; text-decoration: none; }
			#content a.genlink:visited { color: #5555ff; text-decoration: none; }
			#content a.genlink:hover { color: #2222ff; text-decoration: none; }
			#content a.genlink:active { color: #5555ff; text-decoration: none; }

			#content a.mp4link:link { color: #5555ff; text-decoration: none; }
			#content a.mp4link:visited { color: #5555ff; text-decoration: none; }
			#content a.mp4link:hover { color: #2222ff; text-decoration: none; }
			#content a.mp4link:active { color: #5555ff; text-decoration: none; }

			#content a.mkvlink:link { color: #00bb00; text-decoration: none; }
			#content a.mkvlink:visited { color: #00bb00; text-decoration: none; }
			#content a.mkvlink:hover { color: #009900; text-decoration: none; }
			#content a.mkvlink:active { color: #00bb00; text-decoration: none; }

			#disclaimer {
				color: #888888;
			}

			#clientinfo {
				color: #666666;
			}

		</style>
	</head>

	<body class="home" id="top">
		<div id="content">
			<?php
				if ($video_url != "") {
					echo "<h3><a class=\"mp4link\" href=\"" . $video_url . "\">" . str_replace($video_dir_url . "/", "", $video_url) . "</a></h3>";
					echo "<div id=\"video\">" . getVideoPlayer($video_url) . "</div>";
					if (strpos($_SERVER['HTTP_USER_AGENT'], 'Linux') !== false) {
						echo "<p>";
						echo "HTML5 MP4 video playback can be enabled in Linux using Firefox with the addition of GStreamer. ";
						echo "Perhaps the easiest way to add this support in Ubuntu Linux (and variants) is to install ";
						echo "Ubuntu Restricted Extras <code>(sudo apt-get install ubuntu-restricted-extras)</code>. ";
						echo "</p>";
					}
					echo "<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>";
					echo "<h1><a class=\"genlink\" href=\"video.php\">Videos</a></h1>";
				} else {
					echo "<h1>Videos</h1>";
				}
				echo "<h4>Space remaining: " . getServerFreeSpace() . "</h4>";
				echo getVideoListAsTable($video_dir, $video_dir_url);
			?>
			<p id="disclaimer">
				WARNING: Any unauthorized access to this system is prohibited and is subject to criminal and civil penalties
				under Federal Laws (including but not limited to Public Laws 83-703 and 99-474). Individuals using this system
				are subject to having all activities on this system monitored by system or security personnel. Anyone using this
				system expressly consents to such monitoring.
			</p>
			<p id="clientinfo">
				<?php echo getClientInfo(); ?>
			</p>
		</div>
	</body>

</html>

