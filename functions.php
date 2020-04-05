<?php

// altera o formato do número vindo do BD.
function formatPrice(float $vlprice)
{
    
    // o number_format é uma função nativa do PHP para formatar números
    return number_format($vlprice, 2, ",", ".");

}