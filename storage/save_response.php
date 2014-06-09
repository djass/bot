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
		$base = mysql_connect ('localhost', 'root', 'toor');
		mysql_select_db ('response', $base) ;
		mysql_query("SET NAMES UTF8");
		$question_clean =$question['clean'];
		$question_en_clair = $question['clair']; 

		if(empty($procedure)){
			//echo "Sauvegarde error :".$question_en_clair;
			$sql = "INSERT INTO `bot_hermione`.`aiml` (`id`, `bot_id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`) 
				VALUES (NULL, '1', '', '$question_en_clair', '', 'Je n ai rien trouvé concernant ce problème, ni sur internet, ni dans mes connaissances, je vous conseille donc de consulter un humain ...', 'mode_resol_panne', 'from_internet');
				";  
			mysql_query ($sql) or die ('Erreur SQL !'.$sql.'<br />'.mysql_error());	 
			
			$sql = "INSERT INTO `bot_hermione`.`aiml` (`id`, `bot_id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`) 
				VALUES (NULL, '1', '', '$question_en_clair', '', 'Je n ai rien trouvé concernant ce problème, ni sur internet, ni dans mes connaissances, je vous conseille donc de consulter un humain ...', '', 'from_internet');
				";  
			mysql_query ($sql) or die ('Erreur SQL !'.$sql.'<br />'.mysql_error());	

			 
		}
		else
		{ 
			
			
			
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
				// en mode attente
				$sql = "INSERT INTO `bot_hermione`.`aiml` (`id`, `bot_id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`) 
				VALUES (NULL, '1', '', '$question_en_clair', '', '$template_init', 'mode_resol_panne', 'from_internet');
				";  
				mysql_query ($sql) or die ('Erreur SQL !'.$sql.'<br />'.mysql_error());	 

				// en mode resolution
				$sql = "INSERT INTO `bot_hermione`.`aiml` (`id`, `bot_id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`) 
				VALUES (NULL, '1', '', '$question_en_clair', '', '$template_init', '', 'from_internet');
				";  
				mysql_query ($sql) or die ('Erreur SQL !'.$sql.'<br />'.mysql_error());	

			}
	
			if(!is_null($template_still_not)){   
				$sql = "INSERT INTO `bot_hermione`.`aiml` (`id`, `bot_id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`) 
				VALUES (NULL, '1', '', 'toujours pas', '', '<condition>$template_still_not</condition>', '', 'from_internet');
				";  
				mysql_query ($sql) or die ('Erreur SQL !'.$sql.'<br />'.mysql_error());	 
				

				$sql = "INSERT INTO `bot_hermione`.`aiml` (`id`, `bot_id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`) 
				VALUES (NULL, '1', '', 'toujours pas', '', '<condition>$template_still_not</condition>', 'mode_resol_panne', 'from_internet');
				";  
				mysql_query ($sql) or die ('Erreur SQL !'.$sql.'<br />'.mysql_error());	
				
			}
			
		}
		mysql_close();
	}

	function get_procedure_from_microsoft($url){
		$procedure = array();
		
		if( is_object(file_get_html($url)) ){

			$html = file_get_html($url);

			if(is_object( current($html->find('a.link_expandAll')) ) ){				
				$a = current($html->find('a.link_expandAll'));
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
		}

		return $procedure;

	}  
	$useless_words = array("peux-","tu-","ai-","est-","a-","jai-","j-","le-","la-","un-","une-","mon-","ma-","mes-",
		"probleme-","souci-","avec-","ne-","d-","de-","des-","les-","avec-","m'aider","d'aide");

	$var = explode('|', $_GET['v']);
	$url = $var[1];
	$question_en_clair = str_replace("-"," ", $var[0]);
	$question_clean = str_replace($useless_words, array(""), $var[0]);
	$question = array("clair"=>$question_en_clair,"clean"=>$question_clean);
	//echo "<pre>";var_dump($procedure);echo "</pre>";
	//echo "(".$question['clair'].") ";
	if(filter_var($url, FILTER_VALIDATE_URL)){
		$procedure = get_procedure_from_microsoft($url);
		 
		if(empty($procedure)){
				echo "Je n'ai rien trouvé. Pouvez vous reformuler votre probleme s'il vous plaît? <br/>
				Peut etre trouverez vous une solution sur cette <a target='_blank' href='".$url."'>page</a>.";}
		else{
				echo "J'ai peut etre trouvé quelque chose pour vous. :)";			
		}
		save_procedure_in_aiml_form($question,$procedure);
	}
	else{
		echo "Oups! Je n arrive pas à acceder à internet ...";
	}

	
?>