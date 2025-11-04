<?php namespace Lovata\BaseCode\Classes\Console;

use Backend\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Class ResetAdminPassword
 * @package Lovata\SitemapGenerate\Classes\Console
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ResetAdminPassword extends Command
{
    /**
     * The console command name.
     * @var string
     */
    protected $name = 'basecode:reset_admin_password';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Reset admin password to default value';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
       $obUser = User::where('login', 'admin')->first();
       if (empty($obUser)) {
           $obUser = new User();
           $obUser->email = 'webmaster@lovata.com';
           $obUser->login = 'admin';
           $obUser->password_changed_at = Carbon::now();
           $obUser->activated_at = Carbon::now();
           $obUser->is_activated = true;
           $obUser->is_superuser = true;
       }

       $obUser->password = 'admin';
       $obUser->password_confirmation = 'admin';
       $obUser->save();
    }
}
