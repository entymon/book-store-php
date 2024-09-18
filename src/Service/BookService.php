<?php
use App\Entity\Book;

class BookSevice 
{  
    public static function shapeResponse(Book $book): Array
    {
        return [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'description' => $book->getDescription(),
            'publishDate' => $book->getPublishDate(),
            'isbn' => $book->getIsbn(),
            'coverPhoto' => $book->getCoverPhoto(),
        ];
    }
}

