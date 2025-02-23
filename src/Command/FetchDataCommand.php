<?php

namespace App\Command;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:fetch-data',
    description: 'Add a short description for your command',
)]
class FetchDataCommand extends Command
{
    private $client;
    private $entityManager;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Импорт данных из API в базу данных.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $response = $this->client->request('GET', 'https://jsonplaceholder.typicode.com/posts');
        $posts = $response->toArray();
        $response = $this->client->request('GET', 'https://jsonplaceholder.typicode.com/comments');
        $comments = $response->toArray();
        $postCount = 0;
        $commentCount = 0;

        foreach ($posts as $postData) {
            if ($this->entityManager->getRepository(Post::class)->find($postData['id']) !== null) {
                $io->warning("пост с id ". $postData['id']. " уже существует");
                continue;
            }
            $post = new Post();
            $post->setId($postData['id']);
            $post->setTitle($postData['title']);
            $post->setUserId($postData['userId']);
            $post->setBody($postData['body']);
            $this->entityManager->persist($post);
            $postCount++;
        }

        $this->entityManager->flush();

        foreach ($comments as $commentData) {
            if ($this->entityManager->getRepository(Comment::class)->find($commentData['id']) !== null) {
                $io->warning("коментарий с id ". $commentData['id']. " уже существует");
                continue;
            }
            $post = $this->entityManager->getRepository(Post::class)->find($commentData['postId']);
            $comment = new Comment();
            $comment->setId($commentData['id']);
            $comment->setName($commentData['name']);
            $comment->setPost($post);
            $comment->setEmail($commentData['email']);
            $comment->setBody($commentData['body']);
            $this->entityManager->persist($comment);
            $commentCount++;
        }

        $this->entityManager->flush();

        $io->success("Загружено " . $postCount . " записей и " . $commentCount . " комментариев");

        return Command::SUCCESS;
    }
}
