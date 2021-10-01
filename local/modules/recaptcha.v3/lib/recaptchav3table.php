<?php
namespace ReCaptcha\V3;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\DatetimeField,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\TextField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator,
    Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class Recaptchav3Table
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime optional default current datetime
 * <li> FORM_ID int optional
 * <li> FORM_SID string(255) optional
 * <li> STATUS string(255) optional
 * <li> USER_IP string(255) optional
 * <li> RECAPTCHA text optional
 * </ul>
 *
 * @package ReCaptcha\V3
 **/

class Recaptchav3Table extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_recaptchav3';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('RECAPTCHAV3_ENTITY_ID_FIELD')
                ]
            ),
            new DatetimeField(
                'TIMESTAMP_X',
                [
                    'default' => function()
                    {
                        return new DateTime();
                    },
                    'title' => Loc::getMessage('RECAPTCHAV3_ENTITY_TIMESTAMP_X_FIELD')
                ]
            ),
            new IntegerField(
                'FORM_ID',
                [
                    'title' => Loc::getMessage('RECAPTCHAV3_ENTITY_FORM_ID_FIELD')
                ]
            ),
            new StringField(
                'FORM_SID',
                [
                    'validation' => [__CLASS__, 'validateFormSid'],
                    'title' => Loc::getMessage('RECAPTCHAV3_ENTITY_FORM_SID_FIELD')
                ]
            ),
            new StringField(
                'STATUS',
                [
                    'validation' => [__CLASS__, 'validateStatus'],
                    'title' => Loc::getMessage('RECAPTCHAV3_ENTITY_STATUS_FIELD')
                ]
            ),
            new StringField(
                'USER_IP',
                [
                    'validation' => [__CLASS__, 'validateUserIp'],
                    'title' => Loc::getMessage('RECAPTCHAV3_ENTITY_USER_IP_FIELD')
                ]
            ),
            new TextField(
                'RECAPTCHA',
                [
                    'title' => Loc::getMessage('RECAPTCHAV3_ENTITY_RECAPTCHA_FIELD')
                ]
            ),
        ];
    }

    /**
     * Returns validators for FORM_SID field.
     *
     * @return array
     */
    public static function validateFormSid()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for STATUS field.
     *
     * @return array
     */
    public static function validateStatus()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for USER_IP field.
     *
     * @return array
     */
    public static function validateUserIp()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }
}