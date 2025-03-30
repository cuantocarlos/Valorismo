<?php
class cUpload_file {
	var $cusers;
	var $gene;
	var $sqli;
	function cUpload_file($cusers, $gene, $sqli) {
	  $this->cusers = $cusers;
	  $this->gene = $gene;
	  $this->sqli = $sqli;
	}
	function UploadImage($var, $width_e, $height_e, $target_path, $table, $field, $where, $string){
		ini_set("memory_limit","60M");
		/* RECUERDA SUBIR EL MAX_FILE_UPLOAD A 3MB desde el php.ini*/
		$errori=0;
		if(isset($_FILES[$var]['name']) && $_FILES[$var]['name']!=""){
			$newname=$string;

			$ext = pathinfo($_FILES[$var]['name'], PATHINFO_EXTENSION);
			$ext=".".$ext;
			$todel = $target_path;

			//Comprobacion de extension, tamaño
			$safeExtensions = array(
				'.gif',
				'.jpg',
				'.jpeg',
				'.png',
				'.GIF',
				'.JPG',
				'.JPEG',
				'.PNG'
			);

			if (!in_array($ext, $safeExtensions)) {
				$errori=1;	
			}


			if(($_FILES[$var]["size"] )> 3072000){
				$errori=2;
			}
			if($errori==0){
				list($width, $height, $type) = getimagesize($_FILES[$var]['tmp_name']);
				if($width> 3300 || $height> 3300 ){
					$errori=3;
				}
				$newname=$newname. $ext;
				$target_pathorig = $target_path;
				$target_path = $target_path . $newname;
				
				if($errori==0){
						if(move_uploaded_file($_FILES[$var]['tmp_name'], $target_path)) {
							include_once("resize-class.php");

							// *** 1) Initialise / load image

							$resizeObj = new resize($target_path);
							// *** 2) Resize image (options: exact, portrait, landscape, auto, crop)

							$resizeObj -> resizeImage($width_e, $height_e, 'crop');
							
							// *** 3) Save image
							$resizeObj -> saveImage($target_path, 100);

							//Save foto
							$toupt=Array($field => $newname);
							$olddata = $this->sqli->runSelect($table, $where, $field);
							$this->sqli->runUpdate($table, $toupt, $where);
							if(isset($olddata[0][$field]) && file_exists($target_pathorig."/".$olddata[0][$field]) && $olddata[0][$field] != "thumb1.png"){
								unlink($target_pathorig."/".$olddata[0][$field]);
							}
							$imagenc=$newname;
							
						}else{
							$errori=4;
						}
				}
			}
		}else{
			$errori=5;
		}
		return $errori;
	}
	
