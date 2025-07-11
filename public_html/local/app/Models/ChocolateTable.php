<?php
namespace Models;
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\DateField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\TextField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator,
    Bitrix\Main\ORM\Fields\Validator\Base,
    Bitrix\Main\ORM\Fields\Validators\RegExpValidator,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Fields\Relations\OneToMany,
    Bitrix\Main\ORM\Fields\Relations\ManyToMany,
    Bitrix\Main\Entity\Query\Join;

//\Bitrix\Main\Loader::includeModule('iblock');
use \Models\Lists\FactoryTable as FactoryTable;
use \Models\Lists\CountryTable as CountryTable;
/**
 * Class PublisherTable
 *
 * @package Models
 **/

class ChocolateTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'chocolate';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            'id' => (new IntegerField('id',
                []
            ))->configureTitle(Loc::getMessage('_ENTITY_ID_FIELD'))
                ->configurePrimary(true)
                ->configureAutocomplete(true),
            'name' => (new StringField('name',
                [
                    'validation' => [__CLASS__, 'validateName']
                ]
            ))->configureTitle(Loc::getMessage('_ENTITY_NAME_FIELD')),
            'weight' => (new IntegerField('weight',
                []
            ))->configureTitle(Loc::getMessage('_ENTITY_WEIGHT_FIELD')),

            'country_id' => (new IntegerField('country_id',
                []
            ))->configureTitle(Loc::getMessage('_ENTITY_COUNTRY_ID_FIELD')),
            'factory_id' => (new IntegerField('factory_id',
                []
            ))->configureTitle(Loc::getMessage('_ENTITY_FACTORY_ID_FIELD')),

            (new Reference('COUNTRY', \Bitrix\Iblock\Elements\ElementCountryTable::class, Join::on('this.country_id', 'ref.ID')))
                ->configureJoinType('inner'),
            (new Reference('FACTORY',\Bitrix\Iblock\Elements\ElementFactoryTable::class, Join::on('this.factory_id', 'ref.ID')))
                ->configureJoinType('inner'),
        ];
    }
    /**
     * Returns validators for name field.
     *
     * @return array
     */
    public static function validateName()
    {
        return [
            new LengthValidator(3, 50),
        ];
    }
}