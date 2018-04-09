# Karadocteur / Router

Il s'agit d'un routeur PHP puissant, léger et très simple d'utilisation.

Ce système permet de gérer l'ensemble des routes / URL d'une application PHP en quelques lignes de code seulement.

Il a été développé en vue d'être utilisé dans des applications ayant une architecture MVC (Modele / View / Controller) et implémente donc la programmation orientée objet (POO).

## Généralités sur le fonctionnement

Premièrement, vous devez lister toutes les Routes (URL) de votre application dans un fichier de votre choix. Ce fichier peut être nommé comme bon vous semble et placé où vous le souhaitez selon vos préférences et l'architecture de votre projet. C'est très adaptable !

Ce que nous appelons une Route, c'est un objet PHP qui permet de faire correspondre un certain prototype d'URL (avec des paramètres variables personnalisables) et une méthode de votre choix à appeler dans un controleur de votre choix également. C'est extrêmement adaptable !

Le principe est le suivant : lorsqu'un client tente de se connecter à l'application, le routeur analyse l'URL demandée et retourne la route correspondante. En récupérant celle-ci, vous aurez donc accès à la méthode et au controleur qui doivent être appelés ainsi qu'aux paramètres de l'URL.

De plus, il est possible de générer des URL depuis les vues très facilement, grâce aux identifiants uniques des routes.

## Installation via composer

L'installation du routeur est très simple via composer (packagist) puisqu'il suffit d'utiliser la commande suivante :

```
composer require karadocteur/router
```

Et pour utiliser l'autoloader, évidemment :

```php
define("ROOT", "Racine de votre application"); // Définissez la racine de votre application
require_once(ROOT.'/vendor/autoload.php');
```

## Définir les routes de l'application

Pour définir les routes, vous devez les lister dans un fichier séparé, que vous pouvez nommer comme vous voulez et placer n'importe où selon vos préférences et l'architecture de votre projet.

Ce fichier sera en fait inclus par la classe Router.

Exemple : créer la route de la page d'accueil de votre application :

```php
/**
 * Exemple : ROOT/config/routes.php    /!\ Selon vos préférences et l'architecture de votre application
 * Liste toutes les routes de l'application
 */

namespace Karadocteur\Router;

// Exemple :
$name = 'home';                       // Identifiant unique de la route : utile pour générer les URL depuis les vues
$urlPrototype = '/';                  // Prototype de l'URL : ici il s'agit de la racine du site
$action = 'PagesController@index';    // Controleur "PagesController" et Méthode "index" à appeler pour gérer la page demandée
$args = NULL;                         // Tableau d'arguments si l'URL doit comprendre des paramètres

$this->route($name, $urlPrototype, $action, $args);
```

Si l'URL contient des paramètres (ID d'un membre, SLUG d'un article, etc), vous pouvez utiliser des arguments entre accolades en définissant la route. Ces arguments doivent correspondre à des chaînes de caractères qui représentent des expressions régulières :

```php
$urlPrototype = '/blog/{id}';    // Prototype de l'URL qui devra comporter 1 argument entre accolades : "id"
$args = ['id' => '[0-9]+'];      // Tableau d'arguments correspondants à des expressions régulières (regexp)

```

Il faut également savoir qu'il existe des constantes prédéfinies pour se simplifier la vie :

```php
Route::PARAM_INT = '[0-9]+';            // Pour les entiers : 1 

Route::PARAM_WORD = '[a-z]+';           // Pour les mots (sans espace et en minuscule) : "title"
  
Route::PARAM_SLUG = '[a-z0-9\-_]+';     // Pour les slugs (caractères alphanumériques et tirets) : "mon-super_titre-8"
```

Enfin, voici un exemple pour créer la route d'une page de lecture d'un article de blog avec comme paramètre un SLUG et un ID :

```php
/**
 * Exemple : ROOT/config/routes.php    /!\ Selon vos préférences et l'architecture de votre application
 * Liste toutes les routes de l'application
 */

namespace Karadocteur\Router;

$this->route('blog/read', '/blog/{slug}-{id}', 'BlogController@read', [
  'slug' => Route::PARAM_SLUG,
  'id' => Route::PARAM_INT
]);
```

## Initialisation du routeur

Lors de la première utilisation du routeur, il est nécessaire de l'initialiser. Pour cela, vous devez lui indiquer en paramètre le chemin vers le fichier listant toutes les Routes (URL) de l'application.

```php
use \Karadocteur\Router\Router;

$routesPath = ROOT.'/config/routes.php'; // Dépend de l'architecture de votre projet

$router = Router::getInstance()
  ->setRoutesPath($routesPath)
  ->init();
```

Une version plus courte est également possible :

```php
use \Karadocteur\Router\Router;

$routesPath = ROOT.'/config/routes.php'; // Dépend de l'architecture de votre projet

$router = Router::getInstance($routesPath);
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
// On initialise le routeur une 1ère fois
use \Karadocteur\Router\Router;
$routesPath = ROOT.'/config/routes.php'; // Dépend de l'architecture de votre projet
$router = Router::getInstance($routesPath);

// On récupère l'URL demandée par le client : pour poursuivre l'exemple on simule une requête sur l'URL "/blog/mon-super_article-8"
// Si vous utilisez le PSR7, comme guzzle/psr7 par exemple (https://github.com/guzzle/psr7) => $urlRequest = $request->getUri()->getPath();
$urlRequest = '/blog/mon-super_article-8';  

// On récupère la route qui correspond à l'URL demandée 
$route = $router->match($urlRequest); // renvoie l'objet Route retrouvée, sinon renvoie FALSE

// Vous pouvez ainsi vérifier si la route existe ou non
if($route){
  echo 'La route existe';
} else {
  echo 'erreur 404';
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
