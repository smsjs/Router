# Karadocteur / Router

Il s'agit d'un **routeur PHP** puissant, léger et très simple d'utilisation.

Ce système permet de gérer l'ensemble des *routes / URL* d'une application PHP en quelques lignes de code seulement.

Il a été développé en vue d'être utilisé dans des applications ayant une *architecture MVC* (Modele / View / Controller) et implémente donc la *programmation orientée objet (POO)*.



## Généralités sur le fonctionnement

Premièrement, vous devez lister toutes les Routes (URL) de votre application dans un fichier de votre choix. Ce fichier peut être nommé comme bon vous semble et placé où vous le souhaitez selon vos préférences et l'architecture de votre projet. C'est très **adaptable** !

Ce que nous appelons une **Route**, c'est un *objet PHP* qui permet de faire correspondre un certain *prototype d'URL* (avec des paramètres variables personnalisables) et une *méthode* de votre choix à appeler dans un *controleur* de votre choix également. C'est extrêmement **adaptable** !

Le principe est le suivant : lorsqu'un client tente de se connecter à l'application, le routeur analyse l'URL demandée et retourne la route correspondante. En récupérant celle-ci, vous aurez donc accès à la méthode et au controleur qui doivent être appelés ainsi qu'aux paramètres de l'URL.

De plus, il est très facilement possible de **générer des URL complexes** depuis les vues, en fonction de leurs paramètres, grâce aux identifiants uniques des routes.



## Installation via composer

L'installation du routeur est très simple via **composer (packagist)** puisqu'il suffit d'utiliser la commande suivante :

```
composer require karadocteur/router
```

Et pour utiliser l'autoloader, évidemment :

```php
require_once __DIR__.'/vendor/autoload.php'; // Dépend de l'architecture de votre projet
```


## Définir les routes de l'application

Pour définir les routes, vous devez les lister dans un *fichier séparé*, que vous pouvez nommer comme vous voulez et placer n'importe où selon vos préférences et l'architecture de votre projet.

Ce fichier sera en fait inclus par la classe Router, ce qui signifie que le routeur y sera accessible depuis la variable `$this`.

Exemple : créer la route de la page d'accueil de votre application :

```php
/**
 * Exemple : /root/config/routes.php    /!\ Selon vos préférences et l'architecture de votre application
 * Liste toutes les routes de l'application
 */

namespace Karadocteur\Router;

// Exemple :
$name = 'home';                       // Identifiant unique de la route : utile pour générer les URL depuis les vues
$urlPrototype = '/';                  // Prototype de l'URL : ici il s'agit de la racine du site
$action = 'PagesController@index';    // Controleur "PagesController" et Méthode "index" à appeler pour gérer la page demandée

$this->route($name, $urlPrototype, $action);
```


Si l'URL contient des paramètres (ID d'un membre, SLUG d'un article, etc), vous pouvez utiliser des arguments entre accolades en définissant la route. Ces arguments doivent correspondre à des chaînes de caractères qui représentent des expressions régulières :

```php
$urlPrototype = '/blog/{id}';    // Prototype de l'URL qui devra comporter 1 argument entre accolades : "id"
$args = ['id' => '[0-9]+'];      // Tableau d'arguments correspondants à des expressions régulières (regexp)

$this->route($name, $urlPrototype, $action, $args);

```


Il faut également savoir qu'il existe des constantes prédéfinies pour se simplifier la vie :

```php
Route::PARAM_INT = '[0-9]+';            // Pour les entiers : 1 

Route::PARAM_WORD = '[a-z]+';           // Pour les mots (sans espace et en minuscule) : "title"
  
Route::PARAM_SLUG = '[a-z0-9\-_]+';     // Pour les slugs (caractères alphanumériques et tirets) : "mon-super_titre-8"
```


A noter que par défaut les routes sont accessibles uniquement via la méthode `GET`, si vous souhaiter créer des routes accessibles via d'autres méthodes (en `POST` par exemple) alors il faudra le signaler comme cela :

```php
$accessibility = ['GET', 'POST'];       // Tableau des méthodes de requêtes acceptées par cette route
$this->route($name, $urlPrototype, $action, $args, $accessibility);
```


Si la route ne doit être accessible uniquement en `GET` ou uniquement en `POST`, vous pouvez utiliser ces méthodes directement :

```php
$this->get($name, $urlPrototype, $action);   // Route uniquement accessible via la méthode GET
$this->post($name, $urlPrototype, $action);  // Route uniquement accessible via la méthode POST
```


