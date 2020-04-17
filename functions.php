<?php

use \Hcode\Model\User;

// altera o formato do número vindo do BD.
function formatPrice($vlprice)
{

    // o number_format é uma função nativa do PHP para formatar números
    return $vlprice > 0 ? number_format((float)$vlprice, 2, ",", ".") : '0,00';

}

function checkLogin($inadmin = true)
{

    return User::checklogin($inadmin);

}

function getUserName()
{

    $user = User::getFromSession();
    
    return $user->getdesperson();

}


