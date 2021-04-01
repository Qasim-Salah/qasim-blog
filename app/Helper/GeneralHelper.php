<?php
define('PAGINATION_COUNT', 5);
define('PAGINATION_DASHBOARD', 10);


function uploadImage($folder, $image)
{
    $image->store('/', $folder);
    $filename = $image->hashName();
    return $filename;
}

