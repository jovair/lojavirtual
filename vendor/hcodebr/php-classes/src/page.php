<?php

namespace Hcode;

// Este é o namespace do template
use Rain\Tpl;

class Page {

    // Declaração do atributo $tpl
    private $tpl;

    // recebe atributos opcionais para o método construtor
    private $options = [];

    // recebe atributos defaults para o método construtor
    private $defaults = [
        "data"=>[]
    ];

    // os atributos do método construtor chegam de acordo com a rota orientada pelo slim
    public function __construct($opts = array()) {

        // faz o merge do conteúdo do código com o template;
        // se os dados recebidos de $defaults e $opts confltarem, vale o $opts
        $this->options = array_merge($this->defaults, $opts);

        // configuração do template
        // $_SERVER["DOCUMENTO_ROOT"] encontra o diretório root do projeto
        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/", // endereço das páginas HTML
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/", // endereço da página montada
            "debug"         => false // configurado com false melhora a velocidade
        );

        Tpl::configure( $config );

        // o atributo tpl recebe o objeto tpl que é o template
        $this->tpl = new Tpl;

        $this->setData($this->options["data"]);
    
        $this->tpl->draw("header");
    
    }

    // atribui as variáveis que vão aparecer no template
    private function setData($data = array()) {

        foreach ($data as $key => $value) {

            $this->tpl->assign($key, $value);

        }

    }

    public function setTpl($name, $data = array(), $returnHTML = false) {
        
        $this->setData($data);

        return $this->tpl->draw($name, $returnHTML);

    }

    public function __destruct() {

        $this->tpl->draw("footer");
    }

}