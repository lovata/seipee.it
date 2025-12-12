<?php namespace Lovata\BaseCode\Components;

use Flash;
use Input;
use Lang;
use Log;
use Lovata\Buddies\Components\RestorePassword;
use Lovata\Buddies\Models\User;
use Lovata\Toolbox\Traits\Helpers\TraitComponentNotFoundResponse;
use Str;

/**
 * Class UserChildrenPage
 * @package Lovata\Buddies\Components
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class UserChildrenPage extends \Lovata\Buddies\Components\Buddies
{
    use TraitComponentNotFoundResponse;

    CONST DEPARTAMENTS = [
        ['value' => 'procurement', 'label_key' => 'department_procurement'],
        ['value' => 'technical', 'label_key' => 'department_technical'],
        ['value' => 'administration', 'label_key' => 'department_administration'],
        ['value' => 'sales', 'label_key' => 'department_sales'],
        ['value' => 'warehouse', 'label_key' => 'department_warehouse']
    ];

    /** @var null|User */
    protected $obElement = null;

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

    /**
     * Get element object
     * @throws \October\Rain\Exception\AjaxException
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|null
     */
    public function onRun()
    {
        $iUserID = $this->property('slug');
        $bSlugRequired = $this->property('slug_required');

        if ($bSlugRequired && !empty($iUserID)) {
            $this->obElement = User::where('parent_id', $this->obUser->id)
                ->find($iUserID);

            if (!$this->obElement) {
                return $this->getErrorResponse();
            }
            $this->page['obUserEdit'] = $this->obElement;

        } else {
            $this->page['obUsersList'] = $this->getUsers();
        }
        $this->page['departmentOptions'] = $this->getDepartmentOptions();
    }

    /**
     * Registration (ajax request)
     * @return \Illuminate\Http\RedirectResponse|array
     */
    public function onAjax()
    {
        $iUserID = $this->property('slug');
        $bSlugRequired = $this->property('slug_required');

        $bErrorResponse = empty($this->obUser) || ($bSlugRequired && (empty($iUserID) || $this->obUser->id != $iUserID));
        if ($bErrorResponse) {

            $sMessage = Lang::get('lovata.toolbox::lang.message.e_not_correct_request');
            Flash::error($sMessage);

            return $this->getResponseModeAjax();
        }

        $arUserData = Input::all();

        return $this->updateUserData($arUserData);
    }

    /**
     * Update user data
     * @param array $arUserData
     *
     * @return bool|array
     */
    public function updateUserData($arUserData)
    {
        if (empty($arUserData) || empty($this->obUser)) {
            $sMessage = Lang::get('lovata.toolbox::lang.message.e_not_correct_request');
            Flash::error($sMessage);
            return false;
        }

        $iUserID = $arUserData['id'] ?? null;

        if ($iUserID) {
            $obUser = User::where('parent_id', $this->obUser->id)
                ->find($iUserID);

            if (!$obUser) {
                Flash::error(Lang::get('messages_user_not_found'));
                return false;
            }

            $obEmailCheck = User::getByEmail($arUserData['email'])
                ->where('id', '<>', $obUser->id)
                ->first();

            if ($obEmailCheck) {
                $sMessage = Lang::get('lovata.buddies::lang.message.email_is_busy', ['email' => $arUserData['email']]);
                Flash::error($sMessage);
                return false;
            }
        } else {
            $obUser = User::getByEmail($arUserData['email'])->first();
            if ($obUser) {
                $sMessage = Lang::get('lovata.buddies::lang.message.email_is_busy', ['email' => $arUserData['email']]);
                Flash::error($sMessage);
                return false;
            }

            $obUser = new User();
        }

        $obUser->name = $arUserData['name'];
        $obUser->last_name = $arUserData['last_name'];
        $obUser->email = $arUserData['email'];
        $obUser->parent_id = $this->obUser->id;
        $arProperty['role_department'] = $arUserData['property']['role_department'] ?? null;
        $obUser->property = $arProperty;
        $obUser->b2b_permission = isset($arUserData['b2b_permission']);

        if (!$obUser->exists) {
            $obUser->password = $obUser->password_confirmation = Str::random(12);
            $obUser->activate();
            $isNewUser = true;
        }

        try {
            $obUser->save();
        } catch (\October\Rain\Database\ModelException $obException) {
            $this->processValidationError($obException);
            return false;
        }

        $this->page['departmentOptions'] = $this->getDepartmentOptions();

        if ($iUserID) {
            $sMessage = Lang::get('lovata.buddies::lang.message.email_is_busy');
            Flash::success($sMessage);
            return false;
        } else {
            $arUsers = $this->getUsers();
            $this->page['obUsersList'] = $arUsers;

            if (isset($isNewUser)) {
                $this->afterAddUser($obUser);
            }

            return [
                '#usersListContainer' => $this->renderPartial('account/user-list', [
                    'obUsersList' => $arUsers
                ])
            ];
        }
    }

    public function onUpdateUser()
    {
        $arData = Input::get();

        return $this->updateUserData($arData);
    }

    public function onDeleteUser()
    {
        $iUserID = post('user_id');

        if (!$iUserID) {
            $sMessage = Lang::get('messages_user_not_found');
            Flash::error($sMessage);
            return;
        }

        $obUser = User::where('parent_id', $this->obUser->id)
            ->find($iUserID);

        if (!$obUser) {
            $sMessage = Lang::get('messages_user_not_found');
            return;
        }

        try {
            $obUser->delete();
            $sMessage = Lang::get('messages_user_deleted');
            Flash::success($sMessage);
        } catch (\Exception $e) {
            $sMessage = Lang::get('messages_user_delete_error');
            Flash::error($sMessage);
            return;
        }

        $arUsers = $this->getUsers();
        $this->page['obUsersList'] = $arUsers;
        $this->page['departmentOptions'] = $this->getDepartmentOptions();

        return [
            '#usersListContainer' => $this->renderPartial('account/user-list', [
                'obUsersList' => $arUsers
            ])
        ];
    }


    private function getUsers()
    {
        return User::where('parent_id', $this->obUser->id)->get();
    }

    private function getDepartmentOptions()
    {
        return array_reduce(self::DEPARTAMENTS, function($carry, $dep) {
            $carry[] = [
                'value' => $dep['value'],
                'label' => Lang::get($dep['label_key']) ?? '1'
            ];
            return $carry;
        }, []);
    }

    private function afterAddUser(User|null $obUser)
    {
        if (empty($obUser)) {
            return;
        }

        $service = new RestorePassword();

        $service->sendRestoreMail(['email' => $obUser->email]);

        /*Todo логика отправки письма */
        if ($obUser->property['role_department'] === 'sales') {
            Log::info("Send email");
        }
    }
}
