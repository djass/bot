<?php 
	//ini_set('display_errors', 1);

	include 'simple_html_dom.php';

	function nettoyage($t){
		$t = str_replace('Ã©', 'é', $t);
		return $t;
	}

	function saut_apres_phrases($p){
		for ($i = 0; $i < strlen($p); $i++) 
		    if($p{$i} == ".")
		    	if(isset($p{$i+1}) && (ctype_upper($p{$i+1}) || ctype_upper($p{$i+2}))) $p{$i} = "~";
		$p = str_replace("~",".<br/>",$p);
		return $p;
	}

	function save_procedure_in_aiml_form($question,$procedure){
		
		$question_clean = $question['clean'];
		$question_en_clair = $question['clair'];
		// on se connecte à notre base
		$base = mysql_connect ('localhost', 'root', 'toor');
		mysql_select_db ('response', $base) ;
		mysql_query("SET NAMES UTF8");
		
		$aiml = array();

		$template_still_not =  "";
	    $template_init = "";
	    $template_close = "";

		foreach ($procedure as $key => $value) {

			if(array_key_exists($key+1, $procedure)) $next_key = $key+1; else $next_key = -1;

			if($key == 0){				
				if(!is_null($value['title']))$template_init = 'Pour résoudre votre souci, vous devriez '.strtolower($value['title']).'.<br/><br/>Pour ce se faire la procedure a suivre est la suivante:<br/>';
				$template_init .= nettoyage(addslashes($value['desc'])).'
				<br/><br/>Dites moi ci cela résout votre probleme.<think><set name="'.urlencode($question_clean).'">'.$next_key.'</set></think>';
			}
			else{ 
				$template_still_not .= '
					<li name="'.urlencode($question_clean).'" value="'.$key.'">
						Ok. J ai peut etre une autre solution pour vous :<br/>
						'.nettoyage(addslashes($value['title'])).'<br/><br/>
						'.nettoyage(addslashes($value['desc'])).'
						Essayez cela, si vous avez encore des soucis dites moi.
	                    <br/><br/>Dites moi ci cela résout votre probleme.<think><set name="'.urlencode($question_clean).'">'.$next_key.'</set></think>
	                </li>
	            ';

	            if($next_key == -1)
	            	$template_still_not .= '
					<li name="'.urlencode($question_clean).'" value="'.$next_key.'">
						Je suis à court d idée...contactez le fabricant de votre ordinateur ou l’assistance technique pour obtenir une assistance.
            			<think><set name="topic">mode_attente</set></think>
	                </li>
	            ';
			}
		}

		
		if(!is_null($template_init)){ 
			// lancement de la requete
			$sql = "INSERT INTO `bot_hermione`.`aiml` (`id`, `bot_id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`) 
			VALUES (NULL, '1', '', '$question_en_clair', '', '$template_init', 'mode_resol_panne', 'from_internet');
			";  
			mysql_query ($sql) or die ('Erreur SQL !'.$sql.'<br />'.mysql_error());	mysql_close();
		}

		if(!is_null($template_init)){ 
			$base = mysql_connect ('localhost', 'root', 'toor');
			mysql_select_db ('bot_hermione', $base) ;
			// lancement de la requete
			$sql = "INSERT INTO `bot_hermione`.`aiml` (`id`, `bot_id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`) 
			VALUES (NULL, '1', '', 'toujours pas', '', '<condition>$template_still_not</condition>', 'mode_resol_panne', 'from_internet');
			";  
			mysql_query ($sql) or die ('Erreur SQL !'.$sql.'<br />'.mysql_error());	mysql_close();
		}
	}

	function get_procedure_from_microsoft($url){
		$procedure = array();
		$html = file_get_html($url);
		$a = current($html->find('a.link_expandAll'));
		 
		if(is_object($a)){
			foreach($html->find('h4.title_section') as $article) {  
			    $item['title']     = $article->plaintext;
			   	$item['desc']  = null;
			    $procedure[] = $item;
			} 
			
			$i = -1;
			foreach($html->find('div.collapse') as $article) {  
			    $i++;
			    $procedure[$i]['desc'] = saut_apres_phrases($article->plaintext);
			    
			   
			}  
		}else{
			// Find all article blocks
			//echo count($html->find('div.procedure'));
			foreach($html->find('div.procedure') as $article) {
				if($article->find('ol.ordered_dec', 0)->plaintext != null){
					$item['title']     = $article->find('h3.title_procedure', 0)->plaintext;
				    $item['desc']     = saut_apres_phrases($article->find('ol.ordered_dec', 0)->plaintext);
				    $procedure[] = $item;
				}  					
				    
				 
			}
		}

		return $procedure;

	}  
	$useless_words = array("ai-","est-","a-","j-ai-","j-","le-","la-","un-",-"une-","mon-","ma-",
		"probleme-","souci-","avec-","ne-","d-");

	$var = explode('|', $_GET['v']);
	$url = $var[1];
	$question_en_clair = str_replace("-"," ", $var[0]);
	$question_clean = str_replace($useless_words, array(""), $var[0]);
	$question = array("clair"=>$question_en_clair,"clean"=>$question_clean);
	$procedure = get_procedure_from_microsoft($url);
	//echo "<pre>";var_dump($procedure);echo "</pre>";
	//echo "(".$question['clair'].") ";
	if(empty($procedure)){
			echo "Je n'ai rien trouvé. Pouvez vous reformuler votre probleme s'il vous plaît?";}
	else{
			echo "J'ai terminé mes petites recherches :)";
			save_procedure_in_aiml_form($question,$procedure);
	}
	
?>