<?php
namespace App\Form\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use SYmfony\Component\Form\FormBuilderInterface;

class MontreTypes extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Make', TextType::class)
            ->add('model', TextType::class)
            ->add('Price', TextType::class)
            ->add('Description', TextType::class)
            ->add('Image', TextType::class)
            ->add('save', SubmitType::class)
        ;
    }
}

