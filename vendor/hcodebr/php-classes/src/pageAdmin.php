<?php

namespace Hcode;

class PageAdmin extends Page {

    // esta classe é uma herança da classe Page e recebe todos os recursos existentes lá;
    // aqui a variável $tpl_dir aponta os arquivos de construção da página para outro diretório
    public function __construct($opts = array(), $tpl_dir = "/views/admin/"){

        // todos os recursos da classe pai são aproveitados, exceto o caminho da pasta admin,
        // que são exclusivos, caso contrário seria passado o padrão, que seria apasta /views/;
        // neste caso,  o método construtor da classe pái é chamado e são passados os atributos 
        // desta classe, $opts e $tpl_dir.
        parent::__construct($opts, $tpl_dir);

    }

}