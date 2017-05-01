<?php
	define('APP_ROOT', dirname(__DIR__));
	chdir(APP_ROOT);	

	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\HttpFoundation\Request;
	use Silex\Application;

	require 'vendor/autoload.php';
	require_once 'JWTWrapper.php';

	$app = new Silex\Application();

	$app['debug'] = true;

	// Conexao banco
	$dsn = 'mysql:dbname=car_model;host=localhost;charset=utf8';
	try {
		$dbh = new PDO($dsn, 'user', 'password');
	} catch (PDOException $e) {
		echo 'Connection failed: ' . $e->getMessage();
	}

// AUTENTICACAO ===

	// Autenticacao - login = user, senha= password
	$app->post('/auth', function (Request $request) use ($app) {
		$dados = array('login' => $request->request->get('login'),
						'senha' => $request->request->get('senha'));
	 
		if($dados['login'] == 'user' && $dados['senha'] == 'password') {
		    //Valido
		    $jwt = JWTWrapper::encode([
		        'expiration_sec' => 3600,
		        'iss' => 'localhost',        
		        'dadosLogin' => [
		            'login' => $dados['login'],
		            'senha' => $dados['senha']
		        ]
		    ]);
	 
		    return $app->json([
		        'login' => 'true',
		        'token' => $jwt
		    ]);
		}
	 
		return $app->json([
		    'login' => 'false, invalido',
		]);
	});

	/*	
		Metodo utilizado para verificar autenticacao
		antes da execucao de outros caminhos
	*/
	$app->before(function(Request $request, Application $app) {
		$route = $request->get('_route');
	 
		// Exceto POST_auth - insercao da autorizacao
		if($route != 'POST_auth') {

			// O token gerado sera lido a partir da Header
		    $authorization = $request->headers->get("Authorization");
		    list($jwt) = sscanf($authorization, 'Bearer %s');
	 
		    if($jwt) {
		        try {
		            $app['jwt'] = JWTWrapper::decode($jwt);
		        } catch(Exception $ex) {
		            return new Response('Erro token, acesso nao autorizado', 400);
		        }		 
		    } else {
		        return new Response('O token foi nao informado', 400);
		    }
		}
	});
	 
