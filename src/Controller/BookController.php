<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends AbstractController
{
    #[Route('/books', name: 'list_books', methods:['get'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $books = $entityManager
            ->getRepository(Book::class)
            ->findAll();

        $data = [];
    
        foreach ($books as $book) {
           $data[] = $this->shapeResponse($book);
        }
    
        return $this->json($data);
    }

    #[Route('/books', name: 'book_create', methods:['post'] )]
    public function create(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);
        // $bookValidator = $this->get('app.validation_iban');
        // var_dump($parameters); die;

        $book = new Book();
        $book->setTitle($parameters['title']);
        $book->setAuthor($parameters['author']);
        $book->setDescription($parameters['description']);
        $book->setPublishDate(new \DateTime($parameters['publishDate']));
        $book->setIsbn($parameters['isbn']);
        $book->setCoverPhoto($parameters['coverPhoto']);
    
        $entityManager->persist($book);
        $entityManager->flush();
    
        $data =  $this->shapeResponse($book);

            
        return $this->json($data);
    }
  
    #[Route('/books/{id}', name: 'book_show', methods:['get'] )]
    public function show(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);
    
        if (!$book) {
    
            return $this->json('No book found for id ' . $id, 404);
        }
    
        $data =  $this->shapeResponse($book);
            
        return $this->json($data);
    }
  
    #[Route('/books/{id}', name: 'book_update', methods:['put', 'patch'] )]
    public function update(EntityManagerInterface $entityManager, Request $request, int $id): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);
    
        if (!$book) {
            return $this->json('No book found for id ' . $id, 404);
        }

        $parameters = json_decode($request->getContent(), true);

        if (isset($parameters['title'])) { $book->setTitle($parameters['title']); }
        if (isset($parameters['author'])) { $book->setAuthor($parameters['author']); }
        if (isset($parameters['description'])) { $book->setDescription($parameters['description']); }
        if (isset($parameters['publishDate'])) { $book->setPublishDate(new \DateTime($parameters['publishDate'])); }
        if (isset($parameters['isbn'])) { $book->setIsbn($parameters['isbn']); }
        if (isset($parameters['coverPhoto'])) { $book->setCoverPhoto($parameters['coverPhoto']); }
        $entityManager->flush();
    
        $data =  $this->shapeResponse($book);
            
        return $this->json($data);
    }
  
    #[Route('/books/{id}', name: 'book_delete', methods:['delete'] )]
    public function delete(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);
    
        if (!$book) {
            return $this->json('No book found for id ' . $id, 404);
        }
    
        $entityManager->remove($book);
        $entityManager->flush();
    
        return $this->json('Deleted a book successfully with id ' . $id);
    }

    private function shapeResponse(Book $book): Array
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
