<?php namespace Lovata\BaseCode\Components;

use Cms\Classes\Page;
use Lovata\Buddies\Facades\AuthHelper;
use Lovata\Shopaholic\Models\Product;
use Lovata\Shopaholic\Models\Settings;
use Lovata\Toolbox\Classes\Helper\SendMailHelper;
use Lovata\Basecode\Models\RequestQuotation as RequestQuotationModel;

/**
 * Class RequestQuotation
 * @package Lovata\Buddies\Components
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class RequestQuotation extends \Lovata\Buddies\Components\Buddies
{
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

    public function onSend()
    {
        $request = post();

        $user = AuthHelper::getUser();

        if (empty($user)) {
            return;
        }

        if (!empty($user->parent)) {
            $user = $user->parent;
        }

        $data = [
            'title'    => $request['quotation_title'] ?? null,
            'notes'    => $request['form_quotation_notes'] ?? null,
            'variants' => isset($request['selected_variants'])
                ? json_decode($request['selected_variants'], true)
                : null,
            'product_id' => $request['product_id'] ?? null,
            'user_id' => $user->id ?? null,
        ];

        $requestQuotation = RequestQuotationModel::create($data);

        $this->sendNotificationManagerEmail($request, $user);

        return [
            'redirect' => Page::url('request-quotation-success')
        ];
    }

    private function sendNotificationManagerEmail($request, $user)
    {
        $sEmailList = Settings::getValue('creating_order_manager_email_list');

        if (empty($sEmailList)) {
            return;
        }

        $arMailData['title'] = $request['quotation_title'] ?? '';
        $arMailData['notes'] = $request['form_quotation_notes'] ?? '';
        $arMailData['name'] = $user->name;
        $arMailData['email'] = $user->email;

        if (empty($request['product_id'])) {
            return;
        }

        $product = Product::find($request['product_id']);

        if (empty($product)) {
            return;
        }

        $arMailData['product_name'] = $product->name;
        $arMailData['product_code'] = $product->code;

        if (!empty($product->aliases)) {
            $arMailData['product_aliases'] = implode(', ', $product->aliases);
        } else {
            $arMailData['product_aliases'] = '';
        }

        $variants = json_decode($request['selected_variants'], true);

        $humanReadableVariants = [];

        if ($variants && is_array($variants)) {
            foreach ($variants as $variant) {
                if (!empty($variant['enabled']) && $variant['enabled'] === true) {
                    $name = trim($variant['name'], '"');
                    $humanReadableVariants[] = "{$name}: {$variant['value']}";
                }
            }
        }

        $arMailData['variants'] = implode("; ", $humanReadableVariants);

        \Log::info(print_r($arMailData, true));

        $obSendMailHelper = SendMailHelper::instance();
        $obSendMailHelper->send(
            'lovata.basecode::mail.request_quotation',
            $sEmailList,
            $arMailData,
            '',
            true);
    }
}
