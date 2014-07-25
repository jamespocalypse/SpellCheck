<?php

/*
 * Author: James Robertson
 * Date: July 25, 2014
 * Description: Generic Word similiarity search suggestion class based on the Google algorithm for
 * "Did you mean". Python code available here: http://www.norvig.com/spell-correct.html
 */
ini_set('memory_limit','1000M');


/*
 * General Useage
$dictionary = new SpellCheck();
$dictionary->build('/Users/jrobertson/Desktop/animals.txt');
$dictionary->search("Hipposideros galeritus 9");
var_dump($dictionary);
*/


class SpellCheck 
{
    var $dictionary = array();                                  //Dictionary of words to scan
    var $alphabet = "abcdefghijklmnopqrstuvwxyz0123456789";	//Default Alphanumeric Characters
    var $delimeter = "\t";                                      //Default delimeter for tokenizing lines
    /**
     * Sets the instance faviable alphabet to be a user supplied string
     * @param String $alpha string containing the characters of the alphabet to be used to edit the search word
     * 
     */
    function setAlphabet($alpha)
    {
    	$this->alphabet = $alpha;
    }
    /**
     * Generic Get function to access the alphabet instance variable
     * @return String containing the alphabet instance variable to be used for query string modification
     */
    function getAlphabet()
    {
    	return $this->alphabet;
    }
    /**
     * Generic Get function to access the delimeter instance variable
     * @return char delimeter instance variable
     */
    function getDelimeter()
    {
        return $this->delimeter;
    }
    /**
     * Sets the delimter instance variable to be the character supplied
     * @param Char $d
     */
    function setDelimeteter($d)
    {
        $this->delimeter = $d;
    }
    
    /**
     * Builds the dictionary array containing all lowercase variations of input words as key and value as the original word
     * @param String $file path to file to read the dictionary into memory
     */
    function build($file)
    {
        $d = $this->getDelimeter();
        $contents = explode("\n",file_get_contents($file));
        foreach($contents as $line)
        {
            $row = explode("$d",trim($line));
            foreach($row as $token)
            {
                $this->add($token);
            }
        }
    }
    /**
     * Wrapper function to add a lowercase version of a word to the dictionary with the value as the original
     * @param String $word
     */
    function add($word)
    {
        $this->dictionary[strtolower($word)] = "$word";
    }
    /**
     * Generic get function to return the value of a word in the dictionary or false if it is not in the dictionary
     * @param String $word
     * @return String Value of the word in the dictionary
     * @return boolean false if word is not in the dictionary
     */
    function get($word)
    {
        if(array_key_exists($word, $this->dictionary))
        {
            return $this->dictionary[$word];
        }
        else{
            return false;
        }
    }
    /**
     * Checks if word exists in the dictionary 
     * @param String $word
     * @return boolean true if it is in list
     */
    function inList($word)
    {
        if(array_key_exists($word, $this->dictionary))
        {
            return true;
        }
        else{
            return false;
        }
    }
    /**
     * Wrapper function to perform all of the edit operations on a search word
     * @param String $word
     * @return Array of strings with the original word modified in different ways
     */
    function edits($word)
    {
    	$alphabet = $this->getAlphabet();
        return array_merge($this->deletes($word),
                $this->substitute($word,$alphabet),
                $this->insert($word,$alphabet),
                $this->transpose($word));
        
    }
    /**
     * Processes incoming string and deletes one character at every position in the word.
     * @param String $word
     * @return Array of Strings
     */
    function deletes($word)
    {
        $len = strlen($word);
        $edits = array();
        for($i=0; $i< $len; $i++)
        {
            $newWord = '';
            for($k=0; $k< $len; $k++)
            {
                if($i!=$k)
                {
                    $newWord.=$word[$k];
                }
            }
            $edits[] = $newWord;
        }
        return $edits;
    }
    /**
     * Function requires two parameters, instance variable of alphabet contains all of the characters which will
     * be subsituted at each position in a word.
     * @param String $word
     * @param String $alphabet
     * @return Array of strings
     */
    function substitute($word)
    {
        $alphabet = $this->getAlphabet();
        $word_len = strlen($word);
        $alpha_len = strlen($alphabet);
        $edits = array();
        for($i=0; $i< $word_len; $i++)
        {
            
            for($k=0; $k< $alpha_len; $k++)
            {
                $newWord = '';
                for($j=0; $j < $word_len; $j++)
                {
                    
                    if($i != $j)
                    {
                        $newWord.=$word[$j];
                    }
                    else{
                        $newWord.=$alphabet[$k];
                    }
                }
                $edits[] = $newWord;
            }
            
        }
        return $edits;      
    }
     /**
     * Function requires two parameters, instance variable of alphabet contains all of the characters which will
     * be inserted at each position in a word.
     * @param String $word
     * @param String $alphabet
     * @return Array of strings
     */
    function insert($word,$alphabet)
    {
        $word_len = strlen($word);
        $alpha_len = strlen($alphabet);
        $edits = array();
        for($i=0; $i< $word_len; $i++)
        {
            for($k=0; $k< $alpha_len; $k++)
            {
                $newWord = '';
                for($j=0; $j < $word_len; $j++)
                {
                    if($i == $j)
                    {
                        $newWord.=$alphabet[$k];
                    }
                    $newWord.=$word[$j];
 
                }
                $edits[] = $newWord;
            }
        }
        return $edits;        
    }
    /**
     * Processes incoming string and swaps character at every position in the word.
     * @param String $word
     * @return Array of Strings
     */
    function transpose($word)
    {
        $len = strlen($word);
        $word_len = $len-1;
        $edits = array();
        for($i=0; $i< $word_len; $i++)
        {
            $newWord = '';
            for($k=0; $k< $len; $k++)
            {
                if($i==$k)
                {
                    $newWord.=$word[$k+1].$word[$k];
                }
                elseif($k != $i+1){
                    $newWord.=$word[$k];
                }
                
            }
            $edits[] = $newWord;
        }
        return $edits;        
    }
    /**
     * Function accepts a word and checks if word or variation of a word if it exists in the dictionary
     * @param String $word
     * @return Array of Strings of matches
     */
    function search($word)
    {
        $word =strtolower($word);
        $candidates = array();
        if($this->inList($word))
        {
            return array($word);
        }
        else{
            $edits = $this->edits($word);     
            foreach($edits as $word)
            {
                if($this->inList($word))
                {
                    $candidates[$this->get($word)] = "";
                }
            }
            return $candidates;
        }
    }
}
