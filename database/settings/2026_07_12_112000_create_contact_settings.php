<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contact.phone', '');
        $this->migrator->add('contact.email', '');
        $this->migrator->add('contact.address', '');
        $this->migrator->add('contact.working_hours', '');
        $this->migrator->add('contact.show_map', false);
        $this->migrator->add('contact.social_links', '[]');
    }
};
