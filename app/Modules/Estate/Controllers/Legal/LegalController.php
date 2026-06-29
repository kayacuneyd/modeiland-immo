<?php

namespace App\Modules\Estate\Controllers\Legal;

use App\Core\Controllers\BaseWebController;

class LegalController extends BaseWebController
{
    public function impressum(): string
    {
        $this->setSeo(['title' => 'Impressum — modeiland', 'robots' => 'noindex']);
        return $this->render('App\Modules\Estate\Views\legal\impressum');
    }

    public function datenschutz(): string
    {
        $this->setSeo(['title' => 'Datenschutzerklärung — modeiland', 'robots' => 'noindex']);
        return $this->render('App\Modules\Estate\Views\legal\datenschutz');
    }

    public function agb(): string
    {
        $this->setSeo(['title' => 'Allgemeine Geschäftsbedingungen — modeiland', 'robots' => 'noindex']);
        return $this->render('App\Modules\Estate\Views\legal\agb');
    }
}
