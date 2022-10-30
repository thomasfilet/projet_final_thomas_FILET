<?php


namespace App\Controller;
use App\Entity\Montre;
use App\Form\Type\MontreTypes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\MontreRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



class ProjetFinalController extends AbstractController
{

    ##################################
    # controller User + Info Product #
    ##################################

    ###########Controller de la page User############
    #[Route('/', name: 'app_homepageUser')]
    public function HomepageUser(MontreRepository $MontreRepository): Response
    {
        //La variable $all devient une liste contenant tout les objet mis en base de donnée
        $all = $MontreRepository->findBy(array(), array('id' => 'DESC'));

        return $this->render('projet_final/HomepageUser.html.twig', [
        "all" => $all,]);

        
    }




    ###################Controller de la page d'administrateur##################
    #[IsGranted('ROLE_USER')]
    #[Route('/admin', name: 'app_homepageAdmin')]
    public function HomepageAdmin(MontreRepository $MontreRepository): Response
    {
    $montraAll = $MontreRepository->findAll();
    $all = $MontreRepository->findBy(array(), array('id' => 'DESC'));
    return $this->render('projet_final/HomepageAdmin.html.twig', [
        "all" => $all,
        "MontreAll" =>  $montraAll]);
    }

    



    #############################################
    # Controller  New / Delete / Update  #
    #############################################

    ############Controller de la page pour creer un produit############

    //Verifie si on a le rose User
    #[IsGranted('ROLE_USER')]
    #[Route('/product/new', name: 'app_new')]
    public function Newproduit(ManagerRegistry $doctrine, Request $request): Response
    {
        //permet de récupérer l'EntityManager par défaut en omettant l'argument $name
        //EntityManager permet de manipuler les données
        $entityManager = $doctrine->getManager();
        
        //creer une nouvelle instance de Montre
        $montre = new Montre();

        $form = $this->createForm(MontreTypes::class, $montre);

        //permet de récupérer les valeurs des champs dans les inputs du formulaire
        $form= $form->handleRequest($request);

        //SI la variable $form a appeler la fct isSubmitted() et la fct isValid()
        if ($form->isSubmitted() && $form->isValid()){

            // alors $montre recupere les data
            $montre = $form->getData(); 

            //Enregistre la variable $montre
            $entityManager -> persist($montre);

            //exécute réellement les requêtes
            $entityManager -> flush();
            return $this-> redirectToRoute('app_homepageAdmin');
        }

        return $this->renderForm('projet_final/New.html.twig', [
            'form' => $form,
        ]);
        
    }


    ##########Controller page la page d'affichage d'information de chaque montre en fonction de l'id #############
    #[Route('/product/{id}', name: 'app_montre_id')]
    public function show(string $id, ManagerRegistry $doctrine, MontreRepository $MontreRepository): Response
    {
    
        $montre = $doctrine->getRepository(Montre::class)->find($id);
        $entityManager = $doctrine->getManager();


        //permet d'afficher dans la description des produits les produit ayant la meme marque
        
        $montreSimilaire = $entityManager->getRepository(Montre::class)->find($id);
        //defini le Make
        $make = $montreSimilaire->getMake('');



        // rechercher tous les produits correspondant a la marque
        $montre_object = $MontreRepository->findBy(
        array('Make' => $make));
        return $this->render('projet_final/infoProducts.html.twig', [
            "montre" => $montre, 
            "montre_object" => $montre_object
        ]);
    }  




    ###############controller permettant de supprimer une montre en fonction de l'id #################
    #[IsGranted('ROLE_USER')]
    #[Route('product/{id}/delete', name: 'montre_delete')]
    public function delete(ManagerRegistry $doctrine, Montre $montre, string $id): Response {

        $entityManager = $doctrine->getManager();

        if (!$montre) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
        //remove() permet de supprimer un objet de la base de donnée
        $entityManager->remove($montre);

        //exécute réellement les requêtes
        $entityManager->flush();

        return $this->redirectToRoute('app_homepageAdmin');
    }




    ###############Controller de la page pour éditer un produit en fonction de l'id ################
    #[IsGranted('ROLE_USER')]
    #[Route('/product/{id}/update', name: 'montre_edit')]
    public function update(ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        $montreUpdate = $entityManager->getRepository(Montre::class)->find($id);

        if (!$montreUpdate) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        };

        //Creer le formulaire
        $form = $this->createForm(MontreTypes::class, $montreUpdate);

        //recupere les donnée qui sont dans dans les inputs du formulaire
        $form= $form->handleRequest($request);

        //SI la variable $form a appeler la fct isSubmitted() et la fct isValid()
        if ($form->isSubmitted() && $form->isValid()){

            // alors $montre recupere les data
            $montreUpdate = $form->getData();

            //Enregistre la variable $montre
            $entityManager -> persist($montreUpdate);

            //exécute réellement les requêtes
            $entityManager -> flush();

            return $this-> redirectToRoute('app_homepageAdmin',[
                'id' => $montreUpdate->getId(),
                
            ]);
        }

        return $this->renderForm('projet_final/UpdateProducts.html.twig', [
            'form' => $form,
        ]);
    }




    

    ########################################
    # Controller Login / Logout / Register #
    ########################################

    
    ###########Controller pour la page Login##########
    #[Route('/connect', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // obtenir l'erreur de connexion s'il y en a une
         $error = $authenticationUtils->getLastAuthenticationError();

         // dernier nom d'utilisateur entré par l'utilisateur
         $lastUsername = $authenticationUtils->getLastUsername();


          return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
          ]);

          
    }


    ######## permet de se deconnecter en tant qu'utilisateur########
    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout()
    {}

 
    ########### Permet de ce creer un User###############
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //encode le mot de passe simple
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            //classe générique qu'on appel pour créer un objet dans une table de la base de données
            'registrationForm' => $form->createView(),
        ]);
    }
}
