<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ContactType;
use App\Entity\Enquiry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\EnquiryRepository;
use Psr\Log\LoggerInterface;

class FormController extends AbstractController
{
    #[Route('/form', name: 'app_form')]
    public function index(Request $request, EntityManagerInterface $em, MailerInterface $mailer, SluggerInterface $slugger, LoggerInterface $logger): Response
    {
        try {
            $enquiry = new Enquiry();
            $form = $this->createForm(ContactType::class, $enquiry);
            $form->handleRequest($request);

// dump($form->getData()); // Shows data bound to the entity
// dump($form->getErrors(true)); // Shows validation errors
// dump($request->request->all()); // Shows raw submitted POST data
// die;
            if ($form->isSubmitted() && $form->isValid()) { 
            // Handle uploaded profile file (if any)
            $profileFile = $form->get('profile')->getData();

                if ($profileFile) {
                $originalFilename = pathinfo($profileFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$profileFile->guessExtension();

                $uploadDir = $this->getParameter('kernel.project_dir').'/public/assets/profiles';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }

                    try {
                        $profileFile->move($uploadDir, $newFilename);
                        $enquiry->setProfile($newFilename);
                    } catch (\Exception $e) {
                        $logger->error('Profile upload failed', ['exception' => $e]);
                        $this->addFlash('danger', 'Failed to upload profile file.');
                        return $this->redirectToRoute('app_form');
                    }
            }

            // Persist the enquiry
            $em->persist($enquiry);
            $em->flush();

            // Send admin notification
            $adminEmail = 'admin@example.com'; //has to be changed
            $adminMessage = (new TemplatedEmail())
                ->from(new Address('no-reply@example.com', 'Website'))
                ->to($adminEmail)
                ->subject('New enquiry submitted')
                ->htmlTemplate('emails/enquiry_admin.html.twig')
                ->context([
                    'enquiry' => $enquiry,
                ]);

            $mailer->send($adminMessage);

            // Optionally send confirmation to user if email provided
            if ($enquiry->getEmail()) {
                $userMessage = (new TemplatedEmail())
                    ->from(new Address('no-reply@example.com', 'Website'))
                    ->to($enquiry->getEmail())
                    ->subject('We received your enquiry')
                    ->htmlTemplate('emails/enquiry_user.html.twig')
                    ->context([
                        'enquiry' => $enquiry,
                    ]);

                $mailer->send($userMessage);
            }

            $this->addFlash('success', 'Your enquiry has been stored in the database.');
            // Redirect to avoid re-post on refresh
            return $this->redirectToRoute('app_form');
        }

        return $this->render('form/index.html.twig', [
            'form' => $form->createView(),
        ]);
        } catch (\Exception  $e) {
            $logger->error('Unexpected error in FormController::index', ['exception' => $e]);
            $this->addFlash('danger', 'An unexpected error occurred. Please try again later.');
            return $this->redirectToRoute('app_form');
        }
    }

    #[Route('/contacts', name: 'contact_list')]
    public function contactList(EnquiryRepository $repo, LoggerInterface $logger): Response
    {
        try {
            $enquiries = $repo->findBy([], ['id' => 'DESC']);

            return $this->render('form/list.html.twig', [
                'enquiries' => $enquiries,
            ]);
        } catch (\Exception  $e) {
            $logger->error('Error loading contact list', ['exception' => $e]);
            $this->addFlash('danger', 'Could not load contact list.');
            return $this->redirectToRoute('app_form');
        }
    }

    #[Route('/contacts/{id}/edit', name: 'contact_edit')]
    public function edit(Request $request, int $id, EnquiryRepository $repo, EntityManagerInterface $em, SluggerInterface $slugger, LoggerInterface $logger): Response
    {
        try {
            $enquiry = $repo->find($id);
            if (!$enquiry) {
                throw $this->createNotFoundException('Enquiry not found.');
            }

            $form = $this->createForm(ContactType::class, $enquiry);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var UploadedFile|null $profileFile */
                $profileFile = $form->get('profile')->getData();

                if ($profileFile) {
                    // remove old file if any
                    $old = $enquiry->getProfile();
                    if ($old) {
                        $oldPath = $this->getParameter('kernel.project_dir').'/public/assets/profiles/'.$old;
                        if (is_file($oldPath)) {
                            @unlink($oldPath);
                        }
                    }

                    $originalFilename = pathinfo($profileFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$profileFile->guessExtension();

                    $uploadDir = $this->getParameter('kernel.project_dir').'/public/assets/profiles';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }

                    try {
                        $profileFile->move($uploadDir, $newFilename);
                        $enquiry->setProfile($newFilename);
                    } catch (\Exception $e) {
                        $logger->error('Profile upload failed (edit)', ['exception' => $e]);
                        $this->addFlash('danger', 'Failed to upload profile file.');
                        return $this->redirectToRoute('contact_edit', ['id' => $enquiry->getId()]);
                    }
                }

                $em->flush();
                $this->addFlash('success', 'Enquiry updated.');

                return $this->redirectToRoute('contact_list');
            }

            return $this->render('form/edit.html.twig', [
                'form' => $form->createView(),
                'enquiry' => $enquiry,
            ]);
        } catch (\Exception  $e) {
            $logger->error('Error in FormController::edit', ['exception' => $e, 'id' => $id]);
            $this->addFlash('danger', 'An error occurred while editing the enquiry.');
            return $this->redirectToRoute('contact_list');
        }
    }

    #[Route('/contacts/{id}/delete', name: 'contact_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EnquiryRepository $repo, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        try {
            $enquiry = $repo->find($id);
            if (!$enquiry) {
                $this->addFlash('danger', 'Enquiry not found.');
                return $this->redirectToRoute('contact_list');
            }

            if ($this->isCsrfTokenValid('delete'.$enquiry->getId(), $request->request->get('_token'))) {
                // remove uploaded file
                $filename = $enquiry->getProfile();
                if ($filename) {
                    $path = $this->getParameter('kernel.project_dir').'/public/assets/profiles/'.$filename;
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }

                $em->remove($enquiry); //remove the record from database 
                $em->flush(); //save all changes to database
                $this->addFlash('success', 'Enquiry deleted.');
            }

            return $this->redirectToRoute('contact_list');
        } catch (\Exception  $e) {
            $logger->error('Error deleting enquiry', ['exception' => $e, 'id' => $id]);
            $this->addFlash('danger', 'Could not delete enquiry.');
            return $this->redirectToRoute('contact_list');
        }
    }
}
