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

$json = explode("(function (dropbox) { var SharingModel = dropbox.SharingModel;SharingModel.init_folder(true, true, ", $html);
$json = $json[1];
$json = explode(") }(dropbox));", $json);
$json = $json[0];
$json = json_decode($json);

$photoIndex = rand(0, sizeof($json)-1);
$photoURL = $json[$photoIndex]->dl_url;

$path = $_SERVER['HOME'] . '/.wallpapers/' . basename($photoURL);
$path = explode('?', $path);
$path = $path[0];
$path = urldecode($path);

if(!file_exists($path))
{
	echo "Getting Photo from: $photoURL\n";
	exec("mkdir -p ~/.wallpapers");
	exec("wget -qO '$path' '$photoURL'");
}

echo "Setting wallpaper to: $path\n";
exec("gsettings set org.gnome.desktop.background picture-uri 'file:///$path'");
