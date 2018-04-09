<?php

namespace Karadocteur\Router;

/**
 * Class Route 
 * Gère une route de l'application
 */
class Route {
  
  
  
  /** @const string Regexp correspondant à un argument pour $urlPrototype de type INT (exemple : 1) */
  const PARAM_INT = '[0-9]+';
  
  /** @const string Regexp correspondant à un argument pour $urlPrototype contenant uniquement des lettres minuscules (exemple : "title") */
  const PARAM_WORD = '[a-z]+';
  
  /** @const string Regexp correspondant à un argument pour $urlPrototype contenant un chaîne alphanumérique et/ou des tirets (exemple : "my-super_title") */
  const PARAM_SLUG = '[a-z0-9\-_]+';
  
  
  
  /** @var string|null Nom unique de la route */
  private $name = NULL;
  
  /** @var string Prototype de l'URL correspondante à cette Route, si des arguments sont passés ils doivent être nommés comme ceci : {arg} */
  private $urlPrototype;
  
  /** @var string Nom du controleur qui contient la méthode à appeler pour gérer la page demandée */
  private $controller;
  
  /** @var string Nom de la méthode à appeler pour gérer la page demandée */
  private $method;
  
  /** @var array|null Liste des arguments contenus dans le prototype de l'URL, notés sous la forme : ['name' => [a-z]+] ou ['name' => Route::PARAM_WORD] */
  private $args = NULL;
  
  /** @var array|null Liste des paramètres présents dans l'URL demandée par le client */
  private $params = NULL;
  
  /** @var string|null Prototype de l'URL dont les arguments (sous forme {arg}) ont été remplacés par des expressions régulières pour la comparer avec l'URL demandée par le client */
  private $regexp = NULL;
  
  
  
  /**
   * Initialise une nouvelle route
   * @param  string       $name           Nom unique de la route
   * @param  string       $urlPrototype   URL correspondante à la route
   * @param  string       $action         Nom de la méthode et du controleur à appeler pour gérer la route
   * @param  array|null   $args           Liste des arguments de l'URL nécessaires pour créer une Route, sous forme {name}
   */
  public function __construct($name = NULL, $urlPrototype, $action, $args = NULL){
    $this->name = $name;
    $this->urlPrototype = $urlPrototype;
    $parts = explode('@', $action, 2);
    $this->controller = $parts[0];
    $this->method = $parts[1];
    $this->args = $args;
  }
  
  
 
  /**
   * Vérifie si cette Route correspond à l'URL demandée par le client et si oui on initialise les paramètres envoyés
   * @param  string   $urlRequest  URL demandée par le client
   * @return boolean
   */
  public function match($urlRequest){
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
   * Initialise les paramètres envoyés dans l'URL par le client
   * @param array $params Liste des paramètres envoyés par le client dans l'URL
   */
  public function setParams($params){
    $i = 0;
    foreach($this->args AS $arg => $v){
      $this->params[$arg] = isset($params[$i]) ? $params[$i] : $params[$arg];
      $i++;
    }
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
   * @return string URL correspondant à cette route
   */
  public function getUrlRequest(){
    if(!is_array($this->args)){
      return $this->urlPrototype;
    }
    $result = $this->urlPrototype;
    foreach($this->args AS $argKey => $argValue){
      $result = str_replace('{'.$argKey.'}', $this->params[$argKey], $result);
    }
    return $result;
  }
  
  
  
  /** @return string Identifiant unique de la route */
  public function getName(){ return $this->name; }
  
  /** @return string Prototype de l'URL correspondante à cette route, avec les arguments sous forme : {name} */
  public function getUrlPrototype(){ return $this->urlPrototype; }
  
  /** @return string Controleur contenant la méthode gérant cette route */
  public function getController(){ return $this->controller; }
  
  /** @return string Méthode gérant cette route */
  public function getMethod(){ return $this->method; }
  
  /** @return array Liste des arguments que le prototype de l'URL doit contenir */
  public function getArgs(){ return $this->args; }
  
  /** @return array Liste des paramètres de l'URL envoyée par le client */
  public function getParams(){ return $this->params; }
  
}