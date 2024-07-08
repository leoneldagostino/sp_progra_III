<?php

require_once './interfaces/IApiUsable.php';
require_once './models/Producto.php';

class ProductoController extends Producto implements IApiUsable {
    public function CargarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $archivo = $request->getUploadedFiles();
        $rutaImagenes = 'imagenesProductos/2024/';

        $nombre = $parametros['nombre'];
        $precio = $parametros['precio'];
        $tipo = $parametros['tipo'];
        $stock = $parametros['stock'];
        $marca = $parametros['marca'];
        $imagen = '';

        if (isset($archivo['imagen'])) {
            $imagenArchivo = $archivo['imagen'];
            $extension = pathinfo($imagenArchivo->getClientFilename(), PATHINFO_EXTENSION);
            $filename = $nombre . '_' . $tipo . '_' . $marca . '.' . $extension;
            $rutaDestino = $rutaImagenes . $filename;
            $imagenArchivo->moveTo($rutaDestino);
            $imagen = $rutaDestino;
        }
        else
        {  
            $imagen = '';
        }

        $verificoProducto = Producto::consultar($nombre,$tipo,$marca);

        if($verificoProducto) {
            Producto::actualizarProducto($nombre,$marca,$tipo,$precio,$stock);
            if($imagen){
                Producto::guardarImagen($nombre,$tipo,$marca,$imagen);
            }

            $payload = json_encode(array("mensaje" => "el producto ya existe"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
        else{

            Producto::guardarProducto($nombre, $precio, $tipo, $stock, $marca);
            Producto::guardarImagen($nombre,$tipo,$marca,$imagen);
    
            $payload = json_encode(array("mensaje" => "producto creado con exito"));
    
            $response->getBody()->write($payload);
        
            return $response->withHeader('Content-Type', 'application/json');
        }
    }


    public function TraerUno($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $nombre = $parametros['nombre'];
        $tipo = $parametros['tipo'];
        $marca = $parametros['marca'];

        $producto = Producto::consultar($nombre,$tipo,$marca);

        if($producto) {
            $payload = json_encode(array("mensaje" => "producto existe"));
        }
        else {
            $existeMarca = Producto::buscarMarca($marca);
            $existeTipo = Producto::buscarTipo($tipo);
            $existeNombre = Producto::buscarNombre($nombre);

            if($existeNombre){
                if($existeMarca)
                {
                    if($existeTipo)
                    {
                        $payload = json_encode(array("mensaje" => "el producto no existe"));
                    }
                    else
                    {
                        $payload = json_encode(array("mensaje" => "el tipo no existe"));
                    }
                }
                else
                {
                    $payload = json_encode(array("mensaje" => "la marca no existe"));
                }
            }
            else{
                $payload = json_encode(array("mensaje" => "el nombre no existe"));
            }
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
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
