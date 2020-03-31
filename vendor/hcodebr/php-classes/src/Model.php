<?php

namespace Hcode;

class Model {

    // contém todos os valores dos objetos. No caso do usuário, todos os campos da tabela
    private $values = [];

    public function __call($name, $args)
    {

        // recebe os três primeiros caracteres para identificar se é um ge ou um set
        $method = substr($name, 0, 3);
        
        // recebe o restante da cadeia de caracteres a partir da posição 3
        $fieldName = substr($name, 3, strlen($name));

        // toma ação diferente para get e set
        switch ($method)
        {

            case "get": //se a categoria existe, retorna o seu nome, se não, retorna vazio
                return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
            break;

            case "set":
                $this->values[$fieldName] = $args[0];
            break;
        }

    }

    // este método recebe um array e cria os atributos e seus respectivos valores de cada campo do array
    public function setData($data = array())
    {
        foreach ($data as $key => $value)
        {
            // no momento que o $this vai incluir o atributo, ele precisaria do valor da chave set$key já 
            // definido, mas esse valor ainda não existe. Dentro do par de chaves é feita a concatenação
            // da string set + o valor da chave recebido, por exemplo setidusuario e então é passado o valor
            // em $value 
            $this->{"set".$key}($value);
        }
    }

    // esse método retorna os dados do usuário para ser iniciada a seção no método SESSION da classe User
    public function getValues()
    {

        return $this->values;

    }

}