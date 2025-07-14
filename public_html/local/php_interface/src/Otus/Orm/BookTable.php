<?php
namespace Otus\Orm;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\Relations\ManyToMany;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\Elements\ElementdoctorsTable;
use Bitrix\Iblock\Elements\ElementspecsTable;
use Bitrix\Iblock\Elements\ElementprocedureTable;
use Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

class BookTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'aziza_book';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

            (new StringField('TITLE'))
                ->configureRequired()
                ->configureSize(255),

            (new IntegerField('YEAR')),

            (new IntegerField('PAGES')),

            (new TextField('DESCRIPTION')),

            (new DateField('PUBLISH_DATE')),

            (new ManyToMany('AUTHORS', AuthorTable::class))
                ->configureTableName('aziza_book_author')
                ->configureLocalPrimary('ID', 'BOOK_ID')
                ->configureRemotePrimary('ID', 'AUTHOR_ID'),

            (new ManyToMany('DOCTOR_RECOMMENDS', ElementdoctorsTable::class))
                ->configureTableName('aziza_doctor_recommends')
                ->configureLocalPrimary('ID', 'BOOK_ID')
                ->configureRemotePrimary('ID', 'DOCTOR_ID'),

            (new ManyToMany('PROCEDURE', ElementprocedureTable::class))
                ->configureTableName('aziza_book_specialization')
                ->configureLocalPrimary('ID', 'BOOK_ID')
                ->configureRemotePrimary('ID', 'SPEC_ID'),

            (new IntegerField('PUBLISHER_ID')),

            (new Reference(
                'PUBLISHER', 
                PublisherTable::class, 
                Join::on('this.PUBLISHER_ID', 'ref.ID')
            ))
                ->configureJoinType('inner'),
             
        ];
    }
}