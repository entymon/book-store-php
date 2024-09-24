<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class AppFixtures extends Fixture
{
    private $authors = [
        'Jordan',
        'Pawel',
        'Agatha',
        'Judy',
        'Robert',
        'Alex',
        'Neron',
        'Jose',
        'Maria',
    ];

    public static function getGroups(): array
    {
        return ['group1'];
    }

    public function load(ObjectManager $manager): void
    {     
        for ($n = 0; $n <= 20000; $n++) {
            if ($n % 1000 === 0) {
                echo "Start loop: ". $n . "\n";
            }

            for ($i = 0; $i <= 25; $i++) {
                $author = $this->authors[rand(0, count($this->authors) - 1)];
                $date = new \DateTimeImmutable('2000-01-01');

                $book = new Book();
                $book->setTitle('Kubuś Puchatek nr'. uniqid());
                $book->setAuthor($author);
                $book->setDescription('A pure book of drummers turned singers! author of the: '. $author);
                $book->setPublishDate($date);
                $book->setIsbn('9781472911292');
                $book->setCoverPhoto('http://localhost:8100/images/cat-1.jpg');
                $manager->persist($book);
            }

            $manager->flush();
            $manager->clear();
        }
    }
}
