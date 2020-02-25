<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use DateTime;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectManager;
use app\Form\ArticleType;


class BlogController extends AbstractController
{


    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('blog/home.html.twig', [
            'title' => 'Welcome to this blog',
            'age' => 31
        ]);
    }


    /**
     * @Route("/blog", name="blog")
     */
    /*
     public function index()
    {
        
        $repo = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repo->findAll();

        
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles

        ]);
    }
    */

    /* Bonus...with a dependence injection */
    /* Symfony uses its Service Container */
    public function index(ArticleRepository $repo)
    {
        
        /*$repo = $this->getDoctrine()->getRepository(Article::class);*/
        $articles = $repo->findAll();

        
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles

        ]);
    }

    /* this new function must be defined before the function show()
    *  because the controller will run the first function that match the 'route'
    * and here 'new' can be seen as an 'id' !!!!
    */
    /**
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form($id = null, Request $request) 
    {
        
        $manager = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(Article::class);

        dump($id);

        if (!$id) {
            $article = new Article();
        }
        else
            $article = $repo->find($id);

/* First methode */
/*
        $form = $this->createFormBuilder($article)
        -> add('title')
        -> add('content')
        -> add('image')
        ->getForm();
*/
        /* Second method. With CLI, it is possible to create a form. The following file is created "Form/ArticleType.php" */
        /* so we can use the following code to create the form */
        /* it is a better way to create a form because no code will be duplicated in the different functions */
        $form = $this-> createForm(ArticleType::class, $article);


        $form->handleRequest($request);
        dump($article);

        if ($form->isSubmitted() && $form->isValid()) {
            if(!$article->getId()){
                $article->setCreateAt(new \DateTime());
            }
          
            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);

        }
        
        return $this->render('blog/create.html.twig', [
            'modificationMode' => $article->getId() !== null,
            'formArticle' => $form->createView()
        ]); 
    }


    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show($id)
    {

        $repo = $this->getDoctrine()->getRepository(Article::class);
        $article = $repo->find($id);

        return $this->render('blog/show.html.twig', 
    [
        'article' => $article
    ]);
    }

  

}
