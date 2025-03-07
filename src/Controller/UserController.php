<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends AbstractController
{
    #[Route('/login', name: 'login-page')]
    public function loginForm(): Response
    {
        return $this->render('user/login.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/register', name: 'register-page')]
    public function registerForm(): Response
    {
        return $this->render('user/register.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/registerr', name: 'registerr', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Récupération des données du formulaire
        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $role = $request->request->get('role');

        // Vérification des champs requis
        if (!$username || !$email || !$password) {
            $this->addFlash('danger', 'Tous les champs sont obligatoires.');
            return $this->redirectToRoute('register-page');
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles([$role]);

        // Hachage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Sauvegarde en base
        $em->persist($user);
        $em->flush();

        // Message de succès
        $this->addFlash('success', 'Compte créé avec succès ! Vous pouvez vous connecter.');

        return $this->redirectToRoute('login-page');
    }

    #[Route('/loginn', name: 'loginn')]
    public function login(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder): Response
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        // Vérification des champs requis
        if (!$username || !$password) {
            $this->addFlash('danger', 'Tous les champs sont obligatoires.');
            return $this->redirectToRoute('login-page');
        }

        // Récupérer l'utilisateur en base
        $user = $em->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user || !$passwordEncoder->isPasswordValid($user, $password)) {
            
            return $this->render('user/login.html.twig', [
                'error' => 'Invalid username or password.'
            ]);
        }

        // Créer un token d'authentification
        $token = bin2hex(random_bytes(32)); // Générer un token unique
        $expiresAt = new \DateTime('+1 hour'); // Expiration du token dans 1 heure

        // Créer une nouvelle session pour l'utilisateur
        $userSession = new UserSession();
        $userSession->setUser($user);
        $userSession->setToken($token);
        $userSession->setExpiresAt($expiresAt);

        $em->persist($userSession);
        $em->flush();

        // Créer un cookie sécurisé avec le token
        $cookie = new Cookie(
            'auth_token', // Nom du cookie
            $token, // Valeur du token
            $expiresAt, // Date d'expiration
            '/', // Path (accessible dans toute l'application)
            null, // Domaine (null pour utiliser le domaine courant)
            true, // Secure (toujours envoyé via HTTPS)
            true, // HttpOnly (non accessible via JavaScript)
            false, // SameSite (peut être 'Strict', 'Lax', ou 'None')
        );

        // Ajouter le cookie à la réponse
        $response = $this->redirectToRoute('dashboard');
        $response->headers->setCookie($cookie);

        // Message de connexion réussie
        $this->addFlash('success', 'Vous êtes maintenant connecté !');

        return $response;
    }

    #[Route('/update', name: 'update', methods: ['POST'])] 
    public function update(Request $request, RequestStack $requestStack, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder): Response
    {
        // Récupérer la session via RequestStack
        $session = $requestStack->getSession();
        // Récupérer le token du cookie
        $token = $request->cookies->get('auth_token');
        if (!$token) {
            return new Response('Non autorisé', Response::HTTP_UNAUTHORIZED);
        }

        // Trouver la session utilisateur associée au token
        $userSession = $em->getRepository(UserSession::class)->findOneBy(['token' => $token]);

        if (!$userSession || $userSession->getExpiresAt() < new \DateTime()) {
            return new Response('Session expirée ou invalide', Response::HTTP_UNAUTHORIZED);
        }

        // Récupérer l'utilisateur connecté
        $user = $userSession->getUser();
        if (!$user) {
            return new Response('Utilisateur introuvable', Response::HTTP_NOT_FOUND);
        }

        // Récupérer les données du formulaire
        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $role = $request->request->get('role');

        // Mettre à jour l'utilisateur
        if ($username) {
            $user->setUsername($username);
        }
        if ($email) {
            $user->setEmail($email);
        }
        if ($password) {
            $hashedPassword = $passwordEncoder->hashPassword($user, $password);
            $user->setPassword($hashedPassword);
        }
        if ($role) {
            $user->setRoles([$role]);
        }

        try {
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Une erreur est survenue. Veuillez réessayer.');
        }
    
        return $this->redirectToRoute('dashboard');
    }

    #[Route('/delete', name: 'delete')]
    public function delete(Request $request, EntityManagerInterface $em, RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();

        // Récupérer le token du cookie
        $token = $request->cookies->get('auth_token');
        if (!$token) {
            return new Response('Non autorisé', Response::HTTP_UNAUTHORIZED);
        }

        // Trouver la session utilisateur associée au token
        $userSession = $em->getRepository(UserSession::class)->findOneBy(['token' => $token]);

        if (!$userSession || $userSession->getExpiresAt() < new \DateTime()) {
            return new Response('Session expirée ou invalide', Response::HTTP_UNAUTHORIZED);
        }

        // Récupérer l'utilisateur
        $user = $userSession->getUser();
        if (!$user) {
            return new Response('Utilisateur introuvable', Response::HTTP_NOT_FOUND);
        }

        try {
            // Supprimer la session utilisateur
            $em->remove($userSession);
            
            // Supprimer l'utilisateur (et tout ce qui en dépend en cascade)
            $em->remove($user);
            $em->flush();

            // Supprimer le cookie
            $response = $this->redirectToRoute('login-page');
            $response->headers->clearCookie('auth_token');

            return $response;
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression du compte.');
            return $this->redirectToRoute('dashboard');
        }
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request, RequestStack $requestStack, EntityManagerInterface $em): Response
    {
        $session = $requestStack->getSession();
        
        // Récupérer le token stocké dans le cookie
        $token = $request->cookies->get('auth_token');

        if ($token) {
            // Trouver la session utilisateur associée au token
            $userSession = $em->getRepository(UserSession::class)->findOneBy(['token' => $token]);

            if ($userSession) {
                $em->remove($userSession);
                $em->flush();
            }
        }

        // Supprimer le cookie en mettant une date d'expiration passée
        $response = $this->redirectToRoute('login-page');
        $response->headers->clearCookie('auth_token');


        return $response;
    }
}