	function UploadImageShield($var, $caract, $option, $value){
		ini_set("memory_limit","60M");
		/* RECUERDA SUBIR EL MAX_FILE_UPLOAD A 3MB desde el php.ini*/
		$errori=0;
		if(isset($_FILES[$var]['name']) && $_FILES[$var]['name']!=""){
			$newname=$value;

			$ext = pathinfo($_FILES[$var]['name'], PATHINFO_EXTENSION);
			$ext=".".$ext;
			//$todel = $target_path;

			//Comprobacion de extension, tamaño
			$safeExtensions = array(
				'.gif',
				'.jpg',
				'.jpeg',
				'.png',
				'.GIF',
				'.JPG',
				'.JPEG',
				'.PNG'
			);

			if (!in_array($ext, $safeExtensions)) {
				$errori=1;	
			}


			if(($_FILES[$var]["size"] )> 3072000){
				$errori=2;
			}
			if($errori==0){
				list($width, $height, $type) = getimagesize($_FILES[$var]['tmp_name']);
				if($width> 3300 || $height> 3300 ){
					$errori=3;
				}
				$newname=$newname. $ext;
					$target_path = $caract[0][0];
					$width_e = $caract[0][1];
					$height_e = $caract[0][2];
					$target_path2 = $caract[1][0];
					$width_e2 = $caract[1][1];
					$height_e2 = $caract[1][2];

				
					$target_path = $target_path . $newname;
					$target_path2 = $target_path2 . $newname;
					if($errori==0){
							if(copy($_FILES[$var]['tmp_name'], $target_path) && move_uploaded_file($_FILES[$var]['tmp_name'], $target_path2)) {
								include_once("resize-class.php");

								// *** 1) Initialise / load image
								
								$resizeObj = new resize($target_path);
								$resizeObj2 = new resize($target_path2);
								// *** 2) Resize image (options: exact, portrait, landscape, auto, crop)

								$resizeObj -> resizeImage($width_e, $height_e, 'crop');
								$resizeObj2 -> resizeImage($width_e2, $height_e2, 'crop');
								
								// *** 3) Save image
								$resizeObj -> saveImage($target_path, 100);
								$resizeObj2 -> saveImage($target_path2, 100);

								if($option > 0){
									//Save foto
									$toupt=Array("value" => $newname);
									//check si es insert o update
									$rows = $this->cusers->dbc->runSelect("clients_options", "id_client = '".$this->cusers->club_id."' AND id_option = '".$option."'");
									if(count($rows)==1){
										//UPDATEA
										$this->cusers->dbc->runUpdate("clients_options", $toupt, "id_client = '".$this->cusers->club_id."' AND id_option = '".$option."'");
									}else{
										//INSERT
										$toupt["id_client"] = $this->cusers->club_id;
										$toupt["id_option"] = $option;
										$this->cusers->dbc->runInsert("clients_options", $toupt);
									}
								}
								
							}else{
								$errori=4;
							}
							
					}
			}
		}else{
			$errori=5;
		}
		return $errori;
	}
	
	function UploadXls($var, $target_path, $string){
		ini_set("memory_limit","60M");
		/* RECUERDA SUBIR EL MAX_FILE_UPLOAD A 3MB desde el php.ini*/
		$errori=0;
		if(isset($_FILES[$var]['name']) && $_FILES[$var]['name']!=""){
			$newname=$string;

			$ext = pathinfo($_FILES[$var]['name'], PATHINFO_EXTENSION);
			$ext=".".$ext;
			$todel = $target_path;

			//Comprobacion de extension, tamaño
			$safeExtensions = array(
				'.xls'
			);

			if (!in_array($ext, $safeExtensions)) {
				$errori=1;	
			}


			if(($_FILES[$var]["size"] )> 3072000){
				$errori=2;
			}
			if($errori==0){
				$newname=$newname.$ext;
				$target_path = $target_path . $newname;
				if($errori==0){
						if(move_uploaded_file($_FILES[$var]['tmp_name'], $target_path)) {
							/*SUBIDA*/
						}else{
							$errori=4;
						}
				}
			}
		}else{
			$errori=5;
		}
		return $errori;
	}
	function UploadArchivo($var, $target_path, $string){
		ini_set("memory_limit","60M");
		/* RECUERDA SUBIR EL MAX_FILE_UPLOAD A 3MB desde el php.ini*/
		$errori=0;
		if(isset($_FILES[$var]['name']) && $_FILES[$var]['name']!=""){
			$newname=$string;

			$ext = pathinfo($_FILES[$var]['name'], PATHINFO_EXTENSION);
			$ext=".".$ext;
			$todel = $target_path;

			//Comprobacion de extension, tamaño
			$safeExtensions = array(
				'.doc',
				'.docx',
				'.odt',
				'.pdf',
				'.txt'
			);

			if (!in_array($ext, $safeExtensions)) {
				$errori=1;	
			}


			if(($_FILES[$var]["size"] )> 3072000){
				$errori=2;
			}
			if($errori==0){
				$newname=$newname.$ext;
				$target_path = $target_path . $newname;
				if($errori==0){
					if(move_uploaded_file($_FILES[$var]['tmp_name'], $target_path)) {
						/*SUBIDA*/
					}else{
						$errori=4;
					}
				}
			}
		}else{
			$errori=5;
		}
		$arr["errori"] = $errori;
		$arr["filename"] = $newname;
		return $arr;
	}
}
?>