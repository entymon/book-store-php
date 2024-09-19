<?php 
namespace App\Service;

use App\Exception\ValidationException;
use Biblys\Isbn\Isbn;

class BookService
{
    const COVER_IMAGES = 'images';

    private $errors = [];

    public function getImagePath(String $fileName)
    {
        return $_ENV['SERVER_PATH'].'/'.self::COVER_IMAGES.'/'.$fileName;
    }

    public function simpleDataValdiation(Array $parameters): void
    {
        $this->errors = [];
        
        if (!isset($parameters['title'])) { $this->errors[] = 'title is required'; }
        if (!isset($parameters['author'])) { $this->errors[] = 'author is required'; }
        if (!isset($parameters['publishDate'])) { $this->errors[] = 'publish date is required'; }
        if (!isset($parameters['isbn'])) { $this->errors[] = 'ISBN is required'; }
        if (!isset($parameters['title'])) { $this->errors[] = 'Title is required'; }

        $this->validateISBN($parameters['isbn']);

        if (count($this->errors) > 0) {
            throw new ValidationException(json_encode($this->errors));
        }
    }

    public function validateISBN(String $isbn): void {
        try {
            Isbn::validateAsIsbn13($isbn);
        } catch (\Exception $error) {
            $this->errors[] = $error->getMessage();
        }
    }
}