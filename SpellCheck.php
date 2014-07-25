<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
ini_set('memory_limit','1000M');

$dictionary = new SpellCheck();
$prev = time();
$dictionary->build('/Users/jrobertson/Desktop/animals.txt');
echo (time()-$prev)."\n";
$prev = time();
var_dump($dictionary->search("Hipposideros galeritus 9","abcdefghijklmnopqrstuvwxyz0123456789"));
echo (time()-$prev)."\n";
//var_dump($dictionary);
/**
 * Description of SpellCheck
 *
 * @author jrobertson
 */

class SpellCheck 
{
    var $dictionary = array();
    function build($file)
    {
        $contents = explode("\n",file_get_contents($file));
        foreach($contents as $line)
        {
            $row = explode("|",$line);
            foreach($row as $token)
            {
                $this->add($token);
            }
        }
    }
    function add($word)
    {
        $this->dictionary[strtolower($word)] = "$word";
    }
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
    function edits($word,$alphabet)
    {
        return array_merge($this->deletes($word),
                $this->substitute($word,$alphabet),
                $this->insert($word,$alphabet),
                $this->transpose($word));
        
    }
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
    function substitute($word,$alphabet)
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
    function search($word,$alphabet)
    {
        $word =strtolower($word);
        $candidates = array();
        if($this->inList($word))
        {
            return array($word);
        }
        else{
            $edits = $this->edits($word, $alphabet);
            
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
