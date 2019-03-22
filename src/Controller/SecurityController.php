<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/registration", name="app_registration")
     */
    public function registration(Request $request, UserPasswordEncoderInterface $passwordEncoder, \Swift_Mailer $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $body = $this->generateUrl('active_account', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);
            $message = (new \Swift_Message('Active account'))
                ->setFrom('mhdwhm88@gmail.com')
                ->setTo($user->getEmail())
                ->setBody($body);
            $mailer->send($message);
            return $this->redirectToRoute('app_login');
        }
        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
//        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    /**
     * @Route("/active-account/{token}", name="active_account", methods={"GET"})
     */
    public function activeAccount($token, UserRepository $userRepository, ObjectManager $manager): Response
    {
        $user = $userRepository->findOneBy(['token' => $token]);
        $user->setIsActive(true);
        $manager->flush();
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/email-forget-password", name="email_forget_password", methods={"GET"})
     */
    public function emailForgetPassword(UserRepository $userRepository, Request $request, ObjectManager $manager, \Swift_Mailer $mailer,Environment $environment): Response
    {
        $email = $request->get('email');
        if ($email) {
            $user = $userRepository->findOneBy(['email' => $email]);
            if (!$user) {
                dd("'user n'exist pas");
            } else {
                $user->setToken(md5(random_bytes(10)));
                $manager->flush();
                $link = $this->generateUrl('change_password', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);
                $message = (new \Swift_Message('change password'))
                    ->setFrom('mhdwhm88@gmail.com')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $environment->render('email/registration.html.twig',['link'=>$link]),
                        'text/html'
                    );
                $mailer->send($message);
                return new Response("un email a été envoyé a votre adresse email pour changer votre mot de passe ");
            }
        }
            return $this->render('security/forget_password.html.twig');
    }

    /**
     * @Route("/change-password/{token}", name="change_password")
     */
    public function ChangePassword(UserRepository $userRepository, Request $request, $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $userRepository->findOneBy(['token' => $token]);
        if ($user) {
            $form = $this->createForm(ChangePasswordType::class);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $password = $passwordEncoder->encodePassword($user, $form->getData()['password']);
                $user->setPassword($password);
                $entityManager->flush();
                return $this->redirectToRoute('app_login');
            }
            return $this->render('security/change_password.twig', [
                'form' => $form->createView(),
            ]);
        } else {
            return new Response('error token');
        }
    }


}
