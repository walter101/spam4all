<?php

namespace App\Form;

use App\Entity\ApiToken;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiTokenType extends AbstractType
{
    /**(
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('token', TextareaType::class, [
                'disabled' => true,
                'attr' => [
                    'rows' => 8
                ]
            ])
            ->add('expiresAt', DateTimeType::class, [
                'date_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd HH:mm:ss',
                'disabled' => true,
                'html5' => false
            ])
            ->add('active', CheckboxType::class, [
                'required' => false
            ])
            ->add('Renew', SubmitType::class, [
                'attr' => [
                    'class' => 'bnt, btn-success'
                ]
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApiToken::class,
        ]);
    }
}