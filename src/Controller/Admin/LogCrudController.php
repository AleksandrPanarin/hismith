<?php

namespace App\Controller\Admin;

use App\Entity\Log;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Log::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id')->onlyOnIndex();
        $requestMethod = TextField::new('requestMethod', 'Метод запроса');
        $requestUrl = TextField::new('requestUrl', 'URL');
        $responseCode = IntegerField::new('responseCode', 'Код ответа');
        $responseBody = IntegerField::new('responseBody', 'Тело запроса')->onlyOnDetail();
        $createdAt = DateTimeField::new('createdAt', 'Дата создания')
            ->setFormat('d.m.Y H:mm');

        return [$id, $requestMethod, $requestUrl, $responseCode, $responseBody, $createdAt];
    }
}
