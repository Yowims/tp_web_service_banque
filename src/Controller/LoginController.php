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


class LoginController extends AbstractController {

    /**
     * @Route("api/jwt", methods={"POST"})
     */
    function GenerateJWT(Request $request)
    {
        $userRepository = $this->getDoctrine()->getRepository(Users::class);

        $user = $userRepository->findByLogin($request->request->get('login'));

        if($user[0]->getLogin() == $request->request->get('login') && $user[0]->getPinCode() == $request->request->get('password'))
        {
            $user_data = array(
                'login' => $user[0]->getLogin(),
                'pin_code' => $user[0]->getPinCode(),
                'expiration_date' => date('Y-m-d H:i:s', strtotime("+3 hours"))

            );
            $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

            $payload = json_encode($user_data);

            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'abC123!', true);

            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

            $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        }

        $response = new Response(
            $jwt,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );

        $response->headers->setCookie(Cookie::create('jwt', $jwt));

        return $response;
    }

    /**
     * @Route("api/login", methods={"POST"})
     */
    function Login(Request $request)
    {
        $MyJwt = $this->GenerateJWT($request);

        return $this->render('login/login_done.html.twig');

    }

    /**
     * @Route("login", name="login")
     */
    public function loginView()
    {
        return $this->render('login/login.html.twig');
    }

}