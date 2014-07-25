<?php
/*
 * Author: James Robertson
 * Date: July 25, 2014
 * Description: Similarity searching based on kwords and word weighting based on TF-IDF information retrieval scheme
 */

/*
$fileName = '/Users/jrobertson/Desktop/instlibrary.txt';
$entity = 'Biodiversity Institute';
$list = new TD-IF_SimilaritySearch($fileName);
$matches = $list->search($entity);
$matches = $list->getBestMatches($matches,10);
$matches =$list->normalize_matches($entity,$matches);
*/


class TF_IDF_SimilaritySearch
{
var $entity_lookup = array();	
var $entity_array = array();
var $numberOfInst = 0;
var $word_search_array = array();

function __construct($fileName) 
{
    $this->set_entity_list ($fileName);
    $this->word_search_array['WordFrequency'] = array();
    $this->word_search_array['WordInstitutions'] = array();
    $this->word_search_array['WordWeight'] = array();
    $this->init_search_list();
    $this->calc_word_weights();
}
/**
 * 
 * @param String $inst entity to look up
 * @return boolean true if in list
 */

function inList($inst)
{
	if(array_key_exists($inst,$this->entity_lookup))
	{
		return true;
	}
	else{
		return false;	
	}
}

/*Method accepts a file name with the full path and sets the contents of the file to be
 * a numerically indexed array of entities
 * @param String $fileName file name location of the file to set as the contents of the entity array
 * @return nothing
 * 
 */

function set_entity_list ($fileName)
{
    $this->entity_array = explode("\n",file_get_contents($fileName));
    $this->numberOfInst = count($this->entity_array);
	foreach($this->entity_array as $inst)
	{
		$this->entity_lookup[trim($inst)] = "";
	}
}

/*
 * Method accepts an integer index for an entity and if the key exists, then the
 * function returns the name of the entity or false if it doesnt
 * @param integer $num Numeric index of the array to return
 * @return string or boolean
 */

function get_entity_by_line_number ($num)
{
    if(array_key_exists($num, $this->entity_array))
    {
        return $this->entity_array[$num];
    }
    else{
        return false;
    }   
}

/**
 * Method returns the count of the number of entitys in the array
 * @return integer number of elements in the array
 */
function get_inst_count()
{
    return $this->numberOfInst;
}

/**
 * Method returns the count of a word within the list of entitys or returns 0 if the word is not present in the array
 * @param type String $word to search the array
 * @return type integer count of the number of occurances of the word in the array
 */
function get_word_freq($word)
{
    if(array_key_exists($word, $this->word_search_array['WordFrequency']))
    {
        return $this->word_search_array['WordFrequency'][$word];
    }
    else{
        return 0;
    }
}

/**
 * Method accepts a word and an integer and sets the array to have a key of the word and the count of that word
 * @param type String $word in question to set the count of it in the document
 * @param type Integer $count number of occurances of the word in the document
 * @return null
 */

function set_word_freq($word,$count)
{
    $this->word_search_array['WordFrequency'][$word] = $count;
}

/**
 * Method accepts a word to search the array and if found it returns the string list of entitys associated with that word
 * @param type $word
 * @return string containing the entitys found or empty string
 */

function get_word_entity_association($word)
{
    if(array_key_exists($word, $this->word_search_array['WordInstitutions']))
    {
        return $this->word_search_array['WordInstitutions'][$word];
    }
    else{
        return "";
    }    
}

/**
 * Method accepts a word and a sting list of entitys
 * @param $word String to use as the key in the array
 * @param $list String containing the CSV list of entitys
 */
function set_word_entity_association($word,$list)
{
    $this->word_search_array['WordInstitutions'][$word] = $list;
}

/**
 * Method is a wrapper function that processes each line of the entity list and calculate their frequency and entity
 * Association
 */
function init_search_list()
{
$element_count = $this->get_inst_count();
for($i =0; $i < $element_count; $i++)
{
    $inst = preg_replace("/\,|\n|\n\r/","",$this->get_entity_by_line_number($i));
    if(strlen($inst) == 0)
    {
        continue;
    }    
    $tok = strtok($inst, " ");
	$tok = strtolower ($tok);
    while($tok != false)
    {
        if(strlen($tok) == 0)
        {
            continue;
        }
		$tok = strtolower ($tok);
        $frequency = $this->get_word_freq($tok);
        $this->set_word_freq($tok,++$frequency);
        $list = $i.','.$this->get_word_entity_association($tok);
        $this->set_word_entity_association($tok,$list);   
        //Get next token
        $tok = strtok(" ");
    }    
}
}

/**
 * Method calculates the inverse document frequency to determine how common a word is in the list of instutiones
 * and decresses the weight of the word as its frequency increases.
 */

function calc_word_weights()
{
$numDocs = $this->get_inst_count();
foreach($this->word_search_array['WordInstitutions'] as $word => $insts)
{
    $numInstWithWord = count(explode(",",$insts));
    $idf = log($numDocs/$numInstWithWord,10);
    $this->word_search_array['WordWeight'][$word] = $idf; 
}    
    
}

/**
 * Method accepts a word and then returns the numerical value for the weight that word should have in similarity calculations
 * @param type String word to search the index 
 * @return float
 */


function get_word_weight($word)
{
    if(array_key_exists($word, $this->word_search_array['WordWeight']))
    {
        return $this->word_search_array['WordWeight'][$word];
    }
    else{
        return 0;
    }
}

/***
 * Method accepts a string to search the index for similar looking entitys
 * @param String $entitys Search string of an entity to search the index for
 * @return array of entitys with at least one word in common
 */
function search($entity)
{
$entity = preg_replace("/\,|\n|\n\r/","",$entity);
$tok = strtok($entity, " ");
$inst_list = array();   //Array containing the entity and score for that entity
$tok = strtolower ($tok);
while($tok != false)
{
    if(strlen($tok) == 0)
    {
        continue;
    }
	$tok = strtolower ($tok);
    $list = explode(',',$this->get_word_entity_association($tok));
	
    foreach($list as $i)
    {
        if(array_key_exists($i, $inst_list))
        {
            $inst_list[$i] = $inst_list[$i] + $this->get_word_weight($tok);
        }
        else{
            $inst_list[$i] = $this->get_word_weight($tok);
        }
                
    }    
    $tok = strtok(" ");
}
return $inst_list;
}

/**
 * Method accepts an array list of isntitutions and returns a sub array of the top hits
 * @param Array $inst_list with entitys as key and scores as value (higher score is better)
 * @param Int $num_match_to_return used to determine how many matches the method returns
 * @return Array sub array of $num_match_to_return size of entitys
 */

function getBestMatches($inst_list,$num_match_to_return)
{
arsort($inst_list);
$i =0;
$top_matches = array();
foreach($inst_list as $inst => $score)
{
    if(strlen($this->get_entity_by_line_number($inst)) < 3)
    {
        continue;
    }
    $i++;
    $top_matches[$this->get_entity_by_line_number($inst)] = $score;
    if($i == $num_match_to_return)
    {
        break;
    }    
    
}
return $top_matches;
}

/**
 * Method Normalizes matches using levenshtein distance
 * @param String $query_string
 * @param String $match_array
 * @return Array of Strings with Entity as Key and Score as Value
 */

function normalize_matches($query_string,$match_array)
{
    foreach($match_array as $match => $score )
    {
        $score = $score/(1+levenshtein ( $query_string, $match));
        $match_array[$match] = $score;
    }
    arsort($match_array);
    return $match_array;
} 




}

       



?>
