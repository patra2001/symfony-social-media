<?php
namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/get-copied-data/{entity}/{id}', name: 'api_copy_data')]
    public function getDataold(string $entity, int $id, EntityManagerInterface $em): JsonResponse
    {
        $entityClass = "App\\Entity\\" . $entity;
        if (!class_exists($entityClass)) {
            return new JsonResponse(['error' => 'Invalid entity'], 400);
        }
        $record = $em->getRepository($entityClass)->find($id);
        if (!$record) {
            return new JsonResponse(['error' => 'Record Not found'], 404);
        }

        $reflection = new \ReflectionClass($record); // Dynamically get all data using reflection

        $data = [];
        $excludedMethods = ['getId', 'getPassword', 'getEmailVerificationToken', 'getClientsMissionData']; // can be exclude data
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();
            if ($method->getNumberOfRequiredParameters() === 0 &&
                (str_starts_with($name, 'get') || str_starts_with($name, 'is')) &&
                !in_array($name, $excludedMethods)) {
                $key = lcfirst(str_starts_with($name, 'get') ? substr($name, 3) : substr($name, 2));
                $value = $record->$name();
                    $data[$key] = $value;
                
            }
        }
        return new JsonResponse($data);
    }


        #[Route('/get-copied-data/{entity}/{id}', name: 'api_copy_data')]
    public function getData(string $entity, int $id, EntityManagerInterface $em): JsonResponse
    {
        $entity = strtolower($entity);
        $output = '';
        $entityClass = "App\\Entity\\" . $entity;

        if (!class_exists($entityClass)) {return new JsonResponse(['error' => 'Invalid entity'], 400);}
        $data = $em->getRepository($entityClass)->find($id);
        if (!$data) {return new JsonResponse(['error' => 'User not found'], 404);}
        switch ($entity) {
        case 'user':
            $output .= "Name: " . $data->getFirstName() . " " . $data->getLastName() . PHP_EOL;
            $output .= "Email: " . $data->getEmail() . PHP_EOL;
            $output .= "Joined: " . $data->getCreatedAt()->format('Y-m-d') . PHP_EOL;
            break;

        case 'post':
            $output .= "Title: " . $data->getTitle() . PHP_EOL;
            $output .= "Content: " . $data->getContent() . PHP_EOL;
            break;
    }

    return new JsonResponse(['data' => $output]);
    }

}