//MODELOS ===

	// Consulta
	$app->get('/modelos', function () use ($app, $dbh) {

		$sth = $dbh->prepare('SELECT * FROM model');
		$sth->execute();
		$modelos = $sth->fetchAll(PDO::FETCH_ASSOC);
	 
		return $app->json($modelos);
	});
	
	// Consulta especifica
	$app->get('/modelos/{id_model}', function ($id_model) use ($app, $dbh) {
		$sth = $dbh->prepare('SELECT * FROM model WHERE id_model=:id_model');
		$sth->bindValue(':id_model', $id_model, PDO::PARAM_INT);
		$sth->execute();
	 
		$modelo = $sth->fetchAll(PDO::FETCH_ASSOC);
		if(empty($modelo)) {
		    return new Response("Modelo ".$id_model." nao encontrado", 404);
		}
	 
		return $app->json($modelo);
	});

	// Inclui
	$app->post('/modelos', function(Request $request) use ($app, $dbh) {
		$dados = array('nome' => $request->request->get('nome'),
						'ano' => $request->request->get('ano'),
						'aro' => $request->request->get('aro'));
	 
		$sth = $dbh->prepare('INSERT INTO model (nome, ano, aro) 
		        VALUES(:nome, :ano, :aro)');
		 
		$sth->execute($dados); 

		$response = new Response('Modelo inserido', 201);
		return $response;
	});
	 
	// Edita
	$app->put('/modelos/{id_model}', function(Request $request, $id_model) use ($app, $dbh) {
		$dados = array('nome' => $request->request->get('nome'),
						'ano' => $request->request->get('ano'),
						'aro' => $request->request->get('aro'),
						'id_model' => $id_model);	 
		$sth = $dbh->prepare('UPDATE model
		        SET nome=:nome, ano=:ano, aro=:aro
		        WHERE id_model=:id_model');
		 
		$sth->execute($dados);
		return $app->json($dados, 200);
	});
	 
	// Deleta
	$app->delete('/modelos/{id_model}', function($id_model) use ($app, $dbh) {		
		$sth = $dbh->prepare('DELETE FROM model WHERE id_model=:id_model');
		
		$sth->bindValue(':id_model', $id_model, PDO::PARAM_INT);
		$sth->execute();
	 
		if($sth->rowCount() < 1) {
		    return new Response("Modelo ".$id_model." nao encontrado ou nao pode ser deletado", 404);
		}

		return new Response("Modelo excluido", 200);
	});

//ACESSORIOS

	// Consulta
	$app->get('/modelos/{id_model}/acessorios', function ($id_model) use ($app, $dbh) {
		
		$sth = $dbh->prepare('SELECT * FROM acessorio WHERE id_model=:id_model');
		$sth->bindValue(':id_model', $id_model, PDO::PARAM_INT);
		$sth->execute();
		$acessorios = $sth->fetchAll(PDO::FETCH_ASSOC);
	 
		return $app->json($acessorios);
	});
	
	// Consulta especifica
	$app->get('/modelos/{id_model}/acessorios/{id_acessorio}', function($id_model, $id_acessorio) use ($app, $dbh) {		
		$sth = $dbh->prepare('SELECT * FROM acessorio WHERE id_acessorio=:id_acessorio AND id_model=:id_model');
		
		$sth->bindValue(':id_model', $id_model, PDO::PARAM_INT);
		$sth->bindValue(':id_acessorio', $id_acessorio, PDO::PARAM_INT);
		$sth->execute();
	 
		$acessorios = $sth->fetchAll(PDO::FETCH_ASSOC);
		if(empty($acessorios)) {
		    return new Response("Acessorio ".$id_acessorio." nao encontrado", 404);
		}
	 
		return $app->json($acessorios);
	});

	// Inclui
	$app->post('/modelos/{id_model}/acessorios', function(Request $request, $id_model) use ($app, $dbh) {
		$dados = array('nome' => $request->request->get('nome'),
						'opcional' => $request->request->get('opcional'),
						'fabrica' => $request->request->get('fabrica'),
						'id_model' => $id_model);
	 
		$sth = $dbh->prepare('INSERT INTO acessorio (nome, opcional, fabrica, id_model) 
		        VALUES(:nome, :opcional, :fabrica, :id_model)');
		 
		$sth->execute($dados); 

		$response = new Response('Acessorio inserido', 201);
		return $response;
	});
	 
	// Edita
	$app->put('/modelos/{id_model}/acessorios/{id_acessorio}', function(Request $request, $id_model, $id_acessorio) use ($app, $dbh) {
		$dados = array('nome' => $request->request->get('nome'),
						'opcional' => $request->request->get('opcional'),
						'fabrica' => $request->request->get('fabrica'),
						'id_model' => $id_model,
						'id_acessorio' => $id_acessorio);	
		$sth = $dbh->prepare('UPDATE acessorio
		        SET nome=:nome, opcional=:opcional, fabrica=:fabrica
		        WHERE id_acessorio=:id_acessorio AND id_model=:id_model');
		 
		$sth->execute($dados);
		return $app->json($dados, 200);
	});
	 
	// Deleta
	$app->delete('/modelos/{id_model}/acessorios/{id_acessorio}', function($id_model, $id_acessorio) use ($app, $dbh) {		
		$sth = $dbh->prepare('DELETE FROM acessorio WHERE id_acessorio=:id_acessorio AND id_model=:id_model');
		
		$sth->bindValue(':id_model', $id_model, PDO::PARAM_INT);
		$sth->bindValue(':id_acessorio', $id_acessorio, PDO::PARAM_INT);
		$sth->execute();
	 
		if($sth->rowCount() < 1) {
		    return new Response("Acessorio ".$id_acessorio." nao encontrado", 404);
		}

		return new Response("Acessorio excluido", 200);
	});

	$app->run();
?>
