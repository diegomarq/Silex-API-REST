<?php
	use \Firebase\JWT\JWT;

	//Gerencia tokens jwt
	class JWTWrapper {
		// Chave
		const KEY = 'ABCD';

		//Gera novo token jwt
		public static function encode(array $dados) {
			$issuedAt = time();
	        $expire = $issuedAt + $dados['expiration_sec'];

			/*	iat - tempo que foi gerado token
				iss - dominio utilizado
				exp - tempo de expiracao token
				nbf - tempo token nao e valido
				data - dados para modelo
			*/
			$token = array('iat' => $issuedAt, 'iss' => $dados['iss'], 'exp' => $expire, 'nbf' => $issuedAt-1, 'data' => $dados['dadosLogin']);
			return JWT::encode($token, self::KEY);
		}

		// Decodifica tokens jwt
		public static function decode($jwt) {
			// HS256 esta relacionado com algoritmo de decodificacao utilizado
			return JWT::decode($jwt, self::KEY, array('HS256'));
		}
	}
?>

