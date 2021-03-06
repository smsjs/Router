<?php

namespace Karadocteur\Router;

/**
 * Class Route 
 * Gère une route de l'application
 */
class Route {
  
  
  
    /** @const  string  Regexp pour les chiffres (exemple : 1) */
    const PARAM_INT = '[\d]+';
  
    /** @const  string  Regexp pour des mots en minuscules (exemple : "title") */
    const PARAM_WORD = '[a-z]+';
  
    /** @const  string  Regexp pour une chaîne alphanumérique avec ou sans tirets (exemple : "my_super_title-8") */
    const PARAM_SLUG = '[a-z0-9_-]+';
  
  
  
    /** @var  string      Identifiant unique de la route */
    private $name;
    
    /** @var  array       Tableau contenant la liste des méthodes de requête via lesquelles la route est accessible (exemple : ['get', 'post'] */
    private $accessibility = [];
  
    /** @var  string      Prototype de l'URL correspondante à cette Route, si des arguments sont passés ils doivent être nommés comme ceci : {arg} */
    private $urlPrototype;
  
    /** @var  string      Nom du controleur qui contient la méthode à appeler pour gérer la page demandée */
    private $controller;
  
    /** @var  string      Nom de la méthode à appeler pour gérer la page demandée */
    private $method;
  
    /** @var  array|null  Liste des arguments contenus dans le prototype de l'URL, notés sous la forme : ['name' => '[a-z]+'] ou ['name' => Route::PARAM_WORD] */
    private $args = NULL;
  
    /** @var array|null   Liste des paramètres présents dans l'URL demandée par le client */
    private $params = NULL;
  
    /** @var string|null  Prototype de l'URL avec les arguments sous forme d'expression régulière */
    private $regexp = NULL;
  
  
  
    /**
     * Initialise une nouvelle route
     * @param  string       $name           Identifiant unique de la route
     * @param  string       $urlPrototype   URL correspondante à la route
     * @param  string       $action         Nom de la méthode et du controleur à appeler pour gérer la route sous forme "MyController@method"
     * @param  array|null   $args           Liste des arguments de l'URL nécessaires pour créer une Route, sous forme ['id' => Route::PARAM_INT]
     * @param  array|null   $accessibility  Méthodes de requête par lesquelles la route est accessible (exempe : ['get', 'post'])
     */
    public function __construct($name, $urlPrototype, $action, $args = NULL, $accessibility = ['get']){
        $this->name = $name;
        $this->urlPrototype = $urlPrototype;
        if(is_array($args)){
            $this->setArgs($args);
        }
        $parts = explode('@', $action, 2);
        $this->controller = $parts[0];
        $this->method = $parts[1];
        $this->accessibility = array_map('strtoupper', $accessibility);
    }
  
  
  
    /**
     * Initialise les arguments de la route dans l'ordre d'apparition du Prototype de l'URL
     * @param   array  $args  Liste des arguments de l'URL nécessaires pour créer une Route, sous forme ['id' => Route::PARAM_INT]
     * @return  Route
     */  
    public function setArgs(array $args){
        uksort($args, function($a, $b){
    	    $aCursor = strpos($this->urlPrototype, '{'.$a.'}');
    	    $bCursor = strpos($this->urlPrototype, '{'.$b.'}');
    	    return ($aCursor < $bCursor) ? -1 : 1;
        });
        $this->args = $args;
        return $this;
    }
  
  
 
    /**
     * Vérifie si cette Route correspond à l'URL demandée par le client et si oui on initialise les paramètres envoyés
     * @param   string   $method      Méthode de la requête : GET, POST, etc
     * @param   string   $urlRequest  URL demandée
     * @return  boolean
     */
    public function match($method, $urlRequest){
        if(!in_array(strtoupper($method), $this->accessibility)){
            return FALSE;
        }
        if(is_array($this->args)){
            $result = preg_match('#^'.$this->getRegexp().'$#', $urlRequest, $match);
            if($result){
                array_shift($match);
                $this->setParams($match);
            }
            return $result;
        }
        return $this->urlPrototype === $urlRequest;
    }
  
  
  
    /**
     * Vérifie et initialise les paramètres de la route
     * @param   array  $params  Liste des paramètres
     * @return  Route
     */
    public function setParams($params){
        if(count($this->args) != count($params)){
            throw new \LengthException('La route ['.$this->name.'] doit avoir '.count($this->args).' paramètre(s), '.count($params).' ont été passé(s)');
        }
        $i = 0;       
        foreach($this->args AS $argKey => $argValue){             
            if(isset($params[$i])){
                $params[$argKey] = $params[$i];
                $i++;
            }          
            if(!isset($params[$argKey])){
                throw new \LengthException('La Route ['.$this->name.'] nécessite un argument appelé ['.$argKey.']');
            }                      
            if(!preg_match('#('.$argValue.')#', $params[$argKey])){
                throw new \InvalidArgumentException('Le paramètre ["'.$argKey.'" => "'.$params[$argKey].'"] de la route ['.$this->name.'] doit correspond à la Regexp : '.$argValue);
            }
            $this->params[$argKey] = $params[$argKey];
        }
        return $this;
    }
  
  
  
    /**
     * Créer la regexp correspondant au prototype de l'URL selon les arguments
     * @return string
     */
    public function getRegexp(){
        if(is_null($this->regexp)){
            $this->regexp = $this->urlPrototype;
            if(is_array($this->args)){
                foreach($this->args AS $argKey => $argValue){
                    $this->regexp = str_replace('{'.$argKey.'}', '('.$argValue.')', $this->regexp);
                }
            }
        }
        return $this->regexp;
    }
  
  
  
    /**
     * Génère une URL qui correspond à cette Route, en fonction des paramètres si nécessaire
     * @params   string|null   $root   URL de ma racine du site (exemple : http://exemple.com)
     * @return   string
     */
    public function getUrlRequest($root = NULL){
        if(!is_array($this->args)){
            return rtrim($root.$this->urlPrototype, '/');
        }
        $url = $this->urlPrototype;
        foreach($this->params AS $paramKey => $paramValue){
            $url = str_replace('{'.$paramKey.'}', $paramValue, $url);
        }
        return rtrim($root.$url, '/');
    }
  
  
  
    /** @return  string  Identifiant unique de la route */
    public function getName(){ return $this->name; }
    
    /** @return  string  Méthodes de requête par lesquelles la route est accessible (exemple : ['get', 'post'] */
    public function getAccessibility(){ return $this->accessibility; }
  
    /** @return  string  Prototype de l'URL correspondant à cette route, avec les arguments sous forme : {name} */
    public function getUrlPrototype(){ return $this->urlPrototype; }
  
    /** @return  string  Controleur contenant la méthode gérant cette route */
    public function getController(){ return $this->controller; }
  
    /** @return  string  Méthode gérant cette route */
    public function getMethod(){ return $this->method; }
  
    /** @return  array  Liste des arguments que le prototype de l'URL doit contenir */
    public function getArgs(){ return $this->args; }
  
    /** @return  array  Liste des paramètres de l'URL envoyée par le client */
    public function getParams(){ return $this->params; }
  
}
