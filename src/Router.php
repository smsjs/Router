<?php

namespace Karadocteur\Router;

/**
 * Class Router
 * Gestion des routes d'une application PHP
 */
class Router {

  
  
    /** @var  Router|null  Instance unique de cet objet Router */
    private static $_instance = NULL;
    
    /** @var  string|null  URL de la racine du site (exemple : http://exemple.com) */
    private $root = NULL;
  
    /** @var  string|null  Chemin vers le fichier qui liste toutes les Routes de l'application */
    private $routesPath = NULL;
  
    /** @var  array|null  Tableau contenant la liste des routes du site (objets Route) */
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
     * @param  string  $path Chemin vers le fichier listant les Routes
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
            throw new \RuntimeException('Impossible d\'inclure le fichier de définition des routes : '.$this->routesPath);
        }
        require_once($this->routesPath);
        return $this;
    }
  
  
  
    /**
     * Ajoute un objet Route à la liste des routes de l'application
     * @param  string       $name          Identifiant unique de la route (utile pour la générer dans les vues)
     * @param  string       $urlPrototype  Prototype de l'URL pouvant contenir des arguments sous forme : {arg}
     * @param  string       $action        Controleur + méthode à appeler pour gérer la page à afficher, sous forme : "MyController@method"
     * @param  array|null   $args          Tableau d'arguments contenus dans le prototype de l'URL sous forme ['id' => '[0-9]+']
     */
    public function route($name, $urlPrototype, $action, $args = NULL){
        $this->routes[$name] = new Route($name, $urlPrototype, $action, $args);
    }
  
  
  
    /**
     * Retourne la Route correspondante à l'URL demandée par le client si elle existe, sinon retourne FALSE
     * @param   string          $urlRequest    URL demandée par le client
     * @return  Route|boolean
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
     * Génère une URL correspondant à une Route selon son NAME +/- des paramètres
     * @param  string       $name    Identifiant unique de la Route à générer
     * @param  array|null   $params  Tableau des paramètres nécessaires pour générer une URL (exemple : ['id' => 1])
     * @return string                Exemple : https://exemple.com/blog/mon-article-8
     */
    public function url($name, $params = NULL){
        $route = clone $this->routes[$name];       
        foreach($route->getArgs() AS $argKey => $argValue){
            if(!isset($params[$argKey])){
                throw new \LengthException('La Route ['.$route->getName().'] nécessite un argument appelé ['.$argKey.']');
            }           
            if(!preg_match('#('.$argValue.')#', $params[$argKey])){
                throw new \InvalidArgumentException('Le paramètre ["'.$argKey.'" => "'.$params[$argKey].'"] de la route ['.$route->getName().'] doit correspond à la Regexp : '.$argValue);
            }
        }            
        $route->setParams($params);
        return $this->root.$route->getUrlRequest();
    }
  
}
