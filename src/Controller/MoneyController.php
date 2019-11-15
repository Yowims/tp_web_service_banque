<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class MoneyController extends AbstractController
{
    /**
     * @Route("api/deposit", methods={"POST"})
     */
    function Deposit(Request $request)
    {
        $jwt = $request->request->get('jwt');
        $montant = $request->request->get('montant');

        $userRepository = $this->getDoctrine()->getRepository(Users::class);

        $payload = explode('.', $jwt);

        $user = base64_decode($payload[1]);

        $id = json_decode($user, true);

        $myUser = $userRepository->findByLogin($id['login']);

        $myUser[0]->setBalance(strval(intval($myUser[0]->getBalance()) + intval($request->request->get('montant'))));

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($myUser[0]);
        $entityManager->flush();

        $entityManager->clear();

        $modifiedUser = $userRepository->findByLogin($id['login']);

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($modifiedUser[0], 'json');

        return new Response(
            $jsonContent,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    /**
     * @Route("api/withdraw", methods={"POST"})
     */
    function Withdraw(Request $request)
    {
        $jwt = $request->request->get('jwt');
        $montant = $request->request->get('montant');

        $userRepository = $this->getDoctrine()->getRepository(Users::class);

        $payload = explode('.', $jwt);

        $user = base64_decode($payload[1]);

        $id = json_decode($user, true);

        $myUser = $userRepository->findByLogin($id['login']);

        $myUser[0]->setBalance(strval(intval($myUser[0]->getBalance()) - intval($request->request->get('montant'))));

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($myUser[0]);
        $entityManager->flush();

        $entityManager->clear();

        $modifiedUser = $userRepository->findByLogin($id['login']);

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($modifiedUser[0], 'json');

        return new Response(
            $jsonContent,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }
}