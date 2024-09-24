<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

final class BookCreatedListener
{
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    #[AsEventListener(event: KernelEvents::RESPONSE)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        $filesystem = $this->fileSystem();
        $request = $event->getRequest();
        $content = $event->getResponse()->getContent();

        $method = $request->getMethod();
        $path = $request->getPathInfo();

        $object = json_decode($content, true);

        if ($method === 'POST' && $path === '/book' && !isset($object['status'])) {
            $filesystem->appendToFile($this->projectDir.'/bookLog/book.txt', "Book with ID: " . $object['id'] . "was saved with success\n", true);
        }
    }

    private function fileSystem() 
    {
        $filesystem = new Filesystem();
        try {
            if (!$filesystem->exists($this->projectDir.'/bookLog')) {
                $filesystem->mkdir(
                    Path::normalize($this->projectDir.'/bookLog'),
                );
            }
            return $filesystem;
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at ".$exception->getPath();
        }
    }
}
