<?php namespace Lovata\BaseCode\Components;

use Lovata\Buddies\Facades\AuthHelper;
use Lovata\Shopaholic\Models\Product;
use Lovata\Shopaholic\Models\Settings;
use Lovata\Toolbox\Classes\Helper\SendMailHelper;

/**
 * Class ProductAliasesManager
 * @package Lovata\Buddies\Components
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ProductAliasesManager extends \Lovata\Buddies\Components\Buddies
{

    CONST TYPE_DELETE = 'Delete alias';
    CONST TYPE_UPDATE = 'Edit alias';
    CONST TYPE_CREATE = 'Add new alias';

    /**
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'lovata.buddies::lang.component.user_page',
            'description' => 'lovata.buddies::lang.component.user_page_desc',
        ];
    }

    public function onDelete()
    {
        $data = post();

        $this->sendNotificationManagerEmail($data, self::TYPE_DELETE);
    }

    public function onUpdate()
    {
        $data = post();

        $type = (!empty($data['alias_old'])) ? self::TYPE_UPDATE : self::TYPE_CREATE;

        $this->sendNotificationManagerEmail($data, $type);
    }

    private function sendNotificationManagerEmail($arMailData, $type)
    {
        $sEmailList = Settings::getValue('creating_order_manager_email_list');
        if (empty($sEmailList)) {
            return;
        }

        $user = AuthHelper::getUser();

        if (empty($user)) {
            return;
        }

        if (!empty($user->parent)) {
            $user = $user->parent;
        }

        $arMailData['name'] = $user->name;
        $arMailData['email'] = $user->email;

        if (empty($arMailData['product_id'])) {
            return;
        }

        $product = Product::find($arMailData['product_id']);

        if (empty($product)) {
            return;
        }

        $arMailData['product_name'] = $product->name;
        $arMailData['product_code'] = $product->code;

        $arMailData['type'] = $type;

        $obSendMailHelper = SendMailHelper::instance();
        $obSendMailHelper->send(
            'lovata.basecode::mail.edit_aliases',
            $sEmailList,
            $arMailData,
            '',
            true);
    }
}
