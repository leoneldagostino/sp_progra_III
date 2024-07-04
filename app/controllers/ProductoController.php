<?php

require_once './interfaces/IApiUsable.php';
require_once './models/Producto.php';

class ProductoController extends Producto implements IApiUsable {
    public function CargarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $precio = $parametros['precio'];
        $tipo = $parametros['tipo'];
        $stock = $parametros['stock'];
        $marca = $parametros['marca'];
        $imagen = $parametros['imagen'];
        Producto::guardarProducto($nombre, $precio, $tipo, $stock, $marca, $imagen);

        $payload = json_encode(array("mensaje" => "producto creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        // Tu código aquí
    }

    public function TraerTodos($request, $response, $args) {
        // Tu código aquí
    }

    public function BorrarUno($request, $response, $args) {
        // Tu código aquí
    }

    public function ModificarUno($request, $response, $args) {
        // Tu código aquí
    }
}
