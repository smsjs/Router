<?php

namespace Karadocteur\Router;

/**
 * Class Router
 * Gestion des routes d'une application PHP
 */
class Router {
  
  
    /** @var  Router|null            Instance unique de l'objet Router */
    private static $_instance = NULL;
    
    /** @var  string|null            URL de la racine du site (exemple : http://exemple.com) */
    private $root = NULL;
  
    /** @var  string|null            Chemin vers le fichier qui liste toutes les Routes de l'application */
    private $routesPath = NULL;
  
    /** @var  RoutesCollection|null  Objet RoutesCollection contenant la liste de toutes les routes du site (objets Route) */
    private $routes = NULL;
  
  
  
    /**
     * Retourne l'instance de l'objet Router pour l'utiliser qu'une seule fois au cours de l'application
     * @param   string|null  $root        URL de la racine du site (exemple : http://exemple.com)
     * @param   string|null  $routesPath  Chemin vers le fichier qui liste toutes les Routes de l'application
     * @return  Router                    Instance de l'objet Router
     */
    public static function getInstance($root = NULL, $routesPath = NULL){
        if(is_null(self::$_instance)){
            self::$_instance = new self();
            self::$_instance->routes = new RoutesCollection();
            if(!is_null($root)){
                self::$_instance->setRoot($root);
            }
            if(!is_null($routesPath)){
                self::$_instance->setRoutesPath($routesPath);
                self::$_instance->init();
            }
        }
        return self::$_instance;
    }
    
    
    /**
     * Initialise l'URL de la racine du site (exemple : https://exemple.com)
     * @param   string  $root
     * @return  Router
     */
    public function setRoot($root){
        $this->root = rtrim($root, '/');
        return $this;
    }
  
  
  
    /**
     * Défini le chemin vers le fichier chargé d'initialiser toutes les Routes de l'application
     * @param  string  $path  Chemin vers le fichier listant les Routes
     * @return Router
     */
    public function setRoutesPath($path){ 
        $this->routesPath = $path;
        return $this;
    }
  
  
  
    /**
     * Initialise le Router pour récupérer toutes les Routes
     * @return Route
     */
    public function init(){
        if(!file_exists($this->routesPath)){
            throw new \RuntimeException('It\'s impossible to include this file : '.$this->routesPath);
        }
        require_once($this->routesPath);
        return $this;
    }
  
  
  
    /**
     * Ajoute un objet Route à la liste des routes de l'application (Objet RoutesCollection)
     * @param  string       $name          Identifiant unique de la route (utile pour la générer dans les vues)
     * @param  string       $urlPrototype  Prototype de l'URL pouvant contenir des arguments sous forme : {arg}
     * @param  string       $action        Controleur + méthode à appeler pour gérer la page à afficher, sous forme : "MyController@method"
     * @param  array|null   $args          Tableau d'arguments contenus dans le prototype de l'URL sous forme ['id' => '[0-9]+']
     * @param  array|null   $accessibility Tableau des méthodes de requête accessibles pour cette route (exemple : ['get', 'post'])
     * @return Route                       Retourne la route créée
     */
    public function route($name, $urlPrototype, $action, $args = NULL, $accessibility = ['get']){        
        if(isset($this->routes[$name])){
            throw new \UnexpectedValueException('The route ['.$name.'] already exists, it can\'t be defined 2 times.');
        }       
        $route = new Route($name, $urlPrototype, $action, $args, $accessibility);
        $this->routes[$name] = $route;
        return $route;
    }
    
    
    
    /**
     * Ajoute un objet Route à la liste des routes de l'application (Objet RoutesCollection) selon la méthode de requête GET
     * @param  string       $name          Identifiant unique de la route (utile pour la générer dans les vues)
     * @param  string       $urlPrototype  Prototype de l'URL pouvant contenir des arguments sous forme : {arg}
     * @param  string       $action        Controleur + méthode à appeler pour gérer la page à afficher, sous forme : "MyController@method"
     * @param  array|null   $args          Tableau d'arguments contenus dans le prototype de l'URL sous forme ['id' => '[0-9]+']
     * @return Route                       Retourne la route créée
     */
    public function get($name, $urlPrototype, $action, $args = NULL){
        return $this->route($name, $urlPrototype, $action, $args, ['GET']);
    }
    
    
    
    /**
     * Ajoute un objet Route à la liste des routes de l'application (Objet RoutesCollection) selon la méthode de requête POST
     * @param  string       $name          Identifiant unique de la route (utile pour la générer dans les vues)
     * @param  string       $urlPrototype  Prototype de l'URL pouvant contenir des arguments sous forme : {arg}
     * @param  string       $action        Controleur + méthode à appeler pour gérer la page à afficher, sous forme : "MyController@method"
     * @param  array|null   $args          Tableau d'arguments contenus dans le prototype de l'URL sous forme ['id' => '[0-9]+']
     * @return Route                       Retourne la route créée 
     */
    public function post($name, $urlPrototype, $action, $args = NULL){
        return $this->route($name, $urlPrototype, $action, $args, ['POST']);
    }
  
  
  
    /**
     * Retourne la Route correspondante à l'URL demandée par le client si elle existe, sinon retourne FALSE
     * @param   string          $method        Méthode de requête demandée : GET, POST, etc
     * @param   string          $urlRequest    URL demandée
     * @return  Route|boolean
     */
    public function match($method, $urlRequest){    
        foreach($this->routes AS $route){
            if($route->match($method, $urlRequest)){
                return $route;
            }
        }
        return FALSE;
    }
  
  
  
    /**
     * Génère une URL correspondant à une Route selon son NAME +/- des paramètres
     * @param  string       $name    Identifiant unique de la Route à générer
     * @param  array|null   $params  Tableau des paramètres nécessaires pour générer une URL (exemple : ['id' => 1])
     * @return string                Exemple : https://exemple.com/blog/mon-article-8
     */
    public function url($name, $params = NULL){
        $route = clone $this->routes[$name];
        if(is_array($route->getArgs())){
            $route->setParams($params);
        }
        return $route->getUrlRequest($this->root);
    }
  
}
