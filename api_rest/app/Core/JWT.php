<?php
abstract class  JWT {

	public static function create ($id) {
    $header = [
      'typ' => 'JWT',
      'alg' => 'HS256'
    ];

    $header = json_encode($header);
    $header = base64_encode($header);

    $payload = ['id' => $id];
    $payload = json_encode($payload);
    $payload = base64_encode($payload);

    return $header.'.'.$payload.'.'.self::signature($header, $payload);
  }

  public static function valid ($token) {
    $part = explode('.', $token);
    $header = $part[0];
    $payload = $part[1];
    $signature = $part[2];

    $valid = self::signature($header, $payload);

    if($signature === $valid){
      return (array)json_decode(base64_decode($payload));
    }

    return false;
  }

  private static function signature ($header, $payload) {
    $signature = hash_hmac('sha256', $header.'.'.$payload, Config::JWTSECRET, true);
    return base64_encode($signature);
  }
}

