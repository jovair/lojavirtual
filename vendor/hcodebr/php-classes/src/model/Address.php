<?php

// aqui é informado o namespace do caminho onde está a classe Adress
namespace Hcode\Model;

// para executar esta classe, precisamos dos dados que estão no Banco de Dados e para acessar
// o banco, precisamos da classe Sql; a barra antes do Hcode é importante
use \Hcode\DB\Sql;

// a classe User é uma extensão da classe Model e para isso precisamos dessa classe
use \Hcode\Model;

class Address extends Model {

    const SESSION_ERROR = 'AddressError';

    public static function getCEP($nrcep)
    {

        $nrcep = str_replace("-", "", $nrcep);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json/");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return $data;

    }

    public function loadFromCEP($nrcep)
    {
        $data = Address::getCEP($nrcep);

        if (isset($data['logradouro']) && $data['logradouro']) {

            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdesstate($data['uf']);
            $this->setdescountry('Brasil');
            $this->setdeszipcode($nrcep);
        }
    }

    public function Save()
    {

        $sql = new Sql();

        $results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)", [
            ':idaddress'=>$this->getidaddress(),
            ':idperson'=>$this->getidperson(),
            ':desaddress'=>utf8_decode($this->getdesaddress()),
            ':descomplement'=>utf8_decode($this->getdescomplement()),
            ':descity'=>utf8_decode($this->getdescity()),
            ':desstate'=>utf8_decode($this->getdesstate()),
            ':descountry'=>utf8_decode($this->getdescountry()),
            ':deszipcode'=>$this->getdeszipcode(),
            ':desdistrict'=>utf8_decode($this->getdesdistrict())
        ]);

        if (count($results) > 0) {

            $this->setData($results[0]);

        }
        
    }

    public static function setMsgError($msg)
    {

        $_SESSION[Address::SESSION_ERROR] = $msg;

    }

    // pega a mensagem de erro
    public static function getMsgError()
    {

        $msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";

        Address::clearMsgError();

        return $msg;

    }

    // limpa a mensagem de erro
    public static function clearMsgError()
    {

        $_SESSION[Address::SESSION_ERROR] = NULL;

    }

}