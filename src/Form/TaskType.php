<?php

namespace App\Form;

use Requestum\ApiBundle\Form\Type\AbstractApiType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TaskType
 */
class TaskType extends AbstractApiType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('description');
    }
}
