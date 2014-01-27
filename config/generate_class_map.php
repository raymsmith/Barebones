<?php
$basePath = explode("/",__DIR__);
array_pop($basePath);
$basePath = implode("/",$basePath)."/";

$baseDir = array(
	"controllers/",
	"models/",
	"utilities/"
);
// echo "Generating class map\n";
$classMap = array();
foreach($baseDir as $directory){
	$directory = $basePath.$directory;
	// echo "Reading files for directory: ". $directory . "\n";
	$files = `find $directory -type f -name "*.class.php"`;
	$files = explode("\n", $files);
	foreach ($files as $filepath) {
		if(trim($filepath)==""){
			continue;
		}
		$className = current(explode('.',end(explode('/', $filepath))));
		$classMap[$className] = str_replace($basePath, "", $filepath);
	}
}
$CLASSMAP = "<?php\n\$CLASSMAP=array(\n";
foreach ($classMap as $className => $path) {
	$CLASSMAP.="\"".$className."\"=>\"".$path."\",\n";
}
$CLASSMAP = substr($CLASSMAP,0,-2);
$CLASSMAP.=");\n?>";
// echo "Writing map file\n";
file_put_contents(__DIR__."/".'classmap.php', $CLASSMAP);
// die("Done\n");
?>