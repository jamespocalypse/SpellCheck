<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
error_reporting(E_ALL);
include 'kmer_similarity.php';
ini_set('memory_limit','1000M');
//$k = new Kmer();
//$k->init('/Users/jrobertson/Desktop/animals.txt');


class Kmer extends kmer_similarity {
    var $entity_assoc = array();
    var $num_entity = 0;
    var $kmer_weights = array();
    var $kmer_freq = array();
    var $entity_list = array();
    var $kmer;
    var $kSize =2;
    

    function init($filename)
    {
        $this->kmer_weights = array_fill(0,$this->num_combinations,0);
        $this->kmer_freq = array_fill(0,$this->num_combinations,0);
        $contents = explode("\n",file_get_contents($filename));
        $size = $this->getkSize();
        $start = time();
        foreach($contents as $i => $line)
        {
            $line = trim($line);
            //echo "$line\t".($start-time())."\n";
            $start = time();
            $this->addEntity($i, $line);
            $line = strtolower($line);
            $kmers = $this->processLine($line);
            foreach($kmers as $k)
            {
               // echo "--$k--\n";
                if(strlen($k) != $size)
                {
                    continue;
                }
                $pos = $this->calc_position($k);
                $this->setFreq($pos, $this->getFreq($pos)+1);
                $this->setAssoc($pos, $this->getAssoc($pos).','.$i);
            }
        }
        $this->calc_word_weights();
    }
        /**
     * Method calculates the inverse document frequency to determine how common a word is in the list of instutiones
     * and decresses the weight of the word as its frequency increases.
     */

    function calc_word_weights()
    {
        $numDocs = count($this->entity_list);
        foreach($this->entity_assoc as $word => $insts)
        {
            $numInstWithWord = count(explode(",",$insts));
            $idf = log($numDocs/$numInstWithWord,10);
            $pos = $this->calc_position($word);
            $this->setWeight($pos, $idf); 
        }    
    }

    function setAssoc($pos,$entity)
    {
        $this->entity_assoc[$pos] = $entity;
    }
    function getAssoc($pos)
    {
        if(array_key_exists($pos, $this->entity_assoc))
        {
          return $this->entity_assoc[$pos];   
        }
        else{
            return '';
        }
       
    }
    function addEntity($pos,$entity)
    {
        $this->entity_list[$pos] = $entity;
    }
    function processLine($line)
    {
        $len = strlen($line);
        $kmers = array();
        $size = $this->getkSize();
        for($i=0; 
        $i< $len; $i++)
        {
            $kmers[] = substr($line,$i,$size);
        }
        return $kmers;
    }
    function setkSize($s)
    {
        $this->kSize = $s;
    }
    function getkSize()
    {
        return $this->kSize;
    }
    function getWeight($pos)
    {
        return $this->kmer_weights[$pos];
    }
    function setWeight($pos,$w)
    {
        $this->kmer_weights[$pos] = $w;
    }
    function getFreq($pos)
    {
        return $this->kmer_freq[$pos];
    }
    function setFreq($pos,$count)
    {
        $this->kmer_freq[$pos] = $count;
    }
}