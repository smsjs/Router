<?php

namespace Karadocteur\Router;

/**
 * Class RoutesCollection
 * Liste toutes les routes de l'application comme un tableau d'objets
 */
class RoutesCollection implements \Iterator, \Countable, \ArrayAccess {



    /*
     * @var array Tableau d'objets
     */
    protected $collection = [];
    
    
    
    /*
     * Compter le nombre d'objects dans $this->collection
     * @return int
     */
    public function count() {
        return count($this->collection);
    }
    
    
    
    /*
     * Retourne l'élément courant du tableau
     * @return Object
     */
    public function current() {
        return current($this->collection); 
    }
    
    
    
    /**
     * Avance le pointeur interne d'un tableau
     */ 
    public function next() { 
        next($this->collection); 
    }
    
    
    
    /**
     * Vérifie si la position courante est valide
     * @return boolean
     */
    public function valid() { 
        return ($this->key() === NULL ? FALSE : TRUE); 
    }
    
    
    /**
     * Retourne la clé de l'élément courant
     * @return string|int
     */
    public function key() {
        return key($this->collection);
    }
    
    
    /**
     * Replace le pointeur de fichier au début
     */
    public function rewind() { 
        reset($this->collection); 
    }
    
    
    /**
     * Indique si une position existe
     * @return boolean
     */
    public function offsetExists($k) {
        return isset($this->collection[$k]); 
    }
    
    
    /**
     * Obtenir un objet selon une position
     * @return Object
     */
    public function offsetGet($k) {
        return ($this->offsetExists($k) ? $this->collection[$k] : FALSE); 
    }
    
    
    /**
     * Assigne une valeur à une position donnée
     */
    public function offsetSet($k, $v) { 
        $this->collection[$k] = $v; 
    }
    
    
    
    /**
     * Supprime un élément à une position donnée
     */
    public function offsetUnset($k) { 
        if ($this->offsetExists($k)) { 
            unset($this->collection[$k]); 
        } 
    }
    
}