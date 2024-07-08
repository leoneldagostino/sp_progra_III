<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './middlewares/Logger.php';
require_once './middlewares/ConfirmarPerfil.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/VentaController.php';


// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

$middlewareAdministrador = new ConfirmarPerfil(['admin']);
$middlewareAdministradorEmpleado = new ConfirmarPerfil(['admin','empleado']);

// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
  $group->get('[/]', \UsuarioController::class . ':TraerTodos');
  $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
  $group->post('[/]', \UsuarioController::class . ':CargarUno');
});

$app->post('/login', \UsuarioController::class . ':Login');
$app->post('/registro', \UsuarioController::class . ':Registro');

$app->group('/tienda', function (RouteCollectorProxy $group) use ($middlewareAdministrador, $middlewareAdministradorEmpleado)  {
  $group->post('/alta', \ProductoController::class . ':CargarUno')->add($middlewareAdministrador);
  $group->post('/consultar', \ProductoController::class . ':TraerUno')->add($middlewareAdministradorEmpleado);
});

$app->group('/venta', function (RouteCollectorProxy $group) use ($middlewareAdministrador, $middlewareAdministradorEmpleado){
  $group->post('/alta', \VentaController::class . ':CargarUno') ->add($middlewareAdministradorEmpleado);
  $group->get('/consultar/{ruta:.+}', \VentaController::class . ':Consultar') ->add($middlewareAdministradorEmpleado);
  $group->put('/modificar', \VentaController::class . ':ModificarUno')->add($middlewareAdministrador);
  $group->get('/descargar', \VentaController::class . ':DescargarCSV')->add($middlewareAdministrador);
});

// $app->group('/usuarios', function (RouteCollectorProxy $group) {
//   $group->get('[/]', \UsuarioController::class . ':TraerTodos');
//   $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
//   $group->post('[/]', \UsuarioController::class . ':CargarUno');
// });

// $app->post('/login', \UsuarioController::class . ':Login');
// $app->post('/registro', \UsuarioController::class . ':Registro');

// $app->group('/tienda', function (RouteCollectorProxy $group)   {
//   $group->post('/alta', \ProductoController::class . ':CargarUno');
//   $group->post('/consultar', \ProductoController::class . ':TraerUno');
// });
// $app->group('/venta', function (RouteCollectorProxy $group){
//   $group->post('/alta', \VentaController::class . ':CargarUno') ;
//   $group->get('/consultar/{ruta:.+}', \VentaController::class . ':Consultar') ;
//   $group->put('/modificar', \VentaController::class . ':ModificarUno');
//   $group->get('/modificar', \VentaController::class . ':DescargarCSV');
// });



$app->get('[/]', function (Request $request, Response $response) {    
    $payload = json_encode(array("mensaje" => "Slim Framework 4 PHP"));
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
