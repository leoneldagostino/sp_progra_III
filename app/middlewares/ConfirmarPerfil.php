<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
include_once __DIR__ . '/../models/jwt.php';
class ConfirmarPerfil
{
    private $perfilesPermitidos;

    public function __construct($perfiles)
    {
        $this->perfilesPermitidos = is_array($perfiles) ? $perfiles : [$perfiles];
    }

    public function __invoke($request, $handler)
    {
        $token = $request->getHeader('Authorization')[0];
        try {
            Autenticador::verificarToken($token);
            $datosUsuario = Autenticador::obtenerDatos($token);
            if (!in_array($datosUsuario->perfil, $this->perfilesPermitidos)) {
                throw new Exception("Perfil no autorizado");
            }
        } catch (Exception $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        return $handler->handle($request);
    }
}