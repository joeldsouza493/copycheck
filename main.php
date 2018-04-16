<?php

	include('./php-nlp-tools/autoloader.php');
	use \NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;
	use \NlpTools\Similarity\JaccardIndex;
	use \NlpTools\Similarity\CosineSimilarity;
	use \NlpTools\Similarity\Simhash;

	$directory = "./";
	$filedest = "./Files/";
	if(!file_exists($filedest))
      	mkdir($filedest, 0777, true);
	$folder = "/temp";
	traverseAllCFiles($directory);
	// compareTokenFiles();

	function strbefore($directory, $string, $substring){
		$offset = strlen($directory);
		$pos = strpos($string, $substring);
		if($pos === false){
			die("Error");
		}
		else{
			return(substr($string, $offset, $pos - $offset));
		}
	}

	function traverseAllCFiles($directory){
		global $filedest, $folder;
		$directory = $directory;
		if(file_exists($directory)){
   			foreach(glob($directory . '*' . ".tar.gz", GLOB_BRACE) as $file ){
      			$folder = strbefore($directory, $file, ".tar.gz");
    			if(!file_exists($filedest.$folder))
      				mkdir($filedest.$folder, 0777, true);
      			if(!file_exists($directory.$folder))
      				mkdir($directory.$folder, 0777, true);
      			try {
      				$phar = new PharData($file);
      				$phar->extractTo($directory.$folder, null, true);
      			}catch (Exception $e) {
      				error_log("Couldnt extract $file");
      			}
      			echo "Extracted $folder ";
      			traverseFolder($directory.$folder);
   			}
   		}
	}

	function traverseFolder($dir){
		echo "traversing $dir ";
		foreach(glob($dir.'/*.{java, c, cpp, js, css, htm, html, py}', GLOB_BRACE) as $file){
      		normaliseFile($file);
      	}
		if($handle = opendir($dir)){
		    while (false !== ($file = readdir($handle))){
		        if('.' === $file) continue;
		        elseif('..' === $file) continue;
		        elseif(is_dir($file))
		        	traverseFolder($dir."/".$file);
		    }
		}
	    closedir($handle);
		deleteFolder($dir);
	}

	function storeTokenFile($arr, $name){
		global $filedest, $folder;
		if(!file_exists($filedest.$folder)){
      		mkdir($filedest.$folder, 0777, true);
		}
		file_put_contents($filedest.$folder."/".$name.".json", json_encode($arr));
	}

	function normaliseFile($file){
		global $directory;
		echo "Hello";
		$extension = end(explode(".", $file));
		$filename = "";
		$file_contents = file_get_contents($file);
		$nfile = "";
		if($extension != "html" && $extension!= "py" && $extension != "htm") {
			$pattern = "/\/\\*[\\s\\S]*?\\*\//";
			$replacement = " ";
			$nfile = preg_replace($pattern, $replacement, $file_contents);
			strbefore($directory, $file, ".".$extension);
		}
		elseif($extension == "htm" || $extension == "html") {
			$pattern = "/<!--(.|\s)*?-->/";
			$replacement = " ";
			$nfile = preg_replace($pattern, $replacement, $file_contents);
		}
		else{

		}
		echo $nfile;
		$tok = new WhitespaceAndPunctuationTokenizer();
		$token_file = $tok->tokenize($nfile);
		$name = strbefore($directory, $file, ".".$extension);
		echo $name;
		$name = str_replace("/", "_", $name);
		storeTokenFile($token_file, $name);
	}

	function compareTokenFiles(){
		global $filedest;
		$J = new JaccardIndex();
		$cos = new CosineSimilarity();
		$simhash = new Simhash(16);
		if ($handle = opendir($filedest)) {
    		while (false !== ($sfold = readdir($handle))) {
        		if('.' === $sfold) continue;
        		if('..' === $sfold) continue;
        		$sfold = $filedest."/".$sfold;
        		if(is_dir($sfold)) {
        			if($shandle = opendir($sfold)){
        				while (false !== ($sfile = readdir($shandle))) {
        					if('.' === $sfile) continue;
        					if('..' === $sfile) continue;
        					$sfile = $sfold."/".$sfile;
        					$setA = json_decode(file_get_contents($sfile), true); 
        					while(false !== ($dfold = readdir($handle))){
        						if('.' === $dfold) continue;
        						if('..' === $dfold) continue;
        						if($dfold === $sfold) continue;
        						$dfold = $filedest."/".$dfold;
        						if(is_dir($dfold)){
        							if($dhandle = opendir($dfold)){
        								while (false !== ($dfile = readdir($dhandle))) {
        									if('.' === $dfile) continue;
        									if('..' === $dfile) continue;

        									$dfile = $dfold."/".$dfile;
        									$setB = json_decode(file_get_contents($sfile), true);

        									echo "Source is $sfile and dest is $dfile------>";
        									echo "Jaccard: ".$J->similarity($setA, $setB)." ";
        									echo "Cosine: ".$cos->similarity($setA, $setB)." ";
        									echo "Simhash Sim: ".$simhash->similarity($setA, $setB)." ";
        									echo "SimA: ".$simhash->simhash($setA)." ";
        									echo "SimB: ".$simhash->simhash($setB)." ";
        								}
        							}
        							closedir($dhandle);
        						}
        					}
        				}
        			}
        			closedir($shandle);
        		}
        	}
    	}
    	closedir($handle);
	}

	function deleteFolder($dir){
		if (is_dir($dir)) {
    		$objects = scandir($dir);
	     	foreach ($objects as $object) {
	       		if ($object != "." && $object != "..") {
	         		if (is_dir($dir."/".$object))
	           			deleteFolder($dir."/".$object);
	         		else
	           			unlink($dir."/".$object);
	       		}
	     	}
	     	rmdir($dir);
	     	echo "deleted $dir ";
   		}
   	}

?>
