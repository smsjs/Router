<?php

namespace Karadocteur\Router;

/**
 * Class Router
 * Gestion de toutes les routes de l'application
 */
class Router {
  
  

  /** @var Router|null Instance de l'objet Router pour une utilisation unique dans l'application */
  private static $_instance = NULL;
  
  /** @var string|null Chemin vers le fichier qui créé toutes les Routes de l'application */ 
  private $routesPath = NULL;
  
  /** @var array Tableau contenant la liste des routes du site (objets Route) */  
  private $routes = [];
  
  
  
  /**
   * Retourne l'instance de l'objet Router pour l'utiliser qu'une seule fois au cours de l'application
   * @param   string|null  $routesPath Chemin vers le fichier qui créé toutes les Routes de l'application
   * @return  Router       Instance de l'objet Router
   */ 
  public static function getInstance($routesPath = NULL){
    if(is_null(self::$_instance)){
      self::$_instance = new self();
      if(!is_null($routesPath)){
        self::$_instance->setRoutesPath($routesPath);
        self::$_instance->init();
      }
    }
    return self::$_instance;
  }
  
  
  
  /**
   * Défini le chemin vers le fichier chargé d'initialiser toutes les Routes de l'application
   * @param string $path Chemin vers le fichier listant les Routes
   */
  public function setRoutesPath($path){
    $this->routesPath = $path;
    return $this;
  }
  
  
  
  /**
   * Initialise le Router pour récupérer toutes les Routes
   */
  public function init(){
    require_once($this->routesPath);
    return $this;
  }
  
  
  
  /**
   * Ajoute un objet Route à la liste des routes de l'application
   * @param  string       $name          Nom unique de la route
   * @param  string       $urlPrototype  Prototype de l'URL pouvant contenir des arguments sous forme : {arg}
   * @param  string       $action        Nom du controleur et de la méthode à appeler pour gérer la page à afficher, sous forme "MyController@method"
   * @param  array|null   $args          Tableau d'arguments contenus dans le prototype de l'URL sous forme ['id' => '[0-9]+']
   */
  public function route($name, $urlPrototype, $action, $args = NULL){
    $this->routes[$name] = new Route($name, $urlPrototype, $action, $args);
  }
  
  
  
  /**
   * Retourne la Route correspondante à l'URL demandée par le client si elle existe, sinon retourne FALSE
   * @param string $urlRequest URL demandée par le client
   * @return Route|boolean
   */
  public function match($urlRequest){    
    foreach($this->routes AS $route){
      if($route->match($urlRequest)){
        return $route;
      }
    }
    return FALSE;
  }
  
  
  
  /**
   * Génère une URL correspondant à une Route en fonction de son NAME +/- des paramètres
   * @param string $name    Identifiant de la Route à générer
   * @param array  $params  Tableau des paramètres nécessaires pour générer une URL (exemple : ['id' => 1])
   * @return string
   */
  public function url($name, $params = NULL){
    $route = clone $this->routes[$name];
    $route->setParams($params);
    return $route->getUrlRequest();
  }
  
  
  
}
