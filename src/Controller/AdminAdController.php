<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AdType;
use App\Repository\AdRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    /**
     * @Route("/admin/ads/{page<\d+>?1}", name="admin_ads_index")
     */
    public function index(AdRepository $repo, $page, Pagination $pagination)
    {

        // $ad = $repo->find(5);
        // $ad = $repo->findOneBy([
        //     'title' => 'Dolor ullam at tempore quis aperiam a.'
        // ]);
        // dump($ad);
        // $ads = $repo->findBy([], [], 5, 10);
        // dump($ads);
        $pagination->setEntityClass(Ad::class)->setLimit(10)->setPage($page);
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'User tried to access a page without having ROLE_ADMIN');

        return $this->render('admin/ad/index.html.twig', [
           'pagination' => $pagination
        ]);
    }

    /**
     * Grants ad edition
     * @Route("/admin/ads/{id}/edit", name="admin_ads_edit")
     * @param Ad $ad
     * @return Response
     */
    public function edit (Ad $ad,  Request $request, EntityManagerInterface $manager) {
        $form = $this->createForm(AdType::class, $ad);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée !"
            );

        }
        return $this->render('admin/ad/edit.html.twig', [
            'ad' => $ad,
            'form' => $form->createView()
        ]);
    }

    /**
     * Grants ad deletion
     *
     * @Route("/admin/ads/{id}/delete", name="admin_ads_delete")
     * 
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function delete(Ad $ad, EntityManagerInterface $manager) {
        
        if (count($ad->getBookings()) > 0) {
            $this->addFlash(
                'warning',
                "Vous ne pouvez pas supprimer l'annonce <strong>{$ad->getTitle()}</strong>, car elle possède déjà des réservations !"
            );
        } else {
            $manager->remove($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce a bien été supprimée !"
            );
        } 
        return $this->redirectToRoute('admin_ads_index');
    }
}
