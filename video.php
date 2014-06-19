<?php

// if a video is selected
$video_url = $_GET["video"];

// for getting the video list
$video_dir = "/var/www/public/temp/downloads";
$video_dir_url = "http://vps.bismith.net/temp/downloads";

function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}

function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

function getServerFreeSpace()
{
	// the device the video files are stored on
	$dev = "/dev/vda";
	$split = explode(' ', exec("df -h | grep " . $dev));
	return $split[14] . "B";
}

function getVideoListAsTable($video_dir, $video_dir_url)
{
	$return = "<table border=0>";
	$array = scandir($video_dir);
	foreach ($array as $item) {
		if (endsWith($item, ".mp4")) {
			$return .= "<tr>";
			$return .= "<td><a href=\"video.php?video=" . $video_dir_url . "/" . $item . "\">" . $item . "</a></td>";
			$return .= "</tr>\n";
		} else if (is_dir($video_dir . "/" . $item) && !startsWith($item, '.')) {
			$within = scandir($video_dir . "/" . $item);
			foreach ($within as $thing) {
				if (endsWith($thing, ".mp4")) {
					$return .= "<tr>";
					$return .= "<td><a href=\"video.php?video=" . $video_dir_url . "/" . $item . "/" . $thing . "\">" . $thing . "</a></td>";
					$return .= "</tr>";
				}
			}
		}
	}
	$return .= "</table>";
	return $return;
}

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

				#content a:link { color: #5555ff; text-decoration: none; }
				#content a:active { color: #5555ff; text-decoration: none; }
				#content a:visited { color: #5555ff; text-decoration: none; }
				#content a:hover { color: #2222ff; text-decoration: none; }

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
					// if the video url is defined, play that video
					// <p><a href="video.php">Return to video list</a></p>
					// echo $video_url;
					echo "<h3>" . str_replace($video_dir_url . "/", "", $video_url) . "</h3>";
					echo "<div id=\"video\">" . getVideoPlayer($video_url) . "</div>";
				} else {
					// if there is no video to play, show a list of possible videos
					echo "<h1>Videos</h1>";
					echo "<h4>Space remaining: " . getServerFreeSpace() . "</h4>";
					echo getVideoListAsTable($video_dir, $video_dir_url);
				}

				if (strpos($_SERVER['HTTP_USER_AGENT'], 'Linux') !== false) {
					echo "<p>";
					echo "HTML5 MP4 video playback can be enabled in Linux using Firefox with the addition of GStreamer. ";
					echo "Perhaps the easiest way to add this support in Ubuntu Linux (and variants) is to install ";
					echo "Ubuntu Restricted Extras <code>(sudo apt-get install ubuntu-restricted-extras)</code>. ";
					echo "</p>";
				}
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

