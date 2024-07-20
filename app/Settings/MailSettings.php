<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MailSettings extends Settings
{
    public string $from_address;
    public string $from_name;
    public string $to_address;
    public string $to_barat;
    public ?array $cc_barat;
    public string $to_timur;
    public ?array $cc_timur;
    public string $to_pusat;
    public ?array $cc_pusat;
    public string $to_cass_barat;
    public ?array $cc_cass_barat;
    public string $to_luar_kota;
    public ?array $cc_luar_kota;
    public ?string $driver;
    public ?string $host;
    public int $port;
    public string $encryption;
    public ?string $username;
    public ?string $password;
    public ?int $timeout;
    public ?string $local_domain;

    public static function group(): string
    {
        return 'mail';
    }

    public static function encrypted(): array
    {
        return [
            'username',
            'password',
        ];
    }

    public function loadMailSettingsToConfig($data = null): void
    {
        config([
            'mail.mailers.smtp.host' => $data['host'] ?? $this->host,
            'mail.mailers.smtp.port' => $data['port'] ?? $this->port,
            'mail.mailers.smtp.encryption' => $data['encryption'] ?? $this->encryption,
            'mail.mailers.smtp.username' => $data['username'] ?? $this->username,
            'mail.mailers.smtp.password' => $data['password'] ?? $this->password,
            'mail.from.address' => $data['from_address'] ?? $this->from_address,
            'mail.from.name' => $data['from_name'] ?? $this->from_name,
            'mail.to.address' => $data['to_address'] ?? $this->to_address,
            'mail.to.barat' => $data['to_barat'] ?? $this->to_barat,
            'mail.cc.barat' => $data['cc_barat'] ?? $this->cc_barat,
            'mail.to.timur' => $data['to_timur'] ?? $this->to_timur,
            'mail.cc.timur' => $data['cc_timur'] ?? $this->cc_timur,
            'mail.to.pusat' => $data['to_pusat'] ?? $this->to_pusat,
            'mail.cc.pusat' => $data['cc_pusat'] ?? $this->cc_pusat,
            'mail.to.cass_barat' => $data['to_cass_barat'] ?? $this->to_cass_barat,
            'mail.cc.cass_barat' => $data['cc_cass_barat'] ?? $this->cc_cass_barat,
            'mail.to.luar_kota' => $data['to_luar_kota'] ?? $this->to_luar_kota,
            'mail.cc.luar_kota' => $data['cc_luar_kota'] ?? $this->cc_luar_kota,
        ]);
    }
}
