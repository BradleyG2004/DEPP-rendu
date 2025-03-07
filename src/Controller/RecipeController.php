<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RecipeController extends AbstractController{
    #[Route('/recipe', name: 'app_recipe')]
    public function index(Request $request): Response
    {
        return $this->render('recipe/index.html.twig'); 
    }

    #[Route('/recipe/{slug}-{id}', name: 'app_recipe.show',requirements:['id'=>'\d+','slug'=>'[A-Za-z0-9-]+'])]
    public function show(Request $request,string $slug,int $id): Response
    {
        return $this->render('recipe/show.html.twig',[
            'slug'=>$slug ,
            'id'=>$id,
            'html'=>'<br>',
            'person'=>[
                'name'=>'boojack',
                'surname'=>'horseman'
            ]
        ]); 

    }
}
