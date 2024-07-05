<?php

class Venta
{
    private $id;
    public $email_usuario;
    public $nombre_producto;
    public $tipo_producto;
    public $marca_producto;
    public $cantidad;
    public $fecha;
    public $numero_pedido;
    public $precio_total;
    public $imagen;


    

    public static function generarNumeroPedido()
    {
        $numero_pedido = mt_rand(0, 99999); // Generar un número aleatorio entre 0 y 99999

        // Verificar si el número de pedido ya existe en la base de datos
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT COUNT(*) FROM ventas WHERE numero_pedido = :numero_pedido");
        $consulta->bindValue(':numero_pedido', $numero_pedido, PDO::PARAM_INT);
        $consulta->execute();
        $cantidad = $consulta->fetchColumn();

        // Si el número de pedido ya existe, generar uno nuevo
        while ($cantidad > 0) {
            $numero_pedido = mt_rand(0, 99999);
            $consulta->bindValue(':numero_pedido', $numero_pedido, PDO::PARAM_INT);
            $consulta->execute();
            $cantidad = $consulta->fetchColumn();
        }

        return $numero_pedido;
    }


    public static function calcularPrecio($nombre,$tipo,$marca, $stock)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT precio FROM tienda WHERE nombre = :nombre AND tipo = :tipo AND marca = :marca");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
        $consulta->execute();

        $resultado = $consulta->fetch();
        return $resultado['precio'] * $stock;

    }
    public function guardar()
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("INSERT INTO ventas (email_usuario, nombre_producto, tipo_producto, marca_producto, cantidad, numero_pedido, precio_total, fecha) VALUES (:email_usuario, :nombre_producto, :tipo_producto, :marca_producto, :cantidad, :numero_pedido, :precio_total, :fecha)");
        $consulta->bindValue(':email_usuario', $this->email_usuario, PDO::PARAM_STR);
        $consulta->bindValue(':nombre_producto', $this->nombre_producto, PDO::PARAM_STR);
        $consulta->bindValue(':tipo_producto', $this->tipo_producto, PDO::PARAM_STR);
        $consulta->bindValue(':marca_producto', $this->marca_producto, PDO::PARAM_STR);
        $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
        $consulta->bindValue(':numero_pedido', $this->numero_pedido, PDO::PARAM_INT); // Guardar como entero
        $consulta->bindValue(':precio_total', $this->precio_total, PDO::PARAM_INT);
        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->execute();
    
        return $objetoAccesoDato->obtenerUltimoId();
    }
    

    public static function consultarPorDia($fecha)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM ventas WHERE fecha = :fecha");
        $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }
    public static function consultarPorUsuario($email)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM ventas WHERE email_usuario = :email_usuario");
        $consulta->bindValue(':email_usuario', $email, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }
    public static function consultarPorProducto($tipo)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM ventas WHERE tipo_producto = :tipo_producto");
        $consulta->bindValue(':tipo_producto', $tipo, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }
    public static function consultarEntreValores($valorMin,$valorMax)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM ventas WHERE precio_total BETWEEN :valorMin AND :valorMax");
        $consulta->bindValue(':valorMin', $valorMin, PDO::PARAM_INT);
        $consulta->bindValue(':valorMax', $valorMax, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }
    public static function consultarIngresoPorDia($fecha=null)
    {
        if($fecha != null)
        {
            $objetoAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objetoAccesoDato->prepararConsulta("SELECT SUM(precio_total) as Ganancia FROM ventas WHERE fecha = :fecha");
            $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
            $consulta->execute();
            return $consulta->fetch(PDO::FETCH_ASSOC);
            
        }
        else{
            $objetoAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objetoAccesoDato->prepararConsulta("SELECT SUM(precio_total) as Ganancia FROM ventas");
            $consulta->execute();
            return $consulta->fetch(PDO::FETCH_ASSOC);
        
        }
    }
    public static function consultarProductoMasVendido()
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT nombre_producto, SUM(cantidad) as cantidad_vendida FROM ventas GROUP BY nombre_producto ORDER BY cantidad_vendida DESC LIMIT 1");        
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }
    public static function modificar($numero_pedido,$email,$nombre_producto,$tipo_producto,$marca_producto,$cantidad)
    {
        try{
            $objetoAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objetoAccesoDato->prepararConsulta("UPDATE ventas SET email_usuario = :email_usuario, nombre_producto = :nombre_producto, tipo_producto = :tipo_producto, marca_producto = :marca_producto, cantidad = :cantidad WHERE numero_pedido = :numero_pedido");
            $consulta->bindValue(':numero_pedido', $numero_pedido, PDO::PARAM_INT);
            $consulta->bindValue(':email_usuario', $email, PDO::PARAM_STR);
            $consulta->bindValue(':nombre_producto', $nombre_producto, PDO::PARAM_STR);
            $consulta->bindValue(':tipo_producto', $tipo_producto, PDO::PARAM_STR);
            $consulta->bindValue(':marca_producto', $marca_producto, PDO::PARAM_STR);
            $consulta->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
            $consulta->execute();
            return $consulta->rowCount();
        }catch(PDOException $error) {
            return $error->getMessage();
        }
    }
    public static function descontarStock($nombre,$tipo,$marca,$stock)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE tienda SET stock = stock - :stock WHERE nombre = :nombre AND tipo = :tipo AND marca = :marca");
        $consulta->bindValue(':stock', $stock, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->rowCount();
    }

    public static function consultarStock($stock,$nombre,$tipo,$marca)
    {        
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT stock FROM tienda WHERE nombre = :nombre AND tipo = :tipo AND marca = :marca");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
        $consulta->execute();
        $resultado = $consulta->fetch();
        if($resultado['stock'] >= $stock)
        {
            return true;
        }
        else
        {
            return false;
        }
    }



    public static function guardarFoto(Venta &$venta, $imagen)
    {
        $nombreImagen = $venta->nombre_producto . '_' . $venta->tipo_producto . '_' . $venta->marca_producto . '_' . substr($venta->email_usuario, 0, strpos($venta->email_usuario, '@')) . '_' . $venta->fecha;
        $rutaImagen = '/ImagenesDeVenta/' . date('Y') . '/' . $nombreImagen;

        try {
            // Guardar la imagen en la carpeta
            move_uploaded_file($imagen['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $rutaImagen);

            // Guardar la ruta de la imagen en la base de datos
            $objetoAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objetoAccesoDato->prepararConsulta("UPDATE ventas SET imagen = :imagen WHERE numero_pedido = :numero_pedido");
            $consulta->bindValue(':imagen', $rutaImagen, PDO::PARAM_STR);
            $consulta->bindValue(':numero_pedido', $venta->numero_pedido, PDO::PARAM_INT);
            $consulta->execute();

            return true;
        } catch (PDOException $error) {
            return $error->getMessage();
        }
    }
    public static function consultarPorNumeroPedido($numero_pedido)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM ventas WHERE numero_pedido = :numero_pedido");
        $consulta->bindValue(':numero_pedido', $numero_pedido, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }
}