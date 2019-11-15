<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserController extends AbstractController {

    /**
     * @Route("api/users", methods={"GET"})
     */
    function GetUsers(Request $request)
    {
        $userRepository = $this->getDoctrine()->getRepository(Users::class);

        $user = $userRepository->findAll();

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($user, 'json');

        return new Response(
            $jsonContent,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    /**
     * @Route("api/user/{login}", methods={"GET"})
     */
    function GetUserByLogin(string $login)
    {
        $userRepository = $this->getDoctrine()->getRepository(Users::class);

        $user = $userRepository->findByLogin($login);
        

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($user, 'json');
        return new Response(
            $jsonContent,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    /**
     * @Route("api/new_user", methods={"POST"})
     */
    function NewUser(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $bank_id = 12345;

        $login_int = random_int(10000,99999);
        $login = $bank_id.$login_int.substr($request->request->get('prenom'),0,1).substr($request->request->get('nom'),0,1);

        $password = random_int(100000, 999999);


        $user = new Users();
        $user->setCivilite($request->request->get('civilite'));
        $user->setPrenom($request->request->get('prenom'));
        $user->setNom($request->request->get('nom'));
        $user->setBirthDate(date_create_from_format('Y-m-d', $request->request->get('birth_date')));
        $user->setAdressePostale($request->request->get('adresse_postale'));
        $user->setLogin($login);
        $user->setPinCode($password);
        $entityManager->persist($user);
        $entityManager->flush();

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($user, 'json');

        return new Response($jsonContent, Response::HTTP_CREATED);
    }

    /**
     * @Route("api/register", methods={"POST"})
     */
    function NewUserRender(Request $request)
    {
        $content = json_decode($this->NewUser($request)->getContent(), true);

        return $this->render('register/register_done.html.twig', [
            'login' => $content['login'],
            'pin_code' => $content['pinCode'],
        ]);
    }

    /**
     * @Route("register", name="register")
     */
    public function registerView()
    {
        return $this->render('register/register.html.twig');
    }

    public function registerDoneView()
    {
        return $this->render('register/register_done.html.twig');
    }
}