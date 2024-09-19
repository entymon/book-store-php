<?php 
namespace App\Service;

class BookService
{
    const COVER_IMAGES = 'images';

    public function getImagePath(String $fileName)
    {
        return $_ENV['SERVER_PATH'].'/'.self::COVER_IMAGES.'/'.$fileName;
    }
}