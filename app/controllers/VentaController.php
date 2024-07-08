<?php

require_once './models/Venta.php';
require_once './interfaces/IApiUsable.php';

class VentaController extends Venta implements IApiUsable
{
    public function Consultar($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        $ruta = $args['ruta'];
        var_dump($ruta);
        switch ($ruta) {
            case 'productos/vendidos':
                $fecha = isset($parametros['fecha']) ? $parametros['fecha'] : date('Y-m-d', strtotime('-1 day'));
                $ventas = Venta::consultarPorDia($fecha);
                break;
            case 'ventas/porUsuario':
                $email = $parametros['email'];
                $ventas = Venta::consultarPorUsuario($email);
                break;
            case 'ventas/porProducto':
                $tipo = $parametros['tipo'];
                $ventas = Venta::consultarPorProducto($tipo);
                break;
            case 'productos/entreValores':
                $valorMin = $parametros['valorMin'];
                $valorMax = $parametros['valorMax'];
                $ventas = Venta::consultarEntreValores($valorMin, $valorMax);
                break;
            case 'ventas/ingresos':
                $fecha = isset($parametros['fecha']) ? $parametros['fecha'] : null;
                $ventas = Venta::consultarIngresoPorDia($fecha);
                break;
            case 'productos/masVendido':
                $ventas = Venta::consultarProductoMasVendido();
                break;
            default:
                $ventas = array("mensaje" => "Ruta no encontrada");
                break;
        }

        $payload = json_encode($ventas);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $numero_pedido = $parametros['numero_pedido'];
        $email = $parametros['email'];
        $nombre_producto = $parametros['nombre_producto'];
        $tipo_producto = $parametros['tipo_producto'];
        $marca_producto = $parametros['marca_producto'];
        $cantidad = $parametros['cantidad'];

        $ventaExistente = Venta::consultarPorNumeroPedido($numero_pedido);

        if ($ventaExistente) {
            Venta::modificar($numero_pedido, $email, $nombre_producto, $tipo_producto, $marca_producto, $cantidad);
            $payload = json_encode(array("mensaje" => "Venta modificada con exito"));
        } else {
            $payload = json_encode(array("mensaje" => "No existe ese número de pedido"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function CargarUno($request, $response, $args)
    {

        $rutaImagenes = './imagenesVenta/2024';
        $parametros = $request->getParsedBody();
        $archivo = $request->getUploadedFiles();
        $venta = new Venta();

        $venta->email_usuario = $parametros['email'];
        $venta->nombre_producto = $parametros['nombre_producto'];
        $venta->tipo_producto = $parametros['tipo_producto'];
        $venta->marca_producto = $parametros['marca_producto'];
        $venta->cantidad = $parametros['cantidad'];
        $venta->fecha = date('Y-m-d');
        $venta->numero_pedido = Venta::generarNumeroPedido();
        
        if(Producto::consultar($venta->nombre_producto,$venta->tipo_producto,$venta->marca_producto))
        {
            $venta->precio_total = Venta::calcularPrecio($venta->nombre_producto,$venta->tipo_producto,$venta->marca_producto,$venta->cantidad);
            if(Venta::consultarStock($venta->cantidad,$venta->nombre_producto,$venta->tipo_producto,$venta->marca_producto))
            {
                $imagen = '';

                if (isset($archivo['imagen'])) {
                    $imagenArchivo = $archivo['imagen'];
                    $extension = pathinfo($imagenArchivo->getClientFilename(), PATHINFO_EXTENSION);
                    $filename = $venta->nombre_producto . '_' . $venta->tipo_producto . '_' . $venta->marca_producto . '.' . $extension;
                    $rutaDestino = $rutaImagenes . $filename;
                    $imagenArchivo->moveTo($rutaDestino);
                    $imagen = $rutaDestino;
                }
                Venta::guardarImagen($venta->numero_pedido,$imagen);
                Venta::descontarStock($venta->nombre_producto,$venta->tipo_producto,$venta->marca_producto,$venta->cantidad);
                $venta->guardar();
                $payload = json_encode(array("mensaje" => "Venta guardada!"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
            else{
                $payload = json_encode(array("mensaje" => "No hay stock suficiente"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }

        }
        else{
            $payload = json_encode(array("mensaje" => "El producto no existe"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        
        }
    }

    public function DescargarCSV($request, $response, $args)
    {
        $ventas = Venta::consultarTodos();
        $csv = fopen('./csv/ventas.csv', 'w+');
        fputcsv($csv, array('email_usuario', 'nombre_producto', 'tipo_producto', 'marca_producto', 'cantidad', 'fecha', 'numero_pedido', 'precio_total'));
        foreach ($ventas as $venta)
        {
            fputcsv($csv, array($venta->email_usuario, $venta->nombre_producto, $venta->tipo_producto, $venta->marca_producto, $venta->cantidad, $venta->fecha, $venta->numero_pedido, $venta->precio_total));
        }
        fclose($csv);
        $csv = file_get_contents('./csv/ventas.csv');
        $response->getBody()->write($csv);

        $response = $response->withHeader('Content-Type', 'text/csv');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="ventas.csv"');
        $response->getBody()->write($csv);
        
        return $response;
    }


    public function TraerUno($request, $response, $args){}
	public function TraerTodos($request, $response, $args){}
	public function BorrarUno($request, $response, $args){}

}

