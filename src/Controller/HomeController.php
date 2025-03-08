<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\UserSession;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController{
    #[Route('/', name: 'dashboard')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->cookies->get('auth_token');
        if (!$token) {
            return $this->redirectToRoute('login-page');
        }
        $userSession = $em->getRepository(UserSession::class)->findOneBy(['token' => $token]);

        if (!$userSession || $userSession->getExpiresAt() < new \DateTime()) {
            return $this->redirectToRoute('login-page');
        }
        $user = $userSession->getUser();
        if (!$user) {
            return new Response('Utilisateur introuvable', Response::HTTP_NOT_FOUND);
        }
        return $this->render('home/dashboard.html.twig', [
            'user' => $user
        ]); 
    }
}
