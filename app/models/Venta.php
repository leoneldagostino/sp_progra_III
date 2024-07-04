<?php

class Venta
{
    public $id;
    public $email_usuario;
    public $nombre_producto;
    public $tipo_producto;
    public $marca_producto;
    public $cantidad;
    public $fecha;
    public $numero_pedido;
    public $precio_total;
    public $imagen;


    public function __construct($email_usuario, $nombre_producto, $tipo_producto, $marca_producto, $cantidad, $fecha, $imagen)
    {
        $this->email_usuario = $email_usuario;
        $this->nombre_producto = $nombre_producto;
        $this->tipo_producto = $tipo_producto;
        $this->marca_producto = $marca_producto;
        $this->cantidad = $cantidad;
        $this->fecha = $fecha;
        $this->imagen = $imagen;
    }

    public function guardar()
    {
        try{

            $objetoAccesoDato = AccesoDatos::obtenerInstancia();
            $consulta = $objetoAccesoDato->prepararConsulta("INSERT INTO ventas (email_usuario, nombre_producto, tipo_producto, marca_producto, cantidad, fecha, imagen) VALUES (:email_usuario, :nombre_producto, :tipo_producto, :marca_producto, :cantidad, :fecha, :imagen)");
            $consulta->bindValue(':email_usuario', $this->email_usuario, PDO::PARAM_STR);
            $consulta->bindValue(':nombre_producto', $this->nombre_producto, PDO::PARAM_STR);
            $consulta->bindValue(':tipo_producto', $this->tipo_producto, PDO::PARAM_STR);
            $consulta->bindValue(':marca_producto', $this->marca_producto, PDO::PARAM_STR);
            $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
            $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
            $consulta->bindValue(':imagen', $this->imagen, PDO::PARAM_STR);
            $consulta->execute();
            return $objetoAccesoDato->obtenerUltimoId();
        }
        catch(PDOException $error){
            return $error->getMessage();
        }
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
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM ventas WHERE cantidad BETWEEN :valorMin AND :valorMax");
        $consulta->bindValue(':valorMin', $valorMin, PDO::PARAM_INT);
        $consulta->bindValue(':valorMax', $valorMax, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Venta');
    }
    public static function consultarIngresoPorDia($fecha)
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT SUM(precio_total) FROM ventas WHERE fecha = :fecha");
        $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }
    public static function consultarProductoMasVendido()
    {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT nombre_producto, COUNT(nombre_producto) as cantidad FROM ventas GROUP BY nombre_producto ORDER BY cantidad DESC LIMIT 1");
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
}