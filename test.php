<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include 'KmerTdf_Idf.php';
$k = new Kmer();
$k->init('/Users/jrobertson/Desktop/temp.txt');
var_dump($k->kmer_weights);