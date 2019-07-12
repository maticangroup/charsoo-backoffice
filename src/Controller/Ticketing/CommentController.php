<?php

namespace App\Controller\Ticketing;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ticketing/comment", name="ticketing_comment")
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/list", name="_ticketing_comment_list")
     */
    public function fetchAll()
    {
        return $this->render('ticketing/comment/list.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    /**
     * @Route("/read", name="_ticketing_comment_read")
     */
    public function read()
    {
        return $this->render('ticketing/comment/read.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    public function addComment(){

    }

    public function approveComment(){

    }

    public function spamComment(){

    }

    public function readComment(){

    }
}
