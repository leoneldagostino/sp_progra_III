<?php

class moverImagenes
{
    public function __invoke($request, $handler)
    {
        $parametros = $request->getParsedBody();
        $archivos = $request->getUploadedFiles();

        if (isset($archivos['imagen'])) {
            $imagenSubida = $archivos['imagen'];
            $nombreArchivo = $imagenSubida->getClientFilename();
            $ruta = "./imagenesProductos/2024/" . $nombreArchivo;

            // Mueve la imagen a la nueva ruta
            $imagenSubida->rename($ruta);

            // Agregar la ruta de la imagen a los parámetros
            $parametros['imagen'] = $ruta;
            $request = $request->withParsedBody($parametros);
        } else {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write('Error: No se pudo subir la imagen.');
            return $response->withStatus(400);
        }

        return $handler->handle($request);
    }
}

