<?php

namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StudentController extends AbstractController
{
    #[Route('/student/add', name: 'student_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        try {
        $student = new Student();

        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($student);
            $em->flush();

            $this->addFlash('success', 'Student saved.');
            

            return $this->redirectToRoute('student_new');
        }

        $students = $em->getRepository(Student::class)->findAll();

        return $this->render('student/addstd.html.twig', [
            'form' => $form->createView(),
            'students' => $students,
        ]);
         } catch (\Exception  $th) {
            $this->addFlash('danger', 'An error occurred while add the student.');
            return $this->redirectToRoute('student_new');
        }
    }

    #[Route('/student/{id}/edit', name: 'student_edit')]
    public function edit(Request $request, int $id, EntityManagerInterface $em): Response
    {
        try {
            $student = $em->getRepository(Student::class)->find($id);
        if (!$student) {
            throw $this->createNotFoundException('Student not found');
        }

        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Student updated.');

            return $this->redirectToRoute('student_edit', ['id' => $student->getId()]);
        }

        return $this->render('student/edit.html.twig', [
            'form' => $form->createView(),
            'student' => $student,
        ]);
        } catch (\Exception  $th) {
            $this->addFlash('danger', 'An error occurred while editing the student.');
            return $this->redirectToRoute('student_new');
        }
        
    }

    #[Route('/student/{id}', name: 'student_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $em): Response
    {
        try{
        $student = $em->getRepository(Student::class)->find($id);
        if (!$student) {
            $this->addFlash('warning', 'Student not found.');
            return $this->redirectToRoute('student_new');
        }

        if ($this->isCsrfTokenValid('delete'.$student->getId(), $request->request->get('_token'))) {
            $em->remove($student);
            $em->flush();

            $this->addFlash('success', 'Student deleted.');
        }

        return $this->redirectToRoute('student_new');
    } catch (\Exception  $e) {
            $this->addFlash('danger', 'Could not delete student.');
            return $this->redirectToRoute('student_new');
    }
}
}
