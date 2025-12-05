<?php namespace Lovata\ApiSynchronization\classes;

use Lovata\Buddies\Models\User as UserModel;
use Lovata\Buddies\Facades\AuthHelper;
use Illuminate\Console\Command;

class CustomersSyncService
{
    /** @var ApiClientService */
    protected $api;

    /** @var Command|null */
    protected $console;

    public function __construct(ApiClientService $api, Command $console = null)
    {
        $this->api = $api;
        $this->console = $console;
    }

    /**
     * Sync B2B users and create/update Buddies users.
     * Returns array with counts and generated passwords for created users.
     */
    public function sync(int $rows = 200): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $passwords = [];

        $table = 'system_plugin_versions';
//        (new Lovata\Buddies\Updates\UpgradeCustomerFields)->up();
// Получаем последнюю запись
//        $last = DB::table($table)
//            ->orderByDesc('created_at')
//            ->first();
//
//        if ($last) {
//            DB::table($table)
//                ->where('id', $last->id)
//                ->delete();
//
//            echo "Last migration/version '{$last->version}' deleted from history.\n";
//        } else {
//            echo "No migrations found in table '$table'.\n";
//        } die();

        foreach ($this->api->paginate('xbtvw_B2B_Users', $rows, "TIPO_CONTATTO = 'B2B'") as $page) {
            $items = $page['result'] ?? [];
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $row) {
                // Normalize fields from API row
                $email = $this->extractEmail($row);
                if (!$email) {
                    $skipped++;
                    continue;
                }

                // Try to find existing user by email using model scope
                $user = UserModel::getByEmail($email)->first();

                if (!$user) {
                    // Create user with random password
                    $password = $this->generatePassword();
                    $prepared = $this->prepareUserData($row, $email, $password);
                    $data = $prepared['data'];
                    $property = $prepared['property'];
                    try {
                        // Register via AuthHelper and activate immediately (equivalent to ACTIVATION_ON)
                        $user = AuthHelper::register($data, true);
                        if (empty($user)) {
                            throw new \RuntimeException('AuthHelper::register returned empty user');
                        }

                        // Merge and save direct fields on the user model
                        if (!empty($data)) {
                            foreach ($data as $k => $v) {
                                // skip password fields (already set during registration)
                                if (in_array($k, ['password', 'password_confirmation'])) {
                                    continue;
                                }
                                $user->{$k} = $v;
                            }
                            if (!empty($property)) {
                                // in this refactor property entries are just the same keys as data
                                foreach ($property as $k => $v) {
                                    $user->{$k} = $v;
                                }
                            }
                            $user->forceSave();
                        }

                        $created++;
                        $passwords[$email] = $password;
                        if ($this->console) {
                            $this->console->line('Created user: '.$email);
                        }
                    } catch (\Throwable $e) {
                        $skipped++;
                        if ($this->console) {
                            $this->console->error('Failed to create user '.$email.': '.$e->getMessage());
                        }
                        continue;
                    }
                } else {
                    // Optionally update company/name fields if empty
                    $changed = false;
                    if (!empty($row['COMPANY']) && empty($user->last_name)) {
                        $user->last_name = trim($row['COMPANY']);
                        $changed = true;
                    }
                    if (!empty($row['TELEFONO_AGENTE']) && empty($user->phone)) {
                        $user->phone = trim($row['TELEFONO_AGENTE']);
                        $changed = true;
                    }
                    // Merge direct fields from source into existing user model
                    $prepared = $this->prepareUserData($row, $email, null);
                    $data = $prepared['data'] ?? [];
                    $property = $prepared['property'] ?? [];
                    foreach (array_merge($data, $property) as $k => $v) {
                        // skip email
                        if ($k === 'email') {
                            continue;
                        }
                        $user->{$k} = $v;
                        $changed = true;
                    }
                    if ($changed) {
                        try {
                            $user->save();
                            $updated++;
                        } catch (\Throwable $e) {
                            if ($this->console) {
                                $this->console->error('Failed to update user '.$email.': '.$e->getMessage());
                            }
                        }
                    }
                }
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'passwords' => $passwords,
        ];
    }

    protected function extractEmail(array $row): ?string
    {
        // Get EMAIL from row
        $email = $row['EMAIL'] ?? null;
        if (!empty($email) && filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            return trim($email);
        }

        return null;
    }

    protected function prepareUserData(array $row, string $email, ?string $password): array
    {
        // Use NOME as first name
        $firstName = !empty($row['NOME']) ? trim($row['NOME']) : '';
        $lastName = !empty($row['COGNOME']) ? trim($row['COGNOME']) : '';

        $data = [
            'email' => $email,
            'name' => $firstName,
            'last_name' => $lastName,
        ];

        // Add password only when provided (creation path)
        if ($password !== null) {
            $data['password'] = $password;
            $data['password_confirmation'] = $password;
        }

        // Normalize function for field values
        $normalize = function ($v) {
            // Normalize strings
            if (is_string($v)) {
                $s = trim($v);
                // Treat empty JSON-like strings as null
                if ($s === '[]' || $s === '{}' || strtolower($s) === 'null') {
                    return null;
                }
                return $s === '' ? null : $s;
            }

            // Normalize arrays (empty arrays -> null)
            if (is_array($v)) {
                if (empty($v)) {
                    return null;
                }
                // if array of scalars, join with comma
                $allScalar = true;
                $items = [];
                foreach ($v as $item) {
                    if (is_scalar($item) || is_null($item)) {
                        $items[] = trim((string)$item);
                    } else {
                        $allScalar = false;
                        break;
                    }
                }
                if ($allScalar) {
                    $items = array_filter($items, function ($x) { return $x !== ''; });
                    return empty($items) ? null : implode(', ', $items);
                }
                // fallback to json for complex arrays
                $json = json_encode($v, JSON_UNESCAPED_UNICODE);
                return ($json === '[]' || $json === '{}') ? null : $json;
            }

            // Normalize objects
            if (is_object($v)) {
                $vars = get_object_vars($v);
                if (empty($vars)) {
                    return null;
                }
                $json = json_encode($v, JSON_UNESCAPED_UNICODE);
                return ($json === '[]' || $json === '{}') ? null : $json;
            }

            // For other scalar types
            if (is_scalar($v)) {
                return (string)$v;
            }

            return null;
        };

        // Map B2B_Users fields to user properties
        $property = [
            'erp_user_code' => $normalize($row['CODICE_UTENTE_ERP'] ?? null),
            'external_id' => $normalize($row['CODICE_CLIENTE_ARCA'] ?? null),
            'alternate_destination_code' => $normalize($row['CODICE_DESTINAZIONE_DIVERSA'] ?? null),
            'payment' => $normalize($row['PAGAMENTO'] ?? null),
            'shipping' => $normalize($row['SPEDIZIONE'] ?? null),
        ];

        // Remove null entries to keep JSON compact
        $property = array_filter($property, function ($v) { return $v !== null; });

        // Return both form data for registration/update and the property array separately
        return [
            'data' => $data,
            'property' => $property,
        ];
    }

    protected function generatePassword(int $length = 12): string
    {
        // Ensure at least one digit is present in the generated password.
        $digits = '0123456789';
        $others = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#%&*()-_';

        if ($length < 1) {
            return '';
        }

        // Start with one guaranteed digit
        $passwordChars = [];
        $passwordChars[] = $digits[random_int(0, strlen($digits) - 1)];

        $all = $digits . $others;
        $max = strlen($all) - 1;

        for ($i = 1; $i < $length; $i++) {
            $passwordChars[] = $all[random_int(0, $max)];
        }

        // Shuffle characters to avoid digit always being first
        shuffle($passwordChars);

        return implode('', $passwordChars);
    }
}
