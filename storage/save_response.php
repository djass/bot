<?php 
	ini_set('display_errors', 1);

	include 'simple_html_dom.php';


	function save_procedure_in_aiml_form($question,$procedure){
		// on se connecte à notre base
		$base = mysql_connect ('localhost', 'root', 'toor');
		mysql_select_db ('response', $base) ;
		
		$aiml = array();

		$template_still_not =  null;
	    $template_init = null;
	    $template_close = null;

		foreach ($procedure as $key => $value) {

			if(array_key_exists($key+1, $procedure)) $next_key = $key+1; else $next_key = null;

			if($key == 0){				
				$template_init = 'Pour résoudre votre souci, j ai quelque chose a vous proposer.
				Vous devriez '.$value['title'].'
				=> '.$value['desc'].'
				<think><set name="'.urlencode($question).'">'.$next_key.'</set></think>';
			}
			else{ 
				$template_still_not .= '
					<li name="'.urlencode($question).'" value="'.$key.'">
						'.$value['desc'].'
	                    <think><set name="'.urlencode($question).'">'.$next_key.'</set></think>
	                </li>
	            ';
			}
		}

		
		if(!is_null($template_init)){ 
			// lancement de la requete
			$sql = "INSERT INTO `bot_hermione`.`aiml` (`id`, `bot_id`, `aiml`, `pattern`, `thatpattern`, `template`, `topic`, `filename`) 
			VALUES (NULL, '1', '', '$question', '', '$template_init', 'mode_resol_panne', 'from_internet');
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
		$html = file_get_html($url);

		// Find all article blocks
		echo count($html->find('div.procedure'))." procedures<br/>";
		foreach($html->find('div.procedure') as $article) {  
		    $item['title']     = $article->find('h3.title_procedure', 0)->plaintext;
		    $item['desc']     = $article->find('ol.ordered_dec', 0)->plaintext; 
		    $procedure[] = $item;
		}
		return $procedure;
	} 

	$var = explode('|', $_GET['v']);
	$url = $var[1];
	$question = $var[0];
	 

	$procedure = get_procedure_from_microsoft($url);
	 
	echo "<pre>";
		print_r($procedure);
	echo "</pre>";
	save_procedure_in_aiml_form($question,$procedure);
	
?>