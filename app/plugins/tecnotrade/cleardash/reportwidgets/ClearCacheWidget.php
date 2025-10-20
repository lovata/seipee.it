<?php namespace Tecnotrade\Cleardash\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use BackendAuth;
use Artisan;
use Flash;
use Log;

class ClearCacheWidget extends ReportWidgetBase
{
    public function render()
    {
        $this->vars['widget'] = $this;
        return $this->makePartial('widget');
    }

    public function onClearCache()
    {
        $user = BackendAuth::getUser();
        if (!$user || !$user->is_superuser) {
            Flash::error('⛔ Accesso negato: solo SuperUser');
            return ['#clear-cache-result' => 'Permesso negato'];
        }

        Artisan::call('cache:clear');
        Flash::success('✅ Cache cleared successfully!');
        Log::info('[Cache Cleaner] Cache cleared by: ' . $user->login);
        return ['#clear-cache-result' => 'Cache pulita con successo!'];
    }

    public function onOptimize()
    {
        $user = BackendAuth::getUser();
        if (!$user || !$user->is_superuser) {
            Flash::error('⛔ Accesso negato: solo SuperUser');
            return ['#clear-cache-result' => 'Permesso negato'];
        }

        Artisan::call('october:optimize');
        Flash::success('🚀 Ottimizzazione completata!');
        Log::info('[Cache Cleaner] Optimize run by: ' . $user->login);
        return ['#clear-cache-result' => 'Ottimizzazione completata con successo!'];
    }
}