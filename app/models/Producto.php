<?php

class Producto {
    public $nombre;
    public $precio;
    public $tipo;
    public $stock;
    public $marca;
    public $imagen;



    public static function guardarProducto($nombre, $precio, $tipo, $stock, $marca, $imagen) {

        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("INSERT INTO tienda (nombre, precio, tipo, stock, marca, imagen) VALUES (:nombre, :precio, :tipo, :stock, :marca, :imagen)");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $precio, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':stock', $stock, PDO::PARAM_INT);
        $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
        $consulta->bindValue(':imagen', $imagen, PDO::PARAM_STR);
        $consulta->execute();
        return $objetoAccesoDato->obtenerUltimoId();
        
    }

    public static function consultarProductos() {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM productos");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function actualizarProducto($nombre,$marca,$tipo,$precio, $stock) {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("UPDATE tienda SET precio = :precio, stock = :stock, WHERE nombre = :nombre AND marca = :marca AND tipo = :tipo");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $precio, PDO::PARAM_INT);
        $consulta->bindValue(':stock', $stock, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }

    public static function consultar($nombre, $tipo, $marca) {
        $objetoAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objetoAccesoDato->prepararConsulta("SELECT * FROM productos WHERE nombre = :nombre AND tipo = :tipo AND marca = :marca");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
        if ($consulta->execute()) {
            return true;
        }
        return false;
    }

    public static function cargarProductosPorCSV($archivo) {
        $archivo = fopen($archivo, "r");
        $linea = fgetcsv($archivo, 1000, ",");
        while (($linea = fgetcsv($archivo, 1000, ",")) !== FALSE) {
            // $producto = new Producto($linea[0], $linea[1], $linea[2], $linea[3], $linea[4], $linea[5]);
            // Producto::guardarProducto($producto->nombre, $producto->precio, $producto->tipo, $producto->stock, $producto->marca, $producto->imagen);
        }
    }
}

