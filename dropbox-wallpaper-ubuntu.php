<?php
# Gets a random photo from a shared Drobox folder, and sets it as the wallpaper
# 
# Usage:
# 	
# 	wget -qO- https://raw.githubusercontent.com/deanrather/dropbox-wallpaper-ubuntu/master/dropbox-wallpaper-ubuntu.php | php -- <url to public folder>
# 
# Crontab installation (each 10 minutes)
# 
# 	crontab -e
# 	*/10 * * * * <above command>
# 	

$URL = $argv[1];
echo "Getting Photo List from: $URL\n";
$html = file_get_contents($URL);
if(!$html) { echo "Failed :(\n"; exit(1); }

$json = explode("(function (dropbox) { var SharingModel = dropbox.SharingModel;SharingModel.init_folder(true, true, ", $html);
$json = $json[1];
$json = explode(") }(dropbox));", $json);
$json = $json[0];
$json = json_decode($json);
if(!$json) { echo "Failed :(\n"; exit(2); }

$photoIndex = rand(0, sizeof($json)-1);
$photoURL = $json[$photoIndex]->dl_url;
if(!$json) { echo "Failed :(\n"; exit(3); }

$path = $_SERVER['HOME'] . '/.wallpapers/' . basename($photoURL);
$path = explode('?', $path);
$path = $path[0];
$path = urldecode($path);

if(!file_exists($path))
{
	echo "Getting Photo from: $photoURL\n";
	exec("mkdir -p ~/.wallpapers", $output, $returnCode);
	if($returnCode !== 0) { echo "Failed :(\n"; exit(4); }
	exec("wget -qO '$path' '$photoURL'", $output, $returnCode);
	if($returnCode !== 0) { echo "Failed :(\n"; exit(5); }
}

echo "Setting wallpaper to: $path\n";
exec("export DISPLAY=:0; gsettings set org.gnome.desktop.background picture-uri 'file:///$path'", $output, $returnCode);
if($returnCode !== 0) { echo "Failed :(\n"; exit(6); }
