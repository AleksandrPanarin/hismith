<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Entity\News;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class NewsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return News::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setSearchFields(['title', 'author']);
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id')->onlyOnIndex();
        $title = TextField::new('title', 'Название');
        $link = UrlField::new('link', 'Ссылка на новость')
            ->formatValue(function ($value, $entity) {
                return "<a href='{$entity->getLink()}' target='_blank'>ссылка</a>";
            });
        $author = TextField::new('author', 'Автор');
        $publishDate = DateTimeField::new('publishDate', 'Дата публикации')
            ->setFormat('d.m.Y H:mm');
        $images = IntegerField::new('images');

        if (Crud::PAGE_DETAIL === $pageName) {
            $images
                ->setLabel('Альбом')
                ->formatValue(function ($value, $entity) {
                    if ($entity->getImages()->count()) {
                        $str = '';
                        /** @var Image $image */
                        foreach ($entity->getImages() as $image) {
                            $str .= "<img src='{$image->getLink()}' width='300' height='300' style='margin: 5px'>";
                        }
                        return $str;
                    }
                    return 1;
                });
        } else {
            $images
                ->setLabel('Кол-во картинок')
                ->formatValue(function ($value, $entity) {
                    return $entity->getImages()->count();
                });
        }
        return [$id, $title, $link, $author, $publishDate, $images];
    }
}
