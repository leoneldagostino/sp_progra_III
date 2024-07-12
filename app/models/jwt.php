<?php

use Firebase\JWT\JWT;

class Autenticador 
{
    private static $claveSecreta = 'claveSecreta';
    private static $encriptado = ['HS256'];
    private static $aud = null;

    public static function CrearToken($datos)
    {
        $ahora = time();
        $payload = array(
            'iat' => $ahora,
            'exp' => $ahora + 60 * 60,
            'aud' => self::Aud(),
            'data' => $datos,
            'app' => "API REST"
        );
        return JWT::encode($payload, self::$claveSecreta);
    }


    public static function verificarToken($token)
    {
        if(empty($token) || $token == '')
        {
            throw new Exception("El token esta vacio.");
        }
        try {
            $decodificado = JWT::decode(
                $token,
                self::$claveSecreta,
                self::$encriptado
            );
        } 
        catch (Exception $e) {
            throw $e;
        }
        if($decodificado->aud !== self::Aud())
        {
            throw new Exception("No es el usuario valido");
        }
    }

    public static function obtenerPayload($token)
    {
        return JWT::decode(
            $token,
            self::$claveSecreta,
            self::$encriptado
        );
    }

    public static function obtenerDatos($token)
    {
        return JWT::decode($token
        ,self::$claveSecreta,
        self::$encriptado)->data;
    }

    public static function Aud()
    {
        $aud = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud = $_SERVER['REMOTE_ADDR'];
        }
        
        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();
        
        return sha1($aud);
    }
}