<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
use Firebase\JWT\JWT;

class UsuarioController extends Usuario implements IApiUsable
{

    private static $claveSecreta = 'ClaveSecreta';
    private static $encriptacion = ['HS256'];
    private static $aud = null;

    public function Login( $request, $response, array $args) 
    {
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $password = $parametros['password'];

        // Validar el usuario y la contraseña contra la base de datos
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM usuarios WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();
        $usuarioBD = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($usuarioBD && ($password == $usuarioBD['clave'])) {
            $datos = array(
                "usuario" => $usuario,
                "perfil" => $usuarioBD['perfil']
            );
            $token = Autenticador::CrearToken($datos);
            $payload = json_encode(array("token" => $token));
        } else {
            $payload = json_encode(array("mensaje" => "Usuario o contraseña incorrectos"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }



    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $archivo = $request->getUploadedFiles();
        $rutaImagenes= './ImagenesDeUsuarios/2024/';

        $mail= $parametros['mail'];
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $perfil = $parametros['perfil'];
        $fecha = date('Y-m-d');
        
        if (isset($archivo['foto'])) {
          $foto = $archivo['foto'];
          $extension = pathinfo($foto->getClientFilename(), PATHINFO_EXTENSION);
          $filename = $usuario . '_' . $perfil . '_' . $fecha . '.' . $extension;
          $rutaDestino = $rutaImagenes . $filename;
          $foto->moveTo($rutaDestino);
          $rutaFoto = $rutaDestino;
      }
        else
        {  
          $rutaFoto = '';
        }



        // Creamos el usuario
        $usuarioNuevo = new Usuario();
        $usuarioNuevo->mail = $mail;
        $usuarioNuevo->usuario = $usuario;
        $usuarioNuevo->clave = $clave;
        $usuarioNuevo->perfil = $perfil;
        $usuarioNuevo->foto = $rutaFoto;

        $usuarioNuevo->crearUsuario();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por nombre
        $usr = $args['usuario'];
        $usuario = Usuario::obtenerUsuario($usr);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        Usuario::modificarUsuario($nombre);

        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuarioId = $parametros['usuarioId'];
        Usuario::borrarUsuario($usuarioId);

        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
