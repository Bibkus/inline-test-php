<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request, CommentRepository $commentRepository): Response
    {
        $text = $request->query->get('text');
        if (strlen($text) < 3) {
            throw $this->createNotFoundException("Введено меньше 3 символов");
        }
        $posts = $commentRepository->findBySubstring($text);
        return $this->render('search.twig', ['title' => "res", 'posts' => $posts]);
    }

}