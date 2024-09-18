<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Book;
use App\Service\BookSevice;

class BookController extends AbstractController
{
    #[Route('/books', name: 'book_index', methods:['get'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $books = $entityManager
            ->getRepository(Book::class)
            ->findAll();
    
        $data = [];
    
        foreach ($books as $book) {
           $data[] = \BookSevice::shapeResponse($book);
        }
    
        return $this->json($data);
    }

    #[Route('/books', name: 'book_create', methods:['post'] )]
    public function create(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $book = new Book();
        $book->setTitle($request->request->get('title'));
        $book->setAuthor($request->request->get('author'));
        $book->setDescription($request->request->get('description'));
        $book->setPublishDate(new \DateTime($request->request->get('publishDate')));
        $book->setIsbn($request->request->get('isbn'));
        $book->setCoverPhoto($request->request->get('coverPhoto'));
    
        $entityManager->persist($book);
        $entityManager->flush();
    
        $data =  \BookSevice::shapeResponse($book);
            
        return $this->json($data);
    }
  
  
    #[Route('/books/{id}', name: 'book_show', methods:['get'] )]
    public function show(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);
    
        if (!$book) {
    
            return $this->json('No book found for id ' . $id, 404);
        }
    
        $data =  \BookSevice::shapeResponse($book);
            
        return $this->json($data);
    }
  
    #[Route('/books/{id}', name: 'book_update', methods:['put', 'patch'] )]
    public function update(EntityManagerInterface $entityManager, Request $request, int $id): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);
    
        if (!$book) {
            return $this->json('No book found for id ' . $id, 404);
        }
    
        $book->setTitle($request->request->get('title'));
        $book->setAuthor($request->request->get('author'));
        $book->setDescription($request->request->get('description'));
        $book->setPublishDate(new \DateTime($request->request->get('publishDate')));
        $book->setIsbn($request->request->get('isbn'));
        $book->setCoverPhoto($request->request->get('coverPhoto'));
        $entityManager->flush();
    
        $data =  \BookSevice::shapeResponse($book);
            
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
}
