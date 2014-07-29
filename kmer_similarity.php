<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of kmer_similarity
 *
 * @author jrobertson
 */

class kmer_similarity 
{
    var $alphabet = "abcdefghijklmnopqrstuvwxyz123456789.() ";
    var $size = 3;
    var $counts = array();
    var $num_combinations = 0;
    
    function __construct() 
    {
        $this->num_combinations();
        $this->initCounts();
    }
    function getWord($pos)
    {
        $len = strlen($this->getAlphabet());
        $alphabet = $this->getAlphabet();
        $size = $this->size;
        $word='';  
        for($i=0; $i < $size; $i++)
        {
            $r = $pos % $len;
            $word.=$alphabet[$r];
            $pos = intval($pos/$len);
        }
        return strrev($word);
    }
    function calc_position($word)
    {
        $len = strlen($word);
        $pos = array();
        $alphabet = $this->getAlphabet();
        $alphaSize = strlen($alphabet);
        $size = $this->size-1;
        for($i=0; $i < $len; $i++)
        {
            $char = $word[$i];
            $pos[] = strpos($alphabet,$char);
        }
        $value = $pos[0]* pow($alphaSize,$size);
        $count = count($pos);
        for($i=1; $i<$count; $i++)
        {
            $size--;
            $value = $value + $pos[$i]* pow($alphaSize,$size);
        }
        return $value;
    }
    function calcArraySize()
    {
        $this->num_combinations = pow(strlen($this->getAlphabet()),$this->getSize());
    }
    function initCounts()
    {
        $this->counts = array_fill(0, $this->num_combinations, 0);
    }
    function setAlphabet($alpha)
    {
        $this->alphabet = $alpha;
    }
    function getAlphabet()
    {
        return $this->alphabet;
    }
    function getSize()
    {
        return $this->size;
    }
    function setSize($s)
    {
        $this->size = $s;
    }
    function setKcount($word,$count)
    {
        $pos = $this->calc_position($word);
        $this->dictionary[$pos] = $count;
    }
    function getKcount($word)
    {
        return $this->counts[$pos];
    }   
 
}
