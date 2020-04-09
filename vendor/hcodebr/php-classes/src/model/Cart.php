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

}