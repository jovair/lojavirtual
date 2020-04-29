<?php

use \Hcode\Model\User;
use \Hcode\Model\Cart;

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

function getCartNrQtd()
{

    $cart = Cart::getFromSession();

    $totals = $cart->getProductsTotals();

    return $totals['nrqtd'];
}

function getCartVlSubTotal()
{

    $cart = Cart::getFromSession();

    $totals = $cart->getProductsTotals();

    return formatPrice($totals['vlprice']);
}
