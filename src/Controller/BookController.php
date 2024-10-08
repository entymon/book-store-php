<?php

namespace App\Controller;

use App\Service\FileUploader;
use Psr\Log\LoggerInterface;
use App\Entity\Book;
use App\Repository\BookRepository;
use App\Service\BookService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends AbstractController
{    

    const NUMBER_ON_PAGE = 10;

    #[Route('/books/{page}', name: 'list_books', methods:['get'] )]
    public function index(BookRepository $bookRepository, int $page = 1): JsonResponse
    {
        $countBooks = $bookRepository->countBooks();
        $books = $bookRepository->getBooks(self::NUMBER_ON_PAGE, ($page - 1) * self::NUMBER_ON_PAGE);
        $data = [];
        // http://localhost:8100/images/cat-1.jpg

        foreach ($books as $book) {
           $data[] = $this->shapeResponse($book);
        }
    
        return $this->json([
            'data' => $data,
            'total' => $countBooks,
            'page' => $page,
            'numberOnPage' => self::NUMBER_ON_PAGE
        ]);
    }
    
    #[Route('/book', name: 'book_create', methods:['post'] )]
    public function create(
        EntityManagerInterface $entityManager, 
        Request $request, 
        LoggerInterface $logger,
        BookService $bookService
    ): JsonResponse 
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            $bookService->simpleDataValdiation($parameters);

            $book = $entityManager->getRepository(Book::class)->findBy(['title' => $parameters['title']]);
            if ($book) {
                return $this->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'book found for title ' . $parameters['title']
                ], Response::HTTP_BAD_REQUEST);
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
  
    #[Route('/book/{id}', name: 'book_show', requirements: ['id' => '\d+'], methods:['get'] )]
    public function show(EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);
    
        if (!$book) {
    
            return $this->json('No book found for id ' . $id, 404);
        }
    
        $data =  $this->shapeResponse($book);
            
        return $this->json($data);
    }
  
    #[Route('/book/{id}', name: 'book_update', requirements: ['id' => '\d+'], methods:['put', 'patch'] )]
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
  
    #[Route('/book/{id}', name: 'book_delete', requirements: ['id' => '\d+'], methods:['delete'] )]
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

    #[Route('/upload/{bookId}', name: 'book_upload_cover_photo', requirements: ['bookId' => '\d+'], methods:['POST'] )]
    public function upload(
        EntityManagerInterface $entityManager, 
        FileUploader $fileUploader, 
        Request $request,
        BookService $bookService,
        int $bookId
    ): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($bookId);
        if (!$book) {
            return $this->json('No book found for id ' . $bookId, 404);
        }
        
        $coverPhoto = $request->files->get('url_image');
        
        if ($coverPhoto) {
            $coverPhotoFileName = $fileUploader->upload($coverPhoto, $bookId);

            $fullPath = $bookService->getImagePath($coverPhotoFileName);
            $book->setCoverPhoto($fullPath);
        }

        $entityManager->persist($book);
        $entityManager->flush();

    
        return $this->json('Image uploaded with success ' . $bookId);
    }

    #[Route('/search/{phrase}', name: 'search_book', methods:['GET'] )]
    public function search(
        BookRepository $bookRepository, string $phrase = ''
    ): JsonResponse
    {
        $books = $bookRepository->searchByAuthorAndTitle($phrase);
        $data = [];

        foreach ($books as $book) {
            $data[] = $this->shapeResponse($book);
        }
            
        return $this->json($data);
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