Pour finir, voici un exemple qui crée la route d'une page de lecture d'un article de blog avec comme paramètres un SLUG et un ID, uniquement accessible avec la méthode `GET` :

```php
/**
 * Exemple : /root/config/routes.php    /!\ Selon vos préférences et l'architecture de votre application
 * Liste toutes les routes de l'application
 */

namespace Karadocteur\Router;

$this->get('blog/read', '/blog/{slug}-{id}', 'BlogController@read', [
  'slug' => Route::PARAM_SLUG,
  'id' => Route::PARAM_INT
]);
```


## Initialisation du routeur

Lors de la première utilisation du routeur, il est nécessaire de l'initialiser. Pour cela, vous devez lui indiquer en paramètre le chemin vers le fichier listant toutes les Routes (URL) de l'application.

```php
use \Karadocteur\Router\Router;

$routesPath = '/home/config/routes.php'; // Dépend de l'architecture de votre projet

$router = Router::getInstance()
  ->setRoutesPath($routesPath)
  ->init();
```


Pour pouvoir générer les routes en chemin absolue depuis les vues très facilement, il est intéressant de spécifier l'URL de la racine du site dès l'initialisation du routeur, comme ceci :

```php
$root = 'https://example.com';

$router = Router::getInstance()
  ->setRoot($root)
  ->setRoutesPath($routesPath)
  ->init();
```


Une version plus courte est également possible :

```php
use \Karadocteur\Router\Router;

$router = Router::getInstance($root, $routesPath);
```


## Accéder au routeur partout dans votre application

Le routeur étant un Singleton, vous pouvez récupérer l'unique instance de l'objet depuis n'importe où dans votre application avec le code suivant :

```php
use \Karadocteur\Router\Router;

$router = Router::getInstance();
```


## Récupérer la route de l'URL demandée par le client

Pour vérifier que l'URL demandée par le client est une route qui existe, c'est très simple :

```php

use \Karadocteur\Router\Router;

// Pour l'exemple on définit quelques constantes de notre application
$root = 'https://example.com';
$routesPath = '/home/config/routes.php'; // Dépend de l'architecture de votre projet

// Pour poursuivre l'exemple on simule une requête sur l'URL "/blog/mon-super_article-8" via une méthod "GET"
// Ces informations peuvent être récupérées via la variable $_SERVER ou via une implémentation de l'objet Request compatible avec le PSR7 (exemple : https://github.com/guzzle/psr7 )
$requestMethod  = 'GET';
$urlRequest = '/blog/mon-super_article-8'; 


// On initialise le routeur une 1ère fois
$router = Router::getInstance($root, $routesPath);

// On récupère la route qui correspond à l'URL demandée
$route = $router->match($requestMethod, $urlRequest);

// Vous pouvez ensuite vérifier si la route existe ou non
if($route){
  // La route existe         =>    Il faut appeler le controleur
} else {
  // La route n'existe pas   =>    Il faut renvoyer une erreur 404
}
```


Enfin, voici comment vous pouvez utiliser l'objet Route pour appeler le controleur et la méthode avec les paramètres de l'URL :

```php 
$controllerName = $route->getController();
$methodName = $route->getMethod();
$params = $route->getParams();

// On peut instancier le controleur comme ceci
$controller = new $controllerName();

// Et on peut ensuite appeller la méthode en lui passant les paramètres
$controller->methodName($params);
```


## Créer une URL depuis une vue

Depuis une vue, vous pouvez très simplement générer une URL grâce au routeur en lui indiquant l'identifiant unique ($name) de la route :

```php
use \Karadocteur\Router\Router;

$router = Router::getInstance();

$name = 'home'; // Identifiant unique de la route

echo $router->url($name); // Affiche "/"
```

Si l'URL nécessite des paramètres, vous pouvez également les passer en argument :

```php
use \Karadocteur\Router\Router;

$router = Router::getInstance();

$name = 'blog/read';                // Identifiant unique de la route
$params = [                         
  'slug' => 'mon-super_article',    // Tableau des paramètres nécessaires pour créer l'URL selon son prototype
  'id'   => 8
];

echo $router->url($name, $params); // Affiche "/blog/mon-super_article-8"

```


## A vous de coder !

Ce projet est **open-source** : n'hésitez pas à l'utiliser et le partager. 

Toutes remarques / suggestions / commentaires qui pourraient contribuer à faire avancer ce projet sont les bienvenues.
