1- Instalacao do php, mysql e phpMyAdmin.
https://connectwww.com/how-to-install-and-configure-apache-php-mysql-and-phpmyadmin-on-linux-mint/1443/

2- Instalacao e configuracao do Micro-framework Silex, segue em anexo o composer utilizado.
-- Step 1
https://www.digitalocean.com/community/tutorials/how-to-get-started-with-silex-on-ubuntu-14-04

3- Habilitar PDO para acesso ao banco de dados. Nas versoes mais recentes do PHP nao e necessario.
http://www.devmedia.com.br/introducao-ao-php-pdo/24973

4- Deve ser criado um banco de dados por meio do phpMyAdmin com nome car_model. Arquivo car_model.sql seque em anexo.

5- Deve ser criado um novo usuario no banco com user name = user e password = password;

6- Instalar Postman.
https://www.getpostman.com/

7- Importar arquivo de teste Teste API REST Diego.postman_collection.json

*ATENCAO: Durante o teste, os ids utilizados devem ser modificados de acordo com as operacoes utilizadas.

Ao utilizar o Postman, a autenticacao deve ser feita por meio da insercao de login e senha, onde login = user e senha = password. Apos a geracao do token, ele deve ser copiado e colado na aba Header, tal que key = Authorization e Value = Bearer token-gerado. Apos a insercao do Header, os links do teste podem ser utilizados.
