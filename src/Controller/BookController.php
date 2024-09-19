<?php

namespace App\Controller;

use App\Service\FileUploader;
use Psr\Log\LoggerInterface;
use App\Exception\ValidationException;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends AbstractController
{    
    #[Route('/books', name: 'list_books', methods:['get'] )]
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
    public function create(
        EntityManagerInterface $entityManager, 
        Request $request, 
        LoggerInterface $logger
    ): JsonResponse 
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            $this->simpleDataValdiation($parameters);

            $book = $entityManager->getRepository(Book::class)->findBy(['title' => $parameters['title']]);
            if ($book) {
                return $this->json('book found for title ' . $parameters['title'], 400);
            }
    
            $book = new Book();
            $book->setTitle($parameters['title']);
            $book->setAuthor($parameters['author']);
            $book->setPublishDate(new \DateTime($parameters['publishDate']));
            $book->setIsbn($parameters['isbn']);
    
            if (isset($parameters['description'])) { $book->setDescription($parameters['description']); }
        
            $entityManager->persist($book);
            $entityManager->flush();
        
            $data =  $this->shapeResponse($book);
    
                
            return $this->json($data);
        } catch (\Exception $ex) {
            $logger->warning('error while adding new book ' . $ex->getMessage());
            if (get_class($ex) === 'App\Exception\ValidationException') {
                return $this->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'errors' => json_decode($ex->getMessage()),
                ], Response::HTTP_BAD_REQUEST);
            }
            return $this->json('There is a problem with page. Please contact with support 666-666-666', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

    #[Route('/books/{id}/upload', name: 'book_upload_cover_photo', methods:['POST'] )]
    public function upload(
        EntityManagerInterface $entityManager, 
        FileUploader $fileUploader, 
        Request $request,
        int $id
    ): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);
        if (!$book) {
            return $this->json('No book found for id ' . $id, 404);
        }
        
        $coverPhoto = $request->files->get('url_image');
        
        if ($coverPhoto) {
            $coverPhotoFileName = $fileUploader->upload($coverPhoto, $id);
            $book->setCoverPhoto($coverPhotoFileName);
        }

        $entityManager->persist($book);
        $entityManager->flush();

    
        return $this->json('Image uploaded with success ' . $id);
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

    private function simpleDataValdiation(Array $parameters): void
    {
        $errors = [];

        if (!isset($parameters['title'])) { $errors[] = 'title is required'; }
        if (!isset($parameters['author'])) { $errors[] = 'author is required'; }
        if (!isset($parameters['publishDate'])) { $errors[] = 'publish date is required'; }
        if (!isset($parameters['isbn'])) { $errors[] = 'ISBN is required'; }
        if (!isset($parameters['title'])) { $errors[] = 'Title is required'; }

        if (count($errors) > 0) {
            throw new ValidationException(json_encode($errors));
        }
    }
}
