<?php

// aqui é informado o namespace do caminho onde está a classe Cart
namespace Hcode\Model;

// para executar esta classe, precisamos dos dados que estão no Banco de Dados e para acessar
// o banco, precisamos da classe Sql; a barra antes do Hcode é importante
use \Hcode\DB\Sql;

// a classe User é uma extensão da classe Model e para isso precisamos dessa classe
use \Hcode\Model;

// a classe Mailer é necessária para verificar se o usuário está logado
use \Hcode\Mailer;

// a classe User é necessária para validar a sessão do usuário
use \Hcode\Model\User;

class Cart extends Model {

    // armazena o id da sessão para movimentar produtos no carrinho
    const SESSION = "Cart";
    const SESSION_ERROR = "cartError";

    // verifica se o carrinho existe no BD e se a sessão está ativa
    public static function getFromSession()
    {
        $cart = new Cart();

        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION] > 0) {

            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

        } else {

            $cart->getFromSessionID();

            if (!(int)$cart->getidcart() > 0) {

                $data = [
                    'dessessionid'=>session_id()
                ];

                if (User::checkLogin(false)) {

                    $user = User::getFromSession();

                    $data['iduser'] = $user->getiduser();

                }

                $cart->setData($data);

                $cart->save();

                $cart->setToSession();

            }
            
        }

        return $cart;
    }

    // insere um carrinho novo na seção
    public function setToSession()
    {

        $_SESSION[Cart::SESSION] = $this->getValues();

    }

    // carrega o carrinho a partir do session_id
    public function getFromSessionID()
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
            ':dessessionid'=>session_id()
        ]);

        if (count($results) > 0) {

            $this->setData($results[0]);
        
        }

    }

    // busca o carrinho do usuário já existente no BD.
    public function get(int $idcart)
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
            ':idcart'=>$idcart
        ]);

        if (count($results) > 0) {

            $this->setData($results[0]);
        
        }

    }

    // salva o carrinho no BD
    public function save()
    {

        $sql = new Sql();

        $results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
           ':idcart'=>$this->getidcart(), 
            ':dessessionid'=>$this->getdessessionid(), 
            ':iduser'=>$this->getiduser(), 
            ':deszipcode'=>$this->getdeszipcode(), 
            ':vlfreight'=>$this->getvlfreight(), 
            ':nrdays'=>$this->getnrdays()
        ]);
            $this->setData($results[0]);
    }
    
    
    // adiciona produtos no carrinho
    public function addProduct(Product $product)
    {
        
        $sql = new Sql();

        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", [
            ':idcart'=>$this->getidcart(),
            ':idproduct'=>$product->getidproduct()
        ]);

    }
    
    // se all remove todos os produtos do carrinho clicando no ícone do lado esquerdo do carrinho, ao lado da imagem do produto
    // se all for true, remove um produto de cada vez, clicando no sinal de menos na quantidade de produtos
    public function removeProduct(Product $product, $all = false)
    {
        
        $sql = new Sql();

        if ($all) {

            $sql->query("UPDATE tb_cartsproducts 
            SET dtremoved = NOW() 
            WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
                ':idcart'=>$this->getidcart(),
                ':idproduct'=>$product->getidproduct()
            ]);
        } else {

            $sql->query("UPDATE tb_cartsproducts 
            SET dtremoved = NOW() 
            WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
                ':idcart'=>$this->getidcart(),
                ':idproduct'=>$product->getidproduct()
            ]);
        }

    }

    // pega um produto no BD para colocar no carrinho de compras
    public function getProducts()
    {
        
        $sql = new Sql();

        $rows = $sql->select(
        "SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
        FROM tb_cartsproducts a
        INNER JOIN tb_products b ON a.idproduct = b.idproduct
        WHERE a.idcart = :idcart AND a.dtremoved IS NULL
        GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
        ORDER BY b.desproduct
        ", [
            'idcart'=>$this->getidcart()
        ]);

        return Product::checkList($rows);
    }

    // pega a soma de todos os itenhs do carrinho no BD.
    public function getProductsTotals()
    {
        
        $sql = new Sql();

        $results = $sql->select(
            "SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
            FROM tb_products a
            INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
            WHERE b.idcart = :idcart AND dtremoved IS NULL;
        ", [
            ':idcart'=>$this->getidcart()
        ]);

        if (count($results) > 0) {
            return $results[0];
        } else {
            return[];
        }
    }
    
    // pega os dados do produto do carrinho no BD e passa para o webservice dos correios calcular o frete de acordo com os CEPs de origem/destino
    public function setFreight($nrzipcode)
    {
        
        $nrzipcode = str_replace('-', '', $nrzipcode);

        $totals = $this->getProductsTotals();

        if ($totals['vlheight'] < 2) $totals['vlheight'] = 2;
        if ($totals['vllength'] < 16) $totals['vllength'] = 16;
        
        if ($totals['nrqtd'] > 0) {

            $qs = http_build_query([
            'nCdEmpresa'=>'',
            'sDsSenha'=>'',
            'nCdServico'=>'40010',
            'sCepOrigem'=>'37640000',
            'sCepDestino'=>$nrzipcode,
            'nVlPeso'=>$totals['vlweight'],
            'nCdFormato'=>'1',
            'nVlComprimento'=>$totals['vllength'],
            'nVlAltura'=>$totals['vlheight'],
            'nVlLargura'=>$totals['vlwidth'],
            'nVlDiametro'=>'0',
            'sCdMaoPropria'=>'S',
            'nVlValorDeclarado'=>$totals['vlprice'],
            'sCdAvisoRecebimento'=>'S'

            ]);

            // webservice Correios
            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

            // recebe as variáveis dos Correios
            $result = $xml->Servicos->cServico;

            if ($result->MsgErro != '') {

                Cart::setMsgError($result->MsgErro);

            } else {

                Cart::clearMsgError();

            }

            // variáveis de interesse para o site
            $this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
            $this->setdeszipcode($nrzipcode);

            $this->save();

            return $result;

        } else {
    
    
        }
    }

    // substitui a formatação recebida dos correios de R$ para $ (padrão do BD)
    public static function formatValueToDecimal($value):float {

        $value = str_replace('.', '', $value);
        return str_replace(',', '.', $value);
    }

    // configura mensagem de erro de acordo com os Correios
    public static function setMsgError($msg)
    {

        $_SESSION[Cart::SESSION_ERROR] = $msg;

    }

    // pega a mensagem de erro
    public static function getMsgError()
    {

        $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

        Cart::clearMsgError();

        return $msg;

    }

    // limpa a mensagem de erro
    public static function clearMsgError()
    {

        $_SESSION[Cart::SESSION_ERROR] = NULL;

    }



}