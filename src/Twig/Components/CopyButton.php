<?php

namespace App\Twig\Components;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Doctrine\ORM\EntityManagerInterface;

#[AsTwigComponent('copy_button')]
class CopyButton
{
    public function __construct(EntityManagerInterface $em){ $this->em = $em;}

    public string $type = 'button';
    public string $class = 'btn btn-primary';
    public string $label = 'Button';
    public array $dataAttributes = [];
    public string $alertId = 'copy-alert';
    public bool $alertDismissible = true;
    public ?string $entity = null; 
    public int $entityType = 0;
    public ?string $copyText = null;
    private EntityManagerInterface $em;

    public function getCopyData(): ?string
    {
        if (!$this->entity || !$this->entityType) { return null;}
        $entity = strtolower($this->entity);
        $entityClass = "App\\Entity\\" . ucfirst($this->entity);
        if (!class_exists($entityClass)) { return null;}
        $data = $this->em->getRepository($entityClass)->find($this->entityType);
        if (!$data) {return null;}
        $output = '';
        switch ($this->entity) {
            case 'user':
                $output .= "Name: " . $data->getFirstName() . " " . $data->getLastName() . PHP_EOL;
                $output .= "Email: " . $data->getEmail() . PHP_EOL;
                $output .= "Joined: " . $data->getCreatedAt()->format('Y-m-d') . PHP_EOL;
                break;
            case 'post':
                $output .= "Title: " . $data->getTitle() . PHP_EOL;
                $output .= "Content: " . $data->getContent() . PHP_EOL;
                // Optional: add more fields if needed
                // $output .= "Likes: " . $data->getLikeCount() . PHP_EOL;
                // $output .= "Created: " . $data->getCreatedAt()->format('Y-m-d') . PHP_EOL;
                break;
            default:
                $output .= "No data available." . PHP_EOL;
                break;
        }
        $this->copyText = $output;
        return $output;
    }

    
    // public function mount(string $entity = '', int $entityType = 0): void{ //called before rendering the component
    //     if (!$entity || !$entityType) { return;}
    //     $entity = strtolower($entity);
    //     $entityClass = "App\\Entity\\" . ucfirst($entity);
    //     if (!class_exists($entityClass)) { return;}
    //     $data = $this->em->getRepository($entityClass)->find($entityType);
    //     if (!$data) {return;}
    //     $output = '';
    //     switch ($entity) {
    //         case 'user':
    //             $output .= "Name: " . $data->getFirstName() . " " . $data->getLastName() . PHP_EOL;
    //             $output .= "Email: " . $data->getEmail() . PHP_EOL;
    //             $output .= "Joined: " . $data->getCreatedAt()->format('Y-m-d') . PHP_EOL;
    //             break;
    //         case 'post':
    //             $output .= "Title: " . $data->getTitle() . PHP_EOL;
    //             $output .= "Content: " . $data->getContent() . PHP_EOL;
    //             // Optional: add more fields if needed
    //             // $output .= "Likes: " . $data->getLikeCount() . PHP_EOL;
    //             // $output .= "Created: " . $data->getCreatedAt()->format('Y-m-d') . PHP_EOL;
    //             break;
    //         default:
    //             $output .= "No data available." . PHP_EOL;
    //             break;
    //     }
    //     $this->copyText = $output;
    // }
}

