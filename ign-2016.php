<?php
$row = 183193 ;
$col = 259755 ;

$nbRow = 15 ; //15
$nbCol = 16 ; // 16

?>


<html>
<head>
<title>IGN 2016</title>

<style>
body {width: <?= $nbCol*256+500 ?>px}
</style>

</head>
<body>

<?php

for ($i = 0; $i < $nbRow ; $i++ ) {
	for ($j = 0 ; $j < $nbCol ; $j++) {
		//echo "IGN-2016-$i-$j" ;
		echo '<img src="http://wxs.ign.fr/j5tcdln4ya4xggpdu4j0f0cn/geoportail/wmts?SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0&LAYER=ORTHOIMAGERY.ORTHOPHOTOS&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX=19&TILEROW=' ;
		echo $row + $i ;
		echo '&TILECOL=' ;
		echo $col + $j ;
		echo '&FORMAT=image%2Fjpeg" />' ;
	}
	echo '<br />' ;
}

?>

<?php


$bigImage = imagecreatetruecolor(256 * $nbCol, 256 * $nbRow);
for ($i = 0; $i < $nbRow ; $i++ ) {
	for ($j = 0 ; $j < $nbCol ; $j++) {
		$index = $nbCol * $i +$j ;
		$imageName = 'wmts.jpg ' ;
		if ($index != 0) {
			$imageName = sprintf('wmts_%03d.jpg',$index+1);
		}

		$img = imagecreatefromjpeg('ign2016_files/'.$imageName);
		if(!$img){
			echo 'Failed' ; die;
		}
		imagecopy($bigImage, $img, 256 * $j, 256 * $i, 0, 0, 256, 256);
		imagedestroy($img);
	}
}
imagepng ($bigImage, 'bigImage.png');
imagedestroy($bigImage);


?>




<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script>
$(document).ready(function(){
	alert('Terminé ! Faites Ctrl+S pour sauvegarder la page.');
});
</script>



</body>
</html>