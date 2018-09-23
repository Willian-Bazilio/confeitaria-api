<?php

namespace app\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use app\model\BoloDAOImplementation as BoloDAO;
use app\classes\BadHttpRequest;
use app\classes\Bolo;

class ControladorApp
{

    public function lerTodosBolos( Request $request, Response $response, array $args )
    {
        $status = 200;

        try {
            $dao = new BoloDAO();
            $bolosArray = $dao->getAllBolos();
            $corpoResp =  json_encode( array( "bolos" =>$bolosArray ) );
            $response = $response->withHeader('Content-type', 'application/json')
                                 ->write( $corpoResp );
        } catch ( \PDOException $e ) {
            $status = 500;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        }
        return $response->withStatus($status);
    }

    public function cadastrarBolo( Request $request, Response $response, array $args )
    {
        # Verificar erro na entrada
        # Esperamos uma entrada Json da seguinte forma:
        # {"nome":"valor","sabor":"valor","cobertura":"valor","descricao":"valor"}
        $objEntrada = $request->getParsedBody();
        $status = 201;

        try {
            # getParsedBody, irá parsear o Json de entrada, caso ele falhe
            # pela entrada não ser um Json válido, esse if irá capturar
            if ( is_null($objEntrada) )
                throw new BadHttpRequest();

            # Verifica se os campos obrigatórios estão preenchidos
            if (!( isset( $objEntrada["nome"] ) &&
            isset( $objEntrada["sabor"] ) &&
            isset( $objEntrada["cobertura"])))
                throw new BadHttpRequest();

            # Descrição é opcional, então caso não tenha sido passada será substituida por ""
            $desc = (!\is_null($objEntrada["descricao"])) ? $objEntrada["descricao"] : "";

            $arrayBolo = array( "nome"=>$objEntrada["nome"],
                                "sabor"=>$objEntrada["sabor"],
                                "cobertura"=>$objEntrada["cobertura"],
                                "descricao"=> $desc);

            $boloInst = new Bolo($arrayBolo);
            $dao = new BoloDAO();
            $dao->createBolo( $boloInst );
        } catch (BadHttpRequest $e) {
            $status = 400;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        } catch (\PDOException $e) {
            $status = 500;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        }

        return $response->withStatus($status);
    }
}