<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * The picture of a Vich-uploadable entity: a thumbnail in lists (read from
 * the stored file name), an upload widget in forms. The picture is optional.
 */
trait ImageFieldsTrait
{
    private function imageFields(string $fileNameProperty): iterable
    {
        yield ImageField::new($fileNameProperty, 'Image')
            ->setBasePath($this->getParameter('app.path.product_images'))
            ->onlyOnIndex();
        yield Field::new('imageFile', 'Image')
            ->setFormType(VichImageType::class)
            ->setRequired(false)
            ->onlyOnForms();
    }
}
